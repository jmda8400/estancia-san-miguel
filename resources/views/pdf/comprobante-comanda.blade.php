<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<style>
@page { margin: 0; size: 58mm auto; }
* { box-sizing: border-box; }
body { margin: 0; padding: 0; background: #fff; color: #000; font-family: "DejaVu Sans Mono", "Courier New", monospace; font-size: 9px; }
.ticket { width: 53mm; margin: 0 auto; padding: 2mm 2mm 1.5mm; }
.ticket, .ticket * { color: #000 !important; }
.center { text-align: center; }
.ticket-logo { display: block; margin: 0 auto 1mm; max-width: 22mm; max-height: 11mm; image-rendering: crisp-edges; }
.sep { border-top: 1px solid #000; margin: 1.4mm 0; }
.ticket-table { width: 100%; border-collapse: collapse; }
.ticket-table td { padding: 0; vertical-align: top; color: #000 !important; }
td.label { text-align: left; width: 68%; }
td.value { text-align: right; width: 32%; white-space: nowrap; }
.title { font-size: 11px; font-weight: 700; letter-spacing: .4px; }
.subtitle { font-size: 10px; font-weight: 700; }
.total { font-weight: 700; font-size: 11px; border-top: 1px solid #000; }
.total td { padding-top: .8mm; }
.muted { font-size: 8.5px; }
.item-name { white-space: normal; word-break: break-word; }
.item { margin: 1mm 0; }
.payment-qr { display: block; width: 28mm; max-width: 28mm; margin: 1.5mm auto 1mm; }
</style>
</head>
<body>
<main class="ticket">
<div class="center">@if(!empty($logoDataUri))<img src="{{ $logoDataUri }}" class="ticket-logo" alt="Logo">@endif
<div class="title">EL CASCO</div><div class="subtitle">COMPROBANTE NO FISCAL</div></div>
<div class="sep"></div>
<table class="ticket-table">
<tr><td class="label">Comanda:</td><td class="value">{{ $comanda->id }}</td></tr>
<tr><td class="label">Fecha:</td><td class="value">{{ $fecha->format('d/m/Y H:i') }}</td></tr>
<tr><td class="label">Cliente:</td><td class="value">{{ !empty($comanda->nombre) ? $comanda->nombre : 'Consumidor final' }}</td></tr>
</table>
<div class="sep"></div>
@foreach($productos as $producto)
@php $precio=(float)($producto->precio??0); $cantidad=(int)$producto->cantidad; $importe=$precio*$cantidad; @endphp
<article class="item"><div class="item-name">{{ $cantidad }} x {{ $producto->nombre }}</div>
<table class="ticket-table"><tr><td class="label muted">{{ $ars($precio) }} c/u</td><td class="value"><strong>{{ $ars($importe) }}</strong></td></tr></table>
</article>
@endforeach
<div class="sep"></div>
<table class="ticket-table total"><tr><td class="label">TOTAL</td><td class="value">{{ $ars($total) }}</td></tr></table>
@if(!empty($paymentQrDataUri) && !empty($paymentValue))
<div class="sep"></div>
<div class="center">Escaneá para pagar</div>
<img src="{{ $paymentQrDataUri }}" class="payment-qr" alt="QR de pago">
<div class="center muted">{{ $paymentLabel }}: {{ $paymentValue }}</div>
@endif
<div class="sep"></div>
<div class="center muted">WhatsApp: +54 2944 360712<br>Gracias por su visita</div>
</main>
</body></html>
