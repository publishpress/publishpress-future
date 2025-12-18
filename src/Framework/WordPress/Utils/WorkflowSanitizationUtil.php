<?php

namespace PublishPress\Future\Framework\WordPress\Utils;

class WorkflowSanitizationUtil
{
    public function sanitizeWorkflowData($data)
    {
        if (is_array($data)) {
            $sanitized = [];
            foreach ($data as $key => $value) {
                $sanitizedKey = $this->sanitizeWorkflowKey($key);

                if (is_array($value)) {
                    $sanitized[$sanitizedKey] = $this->sanitizeWorkflowData($value);
                } elseif (is_string($value)) {
                    $sanitized[$sanitizedKey] = sanitize_text_field($value);
                } else {
                    // Preserve booleans, numbers, null as-is
                    $sanitized[$sanitizedKey] = $value;
                }
            }
            return $sanitized;
        }

        return is_string($data) ? sanitize_text_field($data) : $data;
    }

    private function sanitizeWorkflowKey($key)
    {
        /**
         * Sanitize keys while preserving JSON Logic operators and camelCase keys.
         *
         * JSON Logic uses operators like ==, !=, >, <, >=, <= as keys in the data structure.
         * These are data, not executable code, so we need to preserve them while removing
         * truly dangerous characters that could be used for injection attacks.
         *
         * - If the key is exactly a valid JSON Logic operator, return it as-is
         * - Otherwise, proceed with normal sanitization (removes dangerous chars including operators)
         */

        // Whitelist of valid JSON Logic operators that contain =, !, >, or <
        $validJsonLogicOperators = [
            '==', '===', '!=', '!==',  // Equality operators
            '>', '<', '>=', '<=',      // Comparison operators
            '!', '!!',                 // Logical operators
        ];

        // If the key is exactly a valid JSON Logic operator, return it early
        if (in_array($key, $validJsonLogicOperators, true)) {
            return $key;
        }

        // Proceed with normal sanitization
        // For valid camelCase keys (postId, postType, etc.), str_replace and preg_replace
        // will return the original string quickly when no dangerous characters are found.
        // Remove dangerous characters that could be used for code injection:
        // - Quotes (single and double) - could break out of JSON/string contexts
        // - Backslashes - escape sequences
        // - Forward slashes - path traversal
        // - Semicolons - command injection
        // - Parentheses - function calls
        // - Dollar signs - variable references, potential code injection
        // - Operator characters (=, !, >, <) if not part of a valid operator
        $dangerous = ['"', "'", '\\', '/', ';', '(', ')', '$', '=', '!', '>', '<'];
        $sanitized = str_replace($dangerous, '', $key);

        // Remove control characters and null bytes for additional safety
        $sanitized = preg_replace('/[\x00-\x1F\x7F]/', '', $sanitized);

        return $sanitized;
    }

    public function sanitizeWorkflows($workflows)
    {
        if (!is_array($workflows)) {
            return [];
        }

        $sanitized = [];
        foreach ($workflows as $workflow) {
            if (!is_array($workflow)) {
                continue;
            }

            $sanitized[] = $this->sanitizeWorkflowData($workflow);
        }

        return $sanitized;
    }
}
