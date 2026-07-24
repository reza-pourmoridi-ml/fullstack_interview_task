<?php

namespace App\Support;

class Response
{
    public static function json(array $data, int $status = 200, array $headers = []): void
    {
        header('Content-Type: application/json; charset=utf-8', true, $status);
        foreach ($headers as $name => $value) {
            header("$name: $value");
        }
        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }
}
