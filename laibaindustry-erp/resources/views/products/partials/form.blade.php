@php
    $product = $product ?? null;
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="name">Name <span class="text-red-500">*</span></label>
        <input class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary @error('name') border-red-500 @enderror" id="name" name="name" type="text" value="{{ old('name', $product?->name) }}" required maxlength="255">
        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="sku">SKU <span class="text-red-500">*</span></label>
        <input class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary @error('sku') border-red-500 @enderror" id="sku" name="sku" type="text" value="{{ old('sku', $product?->sku) }}" required maxlength="100">
        @error('sku')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="category_id">Category <span class="text-red-500">*</span></label>
        <select class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary @error('category_id') border-red-500 @enderror" id="category_id" name="category_id" required>
            <option value="">Select category</option>
            @foreach($categories ?? [] as $cat)
            <option value="{{ $cat->id }}" {{ old('category_id', $product?->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
        @error('category_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div></div>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="cost_price">Cost Price <span class="text-red-500">*</span></label>
        <input class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary @error('cost_price') border-red-500 @enderror" id="cost_price" name="cost_price" type="number" step="0.01" min="0" value="{{ old('cost_price', $product?->cost_price ?? 0) }}" required>
        @error('cost_price')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="selling_price">Selling Price</label>
        <input class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary @error('selling_price') border-red-500 @enderror" id="selling_price" name="selling_price" type="number" step="0.01" min="0" value="{{ old('selling_price', $product?->selling_price) }}">
        @error('selling_price')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="stock_quantity">Stock Quantity</label>
        <input class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary @error('stock_quantity') border-red-500 @enderror" id="stock_quantity" name="stock_quantity" type="number" min="0" value="{{ old('stock_quantity', $product?->stock_quantity ?? 0) }}">
        @error('stock_quantity')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="reorder_level">Reorder Level</label>
        <input class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary @error('reorder_level') border-red-500 @enderror" id="reorder_level" name="reorder_level" type="number" min="0" value="{{ old('reorder_level', $product?->reorder_level ?? 10) }}">
        @error('reorder_level')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</div>
