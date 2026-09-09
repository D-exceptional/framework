<?php

declare(strict_types=1);

namespace App\Media;

use Cloudinary\Cloudinary;

class CloudinaryManager
{
    protected Cloudinary $cloudinary;
    protected bool $isLocal;
    protected string $logFile;
    protected string $pemFile;

    public function __construct()
    {
        $config = config('cloudinary');

        $this->cloudinary = new Cloudinary($config);

        $this->initialize();
    }

    // =========================================
    // INITIALIZE CLOUDINARY SETUP
    // =========================================
    public function initialize(): void
    {
        $this->isLocal =
            in_array(
                $_SERVER['SERVER_NAME'] ?? 'localhost',
                ['localhost', '127.0.0.1'],
                true
            )
            || PHP_SAPI === 'cli';

        $this->logFile =
            dirname(__DIR__, 2) .
            '/storage/logs/media-manager.log';

        $this->pemFile =
            dirname(__DIR__, 2) .
            '/storage/cacert.pem';

        $this->writeLog(
            "CloudinaryManager initialized. Log file: {$this->logFile}"
        );

        if (!file_exists($this->pemFile)) {
            $this->writeLog(
                "PEM file not found at {$this->pemFile}"
            );
        }
    }

    // =========================================
    // DETECT FILE TYPE
    // =========================================
    private function detect(
        string $url
    ): string {

        $path = parse_url($url, PHP_URL_PATH);

        $ext = strtolower(
            pathinfo($path, PATHINFO_EXTENSION)
        );

        if (in_array($ext, [
            'jpg',
            'jpeg',
            'png',
            'gif',
            'webp'
        ], true)) {
            return 'image';
        }

        if (in_array($ext, [
            'mp4',
            'webm',
            'avi',
            'mov',
            'mkv'
        ], true)) {
            return 'video';
        }

        return 'raw';
    }

    // =========================================
    // EXTRACT PUBLIC ID
    // =========================================
    private function extract(
        string $url
    ): string {

        $path = parse_url($url, PHP_URL_PATH);

        if (!$path) {
            return '';
        }

        $parts = explode(
            '/',
            trim($path, '/')
        );

        $uploadIndex = array_search(
            'upload',
            $parts,
            true
        );

        if ($uploadIndex === false) {
            return '';
        }

        $publicIdParts = array_slice(
            $parts,
            $uploadIndex + 1
        );

        // Remove Cloudinary version
        if (
            isset($publicIdParts[0]) &&
            preg_match(
                '/^v\d+$/',
                $publicIdParts[0]
            )
        ) {
            array_shift($publicIdParts);
        }

        if (empty($publicIdParts)) {
            return '';
        }

        $lastPart = array_pop($publicIdParts);

        $filename = pathinfo(
            $lastPart,
            PATHINFO_FILENAME
        );

        if ($filename === '') {
            return '';
        }

        $publicIdParts[] = $filename;

        return implode(
            '/',
            $publicIdParts
        );
    }

    // =========================================
    // DELETE SINGLE FILE
    // =========================================
    public function delete(
        string $url
    ): void {

        $this->writeLog(
            "Deleting: {$url}"
        );

        $resourceType = $this->detect($url);
        $publicId     = $this->extract($url);

        if ($publicId === '') {
            $this->writeLog(
                "Invalid public_id from URL: {$url}"
            );

            throw new \RuntimeException(
                'Unable to extract Cloudinary public ID from URL.'
            );
        }

        $options = [
            'resource_type' => $resourceType
        ];

        // =========================================
        // SSL HANDLING
        // =========================================
        if ($this->isLocal) {
            $options['curl_options'] = [
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0
            ];
        } else {
            if (!file_exists($this->pemFile)) {
                $this->writeLog(
                    "SSL certificate missing: {$this->pemFile}"
                );

                throw new \RuntimeException(
                    'SSL certificate not found.'
                );
            }

            $options['curl_options'] = [
                CURLOPT_CAINFO => $this->pemFile,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2
            ];
        }

        try {
            $result = $this->cloudinary
                ->uploadApi()
                ->destroy($publicId, $options);

            $this->writeLog(
                "Delete result: " .
                json_encode($result)
            );

            if (
                ($result['result'] ?? null) === 'ok'
            ) {
                $this->writeLog(
                    "File deleted successfully: {$publicId}"
                );

                return;
            }

            $this->writeLog(
                "File deletion not confirmed: " .
                json_encode($result)
            );

        } catch (\Throwable $e) {
            $this->writeLog(
                "Error deleting {$publicId}: " .
                $e->getMessage()
            );

            throw $e;
        }
    }

    // =========================================
    // DELETE BULK FILES
    // =========================================
    public function deleteBulk(
        array $urls
    ): void {
        
        $grouped = [
            'image' => [],
            'video' => [],
            'raw'   => []
        ];

        foreach ($urls as $url) {
            $publicId = $this->extract($url);

            if ($publicId === '') {
                $this->writeLog(
                    "Unable to extract public ID: {$url}"
                );

                continue;
            }

            $type = $this->detect($url);

            $grouped[$type][] = $publicId;
        }

        $this->deleteGrouped($grouped);
    }

    // =========================================
    // DELETE GROUPED FILES
    // =========================================
    private function deleteGrouped(
        array $grouped
    ): void {

        $results = [];

        foreach ($grouped as $type => $publicIds) {
            if (empty($publicIds)) {
                continue;
            }

            try {
                $result = $this->cloudinary
                    ->adminApi()
                    ->deleteAssets(
                        $publicIds,
                        [
                            'resource_type' => $type
                        ]
                    );

                $results[$type] = $result;

            } catch (\Throwable $e) {
                $results[$type] = [
                    'error' => $e->getMessage()
                ];
            }
        }

        $this->writeLog(
            "Cloudinary bulk delete operation results: " .
            json_encode($results)
        );
    }

    // =========================================
    // WRITE MEDIA LOG
    // =========================================
    private function writeLog(
        mixed $data
    ): void {

        $timestamp = date('Y-m-d H:i:s');

        $message = is_array($data)
            ? json_encode($data, JSON_PRETTY_PRINT)
            : (string) $data;

        $directory = dirname($this->logFile);

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $result = file_put_contents(
            $this->logFile,
            "[{$timestamp}] {$message}" . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );

        if ($result === false) {
            throw new \RuntimeException(
                "Unable to write Cloudinary log: {$this->logFile}"
            );
        }
    }
}