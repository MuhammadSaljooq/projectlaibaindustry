<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class QuotationController extends Controller
{
    public function index(): View
    {
        $quotations = Quotation::query()->latest()->paginate(20);

        return view('quotations.index', compact('quotations'));
    }

    public function create(): View
    {
        return view('quotations.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'quotation_number' => [
                'required',
                'string',
                'max:40',
                Rule::unique('quotations', 'quotation_number'),
            ],
            'quotation_date' => ['required', 'date'],
            'expiration_date' => ['nullable', 'date', 'after_or_equal:quotation_date'],
            'salesperson' => ['nullable', 'string', 'max:255'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_vat_number' => ['nullable', 'string', 'max:100'],
            'customer_cr_number' => ['nullable', 'string', 'max:100'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_address' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $quotation = Quotation::create($data);

        foreach ($data['items'] as $index => $itemData) {
            $quotation->items()->create([
                'sort_order' => $index,
                'description' => $itemData['description'],
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['unit_price'],
                'tax_rate' => $itemData['tax_rate'],
            ]);
        }

        $quotation->recalculateTotals();

        return redirect()->route('quotations.show', $quotation)
            ->with('success', 'Quotation created successfully.');
    }

    public function show(Quotation $quotation): View
    {
        $quotation->load('items');

        return view('quotations.show', compact('quotation'));
    }

    public function edit(Quotation $quotation): View
    {
        $quotation->load('items');

        return view('quotations.edit', compact('quotation'));
    }

    public function update(Request $request, Quotation $quotation): RedirectResponse
    {
        $data = $request->validate([
            'quotation_number' => [
                'required',
                'string',
                'max:40',
                Rule::unique('quotations', 'quotation_number')->ignore($quotation->id),
            ],
            'quotation_date' => ['required', 'date'],
            'expiration_date' => ['nullable', 'date'],
            'salesperson' => ['nullable', 'string', 'max:255'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_vat_number' => ['nullable', 'string', 'max:100'],
            'customer_cr_number' => ['nullable', 'string', 'max:100'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_address' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $quotation->update($data);

        $quotation->items()->delete();
        foreach ($data['items'] as $index => $itemData) {
            $quotation->items()->create([
                'sort_order' => $index,
                'description' => $itemData['description'],
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['unit_price'],
                'tax_rate' => $itemData['tax_rate'],
            ]);
        }

        $quotation->recalculateTotals();

        return redirect()->route('quotations.show', $quotation)
            ->with('success', 'Quotation updated successfully.');
    }

    public function destroy(Quotation $quotation): RedirectResponse
    {
        $quotation->delete();

        return redirect()->route('quotations.index')
            ->with('success', 'Quotation deleted.');
    }

    public function pdf(Quotation $quotation): Response|RedirectResponse
    {
        $quotation = Quotation::query()->with('items')->findOrFail($quotation->getKey());

        $fontsDir = storage_path('fonts');
        if (! is_dir($fontsDir)) {
            @mkdir($fontsDir, 0755, true);
        }

        try {
            $pdf = Pdf::loadView('quotations.pdf', compact('quotation'))
                ->setPaper('a4', 'portrait')
                ->setOption('isHtml5ParserEnabled', true)
                ->setOption('isRemoteEnabled', false)
                ->setOption('defaultFont', 'DejaVu Sans')
                ->setOption('fontDir', $fontsDir)
                ->setOption('fontCache', $fontsDir)
                ->setOption('dpi', 150)
                ->setOption('isFontSubsettingEnabled', false);

            $slug = Str::slug($quotation->quotation_number);
            if ($slug === '') {
                $slug = 'quotation';
            }
            $filename = 'quotation-'.$quotation->id.'-'.$slug.'.pdf';

            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
                'Cache-Control' => 'private, no-store, no-cache, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
                'Expires' => 'Sat, 01 Jan 2000 00:00:00 GMT',
            ]);
        } catch (\Throwable $e) {
            Log::error('Quotation PDF failed', ['quotation' => $quotation->id, 'error' => $e->getMessage()]);

            return redirect()->route('quotations.show', $quotation)
                ->with('error', 'PDF generation failed: '.$e->getMessage());
        }
    }

    public function preview(Quotation $quotation): Response|RedirectResponse
    {
        $quotation = Quotation::query()->with('items')->findOrFail($quotation->getKey());

        $fontsDir = storage_path('fonts');
        if (! is_dir($fontsDir)) {
            @mkdir($fontsDir, 0755, true);
        }

        try {
            $pdf = Pdf::loadView('quotations.pdf', compact('quotation'))
                ->setPaper('a4', 'portrait')
                ->setOption('isHtml5ParserEnabled', true)
                ->setOption('isRemoteEnabled', false)
                ->setOption('defaultFont', 'DejaVu Sans')
                ->setOption('fontDir', $fontsDir)
                ->setOption('fontCache', $fontsDir)
                ->setOption('dpi', 150)
                ->setOption('isFontSubsettingEnabled', false);

            $slug = Str::slug($quotation->quotation_number);
            if ($slug === '') {
                $slug = 'quotation';
            }
            $filename = 'quotation-'.$quotation->id.'-'.$slug.'.pdf';

            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$filename.'"',
                'Cache-Control' => 'private, no-store, no-cache, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
                'Expires' => 'Sat, 01 Jan 2000 00:00:00 GMT',
            ]);
        } catch (\Throwable $e) {
            Log::error('Quotation preview PDF failed', ['quotation' => $quotation->id, 'error' => $e->getMessage()]);

            return redirect()->route('quotations.show', $quotation)
                ->with('error', 'PDF preview failed: '.$e->getMessage());
        }
    }
}
