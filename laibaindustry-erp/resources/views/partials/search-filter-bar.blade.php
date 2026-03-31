<form method="GET" action="{{ $action }}" class="flex flex-wrap items-end gap-3 p-4 bg-slate-50 dark:bg-slate-800/30 rounded-lg border border-slate-200 dark:border-slate-700">
<div class="flex-1 min-w-[180px]">
<label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Search</label>
<div class="relative">
<span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 material-symbols-outlined text-[18px]">search</span>
<input class="w-full h-9 pl-9 pr-3 text-sm rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-primary focus:border-primary" type="text" name="search" value="{{ request('search') }}" placeholder="{{ $searchPlaceholder ?? 'Search...' }}">
</div>
</div>
<div class="min-w-[140px]">
<label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">From</label>
<input class="w-full h-9 px-3 text-sm font-mono rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary" type="text" name="from" value="{{ old('from', filter_date_input_value(request('from'))) }}" placeholder="dd/mm/yyyy" inputmode="numeric" autocomplete="off">
</div>
<div class="min-w-[140px]">
<label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">To</label>
<input class="w-full h-9 px-3 text-sm font-mono rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary" type="text" name="to" value="{{ old('to', filter_date_input_value(request('to'))) }}" placeholder="dd/mm/yyyy" inputmode="numeric" autocomplete="off">
</div>
<div class="flex gap-2">
<button type="submit" class="h-9 px-4 text-sm font-medium text-white bg-primary hover:bg-blue-600 rounded-lg transition-colors inline-flex items-center gap-1.5">
<span class="material-symbols-outlined text-[16px]">filter_list</span>
Filter
</button>
@if(request('search') || request('from') || request('to'))
<a href="{{ $action }}" class="h-9 px-3 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white border border-slate-200 dark:border-slate-600 rounded-lg transition-colors inline-flex items-center gap-1">
<span class="material-symbols-outlined text-[16px]">close</span>
Clear
</a>
@endif
</div>
</form>
