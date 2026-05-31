<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Companies extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->require_group(['Admin']);
        $this->load->model('Company_model');
        $this->load->library('form_validation');
    }

    public function index()
    {
        $companies = $this->Company_model->get_all();

        foreach ($companies as $c) {
            $c->user_count = $this->Company_model->user_count($c->id);
        }

        $data = [
            'title'     => 'Companies',
            'user'      => $this->current_user,
            'companies' => $companies,
        ];
        $this->load->view('layouts/header', $data);
        $this->load->view('companies/index', $data);
        $this->load->view('layouts/footer');
    }

    public function create()
    {
        $data = [
            'title'     => 'Add Company',
            'user'      => $this->current_user,
            'is_edit'   => FALSE,
            'record'    => NULL,
        ];
        $this->load->view('layouts/header', $data);
        $this->load->view('companies/form', $data);
        $this->load->view('layouts/footer');
    }

    public function store()
    {
        $this->_set_rules();

        if ($this->form_validation->run() === FALSE) {
            $this->create();
            return;
        }

        $this->Company_model->create([
            'name'      => $this->input->post('name',    TRUE),
            'address'   => $this->input->post('address', TRUE),
            'phone'     => $this->input->post('phone',   TRUE),
            'email'     => $this->input->post('email',   TRUE),
            'is_active' => $this->input->post('is_active') ? 1 : 0,
        ]);

        $this->session->set_flashdata('success', 'Company created successfully.');
        redirect('companies');
    }

    public function edit($id = NULL)
    {
        $record = $this->_get_or_404($id);
        $data   = [
            'title'   => 'Edit ' . $record->name,
            'user'    => $this->current_user,
            'is_edit' => TRUE,
            'record'  => $record,
        ];
        $this->load->view('layouts/header', $data);
        $this->load->view('companies/form', $data);
        $this->load->view('layouts/footer');
    }

    public function update($id = NULL)
    {
        $this->_get_or_404($id);
        $this->_set_rules();

        if ($this->form_validation->run() === FALSE) {
            $this->edit($id);
            return;
        }

        $this->Company_model->update($id, [
            'name'      => $this->input->post('name',    TRUE),
            'address'   => $this->input->post('address', TRUE),
            'phone'     => $this->input->post('phone',   TRUE),
            'email'     => $this->input->post('email',   TRUE),
            'is_active' => $this->input->post('is_active') ? 1 : 0,
        ]);

        $this->session->set_flashdata('success', 'Company updated successfully.');
        redirect('companies');
    }

    public function delete($id = NULL)
    {
        $this->_get_or_404($id);

        if ($this->Company_model->user_count($id) > 0) {
            $this->session->set_flashdata('error', 'Cannot delete — users are linked to this company.');
            redirect('companies');
            return;
        }

        $this->Company_model->delete($id);
        $this->session->set_flashdata('success', 'Company deleted.');
        redirect('companies');
    }

    private function _get_or_404($id)
    {
        $record = $this->Company_model->get_by_id((int)$id);
        if (!$record) {
            $this->session->set_flashdata('error', 'Company not found.');
            redirect('companies');
        }
        return $record;
    }

    private function _set_rules()
    {
        $this->form_validation->set_rules('name',  'Company Name', 'required|trim|max_length[150]');
        $this->form_validation->set_rules('email', 'Email',        'trim|valid_email|max_length[150]');
    }
}
