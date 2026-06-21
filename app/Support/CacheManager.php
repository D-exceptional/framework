<?php

namespace App\Support;

class CacheManager
{
    // =========================================
    // GENERATE CACHE BUSTED URL
    // =========================================
    public function parse(
        string $filePath
    ): string {

        if (file_exists($filePath)) {
            
            $fileModificationTime = filemtime($filePath);
            
            return $filePath . '?v=' . $fileModificationTime;
        }

        return $filePath; 
    }

    // =========================================
    // GENERATE PAGINATION URL
    // =========================================
    public function pageUrl(
        int $page, 
        string $baseUrl
    ): string {
        
        return $baseUrl . '&page=' . $page;
    }
}
