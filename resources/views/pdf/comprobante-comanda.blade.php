<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<style>
@page { margin: 1.5mm 1.5mm 1mm; size: 58mm auto; }
* { box-sizing: border-box; }
body { margin: 0; padding: 0; background: #fff; color: #000; font-family: "DejaVu Sans Mono", "Courier New", monospace; font-size: 9px; }
.ticket { width: 100%; margin: 0 auto; padding: 0; }
.ticket, .ticket * { color: #000 !important; }
.center { text-align: center; }
.ticket-logo { display: block; margin: 0 auto 1mm; max-width: 22mm; max-height: 11mm; image-rendering: pixelated; }
.sep { border-top: 1px solid #000; margin: 1.2mm 0; height: 0; }
.ticket-table { width: 100%; border-collapse: collapse; }
.ticket-table td { padding: 0; vertical-align: top; color: #000 !important; }
td.label { text-align: left; width: 68%; }
td.value { text-align: right; width: 32%; white-space: nowrap; }
.title { font-size: 11px; font-weight: 700; letter-spacing: .4px; }
.subtitle { font-weight: 700; }
.total { font-weight: 700; font-size: 12px; }
.muted { font-size: 8px; }
.item-name { white-space: normal; word-break: break-word; }
.payment-qr { display: block; width: 28mm; max-width: 28mm; margin: 1.5mm auto 1mm; }
.meta-row { margin: .4mm 0; }
.item { margin: 1mm 0; }
.total-wrap { border-top: 1px solid #000; padding-top: .8mm; margin-top: .5mm; }
</style>
</head>
<body>
<main class="ticket">
<div class="center">@if(!empty($logoDataUri))<img src="{{ $logoDataUri }}" class="ticket-logo" alt="Logo">@endif
<div class="title">EL CASCO</div><div class="subtitle">COMPROBANTE NO FISCAL</div></div>
<div class="sep"></div>
<div class="meta-row"><strong>Comanda:</strong> {{ $comanda->id }}</div>
<div class="meta-row"><strong>Fecha:</strong> {{ $fecha->format('d/m/Y H:i') }}</div>
<div class="meta-row"><strong>Cliente:</strong> {{ !empty($comanda->nombre) ? $comanda->nombre : 'Consumidor final' }}</div>
<div class="sep"></div>
@foreach($productos as $producto)
@php $precio=(float)($producto->precio??0); $cantidad=(int)$producto->cantidad; $importe=$precio*$cantidad; @endphp
<article class="item">
<div class="item-name">{{ $cantidad }} x {{ $producto->nombre }}</div>
<table class="ticket-table"><tr><td class="label muted">{{ $ars($precio) }} c/u</td><td class="value"><strong>{{ $ars($importe) }}</strong></td></tr></table>
</article>
@endforeach
<div class="sep"></div>
<div class="total-wrap"><table class="ticket-table total"><tr><td class="label">TOTAL</td><td class="value">{{ $ars($total) }}</td></tr></table></div>
@if(!empty($paymentQrDataUri) && !empty($paymentValue))
<div class="sep"></div>
<div class="center">Escaneá para pagar</div>
<img src="{{ $paymentQrDataUri }}" class="payment-qr" alt="QR de pago">
<div class="center muted">{{ $paymentLabel }}: {{ $paymentValue }}</div>
@endif
<div class="sep"></div>
<div class="center muted">Gracias por su visita<br>WhatsApp: +54 2944 360712</div>
</main>
</body></html>
