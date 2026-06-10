<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Public_quote extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Quotation_model');
        $this->load->helper('url');
    }

    // ── GET /q/:token ─────────────────────────────────────────────────
    public function view($token)
    {
        $quote = $this->Quotation_model->get_by_token($token);

        if (!$quote) {
            show_404();
        }

        $items = $this->Quotation_model->get_items($quote->id);

        $this->load->view('public_quote/view', [
            'quote' => $quote,
            'items' => $items,
        ]);
    }

    // ── POST /q/:token/sign ───────────────────────────────────────────
    public function sign($token)
    {
        header('Content-Type: application/json');

        $quote = $this->Quotation_model->get_by_token($token);
        if (!$quote) {
            http_response_code(404);
            echo json_encode(['error' => 'Quote not found.']);
            return;
        }

        $raw      = file_get_contents('php://input');
        $body     = json_decode($raw, TRUE) ?: [];
        $sig_data = trim($body['signature'] ?? '');
        $sig_name = trim(strip_tags($body['name'] ?? ''));

        if (!$sig_data || !$sig_name) {
            http_response_code(422);
            echo json_encode(['error' => 'Signature and name are required.']);
            return;
        }

        // Validate it looks like a PNG data URL
        if (strpos($sig_data, 'data:image/png;base64,') !== 0) {
            http_response_code(422);
            echo json_encode(['error' => 'Invalid signature format.']);
            return;
        }

        $this->Quotation_model->save_signature($quote->id, $sig_data, $sig_name);

        // Auto-advance status from 'sent' → 'accepted'
        if ($quote->status === 'sent') {
            $this->Quotation_model->update_status($quote->id, 'accepted');
        }

        echo json_encode([
            'success'   => TRUE,
            'signed_at' => date('d M Y H:i'),
            'name'      => $sig_name,
        ]);
    }
}
