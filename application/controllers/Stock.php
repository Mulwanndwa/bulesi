<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Stock extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Stock_model');
        $this->load->library('form_validation');
    }

    // ── List ──────────────────────────────────────────────────────────
    public function index($category_id = NULL)
    {
        $data = [
            'title'       => 'Stock Management',
            'user'        => $this->current_user,
            'stats'       => $this->Stock_model->get_stats(),
            'categories'  => $this->Stock_model->get_categories(),
            'items'       => $this->Stock_model->get_all($category_id),
            'category_id' => $category_id,
        ];
        $this->load->view('layouts/header', $data);
        $this->load->view('stock/index', $data);
        $this->load->view('layouts/footer');
    }

    // ── Create (GET) ──────────────────────────────────────────────────
    public function create()
    {
        $data = [
            'title'      => 'Add Stock Item',
            'user'       => $this->current_user,
            'is_edit'    => FALSE,
            'record'     => NULL,
            'categories' => $this->Stock_model->get_categories(),
        ];
        $this->load->view('layouts/header', $data);
        $this->load->view('stock/form', $data);
        $this->load->view('layouts/footer');
    }

    // ── Store (POST) ──────────────────────────────────────────────────
    public function store()
    {
        $this->_set_rules();

        if ($this->form_validation->run() === FALSE) {
            $this->create();
            return;
        }

        $code = trim($this->input->post('code', TRUE));
        if ($code && $this->Stock_model->code_exists($code)) {
            $this->session->set_flashdata('error', 'Item code already exists.');
            redirect('stock/create');
            return;
        }

        $id = $this->Stock_model->create([
            'category_id'      => (int)$this->input->post('category_id'),
            'code'             => $code ?: NULL,
            'name'             => $this->input->post('name',        TRUE),
            'description'      => $this->input->post('description', TRUE),
            'unit'             => $this->input->post('unit',        TRUE),
            'quantity_on_hand' => max(0, (float)$this->input->post('quantity_on_hand')),
            'reorder_level'    => max(0, (float)$this->input->post('reorder_level')),
            'unit_cost'        => max(0, (float)$this->input->post('unit_cost')),
            'unit_price'       => max(0, (float)$this->input->post('unit_price')),
            'location'         => $this->input->post('location', TRUE),
            'is_active'        => 1,
            '_user_id'         => $this->current_user['id'],
        ]);

        $this->session->set_flashdata('success', 'Stock item added successfully.');
        redirect('stock/view/' . $id);
    }

    // ── View (GET) ────────────────────────────────────────────────────
    public function view($id = NULL)
    {
        $record = $this->_get_or_404($id);
        $data   = [
            'title'     => $record->name,
            'user'      => $this->current_user,
            'record'    => $record,
            'movements' => $this->Stock_model->get_movements($id),
        ];
        $this->load->view('layouts/header', $data);
        $this->load->view('stock/view', $data);
        $this->load->view('layouts/footer');
    }

    // ── Edit (GET) ────────────────────────────────────────────────────
    public function edit($id = NULL)
    {
        $record = $this->_get_or_404($id);
        $data   = [
            'title'      => 'Edit: ' . $record->name,
            'user'       => $this->current_user,
            'is_edit'    => TRUE,
            'record'     => $record,
            'categories' => $this->Stock_model->get_categories(),
        ];
        $this->load->view('layouts/header', $data);
        $this->load->view('stock/form', $data);
        $this->load->view('layouts/footer');
    }

    // ── Update (POST) ─────────────────────────────────────────────────
    public function update($id = NULL)
    {
        $record = $this->_get_or_404($id);
        $this->_set_rules();

        if ($this->form_validation->run() === FALSE) {
            $this->edit($id);
            return;
        }

        $code = trim($this->input->post('code', TRUE));
        if ($code && $this->Stock_model->code_exists($code, $id)) {
            $this->session->set_flashdata('error', 'Item code already exists.');
            redirect('stock/edit/' . $id);
            return;
        }

        $this->Stock_model->update($id, [
            'category_id'   => (int)$this->input->post('category_id'),
            'code'          => $code ?: $record->code,
            'name'          => $this->input->post('name',        TRUE),
            'description'   => $this->input->post('description', TRUE),
            'unit'          => $this->input->post('unit',        TRUE),
            'reorder_level' => max(0, (float)$this->input->post('reorder_level')),
            'unit_cost'     => max(0, (float)$this->input->post('unit_cost')),
            'unit_price'    => max(0, (float)$this->input->post('unit_price')),
            'location'      => $this->input->post('location', TRUE),
            'is_active'     => $this->input->post('is_active') ? 1 : 0,
        ]);

        $this->session->set_flashdata('success', 'Stock item updated.');
        redirect('stock/view/' . $id);
    }

    // ── Adjust stock (POST) ───────────────────────────────────────────
    public function adjust($id = NULL)
    {
        $this->_get_or_404($id);

        $type     = $this->input->post('movement_type');
        $quantity = abs((float)$this->input->post('quantity'));

        if (!in_array($type, ['in','out','adjustment']) || $quantity <= 0) {
            $this->session->set_flashdata('error', 'Invalid adjustment data.');
            redirect('stock/view/' . $id);
            return;
        }

        $this->Stock_model->adjust(
            $id,
            $type,
            $quantity,
            abs((float)$this->input->post('unit_cost')),
            $this->input->post('reference', TRUE),
            $this->input->post('notes',     TRUE),
            $this->current_user['id']
        );

        $this->session->set_flashdata('success', 'Stock adjusted successfully.');
        redirect('stock/view/' . $id);
    }

    // ── Delete (POST — Admin only) ─────────────────────────────────────
    public function delete($id = NULL)
    {
        $this->require_group(['Admin']);
        $this->_get_or_404($id);
        $this->Stock_model->delete($id);
        $this->session->set_flashdata('success', 'Stock item deleted.');
        redirect('stock');
    }

    // ── Helpers ───────────────────────────────────────────────────────
    private function _get_or_404($id)
    {
        $record = $this->Stock_model->get_by_id((int)$id);
        if (!$record) {
            $this->session->set_flashdata('error', 'Stock item not found.');
            redirect('stock');
        }
        return $record;
    }

    private function _set_rules()
    {
        $this->form_validation->set_rules('name',        'Item Name',  'required|trim|max_length[150]');
        $this->form_validation->set_rules('category_id', 'Category',   'required|integer');
        $this->form_validation->set_rules('unit',        'Unit',       'required|trim|max_length[30]');
    }
}
