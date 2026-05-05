<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Estancia San Miguel')</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f7f7f7; color: #1f2937; min-height: 100vh; display:flex; flex-direction:column; }
        header { background: #111827; color: white; padding: 12px 20px; }
        nav { display: flex; gap: 12px; flex-wrap: wrap; }
        nav a { color: #e5e7eb; text-decoration: none; padding: 6px 10px; border-radius: 6px; }
        nav a:hover { background: #374151; }
        .container { max-width: 1100px; margin: 20px auto; padding: 0 16px; width: 100%; flex: 1; box-sizing:border-box; }
        .card { background: #fff; border-radius: 10px; padding: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        footer { background:#111827; color:#e5e7eb; padding:14px 20px; text-align:center; font-size:14px; }
        button { cursor: pointer; }
    </style>
    @yield('styles')
</head>
<body>
<header>
    <nav>
        @if (request()->path() === '/')
            <span>Estancia San Miguel</span>
        @elseif (request()->is('login'))
            <span>Acceso interno</span>
        @else
            <a href="/">Frontpage</a>
            <a href="/stock">Stock</a>
            <a href="/comandas">Comandas</a>
            <a href="/admin">Administración</a>
        @endif
    </nav>
</header>
<div class="container">
    @yield('content')
</div>
<footer>
    © {{ date('Y') }} Estancia San Miguel · Sistema interno
</footer>
@yield('scripts')
</body>
</html>
