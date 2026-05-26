<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Quotation_model');
    }

    public function index()
    {
        $data = [
            'title'  => 'Dashboard',
            'user'   => $this->current_user,
            'stats'  => $this->Quotation_model->get_stats(),
            'recent' => $this->Quotation_model->get_recent(8),
        ];
        $this->load->view('layouts/header', $data);
        $this->load->view('dashboard/index', $data);
        $this->load->view('layouts/footer');
    }
}
