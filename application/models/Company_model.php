<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Company_model extends CI_Model {

    public function get_all()
    {
        return $this->db->order_by('name', 'ASC')->get('companies')->result();
    }

    public function get_active()
    {
        return $this->db->where('is_active', 1)->order_by('name', 'ASC')->get('companies')->result();
    }

    public function get_by_id($id)
    {
        return $this->db->where('id', (int)$id)->get('companies')->row();
    }

    public function create($data)
    {
        $this->db->insert('companies', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where('id', (int)$id)->update('companies', $data);
    }

    public function delete($id)
    {
        $this->db->where('id', (int)$id)->delete('companies');
    }

    public function user_count($id)
    {
        return $this->db->where('company_id', (int)$id)->count_all_results('auth_users');
    }
}
