<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Dashboard - Laiba Safety'])
@include('partials.stitch-design')
<style>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
</head>
<body class="bg-[#F8F9FA] text-[#2B3437] h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'dashboard'])

<main class="stitch-ui flex-1 flex flex-col h-full min-h-0 overflow-hidden relative bg-[#F8F9FA]">
<header class="h-16 shrink-0 z-10 flex items-center justify-between px-6 border-b border-[#ABB3B7] bg-white">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-[#586064] hover:bg-[#F1F4F6] rounded-none border border-transparent hover:border-[#ABB3B7]" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined text-[#2B3437]">menu</span>
</button>
<h2 class="text-lg font-bold text-[#2B3437] hidden sm:block tracking-tight uppercase">Dashboard</h2>
</div>
</header>

<div class="flex-1 min-h-0 overflow-y-auto p-6 md:p-8 max-md:pb-24 scroll-smooth no-scrollbar">
<div class="max-w-[1400px] mx-auto flex flex-col gap-8">

<div class="flex flex-col gap-4">
<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
<div class="flex flex-col gap-1 min-w-0">
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">Overview</h1>
</div>
<p class="text-[10px] font-bold uppercase tracking-widest text-[#586064] lg:text-right shrink-0">All-time totals · last updated <span id="dashboard-clock"></span></p>
<script>
(function () {
    function pad(n) { return String(n).padStart(2, '0'); }
    function tick() {
        var d = new Date();
        var formatted =
            pad(d.getDate()) + '/' +
            pad(d.getMonth() + 1) + '/' +
            d.getFullYear() + ' ' +
            pad(d.getHours()) + ':' +
            pad(d.getMinutes()) + ':' +
            pad(d.getSeconds());
        var el = document.getElementById('dashboard-clock');
        if (el) el.textContent = formatted;
    }
    tick();
    setInterval(tick, 1000);
})();
</script>
</div>
<div class="h-0.5 w-full bg-[#5E5E5E]" role="presentation"></div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
@foreach($sections ?? [] as $section)
<article class="st-paper border border-[#ABB3B7] bg-white flex flex-col h-full min-h-0">
<div class="px-5 py-4 border-b border-[#ABB3B7] bg-[#EAEFF1] flex items-center gap-3">
<span class="material-symbols-outlined text-[#5E5E5E] text-[22px] shrink-0" aria-hidden="true">{{ $section['icon'] }}</span>
<h2 class="text-sm font-bold uppercase tracking-widest text-[#586064]">{{ $section['title'] }}</h2>
</div>
<div class="p-5 flex flex-col flex-1 gap-4">
@foreach($section['metrics'] as $metric)
<div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-1 border-b border-[#ABB3B7] pb-3 last:border-0 last:pb-0">
<p class="st-label !mb-0">{{ $metric['label'] }}</p>
<p class="text-lg font-bold font-mono tabular-nums text-[#2B3437] sm:text-right">{{ $metric['value'] }}</p>
</div>
@endforeach
</div>
<div class="px-5 py-4 border-t border-[#ABB3B7] mt-auto flex flex-col gap-3">
<div class="flex flex-wrap items-center gap-2">
<a href="{{ $section['route'] }}" class="st-btn-primary h-9 px-4 inline-flex items-center gap-2 text-[10px]">{{ $section['link_label'] }}</a>
@foreach($section['actions'] ?? [] as $action)
@php $isPrimary = ($action['style'] ?? '') === 'primary'; @endphp
<a href="{{ $action['route'] }}" class="{{ $isPrimary ? 'st-btn-primary' : 'st-btn-secondary' }} h-9 px-4 inline-flex items-center gap-2 text-[10px]">{{ $action['label'] }}</a>
@endforeach
</div>
</div>
</article>
@endforeach
</div>

<p class="text-center text-[10px] uppercase tracking-widest text-[#586064] pt-4 pb-2">© 2026 Laiba Safety. All rights reserved.</p>
</div>
</div>
</main>


</body>
</html>
