<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Estancia San Miguel')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-zinc-900 text-zinc-100 flex flex-col">
<header class="bg-zinc-950 border-b border-zinc-800">
    <nav class="mx-auto max-w-6xl px-4 py-3 flex gap-3 flex-wrap text-sm">
        @if (request()->path() === '/')
            <span class="text-zinc-300 font-semibold">Estancia San Miguel</span>
        @elseif (request()->is('login'))
            <span class="text-zinc-300 font-semibold">Acceso interno</span>
        @else
            <a class="px-3 py-1 rounded-md hover:bg-zinc-800 text-zinc-300" href="/">Frontpage</a>
            <a class="px-3 py-1 rounded-md hover:bg-zinc-800 text-zinc-300" href="/stock">Stock</a>
            <a class="px-3 py-1 rounded-md hover:bg-zinc-800 text-zinc-300" href="/comandas">Comandas</a>
            <a class="px-3 py-1 rounded-md hover:bg-zinc-800 text-zinc-300" href="/admin">Administración</a>
        @endif
    </nav>
</header>
<main class="mx-auto max-w-6xl w-full px-4 py-6 flex-1">
    @yield('content')
</main>
<footer class="bg-zinc-950 border-t border-zinc-800 text-zinc-400 text-center text-sm py-4">
    © {{ date('Y') }} Estancia San Miguel · Sistema interno
</footer>
@yield('scripts')
</body>
</html>
