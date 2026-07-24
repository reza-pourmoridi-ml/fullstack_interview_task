<?php

namespace App\Support;

class Cache
{
    private string $cachePath;

    public function __construct()
    {
        // ذخیره فایل‌های کش در پوشه موقت سیستم یا دایرکتوری محلی ایمن
        $this->cachePath = sys_get_temp_dir() . '/app_cache_';
    }

    public function get(string $key): mixed
    {
        $file = $this->getFilepath($key);
        if (!file_exists($file)) {
            return null;
        }

        $content = file_get_contents($file);
        if ($content === false) {
            return null;
        }

        $data = unserialize($content);
        if (!$data || time() > $data['expires_at']) {
            $this->forget($key); // کش منقضی شده را پاک می‌کنیم
            return null;
        }

        return $data['value'];
    }

    public function put(string $key, mixed $value, int $ttlSeconds): void
    {
        $file = $this->getFilepath($key);
        $data = [
            'expires_at' => time() + $ttlSeconds,
            'value' => $value
        ];
        file_put_contents($file, serialize($data));
    }

    public function forget(string $key): void
    {
        $file = $this->getFilepath($key);
        if (file_exists($file)) {
            @unlink($file);
        }
    }

    public function remember(string $key, int $ttlSeconds, callable $callback): mixed
    {
        $value = $this->get($key);
        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->put($key, $value, $ttlSeconds);
        return $value;
    }

    private function getFilepath(string $key): string
    {
        return $this->cachePath . md5($key) . '.cache';
    }
}
