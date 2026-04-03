<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Date;

if (! function_exists('format_display_date')) {
    /**
     * Format a date for UI/CSV (day/month/year). Returns em dash when null.
     */
    function format_display_date(?DateTimeInterface $value): string
    {
        if ($value === null) {
            return '—';
        }

        return Date::instance($value)
            ->timezone(config('app.timezone'))
            ->format(config('app.display_date_format', 'd/m/Y'));
    }
}

if (! function_exists('format_display_datetime')) {
    /**
     * Format a date-time for UI/CSV. Returns em dash when null.
     */
    function format_display_datetime(?DateTimeInterface $value): string
    {
        if ($value === null) {
            return '—';
        }

        return Date::instance($value)
            ->timezone(config('app.timezone'))
            ->format(config('app.display_datetime_format', 'd/m/Y H:i'));
    }
}

if (! function_exists('format_day_month_year_date')) {
    /**
     * Format a date as day, full month name, year (e.g. "3 April 2026").
     */
    function format_day_month_year_date(?DateTimeInterface $value): string
    {
        if ($value === null) {
            return '—';
        }

        return Date::instance($value)
            ->timezone(config('app.timezone'))
            ->translatedFormat('j F Y');
    }
}

if (! function_exists('parse_filter_date')) {
    /**
     * Parse a date from filter inputs: d/m/Y, j/n/Y, or Y-m-d (bookmarks / redirects).
     * Returns Y-m-d or null when empty or unparseable.
     */
    function parse_filter_date(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $t = trim($value);
        if ($t === '') {
            return null;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $t)) {
            try {
                return Carbon::createFromFormat('Y-m-d', $t)->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }

        foreach (['d/m/Y', 'j/n/Y'] as $fmt) {
            try {
                return Carbon::createFromFormat($fmt, $t)->format('Y-m-d');
            } catch (\Throwable) {
            }
        }

        return null;
    }
}

if (! function_exists('filter_date_input_value')) {
    /**
     * Repopulate a text date filter field as dd/mm/yyyy.
     */
    function filter_date_input_value(?string $value): string
    {
        if ($value === null || trim($value) === '') {
            return '';
        }

        $iso = parse_filter_date(trim($value));
        if ($iso === null) {
            return '';
        }

        return format_display_date(Date::parse($iso));
    }
}

if (! function_exists('parse_list_date_filters')) {
    /**
     * Parse GET list filters from / to or redirect back with validation errors.
     *
     * @return array{0: ?\Illuminate\Http\RedirectResponse, 1: ?string, 2: ?string}
     */
    function parse_list_date_filters(): array
    {
        $fromRaw = request('from');
        $toRaw = request('to');
        $fromStr = is_string($fromRaw) ? trim($fromRaw) : '';
        $toStr = is_string($toRaw) ? trim($toRaw) : '';

        $from = $fromStr !== '' ? parse_filter_date($fromStr) : null;
        $to = $toStr !== '' ? parse_filter_date($toStr) : null;

        $errors = [];
        if ($fromStr !== '' && $from === null) {
            $errors['from'] = 'Enter the date as dd/mm/yyyy.';
        }
        if ($toStr !== '' && $to === null) {
            $errors['to'] = 'Enter the date as dd/mm/yyyy.';
        }

        if ($errors !== []) {
            return [redirect()->back()->withErrors($errors)->withInput(), null, null];
        }

        return [null, $from, $to];
    }
}
