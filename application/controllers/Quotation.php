<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Quotation extends MY_Controller {

    private $statuses = [
        'draft'       => ['label' => 'Draft',       'badge' => 'secondary'],
        'sent'        => ['label' => 'Sent',        'badge' => 'info'],
        'accepted'    => ['label' => 'Accepted',    'badge' => 'primary'],
        'in_progress' => ['label' => 'In Progress', 'badge' => 'warning'],
        'completed'   => ['label' => 'Completed',   'badge' => 'success'],
        'invoiced'    => ['label' => 'Invoiced',    'badge' => 'dark'],
        'rejected'    => ['label' => 'Rejected',    'badge' => 'danger'],
        'cancelled'   => ['label' => 'Cancelled',   'badge' => 'secondary'],
    ];

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Quotation_model');
        $this->load->model('Quotation_type_model');
        $this->load->library('form_validation');
    }

    // ── List ──────────────────────────────────────────────────────────
    public function index($filter = 'all')
    {
        $data = [
            'title'       => 'Quotations',
            'user'        => $this->current_user,
            'statuses'    => $this->statuses,
            'quote_types' => $this->Quotation_type_model->get_active(),
            'filter'      => $filter,
            'quotes'      => $this->Quotation_model->get_all($filter),
        ];
        $this->load->view('layouts/header', $data);
        $this->load->view('quotations/index', $data);
        $this->load->view('layouts/footer');
    }

    // ── Create (GET) ──────────────────────────────────────────────────
    public function create()
    {
        $data = [
            'title'       => 'New Quotation',
            'user'        => $this->current_user,
            'statuses'    => $this->statuses,
            'quote_types' => $this->Quotation_type_model->get_active(),
            'is_edit'     => FALSE,
            'quote'       => NULL,
            'items'       => [],
        ];
        $this->load->view('layouts/header', $data);
        $this->load->view('quotations/form', $data);
        $this->load->view('layouts/footer');
    }

    // ── Store (POST) ──────────────────────────────────────────────────
    public function store()
    {
        $this->form_validation->set_rules('customer_name', 'Customer Name', 'required|trim|max_length[150]');
        $this->form_validation->set_rules('quote_date',    'Quote Date',    'required|trim');

        if ($this->form_validation->run() === FALSE) {
            $this->create();
            return;
        }

        $items = $this->_process_items($this->input->post('items'));
        if (empty($items)) {
            $this->session->set_flashdata('error', 'Add at least one line item.');
            redirect('quotation/create');
            return;
        }

        $totals = $this->_calc_totals($items, $this->input->post('vat_rate'));

        $data = [
            'quote_number'   => $this->Quotation_model->generate_quote_number(),
            'type_id'        => (int)$this->input->post('type_id') ?: 1,
            'user_id'        => $this->current_user['id'],
            'customer_name'  => $this->input->post('customer_name',  TRUE),
            'customer_phone' => $this->input->post('customer_phone', TRUE),
            'customer_email' => $this->input->post('customer_email', TRUE),
            'description'    => $this->input->post('description',    TRUE),
            'status'         => 'draft',
            'subtotal'       => $totals['subtotal'],
            'vat_rate'       => $totals['vat_rate'],
            'vat_amount'     => $totals['vat_amount'],
            'total'          => $totals['total'],
            'quote_date'     => $this->input->post('quote_date',    TRUE),
            'valid_until'    => $this->input->post('valid_until',   TRUE) ?: NULL,
            'notes'          => $this->input->post('notes',         TRUE),
            'image_1'        => $this->_upload_image('image_1'),
            'image_2'        => $this->_upload_image('image_2'),
            'image_3'        => $this->_upload_image('image_3'),
            'image_4'        => $this->_upload_image('image_4'),
        ];

        $id = $this->Quotation_model->create($data, $items);
        if ($id) {
            $this->session->set_flashdata('success', 'Quotation created successfully.');
            redirect('quotation/view/' . $id);
        } else {
            $this->session->set_flashdata('error', 'Failed to save quotation. Please try again.');
            redirect('quotation/create');
        }
    }

    // ── View (GET) ────────────────────────────────────────────────────
    public function view($id = NULL)
    {
        $quote = $this->_get_quote_or_404($id);
        $data  = [
            'title'       => $quote->quote_number,
            'user'        => $this->current_user,
            'statuses'    => $this->statuses,
            'quote_types' => $this->Quotation_type_model->get_active(),
            'quote'       => $quote,
            'items'       => $this->Quotation_model->get_items($id),
        ];
        $this->load->view('layouts/header', $data);
        $this->load->view('quotations/view', $data);
        $this->load->view('layouts/footer');
    }

    // ── Edit (GET) ────────────────────────────────────────────────────
    public function edit($id = NULL)
    {
        $quote = $this->_get_quote_or_404($id);
        $data  = [
            'title'       => 'Edit ' . $quote->quote_number,
            'user'        => $this->current_user,
            'statuses'    => $this->statuses,
            'quote_types' => $this->Quotation_type_model->get_active(),
            'is_edit'     => TRUE,
            'quote'       => $quote,
            'items'       => $this->Quotation_model->get_items($id),
        ];
        $this->load->view('layouts/header', $data);
        $this->load->view('quotations/form', $data);
        $this->load->view('layouts/footer');
    }

    // ── Update (POST) ─────────────────────────────────────────────────
    public function update($id = NULL)
    {
        $quote = $this->_get_quote_or_404($id);

        $this->form_validation->set_rules('customer_name', 'Customer Name', 'required|trim|max_length[150]');
        $this->form_validation->set_rules('quote_date',    'Quote Date',    'required|trim');

        if ($this->form_validation->run() === FALSE) {
            $this->edit($id);
            return;
        }

        $items          = $this->_process_items($this->input->post('items'));
        $existing_items = $this->Quotation_model->get_items($id);

        if (empty($items)) {
            $this->session->set_flashdata('error', 'Add at least one line item.');
            redirect('quotation/edit/' . $id);
            return;
        }

        if (!$this->is_group('Admin') && count($items) < count($existing_items)) {
            $this->session->set_flashdata('error', 'Only admins can remove line items from a quotation.');
            redirect('quotation/edit/' . $id);
            return;
        }

        $totals = $this->_calc_totals($items, $this->input->post('vat_rate'));

        $data = [
            'type_id'        => (int)$this->input->post('type_id') ?: $quote->type_id,
            'customer_name'  => $this->input->post('customer_name',  TRUE),
            'customer_phone' => $this->input->post('customer_phone', TRUE),
            'customer_email' => $this->input->post('customer_email', TRUE),
            'description'    => $this->input->post('description',    TRUE),
            'status'         => in_array($this->input->post('status'), array_keys($this->statuses))
                                    ? $this->input->post('status') : $quote->status,
            'subtotal'       => $totals['subtotal'],
            'vat_rate'       => $totals['vat_rate'],
            'vat_amount'     => $totals['vat_amount'],
            'total'          => $totals['total'],
            'quote_date'     => $this->input->post('quote_date',  TRUE),
            'valid_until'    => $this->input->post('valid_until', TRUE) ?: NULL,
            'notes'          => $this->input->post('notes',       TRUE),
            'image_1'        => $this->_upload_image('image_1', $quote->image_1),
            'image_2'        => $this->_upload_image('image_2', $quote->image_2),
            'image_3'        => $this->_upload_image('image_3', $quote->image_3),
            'image_4'        => $this->_upload_image('image_4', $quote->image_4),
        ];

        if ($this->Quotation_model->update($id, $data, $items)) {
            $this->session->set_flashdata('success', 'Quotation updated successfully.');
            redirect('quotation/view/' . $id);
        } else {
            $this->session->set_flashdata('error', 'Failed to update quotation.');
            redirect('quotation/edit/' . $id);
        }
    }

    // ── Change status (POST) ──────────────────────────────────────────
    public function status($id = NULL)
    {
        $this->_get_quote_or_404($id);
        $new_status = $this->input->post('status', TRUE);
        if (array_key_exists($new_status, $this->statuses)) {
            $this->Quotation_model->update_status($id, $new_status);
            $this->session->set_flashdata('success', 'Status updated.');
        }
        redirect('quotation/view/' . $id);
    }

    // ── Delete (POST) ─────────────────────────────────────────────────
    public function delete($id = NULL)
    {
        $this->require_group(['Admin']);
        $this->_get_quote_or_404($id);
        $this->Quotation_model->delete($id);
        $this->session->set_flashdata('success', 'Quotation deleted.');
        redirect('quotation');
    }

    // ── Helpers ───────────────────────────────────────────────────────
    private function _get_quote_or_404($id)
    {
        $quote = $this->Quotation_model->get_by_id((int)$id);
        if (!$quote) {
            $this->session->set_flashdata('error', 'Quotation not found.');
            redirect('quotation');
        }
        return $quote;
    }

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

    private function _upload_image($field, $existing = NULL)
    {
        if (empty($_FILES[$field]['name'])) {
            return $existing;
        }

        $upload_path = FCPATH . 'uploads/quotations/';
        $this->load->library('upload');
        $this->upload->initialize([
            'upload_path'   => $upload_path,
            'allowed_types' => 'jpg|jpeg|png|gif|webp',
            'max_size'      => 5120,
            'encrypt_name'  => TRUE,
        ]);

        if ($this->upload->do_upload($field)) {
            if ($existing && file_exists(FCPATH . $existing)) {
                @unlink(FCPATH . $existing);
            }
            return 'uploads/quotations/' . $this->upload->data('file_name');
        }

        return $existing;
    }

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
}
