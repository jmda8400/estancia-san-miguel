<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Estancia San Miguel')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col">
<header class="app-shell border-b">
    <nav class="mx-auto max-w-7xl px-4 py-3 flex items-center justify-between gap-3 flex-wrap text-sm">
        <div class="flex items-center gap-3">
            <span class="text-xl" aria-label="Logo">🌿</span>
            @if (request()->path() === '/')
                <span class="font-semibold">Estancia San Miguel</span>
            @elseif (request()->is('login'))
                <span class="font-semibold">Acceso interno</span>
            @else
                <a class="app-btn" href="/">Frontpage</a>
                <a class="app-btn" href="/stock">Stock</a>
                <a class="app-btn" href="/comandas">Comandas</a>
                <a class="app-btn" href="/admin">Administración</a>
            @endif
        </div>
    </nav>
</header>
<main class="mx-auto max-w-7xl w-full px-4 py-6 flex-1">
    @yield('content')
</main>
<footer class="app-shell border-t text-sm py-4">
    <div class="mx-auto max-w-7xl px-4 flex items-center justify-between gap-3">
        <span>© {{ date('Y') }} Estancia San Miguel · Sistema interno</span>
        <div class="flex items-center gap-4 text-lg">
            <a href="#" title="WhatsApp" aria-label="WhatsApp" class="social-logo whatsapp-logo">WA</a>
            <a href="#" title="Instagram" aria-label="Instagram" class="social-logo instagram-logo">IG</a>
        </div>
    </div>
</footer>
@yield('scripts')
</body>
</html>
