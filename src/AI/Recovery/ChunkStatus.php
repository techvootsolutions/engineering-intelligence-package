<?php

namespace Dev\EipAgent\AI\Recovery;

enum ChunkStatus: string
{
    case SUCCESS = 'success';
    case FAILED = 'failed';
    case PARTIAL = 'partial';
    case SKIPPED = 'skipped';
    case RETRYING = 'retrying';
}
