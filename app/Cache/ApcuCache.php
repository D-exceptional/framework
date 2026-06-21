<?php

namespace App\Cache;

use App\Contracts\CacheInterface;

class ApcuCache implements CacheInterface
{
    // =========================================
    // GET APCU CACHE DATA
    // =========================================
    public function get(
        string $key, 
        mixed $default = null
    ): mixed {

        $success = false;

        $value = apcu_fetch($key, $success);

        return $success ? $value : $default;
    }

    // =========================================
    // SET APCU CACHE DATA
    // =========================================
    public function set(
        string $key, 
        mixed $value, 
        int $ttl = 60
    ): bool {

        return apcu_store($key, $value, $ttl);
    }

    // =========================================
    // DELETE APCU CACHE DATA
    // =========================================
    public function delete(
        string $key
    ): bool {

        return apcu_delete($key);
    }
}