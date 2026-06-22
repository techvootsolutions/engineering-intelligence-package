<?php
namespace Dev\EipAgent\Rules;

use Dev\EipAgent\DTOs\FileResult;

class MissingListenerRule extends BaseRule
{
    /**
     * Flag Event classes that contain a handle() method.
     * The handle() method belongs in a Listener, not the Event itself.
     *
     * @param FileResult[] $events
     */
    public function analyze(array $events): array
    {
        $issues = [];

        foreach ($events as $event) {
            if (str_contains($event->content, 'public function handle')) {
                $issues[] = $this->issue(
                    type: 'event_contains_handler',
                    severity: 'info',
                    file: $event->relativePath,
                    message: "Event class contains a handle() method, which belongs in a Listener class.",
                    impact: "Mixing Event definition and Listener logic in the same class violates separation of concerns and makes the code harder to reuse.",
                    recommendation: "Create a dedicated Listener class and move the handle() logic there. Register it in EventServiceProvider.",
                    score: 5
                );
            }
        }

        return $issues;
    }
}
