<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reports extends MY_Controller {

    private $statuses = [
        'draft'       => ['label' => 'Draft',       'color' => '#6c757d'],
        'sent'        => ['label' => 'Sent',        'color' => '#0dcaf0'],
        'accepted'    => ['label' => 'Accepted',    'color' => '#0d6efd'],
        'in_progress' => ['label' => 'In Progress', 'color' => '#fd7e14'],
        'completed'   => ['label' => 'Completed',   'color' => '#198754'],
        'invoiced'    => ['label' => 'Invoiced',    'color' => '#212529'],
        'rejected'    => ['label' => 'Rejected',    'color' => '#dc3545'],
        'cancelled'   => ['label' => 'Cancelled',   'color' => '#adb5bd'],
    ];

    private $periods = [
        'this_month'  => 'This Month',
        'last_month'  => 'Last Month',
        'this_quarter'=> 'This Quarter',
        'this_year'   => 'This Year',
        'last_year'   => 'Last Year',
        'all_time'    => 'All Time',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Report_model');
    }

    public function index($period = 'this_month')
    {
        if (!array_key_exists($period, $this->periods)) {
            $period = 'this_month';
        }

        list($start, $end) = $this->_date_range($period);
        $this->_render($period, $start, $end);
    }

    public function custom($start = NULL, $end = NULL)
    {
        if (!$start || !$end || !strtotime($start) || !strtotime($end)) {
            redirect('reports');
        }
        if ($start > $end) {
            list($start, $end) = [$end, $start];
        }
        $this->_render('custom', $start, $end);
    }

    public function sales($period = 'this_month')
    {
        if (!array_key_exists($period, $this->periods)) {
            $period = 'this_month';
        }

        list($start, $end) = $this->_date_range($period);
        $this->_render_sales($period, $start, $end);
    }

    public function sales_custom($start = NULL, $end = NULL)
    {
        if (!$start || !$end || !strtotime($start) || !strtotime($end)) {
            redirect('reports/sales');
        }
        if ($start > $end) {
            list($start, $end) = [$end, $start];
        }
        $this->_render_sales('custom', $start, $end);
    }

    private function _render_sales($period, $start, $end)
    {
        $year = (int)date('Y', strtotime($start));

        $data = [
            'title'            => 'Sales Report',
            'user'             => $this->current_user,
            'period'           => $period,
            'periods'          => $this->periods,
            'start'            => $start,
            'end'              => $end,
            'year'             => $year,
            'kpis'             => $this->Report_model->get_sales_kpis($start, $end),
            'monthly'          => $this->Report_model->get_sales_monthly($year),
            'by_user'          => $this->Report_model->get_sales_by_user($start, $end),
            'by_company'       => $this->Report_model->get_sales_by_company($start, $end),
            'sales_list'       => $this->Report_model->get_sales_list($start, $end),
        ];

        $this->load->view('layouts/header', $data);
        $this->load->view('reports/sales', $data);
        $this->load->view('layouts/footer');
    }

    public function delete_message($id)
    {
        $this->require_group(['Admin']);
        if ($this->input->method() !== 'post') {
            show_404();
        }
        $this->db->where('id', (int)$id)->delete('messages');
        $ok = $this->db->affected_rows() > 0;
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => $ok]));
    }

    public function chats($period = 'this_month')
    {
        if (!array_key_exists($period, $this->periods)) {
            $period = 'this_month';
        }
        list($start, $end) = $this->_date_range($period);
        $this->_render_chats($period, $start, $end);
    }

    public function chats_custom($start = NULL, $end = NULL)
    {
        if (!$start || !$end || !strtotime($start) || !strtotime($end)) {
            redirect('reports/chats');
        }
        if ($start > $end) list($start, $end) = [$end, $start];
        $this->_render_chats('custom', $start, $end);
    }

    private function _render_chats($period, $start, $end)
    {
        $data = [
            'title'          => 'Chat Report',
            'user'           => $this->current_user,
            'period'         => $period,
            'periods'        => $this->periods,
            'start'          => $start,
            'end'            => $end,
            'kpis'           => $this->Report_model->get_chat_kpis($start, $end),
            'by_user'        => $this->Report_model->get_chat_by_user($start, $end),
            'grouped'        => $this->Report_model->get_chat_grouped_by_user($start, $end),
        ];

        $this->load->view('layouts/header', $data);
        $this->load->view('reports/chats', $data);
        $this->load->view('layouts/footer');
    }

    private function _render($period, $start, $end)
    {
        $year    = (int)date('Y', strtotime($start));
        $monthly = $this->Report_model->get_monthly_trends($year);

        $data = [
            'title'          => 'Reports',
            'user'           => $this->current_user,
            'period'         => $period,
            'periods'        => $this->periods,
            'start'          => $start,
            'end'            => $end,
            'year'           => $year,
            'statuses'       => $this->statuses,
            'kpis'           => $this->Report_model->get_kpis($start, $end),
            'monthly'        => $monthly,
            'by_status'      => $this->Report_model->get_status_breakdown($start, $end),
            'top_customers'  => $this->Report_model->get_top_customers($start, $end),
            'popular_items'  => $this->Report_model->get_popular_items($start, $end),
        ];

        $this->load->view('layouts/header', $data);
        $this->load->view('reports/index', $data);
        $this->load->view('layouts/footer');
    }

    private function _date_range($period)
    {
        switch ($period) {
            case 'last_month':
                return [
                    date('Y-m-01', strtotime('first day of last month')),
                    date('Y-m-t',  strtotime('last day of last month')),
                ];
            case 'this_quarter':
                $q     = ceil(date('n') / 3);
                $start = date('Y') . '-' . str_pad(($q - 1) * 3 + 1, 2, '0', STR_PAD_LEFT) . '-01';
                $end   = date('Y-m-t', strtotime(date('Y') . '-' . str_pad($q * 3, 2, '0', STR_PAD_LEFT) . '-01'));
                return [$start, $end];
            case 'this_year':
                return [date('Y-01-01'), date('Y-12-31')];
            case 'last_year':
                $y = date('Y') - 1;
                return ["{$y}-01-01", "{$y}-12-31"];
            case 'all_time':
                return ['2000-01-01', date('Y-12-31')];
            default: // this_month
                return [date('Y-m-01'), date('Y-m-t')];
        }
    }
}
