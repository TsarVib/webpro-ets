<?php

namespace App\Http\Controllers;

abstract class Controller
{
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
