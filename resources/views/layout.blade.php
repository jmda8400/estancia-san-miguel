<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Estancia San Miguel')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col app-has-fixed-bars">
<header class="app-shell app-fixed-header border-b app-shell-transparent">
    <nav class="mx-auto max-w-7xl px-4 py-3 flex items-center justify-between gap-3 flex-wrap text-sm">
        <div class="flex items-center gap-3 app-brand-wrap">
            <a href="/" aria-label="Volver a la Frontpage"><img src="/logo.svg" alt="Estancia San Miguel" class="brand-logo" loading="eager" decoding="async"></a>
            @if (request()->path() === '/')
                <div class="flex flex-wrap gap-2">
                    <a class="app-btn" href="#hosteria">Hostería</a>
                    <a class="app-btn" href="#habitaciones">Habitaciones</a>
                    <a class="app-btn" href="#cabanas">Cabañas</a>
                    <a class="app-btn" href="#gastronomia">Gastronomía</a>
                    <a class="app-btn" href="#servicios">Servicios & Actividades</a>
                    <a class="app-btn" href="#contacto">Contacto</a>
                </div>
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
<main class="mx-auto max-w-7xl w-full px-4 py-6 flex-1 app-main-content">
    @yield('content')
</main>
<footer class="app-shell app-fixed-footer border-t text-sm py-4">
    <div class="mx-auto max-w-7xl px-4 flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <img src="/logo_footer.svg" alt="Logo footer Estancia San Miguel" class="h-8 w-auto">
            <span>© {{ date('Y') }} Estancia San Miguel</span>
        </div>
        <div class="flex items-center gap-4 text-lg">
            <a href="#" title="WhatsApp" aria-label="WhatsApp" class="social-logo whatsapp-logo">WA</a>
            <a href="#" title="Instagram" aria-label="Instagram" class="social-logo instagram-logo">IG</a>
        </div>
    </div>
</footer>
@yield('scripts')
</body>
</html>
