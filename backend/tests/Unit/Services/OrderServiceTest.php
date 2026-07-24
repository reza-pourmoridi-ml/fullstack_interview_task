<?php

declare(strict_types=1);

use App\Repositories\OrderRepository;
use App\Services\OrderService;
use App\Support\Cache;
use PHPUnit\Framework\TestCase;

final class OrderServiceTest extends TestCase
{
    public function test_it_builds_correct_cache_key_and_remembers_result(): void
    {
        $filters = [
            'user_id' => 5,
            'per_page' => 20,
            'start' => '2024-01-01 00:00:00',
            'end' => '2024-01-31 23:59:59',
            'cursor_created_at' => null,
            'cursor_id' => null,
        ];

        $expectedResult = [
            'total' => 1,
            'has_more' => false,
            'next_cursor' => null,
            'data' => [
                [
                    'id' => 10,
                    'user_id' => 5,
                    'status' => 'paid',
                    'created_at' => '2024-01-10 12:00:00',
                    'payment' => [
                        'status' => 'paid',
                        'amount' => 250.0,
                    ],
                    'items_count' => 2,
                ]
            ],
        ];

        $repository = $this->createMock(OrderRepository::class);
        $cache = $this->createMock(Cache::class);

        $cache->expects($this->once())
            ->method('getNamespaceVersion')
            ->with('orders')
            ->willReturn(3);

        $cache->expects($this->once())
            ->method('remember')
            ->with(
                'orders:v3:user:5:cursor:first:pp:20:s:2024-01-01 00:00:00:e:2024-01-31 23:59:59',
                10,
                $this->callback(static fn ($callback) => is_callable($callback))
            )
            ->willReturnCallback(function (string $key, int $ttl, callable $callback) use ($expectedResult) {
                return $callback();
            });

        $repository->expects($this->once())
            ->method('getPaginatedOrders')
            ->with(
                5,
                20,
                '2024-01-01 00:00:00',
                '2024-01-31 23:59:59',
                null,
                null
            )
            ->willReturn($expectedResult);

        $service = new OrderService($repository, $cache);
        $result = $service->getOrdersList($filters);

        $this->assertSame($expectedResult, $result);
    }

    public function test_it_uses_cursor_key_when_cursor_exists(): void
    {
        $filters = [
            'user_id' => 7,
            'per_page' => 15,
            'start' => null,
            'end' => null,
            'cursor_created_at' => '2024-02-01 10:00:00',
            'cursor_id' => 99,
        ];

        $repository = $this->createMock(OrderRepository::class);
        $cache = $this->createMock(Cache::class);

        $cache->expects($this->once())
            ->method('getNamespaceVersion')
            ->with('orders')
            ->willReturn(1);

        $cache->expects($this->once())
            ->method('remember')
            ->with(
                'orders:v1:user:7:cursor:2024-02-01 10:00:00:99:pp:15:s:null:e:null',
                10,
                $this->isType('callable')
            )
            ->willReturn([
                'total' => 0,
                'has_more' => false,
                'next_cursor' => null,
                'data' => [],
            ]);

        $repository->expects($this->never())
            ->method('getPaginatedOrders');

        $service = new OrderService($repository, $cache);
        $result = $service->getOrdersList($filters);

        $this->assertSame([], $result['data']);
        $this->assertFalse($result['has_more']);
    }
}
