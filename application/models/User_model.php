<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

    private function _base_query()
    {
        return $this->db
            ->select('u.*, g.name AS group_name')
            ->from('auth_users u')
            ->join('user_groups g', 'g.id = u.group_id', 'left');
    }

    public function get_all()
    {
        return $this->_base_query()
            ->order_by('g.id ASC, u.username ASC')
            ->get()->result();
    }

    public function get_by_id($id)
    {
        return $this->_base_query()
            ->where('u.id', (int)$id)
            ->get()->row();
    }

    public function get_groups()
    {
        return $this->db->order_by('id')->get('user_groups')->result();
    }

    public function create($data)
    {
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        $this->db->insert('auth_users', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        } else {
            unset($data['password']);
        }
        $this->db->where('id', (int)$id)->update('auth_users', $data);
    }

    public function toggle_active($id, $active)
    {
        $this->db->where('id', (int)$id)->update('auth_users', ['is_active' => $active ? 1 : 0]);
    }

    public function delete($id)
    {
        $this->db->where('id', (int)$id)->delete('auth_users');
    }

    public function username_exists($username, $exclude_id = NULL)
    {
        $this->db->where('username', $username);
        if ($exclude_id) $this->db->where('id !=', (int)$exclude_id);
        return $this->db->count_all_results('auth_users') > 0;
    }

    public function email_exists($email, $exclude_id = NULL)
    {
        $this->db->where('email', $email);
        if ($exclude_id) $this->db->where('id !=', (int)$exclude_id);
        return $this->db->count_all_results('auth_users') > 0;
    }
}
