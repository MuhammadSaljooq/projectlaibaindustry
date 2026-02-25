<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use App\Models\CustomerLedgerEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CustomerController extends Controller
{
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
        $data = $request->validated();
        $data['opening_balance'] = $data['opening_balance'] ?? 0;

        Customer::create($data);

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

            $data = $request->validated();
            $data['opening_balance'] = $data['opening_balance'] ?? 0;
            $customer->update($data);

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

    public function statement(Customer $customer): View
    {
        $entries = CustomerLedgerEntry::where('customer_id', $customer->id)
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        $openingBalance = (float) $customer->opening_balance;
        $runningBalance = $openingBalance;
        $totalDebit     = 0;
        $totalCredit    = 0;

        $ledgerRows = [];

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

        return view('customers.statement', [
            'customer'        => $customer,
            'openingBalance'  => $openingBalance,
            'ledgerRows'      => $ledgerRows,
            'totalDebit'      => $totalDebit,
            'totalCredit'     => $totalCredit,
            'closingBalance'  => $runningBalance,
        ]);
    }
}
