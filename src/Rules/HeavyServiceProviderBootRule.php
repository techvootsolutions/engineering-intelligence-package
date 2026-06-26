<?php
namespace Techvoot\EIP\Rules;

class HeavyServiceProviderBootRule extends BaseRule
{
    public function analyze(array $providers): array
    {
        $issues = [];
        foreach ($providers as $provider) {
            if (preg_match('/public function boot\(\)\s*\{([^}]+)\}/is', $provider->content, $match)) {
                $bootContent = $match[1];
                // Measure lines or queries. For now, check if boot has too many lines
                $lines = substr_count($bootContent, "\n") + 1;
                
                // Or check for obvious DB queries in boot
                $hasQueries = preg_match('/(::|->)where\(|(::|->)find\(|(::|->)first\(|(::|->)get\(/i', $bootContent);
                
                if ($lines > 20 || $hasQueries) {
                    $issues[] = $this->issue(
                        type: 'heavy_service_provider_boot',
                        file: $provider->relativePath,
                        message: 'Service Provider has a heavy boot() method.',
                        impact: 'Slows down application bootstrapping for every request.',
                        recommendation: 'Move expensive operations out of boot() or defer the provider if possible.'
                    );
                }
            }
        }
        return $issues;
    }
}
