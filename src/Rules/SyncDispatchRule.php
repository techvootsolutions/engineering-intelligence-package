<?php
namespace Dev\EipAgent\Rules;

use Dev\EipAgent\DTOs\FileResult;

class SyncDispatchRule extends BaseRule
{
    /**
     * Flag Job classes that do not implement ShouldQueue.
     * Without ShouldQueue, these jobs run synchronously during the web request.
     *
     * @param FileResult[] $jobs
     */
    public function analyze(array $jobs): array
    {
        $issues = [];

        foreach ($jobs as $job) {
            if (!str_contains($job->content, 'implements ShouldQueue')) {
                $issues[] = $this->issue(
                    type: 'synchronous_job_dispatch',
                    severity: 'warning',
                    file: $job->relativePath,
                    message: "Job class does not implement ShouldQueue and will run synchronously.",
                    impact: "This job blocks the web request lifecycle, potentially causing slow or timed-out responses for users.",
                    recommendation: "Add 'implements ShouldQueue' and configure a queue driver so this job runs in the background.",
                    score: 10
                );
            }
        }

        return $issues;
    }
}
