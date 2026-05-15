<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cierre de caja 58mm</title>
    <style>
        @page { size: 58mm auto; margin: 0; }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; background: #fff; }
        body { font-family: "Courier New", monospace; font-size: 11px; color: #000; }
        .ticket { width: 50mm; margin: 0 auto; padding: 2mm 1mm 4mm; }
        .center { text-align: center; }
        .logo { display: block; margin: 0 auto 1.5mm; max-width: 32mm; max-height: 18mm; width: auto; height: auto; object-fit: contain; image-rendering: pixelated; filter: grayscale(100%) contrast(1.35); }
        .line { font-size: 11px; letter-spacing: 0.4px; overflow: hidden; white-space: nowrap; margin: 1.5mm 0; }
        .meta { line-height: 1.25; }
        .row { display:flex; justify-content:space-between; align-items:flex-start; gap: 1mm; }
        .row .right { text-align: right; white-space: nowrap; }
        .totals .row { margin-bottom: 0.8mm; }
        .strong { font-weight: 700; }
        .muted { font-size: 10px; }
        @media screen { body { padding: 8px; background: #f2f2f2; } .ticket { background: #fff; } }
    </style>
</head>
<body onload="window.print()">
<main class="ticket">
    <img src="{{ asset('logo.png') }}" alt="Estancia San Miguel" class="logo">

    <div class="center meta">
        <div><strong>ESTANCIA SAN MIGUEL</strong></div>
        <div>Comprobante de cierre de caja</div>
        <div>{{ now()->setTimezone('America/Argentina/Buenos_Aires')->format('d/m/Y H:i') }}</div>
        <div>Turno: {{ $cierre['turno'] ?? '-' }}</div>
        <div>Responsable: {{ $cierre['responsable'] ?? '-' }}</div>
    </div>

    <div class="line">{{ str_repeat('_', 34) }}</div>

    <section class="totals">
        <div class="row"><span>Total cobrado</span><strong class="right">{{ '$' . number_format($cierre['total_cobrado'],2,',','.') }}</strong></div>
        <div class="row"><span>Comandas cobradas</span><strong class="right">{{ $cierre['comandas_cobradas'] }}</strong></div>
        <div class="row"><span>Productos cobrados</span><strong class="right">{{ $cierre['productos_cobrados'] }}</strong></div>
        <div class="row"><span>Comandas abiertas</span><strong class="right">{{ $cierre['comandas_abiertas'] }}</strong></div>
        <div class="row"><span>Caja inicial</span><strong class="right">{{ '$' . number_format($cierre['caja_inicial'] ?? 0,2,',','.') }}</strong></div>
        <div class="row"><span>Efectivo contado</span><strong class="right">{{ '$' . number_format($cierre['efectivo_contado'] ?? 0,2,',','.') }}</strong></div>
        <div class="row strong"><span>Diferencia</span><span class="right">{{ '$' . number_format($cierre['diferencia_efectivo'] ?? 0,2,',','.') }}</span></div>
    </section>

    <div class="line">{{ str_repeat('_', 34) }}</div>

    <footer class="center meta">
        <div>Gracias por su trabajo</div>
        <div>Estancia San Miguel</div>
        @if(!empty($cierre['observaciones']))<div class="muted">Obs: {{ $cierre['observaciones'] }}</div>@endif
        <div class="muted">Conserve este comprobante</div>
    </footer>
</main>
</body>
</html>
