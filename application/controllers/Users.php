<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->require_group(['Admin']);
        $this->load->model('User_model');
        $this->load->model('Company_model');
        $this->load->library('form_validation');
    }

    // ── List ──────────────────────────────────────────────────────────
    public function index()
    {
        $data = [
            'title' => 'Users',
            'user'  => $this->current_user,
            'users' => $this->User_model->get_all(),
        ];
        $this->load->view('layouts/header', $data);
        $this->load->view('users/index', $data);
        $this->load->view('layouts/footer');
    }

    // ── Create (GET) ──────────────────────────────────────────────────
    public function create()
    {
        $data = [
            'title'     => 'Add User',
            'user'      => $this->current_user,
            'is_edit'   => FALSE,
            'record'    => NULL,
            'groups'    => $this->User_model->get_groups(),
            'companies' => $this->Company_model->get_active(),
        ];
        $this->load->view('layouts/header', $data);
        $this->load->view('users/form', $data);
        $this->load->view('layouts/footer');
    }

    // ── Store (POST) ──────────────────────────────────────────────────
    public function store()
    {
        $this->_set_rules_create();

        if ($this->form_validation->run() === FALSE) {
            $this->create();
            return;
        }

        $username = $this->input->post('username', TRUE);
        $email    = $this->input->post('email',    TRUE);

        if ($this->User_model->username_exists($username)) {
            $this->session->set_flashdata('error', 'That username is already taken.');
            redirect('users/create');
            return;
        }
        if ($this->User_model->email_exists($email)) {
            $this->session->set_flashdata('error', 'That email is already registered.');
            redirect('users/create');
            return;
        }

        $company_id = (int)$this->input->post('company_id') ?: NULL;
        $new_id = $this->User_model->create([
            'username'   => $username,
            'first_name' => $this->input->post('first_name', TRUE),
            'last_name'  => $this->input->post('last_name',  TRUE),
            'email'      => $email,
            'group_id'   => (int)$this->input->post('group_id'),
            'company_id' => $company_id,
            'password'   => $this->input->post('password'),
            'is_active'  => $this->input->post('is_active') ? 1 : 0,
        ]);

        $avatar = $this->_upload_avatar();
        if ($avatar) {
            $this->User_model->update_avatar($new_id, $avatar);
        }

        $this->session->set_flashdata('success', 'User created successfully.');
        redirect('users');
    }

    // ── Edit (GET) ────────────────────────────────────────────────────
    public function edit($id = NULL)
    {
        $record = $this->_get_or_404($id);
        $data   = [
            'title'     => 'Edit User',
            'user'      => $this->current_user,
            'is_edit'   => TRUE,
            'record'    => $record,
            'groups'    => $this->User_model->get_groups(),
            'companies' => $this->Company_model->get_active(),
        ];
        $this->load->view('layouts/header', $data);
        $this->load->view('users/form', $data);
        $this->load->view('layouts/footer');
    }

    // ── Update (POST) ─────────────────────────────────────────────────
    public function update($id = NULL)
    {
        $record = $this->_get_or_404($id);
        $this->_set_rules_edit();

        if ($this->form_validation->run() === FALSE) {
            $this->edit($id);
            return;
        }

        $username = $this->input->post('username', TRUE);
        $email    = $this->input->post('email',    TRUE);

        if ($this->User_model->username_exists($username, $id)) {
            $this->session->set_flashdata('error', 'That username is already taken.');
            redirect('users/edit/' . $id);
            return;
        }
        if ($this->User_model->email_exists($email, $id)) {
            $this->session->set_flashdata('error', 'That email is already registered.');
            redirect('users/edit/' . $id);
            return;
        }

        $data = [
            'username'   => $username,
            'first_name' => $this->input->post('first_name', TRUE),
            'last_name'  => $this->input->post('last_name',  TRUE),
            'email'      => $email,
            'group_id'   => (int)$this->input->post('group_id'),
            'company_id' => (int)$this->input->post('company_id') ?: NULL,
            'password'   => $this->input->post('password'),
        ];

        // Prevent admin from disabling or changing their own group
        if ((int)$id !== (int)$this->current_user['id']) {
            $data['is_active'] = $this->input->post('is_active') ? 1 : 0;
        }

        $this->User_model->update($id, $data);

        // Handle avatar
        if ($this->input->post('remove_avatar') && !empty($record->avatar_path)) {
            if (file_exists(FCPATH . $record->avatar_path)) @unlink(FCPATH . $record->avatar_path);
            $this->User_model->update_avatar($id, NULL);
        } else {
            $avatar = $this->_upload_avatar();
            if ($avatar) {
                if (!empty($record->avatar_path) && file_exists(FCPATH . $record->avatar_path)) {
                    @unlink(FCPATH . $record->avatar_path);
                }
                $this->User_model->update_avatar($id, $avatar);
            }
        }

        $this->session->set_flashdata('success', 'User updated successfully.');
        redirect('users');
    }

    // ── Toggle active (POST) ──────────────────────────────────────────
    public function toggle($id = NULL)
    {
        $record = $this->_get_or_404($id);

        if ((int)$id === (int)$this->current_user['id']) {
            $this->session->set_flashdata('error', 'You cannot deactivate your own account.');
            redirect('users');
            return;
        }

        $this->User_model->toggle_active($id, !$record->is_active);
        $this->session->set_flashdata('success', 'User ' . ($record->is_active ? 'deactivated' : 'activated') . '.');
        redirect('users');
    }

    // ── Delete (POST) ─────────────────────────────────────────────────
    public function delete($id = NULL)
    {
        $this->_get_or_404($id);

        if ((int)$id === (int)$this->current_user['id']) {
            $this->session->set_flashdata('error', 'You cannot delete your own account.');
            redirect('users');
            return;
        }

        $this->User_model->delete($id);
        $this->session->set_flashdata('success', 'User deleted.');
        redirect('users');
    }

    // ── Generate API token (POST) ─────────────────────────────────────
    public function generate_token($id = NULL)
    {
        $this->_get_or_404($id);
        $token = bin2hex(random_bytes(32)); // 64-char hex token
        $this->User_model->set_api_token($id, $token);
        $this->session->set_flashdata('api_token_' . $id, $token);
        $this->session->set_flashdata('success', 'New API key generated.');
        redirect('users');
    }

    // ── Revoke API token (POST) ───────────────────────────────────────
    public function revoke_token($id = NULL)
    {
        $this->_get_or_404($id);
        $this->User_model->revoke_api_token($id);
        $this->session->set_flashdata('success', 'API key revoked.');
        redirect('users');
    }

    // ── Helpers ───────────────────────────────────────────────────────
    private function _get_or_404($id)
    {
        $record = $this->User_model->get_by_id((int)$id);
        if (!$record) {
            $this->session->set_flashdata('error', 'User not found.');
            redirect('users');
        }
        return $record;
    }

    private function _set_rules_create()
    {
        $this->form_validation->set_rules('first_name',       'First Name',       'required|trim|max_length[75]');
        $this->form_validation->set_rules('last_name',        'Last Name',        'required|trim|max_length[75]');
        $this->form_validation->set_rules('username',         'Username',         'required|trim|min_length[3]|max_length[100]');
        $this->form_validation->set_rules('email',            'Email',            'required|trim|valid_email|max_length[150]');
        $this->form_validation->set_rules('group_id',         'User Group',       'required|integer');
        $this->form_validation->set_rules('company_id',       'Company',          'required|integer');
        $this->form_validation->set_rules('password',         'Password',         'required|min_length[6]');
        $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|matches[password]');
    }

    private function _upload_avatar()
    {
        $file = $_FILES['avatar'] ?? [];
        if (empty($file['name']) || $file['error'] !== UPLOAD_ERR_OK) return NULL;
        if ($file['size'] > 5 * 1024 * 1024) return NULL;

        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $mime    = function_exists('mime_content_type') ? mime_content_type($file['tmp_name']) : '';
        if (!isset($allowed[$mime])) return NULL;

        $dir = FCPATH . 'uploads/avatars/';
        if (!is_dir($dir)) mkdir($dir, 0777, TRUE);
        @chmod($dir, 0777);

        $fname = 'avatar_' . time() . '_' . bin2hex(random_bytes(4)) . '.jpg';
        $dest  = $dir . $fname;

        if (!move_uploaded_file($file['tmp_name'], $dest)) return NULL;

        // Centre-crop to 400 px square
        if (function_exists('imagecreatefromjpeg')) {
            $info = @getimagesize($dest);
            switch ($info[2] ?? 0) {
                case IMAGETYPE_JPEG: $src = @imagecreatefromjpeg($dest); break;
                case IMAGETYPE_PNG:  $src = @imagecreatefrompng($dest);  break;
                case IMAGETYPE_WEBP: $src = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($dest) : NULL; break;
                default: $src = NULL;
            }
            if ($src) {
                $ow = imagesx($src); $oh = imagesy($src);
                $side = min($ow, $oh); $size = min($side, 400);
                $cx = (int)(($ow - $side) / 2); $cy = (int)(($oh - $side) / 2);
                $dst = imagecreatetruecolor($size, $size);
                imagecopyresampled($dst, $src, 0, 0, $cx, $cy, $size, $size, $side, $side);
                imagedestroy($src);
                imagejpeg($dst, $dest, 82);
                imagedestroy($dst);
            }
        }

        return 'uploads/avatars/' . $fname;
    }

    private function _set_rules_edit()
    {
        $this->form_validation->set_rules('first_name', 'First Name', 'required|trim|max_length[75]');
        $this->form_validation->set_rules('last_name',  'Last Name',  'required|trim|max_length[75]');
        $this->form_validation->set_rules('username',   'Username',   'required|trim|min_length[3]|max_length[100]');
        $this->form_validation->set_rules('email',      'Email',      'required|trim|valid_email|max_length[150]');
        $this->form_validation->set_rules('group_id',   'User Group', 'required|integer');
        $this->form_validation->set_rules('company_id', 'Company',    'required|integer');

        if ($this->input->post('password')) {
            $this->form_validation->set_rules('password',         'Password',         'min_length[6]');
            $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'matches[password]');
        }
    }
}
