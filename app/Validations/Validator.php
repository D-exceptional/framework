<?php

declare(strict_types=1);

namespace App\Validations;

use App\Exceptions\ValidationException;

class Validator
{
    // =========================================
    // VALIDATE REQUEST DATA AGAINST SET RULES
    // =========================================
    public function validate(array $data, array $rules): void
    {
        $errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;

            // Required check
            if (in_array('required', $fieldRules) && ($value === null || $value === '')) {
                $errors[$field][] = 'Field is required';
                continue;
            }

            // Skip other checks if value missing
            if ($value === null) {
                continue;
            }

            foreach ($fieldRules as $rule) {
                // Handle rules with parameters (min:3, max:10)
                if (strpos($rule, ':') !== false) {
                    [$ruleName, $param] = explode(':', $rule, 2);
                    $param = (int) $param;

                    if ($ruleName === 'min') {
                        if (is_string($value) && strlen($value) < $param) {
                            $errors[$field][] = "Must be at least {$param} characters";
                        }
                        if (is_numeric($value) && $value < $param) {
                            $errors[$field][] = "Must be at least {$param}";
                        }
                        if (is_array($value) && count($value) < $param) {
                            $errors[$field][] = "Must contain at least {$param} items";
                        }
                    }

                    if ($ruleName === 'max') {
                        if (is_string($value) && strlen($value) > $param) {
                            $errors[$field][] = "Must not exceed {$param} characters";
                        }
                        if (is_numeric($value) && $value > $param) {
                            $errors[$field][] = "Must not exceed {$param}";
                        }
                        if (is_array($value) && count($value) > $param) {
                            $errors[$field][] = "Must not exceed {$param} items";
                        }
                    }
                }

                // Basic rules
                elseif ($rule === 'string' && !is_string($value)) {
                    $errors[$field][] = 'Must be a string';
                }

                elseif ($rule === 'number' && !is_numeric($value)) {
                    $errors[$field][] = 'Must be a number';
                }

                elseif ($rule === 'integer' && !filter_var($value, FILTER_VALIDATE_INT)) {
                    $errors[$field][] = 'Must be an integer';
                }

                elseif ($rule === 'array' && (!is_array($value) || empty($value))) {
                    $errors[$field][] = 'Must be a non-empty array';
                }
            }
        }

        // If errors exist, respond immediately
        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
    }
}
