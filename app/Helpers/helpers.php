<?php

if (!function_exists('app_version')) {
    function app_version(): string
    {
        $path = storage_path('app/version.txt');

        return file_exists($path)
            ? trim(file_get_contents($path))
            : 'dev';
    }
}