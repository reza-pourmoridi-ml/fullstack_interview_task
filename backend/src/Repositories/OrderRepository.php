<?php

namespace App\Repositories;

use PDO;

class OrderRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getPaginatedOrders(int $userId, int $page, int $perPage, ?string $start, ?string $end): array
    {
        $offset = ($page - 1) * $perPage;

        $countSql = "SELECT COUNT(*) FROM orders WHERE user_id = :user_id";
        $countParams = [':user_id' => $userId];

        if ($start) {
            $countSql .= " AND created_at >= :start";
            $countParams[':start'] = $start;
        }
        if ($end) {
            $countSql .= " AND created_at <= :end";
            $countParams[':end'] = $end;
        }

        $stmt = $this->db->prepare($countSql);
        $stmt->execute($countParams);
        $totalOrders = (int)$stmt->fetchColumn();

        if ($totalOrders === 0) {
            return [
                'total' => 0,
                'data' => []
            ];
        }


        $sql = "
            SELECT 
                o.id, 
                o.user_id, 
                o.status, 
                o.created_at,
                p.status AS payment_status,
                p.amount AS payment_amount,
                COUNT(oi.id) AS items_count
            FROM orders o
            LEFT JOIN payments p ON p.order_id = o.id
            LEFT JOIN order_items oi ON oi.order_id = o.id
            WHERE o.user_id = :user_id
        ";

        $params = [':user_id' => $userId];

        if ($start) {
            $sql .= " AND o.created_at >= :start";
            $params[':start'] = $start;
        }
        if ($end) {
            $sql .= " AND o.created_at <= :end";
            $params[':end'] = $end;
        }

        $sql .= "
            GROUP BY o.id, p.id
            ORDER BY o.created_at ASC, o.id ASC
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        if ($start) $stmt->bindValue(':start', $start, PDO::PARAM_STR);
        if ($end) $stmt->bindValue(':end', $end, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $orders = array_map(function ($row) {
            return [
                'id' => (int)$row['id'],
                'user_id' => (int)$row['user_id'],
                'status' => $row['status'],
                'created_at' => $row['created_at'],
                'payment' => $row['payment_status'] ? [
                    'status' => $row['payment_status'],
                    'amount' => (float)$row['payment_amount']
                ] : null,
                'items_count' => (int)$row['items_count']
            ];
        }, $rows);

        return [
            'total' => $totalOrders,
            'data' => $orders
        ];
    }
}
