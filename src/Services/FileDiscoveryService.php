<?php

namespace Dev\EipAgent\Services;

use Dev\EipAgent\DTOs\FileResult;
use Dev\EipAgent\DTOs\Metrics;
use Illuminate\Support\Facades\File;

class FileDiscoveryService
{
    public function __construct(
        private FileClassifier $classifier
    ) {}

    /**
     * @return array{files: FileResult[], metrics: Metrics}
     */
    public function discover(string $basePath): array
    {
        $directoriesToScan = [
            $basePath . '/app',
            $basePath . '/routes',
            $basePath . '/config',
            $basePath . '/bootstrap',
            $basePath . '/database',
        ];

        $files = [];
        $metrics = new Metrics();

        foreach ($directoriesToScan as $dir) {
            if (!is_dir($dir)) {
                continue;
            }

            foreach (File::allFiles($dir) as $splFile) {
                if ($splFile->getExtension() !== 'php') {
                    continue;
                }

                $classification = $this->classifier->classify($splFile);
                $metrics->increment($classification);

                $files[] = new FileResult(
                    path: $splFile->getRealPath(),
                    relativePath: str_replace($basePath . '/', '', $splFile->getRealPath()),
                    classification: $classification,
                    content: file_get_contents($splFile->getRealPath())
                );
            }
        }

        return [
            'files' => $files,
            'metrics' => $metrics,
        ];
    }
}
