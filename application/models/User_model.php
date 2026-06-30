<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $cols = array_column($this->db->field_data('auth_users'), 'name');
        if (!in_array('avatar_path', $cols)) {
            $this->db->query("ALTER TABLE auth_users ADD COLUMN avatar_path VARCHAR(500) NULL DEFAULT NULL");
        }
    }

    public function update_avatar($id, $path)
    {
        $this->db->where('id', (int)$id)->update('auth_users', ['avatar_path' => $path ?: NULL]);
    }

    private function _base_query()
    {
        return $this->db
            ->select('u.*, g.name AS group_name, c.name AS company_name')
            ->from('auth_users u')
            ->join('user_groups g', 'g.id = u.group_id', 'left')
            ->join('companies c', 'c.id = u.company_id', 'left');
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

    public function set_api_token($id, $token)
    {
        $this->db->where('id', (int)$id)->update('auth_users', ['api_token' => $token]);
        return $this->db->affected_rows() > 0;
    }

    public function revoke_api_token($id)
    {
        $this->db->where('id', (int)$id)->update('auth_users', ['api_token' => NULL]);
    }
}
