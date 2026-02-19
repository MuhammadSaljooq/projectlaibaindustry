<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use App\Models\Receivable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
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
        $validated = $request->validated();
        $validated['customer_code'] = $this->generateCustomerCode();

        Customer::create($validated);

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
        $customer->update($request->validated());

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer updated successfully.');
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
        $receivables = Receivable::query()
            ->where('customer_code', $customer->customer_code)
            ->orderByDesc('date')
            ->get();

        $totalOutstanding = $receivables->sum(fn ($r) => (float) $r->amount - (float) $r->received);

        return view('customers.statement', [
            'customer' => $customer,
            'receivables' => $receivables,
            'totalOutstanding' => $totalOutstanding,
        ]);
    }

    private function generateCustomerCode(): string
    {
        $prefix = 'CUST-';
        $maxId = (int) Customer::max('id');
        $code = $prefix . str_pad($maxId + 1, 4, '0', STR_PAD_LEFT);

        if (Customer::where('customer_code', $code)->exists()) {
            $code = $prefix . strtoupper(Str::random(6));
        }

        return $code;
    }
}
