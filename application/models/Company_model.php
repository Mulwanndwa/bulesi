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

    public function get_order_counts()
    {
        $rows = $this->db->query("
            SELECT u.company_id, COUNT(q.id) AS order_count
            FROM quotations q
            JOIN auth_users u ON u.id = q.user_id
            WHERE u.company_id IS NOT NULL
              AND q.description = 'POS Sale'
            GROUP BY u.company_id
        ")->result();

        $map = [];
        foreach ($rows as $r) {
            $map[(int)$r->company_id] = (int)$r->order_count;
        }
        return $map;
    }

    public function get_orders($id)
    {
        return $this->db->query("
            SELECT q.id, q.quote_number, q.customer_name, q.status, q.total,
                   q.quote_date, q.created_at, u.username AS created_by
            FROM quotations q
            JOIN auth_users u ON u.id = q.user_id
            WHERE u.company_id = ?
              AND q.description = 'POS Sale'
            ORDER BY q.created_at DESC
        ", [(int)$id])->result();
    }

    public function get_quote_counts()
    {
        $rows = $this->db->query("
            SELECT u.company_id, COUNT(q.id) AS quote_count
            FROM quotations q
            JOIN auth_users u ON u.id = q.user_id
            WHERE u.company_id IS NOT NULL
              AND (q.description IS NULL OR q.description != 'POS Sale')
            GROUP BY u.company_id
        ")->result();

        $map = [];
        foreach ($rows as $r) {
            $map[(int)$r->company_id] = (int)$r->quote_count;
        }
        return $map;
    }

    public function get_quotes($id)
    {
        return $this->db->query("
            SELECT q.id, q.quote_number, q.customer_name, q.status, q.total,
                   q.quote_date, q.created_at, u.username AS created_by
            FROM quotations q
            JOIN auth_users u ON u.id = q.user_id
            WHERE u.company_id = ?
              AND (q.description IS NULL OR q.description != 'POS Sale')
            ORDER BY q.created_at DESC
        ", [(int)$id])->result();
    }
}
