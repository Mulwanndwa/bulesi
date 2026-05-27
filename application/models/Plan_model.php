<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Plan_model extends CI_Model {

    public function get_all($user_id = NULL)
    {
        if ($user_id) $this->db->where('p.user_id', (int)$user_id);
        return $this->db
            ->select('p.*, u.username')
            ->from('house_plans p')
            ->join('auth_users u', 'u.id = p.user_id', 'left')
            ->order_by('p.updated_at', 'DESC')
            ->get()->result();
    }

    public function get_by_id($id)
    {
        return $this->db->get_where('house_plans', ['id' => (int)$id])->row();
    }

    public function create($user_id, $title, $plan_data, $grid_size = 20)
    {
        $this->db->insert('house_plans', [
            'user_id'   => (int)$user_id,
            'title'     => $title,
            'plan_data' => $plan_data,
            'grid_size' => (int)$grid_size,
        ]);
        return $this->db->insert_id();
    }

    public function update($id, $title, $plan_data, $grid_size = NULL)
    {
        $data = ['title' => $title, 'plan_data' => $plan_data];
        if ($grid_size) $data['grid_size'] = (int)$grid_size;
        $this->db->where('id', (int)$id)->update('house_plans', $data);
    }

    public function delete($id)
    {
        $this->db->where('id', (int)$id)->delete('house_plans');
    }
}
