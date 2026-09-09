<?php

declare(strict_types=1);

namespace App\Cache\Drivers;

use App\Contracts\CacheInterface;

class FileDriver implements CacheInterface
{
    protected string $path;

    public function __construct()
    {
        $this->path = dirname(__DIR__, 3) . '/storage/framework/cache/files/';
    }

    // =========================================
    // GET FILE CACHE DATA
    // =========================================
    public function get(
        string $key, 
        mixed $default = null
    ): mixed {

        $file = $this->path . md5($key);

        if (!file_exists($file)) {
            return $default;
        }

        $data = unserialize(file_get_contents($file));

        if (time() > $data['expires']) {
            unlink($file);

            return $default;
        }

        return $data['value'];
    }

    // =========================================
    // SET FILE CACHE DATA
    // =========================================
    public function set(
        string $key,
        mixed $value,
        int $ttl = 60
    ): bool {

        $file = $this->path . md5($key);

        $data = [
            'expires' => time() + $ttl,
            'value'   => $value
        ];

        return file_put_contents(
            $file,
            serialize($data)
        ) !== false;
    }

    // =========================================
    // DELETE FILE CACHE DATA
    // =========================================
    public function delete(string $key): bool
    {
        $file = $this->path . md5($key);

        return file_exists($file)
            ? unlink($file)
            : true;
    }

    // =========================================
    // REMEMBER FILE CACHE DATA
    // =========================================
    public function remember(
        string $key,
        callable $callback,
        int $ttl = 60
    ): mixed {

        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();

        $this->set($key, $value, $ttl);

        return $value;
    }
}