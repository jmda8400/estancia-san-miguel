<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Cierre de caja</title>
<style>
@page { margin: 1.5mm; size: 58mm auto; }
html, body { margin:0; padding:0; background:#fff; }
body { font-family: DejaVu Sans, sans-serif; font-size: 9.2px; line-height:1.2; color:#000; }
.ticket { width:100%; }
.center { text-align:center; }
.ticket-logo { display:block; margin:0 auto 1mm; max-width:22mm; max-height:11mm; }
.title { font-weight:700; font-size:11.5px; letter-spacing:.4px; }
.subtitle { font-size:9.5px; font-weight:700; margin-top:.3mm; }
.block { border-top:.25mm solid #333; border-bottom:.15mm solid #888; padding:1mm 0; margin:1.2mm 0; }
.section-title { font-weight:700; font-size:9px; letter-spacing:.3px; margin-bottom:.7mm; }
.ticket-table { width:100%; border-collapse:collapse; }
.ticket-table td { padding:.15mm 0; }
.label { width:68%; }
.value { width:32%; text-align:right; white-space:nowrap; }
.total { font-weight:700; }
.diff-zero { font-weight:600; }
.diff-neg { font-weight:700; }
.diff-pos { font-weight:700; }
.footer { text-align:center; margin-top:1.2mm; font-size:8.6px; }
</style>
</head>
<body><main class="ticket">
<div class="center">
@if(!empty($logoDataUri))<img src="{{ $logoDataUri }}" class="ticket-logo" alt="Logo El Casco">@endif
<div class="title">EL CASCO</div>
<div class="subtitle">CIERRE DE CAJA</div>
</div>

<section class="block">
<div><strong>Fecha:</strong> {{ $fecha->format('d/m/Y H:i') }}</div>
<div><strong>Responsable:</strong> {{ $responsable }}</div>
</section>

<section class="block">
<div class="section-title">RESUMEN</div>
<table class="ticket-table total"><tr><td class="label">Total cobrado</td><td class="value">{{ $ars((float)($cierre['total_cobrado']??0)) }}</td></tr></table>
<table class="ticket-table"><tr><td class="label">Comandas</td><td class="value">{{ $cierre['comandas_cobradas']??0 }}</td></tr></table>
<table class="ticket-table"><tr><td class="label">Productos</td><td class="value">{{ $cierre['productos_cobrados']??0 }}</td></tr></table>
</section>

<section class="block">
<div class="section-title">EFECTIVO</div>
<table class="ticket-table"><tr><td class="label">Caja inicial</td><td class="value">{{ $ars((float)($cierre['caja_inicial']??0)) }}</td></tr></table>
<table class="ticket-table"><tr><td class="label">Efectivo esperado</td><td class="value">{{ $ars((float)($cierre['efectivo_esperado']??0)) }}</td></tr></table>
<table class="ticket-table"><tr><td class="label">Efectivo contado</td><td class="value">{{ $ars((float)($cierre['efectivo_contado']??0)) }}</td></tr></table>
@php $dif=(float)($cierre['diferencia_efectivo']??0); $tipo=$dif===0.0?'diff-zero':($dif<0?'diff-neg':'diff-pos'); $texto=$dif===0.0?'(Sin diferencia)':($dif<0?'(Faltante)':'(Sobrante)'); @endphp
<table class="ticket-table total {{ $tipo }}"><tr><td class="label">Diferencia {{ $texto }}</td><td class="value">{{ $ars($dif) }}</td></tr></table>
</section>

<footer class="footer">Cierre generado por sistema</footer>
</main></body></html>
