<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Stateless JSON API — authenticated via Bearer token.
 * All methods return JSON; no session, no views.
 *
 * Authentication:
 *   Authorization: Bearer <api_token>
 *   (token is stored in auth_users.api_token)
 *
 * Endpoints:
 *   POST /api/login      — exchange credentials for a Bearer token
 *   POST /api/quotation  — create a new quotation
 */
class Api extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');

        // Allow cross-origin requests (required when the front-end runs on a
        // different origin, e.g. localhost:5173 during development).
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        // Browsers send an OPTIONS preflight before every credentialed POST.
        // Respond with 200 immediately so the real request is allowed through.
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        $this->load->model('Quotation_model');
    }

    // ── POST /api/login ──────────────────────────────────────────────
    public function login()
    {
        if ($this->input->method() !== 'post') {
            return $this->_json(['error' => 'Method Not Allowed'], 405);
        }

        $body     = $this->_body();
        $login    = $this->_str($body['login']    ?? '');
        $password = $body['password'] ?? '';

        if (!$login || !$password) {
            return $this->_json(['error' => 'login and password are required'], 422);
        }

        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $user  = $this->db
            ->where($field,     $login)
            ->where('is_active', 1)
            ->get('auth_users')->row();

        if (!$user || !password_verify($password, $user->password)) {
            return $this->_json(['error' => 'Invalid credentials'], 401);
        }

        // Issue a token if the user does not have one yet
        $token = $user->api_token;
        if (!$token) {
            $token = bin2hex(random_bytes(32));
            $this->db->where('id', $user->id)->update('auth_users', ['api_token' => $token]);
        }

        return $this->_json([
            'success'    => TRUE,
            'token'      => $token,
            'token_type' => 'Bearer',
            'user'       => [
                'id'         => (int)$user->id,
                'username'   => $user->username,
                'email'      => $user->email,
            ],
        ]);
    }

    // ── GET /api/quotations ───────────────────────────────────────────
    public function quotations()
    {
        if ($this->input->method() !== 'get') {
            return $this->_json(['error' => 'Method Not Allowed'], 405);
        }

        $user = $this->_auth();
        if (!$user) {
            return $this->_json(['error' => 'Unauthorized. Provide a valid Bearer token.'], 401);
        }

        $valid_statuses = ['draft','sent','accepted','in_progress','completed','invoiced','rejected','cancelled'];
        $status     = $this->input->get('status') ?: 'all';
        $with_items = (bool)$this->input->get('with_items');

        if ($status !== 'all' && !in_array($status, $valid_statuses)) {
            return $this->_json([
                'error'   => 'Invalid status value.',
                'allowed' => array_merge(['all'], $valid_statuses),
            ], 422);
        }

        $rows = $this->Quotation_model->get_all($status);

        // Cast numeric fields and optionally embed line items
        $data = [];
        foreach ($rows as $q) {
            $row = [
                'id'             => (int)$q->id,
                'quote_number'   => $q->quote_number,
                'type_name'      => $q->type_name,
                'customer_name'  => $q->customer_name,
                'customer_phone' => $q->customer_phone,
                'customer_email' => $q->customer_email,
                'description'    => $q->description,
                'status'         => $q->status,
                'subtotal'       => (float)$q->subtotal,
                'vat_rate'       => (float)$q->vat_rate,
                'vat_amount'     => (float)$q->vat_amount,
                'total'          => (float)$q->total,
                'quote_date'     => $q->quote_date,
                'valid_until'    => $q->valid_until,
                'notes'          => $q->notes,
                'created_by'     => $q->created_by,
                'created_at'     => $q->created_at,
                'updated_at'     => $q->updated_at,
            ];

            if ($with_items) {
                $items = $this->Quotation_model->get_items($q->id);
                $row['items'] = array_map(function($i) {
                    return [
                        'id'               => (int)$i->id,
                        'sort_order'       => (int)$i->sort_order,
                        'item_description' => $i->item_description,
                        'unit'             => $i->unit,
                        'quantity'         => (float)$i->quantity,
                        'unit_price'       => (float)$i->unit_price,
                        'line_total'       => (float)$i->line_total,
                    ];
                }, $items);
            }

            $data[] = $row;
        }

        return $this->_json([
            'success' => TRUE,
            'count'   => count($data),
            'status'  => $status,
            'data'    => $data,
        ]);
    }

    // ── GET /api/quotation/:id  or  POST /api/quotation ──────────────
    public function quotation($id = NULL)
    {
        $method = $this->input->method();

        // ── GET /api/quotation/:id ────────────────────────────────────
        if ($method === 'get') {
            if (!$id || !ctype_digit((string)$id)) {
                return $this->_json(['error' => 'A numeric quotation ID is required.'], 400);
            }

            $user = $this->_auth();
            if (!$user) {
                return $this->_json(['error' => 'Unauthorized. Provide a valid Bearer token.'], 401);
            }

            $quote = $this->Quotation_model->get_by_id((int)$id);
            if (!$quote) {
                return $this->_json(['error' => 'Quotation not found.'], 404);
            }

            $items = $this->Quotation_model->get_items((int)$id);

            return $this->_json([
                'success' => TRUE,
                'data'    => [
                    'id'             => (int)$quote->id,
                    'quote_number'   => $quote->quote_number,
                    'type_name'      => $quote->type_name,
                    'customer_name'  => $quote->customer_name,
                    'customer_phone' => $quote->customer_phone,
                    'customer_email' => $quote->customer_email,
                    'description'    => $quote->description,
                    'status'         => $quote->status,
                    'subtotal'       => (float)$quote->subtotal,
                    'vat_rate'       => (float)$quote->vat_rate,
                    'vat_amount'     => (float)$quote->vat_amount,
                    'total'          => (float)$quote->total,
                    'quote_date'     => $quote->quote_date,
                    'valid_until'    => $quote->valid_until,
                    'notes'          => $quote->notes,
                    'created_by'     => $quote->created_by,
                    'created_at'     => $quote->created_at,
                    'updated_at'     => $quote->updated_at,
                    'items'          => array_map(function($i) {
                        return [
                            'id'               => (int)$i->id,
                            'sort_order'       => (int)$i->sort_order,
                            'item_description' => $i->item_description,
                            'unit'             => $i->unit,
                            'quantity'         => (float)$i->quantity,
                            'unit_price'       => (float)$i->unit_price,
                            'line_total'       => (float)$i->line_total,
                        ];
                    }, $items),
                    'images'         => $this->_images($quote),
                ],
            ]);
        }

        // ── POST /api/quotation ───────────────────────────────────────
        if ($method !== 'post') {
            return $this->_json(['error' => 'Method Not Allowed'], 405);
        }

        $user = $this->_auth();
        if (!$user) {
            return $this->_json(['error' => 'Unauthorized. Provide a valid Bearer token.'], 401);
        }

        $body   = $this->_body();
        $errors = $this->_validate($body);

        if ($errors) {
            return $this->_json(['error' => 'Validation failed', 'details' => $errors], 422);
        }

        $items = $this->_process_items($body['items']);
        if (empty($items)) {
            return $this->_json(['error' => 'At least one valid line item is required'], 422);
        }

        $totals = $this->_calc_totals($items, $body['vat_rate'] ?? 15.00);

        $row = [
            'quote_number'   => $this->Quotation_model->generate_quote_number(),
            'type_id'        => (int)($body['type_id'] ?? 1),
            'user_id'        => $user->id,
            'customer_name'  => $this->_str($body['customer_name']),
            'customer_phone' => $this->_str($body['customer_phone'] ?? ''),
            'customer_email' => $this->_str($body['customer_email'] ?? ''),
            'description'    => $this->_str($body['description']    ?? ''),
            'status'         => 'draft',
            'subtotal'       => $totals['subtotal'],
            'vat_rate'       => $totals['vat_rate'],
            'vat_amount'     => $totals['vat_amount'],
            'total'          => $totals['total'],
            'quote_date'     => $body['quote_date'],
            'valid_until'    => !empty($body['valid_until']) ? $body['valid_until'] : NULL,
            'notes'          => $this->_str($body['notes'] ?? ''),
        ];

        $id = $this->Quotation_model->create($row, $items);
        if (!$id) {
            return $this->_json(['error' => 'Failed to create quotation. Please try again.'], 500);
        }

        $quote      = $this->Quotation_model->get_by_id($id);
        $items_out  = $this->Quotation_model->get_items($id);

        return $this->_json([
            'success'      => TRUE,
            'id'           => $id,
            'quote_number' => $quote->quote_number,
            'quote'        => $quote,
            'items'        => $items_out,
        ], 201);
    }

    // ── Bearer token auth ─────────────────────────────────────────────
    private function _auth()
    {
        // Apache sets Authorization header; nginx/CLI may put it in $_SERVER
        $auth = $_SERVER['HTTP_AUTHORIZATION']          ?? '';
        if (!$auth) $auth = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
        if (!$auth && function_exists('apache_request_headers')) {
            $h    = apache_request_headers();
            $auth = $h['Authorization'] ?? $h['authorization'] ?? '';
        }

        if (!preg_match('/^Bearer\s+(\S+)$/i', $auth, $m)) {
            return NULL;
        }

        return $this->db
            ->where('api_token', $m[1])
            ->where('is_active',  1)
            ->get('auth_users')->row();
    }

    // ── JSON response ─────────────────────────────────────────────────
    private function _json($data, $status = 200)
    {
        $this->output
            ->set_status_header($status)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    // ── Parse raw JSON body ───────────────────────────────────────────
    private function _body()
    {
        $raw = file_get_contents('php://input');
        if (!$raw) return [];
        $decoded = json_decode($raw, TRUE);
        return is_array($decoded) ? $decoded : [];
    }

    // ── Required-field validation ─────────────────────────────────────
    private function _validate($body)
    {
        $errors = [];
        if (empty($body['customer_name']))                    $errors[] = 'customer_name is required';
        if (empty($body['quote_date']))                       $errors[] = 'quote_date is required';
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $body['quote_date'] ?? ''))
                                                              $errors[] = 'quote_date must be YYYY-MM-DD';
        if (empty($body['items']) || !is_array($body['items'])) $errors[] = 'items must be a non-empty array';
        if (!empty($body['valid_until']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $body['valid_until']))
                                                              $errors[] = 'valid_until must be YYYY-MM-DD';
        return $errors;
    }

    // ── Item processing ───────────────────────────────────────────────
    private function _process_items($raw)
    {
        $items = [];
        foreach ((array)$raw as $item) {
            $desc = trim($item['item_description'] ?? '');
            if (!$desc) continue;
            $qty   = max(0, (float)($item['quantity']   ?? 1));
            $price = max(0, (float)($item['unit_price'] ?? 0));
            $items[] = [
                'item_description' => $desc,
                'unit'             => trim($item['unit'] ?? ''),
                'quantity'         => $qty,
                'unit_price'       => $price,
                'line_total'       => round($qty * $price, 2),
            ];
        }
        return $items;
    }

    // ── Totals ────────────────────────────────────────────────────────
    private function _calc_totals($items, $vat_rate_input)
    {
        $subtotal   = array_sum(array_column($items, 'line_total'));
        $vat_rate   = max(0, min(100, (float)$vat_rate_input ?: 15.00));
        $vat_amount = round($subtotal * $vat_rate / 100, 2);
        return [
            'subtotal'   => round($subtotal, 2),
            'vat_rate'   => $vat_rate,
            'vat_amount' => $vat_amount,
            'total'      => round($subtotal + $vat_amount, 2),
        ];
    }

    // ── Build images array from image_1…image_4 columns ─────────────
    private function _images($quote)
    {
        $images = [];
        foreach ([1, 2, 3, 4] as $n) {
            $path = $quote->{"image_$n"} ?? '';
            if (!$path) continue;
            $images[] = [
                'index' => $n,
                'url'   => base_url($path),
            ];
        }
        return $images;
    }

    // ── Sanitise a string value ───────────────────────────────────────
    private function _str($v)
    {
        return strip_tags(trim((string)$v));
    }
}
