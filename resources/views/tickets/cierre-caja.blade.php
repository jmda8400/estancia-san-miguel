<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cierre de caja 58mm</title>
    <style>
        @page { size: 58mm auto; margin: 0; }
        body { font-family: "Courier New", monospace; font-size: 11px; color: #000; margin: 0; }
        .ticket { width: 50mm; margin: 0 auto; padding: 2mm 1mm 4mm; }
        .center { text-align: center; }
        .logo { display:block; margin:0 auto 1.5mm; max-width:32mm; max-height:18mm; filter: grayscale(100%) contrast(1.35); }
        .line { margin: 1.5mm 0; }
        .row { display:flex; justify-content:space-between; }
    </style>
</head>
<body onload="window.print()">
<main class="ticket">
    <img src="{{ asset('logo.png') }}" alt="Estancia San Miguel" class="logo">
    <div class="center"><strong>CIERRE DE CAJA</strong><br>{{ now()->setTimezone('America/Argentina/Buenos_Aires')->format('d/m/Y H:i') }}</div>
    <div class="line">__________________________________</div>
    <div class="row"><span>Total cobrado</span><strong>{{ '$' . number_format($cierre['total_cobrado'],2,',','.') }}</strong></div>
    <div class="row"><span>Comandas cobradas</span><strong>{{ $cierre['comandas_cobradas'] }}</strong></div>
    <div class="row"><span>Productos cobrados</span><strong>{{ $cierre['productos_cobrados'] }}</strong></div>
    <div class="row"><span>Comandas abiertas</span><strong>{{ $cierre['comandas_abiertas'] }}</strong></div>
</main>
</body>
</html>
