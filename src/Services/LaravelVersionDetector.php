<?php

namespace Dev\EipAgent\Services;

class LaravelVersionDetector
{
    public function detect(string $basePath): string
    {
        $composerFile = $basePath . '/composer.json';
        if (!file_exists($composerFile)) {
            return 'Unknown';
        }

        $composer = json_decode(file_get_contents($composerFile), true);
        
        $laravelVersion = $composer['require']['laravel/framework'] ?? 'Unknown';
        
        if ($laravelVersion !== 'Unknown') {
            // Clean up ^ or ~ from version string
            $laravelVersion = preg_replace('/[\^\~]/', '', $laravelVersion);
            // Extract just the major version
            $parts = explode('.', $laravelVersion);
            return $parts[0] ?? 'Unknown';
        }

        return 'Unknown';
    }
}
