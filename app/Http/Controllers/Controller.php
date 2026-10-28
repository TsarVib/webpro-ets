<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * Usernames and emails are stored lowercase, so "Demo@Example.com" and "demo@example.com" are one account.
     * Runs before validation so the unique rules compare like with like.
     */
    protected function lowercaseIdentity(Request $request): void
    {
        foreach (['username', 'email'] as $field) {
            if (is_string($value = $request->input($field))) {
                $request->merge([$field => mb_strtolower($value)]);
            }
        }
    }

    /**
     * Neutralise spreadsheet formulas so user text like "=HYPERLINK(...)" is shown, not executed (OWASP CSV injection).
     *
     * @param  list<string|int|null>  $values
     * @return list<string|int|null>
     */
    protected function csvRow(array $values): array
    {
        return array_map(
            fn (string|int|null $value): string|int|null => is_string($value) && preg_match('/^[=+\-@\t\r]/', $value) === 1
                ? "'".$value
                : $value,
            $values,
        );
    }
}
