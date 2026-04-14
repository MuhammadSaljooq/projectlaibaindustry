<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateBankStatementEntryRequest;
use App\Models\BankStatementEntry;
use App\Models\Currency;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BankStatementController extends Controller
{
    public function index(): View
    {
        $inflows = BankStatementEntry::query()
            ->where('flow_type', BankStatementEntry::FLOW_INFLOW)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();

        $outflows = BankStatementEntry::query()
            ->where('flow_type', BankStatementEntry::FLOW_OUTFLOW)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();

        $currencySymbol = Currency::query()->where('is_default', true)->value('symbol') ?? '$';
        $inflowTotal = (float) $inflows->sum('amount');
        $outflowTotal = (float) $outflows->sum('amount');

        return view('bank-statement.index', [
            'inflows' => $inflows,
            'outflows' => $outflows,
            'currencySymbol' => $currencySymbol,
            'inflowTotal' => $inflowTotal,
            'outflowTotal' => $outflowTotal,
            'netTotal' => $inflowTotal - $outflowTotal,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if ($request->user()?->role === 'viewer') {
            abort(403);
        }

        $bag = $request->input('flow_type') === BankStatementEntry::FLOW_OUTFLOW
            ? 'storeOutflow'
            : 'storeInflow';

        $validator = Validator::make($request->all(), [
            'flow_type' => ['required', 'string', Rule::in(BankStatementEntry::flowTypes())],
            'transaction_date' => ['required', 'date'],
            'company_name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('bank-statement.index')
                ->withErrors($validator, $bag)
                ->withInput();
        }

        BankStatementEntry::create($validator->validated());

        return redirect()
            ->route('bank-statement.index')
            ->with('success', 'Entry added.');
    }

    public function edit(BankStatementEntry $bank_statement_entry): View
    {
        return view('bank-statement.edit', [
            'entry' => $bank_statement_entry,
            'currencySymbol' => Currency::query()->where('is_default', true)->value('symbol') ?? '$',
        ]);
    }

    public function update(UpdateBankStatementEntryRequest $request, BankStatementEntry $bank_statement_entry): RedirectResponse
    {
        $bank_statement_entry->update($request->validated());

        return redirect()
            ->route('bank-statement.index')
            ->with('success', 'Entry updated.');
    }

    public function destroy(BankStatementEntry $bank_statement_entry): RedirectResponse
    {
        if (request()->user()?->role === 'viewer') {
            abort(403);
        }

        $bank_statement_entry->delete();

        return redirect()
            ->route('bank-statement.index')
            ->with('success', 'Entry deleted.');
    }
}
