<?php

namespace Dev\EipAgent\AI\Schemas;

class AIResponseSchemaValidator
{
    private array $strictFields = [
        'summary',
        'risks',
        'recommendations',
        'priority',
    ];

    private array $optionalDefaults = [
        'technical_debt'    => [],
        'refactoring_notes' => [],
        'metrics'           => [],
    ];

    private array $normalizations = [
        'risk'           => 'risks',
        'recommendation' => 'recommendations',
        'debt'           => 'technical_debt',
    ];

    public function validate(string $jsonString): ValidationResult
    {
        // Strip markdown if it exists (e.g. ```json ... ```)
        $jsonString = preg_replace('/^```json\s*|\s*```$/i', '', trim($jsonString));

        $data = json_decode($jsonString, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            return new ValidationResult(false, [], ['Invalid JSON provided']);
        }

        $warnings = [];
        $repairedFields = [];
        $missingFields = [];

        // 1. Normalization
        foreach ($this->normalizations as $badKey => $goodKey) {
            if (isset($data[$badKey]) && !isset($data[$goodKey])) {
                $data[$goodKey] = $data[$badKey];
                unset($data[$badKey]);
                $repairedFields[] = $goodKey;
            }
        }

        // 2. Strict Core Check
        foreach ($this->strictFields as $field) {
            if (!isset($data[$field])) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            return new ValidationResult(false, $data, ['Missing strictly required fields'], $repairedFields, $missingFields);
        }

        // 3. Optional Recovery
        foreach ($this->optionalDefaults as $field => $default) {
            if (!isset($data[$field])) {
                $data[$field] = $default;
                $repairedFields[] = $field;
                $warnings[] = "Auto-filled optional field: $field";
            }
        }

        return new ValidationResult(true, $data, $warnings, $repairedFields, $missingFields);
    }
}
