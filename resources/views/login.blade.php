@extends('layout')
@section('title', 'Login')
@section('content')
<div class="card" style="max-width: 420px; margin:auto;">
    <h1>Acceso al sistema</h1>
    <p>Esta pantalla es el punto de entrada a las vistas internas.</p>
    <form action="/" method="get">
        <label>Usuario<br><input type="text" style="width:100%;padding:8px;margin:6px 0 12px;"></label><br>
        <label>Contraseña<br><input type="password" style="width:100%;padding:8px;margin:6px 0 12px;"></label><br>
        <button type="submit" style="background:#111827;color:#fff;border:none;padding:10px 12px;border-radius:6px;">Ingresar</button>
    </form>
</div>
@endsection
