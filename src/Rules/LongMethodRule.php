<?php
namespace Dev\EipAgent\Rules;

use Dev\EipAgent\Rules\BaseRule;

class LongMethodRule extends BaseRule
{
    private int $maxMethodLines;

    public function __construct(int $maxMethodLines = 50)
    {
        $this->maxMethodLines = $maxMethodLines;
    }

    public function analyze(array $controllers): array
    {
        $issues = [];

        foreach ($controllers as $controller) {
            $content = file_get_contents(
                $controller->path
            );
            $tokens = token_get_all($content);
            $methods = $this->extractMethods($tokens);

            foreach ($methods as $method) {
                if ($method['lines'] > $this->maxMethodLines) {

                    $issues[] = $this->issue(
                        type: 'long_method',
                        severity: 'warning',
                        file: $controller->relativePath,
                        message: "Method {$method['name']}() has {$method['lines']} lines.",
                        impact: 'Large methods are harder to maintain.',
                        recommendation: 'Split method into smaller methods.',
                        score: 5,
                        extra: [
                            'method' => $method['name'],
                            'lines'  => $method['lines'],
                        ],
                        line: $method['start_line'] ?? 0
                    );
                }
            }
        }

        return $issues;
    }

    private function extractMethods(array $tokens): array
    {
        $methods = [];
        $count = count($tokens);

        for ($i = 0; $i < $count; $i++) {
            if (is_array($tokens[$i]) && $tokens[$i][0] === T_FUNCTION) {
                $methodName = null;
                for ($j = $i + 1; $j < $count; $j++) {
                    if (
                        is_array($tokens[$j]) &&
                        $tokens[$j][0] === T_STRING
                    ) {
                        $methodName = $tokens[$j][1];
                        $startLine = $tokens[$j][2];
                        break;
                    }
                }

                if (!$methodName) {
                    continue;
                }

                $braceLevel = 0;
                $methodStarted = false;
                $endLine = $startLine;

                for ($k = $j; $k < $count; $k++) {
                    $token = $tokens[$k];
                    if ($token === '{') {
                        $braceLevel++;
                        $methodStarted = true;
                    }

                    if ($token === '}') {
                        $braceLevel--;
                        if ($methodStarted && $braceLevel === 0) {
                            $previousToken = $tokens[$k - 1] ?? null;
                            if (is_array($previousToken)) {
                                $endLine = $previousToken[2];
                            }

                            break;
                        }
                    }

                    if (is_array($token)) {
                        $endLine = $token[2];
                    }
                }

                $methods[] = [
                    'name'       => $methodName,
                    'start_line' => $startLine,
                    'lines'      => max(
                        1,
                        $endLine - $startLine + 1
                    ),
                ];
            }
        }

        return $methods;
    }
}
