<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Cierre de caja</title>
    <style>
        body { font-family: monospace; width: 58mm; margin: 0; font-size: 11px; }
        h1 { font-size: 13px; margin: 0 0 6px; text-align: center; }
        .row { display: flex; justify-content: space-between; }
        .sep { border-top: 1px dashed #000; margin: 6px 0; }
    </style>
</head>
<body onload="window.print()">
    <h1>CIERRE DE CAJA</h1>
    <div>{{ now()->format('d/m/Y H:i') }}</div>
    <div class="sep"></div>
    <div class="row"><span>Total cobrado</span><strong>${{ number_format($cierre['total_cobrado'], 2, ',', '.') }}</strong></div>
    <div class="row"><span>Comandas cobradas</span><strong>{{ $cierre['comandas_cobradas'] }}</strong></div>
    <div class="row"><span>Productos cobrados</span><strong>{{ $cierre['productos_cobrados'] }}</strong></div>
    <div class="row"><span>Comandas abiertas</span><strong>{{ $cierre['comandas_abiertas'] }}</strong></div>
</body>
</html>

