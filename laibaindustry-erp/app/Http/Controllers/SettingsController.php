<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    private const CURRENCY_MAP = [
        'SAR' => ['name' => 'Saudi Riyal', 'symbol' => 'SAR'],
        'USD' => ['name' => 'US Dollar', 'symbol' => '$'],
        'EUR' => ['name' => 'Euro', 'symbol' => '€'],
        'GBP' => ['name' => 'British Pound', 'symbol' => '£'],
        'PKR' => ['name' => 'Pakistani Rupee', 'symbol' => 'Rs'],
        'AED' => ['name' => 'UAE Dirham', 'symbol' => 'د.إ'],
    ];

    public function index(): View
    {
        $default = Currency::query()->where('is_default', true)->first();
        return view('settings.index', [
            'defaultCurrencyCode' => $default?->code ?? 'USD',
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate(['currency' => 'required|string|in:SAR,USD,EUR,GBP,PKR,AED']);

        $code = $request->input('currency');
        $info = self::CURRENCY_MAP[$code];

        $currency = Currency::firstOrCreate(
            ['code' => $code],
            [
                'name' => $info['name'],
                'symbol' => $info['symbol'],
                'is_default' => false,
                'is_active' => true,
                'decimal_places' => 2,
            ]
        );

        Currency::query()->update(['is_default' => false]);
        $currency->update([
            'is_default' => true,
            'name' => $info['name'],
            'symbol' => $info['symbol'],
        ]);

        return redirect()->route('settings.index')->with('success', 'Settings saved. Currency is now ' . $currency->code . '.');
    }
}
