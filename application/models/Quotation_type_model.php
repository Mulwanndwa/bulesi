<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Quotation_type_model extends CI_Model {

    public function get_all()
    {
        return $this->db->order_by('sort_order ASC, name ASC')->get('quotation_types')->result();
    }

    public function get_active()
    {
        return $this->db->where('is_active', 1)->order_by('sort_order ASC, name ASC')->get('quotation_types')->result();
    }

    public function get_by_id($id)
    {
        return $this->db->get_where('quotation_types', ['id' => (int)$id])->row();
    }

    public function create($data)
    {
        $this->db->insert('quotation_types', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where('id', (int)$id)->update('quotation_types', $data);
    }

    public function delete($id)
    {
        $in_use = $this->db->where('type_id', (int)$id)->count_all_results('quotations');
        if ($in_use > 0) {
            return FALSE;
        }
        $this->db->where('id', (int)$id)->delete('quotation_types');
        return TRUE;
    }

    public function name_exists($name, $exclude_id = NULL)
    {
        $this->db->where('name', $name);
        if ($exclude_id) $this->db->where('id !=', (int)$exclude_id);
        return $this->db->count_all_results('quotation_types') > 0;
    }

    public function count_in_use($id)
    {
        return $this->db->where('type_id', (int)$id)->count_all_results('quotations');
    }
}
