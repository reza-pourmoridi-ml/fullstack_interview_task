<?php

require_once __DIR__ . '/../../vendor/autoload.php';

$pdo = require_once __DIR__ . '/../src/bootstrap.php';

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($method === 'OPTIONS') {
    exit(0);
}

if ($path === '/health' && $method === 'GET') {
    \App\Support\Response::json([
        'status' => 'ok',
        'timestamp' => time(),
        'token_hint' => 'CAND-RZ4'
    ]);
}

if ($path === '/api/orders' && $method === 'GET') {
    $cache = new \App\Support\Cache();
    $orderRepository = new \App\Repositories\OrderRepository($pdo);
    $orderService = new \App\Services\OrderService($orderRepository, $cache);
    $orderController = new \App\Http\Controllers\OrderController($orderService);
    $orderController->index();
}

\App\Support\Response::json([
    'error' => 'Not Found',
    'path' => $path
], 404);
