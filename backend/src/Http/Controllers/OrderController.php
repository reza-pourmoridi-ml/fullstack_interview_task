<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use App\Support\Response;

class OrderController
{
    private OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(): void
    {
        $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 1;
        $perPage = isset($_GET['per_page']) ? min(100, max(1, (int)$_GET['per_page'])) : 20;

        $start = !empty($_GET['start']) ? $_GET['start'] : null;
        $end = !empty($_GET['end']) ? $_GET['end'] : null;

        $cursorCreatedAt = !empty($_GET['cursor_created_at']) ? $_GET['cursor_created_at'] : null;
        $cursorId = isset($_GET['cursor_id']) && $_GET['cursor_id'] !== ''
            ? max(0, (int)$_GET['cursor_id'])
            : null;

        $filters = [
            'user_id' => $userId,
            'per_page' => $perPage,
            'start' => $start,
            'end' => $end,
            'cursor_created_at' => $cursorCreatedAt,
            'cursor_id' => $cursorId,
        ];

        $result = $this->orderService->getOrdersList($filters);

        Response::json([
            'token_hint' => 'CAND-RZ4',
            'per_page' => $perPage,
            'total' => $result['total'],
            'has_more' => $result['has_more'],
            'next_cursor' => $result['next_cursor'],
            'data' => $result['data'],
        ]);
    }
}
