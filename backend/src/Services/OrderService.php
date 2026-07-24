<?php

namespace App\Services;

use App\Repositories\OrderRepository;
use App\Support\Cache;

class OrderService
{
    private OrderRepository $repository;
    private Cache $cache;

    public function __construct(OrderRepository $repository, Cache $cache)
    {
        $this->repository = $repository;
        $this->cache = $cache;
    }

    public function getOrdersList(array $filters): array
    {
        $userId = $filters['user_id'];
        $page = $filters['page'];
        $perPage = $filters['per_page'];
        $start = $filters['start'];
        $end = $filters['end'];

        $cacheKey = sprintf(
            "orders:user:%d:p:%d:pp:%d:s:%s:e:%s",
            $userId,
            $page,
            $perPage,
            $start ?? 'null',
            $end ?? 'null'
        );

        return $this->cache->remember($cacheKey, 10, function () use ($userId, $page, $perPage, $start, $end) {
            return $this->repository->getPaginatedOrders($userId, $page, $perPage, $start, $end);
        });
    }
}
