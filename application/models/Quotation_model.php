<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Quotation_model extends CI_Model {

    public function generate_quote_number()
    {
        $row = $this->db->query('SELECT COALESCE(MAX(id), 0) + 1 AS next_id FROM quotations')->row();
        return 'QT-' . date('Y') . '-' . str_pad($row->next_id, 4, '0', STR_PAD_LEFT);
    }

    public function get_all($status = 'all', $user_id = NULL)
    {
        $this->db->select('q.*, u.username AS created_by, t.name AS type_name')
                 ->from('quotations q')
                 ->join('auth_users u',       'u.id = q.user_id', 'left')
                 ->join('quotation_types t',  't.id = q.type_id', 'left');
        if ($status !== 'all') {
            $this->db->where('q.status', $status);
        }
        if ($user_id) {
            $this->db->where('q.user_id', (int)$user_id);
        }
        return $this->db->order_by('q.created_at', 'DESC')->get()->result();
    }

    public function get_by_id($id)
    {
        return $this->db->select('q.*, u.username AS created_by, t.name AS type_name,
                                  c.name AS company_name, c.logo AS company_logo,
                                  c.phone AS company_phone, c.email AS company_email,
                                  c.address AS company_address')
                        ->from('quotations q')
                        ->join('auth_users u',      'u.id = q.user_id',      'left')
                        ->join('quotation_types t', 't.id = q.type_id',      'left')
                        ->join('companies c',       'c.id = u.company_id',   'left')
                        ->where('q.id', (int)$id)
                        ->get()->row();
    }

    public function get_items($quotation_id)
    {
        return $this->db->where('quotation_id', (int)$quotation_id)
                        ->order_by('sort_order', 'ASC')
                        ->get('quotation_items')->result();
    }

    public function get_by_token($token)
    {
        return $this->db->select('q.*, u.username AS created_by, t.name AS type_name,
                                  c.name AS company_name, c.logo AS company_logo,
                                  c.phone AS company_phone, c.email AS company_email,
                                  c.address AS company_address')
                        ->from('quotations q')
                        ->join('auth_users u',      'u.id = q.user_id',    'left')
                        ->join('quotation_types t', 't.id = q.type_id',    'left')
                        ->join('companies c',       'c.id = u.company_id', 'left')
                        ->where('q.public_token', $this->db->escape_str($token))
                        ->get()->row();
    }

    public function ensure_token($id)
    {
        $row = $this->db->select('public_token')->where('id', (int)$id)->get('quotations')->row();
        if ($row && !empty($row->public_token)) return $row->public_token;
        $token = bin2hex(random_bytes(24));
        $this->db->where('id', (int)$id)->update('quotations', ['public_token' => $token]);
        return $token;
    }

    public function save_signature($id, $sig_data, $sig_name)
    {
        $this->db->where('id', (int)$id)->update('quotations', [
            'cust_sig_data'  => $sig_data,
            'cust_sig_name'  => $sig_name,
            'cust_signed_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function create($data, $items)
    {
        if (empty($data['public_token'])) {
            $data['public_token'] = bin2hex(random_bytes(24));
        }
        $this->db->trans_start();
        $this->db->insert('quotations', $data);
        $id = $this->db->insert_id();
        foreach ($items as $i => $item) {
            $item['quotation_id'] = $id;
            $item['sort_order']   = $i;
            $this->db->insert('quotation_items', $item);
        }
        $this->db->trans_complete();
        return $this->db->trans_status() ? $id : FALSE;
    }

    public function update($id, $data, $items)
    {
        $this->db->trans_start();
        $this->db->where('id', (int)$id)->update('quotations', $data);
        $this->db->where('quotation_id', (int)$id)->delete('quotation_items');
        foreach ($items as $i => $item) {
            $item['quotation_id'] = (int)$id;
            $item['sort_order']   = $i;
            $this->db->insert('quotation_items', $item);
        }
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function delete($id)
    {
        $this->db->where('id', (int)$id)->delete('quotations');
        return $this->db->affected_rows() > 0;
    }

    public function update_status($id, $status)
    {
        $this->db->where('id', (int)$id)->update('quotations', ['status' => $status]);
    }

    public function get_stats()
    {
        $row = $this->db->select('COUNT(*) AS total, COALESCE(SUM(total),0) AS total_value')
                        ->get('quotations')->row();
        $stats = ['total' => (int)$row->total, 'total_value' => (float)$row->total_value];

        foreach (['draft','sent','accepted','in_progress','completed','invoiced','rejected','cancelled'] as $s) {
            $stats[$s] = $this->db->where('status', $s)->count_all_results('quotations');
        }
        return $stats;
    }

    public function get_recent($limit = 5)
    {
        return $this->db->select('q.*, u.username AS created_by, t.name AS type_name')
                        ->from('quotations q')
                        ->join('auth_users u',      'u.id = q.user_id', 'left')
                        ->join('quotation_types t', 't.id = q.type_id', 'left')
                        ->order_by('q.created_at', 'DESC')
                        ->limit($limit)
                        ->get()->result();
    }
}
