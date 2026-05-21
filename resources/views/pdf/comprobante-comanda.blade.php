<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Comprobante comanda</title>
<style>
@page { margin: 1.5mm; size: 58mm auto; }
html, body { margin:0; padding:0; background:#fff; }
body { font-family: DejaVu Sans, sans-serif; font-size: 9.2px; line-height: 1.2; color:#000; }
.ticket { width:100%; }
.center { text-align:center; }
.ticket-logo { display:block; margin:0 auto 1mm; max-width:22mm; max-height:11mm; }
.title { font-weight:700; font-size:11.5px; letter-spacing:.4px; }
.subtitle { font-size:9px; font-weight:600; margin-top:.3mm; }
.block { border-top:.25mm solid #333; border-bottom:.15mm solid #888; padding:1mm 0; margin:1.2mm 0; }
.meta-row { margin:.35mm 0; }
.item { margin:.9mm 0; }
.item-name { font-weight:600; word-break:break-word; }
.ticket-table { width:100%; border-collapse:collapse; }
.ticket-table td { padding:0; vertical-align:top; }
.label { width:64%; }
.value { width:36%; text-align:right; white-space:nowrap; }
.muted { color:#333; font-size:8.5px; }
.total-wrap { border-top:.3mm solid #000; padding-top:.8mm; margin-top:.8mm; }
.total { font-weight:700; font-size:10.5px; }
.footer { text-align:center; margin-top:1.2mm; font-size:8.7px; }
</style>
</head>
<body>
<main class="ticket">
<div class="center">
@if(!empty($logoDataUri))<img src="{{ $logoDataUri }}" class="ticket-logo" alt="Logo El Casco">@endif
<div class="title">EL CASCO</div>
<div class="subtitle">COMPROBANTE NO FISCAL</div>
</div>

<section class="block">
<div class="meta-row"><strong>Comanda:</strong> {{ $comanda->id }}</div>
<div class="meta-row"><strong>Fecha:</strong> {{ $fecha->format('d/m/Y H:i') }}</div>
<div class="meta-row"><strong>Cliente:</strong> {{ !empty($comanda->nombre) ? $comanda->nombre : 'Consumidor final' }}</div>
</section>

<section>
@foreach($productos as $producto)
@php $cantidad=(int)($producto->cantidad??0); $precio=(float)($producto->precio??0); $importe=$cantidad*$precio; @endphp
<article class="item">
<div class="item-name">{{ $cantidad }} x {{ $producto->nombre }}</div>
<table class="ticket-table"><tr><td class="label muted">{{ $ars($precio) }} c/u</td><td class="value"><strong>{{ $ars($importe) }}</strong></td></tr></table>
</article>
@endforeach
</section>

<section class="total-wrap">
<table class="ticket-table total"><tr><td class="label">TOTAL</td><td class="value">{{ $ars($total) }}</td></tr></table>
</section>

<footer class="footer">Gracias por su visita<br>WhatsApp: {{ $telefono }}</footer>
</main>
</body>
</html>
