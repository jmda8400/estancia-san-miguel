@extends('layout')
@section('title', 'Login')
@section('content')
<div class="card" style="max-width: 420px; margin:auto;">
    <h1>Login</h1>
    <form>
        <label>Usuario<br><input type="text" style="width:100%;padding:8px;margin:6px 0 12px;"></label><br>
        <label>Contraseña<br><input type="password" style="width:100%;padding:8px;margin:6px 0 12px;"></label><br>
        <button type="button" style="background:#111827;color:#fff;border:none;padding:10px 12px;border-radius:6px;">Ingresar</button>
    </form>
</div>
@endsection
