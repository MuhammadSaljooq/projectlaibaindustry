@php
    $product = $product ?? null;
    $outline = '#D3D8DE';
    $primary = '#5E5E5E';
    $error = '#9F403D';
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2" style="gap:1.5rem;">
    <div>
        <label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.05em;color:#5E5E5E;" for="name">Name <span style="color:{{ $error }};">*</span></label>
        <input class="w-full h-11 px-4 text-sm font-bold outline-none transition-all" style="background:#FFFFFF;color:#2B3437;border:1px solid {{ $errors->has('name') ? $error : $outline }};border-radius:0;" id="name" name="name" type="text" placeholder="Product name" value="{{ old('name', $product?->name) }}" required maxlength="255" onfocus="this.style.borderColor='{{ $primary }}';this.style.borderWidth='2px'" onblur="this.style.borderColor='{{ $errors->has('name') ? $error : $outline }}';this.style.borderWidth='1px'">
        @error('name')<p class="mt-1.5 text-xs font-bold" style="color:{{ $error }};">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.05em;color:#5E5E5E;" for="sku">Article # <span style="color:{{ $error }};">*</span></label>
        <input class="w-full h-11 px-4 text-sm font-bold outline-none transition-all" style="background:#FFFFFF;color:#2B3437;border:1px solid {{ $errors->has('sku') ? $error : $outline }};border-radius:0;" id="sku" name="sku" type="text" placeholder="e.g. ART-001" value="{{ old('sku', $product?->sku) }}" required maxlength="100" onfocus="this.style.borderColor='{{ $primary }}';this.style.borderWidth='2px'" onblur="this.style.borderColor='{{ $errors->has('sku') ? $error : $outline }}';this.style.borderWidth='1px'">
        @error('sku')<p class="mt-1.5 text-xs font-bold" style="color:{{ $error }};">{{ $message }}</p>@enderror
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2" style="gap:1.5rem;margin-top:1.5rem;">
    <div>
        <label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.05em;color:#5E5E5E;" for="category_id">Category <span style="color:{{ $error }};">*</span></label>
        <div class="relative">
            <select class="w-full h-11 pl-4 pr-10 text-sm font-bold outline-none appearance-none cursor-pointer transition-all" style="background:#FFFFFF;color:#2B3437;border:1px solid {{ $errors->has('category_id') ? $error : $outline }};border-radius:0;" id="category_id" name="category_id" required onfocus="this.style.borderColor='{{ $primary }}';this.style.borderWidth='2px'" onblur="this.style.borderColor='{{ $errors->has('category_id') ? $error : $outline }}';this.style.borderWidth='1px'">
                <option value="" style="background:#FFFFFF;color:#2B3437;">Select category</option>
                @foreach($categories ?? [] as $cat)
                <option value="{{ $cat->id }}" style="background:#FFFFFF;color:#2B3437;" {{ old('category_id', $product?->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            <span class="absolute right-3 top-1/2 -translate-y-1/2 material-symbols-outlined pointer-events-none" style="font-size:18px;color:#5E5E5E;">expand_more</span>
        </div>
        @error('category_id')<p class="mt-1.5 text-xs font-bold" style="color:{{ $error }};">{{ $message }}</p>@enderror
    </div>
    <div></div>
</div>

{{-- Separator --}}
<div style="margin:2rem 0;border-top:1px solid #D3D8DE;"></div>

<div class="grid grid-cols-1 sm:grid-cols-2" style="gap:1.5rem;">
    <div>
        <label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.05em;color:#5E5E5E;" for="cost_price">Cost Price <span style="color:{{ $error }};">*</span></label>
        <input class="w-full h-11 px-4 text-sm font-bold outline-none transition-all tabular-nums" style="background:#FFFFFF;color:#2B3437;border:1px solid {{ $errors->has('cost_price') ? $error : $outline }};border-radius:0;" id="cost_price" name="cost_price" type="number" step="0.01" min="0" placeholder="0.00" value="{{ old('cost_price', $product?->cost_price ?? 0) }}" required onfocus="this.style.borderColor='{{ $primary }}';this.style.borderWidth='2px'" onblur="this.style.borderColor='{{ $errors->has('cost_price') ? $error : $outline }}';this.style.borderWidth='1px'">
        @error('cost_price')<p class="mt-1.5 text-xs font-bold" style="color:{{ $error }};">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.05em;color:#5E5E5E;" for="selling_price">Selling Price</label>
        <input class="w-full h-11 px-4 text-sm font-bold outline-none transition-all tabular-nums" style="background:#FFFFFF;color:#2B3437;border:1px solid {{ $errors->has('selling_price') ? $error : $outline }};border-radius:0;" id="selling_price" name="selling_price" type="number" step="0.01" min="0" placeholder="0.00" value="{{ old('selling_price', $product?->selling_price) }}" onfocus="this.style.borderColor='{{ $primary }}';this.style.borderWidth='2px'" onblur="this.style.borderColor='{{ $errors->has('selling_price') ? $error : $outline }}';this.style.borderWidth='1px'">
        @error('selling_price')<p class="mt-1.5 text-xs font-bold" style="color:{{ $error }};">{{ $message }}</p>@enderror
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2" style="gap:1.5rem;margin-top:1.5rem;">
    <div>
        <label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.05em;color:#5E5E5E;" for="stock_quantity">Stock Quantity</label>
        <input class="w-full h-11 px-4 text-sm font-bold outline-none transition-all tabular-nums" style="background:#FFFFFF;color:#2B3437;border:1px solid {{ $errors->has('stock_quantity') ? $error : $outline }};border-radius:0;" id="stock_quantity" name="stock_quantity" type="number" min="0" placeholder="0" value="{{ old('stock_quantity', $product?->stock_quantity ?? 0) }}" onfocus="this.style.borderColor='{{ $primary }}';this.style.borderWidth='2px'" onblur="this.style.borderColor='{{ $errors->has('stock_quantity') ? $error : $outline }}';this.style.borderWidth='1px'">
        @error('stock_quantity')<p class="mt-1.5 text-xs font-bold" style="color:{{ $error }};">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.05em;color:#5E5E5E;" for="reorder_level">Reorder Level</label>
        <input class="w-full h-11 px-4 text-sm font-bold outline-none transition-all tabular-nums" style="background:#FFFFFF;color:#2B3437;border:1px solid {{ $errors->has('reorder_level') ? $error : $outline }};border-radius:0;" id="reorder_level" name="reorder_level" type="number" min="0" placeholder="10" value="{{ old('reorder_level', $product?->reorder_level ?? 10) }}" onfocus="this.style.borderColor='{{ $primary }}';this.style.borderWidth='2px'" onblur="this.style.borderColor='{{ $errors->has('reorder_level') ? $error : $outline }}';this.style.borderWidth='1px'">
        @error('reorder_level')<p class="mt-1.5 text-xs font-bold" style="color:{{ $error }};">{{ $message }}</p>@enderror
    </div>
</div>
