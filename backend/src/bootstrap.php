<?php

declare(strict_types=1);

namespace App;

use PDO;
use Exception;

date_default_timezone_set('UTC');

$dbFile = __DIR__ . '/../db/orders.sqlite';
$initSql = __DIR__ . '/../db/migrations/001_init.sql';

if (!file_exists(dirname($dbFile))) {
    mkdir(dirname($dbFile), 0777, true);
}

try {
    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $pdo->exec('PRAGMA foreign_keys = ON;');

    if (!file_exists($dbFile) || filesize($dbFile) === 0) {
        if (file_exists($initSql)) {
            $sql = file_get_contents($initSql);
            $pdo->exec($sql);
        }
    }
} catch (Exception $e) {
    error_log('Database connection error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal Server Error']);
    exit;
}

return $pdo;
