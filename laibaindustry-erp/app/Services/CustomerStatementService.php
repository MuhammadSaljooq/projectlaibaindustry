<?php

namespace App\Services;

use App\Models\CustomerLedgerEntry;
use Carbon\Carbon;

class CustomerStatementService
{
    /**
     * @return array{opening_balance: float, lines: array<int, array{date: string, type: string, reference: string, debit: float, credit: float, running_balance: float, payment_type: string|null}>, closing_balance: float}
     */
    public function getStatementLines(string $customerCode, ?Carbon $fromDate = null, ?Carbon $toDate = null): array
    {
        $query = CustomerLedgerEntry::query()
            ->where('customer_code', $customerCode)
            ->orderBy('entry_date')
            ->orderBy('id');

        if ($fromDate !== null) {
            $query->where('entry_date', '>=', $fromDate->copy()->startOfDay());
        }
        if ($toDate !== null) {
            $query->where('entry_date', '<=', $toDate->copy()->endOfDay());
        }

        $openingBalance = 0.0;
        if ($fromDate !== null) {
            $openingBalance = (float) CustomerLedgerEntry::query()
                ->where('customer_code', $customerCode)
                ->where('entry_date', '<', $fromDate->copy()->startOfDay())
                ->selectRaw('COALESCE(SUM(debit - credit), 0) as total')
                ->value('total');
        }

        $entries = $query->get();
        $runningBalance = $openingBalance;
        $lines = [];

        foreach ($entries as $entry) {
            $debit = (float) $entry->debit;
            $credit = (float) $entry->credit;
            $runningBalance += $debit - $credit;

            $lines[] = [
                'date' => $entry->entry_date->format('Y-m-d H:i'),
                'type' => $entry->type,
                'reference' => $entry->reference ?? '-',
                'debit' => $debit,
                'credit' => $credit,
                'running_balance' => round($runningBalance, 2),
                'payment_type' => $entry->payment_type,
            ];
        }

        $closingBalance = empty($lines) ? $openingBalance : $lines[array_key_last($lines)]['running_balance'];

        $totalDebit = array_sum(array_column($lines, 'debit'));
        $totalCredit = array_sum(array_column($lines, 'credit'));

        return [
            'opening_balance' => round($openingBalance, 2),
            'lines' => $lines,
            'closing_balance' => round($closingBalance, 2),
            'total_debit' => round($totalDebit, 2),
            'total_credit' => round($totalCredit, 2),
        ];
    }
}
