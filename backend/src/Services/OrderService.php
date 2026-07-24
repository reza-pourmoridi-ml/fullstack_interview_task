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
        $perPage = $filters['per_page'];
        $start = $filters['start'];
        $end = $filters['end'];
        $cursorCreatedAt = $filters['cursor_created_at'];
        $cursorId = $filters['cursor_id'];

        $cursorKey = $cursorCreatedAt !== null && $cursorId !== null
            ? $cursorCreatedAt . ':' . $cursorId
            : 'first';

        $ordersVersion = $this->cache->getNamespaceVersion('orders');

        $cacheKey = sprintf(
            'orders:v%d:user:%d:cursor:%s:pp:%d:s:%s:e:%s',
            $ordersVersion,
            $userId,
            $cursorKey,
            $perPage,
            $start ?? 'null',
            $end ?? 'null'
        );

        return $this->cache->remember($cacheKey, 10, function () use (
            $userId,
            $perPage,
            $start,
            $end,
            $cursorCreatedAt,
            $cursorId
        ) {
            return $this->repository->getPaginatedOrders(
                $userId,
                $perPage,
                $start,
                $end,
                $cursorCreatedAt,
                $cursorId
            );
        });
    }
}
