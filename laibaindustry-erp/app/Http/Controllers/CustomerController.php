<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use App\Models\CustomerLedgerEntry;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class CustomerController extends Controller
{
    /**
     * Whether the customers table has the ledger-related columns.
     * Checked once per request and cached to avoid repeated DB schema queries.
     */
    private function hasLedgerColumns(): bool
    {
        static $result = null;
        if ($result === null) {
            $result = Schema::hasColumn('customers', 'opening_balance');
        }
        return $result;
    }

    /**
     * Strip ledger fields from the data array if the live DB
     * doesn't have those columns yet.
     */
    private function prepareSaveData(array $data): array
    {
        if (! $this->hasLedgerColumns()) {
            unset($data['opening_balance'], $data['opening_balance_date']);
        } else {
            $data['opening_balance'] = $data['opening_balance'] ?? 0;
        }
        return $data;
    }

    public function index(): View
    {
        $customers = Customer::query()
            ->orderBy('customer_name')
            ->paginate(15);

        return view('customers.index', ['customers' => $customers]);
    }

    public function create(): View
    {
        return view('customers.create');
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        Customer::create($this->prepareSaveData($request->validated()));

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer added successfully.');
    }

    public function show(Customer $customer): RedirectResponse
    {
        return redirect()->route('customers.edit', $customer);
    }

    public function edit(Customer $customer): View
    {
        return view('customers.edit', ['customer' => $customer]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $oldCode = $customer->customer_code;
        $oldName = $customer->customer_name;
        $newCode = $request->validated()['customer_code'];
        $newName = $request->validated()['customer_name'];

        try {
            DB::beginTransaction();

            $customer->update($this->prepareSaveData($request->validated()));

            $cascade = [];
            if ($newCode !== $oldCode) {
                $cascade['customer_code'] = $newCode;
            }
            if ($newName !== $oldName) {
                $cascade['customer_name'] = $newName;
            }

            if (! empty($cascade) && $oldCode !== null) {
                foreach (['sales', 'receivables', 'purchases', 'payables'] as $table) {
                    DB::table($table)
                        ->where('customer_code', $oldCode)
                        ->update($cascade);
                }
            }

            DB::commit();

            return redirect()
                ->route('customers.index')
                ->with('success', 'Customer updated successfully across all records.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    /**
     * Get statement data for a customer (ledger rows, balances, totals).
     */
    private function getStatementData(Customer $customer): array
    {
        $hasLedger = $this->hasLedgerColumns()
            && Schema::hasTable('customer_ledger_entries');

        $openingBalance = $hasLedger ? (float) $customer->opening_balance : 0;
        $runningBalance = $openingBalance;
        $totalDebit     = 0;
        $totalCredit    = 0;
        $ledgerRows     = [];

        if ($hasLedger) {
            $entries = CustomerLedgerEntry::where('customer_id', $customer->id)
                ->orderBy('date')
                ->orderBy('id')
                ->get();

            foreach ($entries as $entry) {
                $debit  = (float) $entry->debit;
                $credit = (float) $entry->credit;

                $runningBalance += $debit - $credit;
                $totalDebit     += $debit;
                $totalCredit    += $credit;

                $ledgerRows[] = [
                    'date'            => $entry->date,
                    'description'     => $entry->description,
                    'reference'       => $entry->reference,
                    'debit'           => $debit,
                    'credit'          => $credit,
                    'running_balance' => $runningBalance,
                    'source_type'     => $entry->source_type,
                ];
            }
        }

        return [
            'customer'       => $customer,
            'openingBalance' => $openingBalance,
            'ledgerRows'     => $ledgerRows,
            'totalDebit'     => $totalDebit,
            'totalCredit'    => $totalCredit,
            'closingBalance' => $runningBalance,
        ];
    }

    public function statement(Customer $customer): View
    {
        $data = $this->getStatementData($customer);

        return view('customers.statement', $data);
    }

    public function statementPdf(Customer $customer): Response
    {
        $data = $this->getStatementData($customer);

        $pdf = Pdf::loadView('customers.statement-pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false);

        $filename = sprintf(
            'statement-%s-%s.pdf',
            \Illuminate\Support\Str::slug($customer->customer_name),
            now()->format('Y-m-d')
        );

        return $pdf->download($filename);
    }
}
