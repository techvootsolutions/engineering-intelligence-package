<?php

namespace Dev\EipAgent\DTOs;

class FileResult
{
    public function __construct(
        public readonly string $path,
        public readonly string $relativePath,
        public readonly string $classification,
        public readonly string $content
    ) {}
}
