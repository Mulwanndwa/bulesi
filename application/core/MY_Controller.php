<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {

    protected $current_user = [];

    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('user_id')) {
            redirect('auth');
        }
        $this->current_user = [
            'id'         => $this->session->userdata('user_id'),
            'username'   => $this->session->userdata('username'),
            'email'      => $this->session->userdata('email'),
            'group_id'   => $this->session->userdata('group_id'),
            'group_name' => $this->session->userdata('group_name'),
        ];

        // Refresh group info into session if missing (e.g. session predates group support)
        if (empty($this->current_user['group_name'])) {
            $this->load->model('Auth_model');
            $fresh = $this->db
                ->select('u.group_id, g.name AS group_name')
                ->from('auth_users u')
                ->join('user_groups g', 'g.id = u.group_id', 'left')
                ->where('u.id', $this->current_user['id'])
                ->get()->row();

            if ($fresh) {
                $this->session->set_userdata([
                    'group_id'   => $fresh->group_id,
                    'group_name' => $fresh->group_name,
                ]);
                $this->current_user['group_id']   = $fresh->group_id;
                $this->current_user['group_name'] = $fresh->group_name;
            }
        }
    }

    /**
     * Restrict a page to specific groups.
     * Usage: $this->require_group(['Admin', 'Manager']);
     */
    protected function require_group(array $allowed)
    {
        if (!in_array($this->current_user['group_name'], $allowed)) {
            $this->session->set_flashdata('error', 'You do not have permission to access that page.');
            redirect('dashboard');
        }
    }

    protected function is_group(string $group): bool
    {
        return $this->current_user['group_name'] === $group;
    }
}
