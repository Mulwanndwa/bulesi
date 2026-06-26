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

    public function get_sales_kpis($start, $end)
    {
        return $this->db->query("
            SELECT
                COUNT(*)                        AS total_sales,
                COALESCE(SUM(total), 0)         AS total_revenue,
                COALESCE(AVG(total), 0)         AS avg_sale_value,
                COALESCE(SUM(subtotal), 0)      AS total_subtotal,
                COALESCE(SUM(vat_amount), 0)    AS total_vat,
                COUNT(DISTINCT customer_name)   AS unique_customers
            FROM quotations
            WHERE status IN ('completed','invoiced')
              AND quote_date BETWEEN ? AND ?
        ", [$start, $end])->row();
    }

    public function get_sales_by_user($start, $end)
    {
        return $this->db->query("
            SELECT
                COALESCE(u.username, 'Unknown') AS username,
                COALESCE(g.name, '—')           AS group_name,
                COUNT(q.id)                     AS sales_count,
                COALESCE(SUM(q.total), 0)       AS total_revenue,
                COALESCE(AVG(q.total), 0)       AS avg_value,
                MAX(q.quote_date)               AS last_sale
            FROM quotations q
            LEFT JOIN auth_users u ON u.id = q.user_id
            LEFT JOIN user_groups g ON g.id = u.group_id
            WHERE q.status IN ('completed','invoiced')
              AND q.quote_date BETWEEN ? AND ?
            GROUP BY q.user_id, u.username, g.name
            ORDER BY total_revenue DESC
        ", [$start, $end])->result();
    }

    public function get_sales_by_company($start, $end)
    {
        return $this->db->query("
            SELECT
                COALESCE(c.name, 'No Company') AS company_name,
                COUNT(q.id)                    AS sales_count,
                COALESCE(SUM(q.total), 0)      AS total_revenue,
                COALESCE(AVG(q.total), 0)      AS avg_value
            FROM quotations q
            LEFT JOIN auth_users u ON u.id = q.user_id
            LEFT JOIN companies c ON c.id = u.company_id
            WHERE q.status IN ('completed','invoiced')
              AND q.quote_date BETWEEN ? AND ?
            GROUP BY u.company_id, c.name
            ORDER BY total_revenue DESC
        ", [$start, $end])->result();
    }

    public function get_sales_monthly($year)
    {
        $rows = $this->db->query("
            SELECT
                MONTH(quote_date)               AS month,
                COUNT(*)                        AS sales_count,
                COALESCE(SUM(total), 0)         AS revenue,
                COALESCE(SUM(vat_amount), 0)    AS vat_collected
            FROM quotations
            WHERE status IN ('completed','invoiced')
              AND YEAR(quote_date) = ?
            GROUP BY MONTH(quote_date)
            ORDER BY month
        ", [(int)$year])->result();

        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[$m] = (object)['month' => $m, 'sales_count' => 0, 'revenue' => 0, 'vat_collected' => 0];
        }
        foreach ($rows as $row) {
            $months[(int)$row->month] = $row;
        }
        return array_values($months);
    }

    public function get_sales_list($start, $end, $limit = 50)
    {
        return $this->db->query("
            SELECT
                q.quote_number,
                q.customer_name,
                q.customer_phone,
                q.total,
                q.vat_amount,
                q.subtotal,
                q.status,
                q.quote_date,
                COALESCE(u.username, '—') AS sold_by,
                COALESCE(c.name, '—')     AS company_name
            FROM quotations q
            LEFT JOIN auth_users u ON u.id = q.user_id
            LEFT JOIN companies c ON c.id = u.company_id
            WHERE q.status IN ('completed','invoiced')
              AND q.quote_date BETWEEN ? AND ?
            ORDER BY q.quote_date DESC
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

    public function get_chat_kpis($start, $end)
    {
        return $this->db->query("
            SELECT
                COUNT(DISTINCT m.conversation_id)          AS total_conversations,
                COUNT(m.id)                                AS total_messages,
                COUNT(DISTINCT m.sender_id)                AS active_users,
                COUNT(CASE WHEN m.image_path IS NOT NULL THEN 1 END) AS image_messages,
                COUNT(CASE WHEN m.quote_id IS NOT NULL THEN 1 END)   AS quote_messages
            FROM messages m
            WHERE m.created_at BETWEEN ? AND ?
        ", [$start . ' 00:00:00', $end . ' 23:59:59'])->row();
    }

    public function get_chat_by_user($start, $end)
    {
        return $this->db->query("
            SELECT
                u.username,
                TRIM(CONCAT(COALESCE(u.first_name,''),' ',COALESCE(u.last_name,''))) AS full_name,
                COUNT(m.id)                AS message_count,
                COUNT(DISTINCT m.conversation_id) AS conversation_count,
                MAX(m.created_at)          AS last_active
            FROM messages m
            JOIN auth_users u ON u.id = m.sender_id
            WHERE m.created_at BETWEEN ? AND ?
            GROUP BY m.sender_id, u.username, u.first_name, u.last_name
            ORDER BY message_count DESC
            LIMIT 20
        ", [$start . ' 00:00:00', $end . ' 23:59:59'])->result();
    }

    public function get_chat_recent($start, $end, $limit = 50)
    {
        return $this->db->query("
            SELECT
                m.id, m.body, m.image_path, m.created_at,
                u.username AS sender_username,
                TRIM(CONCAT(COALESCE(u.first_name,''),' ',COALESCE(u.last_name,''))) AS sender_full_name,
                m.conversation_id,
                q.quote_number
            FROM messages m
            JOIN auth_users u ON u.id = m.sender_id
            LEFT JOIN quotations q ON q.id = m.quote_id
            WHERE m.created_at BETWEEN ? AND ?
            ORDER BY m.created_at DESC
            LIMIT ?
        ", [$start . ' 00:00:00', $end . ' 23:59:59', (int)$limit])->result();
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
