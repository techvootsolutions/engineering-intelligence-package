<?php

namespace Dev\EipAgent\Scanners;

use Illuminate\Support\Facades\File;

class MetricsScanner
{
    public function scanDirectory(string $path): array
    {
        if (!is_dir($path)) {
            return [];
        }

        return collect(File::allFiles($path))
            ->map(fn ($file) => [
                'name' => $file->getFilename(),
                'path' => $file->getRealPath(),
                'content' => file_get_contents($file->getRealPath()),
                'lines' => count(file($file->getRealPath())),
            ])
            ->toArray();
    }
}