@extends('layout')
@section('title', 'Login')
@section('content')
<div class="card" style="max-width: 420px; margin:auto;">
    <h1>Acceso al sistema</h1>
    <p>Esta pantalla es el punto de entrada a las vistas internas.</p>

    @if ($errors->any())
        <div style="background:#fee2e2;color:#991b1b;padding:10px;border-radius:6px;margin-bottom:12px;">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="/login" method="post">
        @csrf
        <label>Usuario<br><input name="username" type="text" value="{{ old('username') }}" style="width:100%;padding:8px;margin:6px 0 12px;"></label><br>
        <label>Contraseña<br><input name="password" type="password" style="width:100%;padding:8px;margin:6px 0 12px;"></label><br>
        <button type="submit" style="background:#111827;color:#fff;border:none;padding:10px 12px;border-radius:6px;">Ingresar</button>
    </form>
</div>
@endsection
