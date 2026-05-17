<!doctype html><html lang="es"><head><meta charset="utf-8"><style>
@page { margin:2mm; } body{margin:0;font-family:"DejaVu Sans Mono","Courier New",monospace;font-size:10px;color:#111}.ticket{width:74mm;margin:0 auto}.center{text-align:center}.ticket-logo{display:block;margin:0 auto 2mm;max-width:28mm;max-height:14mm}.sep{border-top:1px dashed #666;margin:2mm 0}.ticket-table{width:100%;border-collapse:collapse}.ticket-table td{padding:0}td.label{text-align:left;width:68%}td.value{text-align:right;width:32%;white-space:nowrap}.total{font-weight:700;font-size:12px}
</style></head><body><main class="ticket">
<div class="center">@if(!empty($logoDataUri))<img src="{{ $logoDataUri }}" class="ticket-logo" alt="Logo">@endif<div><strong>ESTANCIA SAN MIGUEL</strong></div><div>CIERRE DE CAJA</div><div>{{ now()->setTimezone('America/Argentina/Buenos_Aires')->format('d/m/Y H:i') }}</div></div>
<div class="sep"></div>
<table class="ticket-table total"><tr><td class="label">Total cobrado</td><td class="value">{{ $ars((float)($cierre['total_cobrado']??0)) }}</td></tr></table>
<table class="ticket-table"><tr><td class="label">Comandas cobradas</td><td class="value">{{ $cierre['comandas_cobradas']??0 }}</td></tr></table>
<table class="ticket-table"><tr><td class="label">Productos cobrados</td><td class="value">{{ $cierre['productos_cobrados']??0 }}</td></tr></table>
<table class="ticket-table"><tr><td class="label">Comandas abiertas</td><td class="value">{{ $cierre['comandas_abiertas']??0 }}</td></tr></table>
<div class="sep"></div>
<table class="ticket-table"><tr><td class="label">Caja inicial</td><td class="value">{{ $ars((float)($cierre['caja_inicial']??0)) }}</td></tr></table>
<table class="ticket-table"><tr><td class="label">Efectivo contado</td><td class="value">{{ $ars((float)($cierre['efectivo_contado']??0)) }}</td></tr></table>
<table class="ticket-table total"><tr><td class="label">Diferencia</td><td class="value">{{ $ars((float)($cierre['diferencia_efectivo']??0)) }}</td></tr></table>
</main></body></html>
