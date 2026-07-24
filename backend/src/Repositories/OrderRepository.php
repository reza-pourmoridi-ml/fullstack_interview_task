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

    public function getPaginatedOrders(
        int $userId,
        int $perPage,
        ?string $start,
        ?string $end,
        ?string $cursorCreatedAt,
        ?int $cursorId
    ): array {
        $countSql = 'SELECT COUNT(*) FROM orders WHERE user_id = :user_id';
        $countParams = [':user_id' => $userId];

        if ($start) {
            $countSql .= ' AND created_at >= :start';
            $countParams[':start'] = $start;
        }

        if ($end) {
            $countSql .= ' AND created_at <= :end';
            $countParams[':end'] = $end;
        }

        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($countParams);
        $totalOrders = (int) $countStmt->fetchColumn();

        if ($totalOrders === 0) {
            return [
                'total' => 0,
                'has_more' => false,
                'next_cursor' => null,
                'data' => [],
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
                (
                    SELECT COUNT(*)
                    FROM order_items oi
                    WHERE oi.order_id = o.id
                ) AS items_count
            FROM orders o
            LEFT JOIN payments p ON p.order_id = o.id
            WHERE o.user_id = :user_id
        ";

        $params = [':user_id' => $userId];

        if ($start) {
            $sql .= ' AND o.created_at >= :start';
            $params[':start'] = $start;
        }

        if ($end) {
            $sql .= ' AND o.created_at <= :end';
            $params[':end'] = $end;
        }

        if ($cursorCreatedAt !== null && $cursorId !== null) {
            $sql .= '
                AND (
                    o.created_at > :cursor_created_at
                    OR (o.created_at = :cursor_created_at AND o.id > :cursor_id)
                )
            ';
            $params[':cursor_created_at'] = $cursorCreatedAt;
            $params[':cursor_id'] = $cursorId;
        }

        $sql .= '
            ORDER BY o.created_at ASC, o.id ASC
            LIMIT :limit
        ';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);

        if ($start) {
            $stmt->bindValue(':start', $start, PDO::PARAM_STR);
        }

        if ($end) {
            $stmt->bindValue(':end', $end, PDO::PARAM_STR);
        }

        if ($cursorCreatedAt !== null && $cursorId !== null) {
            $stmt->bindValue(':cursor_created_at', $cursorCreatedAt, PDO::PARAM_STR);
            $stmt->bindValue(':cursor_id', $cursorId, PDO::PARAM_INT);
        }

        foreach ($params as $key => $value) {
            if (in_array($key, [':user_id', ':start', ':end', ':cursor_created_at', ':cursor_id'], true)) {
                continue;
            }

            $stmt->bindValue($key, $value);
        }

        $stmt->bindValue(':limit', $perPage + 1, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $hasMore = count($rows) > $perPage;

        if ($hasMore) {
            array_pop($rows);
        }

        $orders = array_map(static function (array $row): array {
            return [
                'id' => (int) $row['id'],
                'user_id' => (int) $row['user_id'],
                'status' => $row['status'],
                'created_at' => $row['created_at'],
                'payment' => $row['payment_status'] ? [
                    'status' => $row['payment_status'],
                    'amount' => isset($row['payment_amount']) ? (float) $row['payment_amount'] : null,
                ] : null,
                'items_count' => (int) $row['items_count'],
            ];
        }, $rows);

        $nextCursor = null;
        if ($hasMore && !empty($orders)) {
            $lastOrder = $orders[count($orders) - 1];
            $nextCursor = [
                'created_at' => $lastOrder['created_at'],
                'id' => $lastOrder['id'],
            ];
        }

        return [
            'total' => $totalOrders,
            'has_more' => $hasMore,
            'next_cursor' => $nextCursor,
            'data' => $orders,
        ];
    }
}
