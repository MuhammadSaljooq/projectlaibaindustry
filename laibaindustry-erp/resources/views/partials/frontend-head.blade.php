<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<meta content="{{ csrf_token() }}" name="csrf-token"/>
<title>{{ $title ?? 'ERP' }}</title>
<link rel="icon" type="image/png" sizes="32x32" href="/images/favicon-circle-32.png?v=3"/>
<link rel="icon" type="image/png" sizes="192x192" href="/images/favicon-circle-192.png?v=3"/>
<link rel="apple-touch-icon" href="/images/favicon-circle.png?v=3"/>
<script>
  tailwind.config = { darkMode: 'class', theme: { extend: { colors: { primary: '#137fec', 'background-light': '#f6f7f8', 'background-dark': '#101922' }, fontFamily: { display: ['Manrope', 'sans-serif'] } } } }
</script>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script src="/scripts/frontend-common.js"></script>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;300;400;500;600;700;800;900&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="/styles/frontend-common.css" rel="stylesheet"/>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
