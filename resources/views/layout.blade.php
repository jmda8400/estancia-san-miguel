<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Estancia San Miguel')</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f7f7f7; color: #1f2937; }
        header { background: #111827; color: white; padding: 12px 20px; }
        nav { display: flex; gap: 12px; flex-wrap: wrap; }
        nav a { color: #e5e7eb; text-decoration: none; padding: 6px 10px; border-radius: 6px; }
        nav a:hover { background: #374151; }
        .container { max-width: 1100px; margin: 20px auto; padding: 0 16px; }
        .card { background: #fff; border-radius: 10px; padding: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        button { cursor: pointer; }
    </style>
    @yield('styles')
</head>
<body>
<header>
    <nav>
        <a href="/">Frontpage</a>
        <a href="/stock">Stock</a>
        <a href="/comandas">Comandas</a>
        <a href="/admin">Administración</a>
        <a href="/login">Login</a>
    </nav>
</header>
<div class="container">
    @yield('content')
</div>
@yield('scripts')
</body>
</html>
