<?php

namespace App\Services;

use RuntimeException;

class JsonStorageService
{
    private string $dir;

    public function __construct(?string $dir = null)
    {
        $this->dir = $dir ?? storage_path('app/private');
    }

    public function read(string $file): array
    {
        $path = $this->path($file);
        if (!file_exists($path)) {
            return [];
        }
        $raw = @file_get_contents($path);
        if ($raw === false || trim($raw) === '') {
            return [];
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    public function write(string $file, array $data): void
    {
        $path = $this->path($file);
        if (!is_dir(dirname($path)) && !@mkdir(dirname($path), 0775, true) && !is_dir(dirname($path))) {
            throw new RuntimeException('Unable to create storage directory.');
        }
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new RuntimeException('Unable to encode JSON.');
        }
        $tmp = $path . '.tmp';
        if (@file_put_contents($tmp, $json, LOCK_EX) === false) {
            throw new RuntimeException('Unable to write data file.');
        }
        if (!@rename($tmp, $path)) {
            @unlink($tmp);
            throw new RuntimeException('Unable to finalize data file.');
        }
    }

    /** @template T
     * @param callable(array):T $callback
     * @return T
     */
    public function mutate(string $file, callable $callback)
    {
        $path = $this->path($file);
        if (!is_dir(dirname($path)) && !@mkdir(dirname($path), 0775, true) && !is_dir(dirname($path))) {
            throw new RuntimeException('Unable to create storage directory.');
        }
        $fp = @fopen($path, 'c+');
        if ($fp === false) {
            throw new RuntimeException('Unable to open data file.');
        }
        try {
            if (!flock($fp, LOCK_EX)) {
                throw new RuntimeException('Unable to lock data file.');
            }
            $raw = stream_get_contents($fp);
            $data = [];
            if (is_string($raw) && trim($raw) !== '') {
                $decoded = json_decode($raw, true);
                $data = is_array($decoded) ? $decoded : [];
            }
            $result = $callback($data);
            $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($json === false) {
                throw new RuntimeException('Unable to encode JSON.');
            }
            rewind($fp);
            ftruncate($fp, 0);
            if (fwrite($fp, $json) === false) {
                throw new RuntimeException('Unable to write data file.');
            }
            fflush($fp);
            return $result;
        } finally {
            flock($fp, LOCK_UN);
            fclose($fp);
        }
    }

    private function path(string $file): string
    {
        $file = basename($file);
        return $this->dir . DIRECTORY_SEPARATOR . $file;
    }
}
