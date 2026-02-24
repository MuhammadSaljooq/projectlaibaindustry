<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use App\Services\CustomerStatementService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $query = Customer::query();
        if ($search = $request->query('search')) {
            $query->search($search);
        }
        $customers = $query->orderBy('customer_name')->paginate(15)->withQueryString();

        return view('customers.index', ['customers' => $customers]);
    }

    public function create(): View
    {
        return view('customers.create');
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['customer_code'] = trim($validated['customer_code']);

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
        $validated = $request->validated();
        if (array_key_exists('customer_code', $validated) && trim((string) ($validated['customer_code'] ?? '')) === '') {
            unset($validated['customer_code']);
        }
        $customer->update($validated);

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

    public function statement(Request $request, Customer $customer): View
    {
        $fromDate = $request->query('from_date') ? Carbon::parse($request->query('from_date')) : null;
        $toDate = $request->query('to_date') ? Carbon::parse($request->query('to_date')) : null;

        $service = app(CustomerStatementService::class);
        $data = $service->getStatementLines($customer->customer_code, $fromDate, $toDate);

        return view('customers.statement', [
            'customer' => $customer,
            'opening_balance' => $data['opening_balance'],
            'lines' => $data['lines'],
            'closing_balance' => $data['closing_balance'],
            'total_debit' => $data['total_debit'] ?? 0,
            'total_credit' => $data['total_credit'] ?? 0,
            'from_date' => $request->query('from_date'),
            'to_date' => $request->query('to_date'),
        ]);
    }
}
