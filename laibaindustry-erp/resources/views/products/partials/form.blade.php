@php
    $product = $product ?? null;
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
    <div>
        <label class="st-label block mb-2" for="name">Name <span class="text-[#9F403D]">*</span></label>
        <input class="st-input w-full h-10 px-3 text-sm @error('name') !border-[#9F403D] @enderror" id="name" name="name" type="text" placeholder="Product name" value="{{ old('name', $product?->name) }}" required maxlength="255">
        @error('name')<p class="mt-1.5 text-xs text-[#9F403D] font-medium">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="st-label block mb-2" for="sku">Article no. <span class="text-[#9F403D]">*</span></label>
        <input class="st-input w-full h-10 px-3 text-sm @error('sku') !border-[#9F403D] @enderror" id="sku" name="sku" type="text" placeholder="e.g. ART-001" value="{{ old('sku', $product?->sku) }}" required maxlength="100">
        @error('sku')<p class="mt-1.5 text-xs text-[#9F403D] font-medium">{{ $message }}</p>@enderror
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-6">
    <div class="min-w-0">
        <label class="st-label block mb-2" for="category_id">Category <span class="text-[#9F403D]">*</span></label>
        <select class="st-select w-full min-w-0 h-10 pl-3 pr-12 text-sm cursor-pointer @error('category_id') !border-[#9F403D] @enderror" id="category_id" name="category_id" required>
            <option value="">Select category</option>
            @foreach($categories ?? [] as $cat)
            <option value="{{ $cat->id }}" {{ old('category_id', $product?->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
        @error('category_id')<p class="mt-1.5 text-xs text-[#9F403D] font-medium">{{ $message }}</p>@enderror
    </div>
    <div></div>
</div>

<div class="my-8 border-t border-[#ABB3B7]"></div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
    <div>
        <label class="st-label block mb-2" for="cost_price">Cost price <span class="text-[#9F403D]">*</span></label>
        <input class="st-input w-full h-10 px-3 text-sm tabular-nums @error('cost_price') !border-[#9F403D] @enderror" id="cost_price" name="cost_price" type="number" step="0.01" min="0" placeholder="0.00" value="{{ old('cost_price', $product?->cost_price ?? 0) }}" required>
        @error('cost_price')<p class="mt-1.5 text-xs text-[#9F403D] font-medium">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="st-label block mb-2" for="selling_price">Selling price</label>
        <input class="st-input w-full h-10 px-3 text-sm tabular-nums @error('selling_price') !border-[#9F403D] @enderror" id="selling_price" name="selling_price" type="number" step="0.01" min="0" placeholder="0.00" value="{{ old('selling_price', $product?->selling_price) }}">
        @error('selling_price')<p class="mt-1.5 text-xs text-[#9F403D] font-medium">{{ $message }}</p>@enderror
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-6">
    <div>
        <label class="st-label block mb-2" for="stock_quantity">Stock quantity</label>
        <input class="st-input w-full h-10 px-3 text-sm tabular-nums @error('stock_quantity') !border-[#9F403D] @enderror" id="stock_quantity" name="stock_quantity" type="number" min="0" placeholder="0" value="{{ old('stock_quantity', $product?->stock_quantity ?? 0) }}">
        @error('stock_quantity')<p class="mt-1.5 text-xs text-[#9F403D] font-medium">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="st-label block mb-2" for="reorder_level">Reorder level</label>
        <input class="st-input w-full h-10 px-3 text-sm tabular-nums @error('reorder_level') !border-[#9F403D] @enderror" id="reorder_level" name="reorder_level" type="number" min="0" placeholder="10" value="{{ old('reorder_level', $product?->reorder_level ?? 10) }}">
        @error('reorder_level')<p class="mt-1.5 text-xs text-[#9F403D] font-medium">{{ $message }}</p>@enderror
    </div>
</div>
