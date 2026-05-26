<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Stock_model extends CI_Model {

    public function get_stats()
    {
        return $this->db->query("
            SELECT
                COUNT(*)                                                                      AS total_items,
                COALESCE(SUM(quantity_on_hand * unit_cost), 0)                               AS total_value,
                SUM(CASE WHEN quantity_on_hand <= 0 THEN 1 ELSE 0 END)                      AS out_of_stock,
                SUM(CASE WHEN quantity_on_hand > 0 AND quantity_on_hand <= reorder_level
                         AND reorder_level > 0 THEN 1 ELSE 0 END)                           AS low_stock
            FROM stock_items WHERE is_active = 1
        ")->row();
    }

    public function get_all($category_id = NULL)
    {
        $this->db->select('s.*, c.name AS category_name, (s.quantity_on_hand * s.unit_cost) AS stock_value')
                 ->from('stock_items s')
                 ->join('stock_categories c', 'c.id = s.category_id', 'left');
        if ($category_id) {
            $this->db->where('s.category_id', (int)$category_id);
        }
        return $this->db->order_by('c.name ASC, s.name ASC')->get()->result();
    }

    public function get_by_id($id)
    {
        return $this->db->select('s.*, c.name AS category_name, (s.quantity_on_hand * s.unit_cost) AS stock_value')
                        ->from('stock_items s')
                        ->join('stock_categories c', 'c.id = s.category_id', 'left')
                        ->where('s.id', (int)$id)
                        ->get()->row();
    }

    public function get_categories()
    {
        return $this->db->order_by('name')->get('stock_categories')->result();
    }

    public function get_low_stock()
    {
        return $this->db->select('s.*, c.name AS category_name')
                        ->from('stock_items s')
                        ->join('stock_categories c', 'c.id = s.category_id', 'left')
                        ->where('s.is_active', 1)
                        ->where('s.reorder_level >', 0)
                        ->where('s.quantity_on_hand <=', $this->db->dbprefix('').'s.reorder_level', FALSE)
                        ->order_by('s.quantity_on_hand ASC')
                        ->get()->result();
    }

    public function create($data)
    {
        $user_id = $data['_user_id'];
        unset($data['_user_id']);

        $this->db->insert('stock_items', $data);
        $id = $this->db->insert_id();

        // Auto-generate code if not provided
        if (empty($data['code'])) {
            $this->db->where('id', $id)->update('stock_items', [
                'code' => 'STK-' . str_pad($id, 4, '0', STR_PAD_LEFT),
            ]);
        }

        // Record opening stock movement if quantity > 0
        if (!empty($data['quantity_on_hand']) && $data['quantity_on_hand'] > 0) {
            $this->db->insert('stock_movements', [
                'stock_item_id'   => $id,
                'movement_type'   => 'in',
                'quantity'        => $data['quantity_on_hand'],
                'quantity_before' => 0,
                'quantity_after'  => $data['quantity_on_hand'],
                'unit_cost'       => $data['unit_cost'] ?? 0,
                'reference'       => 'Opening Stock',
                'notes'           => 'Initial stock on item creation',
                'user_id'         => $user_id,
            ]);
        }

        return $id;
    }

    public function update($id, $data)
    {
        $this->db->where('id', (int)$id)->update('stock_items', $data);
    }

    public function adjust($item_id, $type, $quantity, $unit_cost, $reference, $notes, $user_id)
    {
        $item       = $this->get_by_id($item_id);
        $qty_before = (float)$item->quantity_on_hand;

        if ($type === 'in') {
            $qty_after = $qty_before + $quantity;
        } elseif ($type === 'out') {
            $qty_after = max(0, $qty_before - $quantity);
        } else {
            $qty_after = $quantity; // 'adjustment' sets exact value
        }

        $this->db->trans_start();

        $update = ['quantity_on_hand' => $qty_after];
        if ($unit_cost > 0) {
            $update['unit_cost'] = $unit_cost;
        }
        $this->db->where('id', (int)$item_id)->update('stock_items', $update);

        $this->db->insert('stock_movements', [
            'stock_item_id'   => (int)$item_id,
            'movement_type'   => $type,
            'quantity'        => $type === 'adjustment' ? round(abs($qty_after - $qty_before), 2) : $quantity,
            'quantity_before' => $qty_before,
            'quantity_after'  => $qty_after,
            'unit_cost'       => $unit_cost ?: $item->unit_cost,
            'reference'       => $reference,
            'notes'           => $notes,
            'user_id'         => (int)$user_id,
        ]);

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function get_movements($item_id, $limit = 30)
    {
        return $this->db->select('m.*, u.username')
                        ->from('stock_movements m')
                        ->join('auth_users u', 'u.id = m.user_id', 'left')
                        ->where('m.stock_item_id', (int)$item_id)
                        ->order_by('m.created_at', 'DESC')
                        ->limit($limit)
                        ->get()->result();
    }

    public function delete($id)
    {
        $this->db->where('id', (int)$id)->delete('stock_items');
    }

    public function code_exists($code, $exclude_id = NULL)
    {
        $this->db->where('code', $code);
        if ($exclude_id) $this->db->where('id !=', (int)$exclude_id);
        return $this->db->count_all_results('stock_items') > 0;
    }
}
