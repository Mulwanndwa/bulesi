<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Plans extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Plan_model');
    }

    // ── List ──────────────────────────────────────────────────────────
    public function index()
    {
        $data = [
            'title' => 'House Plans',
            'user'  => $this->current_user,
            'plans' => $this->Plan_model->get_all(),
        ];
        $this->load->view('layouts/header', $data);
        $this->load->view('plans/index', $data);
        $this->load->view('layouts/footer');
    }

    // ── Draw (GET) — standalone full-screen page ───────────────────────
    public function draw($id = NULL)
    {
        $plan = $id ? $this->Plan_model->get_by_id((int)$id) : NULL;
        $this->load->view('plans/draw', [
            'plan'    => $plan,
            'user'    => $this->current_user,
            'save_url'=> base_url('plans/save'),
        ]);
    }

    // ── 3D Viewer ─────────────────────────────────────────────────────
    public function view3d($id = NULL)
    {
        if (!$id) redirect('plans');
        $plan = $this->Plan_model->get_by_id((int)$id);
        if (!$plan) {
            $this->session->set_flashdata('error', 'Plan not found.');
            redirect('plans');
        }
        $this->load->view('plans/view3d', [
            'plan' => $plan,
            'base' => base_url(),
        ]);
    }

    // ── Save (POST/AJAX) ───────────────────────────────────────────────
    public function save()
    {
        $this->output->set_content_type('application/json');

        $id        = (int)$this->input->post('id');
        $title     = $this->input->post('title', TRUE) ?: 'Untitled Plan';
        $plan_data = $this->input->post('plan_data');
        $grid_size = max(10, min(50, (int)$this->input->post('grid_size') ?: 20));

        if (!$plan_data || !json_decode($plan_data)) {
            echo json_encode(['success' => FALSE, 'error' => 'Invalid plan data']);
            return;
        }

        if ($id) {
            $existing = $this->Plan_model->get_by_id($id);
            if (!$existing) {
                echo json_encode(['success' => FALSE, 'error' => 'Plan not found']);
                return;
            }
            $this->Plan_model->update($id, $title, $plan_data, $grid_size);
            echo json_encode(['success' => TRUE, 'id' => $id]);
        } else {
            $new_id = $this->Plan_model->create(
                $this->current_user['id'], $title, $plan_data, $grid_size
            );
            echo json_encode(['success' => TRUE, 'id' => $new_id]);
        }
    }

    // ── Delete (POST) ─────────────────────────────────────────────────
    public function delete($id = NULL)
    {
        $this->require_group(['Admin', 'Manager']);
        if ($this->Plan_model->get_by_id((int)$id)) {
            $this->Plan_model->delete((int)$id);
            $this->session->set_flashdata('success', 'Plan deleted.');
        }
        redirect('plans');
    }
}
