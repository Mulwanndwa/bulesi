<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Report_model extends CI_Model {

    public function get_kpis($start, $end)
    {
        $row = $this->db->query("
            SELECT
                COUNT(*)                                                          AS total_quotes,
                COALESCE(SUM(total), 0)                                           AS total_value,
                COALESCE(SUM(CASE WHEN status IN ('draft','sent') THEN total END), 0)
                                                                                  AS pipeline_value,
                COALESCE(SUM(CASE WHEN status IN ('accepted','in_progress','completed','invoiced') THEN total END), 0)
                                                                                  AS accepted_value,
                COALESCE(SUM(CASE WHEN status IN ('completed','invoiced') THEN total END), 0)
                                                                                  AS completed_value,
                COUNT(CASE WHEN status IN ('accepted','in_progress','completed','invoiced') THEN 1 END)
                                                                                  AS accepted_count
            FROM quotations
            WHERE quote_date BETWEEN ? AND ?
        ", [$start, $end])->row();

        $row->conversion_rate = $row->total_quotes > 0
            ? round($row->accepted_count / $row->total_quotes * 100, 1)
            : 0;

        return $row;
    }

    public function get_monthly_trends($year)
    {
        $rows = $this->db->query("
            SELECT
                MONTH(quote_date)                                                        AS month,
                COUNT(*)                                                                 AS quote_count,
                COALESCE(SUM(total), 0)                                                  AS total_value,
                COALESCE(SUM(CASE WHEN status IN ('accepted','in_progress','completed','invoiced') THEN total END), 0)
                                                                                         AS accepted_value
            FROM quotations
            WHERE YEAR(quote_date) = ?
            GROUP BY MONTH(quote_date)
            ORDER BY month
        ", [(int)$year])->result();

        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[$m] = (object)['month' => $m, 'quote_count' => 0, 'total_value' => 0, 'accepted_value' => 0];
        }
        foreach ($rows as $row) {
            $months[(int)$row->month] = $row;
        }
        return array_values($months);
    }

    public function get_status_breakdown($start, $end)
    {
        return $this->db->query("
            SELECT status,
                   COUNT(*)                 AS count,
                   COALESCE(SUM(total), 0)  AS total_value
            FROM quotations
            WHERE quote_date BETWEEN ? AND ?
            GROUP BY status
            ORDER BY count DESC
        ", [$start, $end])->result();
    }

    public function get_top_customers($start, $end, $limit = 10)
    {
        return $this->db->query("
            SELECT
                customer_name,
                customer_phone,
                COUNT(*)                                                                  AS quote_count,
                COALESCE(SUM(total), 0)                                                   AS total_value,
                COALESCE(SUM(CASE WHEN status IN ('accepted','in_progress','completed','invoiced') THEN total END), 0)
                                                                                          AS accepted_value,
                MAX(quote_date)                                                           AS last_quote
            FROM quotations
            WHERE quote_date BETWEEN ? AND ?
            GROUP BY customer_name, customer_phone
            ORDER BY total_value DESC
            LIMIT ?
        ", [$start, $end, (int)$limit])->result();
    }

    public function get_popular_items($start, $end, $limit = 10)
    {
        return $this->db->query("
            SELECT
                qi.item_description,
                COUNT(*)                        AS occurrences,
                COALESCE(SUM(qi.quantity), 0)   AS total_qty,
                COALESCE(SUM(qi.line_total), 0) AS total_value
            FROM quotation_items qi
            JOIN quotations q ON q.id = qi.quotation_id
            WHERE q.quote_date BETWEEN ? AND ?
            GROUP BY qi.item_description
            ORDER BY total_value DESC
            LIMIT ?
        ", [$start, $end, (int)$limit])->result();
    }

    public function get_weekly_activity($start, $end)
    {
        return $this->db->query("
            SELECT
                WEEK(quote_date, 1)             AS week_num,
                MIN(quote_date)                 AS week_start,
                COUNT(*)                        AS quote_count,
                COALESCE(SUM(total), 0)         AS total_value
            FROM quotations
            WHERE quote_date BETWEEN ? AND ?
            GROUP BY WEEK(quote_date, 1)
            ORDER BY week_num
        ", [$start, $end])->result();
    }

    public function get_status_flow($start, $end)
    {
        return $this->db->query("
            SELECT
                status,
                COUNT(*)                        AS count,
                COALESCE(SUM(total), 0)         AS value,
                COALESCE(AVG(total), 0)         AS avg_value
            FROM quotations
            WHERE quote_date BETWEEN ? AND ?
            GROUP BY status
        ", [$start, $end])->result_array();
    }
}
