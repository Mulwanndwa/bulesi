<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth_model extends CI_Model {

    private function _base_query()
    {
        return $this->db
            ->select('u.*, g.name AS group_name')
            ->from('auth_users u')
            ->join('user_groups g', 'g.id = u.group_id', 'left')
            ->where('u.is_active', 1);
    }

    public function get_user_by_email($email)
    {
        return $this->_base_query()->where('u.email', $email)->get()->row();
    }

    public function get_user_by_username($username)
    {
        return $this->_base_query()->where('u.username', $username)->get()->row();
    }

    public function update_last_login($user_id)
    {
        $this->db->where('id', $user_id)->update('auth_users', ['last_login' => date('Y-m-d H:i:s')]);
    }
}
