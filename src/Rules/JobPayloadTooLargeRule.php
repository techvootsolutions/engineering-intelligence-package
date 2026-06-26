<?php
namespace Techvoot\EIP\Rules;

class JobPayloadTooLargeRule extends BaseRule
{
    public function analyze(array $jobs): array
    {
        $issues = [];
        foreach ($jobs as $job) {
            // Check for Eloquent models being injected without SerializesModels, or arrays/large objects
            
            // First, does it use SerializesModels trait?
            $hasSerializesModels = preg_match('/use\s+.*?SerializesModels;/', $job->content);
            
            // Check constructor for array types or generic objects
            if (preg_match('/__construct\s*\((.*?)\)/s', $job->content, $matches)) {
                $deps = $matches[1];
                if (preg_match('/array\s+\$/', $deps) && !$hasSerializesModels) {
                    $issues[] = $this->issue(
                        type: 'job_payload_too_large',
                        file: $job->relativePath,
                        message: 'Job constructor accepts an array but does not use SerializesModels or might contain huge data.',
                        impact: 'Large payloads cause Redis/Queue memory exhaustion and timeouts.',
                        recommendation: 'Pass only IDs or simple data to Jobs, and retrieve models inside the handle() method.'
                    );
                }
            }
        }
        return $issues;
    }
}
