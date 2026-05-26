<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Quotation_types extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->require_group(['Admin', 'Manager']);
        $this->load->model('Quotation_type_model');
        $this->load->library('form_validation');
    }

    // ── List ──────────────────────────────────────────────────────────
    public function index()
    {
        $types = $this->Quotation_type_model->get_all();

        // Attach in-use count to each type
        foreach ($types as $t) {
            $t->in_use = $this->Quotation_type_model->count_in_use($t->id);
        }

        $data = [
            'title' => 'Quotation Types',
            'user'  => $this->current_user,
            'types' => $types,
        ];
        $this->load->view('layouts/header', $data);
        $this->load->view('quotation_types/index', $data);
        $this->load->view('layouts/footer');
    }

    // ── Create (GET) ──────────────────────────────────────────────────
    public function create()
    {
        $data = [
            'title'   => 'Add Quotation Type',
            'user'    => $this->current_user,
            'is_edit' => FALSE,
            'record'  => NULL,
        ];
        $this->load->view('layouts/header', $data);
        $this->load->view('quotation_types/form', $data);
        $this->load->view('layouts/footer');
    }

    // ── Store (POST) ──────────────────────────────────────────────────
    public function store()
    {
        $this->form_validation->set_rules('name', 'Type Name', 'required|trim|max_length[100]');

        if ($this->form_validation->run() === FALSE) {
            $this->create();
            return;
        }

        $name = $this->input->post('name', TRUE);
        if ($this->Quotation_type_model->name_exists($name)) {
            $this->session->set_flashdata('error', 'A type with that name already exists.');
            redirect('quotation_types/create');
            return;
        }

        $this->Quotation_type_model->create([
            'name'        => $name,
            'description' => $this->input->post('description', TRUE),
            'sort_order'  => (int)$this->input->post('sort_order') ?: 0,
            'is_active'   => 1,
        ]);

        $this->session->set_flashdata('success', 'Quotation type added.');
        redirect('quotation_types');
    }

    // ── Edit (GET) ────────────────────────────────────────────────────
    public function edit($id = NULL)
    {
        $record = $this->_get_or_404($id);
        $data   = [
            'title'   => 'Edit Type: ' . $record->name,
            'user'    => $this->current_user,
            'is_edit' => TRUE,
            'record'  => $record,
        ];
        $this->load->view('layouts/header', $data);
        $this->load->view('quotation_types/form', $data);
        $this->load->view('layouts/footer');
    }

    // ── Update (POST) ─────────────────────────────────────────────────
    public function update($id = NULL)
    {
        $this->_get_or_404($id);
        $this->form_validation->set_rules('name', 'Type Name', 'required|trim|max_length[100]');

        if ($this->form_validation->run() === FALSE) {
            $this->edit($id);
            return;
        }

        $name = $this->input->post('name', TRUE);
        if ($this->Quotation_type_model->name_exists($name, $id)) {
            $this->session->set_flashdata('error', 'A type with that name already exists.');
            redirect('quotation_types/edit/' . $id);
            return;
        }

        $this->Quotation_type_model->update($id, [
            'name'        => $name,
            'description' => $this->input->post('description', TRUE),
            'sort_order'  => (int)$this->input->post('sort_order') ?: 0,
            'is_active'   => $this->input->post('is_active') ? 1 : 0,
        ]);

        $this->session->set_flashdata('success', 'Quotation type updated.');
        redirect('quotation_types');
    }

    // ── Delete (POST — Admin only) ─────────────────────────────────────
    public function delete($id = NULL)
    {
        $this->require_group(['Admin']);
        $this->_get_or_404($id);

        if (!$this->Quotation_type_model->delete($id)) {
            $this->session->set_flashdata('error', 'Cannot delete — this type is used by existing quotations.');
            redirect('quotation_types');
            return;
        }

        $this->session->set_flashdata('success', 'Type deleted.');
        redirect('quotation_types');
    }

    // ── Helpers ───────────────────────────────────────────────────────
    private function _get_or_404($id)
    {
        $record = $this->Quotation_type_model->get_by_id((int)$id);
        if (!$record) {
            $this->session->set_flashdata('error', 'Type not found.');
            redirect('quotation_types');
        }
        return $record;
    }
}
