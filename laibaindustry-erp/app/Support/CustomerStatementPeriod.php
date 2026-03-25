<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Optional inclusive calendar date range for customer statements (app timezone).
 */
final class CustomerStatementPeriod
{
    /** Maximum inclusive span between from and to (months). */
    public const MAX_RANGE_MONTHS = 36;

    public function __construct(
        public readonly CarbonImmutable $fromStart,
        public readonly CarbonImmutable $toEnd,
    ) {}

    /**
     * @return array{0: bool, 1: ?self} [hasFilter, period or null]
     *
     * @throws ValidationException
     */
    public static function validateAndParse(Request $request): array
    {
        $from = self::normalizeInput($request->input('from'));
        $to = self::normalizeInput($request->input('to'));

        if ($from === null && $to === null) {
            return [false, null];
        }

        $validator = Validator::make(
            ['from' => $from, 'to' => $to],
            [
                'from' => ['required', 'date'],
                'to' => ['required', 'date', 'after_or_equal:from'],
            ],
            [
                'from.required' => 'Enter a start date when filtering by period.',
                'to.required' => 'Enter an end date when filtering by period.',
                'to.after_or_equal' => 'The end date must be on or after the start date.',
            ]
        );

        $validator->after(function (\Illuminate\Validation\Validator $v): void {
            $f = $v->getData()['from'] ?? null;
            $t = $v->getData()['to'] ?? null;
            if (! is_string($f) || ! is_string($t)) {
                return;
            }
            $tz = config('app.timezone');
            $fromStart = CarbonImmutable::parse($f, $tz)->startOfDay();
            $toDay = CarbonImmutable::parse($t, $tz)->startOfDay();
            $latestAllowed = $fromStart->addMonthsNoOverflow(self::MAX_RANGE_MONTHS);
            if ($toDay->gt($latestAllowed)) {
                $v->errors()->add(
                    'to',
                    'The selected period may not exceed '.self::MAX_RANGE_MONTHS.' months.'
                );
            }
        });

        $validator->validate();

        $tz = config('app.timezone');

        return [
            true,
            new self(
                CarbonImmutable::parse($from, $tz)->startOfDay(),
                CarbonImmutable::parse($to, $tz)->endOfDay(),
            ),
        ];
    }

    private static function normalizeInput(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $s = is_string($value) ? trim($value) : '';

        return $s === '' ? null : $s;
    }
}
