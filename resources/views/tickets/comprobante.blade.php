<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Comprobante ticket</title>
    <style>
        @page { size: 80mm auto; margin: 2mm; }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; background: #fff; }
        body { font-family: "DejaVu Sans Mono", "Courier New", Courier, monospace; font-size: 10.5px; color: #111; }
        .ticket { width: 74mm; margin: 0 auto; padding: 1mm 1.5mm 3mm; }
        .ticket-header, .ticket-footer, .ticket-meta { text-align: center; }
        .ticket-logo { display:block; margin: 0 auto 1.5mm; max-width: 28mm; max-height: 14mm; width: auto; height: auto; opacity: .85; }
        .ticket-title { font-weight: 700; font-size: 12px; letter-spacing: .7px; }
        .ticket-subtitle { margin-top: .5mm; }
        .ticket-meta { line-height: 1.25; margin-top: 1mm; }
        .ticket-line { border-top: 1px dashed #555; margin: 2mm 0; height: 0; }
        .ticket-section { margin: 1.5mm 0; }
        .ticket-item { margin: 1.5mm 0; }
        .ticket-item-name { white-space: normal; word-wrap: break-word; }
        .ticket-row, .ticket-row-strong { width: 100%; border-collapse: collapse; }
        .ticket-row td, .ticket-row-strong td { padding: 0; vertical-align: top; }
        .ticket-row .label, .ticket-row-strong .label { width: 65%; }
        .ticket-row .value, .ticket-row-strong .value { width: 35%; text-align: right; white-space: nowrap; }
        .ticket-row-strong { font-weight: 700; font-size: 11.5px; }
        .muted { font-size: 9.5px; }
    </style>
</head>
<body @if(($autoPrint ?? true)) onload="window.print()" @endif>
<main class="ticket">
    <header class="ticket-header">
        <img src="{{ asset('logo.png') }}" alt="Estancia San Miguel" class="ticket-logo">
        <div class="ticket-title">ESTANCIA SAN MIGUEL</div>
        <div class="ticket-subtitle">Comprobante de consumo</div>
        <div class="ticket-meta">
            <div>N° comanda: {{ $comanda->id }}</div>
            <div>{{ now()->setTimezone('America/Argentina/Buenos_Aires')->format('d/m/Y H:i') }}</div>
            @if(!empty($comanda->mesa_numero))<div>Mesa {{ $comanda->mesa_numero }}</div>@endif
            @if(!empty($comanda->nombre))<div>Cliente: {{ $comanda->nombre }}</div>@endif
        </div>
    </header>

    <div class="ticket-line"></div>

    <section class="ticket-section">
        @foreach($productos as $producto)
            @php
                $precio = (float) ($producto->precio ?? 0);
                $cantidad = (int) $producto->cantidad;
                $importe = $precio * $cantidad;
            @endphp
            <article class="ticket-item">
                <div class="ticket-item-name">{{ $cantidad }}x {{ $producto->nombre }}</div>
                <table class="ticket-row">
                    <tr>
                        <td class="label muted">{{ $ars($precio) }} c/u</td>
                        <td class="value"><strong>{{ $ars($importe) }}</strong></td>
                    </tr>
                </table>
                @if(!empty($producto->notas))<div class="muted">Nota: {{ $producto->notas }}</div>@endif
            </article>
        @endforeach
    </section>

    <div class="ticket-line"></div>

    <section class="ticket-section">
        <table class="ticket-row"><tr><td class="label">Subtotal</td><td class="value">{{ $ars($subtotal) }}</td></tr></table>
        @if($descuento > 0)
            <table class="ticket-row"><tr><td class="label">Descuento</td><td class="value">-{{ $ars($descuento) }}</td></tr></table>
        @endif
        <table class="ticket-row-strong"><tr><td class="label">TOTAL</td><td class="value">{{ $ars($total) }}</td></tr></table>
    </section>

    @if(!empty($adminAliasQrUrl))
        <div class="ticket-line"></div>
        <section class="ticket-meta">
            <div><strong>Alias administración</strong></div>
            <div>{{ $adminAlias }}</div>
            <img src="{{ $adminAliasQrUrl }}" alt="QR Alias" style="width:20mm;height:20mm;image-rendering:pixelated;margin:1.2mm auto 0;display:block;">
        </section>
    @endif

    <div class="ticket-line"></div>

    <footer class="ticket-footer">
        <div>Gracias por su visita</div>
        <div>Estancia San Miguel</div>
        @if(!empty($telefono))<div>Tel: {{ $telefono }}</div>@endif
        <div class="muted">Conserve este comprobante</div>
    </footer>
</main>
</body>
</html>
