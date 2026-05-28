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
 *   GET  /api/users      — list active users (requires Bearer token)
 *   POST /api/users      — create a new user (requires Bearer token)
 *   PUT  /api/users/:id/password — update a user's password (requires Bearer token)
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
        header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        // Browsers send an OPTIONS preflight before every credentialed POST.
        // Respond with 200 immediately so the real request is allowed through.
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        $this->load->model('Quotation_model');
        $this->load->model('User_model');
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

        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'u.email' : 'u.username';
        $user  = $this->db
            ->select('u.*, g.name AS group_name')
            ->from('auth_users u')
            ->join('user_groups g', 'g.id = u.group_id', 'left')
            ->where($field,      $login)
            ->where('u.is_active', 1)
            ->get()->row();

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
                'group_id'   => (int)$user->group_id,
                'group_name' => $user->group_name,
            ],
        ]);
    }

    // ── GET|POST /api/users ───────────────────────────────────────────
    public function users()
    {
        $method = $this->input->method();

        if (!in_array($method, ['get', 'post'])) {
            return $this->_json(['error' => 'Method Not Allowed'], 405);
        }

        $user = $this->_auth();
        if (!$user) {
            return $this->_json(['error' => 'Unauthorized. Provide a valid Bearer token.'], 401);
        }

        // ── GET /api/users ────────────────────────────────────────────
        if ($method === 'get') {
            $rows = $this->db
                ->select('u.id, u.username, u.email, u.is_active, u.created_at, u.updated_at, g.name AS group_name, COUNT(q.id) AS quotations_count')
                ->from('auth_users u')
                ->join('user_groups g', 'g.id = u.group_id', 'left')
                ->join('quotations q', 'q.user_id = u.id AND q.is_read = 0', 'left')
                ->group_by('u.id')
                ->get()->result();

            $data = array_map(function($u) {
                return [
                    'id'               => (int)$u->id,
                    'username'         => $u->username,
                    'email'            => $u->email,
                    'group_name'       => $u->group_name,
                    'is_active'        => (bool)$u->is_active,
                    'quotations_count' => (int)$u->quotations_count,
                    'created_at'       => $u->created_at,
                    'updated_at'       => $u->updated_at,
                ];
            }, $rows);

            return $this->_json([
                'success' => TRUE,
                'count'   => count($data),
                'data'    => $data,
            ]);
        }

        // ── POST /api/users ───────────────────────────────────────────
        $body     = $this->_body();
        $username = $this->_str($body['username'] ?? '');
        $email    = $this->_str($body['email']    ?? '');
        $password = $body['password'] ?? '';
        $group_id = isset($body['group_id']) ? (int)$body['group_id'] : NULL;

        $errors = [];
        if (!$username)                                          $errors[] = 'username is required';
        if (!$email)                                             $errors[] = 'email is required';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))          $errors[] = 'email is invalid';
        if (!$password)                                          $errors[] = 'password is required';
        if (strlen($password) < 6)                               $errors[] = 'password must be at least 6 characters';
        if (!$group_id)                                          $errors[] = 'group_id is required';

        if (!$errors && $this->User_model->username_exists($username)) $errors[] = 'username already taken';
        if (!$errors && $this->User_model->email_exists($email))       $errors[] = 'email already registered';

        if ($errors) {
            return $this->_json(['error' => 'Validation failed', 'details' => $errors], 422);
        }

        $new_id = $this->User_model->create([
            'username'  => $username,
            'email'     => $email,
            'password'  => $password,
            'group_id'  => $group_id,
            'is_active' => isset($body['is_active']) ? (int)(bool)$body['is_active'] : 1,
        ]);

        if (!$new_id) {
            return $this->_json(['error' => 'Failed to create user. Please try again.'], 500);
        }

        $created = $this->User_model->get_by_id($new_id);

        return $this->_json([
            'success' => TRUE,
            'data'    => [
                'id'         => (int)$created->id,
                'username'   => $created->username,
                'email'      => $created->email,
                'group_id'   => (int)$created->group_id,
                'group_name' => $created->group_name,
                'is_active'  => (bool)$created->is_active,
                'created_at' => $created->created_at,
            ],
        ], 201);
    }

    // ── GET /api/groups ───────────────────────────────────────────────
    public function groups()
    {
        if ($this->input->method() !== 'get') {
            return $this->_json(['error' => 'Method Not Allowed'], 405);
        }

        $user = $this->_auth();
        if (!$user) {
            return $this->_json(['error' => 'Unauthorized. Provide a valid Bearer token.'], 401);
        }

        $rows = $this->User_model->get_groups();
        $data = array_map(function($g) {
            return ['id' => (int)$g->id, 'name' => $g->name];
        }, $rows);

        return $this->_json(['success' => TRUE, 'data' => $data]);
    }

    // ── PUT /api/users/:id/password ───────────────────────────────────
    public function user_password($id = NULL)
    {
        if ($this->input->method() !== 'put') {
            return $this->_json(['error' => 'Method Not Allowed'], 405);
        }

        if (!$id || !ctype_digit((string)$id)) {
            return $this->_json(['error' => 'A numeric user ID is required.'], 400);
        }

        $auth = $this->_auth();
        if (!$auth) {
            return $this->_json(['error' => 'Unauthorized. Provide a valid Bearer token.'], 401);
        }

        $target = $this->User_model->get_by_id((int)$id);
        if (!$target) {
            return $this->_json(['error' => 'User not found.'], 404);
        }

        $body        = $this->_body();
        $new_password = $body['password'] ?? '';

        if (!$new_password) {
            return $this->_json(['error' => 'Validation failed', 'details' => ['password is required']], 422);
        }
        if (strlen($new_password) < 6) {
            return $this->_json(['error' => 'Validation failed', 'details' => ['password must be at least 6 characters']], 422);
        }

        $this->User_model->update((int)$id, ['password' => $new_password]);

        return $this->_json(['success' => TRUE, 'message' => 'Password updated.']);
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
        $user_id    = (int)$user->group_id === 1
            ? ($this->input->get('user_id') ? (int)$this->input->get('user_id') : NULL)
            : (int)$user->id;

        if ($status !== 'all' && !in_array($status, $valid_statuses)) {
            return $this->_json([
                'error'   => 'Invalid status value.',
                'allowed' => array_merge(['all'], $valid_statuses),
            ], 422);
        }

        $rows = $this->Quotation_model->get_all($status, $user_id);

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
                'is_read'        => (bool)$q->is_read,
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

            if (!$quote->is_read) {
                $this->db->where('id', (int)$id)->update('quotations', ['is_read' => 1]);
                $quote->is_read = 1;
            }

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
                    'is_read'        => (bool)$quote->is_read,
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

        // ── PUT /api/quotation/:id ────────────────────────────────────
        if ($method === 'put') {
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

            $body   = $this->_body();
            $errors = $this->_validate($body);
            if ($errors) {
                return $this->_json(['error' => 'Validation failed', 'details' => $errors], 422);
            }

            $items = $this->_process_items($body['items']);
            if (empty($items)) {
                return $this->_json(['error' => 'At least one valid line item is required.'], 422);
            }

            $valid_statuses = ['draft','sent','accepted','in_progress','completed','invoiced','rejected','cancelled'];
            $status = isset($body['status']) && in_array($body['status'], $valid_statuses)
                ? $body['status']
                : $quote->status;

            $totals = $this->_calc_totals($items, $body['vat_rate'] ?? $quote->vat_rate);

            $row = [
                'type_id'        => (int)($body['type_id'] ?? $quote->type_id),
                'customer_name'  => $this->_str($body['customer_name']),
                'customer_phone' => $this->_str($body['customer_phone'] ?? ''),
                'customer_email' => $this->_str($body['customer_email'] ?? ''),
                'description'    => $this->_str($body['description']    ?? ''),
                'status'         => $status,
                'subtotal'       => $totals['subtotal'],
                'vat_rate'       => $totals['vat_rate'],
                'vat_amount'     => $totals['vat_amount'],
                'total'          => $totals['total'],
                'quote_date'     => $body['quote_date'],
                'valid_until'    => !empty($body['valid_until']) ? $body['valid_until'] : NULL,
                'notes'          => $this->_str($body['notes'] ?? ''),
            ];

            // Clear image slots requested for removal
            if (!empty($body['remove_image_slots']) && is_array($body['remove_image_slots'])) {
                $clear = [];
                foreach ($body['remove_image_slots'] as $slot) {
                    $slot = (int)$slot;
                    if ($slot < 1 || $slot > 4) continue;
                    $path = $quote->{"image_$slot"} ?? '';
                    if ($path && file_exists(FCPATH . $path)) @unlink(FCPATH . $path);
                    $clear["image_$slot"] = NULL;
                }
                if ($clear) $this->db->where('id', (int)$id)->update('quotations', $clear);
            }

            $ok = $this->Quotation_model->update((int)$id, $row, $items);
            if (!$ok) {
                return $this->_json(['error' => 'Failed to update quotation. Please try again.'], 500);
            }

            $updated    = $this->Quotation_model->get_by_id((int)$id);
            $items_out  = $this->Quotation_model->get_items((int)$id);

            return $this->_json([
                'success'      => TRUE,
                'id'           => (int)$id,
                'quote_number' => $updated->quote_number,
                'quote'        => $updated,
                'items'        => $items_out,
                'images'       => $this->_images($updated),
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

    // ── GET|POST /api/quotation/:id/images ──────────────────────────
    public function quotation_images($id = NULL)
    {
        $method = $this->input->method();

        if (!in_array($method, ['get', 'post'])) {
            return $this->_json(['error' => 'Method Not Allowed'], 405);
        }

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

        // ── GET: return images ────────────────────────────────────────
        if ($method === 'get') {
            $images = $this->_images($quote);
            return $this->_json([
                'success'       => TRUE,
                'quotation_id'  => (int)$id,
                'quote_number'  => $quote->quote_number,
                'count'         => count($images),
                'images'        => $images,
            ]);
        }

        $files = $_FILES['images'] ?? [];
        if (empty($files['name'])) {
            return $this->_json(['error' => 'No images provided.'], 422);
        }

        // Normalise single-file vs multiple-file structure
        if (!is_array($files['name'])) {
            foreach ($files as $k => $v) $files[$k] = [$v];
        }

        $upload_dir    = FCPATH . 'uploads/quotations/';
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif', 'image/heic'];

        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, TRUE);

        $uploaded = 0;
        for ($i = 0, $n = count($files['name']); $i < $n; $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
            if (!in_array(strtolower($files['type'][$i]), $allowed_types)) continue;

            // Find next free slot (re-fetch each iteration so concurrent slots are correct)
            $fresh = $this->Quotation_model->get_by_id((int)$id);
            $slot  = NULL;
            foreach ([1, 2, 3, 4] as $s) {
                if (empty($fresh->{"image_$s"})) { $slot = $s; break; }
            }
            if (!$slot) break;

            $ext   = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION) ?: 'jpg');
            $fname = 'quote_' . $id . '_' . $slot . '_' . time() . $i . '.' . $ext;

            if (move_uploaded_file($files['tmp_name'][$i], $upload_dir . $fname)) {
                $this->db->where('id', (int)$id)
                         ->update('quotations', ["image_$slot" => 'uploads/quotations/' . $fname]);
                $uploaded++;
            }
        }

        $updated = $this->Quotation_model->get_by_id((int)$id);
        return $this->_json([
            'success'  => TRUE,
            'uploaded' => $uploaded,
            'images'   => $this->_images($updated),
        ]);
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
