@php
    $product = $product ?? null;
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2" style="gap:1.5rem;">
    <div>
        <label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.15em;color:#C4C7C8;" for="name">Name <span style="color:#FFB4AB;">*</span></label>
        <input class="w-full h-11 px-4 text-sm font-medium text-white outline-none transition-all" style="background:transparent;border:1px solid {{ $errors->has('name') ? '#FFB4AB' : 'rgba(68,71,72,0.4)' }};border-radius:0.375rem;" id="name" name="name" type="text" placeholder="Product name" value="{{ old('name', $product?->name) }}" required maxlength="255" onfocus="this.style.borderColor='#FFFFFF';this.style.boxShadow='0 0 0 2px rgba(255,255,255,0.1)'" onblur="this.style.borderColor='{{ $errors->has('name') ? '#FFB4AB' : 'rgba(68,71,72,0.4)' }}';this.style.boxShadow='none'">
        @error('name')<p class="mt-1.5 text-xs font-medium" style="color:#FFB4AB;">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.15em;color:#C4C7C8;" for="sku">SKU <span style="color:#FFB4AB;">*</span></label>
        <input class="w-full h-11 px-4 text-sm font-medium text-white outline-none transition-all" style="background:transparent;border:1px solid {{ $errors->has('sku') ? '#FFB4AB' : 'rgba(68,71,72,0.4)' }};border-radius:0.375rem;" id="sku" name="sku" type="text" placeholder="e.g. SKU-001" value="{{ old('sku', $product?->sku) }}" required maxlength="100" onfocus="this.style.borderColor='#FFFFFF';this.style.boxShadow='0 0 0 2px rgba(255,255,255,0.1)'" onblur="this.style.borderColor='{{ $errors->has('sku') ? '#FFB4AB' : 'rgba(68,71,72,0.4)' }}';this.style.boxShadow='none'">
        @error('sku')<p class="mt-1.5 text-xs font-medium" style="color:#FFB4AB;">{{ $message }}</p>@enderror
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2" style="gap:1.5rem;margin-top:1.5rem;">
    <div>
        <label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.15em;color:#C4C7C8;" for="category_id">Category <span style="color:#FFB4AB;">*</span></label>
        <div class="relative">
            <select class="w-full h-11 pl-4 pr-10 text-sm font-medium text-white outline-none appearance-none cursor-pointer transition-all" style="background:transparent;border:1px solid {{ $errors->has('category_id') ? '#FFB4AB' : 'rgba(68,71,72,0.4)' }};border-radius:0.375rem;" id="category_id" name="category_id" required onfocus="this.style.borderColor='#FFFFFF'" onblur="this.style.borderColor='{{ $errors->has('category_id') ? '#FFB4AB' : 'rgba(68,71,72,0.4)' }}'">
                <option value="" style="background:#1B1B1B;">Select category</option>
                @foreach($categories ?? [] as $cat)
                <option value="{{ $cat->id }}" style="background:#1B1B1B;" {{ old('category_id', $product?->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            <span class="absolute right-3 top-1/2 -translate-y-1/2 material-symbols-outlined pointer-events-none" style="font-size:18px;color:#8e9192;">expand_more</span>
        </div>
        @error('category_id')<p class="mt-1.5 text-xs font-medium" style="color:#FFB4AB;">{{ $message }}</p>@enderror
    </div>
    <div></div>
</div>

{{-- Separator --}}
<div style="margin:2rem 0;border-top:1px solid rgba(68,71,72,0.2);"></div>

<div class="grid grid-cols-1 sm:grid-cols-2" style="gap:1.5rem;">
    <div>
        <label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.15em;color:#C4C7C8;" for="cost_price">Cost Price <span style="color:#FFB4AB;">*</span></label>
        <input class="w-full h-11 px-4 text-sm font-medium text-white outline-none transition-all tabular-nums" style="background:transparent;border:1px solid {{ $errors->has('cost_price') ? '#FFB4AB' : 'rgba(68,71,72,0.4)' }};border-radius:0.375rem;" id="cost_price" name="cost_price" type="number" step="0.01" min="0" placeholder="0.00" value="{{ old('cost_price', $product?->cost_price ?? 0) }}" required onfocus="this.style.borderColor='#FFFFFF';this.style.boxShadow='0 0 0 2px rgba(255,255,255,0.1)'" onblur="this.style.borderColor='{{ $errors->has('cost_price') ? '#FFB4AB' : 'rgba(68,71,72,0.4)' }}';this.style.boxShadow='none'">
        @error('cost_price')<p class="mt-1.5 text-xs font-medium" style="color:#FFB4AB;">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.15em;color:#C4C7C8;" for="selling_price">Selling Price</label>
        <input class="w-full h-11 px-4 text-sm font-medium text-white outline-none transition-all tabular-nums" style="background:transparent;border:1px solid {{ $errors->has('selling_price') ? '#FFB4AB' : 'rgba(68,71,72,0.4)' }};border-radius:0.375rem;" id="selling_price" name="selling_price" type="number" step="0.01" min="0" placeholder="0.00" value="{{ old('selling_price', $product?->selling_price) }}" onfocus="this.style.borderColor='#FFFFFF';this.style.boxShadow='0 0 0 2px rgba(255,255,255,0.1)'" onblur="this.style.borderColor='{{ $errors->has('selling_price') ? '#FFB4AB' : 'rgba(68,71,72,0.4)' }}';this.style.boxShadow='none'">
        @error('selling_price')<p class="mt-1.5 text-xs font-medium" style="color:#FFB4AB;">{{ $message }}</p>@enderror
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2" style="gap:1.5rem;margin-top:1.5rem;">
    <div>
        <label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.15em;color:#C4C7C8;" for="stock_quantity">Stock Quantity</label>
        <input class="w-full h-11 px-4 text-sm font-medium text-white outline-none transition-all tabular-nums" style="background:transparent;border:1px solid {{ $errors->has('stock_quantity') ? '#FFB4AB' : 'rgba(68,71,72,0.4)' }};border-radius:0.375rem;" id="stock_quantity" name="stock_quantity" type="number" min="0" placeholder="0" value="{{ old('stock_quantity', $product?->stock_quantity ?? 0) }}" onfocus="this.style.borderColor='#FFFFFF';this.style.boxShadow='0 0 0 2px rgba(255,255,255,0.1)'" onblur="this.style.borderColor='{{ $errors->has('stock_quantity') ? '#FFB4AB' : 'rgba(68,71,72,0.4)' }}';this.style.boxShadow='none'">
        @error('stock_quantity')<p class="mt-1.5 text-xs font-medium" style="color:#FFB4AB;">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.15em;color:#C4C7C8;" for="reorder_level">Reorder Level</label>
        <input class="w-full h-11 px-4 text-sm font-medium text-white outline-none transition-all tabular-nums" style="background:transparent;border:1px solid {{ $errors->has('reorder_level') ? '#FFB4AB' : 'rgba(68,71,72,0.4)' }};border-radius:0.375rem;" id="reorder_level" name="reorder_level" type="number" min="0" placeholder="10" value="{{ old('reorder_level', $product?->reorder_level ?? 10) }}" onfocus="this.style.borderColor='#FFFFFF';this.style.boxShadow='0 0 0 2px rgba(255,255,255,0.1)'" onblur="this.style.borderColor='{{ $errors->has('reorder_level') ? '#FFB4AB' : 'rgba(68,71,72,0.4)' }}';this.style.boxShadow='none'">
        @error('reorder_level')<p class="mt-1.5 text-xs font-medium" style="color:#FFB4AB;">{{ $message }}</p>@enderror
    </div>
</div>
