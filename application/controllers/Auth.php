<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Auth_model');
        $this->load->library(['form_validation', 'session']);
        $this->load->helper(['url', 'form']);
    }

    public function index()
    {
        if ($this->session->userdata('user_id')) {
            redirect('dashboard');
        }
        $this->load->view('auth/login');
    }

    public function do_login()
    {
        $this->form_validation->set_rules('login', 'Email or Username', 'required|trim');
        $this->form_validation->set_rules('password', 'Password', 'required');

        if ($this->form_validation->run() === FALSE) {
            $this->load->view('auth/login');
            return;
        }

        $login    = $this->input->post('login', TRUE);
        $password = $this->input->post('password');

        // Try email first, then username
        $user = filter_var($login, FILTER_VALIDATE_EMAIL)
            ? $this->Auth_model->get_user_by_email($login)
            : $this->Auth_model->get_user_by_username($login);

        if ($user && password_verify($password, $user->password)) {
            $this->Auth_model->update_last_login($user->id);

            $api_token = $user->api_token;
            if (!$api_token) {
                $api_token = bin2hex(random_bytes(32));
                $this->db->where('id', $user->id)->update('auth_users', ['api_token' => $api_token]);
            }

            $this->session->set_userdata([
                'user_id'    => $user->id,
                'username'   => $user->username,
                'email'      => $user->email,
                'group_id'   => $user->group_id,
                'group_name' => $user->group_name,
                'api_token'  => $api_token,
            ]);
            redirect((int)$user->group_id === 5 ? 'pos' : 'dashboard');
        }

        $this->session->set_flashdata('error', 'Invalid credentials. Please try again.');
        redirect('auth');
    }

    public function logout()
    {
        $this->session->sess_destroy();
        redirect('auth');
    }
}
