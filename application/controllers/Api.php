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
 *   POST /api/login           — exchange credentials for a Bearer token
 *   POST /api/register        — create a new account and receive a Bearer token
 *   GET  /api/users           — list active users (requires Bearer token)
 *   POST /api/users           — create a new user (requires Bearer token)
 *   PUT  /api/users/:id/password — update a user's password (requires Bearer token)
 *   POST /api/push-token      — update the authenticated user's push token
 *   POST /api/profile/avatar  — upload / replace the authenticated user's profile photo (multipart: avatar)
 *   GET  /api/stock           — list active stock items grouped by category (requires Bearer token)
 *   GET  /api/companies       — list companies with their linked users (requires Bearer token)
 *   POST /api/quotation       — create a new quotation
 *
 * Chat:
 *   GET  /api/conversations              — list the user's conversations
 *   POST /api/conversations              — start or find a DM with another user { user_id }
 *   GET  /api/conversations/:id          — get a single conversation
 *   GET  /api/conversations/:id/messages — list messages (supports ?limit=&before_id=)
 *   POST /api/conversations/:id/messages — send a message { body, quote_id? }
 *   PUT  /api/conversations/:id/read     — mark all messages in conversation as read
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
        $this->load->model('Company_model');
        $this->load->model('Chat_model');
        $this->config->load('push');
        $this->load->helper('push');
    }

    // ── POST /api/register ───────────────────────────────────────────
    public function register()
    {
        if ($this->input->method() !== 'post') {
            return $this->_json(['error' => 'Method Not Allowed'], 405);
        }

        $body       = $this->_body();
        $username   = $this->_str($body['username']   ?? '');
        $first_name = $this->_str($body['first_name'] ?? '');
        $last_name  = $this->_str($body['last_name']  ?? '');
        $email      = $this->_str($body['email']      ?? '');
        $password   = $body['password']               ?? '';
        $company_id = isset($body['company_id']) ? (int)$body['company_id'] : 0;
        $group_id   = isset($body['group_id'])   ? (int)$body['group_id']   : 0;

        $errors = [];
        if (!$username)                                    $errors[] = 'username is required';
        if (strlen($username) < 3)                         $errors[] = 'username must be at least 3 characters';
        if (!$first_name)                                  $errors[] = 'first_name is required';
        if (!$last_name)                                   $errors[] = 'last_name is required';
        if (!$email)                                       $errors[] = 'email is required';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))    $errors[] = 'email is invalid';
        if (!$password)                                    $errors[] = 'password is required';
        if (strlen($password) < 6)                         $errors[] = 'password must be at least 6 characters';
        if (!$company_id)                                  $errors[] = 'company_id is required';

        if ($errors) {
            return $this->_json(['error' => 'Validation failed', 'details' => $errors], 422);
        }

        if ($this->User_model->username_exists($username)) {
            return $this->_json(['error' => 'Validation failed', 'details' => ['username already taken']], 422);
        }
        if ($this->User_model->email_exists($email)) {
            return $this->_json(['error' => 'Validation failed', 'details' => ['email already registered']], 422);
        }

        // Validate company exists and is active
        $company = $this->db->where('id', $company_id)->where('is_active', 1)->get('companies')->row();
        if (!$company) {
            return $this->_json(['error' => 'Validation failed', 'details' => ['company_id is invalid']], 422);
        }

        // Resolve group — default to first non-admin group if not supplied or invalid
        if ($group_id) {
            $group = $this->db->where('id', $group_id)->get('user_groups')->row();
        }
        if (empty($group)) {
            $group = $this->db->where('name !=', 'Admin')->order_by('id', 'ASC')->limit(1)->get('user_groups')->row();
        }
        if (!$group) {
            return $this->_json(['error' => 'No user group available. Contact an administrator.'], 500);
        }

        $token  = bin2hex(random_bytes(32));
        $new_id = $this->User_model->create([
            'username'   => $username,
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'email'      => $email,
            'password'   => $password,
            'group_id'   => $group->id,
            'company_id' => $company_id,
            'is_active'  => 1,
            'api_token'  => $token,
        ]);

        if (!$new_id) {
            return $this->_json(['error' => 'Registration failed. Please try again.'], 500);
        }

        // Persist push token if provided at registration
        $push_token = $this->_str($body['push_token'] ?? '');
        if ($push_token) {
            $this->db->where('id', $new_id)->update('auth_users', ['push_token' => $push_token]);
        }

        return $this->_json([
            'success'    => TRUE,
            'token'      => $token,
            'token_type' => 'Bearer',
            'user'       => [
                'id'           => (int)$new_id,
                'username'     => $username,
                'first_name'   => $first_name,
                'last_name'    => $last_name,
                'full_name'    => trim("$first_name $last_name"),
                'email'        => $email,
                'group_id'     => (int)$group->id,
                'group_name'   => $group->name,
                'company_id'   => $company_id,
                'company_name' => $company->name,
                'company_logo_url' => !empty($company->logo) ? base_url($company->logo) : NULL,
                'company_address'  => $company->address  ?: NULL,
                'company_phone'    => $company->phone    ?: NULL,
                'company_email'    => $company->email    ?: NULL,
            ],
        ], 201);
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
            ->select('u.*, g.name AS group_name, c.name AS company_name, c.logo AS company_logo_url, c.address AS company_address, c.phone AS company_phone, c.email AS company_email')
            ->from('auth_users u')
            ->join('user_groups g', 'g.id = u.group_id', 'left')
            ->join('companies c', 'c.id = u.company_id', 'left')
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

        // Persist push token if provided
        $push_token = $this->_str($body['push_token'] ?? '');
        if ($push_token) {
            $this->db->where('id', $user->id)->update('auth_users', ['push_token' => $push_token]);
        }

        return $this->_json([
            'success'    => TRUE,
            'token'      => $token,
            'token_type' => 'Bearer',
            'user'       => [
                'id'           => (int)$user->id,
                'username'     => $user->username,
                'first_name'   => $user->first_name ?: NULL,
                'last_name'    => $user->last_name  ?: NULL,
                'full_name'    => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->username,
                'email'        => $user->email,
                'group_id'     => (int)$user->group_id,
                'group_name'   => $user->group_name,
                'company_id'       => $user->company_id ? (int)$user->company_id : NULL,
                'company_name'     => $user->company_name,
                'company_logo_url' => $user->company_logo_url  ?: NULL,
                'company_address'  => $user->company_address   ?: NULL,
                'company_phone'    => $user->company_phone     ?: NULL,
                'company_email'    => $user->company_email     ?: NULL,
                'avatar_url'       => !empty($user->avatar_path) ? base_url($user->avatar_path) : NULL,
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
                ->select('u.id, u.username, u.first_name, u.last_name, u.email, u.is_active, u.avatar_path, u.created_at, u.updated_at, u.company_id, g.name AS group_name, c.name AS company_name, COUNT(q.id) AS quotations_count')
                ->from('auth_users u')
                ->join('user_groups g', 'g.id = u.group_id', 'left')
                ->join('companies c', 'c.id = u.company_id', 'left')
                ->join('quotations q', 'q.user_id = u.id AND q.is_read = 0', 'left')
                ->group_by('u.id')
                ->get()->result();

            $data = array_map(function($u) {
                return [
                    'id'               => (int)$u->id,
                    'username'         => $u->username,
                    'first_name'       => $u->first_name ?: NULL,
                    'last_name'        => $u->last_name  ?: NULL,
                    'full_name'        => trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) ?: $u->username,
                    'email'            => $u->email,
                    'group_name'       => $u->group_name,
                    'company_id'       => $u->company_id ? (int)$u->company_id : NULL,
                    'company_name'     => $u->company_name,
                    'is_active'        => (bool)$u->is_active,
                    'quotations_count' => (int)$u->quotations_count,
                    'avatar_url'       => !empty($u->avatar_path) ? base_url($u->avatar_path) : NULL,
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
        $body       = $this->_body();
        $username   = $this->_str($body['username']   ?? '');
        $first_name = $this->_str($body['first_name'] ?? '');
        $last_name  = $this->_str($body['last_name']  ?? '');
        $email      = $this->_str($body['email']      ?? '');
        $password   = $body['password'] ?? '';
        $group_id   = isset($body['group_id']) ? (int)$body['group_id'] : NULL;

        $errors = [];
        if (!$username)                                          $errors[] = 'username is required';
        if (!$first_name)                                        $errors[] = 'first_name is required';
        if (!$last_name)                                         $errors[] = 'last_name is required';
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
            'username'   => $username,
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'email'      => $email,
            'password'   => $password,
            'group_id'   => $group_id,
            'is_active'  => isset($body['is_active']) ? (int)(bool)$body['is_active'] : 1,
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
                'first_name' => $created->first_name ?: NULL,
                'last_name'  => $created->last_name  ?: NULL,
                'full_name'  => trim(($created->first_name ?? '') . ' ' . ($created->last_name ?? '')) ?: $created->username,
                'email'      => $created->email,
                'group_id'   => (int)$created->group_id,
                'group_name' => $created->group_name,
                'is_active'  => (bool)$created->is_active,
                'created_at' => $created->created_at,
            ],
        ], 201);
    }

    // ── GET /api/companies ────────────────────────────────────────────
    // ── GET /api/companies/public — no auth required ──────────────────
    public function companies_public()
    {
        $rows = $this->db
            ->select('id, name')
            ->where('is_active', 1)
            ->order_by('name', 'ASC')
            ->get('companies')
            ->result();
        return $this->_json(['success' => TRUE, 'data' => $rows]);
    }

    public function companies()
    {
        if ($this->input->method() !== 'get') {
            return $this->_json(['error' => 'Method Not Allowed'], 405);
        }

        $user = $this->_auth();
        if (!$user) {
            return $this->_json(['error' => 'Unauthorized. Provide a valid Bearer token.'], 401);
        }

        $companies = $this->Company_model->get_all();

        // Fetch all users with their group names in one query, keyed by company_id
        $user_rows = $this->db
            ->select('u.id, u.username, u.first_name, u.last_name, u.email, u.is_active, u.company_id, g.name AS group_name')
            ->from('auth_users u')
            ->join('user_groups g', 'g.id = u.group_id', 'left')
            ->where('u.company_id IS NOT NULL', NULL, FALSE)
            ->get()->result();

        $users_by_company = [];
        foreach ($user_rows as $u) {
            $users_by_company[(int)$u->company_id][] = [
                'id'         => (int)$u->id,
                'username'   => $u->username,
                'first_name' => $u->first_name ?: NULL,
                'last_name'  => $u->last_name  ?: NULL,
                'full_name'  => trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) ?: $u->username,
                'email'      => $u->email,
                'group_name' => $u->group_name,
                'is_active'  => (bool)$u->is_active,
            ];
        }

        $data = array_map(function($c) use ($users_by_company) {
            return [
                'id'         => (int)$c->id,
                'name'       => $c->name,
                'address'    => $c->address,
                'phone'      => $c->phone,
                'email'      => $c->email,
                'logo_url'   => !empty($c->logo) ? base_url($c->logo) : NULL,
                'is_active'  => (bool)$c->is_active,
                'created_at' => $c->created_at,
                'updated_at' => $c->updated_at,
                'users'      => $users_by_company[(int)$c->id] ?? [],
            ];
        }, $companies);

        return $this->_json([
            'success' => TRUE,
            'count'   => count($data),
            'data'    => $data,
        ]);
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

    // ── POST /api/push-token ──────────────────────────────────────────
    public function push_token()
    {
        if ($this->input->method() !== 'post') {
            return $this->_json(['error' => 'Method Not Allowed'], 405);
        }

        $user = $this->_auth();
        if (!$user) {
            return $this->_json(['error' => 'Unauthorized. Provide a valid Bearer token.'], 401);
        }

        $body  = $this->_body();
        $token = $this->_str($body['push_token'] ?? '');

        if (!$token) {
            return $this->_json(['error' => 'push_token is required'], 422);
        }

        $this->db->where('id', $user->id)->update('auth_users', ['push_token' => $token]);

        return $this->_json(['success' => TRUE]);
    }

    // ── GET /api/stock ────────────────────────────────────────────────
    public function stock()
    {
        if ($this->input->method() !== 'get') {
            return $this->_json(['error' => 'Method Not Allowed'], 405);
        }

        $user = $this->_auth();
        if (!$user) {
            return $this->_json(['error' => 'Unauthorized. Provide a valid Bearer token.'], 401);
        }

        $categories = $this->db
            ->order_by('name', 'ASC')
            ->get('stock_categories')->result();

        $items = $this->db
            ->where('is_active', 1)
            ->order_by('name', 'ASC')
            ->get('stock_items')->result();

        // Index items by category_id for O(1) grouping
        $grouped = [];
        foreach ($items as $item) {
            $grouped[$item->category_id][] = [
                'id'               => (int)$item->id,
                'code'             => $item->code,
                'name'             => $item->name,
                'description'      => $item->description,
                'unit'             => $item->unit,
                'quantity_on_hand' => (float)$item->quantity_on_hand,
                'unit_price'       => (float)$item->unit_price,
                'location'         => $item->location,
            ];
        }

        $data = [];
        foreach ($categories as $cat) {
            $data[] = [
                'id'          => (int)$cat->id,
                'name'        => $cat->name,
                'description' => $cat->description,
                'items'       => $grouped[$cat->id] ?? [],
            ];
        }

        return $this->_json([
            'success' => TRUE,
            'count'   => count($data),
            'data'    => $data,
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

        // Update push token if the client sends one along with this request
        $push_token = $this->_str($this->input->get('push_token') ?? '');
        if ($push_token) {
            $this->db->where('id', $user->id)->update('auth_users', ['push_token' => $push_token]);
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
                    'public_token'   => $quote->public_token,
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

        // Notify all admin users (group_id = 1) who have a push token
        $admin_tokens = $this->db
            ->select('push_token')
            ->where('group_id', 1)
            ->where('is_active', 1)
            ->where('push_token IS NOT NULL', NULL, FALSE)
            ->get('auth_users')->result_array();

        $tokens = array_column($admin_tokens, 'push_token');
        if ($tokens) {
            send_push(
                $tokens,
                'New Quotation',
                $quote->quote_number . ' — ' . $quote->customer_name,
                ['quotation_id' => (string)$id, 'quote_number' => $quote->quote_number]
            );
        }

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

        $upload_dir = FCPATH . 'uploads/quotations/';

        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, TRUE);

        $uploaded = 0;
        for ($i = 0, $n = count($files['name']); $i < $n; $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
            $mime = function_exists('mime_content_type') ? strtolower(mime_content_type($files['tmp_name'][$i])) : strtolower($files['type'][$i]);
            if (strpos($mime, 'image/') !== 0) continue;

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

    // ── GET|POST /api/conversations ───────────────────────────────────
    public function conversations()
    {
        $method = $this->input->method();

        $user = $this->_auth();
        if (!$user) {
            return $this->_json(['error' => 'Unauthorized. Provide a valid Bearer token.'], 401);
        }

        // ── GET: list conversations ───────────────────────────────────
        if ($method === 'get') {
            $convs = $this->Chat_model->get_conversations((int)$user->id);

            $data = array_map(function($c) {
                return [
                    'id'           => (int)$c->id,
                    'updated_at'   => $c->updated_at,
                    'participants' => array_map(function($p) {
                        return [
                            'id'         => (int)$p->id,
                            'username'   => $p->username,
                            'first_name' => $p->first_name ?: NULL,
                            'last_name'  => $p->last_name  ?: NULL,
                            'full_name'  => $p->full_name,
                            'group_name' => $p->group_name,
                            'avatar_url' => !empty($p->avatar_path) ? base_url($p->avatar_path) : NULL,
                        ];
                    }, $c->participants),
                    'last_message' => $c->last_message ? [
                        'body'            => $c->last_message->body,
                        'sender_username' => $c->last_message->sender_username,
                        'created_at'      => $c->last_message->created_at,
                    ] : NULL,
                    'unread_count' => (int)$c->unread_count,
                ];
            }, $convs);

            return $this->_json(['success' => TRUE, 'count' => count($data), 'data' => $data]);
        }

        // ── POST: start or find a DM ──────────────────────────────────
        if ($method !== 'post') {
            return $this->_json(['error' => 'Method Not Allowed'], 405);
        }

        $body       = $this->_body();
        $other_id   = isset($body['user_id']) ? (int)$body['user_id'] : 0;

        if (!$other_id) {
            return $this->_json(['error' => 'user_id is required'], 422);
        }

        $other = $this->db->where('id', $other_id)->where('is_active', 1)->get('auth_users')->row();
        if (!$other) {
            return $this->_json(['error' => 'User not found.'], 404);
        }

        // Return existing DM if one already exists
        $existing = $this->Chat_model->find_dm((int)$user->id, $other_id);
        if ($existing) {
            $conv = $this->Chat_model->get_conversation($existing, (int)$user->id);
            return $this->_json(['success' => TRUE, 'created' => FALSE, 'data' => $this->_fmt_conv($conv)]);
        }

        $conv_id = $this->Chat_model->create_conversation([(int)$user->id, $other_id]);
        if (!$conv_id) {
            return $this->_json(['error' => 'Failed to create conversation.'], 500);
        }

        $conv = $this->Chat_model->get_conversation($conv_id, (int)$user->id);
        return $this->_json(['success' => TRUE, 'created' => TRUE, 'data' => $this->_fmt_conv($conv)], 201);
    }

    // ── GET /api/conversations/:id ────────────────────────────────────
    public function conversation($id = NULL)
    {
        if (!$id || !ctype_digit((string)$id)) {
            return $this->_json(['error' => 'A numeric conversation ID is required.'], 400);
        }

        $user = $this->_auth();
        if (!$user) {
            return $this->_json(['error' => 'Unauthorized. Provide a valid Bearer token.'], 401);
        }

        $conv = $this->Chat_model->get_conversation((int)$id, (int)$user->id);
        if (!$conv) {
            return $this->_json(['error' => 'Conversation not found.'], 404);
        }

        return $this->_json(['success' => TRUE, 'data' => $this->_fmt_conv($conv)]);
    }

    // ── GET|POST /api/conversations/:id/messages ──────────────────────
    public function conversation_messages($id = NULL)
    {
        if (!$id || !ctype_digit((string)$id)) {
            return $this->_json(['error' => 'A numeric conversation ID is required.'], 400);
        }

        $user = $this->_auth();
        if (!$user) {
            return $this->_json(['error' => 'Unauthorized. Provide a valid Bearer token.'], 401);
        }

        if (!$this->Chat_model->is_participant((int)$id, (int)$user->id)) {
            return $this->_json(['error' => 'Conversation not found.'], 404);
        }

        $method = $this->input->method();

        // ── GET: fetch messages ───────────────────────────────────────
        if ($method === 'get') {
            $limit     = min(100, max(1, (int)($this->input->get('limit') ?: 50)));
            $before_id = (int)$this->input->get('before_id') ?: NULL;

            $rows = $this->Chat_model->get_messages((int)$id, $limit, $before_id);
            $data = array_map([$this->Chat_model, 'format_message'], $rows);

            // Auto-mark as read on fetch
            $this->Chat_model->mark_read((int)$id, (int)$user->id);

            $read_cursors = $this->Chat_model->get_read_cursors((int)$id, (int)$user->id);

            return $this->_json(['success' => TRUE, 'count' => count($data), 'data' => $data, 'read_cursors' => $read_cursors]);
        }

        // ── POST: send a message ──────────────────────────────────────
        if ($method !== 'post') {
            return $this->_json(['error' => 'Method Not Allowed'], 405);
        }

        // Support multipart (file upload) and JSON bodies
        $has_images = !empty($_FILES['images']['name'][0]) || !empty($_FILES['image']['name']);
        $has_audio  = !empty($_FILES['audio']['name']);
        $has_files  = $has_images || $has_audio;
        if ($has_files) {
            $text     = $this->_str($_POST['body']     ?? '');
            $quote_id = isset($_POST['quote_id']) ? (int)$_POST['quote_id'] : NULL;
        } else {
            $body     = $this->_body();
            $text     = $this->_str($body['body']     ?? '');
            $quote_id = isset($body['quote_id']) ? (int)$body['quote_id'] : NULL;
        }

        // Upload images
        $image_paths = [];
        if ($has_images) {
            $image_paths = $this->_upload_chat_images((int)$id);
            if ($image_paths === FALSE) {
                return $this->_json(['error' => 'One or more files are invalid. Any image type is allowed — max 5 MB each.'], 422);
            }
        }

        // Upload voice note
        $audio_path = NULL;
        if ($has_audio) {
            $audio_path = $this->_upload_chat_audio();
            if ($audio_path === FALSE) {
                return $this->_json(['error' => 'Audio upload failed. Please try again.'], 422);
            }
        }

        if (!$text && empty($image_paths) && !$audio_path) {
            return $this->_json(['error' => 'body, image, or audio is required'], 422);
        }

        if ($quote_id) {
            $q = $this->db->where('id', $quote_id)->get('quotations')->row();
            if (!$q) {
                return $this->_json(['error' => 'Quotation not found.'], 404);
            }
        }

        // Create one message per image; text + quote attach to the first message only
        $created = [];
        if (!empty($image_paths)) {
            $group_id = (count($image_paths) > 1) ? bin2hex(random_bytes(8)) : NULL;
            foreach ($image_paths as $i => $path) {
                $msg_text = ($i === 0) ? $text : '';
                $msg_qid  = ($i === 0) ? $quote_id : NULL;
                $msg_id   = $this->Chat_model->send_message((int)$id, (int)$user->id, $msg_text, $msg_qid, $path, $group_id);
                $created[] = $this->Chat_model->format_message($this->Chat_model->get_message($msg_id));
            }
        } elseif ($audio_path) {
            $msg_id  = $this->Chat_model->send_message((int)$id, (int)$user->id, $text, $quote_id, NULL, NULL, $audio_path);
            $created[] = $this->Chat_model->format_message($this->Chat_model->get_message($msg_id));
        } else {
            $msg_id  = $this->Chat_model->send_message((int)$id, (int)$user->id, $text, $quote_id, NULL);
            $created[] = $this->Chat_model->format_message($this->Chat_model->get_message($msg_id));
        }

        // Push notification — receivers only, one notification covering all messages
        $tokens = $this->Chat_model->get_recipient_tokens((int)$id, (int)$user->id);
        $sender_token = $user->push_token ?? '';
        if ($sender_token) {
            $tokens = array_values(array_filter($tokens, fn($t) => $t !== $sender_token));
        }

        if ($tokens) {
            $img_count = count($image_paths);
            if ($audio_path) {
                $preview = '🎤 Voice note';
            } elseif ($img_count > 1) {
                $preview = "📷 {$img_count} Photos";
            } elseif ($img_count === 1) {
                $preview = $text ? '📷 ' . (mb_strlen($text) > 60 ? mb_substr($text, 0, 57) . '…' : $text) : '📷 Photo';
            } else {
                $preview = mb_strlen($text) > 80 ? mb_substr($text, 0, 77) . '…' : $text;
            }

            $sender_name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->username;
            send_push(
                $tokens,
                $sender_name,
                $preview,
                [
                    'type'            => 'chat_message',
                    'conversation_id' => (string)$id,
                    'message_id'      => (string)($created[0]['id'] ?? ''),
                    'sender_id'       => (string)$user->id,
                ]
            );
        }

        // Return single object when one message, array when multiple
        return $this->_json([
            'success' => TRUE,
            'count'   => count($created),
            'data'    => count($created) === 1 ? $created[0] : $created,
        ], 201);
    }

    // ── PUT /api/conversations/:id/read ───────────────────────────────
    public function conversation_read($id = NULL)
    {
        if ($this->input->method() !== 'put') {
            return $this->_json(['error' => 'Method Not Allowed'], 405);
        }

        if (!$id || !ctype_digit((string)$id)) {
            return $this->_json(['error' => 'A numeric conversation ID is required.'], 400);
        }

        $user = $this->_auth();
        if (!$user) {
            return $this->_json(['error' => 'Unauthorized. Provide a valid Bearer token.'], 401);
        }

        if (!$this->Chat_model->is_participant((int)$id, (int)$user->id)) {
            return $this->_json(['error' => 'Conversation not found.'], 404);
        }

        $this->Chat_model->mark_read((int)$id, (int)$user->id);

        return $this->_json(['success' => TRUE]);
    }

    // ── Upload one or more chat images ───────────────────────────────
    // Accepts images[] (multiple) or legacy image (single).
    // Returns array of saved paths, FALSE if any file is invalid, [] if none supplied.
    private function _upload_chat_images($conversation_id)
    {
        // Normalise both images[] and legacy image into a flat list of file entries
        $files = [];
        if (!empty($_FILES['images']['name'])) {
            // Multiple-file input: images[]
            $count = count($_FILES['images']['name']);
            for ($i = 0; $i < $count; $i++) {
                $files[] = [
                    'name'     => $_FILES['images']['name'][$i],
                    'tmp_name' => $_FILES['images']['tmp_name'][$i],
                    'error'    => $_FILES['images']['error'][$i],
                    'size'     => $_FILES['images']['size'][$i],
                ];
            }
        } elseif (!empty($_FILES['image']['name'])) {
            // Legacy single-file input: image
            $files[] = [
                'name'     => $_FILES['image']['name'],
                'tmp_name' => $_FILES['image']['tmp_name'],
                'error'    => $_FILES['image']['error'],
                'size'     => $_FILES['image']['size'],
            ];
        }

        if (empty($files)) return [];

        $upload_dir = FCPATH . 'uploads/chat/';

        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, TRUE);
        @chmod($upload_dir, 0777);

        $paths = [];
        foreach ($files as $file) {
            if ($file['error'] !== UPLOAD_ERR_OK) return FALSE;
            if ($file['size'] > 5 * 1024 * 1024)  return FALSE;

            // Accept any image/* MIME type
            if (function_exists('mime_content_type')) {
                $mime = strtolower(mime_content_type($file['tmp_name']));
                if (strpos($mime, 'image/') !== 0) return FALSE;
            }

            $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) ?: 'jpg';
            $save_ext = $ext;
            $fname    = 'chat_' . $conversation_id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $save_ext;
            $dest     = $upload_dir . $fname;

            if (!move_uploaded_file($file['tmp_name'], $dest)) return FALSE;

            // Compress / resize in-place
            $this->_compress_image($dest, $save_ext);

            $paths[] = 'uploads/chat/' . $fname;
        }

        return $paths;
    }

    private function _upload_chat_audio()
    {
        $file = $_FILES['audio'] ?? [];
        if (empty($file['name']) || $file['error'] !== UPLOAD_ERR_OK) return FALSE;
        // No size limit — file is compressed after upload

        $upload_dir = FCPATH . 'uploads/audio/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, TRUE);
        @chmod($upload_dir, 0777);

        $ext   = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) ?: 'mp4';
        $fname = 'voice_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dest  = $upload_dir . $fname;

        if (!move_uploaded_file($file['tmp_name'], $dest)) return FALSE;

        // Compress with FFmpeg if available
        $this->_compress_audio($dest);

        return 'uploads/audio/' . $fname;
    }

    private function _compress_audio($path)
    {
        $ffmpeg = trim((string)@shell_exec('which ffmpeg 2>/dev/null'))
               ?: trim((string)@shell_exec('where ffmpeg 2>/dev/null'));
        if (!$ffmpeg || !file_exists($path)) return;

        $out = $path . '_tmp.m4a';
        // Mono, 22 050 Hz, 32 kbps AAC — good quality for voice, tiny file size
        $cmd = $ffmpeg
             . ' -y -i ' . escapeshellarg($path)
             . ' -vn -ac 1 -ar 22050 -b:a 32k -c:a aac '
             . escapeshellarg($out)
             . ' 2>/dev/null';
        @exec($cmd, $output, $code);

        if ($code === 0 && file_exists($out)) {
            // Only replace if ffmpeg actually made it smaller
            if (filesize($out) < filesize($path)) {
                rename($out, $path);
            } else {
                @unlink($out);
            }
        } else {
            @unlink($out);
        }
    }

    // ── Compress a saved image in-place (GD) ──────────────────────────
    // Max 1600px on the longest side, JPEG quality 75, PNG level 7.
    private function _compress_image($path, $ext)
    {
        if (!function_exists('imagecreatefromjpeg')) return; // GD not available

        $info = @getimagesize($path);
        if (!$info) return;

        switch ($info[2]) {
            case IMAGETYPE_JPEG: $src = @imagecreatefromjpeg($path); break;
            case IMAGETYPE_PNG:  $src = @imagecreatefrompng($path);  break;
            case IMAGETYPE_WEBP: $src = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : NULL; break;
            case IMAGETYPE_GIF:  $src = @imagecreatefromgif($path);  break;
            default: return;
        }

        if (!$src) return;

        $orig_w = imagesx($src);
        $orig_h = imagesy($src);
        $max    = 1600;

        if ($orig_w > $max || $orig_h > $max) {
            $ratio = $orig_w > $orig_h ? $max / $orig_w : $max / $orig_h;
            $new_w = (int)round($orig_w * $ratio);
            $new_h = (int)round($orig_h * $ratio);
        } else {
            $new_w = $orig_w;
            $new_h = $orig_h;
        }

        $dst = imagecreatetruecolor($new_w, $new_h);

        // Preserve transparency for PNG
        if ($ext === 'png') {
            imagealphablending($dst, FALSE);
            imagesavealpha($dst, TRUE);
            $transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);
            imagefilledrectangle($dst, 0, 0, $new_w, $new_h, $transparent);
        }

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $new_w, $new_h, $orig_w, $orig_h);

        if ($ext === 'png') {
            imagepng($dst, $path, 7); // 0–9, higher = smaller file
        } else {
            imagejpeg($dst, $path, 75); // 0–100, lower = smaller file
        }

        imagedestroy($src);
        imagedestroy($dst);
    }

    // ── Format a conversation row for API output ──────────────────────
    private function _fmt_conv($c)
    {
        return [
            'id'           => (int)$c->id,
            'updated_at'   => $c->updated_at,
            'participants' => array_map(function($p) {
                return [
                    'id'         => (int)$p->id,
                    'username'   => $p->username,
                    'first_name' => $p->first_name ?: NULL,
                    'last_name'  => $p->last_name  ?: NULL,
                    'full_name'  => $p->full_name,
                    'group_name' => $p->group_name,
                    'avatar_url' => !empty($p->avatar_path) ? base_url($p->avatar_path) : NULL,
                ];
            }, $c->participants),
            'unread_count' => (int)$c->unread_count,
        ];
    }

    // ── Bearer token auth ─────────────────────────────────────────────
    // ── GET /api/profile ─────────────────────────────────────────────
    public function profile()
    {
        $user = $this->_auth();
        if (!$user) {
            return $this->_json(['error' => 'Unauthorized.'], 401);
        }

        $row = $this->db->query("
            SELECT u.id, u.username, u.first_name, u.last_name, u.email, u.avatar_path,
                   g.name AS group_name, c.name AS company_name
            FROM auth_users u
            LEFT JOIN user_groups g ON g.id = u.group_id
            LEFT JOIN companies   c ON c.id = u.company_id
            WHERE u.id = ?
        ", [(int)$user->id])->row();

        if (!$row) return $this->_json(['error' => 'User not found.'], 404);

        return $this->_json([
            'success' => TRUE,
            'data'    => [
                'id'           => (int)$row->id,
                'username'     => $row->username,
                'first_name'   => $row->first_name ?: NULL,
                'last_name'    => $row->last_name  ?: NULL,
                'full_name'    => trim(($row->first_name ?? '') . ' ' . ($row->last_name ?? '')) ?: $row->username,
                'email'        => $row->email,
                'group_name'   => $row->group_name,
                'company_name' => $row->company_name,
                'avatar_url'   => !empty($row->avatar_path) ? base_url($row->avatar_path) : NULL,
            ],
        ]);
    }

    // ── POST /api/profile/avatar ──────────────────────────────────────
    public function profile_avatar()
    {
        if ($this->input->method() !== 'post') {
            return $this->_json(['error' => 'Method Not Allowed'], 405);
        }

        $user = $this->_auth();
        if (!$user) {
            return $this->_json(['error' => 'Unauthorized. Provide a valid Bearer token.'], 401);
        }

        $path = $this->_upload_avatar();
        if (!$path) {
            return $this->_json(['error' => 'No valid image provided. Accepted: jpg, png, webp (max 5 MB).'], 422);
        }

        // Delete old avatar
        if (!empty($user->avatar_path) && file_exists(FCPATH . $user->avatar_path)) {
            @unlink(FCPATH . $user->avatar_path);
        }

        $this->load->model('User_model');
        $this->User_model->update_avatar($user->id, $path);

        return $this->_json([
            'success'    => TRUE,
            'avatar_url' => base_url($path),
        ]);
    }

    // ── Upload & square-crop an avatar image ─────────────────────────
    private function _upload_avatar()
    {
        $file = $_FILES['avatar'] ?? [];
        if (empty($file['name']) || $file['error'] !== UPLOAD_ERR_OK) return FALSE;
        if ($file['size'] > 5 * 1024 * 1024) return FALSE;

        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $mime    = function_exists('mime_content_type') ? mime_content_type($file['tmp_name']) : '';
        if (!isset($allowed[$mime])) return FALSE;

        $upload_dir = FCPATH . 'uploads/avatars/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, TRUE);
        @chmod($upload_dir, 0777);

        $fname = 'avatar_' . time() . '_' . bin2hex(random_bytes(4)) . '.jpg';
        $dest  = $upload_dir . $fname;

        if (!move_uploaded_file($file['tmp_name'], $dest)) return FALSE;

        $this->_compress_avatar($dest, $mime);

        return 'uploads/avatars/' . $fname;
    }

    // ── Centre-crop to square, resize to 400 px, save as JPEG ────────
    private function _compress_avatar($path, $mime)
    {
        if (!function_exists('imagecreatefromjpeg')) return;
        $info = @getimagesize($path);
        if (!$info) return;

        switch ($mime) {
            case 'image/jpeg': $src = @imagecreatefromjpeg($path); break;
            case 'image/png':  $src = @imagecreatefrompng($path);  break;
            case 'image/webp': $src = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : NULL; break;
            default: return;
        }
        if (!$src) return;

        $ow = imagesx($src);
        $oh = imagesy($src);
        $side = min($ow, $oh);
        $cx   = (int)(($ow - $side) / 2);
        $cy   = (int)(($oh - $side) / 2);
        $size = min($side, 400);

        $dst = imagecreatetruecolor($size, $size);
        imagecopyresampled($dst, $src, 0, 0, $cx, $cy, $size, $size, $side, $side);
        imagedestroy($src);

        imagejpeg($dst, $path, 82);
        imagedestroy($dst);
    }

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
