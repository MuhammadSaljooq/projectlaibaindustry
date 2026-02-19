<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource (inventory page).
     */
    public function index(Request $request): View
    {
        $query = Product::query()->with('category');
        if ($search = $request->query('search')) {
            $query->search($search);
        }
        if (($catId = $request->query('category_id')) && $catId !== 'all') {
            $query->where('category_id', $catId);
        }
        $products = $query->orderBy('name')->paginate(15)->withQueryString();
        $totalItems = Product::count();
        $lowStockCount = Product::lowStock()->count();
        $totalValue = Product::query()->selectRaw('COALESCE(SUM(cost_price * stock_quantity), 0) as v')->value('v') ?? 0;
        $categories = Category::orderBy('name')->get();

        return view('inventory-dashboard', [
            'products' => $products,
            'totalItems' => $totalItems,
            'lowStockCount' => $lowStockCount,
            'totalValue' => $totalValue,
            'categories' => $categories,
        ]);
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();
        return view('products.create', ['categories' => $categories]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku'],
            'category_id' => ['required', 'exists:categories,id'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
        ]);
        $validated['stock_quantity'] = $validated['stock_quantity'] ?? 0;
        $validated['reorder_level'] = $validated['reorder_level'] ?? 10;
        Product::create($validated);
        return redirect()->route('inventory.dashboard')->with('success', 'Product added successfully.');
    }

    public function edit(Product $product): View
    {
        $categories = Category::orderBy('name')->get();
        return view('products.edit', ['product' => $product, 'categories' => $categories]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku,' . $product->id],
            'category_id' => ['required', 'exists:categories,id'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
        ]);
        $validated['stock_quantity'] = $validated['stock_quantity'] ?? 0;
        $validated['reorder_level'] = $validated['reorder_level'] ?? 10;
        $product->update($validated);
        return redirect()->route('inventory.dashboard')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();
        return redirect()->route('inventory.dashboard')->with('success', 'Product deleted successfully.');
    }

    public function show(Product $product): RedirectResponse
    {
        return redirect()->route('inventory.dashboard');
    }
}
