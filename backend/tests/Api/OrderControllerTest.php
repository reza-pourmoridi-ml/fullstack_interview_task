<?php

declare(strict_types=1);

namespace Api;

use App\Http\Controllers\OrderController;
use App\Services\OrderService;
use PHPUnit\Framework\TestCase;

final class OrderControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        $_GET = [];
        parent::tearDown();
    }

    public function test_it_uses_default_values_and_returns_json_payload(): void
    {
        $_GET = [];

        $service = $this->createMock(OrderService::class);
        $service->expects($this->once())
            ->method('getOrdersList')
            ->with([
                'user_id' => 1,
                'per_page' => 20,
                'start' => null,
                'end' => null,
                'cursor_created_at' => null,
                'cursor_id' => null,
            ])
            ->willReturn([
                'total' => 0,
                'has_more' => false,
                'next_cursor' => null,
                'data' => [],
            ]);

        $controller = new OrderController($service);

        ob_start();
        $controller->index();
        $output = ob_get_clean();

        $this->assertNotFalse($output);
        $decoded = json_decode($output, true);

        $this->assertSame('CAND-RZ4', $decoded['token_hint']);
        $this->assertSame(20, $decoded['per_page']);
        $this->assertSame(0, $decoded['total']);
        $this->assertFalse($decoded['has_more']);
        $this->assertNull($decoded['next_cursor']);
        $this->assertSame([], $decoded['data']);
    }

    public function test_it_clamps_per_page_and_normalizes_empty_values(): void
    {
        $_GET = [
            'user_id' => '7',
            'per_page' => '1000',
            'start' => '',
            'end' => '',
            'cursor_created_at' => '',
            'cursor_id' => '',
        ];

        $service = $this->createMock(OrderService::class);
        $service->expects($this->once())
            ->method('getOrdersList')
            ->with([
                'user_id' => 7,
                'per_page' => 100,
                'start' => null,
                'end' => null,
                'cursor_created_at' => null,
                'cursor_id' => null,
            ])
            ->willReturn([
                'total' => 1,
                'has_more' => false,
                'next_cursor' => null,
                'data' => [],
            ]);

        $controller = new OrderController($service);

        ob_start();
        $controller->index();
        $output = ob_get_clean();

        $decoded = json_decode((string)$output, true);

        $this->assertSame(100, $decoded['per_page']);
    }

    public function test_it_converts_cursor_id_to_non_negative_integer(): void
    {
        $_GET = [
            'cursor_id' => '-15',
        ];

        $service = $this->createMock(OrderService::class);
        $service->expects($this->once())
            ->method('getOrdersList')
            ->with($this->callback(function (array $filters) {
                return $filters['cursor_id'] === 0;
            }))
            ->willReturn([
                'total' => 0,
                'has_more' => false,
                'next_cursor' => null,
                'data' => [],
            ]);

        $controller = new OrderController($service);

        ob_start();
        $controller->index();
        ob_end_clean();

        $this->assertTrue(true);
    }
}
