<?php

namespace App\Support;

class Cache
{
    private string $cachePath;
    private string $versionsPath;

    public function __construct()
    {
        $basePath = sys_get_temp_dir() . '/app_cache';
        $this->cachePath = $basePath . '/data/';
        $this->versionsPath = $basePath . '/versions/';

        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0777, true);
        }

        if (!is_dir($this->versionsPath)) {
            mkdir($this->versionsPath, 0777, true);
        }
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
        if (!is_array($data) || time() > $data['expires_at']) {
            $this->forget($key);
            return null;
        }

        return $data['value'];
    }

    public function put(string $key, mixed $value, int $ttlSeconds): void
    {
        $file = $this->getFilepath($key);
        $data = [
            'expires_at' => time() + $ttlSeconds,
            'value' => $value,
        ];

        file_put_contents($file, serialize($data), LOCK_EX);
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

    public function getNamespaceVersion(string $namespace): int
    {
        $file = $this->versionsPath . md5($namespace) . '.version';

        if (!file_exists($file)) {
            return 1;
        }

        $value = (int) trim((string) file_get_contents($file));
        return max(1, $value);
    }

    public function bumpNamespaceVersion(string $namespace): void
    {
        $file = $this->versionsPath . md5($namespace) . '.version';
        $next = $this->getNamespaceVersion($namespace) + 1;
        file_put_contents($file, (string) $next, LOCK_EX);
    }

    private function getFilepath(string $key): string
    {
        return $this->cachePath . md5($key) . '.cache';
    }
}
