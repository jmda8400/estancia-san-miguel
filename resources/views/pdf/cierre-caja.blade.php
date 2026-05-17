<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<style>
@page { margin: 0; size: 58mm auto; }
body { margin: 0; padding: 0; background: #fff; color: #000; font-family: "DejaVu Sans Mono", "Courier New", monospace; font-size: 9px; }
.ticket { width: 53mm; margin: 0 auto; padding: 2.5mm; }
.center { text-align: center; }
.ticket-logo { display: block; margin: 0 auto 1.5mm; max-width: 24mm; max-height: 12mm; filter: grayscale(1) contrast(1.2); }
.sep { border-top: 1px dashed #000; margin: 1.8mm 0; }
.ticket-table { width: 100%; border-collapse: collapse; }
.ticket-table td { padding: 0; }
td.label { text-align: left; width: 68%; }
td.value { text-align: right; width: 32%; white-space: nowrap; }
.title { font-size: 11px; font-weight: 700; }
.total { font-weight: 700; font-size: 10px; }
.section { font-weight: 700; margin-bottom: 1mm; }
</style>
</head>
<body><main class="ticket">
<div class="center">@if(!empty($logoDataUri))<img src="{{ $logoDataUri }}" class="ticket-logo" alt="Logo">@endif<div class="title">ESTANCIA SAN MIGUEL</div><div>CIERRE DE CAJA</div><div>{{ now()->setTimezone('America/Argentina/Buenos_Aires')->format('d/m/Y H:i') }}</div></div>
<div class="sep"></div>
<div class="section">RESUMEN</div>
<table class="ticket-table total"><tr><td class="label">Total cobrado</td><td class="value">{{ $ars((float)($cierre['total_cobrado']??0)) }}</td></tr></table>
<table class="ticket-table"><tr><td class="label">Comandas</td><td class="value">{{ $cierre['comandas_cobradas']??0 }}</td></tr></table>
<table class="ticket-table"><tr><td class="label">Productos</td><td class="value">{{ $cierre['productos_cobrados']??0 }}</td></tr></table>
<div class="sep"></div>
<div class="section">EFECTIVO</div>
<table class="ticket-table"><tr><td class="label">Caja inicial</td><td class="value">{{ $ars((float)($cierre['caja_inicial']??0)) }}</td></tr></table>
<table class="ticket-table"><tr><td class="label">Efectivo esperado</td><td class="value">{{ $ars((float)($cierre['efectivo_esperado']??0)) }}</td></tr></table>
<table class="ticket-table"><tr><td class="label">Efectivo contado</td><td class="value">{{ $ars((float)($cierre['efectivo_contado']??0)) }}</td></tr></table>
<table class="ticket-table total"><tr><td class="label">Diferencia</td><td class="value">{{ $ars((float)($cierre['diferencia_efectivo']??0)) }}</td></tr></table>
<div class="sep"></div>
<div class="center">Cierre generado por sistema</div>
</main></body></html>
