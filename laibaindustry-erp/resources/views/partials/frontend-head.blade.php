<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<meta content="{{ csrf_token() }}" name="csrf-token"/>
<title>{{ $title ?? 'ERP' }}</title>
<script>
  tailwind.config = { darkMode: 'class', theme: { extend: { colors: { primary: '#137fec', 'background-light': '#f6f7f8', 'background-dark': '#101922' }, fontFamily: { display: ['Manrope', 'sans-serif'] } } } }
</script>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script src="/scripts/frontend-common.js"></script>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="/styles/frontend-common.css" rel="stylesheet"/>
