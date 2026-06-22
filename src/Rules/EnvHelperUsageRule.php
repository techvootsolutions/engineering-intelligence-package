<?php
namespace Dev\EipAgent\Rules;

use Dev\EipAgent\DTOs\FileResult;

class EnvHelperUsageRule extends BaseRule
{
    /**
     * Detect use of the env() helper outside of config files.
     * When config is cached (php artisan config:cache), env() returns null everywhere except config files.
     *
     * @param FileResult[] $files
     */
    public function analyze(array $files): array
    {
        $issues = [];

        foreach ($files as $file) {
            // env() is allowed inside config files — that's its proper home
            if ($file->classification === 'configs' || str_contains($file->relativePath, 'config/')) {
                continue;
            }

            if (preg_match('/\benv\([\'"][^\'"]+[\'"]\)/', $file->content)) {
                $issues[] = $this->issue(
                    type: 'env_helper_outside_config',
                    severity: 'high',
                    file: $file->relativePath,
                    message: "The env() helper is used directly in application code outside of a config file.",
                    impact: "After running 'php artisan config:cache' in production, env() always returns null. This will silently break your application.",
                    recommendation: "Move the value to a config file (e.g. config/services.php) and read it using config('services.your_key') instead.",
                    score: 20
                );
            }
        }

        return $issues;
    }
}
