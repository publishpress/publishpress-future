<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Engine;

class Cache
{
    private string $cacheDirPath;
    private string $cacheFilePath;
    private array $cacheData;

    public function __construct()
    {
        $this->cacheDirPath = WP_CONTENT_DIR . '/workflows/cache';
        $this->cacheFilePath = $this->cacheDirPath . '/index.php';

        if (!file_exists($this->cacheDirPath)) {
            mkdir($this->cacheDirPath, 0755, true);
        }

        if (!file_exists($this->cacheFilePath)) {
            $this->writeCacheData([]);
        }

        $this->cacheData = $this->loadCacheData();
    }

    /**
     * Loads cache data from PHP file securely
     *
     * @return array
     */
    private function loadCacheData(): array
    {
        // Validate file path to prevent directory traversal attacks
        $realCacheFilePath = realpath($this->cacheFilePath);
        $realCacheDirPath = realpath($this->cacheDirPath);

        if ($realCacheFilePath === false || $realCacheDirPath === false) {
            return [];
        }

        // Ensure the cache file is within the cache directory
        if (strpos($realCacheFilePath, $realCacheDirPath) !== 0) {
            return [];
        }

        // Validate file is readable
        if (!is_readable($realCacheFilePath)) {
            return [];
        }

        // Use require with error handling
        try {
            $data = require $realCacheFilePath;

            // Validate that the returned value is an array
            if (!is_array($data)) {
                // If invalid data, return empty array but don't write to file
                return [];
            }

            return $data;
        } catch (\Throwable $e) {
            // If there's any error loading the file, return empty array but don't write to file
            return [];
        }
    }

    /**
     * Writes cache data to PHP file securely
     *
     * @param array $data
     * @return bool
     */
    private function writeCacheData(array $data): bool
    {
        // Generate PHP code that returns the array
        $phpCode = '<?php' . PHP_EOL;
        $phpCode .= 'if (!defined(\'ABSPATH\')) exit;' . PHP_EOL;
        $phpCode .= 'return ' . var_export($data, true) . ';' . PHP_EOL;

        // Use file_put_contents with LOCK_EX for atomic writes
        $result = file_put_contents($this->cacheFilePath, $phpCode, LOCK_EX);

        // Set secure file permissions (readable/writable by owner only)
        if ($result !== false && file_exists($this->cacheFilePath)) {
            chmod($this->cacheFilePath, 0600);
        }

        return $result !== false;
    }

    /**
     * Gets the cache data
     *
     * @return array
     */
    public function getCacheData(): array
    {
        return $this->cacheData;
    }

    /**
     * Sets cache for a workflow
     *
     * @param int|string $workflowId
     * @param mixed $workflow
     * @return bool
     */
    public function setCache($workflowId, $workflow): bool
    {
        $this->cacheData[$workflowId] = $workflow;
        return $this->writeCacheData($this->cacheData);
    }

    public function clearCache(): bool
    {
        return $this->writeCacheData([]);
    }
}
