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
        $companies    = $this->Company_model->get_all();
        $order_counts = $this->Company_model->get_order_counts();
        $quote_counts = $this->Company_model->get_quote_counts();

        foreach ($companies as $c) {
            $c->user_count  = $this->Company_model->user_count($c->id);
            $c->order_count = $order_counts[(int)$c->id] ?? 0;
            $c->quote_count = $quote_counts[(int)$c->id] ?? 0;
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

    public function orders($id = NULL)
    {
        $record = $this->_get_or_404($id);
        $orders = $this->Company_model->get_orders($id);

        $data = [
            'title'   => $record->name . ' — Orders',
            'user'    => $this->current_user,
            'company' => $record,
            'orders'  => $orders,
        ];
        $this->load->view('layouts/header', $data);
        $this->load->view('companies/orders', $data);
        $this->load->view('layouts/footer');
    }

    public function quotes($id = NULL)
    {
        $record = $this->_get_or_404($id);
        $quotes = $this->Company_model->get_quotes($id);

        $data = [
            'title'   => $record->name . ' — Quotes',
            'user'    => $this->current_user,
            'company' => $record,
            'quotes'  => $quotes,
        ];
        $this->load->view('layouts/header', $data);
        $this->load->view('companies/quotes', $data);
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

        $data = [
            'name'      => $this->input->post('name',    TRUE),
            'address'   => $this->input->post('address', TRUE),
            'phone'     => $this->input->post('phone',   TRUE),
            'email'     => $this->input->post('email',   TRUE),
            'is_active' => $this->input->post('is_active') ? 1 : 0,
        ];

        $logo = $this->_upload_logo();
        if ($logo) $data['logo'] = $logo;

        $this->Company_model->create($data);

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
        $record = $this->_get_or_404($id);
        $this->_set_rules();

        if ($this->form_validation->run() === FALSE) {
            $this->edit($id);
            return;
        }

        $data = [
            'name'      => $this->input->post('name',    TRUE),
            'address'   => $this->input->post('address', TRUE),
            'phone'     => $this->input->post('phone',   TRUE),
            'email'     => $this->input->post('email',   TRUE),
            'is_active' => $this->input->post('is_active') ? 1 : 0,
        ];

        // Remove existing logo if requested
        if ($this->input->post('remove_logo') && $record->logo) {
            $this->_delete_logo($record->logo);
            $data['logo'] = NULL;
        }

        // Replace logo if a new file was uploaded
        $logo = $this->_upload_logo();
        if ($logo) {
            if ($record->logo) $this->_delete_logo($record->logo);
            $data['logo'] = $logo;
        }

        $this->Company_model->update($id, $data);

        $this->session->set_flashdata('success', 'Company updated successfully.');
        redirect('companies');
    }

    public function delete($id = NULL)
    {
        $record = $this->_get_or_404($id);

        if ($this->Company_model->user_count($id) > 0) {
            $this->session->set_flashdata('error', 'Cannot delete — users are linked to this company.');
            redirect('companies');
            return;
        }

        if ($record->logo) $this->_delete_logo($record->logo);

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

    private function _upload_logo()
    {
        if (empty($_FILES['logo']['name']))              return NULL;
        if ($_FILES['logo']['error'] !== UPLOAD_ERR_OK) return NULL;
        if ($_FILES['logo']['size'] > 2 * 1024 * 1024)  return NULL;

        $allowed_exts  = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

        $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_exts)) return NULL;

        if (function_exists('mime_content_type')) {
            $mime = mime_content_type($_FILES['logo']['tmp_name']);
            if (!in_array(strtolower($mime), $allowed_mimes)) return NULL;
        }

        $upload_dir = FCPATH . 'uploads/companies/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, TRUE);
        @chmod($upload_dir, 0777);

        $fname = 'company_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

        if (move_uploaded_file($_FILES['logo']['tmp_name'], $upload_dir . $fname)) {
            return 'uploads/companies/' . $fname;
        }
        return NULL;
    }

    private function _delete_logo($path)
    {
        $full = FCPATH . $path;
        if ($path && file_exists($full)) @unlink($full);
    }
}
