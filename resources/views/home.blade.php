@extends('layout')
@section('title', 'Frontpage')
@section('content')
<div class="card" style="display:grid; gap:16px;">
    <header>
        <h1 style="margin:0;">Estancia San Miguel</h1>
        <p style="margin:8px 0 0;">Bienvenido al sistema de gestión.</p>
    </header>

    <section>
        <h2 style="margin-top:0;">Acerca del sistema</h2>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras laoreet feugiat nisi, ac interdum est mattis non. Aenean vel urna vitae lorem commodo ultrices non id lacus.</p>
        <p>Praesent ac felis sodales, pharetra nisi sit amet, faucibus nulla. Nulla facilisi. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.</p>
    </section>

    <section>
        <h2 style="margin-top:0;">Galería</h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;">
            <div style="height:120px;border:2px dashed #9ca3af;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#6b7280;">Espacio para imagen 1</div>
            <div style="height:120px;border:2px dashed #9ca3af;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#6b7280;">Espacio para imagen 2</div>
            <div style="height:120px;border:2px dashed #9ca3af;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#6b7280;">Espacio para imagen 3</div>
        </div>
    </section>
</div>
@endsection
