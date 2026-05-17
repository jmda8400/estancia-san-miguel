<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<style>
@page { margin: 2mm; }
* { box-sizing: border-box; }
body { margin:0; font-family:"DejaVu Sans Mono","Courier New",monospace; font-size:10px; color:#111; }
.ticket { width:74mm; margin:0 auto; }
.center { text-align:center; }
.ticket-logo { display:block; margin:0 auto 2mm; max-width:28mm; max-height:14mm; }
.sep { border-top:1px dashed #666; margin:2mm 0; }
.ticket-table { width:100%; border-collapse:collapse; }
.ticket-table td { padding:0; vertical-align:top; }
td.label { text-align:left; width:66%; }
td.value { text-align:right; width:34%; white-space:nowrap; }
.total { font-weight:700; font-size:12px; }
.muted { font-size:9px; }
</style>
</head>
<body>
<main class="ticket">
<div class="center">@if(!empty($logoDataUri))<img src="{{ $logoDataUri }}" class="ticket-logo" alt="Logo">@endif
<div><strong>ESTANCIA SAN MIGUEL</strong></div><div>COMPROBANTE DE CONSUMO</div>
<div>N° {{ $comanda->id }} · {{ $fecha->format('d/m/Y H:i') }}</div></div>
<div class="sep"></div>
@foreach($productos as $producto)
@php $precio=(float)($producto->precio??0); $cantidad=(int)$producto->cantidad; $importe=$precio*$cantidad; @endphp
<div>{{ $cantidad }}x {{ $producto->nombre }}</div>
<table class="ticket-table"><tr><td class="label muted">{{ $ars($precio) }} c/u</td><td class="value"><strong>{{ $ars($importe) }}</strong></td></tr></table>
@endforeach
<div class="sep"></div>
<table class="ticket-table"><tr><td class="label">Subtotal</td><td class="value">{{ $ars($subtotal) }}</td></tr></table>
<table class="ticket-table total"><tr><td class="label">TOTAL</td><td class="value">{{ $ars($total) }}</td></tr></table>
<div class="sep"></div>
<div class="center muted">Gracias por su visita @if(!empty($telefono)) · Tel: {{ $telefono }}@endif</div>
</main>
</body></html>
