<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Whatsapp extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->require_group(['Admin', 'Manager']);
    }

    public function index()
    {
        $data = [
            'title' => 'WhatsApp Integration',
            'user'  => $this->current_user,
        ];

        $this->load->view('layouts/header', $data);
        $this->load->view('whatsapp/index', $data);
        $this->load->view('layouts/footer');
    }

    public function save_settings()
    {
        $this->require_group(['Admin', 'Manager']);

        // Placeholder – persist to a config/settings table when ready
        $this->session->set_flashdata('success', 'WhatsApp settings saved successfully.');
        redirect('whatsapp');
    }

    public function send_test()
    {
        $this->require_group(['Admin', 'Manager']);

        // Placeholder – call the WhatsApp Cloud API when credentials are stored
        $this->session->set_flashdata('success', 'Test message queued for delivery.');
        redirect('whatsapp');
    }
}
