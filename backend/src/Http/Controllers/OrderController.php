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
        // ولیدیشن و مقداردهی اولیه به پارامترهای ورودی
        $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 1;
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = isset($_GET['per_page']) ? min(100, max(1, (int)$_GET['per_page'])) : 20;

        $start = !empty($_GET['start']) ? $_GET['start'] : null;
        $end = !empty($_GET['end']) ? $_GET['end'] : null;

        $filters = [
            'user_id' => $userId,
            'page' => $page,
            'per_page' => $perPage,
            'start' => $start,
            'end' => $end
        ];

        // دریافت اطلاعات از سرویس
        $result = $this->orderService->getOrdersList($filters);

        // ارسال پاسخ خروجی با همان فرمتی که Front-end انتظار دارد
        Response::json([
            'token_hint' => 'CAND-RZ4',
            'page' => $page,
            'per_page' => $perPage,
            'total' => $result['total'],
            'data' => $result['data']
        ]);
    }
}
