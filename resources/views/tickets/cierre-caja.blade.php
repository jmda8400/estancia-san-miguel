<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cierre de caja ticket</title>
    <style>
        @page { size: 80mm auto; margin: 2mm; }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; background: #fff; }
        body { font-family: "DejaVu Sans Mono", "Courier New", Courier, monospace; font-size: 10.5px; color: #111; }
        .ticket { width: 74mm; margin: 0 auto; padding: 1mm 1.5mm 3mm; }
        .ticket-header, .ticket-footer, .ticket-meta { text-align: center; }
        .ticket-logo { display:block; margin:0 auto 1.5mm; max-width:28mm; max-height:14mm; width:auto; height:auto; opacity:.85; }
        .ticket-title { font-weight:700; font-size:12px; letter-spacing:.7px; }
        .ticket-subtitle { margin-top:.5mm; }
        .ticket-meta { line-height:1.25; margin-top:1mm; }
        .ticket-line { border-top:1px dashed #555; margin:2mm 0; height:0; }
        .ticket-section { margin: 1.2mm 0; }
        .ticket-section-title { font-weight:700; margin-bottom:.8mm; }
        .ticket-row, .ticket-row-strong { width:100%; border-collapse:collapse; }
        .ticket-row td, .ticket-row-strong td { padding:0; vertical-align:top; }
        .label { width:68%; }
        .value { width:32%; text-align:right; white-space:nowrap; }
        .ticket-row-strong { font-weight:700; font-size:11.5px; }
        .muted { font-size:9.5px; }
    </style>
</head>
<body @if(($autoPrint ?? true)) onload="window.print()" @endif>
@php
    $ars = $ars ?? fn (float $importe) => '$' . number_format($importe, ((float) $importe == floor((float) $importe)) ? 0 : 2, ',', '.');
    $diferencia = (float) ($cierre['diferencia_efectivo'] ?? 0);
    $estado = $diferencia == 0.0 ? 'Sin diferencia' : ($diferencia > 0 ? 'Sobrante' : 'Faltante');
    $responsable = trim((string) ($cierre['responsable'] ?? '')) ?: 'Diego Lopez';
    $medios = collect($cierre['movimientos'] ?? [])->filter(fn($m) => is_array($m) || is_object($m));
@endphp
<main class="ticket">
    <header class="ticket-header">
        <img src="{{ asset('logo.png') }}" alt="Estancia San Miguel" class="ticket-logo">
        <div class="ticket-title">ESTANCIA SAN MIGUEL</div>
        <div class="ticket-subtitle">Cierre de caja</div>
        <div class="ticket-meta">{{ now()->setTimezone('America/Argentina/Buenos_Aires')->format('d/m/Y H:i') }}</div>
    </header>

    <div class="ticket-line"></div>

    <section class="ticket-section">
        <div class="ticket-section-title">RESUMEN DE CAJA</div>
        <table class="ticket-row-strong"><tr><td class="label">Total cobrado</td><td class="value">{{ $ars((float) ($cierre['total_cobrado'] ?? 0)) }}</td></tr></table>
        <table class="ticket-row"><tr><td class="label">Comandas cobradas</td><td class="value">{{ $cierre['comandas_cobradas'] ?? 0 }}</td></tr></table>
        <table class="ticket-row"><tr><td class="label">Productos cobrados</td><td class="value">{{ $cierre['productos_cobrados'] ?? 0 }}</td></tr></table>
        <table class="ticket-row"><tr><td class="label">Comandas abiertas</td><td class="value">{{ $cierre['comandas_abiertas'] ?? 0 }}</td></tr></table>
    </section>

    <div class="ticket-line"></div>

    <section class="ticket-section">
        <div class="ticket-section-title">TURNO</div>
        <table class="ticket-row"><tr><td class="label">Fecha/Hora</td><td class="value">{{ now()->setTimezone('America/Argentina/Buenos_Aires')->format('d/m/Y H:i') }}</td></tr></table>
        <table class="ticket-row"><tr><td class="label">Responsable</td><td class="value">{{ $responsable }}</td></tr></table>
    </section>

    @if($medios->isNotEmpty())
    <div class="ticket-line"></div>
    <section class="ticket-section">
        <div class="ticket-section-title">MEDIOS DE PAGO</div>
        @foreach($medios as $mov)
            @php
                $nombre = $mov['nombre'] ?? $mov['medio'] ?? null;
                $importe = (float) ($mov['monto'] ?? $mov['importe'] ?? 0);
            @endphp
            @if($nombre && $importe != 0.0)
                <table class="ticket-row"><tr><td class="label">{{ $nombre }}</td><td class="value">{{ $ars($importe) }}</td></tr></table>
            @endif
        @endforeach
    </section>
    @endif

    <div class="ticket-line"></div>

    <section class="ticket-section">
        <div class="ticket-section-title">CONTROL DE EFECTIVO</div>
        <table class="ticket-row"><tr><td class="label">Caja inicial</td><td class="value">{{ $ars((float) ($cierre['caja_inicial'] ?? 0)) }}</td></tr></table>
        <table class="ticket-row"><tr><td class="label">Efectivo contado</td><td class="value">{{ $ars((float) ($cierre['efectivo_contado'] ?? 0)) }}</td></tr></table>
        <table class="ticket-row-strong"><tr><td class="label">Diferencia</td><td class="value">{{ $ars($diferencia) }}</td></tr></table>
        <table class="ticket-row-strong"><tr><td class="label">Estado</td><td class="value">{{ $estado }}</td></tr></table>
    </section>

    <div class="ticket-line"></div>

    <footer class="ticket-footer">
        <div>Cierre generado por sistema</div>
        <div>Estancia San Miguel</div>
        <div>Tel: +542944360712</div>
        <div class="muted">Conserve este comprobante</div>
    </footer>
</main>
</body>
</html>
