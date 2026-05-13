<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Comprobante 58mm</title>
    <style>
        @page { size: 58mm auto; margin: 0; }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; background: #fff; }
        body { font-family: "Courier New", monospace; font-size: 11px; color: #000; }
        .ticket { width: 50mm; margin: 0 auto; padding: 2mm 1mm 4mm; }
        .center { text-align: center; }
        .logo { display: block; margin: 0 auto 1.5mm; max-width: 32mm; max-height: 18mm; width: auto; height: auto; object-fit: contain; filter: grayscale(100%) contrast(1.35); }
        .line { font-size: 11px; letter-spacing: 0.4px; overflow: hidden; white-space: nowrap; margin: 1.5mm 0; }
        .meta { line-height: 1.25; }
        .item { margin-bottom: 1.2mm; }
        .item-name { white-space: normal; word-break: break-word; }
        .row { display: flex; justify-content: space-between; gap: 1mm; align-items: flex-start; }
        .row .right { text-align: right; white-space: nowrap; }
        .totals .row { margin-bottom: 0.8mm; }
        .total { font-weight: 700; font-size: 12px; }
        .muted { font-size: 10px; }
        @media screen { body { padding: 8px; background: #f2f2f2; } .ticket { background: #fff; } }
    </style>
</head>
<body @if(($autoPrint ?? true)) onload="window.print()" @endif>
<main class="ticket">
    <img src="{{ asset('logo.png') }}" alt="Estancia San Miguel" class="logo">
    <div class="center meta">
        <div><strong>ESTANCIA SAN MIGUEL</strong></div>
        <div>Comprobante de consumo</div>
        <div>N° comanda: {{ $comanda->id }}</div>
        <div>{{ now()->setTimezone('America/Argentina/Buenos_Aires')->format('d/m/Y H:i') }}</div>
        <div>{{ $comanda->nombre ?? ('Mesa ' . $comanda->mesa_numero) }}</div>
    </div>

    <div class="line">{{ str_repeat($separatorChar ?? "_", 34) }}</div>

    @foreach($productos as $producto)
        @php
            $precio = (float) ($producto->precio ?? 0);
            $cantidad = (int) $producto->cantidad;
            $importe = $precio * $cantidad;
        @endphp
        <article class="item">
            <div class="item-name">{{ $cantidad }}x {{ $producto->nombre }}</div>
            <div class="row">
                <span class="muted">{{ $ars($precio) }} c/u</span>
                <strong class="right">{{ $ars($importe) }}</strong>
            </div>
            @if(!empty($producto->notas))
                <div class="muted">Nota: {{ $producto->notas }}</div>
            @endif
        </article>
    @endforeach

    <div class="line">{{ str_repeat($separatorChar ?? "_", 34) }}</div>

    <section class="totals">
        <div class="row"><span>Subtotal</span><span class="right">{{ $ars($subtotal) }}</span></div>
        @if($descuento > 0)
            <div class="row"><span>Descuento</span><span class="right">-{{ $ars($descuento) }}</span></div>
        @endif
        <div class="row total"><span>TOTAL</span><span class="right">{{ $ars($total) }}</span></div>
    </section>

    @if(!empty($transferencia['alias']) || !empty($transferencia['cbu']) || !empty($transferencia['titular']) || !empty($transferencia['cuit']))
        <div class="line">{{ str_repeat($separatorChar ?? "_", 34) }}</div>
        <section class="meta">
            <div><strong>Datos de transferencia</strong></div>
            @if(!empty($transferencia['alias']))<div>Alias: {{ $transferencia['alias'] }}</div>@endif
            @if(!empty($transferencia['cbu']))<div>CBU: {{ $transferencia['cbu'] }}</div>@endif
            @if(!empty($transferencia['titular']))<div>Titular: {{ $transferencia['titular'] }}</div>@endif
            @if(!empty($transferencia['cuit']))<div>CUIT/CUIL: {{ $transferencia['cuit'] }}</div>@endif
        </section>
    @endif

    <div class="line">{{ str_repeat($separatorChar ?? "_", 34) }}</div>

    <footer class="center meta">
        <div>Gracias por su visita</div>
        <div>Estancia San Miguel</div>
        @if(!empty($telefono))<div>Tel: {{ $telefono }}</div>@endif
        <div class="muted">Conserve este comprobante</div>
    </footer>
</main>
</body>
</html>
