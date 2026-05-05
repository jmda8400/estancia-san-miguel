@extends('layout')
@section('title', 'Stock')
@section('content')
<div class="card">
    <h1>Vista de stock</h1>
    <p>Sección inicial para gestionar inventario.</p>

    <table style="width:100%; border-collapse:collapse; margin-top:16px;">
        <thead>
            <tr>
                <th style="text-align:left; border-bottom:1px solid #d1d5db; padding:8px;">Producto</th>
                <th style="text-align:left; border-bottom:1px solid #d1d5db; padding:8px;">Cantidad</th>
                <th style="text-align:left; border-bottom:1px solid #d1d5db; padding:8px;">Unidad</th>
            </tr>
        </thead>
        <tbody>
            @forelse($stockItems as $item)
                <tr>
                    <td style="padding:8px; border-bottom:1px solid #e5e7eb;">{{ $item->producto }}</td>
                    <td style="padding:8px; border-bottom:1px solid #e5e7eb;">{{ $item->cantidad }}</td>
                    <td style="padding:8px; border-bottom:1px solid #e5e7eb;">{{ $item->unidad }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="padding:8px;">No hay registros de stock cargados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
