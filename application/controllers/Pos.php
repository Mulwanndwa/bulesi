<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pos extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Stock_model');
        $this->load->model('Quotation_model');
    }

    private function _cashier_only()
    {
        if ((int)$this->current_user['group_id'] !== 5) {
            redirect('dashboard');
        }
    }

    public function index()
    {
        $this->_cashier_only();

        // Ensure the user has an API token (may be absent for sessions created before this feature)
        $api_token = $this->session->userdata('api_token');
        if (!$api_token) {
            $row = $this->db->select('api_token')->where('id', $this->current_user['id'])->get('auth_users')->row();
            $api_token = $row->api_token ?? '';
            if (!$api_token) {
                $api_token = bin2hex(random_bytes(32));
                $this->db->where('id', $this->current_user['id'])->update('auth_users', ['api_token' => $api_token]);
            }
            $this->session->set_userdata('api_token', $api_token);
        }

        $data = [
            'title'     => 'Point of Sale',
            'user'      => $this->current_user,
            'api_token' => $api_token,
        ];

        $this->load->view('pos/index', $data);
    }

    // ── GET /pos/orders ───────────────────────────────────────────────
    public function orders()
    {
        $this->_cashier_only();

        $orders = $this->db
            ->select('q.id, q.quote_number, q.customer_name, q.subtotal, q.vat_amount, q.total, q.quote_date, q.created_at, q.notes')
            ->from('quotations q')
            ->where('q.user_id', $this->current_user['id'])
            ->where('q.description', 'POS Sale')
            ->order_by('q.created_at', 'DESC')
            ->limit(100)
            ->get()->result();

        $data = [
            'title'  => 'My Orders',
            'user'   => $this->current_user,
            'orders' => $orders,
        ];
        $this->load->view('pos/orders', $data);
    }

    // ── GET /pos/receipt/:id — JSON items for reprinting ──────────────
    public function receipt($id = NULL)
    {
        $this->_cashier_only();

        if (!$id || !ctype_digit((string)$id)) {
            return $this->_json(['error' => 'Invalid ID'], 400);
        }

        $quote = $this->db
            ->where('id', (int)$id)
            ->where('user_id', $this->current_user['id'])
            ->where('description', 'POS Sale')
            ->get('quotations')->row();

        if (!$quote) {
            return $this->_json(['error' => 'Order not found'], 404);
        }

        $items = $this->db
            ->where('quotation_id', (int)$id)
            ->get('quotation_items')->result();

        $company = $this->db
            ->select('c.name, c.logo')
            ->from('auth_users u')
            ->join('companies c', 'c.id = u.company_id', 'left')
            ->where('u.id', $this->current_user['id'])
            ->get()->row();

        return $this->_json([
            'success' => TRUE,
            'quote'   => [
                'id'            => (int)$quote->id,
                'quote_number'  => $quote->quote_number,
                'customer_name' => $quote->customer_name,
                'notes'         => $quote->notes,
                'subtotal'      => (float)$quote->subtotal,
                'vat_amount'    => (float)$quote->vat_amount,
                'total'         => (float)$quote->total,
                'created_at'    => $quote->created_at,
            ],
            'items' => array_map(function($i) {
                return [
                    'name'       => $i->item_description,
                    'unit'       => $i->unit,
                    'quantity'   => (float)$i->quantity,
                    'unit_price' => (float)$i->unit_price,
                    'line_total' => (float)$i->line_total,
                ];
            }, $items),
            'company' => [
                'name'     => $company->name ?? '',
                'logo_url' => !empty($company->logo) ? base_url($company->logo) : NULL,
            ],
        ]);
    }

    // ── POST /pos/place_order (AJAX) ──────────────────────────────────
    public function place_order()
    {
        if ((int)$this->current_user['group_id'] !== 5) {
            return $this->_json(['error' => 'Forbidden'], 403);
        }
        if ($this->input->method() !== 'post') {
            return $this->_json(['error' => 'Method not allowed'], 405);
        }

        $body  = json_decode(file_get_contents('php://input'), TRUE) ?: [];
        $items = [];

        foreach ((array)($body['items'] ?? []) as $item) {
            $desc  = strip_tags(trim($item['name'] ?? ''));
            if (!$desc) continue;
            $qty   = max(0, (float)($item['quantity']   ?? 1));
            $price = max(0, (float)($item['unit_price'] ?? 0));
            if ($qty <= 0) continue;
            $items[] = [
                'item_description' => $desc,
                'unit'             => strip_tags(trim($item['unit'] ?? 'each')),
                'quantity'         => $qty,
                'unit_price'       => $price,
                'line_total'       => round($qty * $price, 2),
            ];
        }

        if (empty($items)) {
            return $this->_json(['error' => 'Cart is empty'], 422);
        }

        $customer = strip_tags(trim($body['customer_name'] ?? '')) ?: 'Walk-in Customer';
        $notes    = strip_tags(trim($body['notes'] ?? ''));
        $vat_rate = 15.00;
        $subtotal = array_sum(array_column($items, 'line_total'));
        $vat_amt  = round($subtotal * $vat_rate / 100, 2);
        $total    = round($subtotal + $vat_amt, 2);

        $row = [
            'quote_number'   => $this->Quotation_model->generate_quote_number(),
            'type_id'        => 1,
            'user_id'        => $this->current_user['id'],
            'customer_name'  => $customer,
            'customer_phone' => '',
            'customer_email' => '',
            'description'    => 'POS Sale',
            'status'         => 'completed',
            'subtotal'       => $subtotal,
            'vat_rate'       => $vat_rate,
            'vat_amount'     => $vat_amt,
            'total'          => $total,
            'quote_date'     => date('Y-m-d'),
            'valid_until'    => NULL,
            'notes'          => $notes,
        ];

        $id = $this->Quotation_model->create($row, $items);
        if (!$id) {
            return $this->_json(['error' => 'Failed to place order. Please try again.'], 500);
        }

        $quote = $this->Quotation_model->get_by_id($id);
        return $this->_json([
            'success'      => TRUE,
            'id'           => $id,
            'quote_number' => $quote->quote_number,
            'subtotal'     => $subtotal,
            'vat_amount'   => $vat_amt,
            'total'        => $total,
        ], 201);
    }

    private function _format_items($items, $categories)
    {
        $cat_map = [];
        foreach ($categories as $c) {
            $cat_map[$c->id] = $c->name;
        }
        $out = [];
        foreach ($items as $item) {
            $out[] = [
                'id'               => (int)$item->id,
                'code'             => $item->code,
                'name'             => $item->name,
                'description'      => $item->description,
                'unit'             => $item->unit,
                'quantity_on_hand' => (float)$item->quantity_on_hand,
                'unit_price'       => (float)$item->unit_price,
                'category_id'      => (int)$item->category_id,
                'category_name'    => $cat_map[$item->category_id] ?? 'Uncategorised',
            ];
        }
        return $out;
    }

    private function _json($data, $status = 200)
    {
        $this->output
            ->set_status_header($status)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data));
    }
}
