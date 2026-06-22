<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Chat_model extends CI_Model {

    // ── Conversations ─────────────────────────────────────────────────

    /**
     * All conversations for a user, newest-activity first.
     * Each row includes participants, last message, and unread count.
     */
    public function get_conversations($user_id)
    {
        $rows = $this->db->query("
            SELECT c.id, c.created_at, c.updated_at
            FROM conversations c
            JOIN conversation_participants cp ON cp.conversation_id = c.id
            WHERE cp.user_id = ?
            ORDER BY c.updated_at DESC
        ", [(int)$user_id])->result();

        $out = [];
        foreach ($rows as $c) {
            $c->participants  = $this->_participants($c->id, $user_id);
            $c->last_message  = $this->_last_message($c->id);
            $c->unread_count  = $this->_unread_count($c->id, (int)$user_id);
            $out[] = $c;
        }
        return $out;
    }

    /**
     * Find an existing 1-on-1 conversation between two users.
     * Returns the conversation id or NULL.
     */
    public function find_dm($user_id, $other_id)
    {
        $row = $this->db->query("
            SELECT c.id
            FROM conversations c
            JOIN conversation_participants p1 ON p1.conversation_id = c.id AND p1.user_id = ?
            JOIN conversation_participants p2 ON p2.conversation_id = c.id AND p2.user_id = ?
            WHERE (
                SELECT COUNT(*) FROM conversation_participants
                WHERE conversation_id = c.id
            ) = 2
            LIMIT 1
        ", [(int)$user_id, (int)$other_id])->row();

        return $row ? (int)$row->id : NULL;
    }

    /**
     * Create a conversation and add participants.
     */
    public function create_conversation(array $user_ids)
    {
        $this->db->trans_start();
        $this->db->query("INSERT INTO conversations (created_at, updated_at) VALUES (NOW(), NOW())");
        $id = $this->db->insert_id();
        foreach (array_unique($user_ids) as $uid) {
            $this->db->insert('conversation_participants', [
                'conversation_id' => $id,
                'user_id'         => (int)$uid,
            ]);
        }
        $this->db->trans_complete();
        return $this->db->trans_status() ? $id : FALSE;
    }

    public function get_conversation($id, $user_id)
    {
        $row = $this->db->query("
            SELECT c.id, c.created_at, c.updated_at
            FROM conversations c
            JOIN conversation_participants cp ON cp.conversation_id = c.id AND cp.user_id = ?
            WHERE c.id = ?
            LIMIT 1
        ", [(int)$user_id, (int)$id])->row();

        if (!$row) return NULL;

        $row->participants = $this->_participants($row->id, $user_id);
        $row->unread_count = $this->_unread_count($row->id, (int)$user_id);
        return $row;
    }

    public function is_participant($conversation_id, $user_id)
    {
        return (bool)$this->db->query(
            "SELECT 1 FROM conversation_participants WHERE conversation_id = ? AND user_id = ? LIMIT 1",
            [(int)$conversation_id, (int)$user_id]
        )->row();
    }

    // ── Messages ──────────────────────────────────────────────────────

    public function get_messages($conversation_id, $limit = 50, $before_id = NULL)
    {
        $sql = "
            SELECT m.id, m.conversation_id, m.sender_id, m.body, m.quote_id, m.created_at,
                   u.username AS sender_username,
                   q.quote_number, q.customer_name, q.total, q.status AS quote_status,
                   q.public_token AS quote_token
            FROM messages m
            JOIN auth_users u ON u.id = m.sender_id
            LEFT JOIN quotations q ON q.id = m.quote_id
            WHERE m.conversation_id = ?
        ";
        $params = [(int)$conversation_id];

        if ($before_id) {
            $sql .= " AND m.id < ?";
            $params[] = (int)$before_id;
        }

        $sql .= " ORDER BY m.id DESC LIMIT ?";
        $params[] = (int)$limit;

        $rows = $this->db->query($sql, $params)->result();
        // Return oldest-first to the client
        return array_reverse($rows);
    }

    public function send_message($conversation_id, $sender_id, $body, $quote_id = NULL)
    {
        $this->db->insert('messages', [
            'conversation_id' => (int)$conversation_id,
            'sender_id'       => (int)$sender_id,
            'body'            => $body,
            'quote_id'        => $quote_id ? (int)$quote_id : NULL,
        ]);
        $msg_id = $this->db->insert_id();

        // Bump conversation updated_at so it floats to top of list
        $this->db->where('id', (int)$conversation_id)
                 ->update('conversations', ['updated_at' => date('Y-m-d H:i:s')]);

        return $msg_id;
    }

    public function get_message($id)
    {
        return $this->db->query("
            SELECT m.id, m.conversation_id, m.sender_id, m.body, m.quote_id, m.created_at,
                   u.username AS sender_username,
                   q.quote_number, q.customer_name, q.total, q.status AS quote_status,
                   q.public_token AS quote_token
            FROM messages m
            JOIN auth_users u ON u.id = m.sender_id
            LEFT JOIN quotations q ON q.id = m.quote_id
            WHERE m.id = ?
        ", [(int)$id])->row();
    }

    public function mark_read($conversation_id, $user_id)
    {
        $this->db->where('conversation_id', (int)$conversation_id)
                 ->where('user_id', (int)$user_id)
                 ->update('conversation_participants', ['last_read_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Return push tokens for all participants except the sender.
     * Skips NULL / empty tokens automatically.
     */
    public function get_recipient_tokens($conversation_id, $exclude_user_id)
    {
        $rows = $this->db->query("
            SELECT u.push_token
            FROM conversation_participants cp
            JOIN auth_users u ON u.id = cp.user_id
            WHERE cp.conversation_id = ?
              AND cp.user_id != ?
              AND u.push_token IS NOT NULL
              AND u.push_token != ''
              AND u.is_active = 1
        ", [(int)$conversation_id, (int)$exclude_user_id])->result();

        return array_column($rows, 'push_token');
    }

    // ── Private helpers ───────────────────────────────────────────────

    private function _participants($conversation_id, $exclude_user_id = NULL)
    {
        $this->db->select('u.id, u.username, g.name AS group_name')
                 ->from('auth_users u')
                 ->join('conversation_participants cp', 'cp.user_id = u.id')
                 ->join('user_groups g', 'g.id = u.group_id', 'left')
                 ->where('cp.conversation_id', (int)$conversation_id);

        if ($exclude_user_id) {
            $this->db->where('u.id !=', (int)$exclude_user_id);
        }

        return $this->db->get()->result();
    }

    private function _last_message($conversation_id)
    {
        return $this->db->query("
            SELECT m.id, m.body, m.created_at, u.username AS sender_username
            FROM messages m
            JOIN auth_users u ON u.id = m.sender_id
            WHERE m.conversation_id = ?
            ORDER BY m.id DESC LIMIT 1
        ", [(int)$conversation_id])->row();
    }

    private function _unread_count($conversation_id, $user_id)
    {
        $cp = $this->db->query("
            SELECT last_read_at FROM conversation_participants
            WHERE conversation_id = ? AND user_id = ?
        ", [(int)$conversation_id, $user_id])->row();

        if (!$cp) return 0;

        $this->db->where('conversation_id', (int)$conversation_id)
                 ->where('sender_id !=', $user_id);

        if ($cp->last_read_at) {
            $this->db->where('created_at >', $cp->last_read_at);
        }

        return $this->db->count_all_results('messages');
    }

    private function _format_message($m)
    {
        $out = [
            'id'               => (int)$m->id,
            'conversation_id'  => (int)$m->conversation_id,
            'sender_id'        => (int)$m->sender_id,
            'sender_username'  => $m->sender_username,
            'body'             => $m->body,
            'created_at'       => $m->created_at,
            'quote'            => NULL,
        ];

        if ($m->quote_id) {
            $out['quote'] = [
                'id'           => (int)$m->quote_id,
                'quote_number' => $m->quote_number,
                'customer_name'=> $m->customer_name,
                'total'        => (float)$m->total,
                'status'       => $m->quote_status,
                'public_url'   => !empty($m->quote_token) ? base_url('q/' . $m->quote_token) : NULL,
            ];
        }

        return $out;
    }

    public function format_message($m) { return $this->_format_message($m); }
}
