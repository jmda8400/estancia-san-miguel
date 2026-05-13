@extends('layouts.internal')
@section('title', 'Administración')
@section('internal_title', 'Vista de administración')
@section('internal_content')
<div class="app-card">
    <div class="mb-4 grid gap-3 md:grid-cols-4">
        <div>
            <label for="startDate" class="font-medium block mb-1">Desde</label>
            <input id="startDate" type="date" class="app-input w-full">
        </div>
        <div>
            <label for="endDate" class="font-medium block mb-1">Hasta</label>
            <input id="endDate" type="date" class="app-input w-full">
        </div>
        <div class="md:col-span-2 flex items-end gap-2 flex-wrap">
            <button class="app-btn" data-range="7">Últimos 7 días</button>
            <button class="app-btn app-btn-active" data-range="30">Últimos 30 días</button>
            <button class="app-btn" data-range="90">Últimos 90 días</button>
            <button id="applyRange" class="app-btn">Aplicar rango</button>
        </div>
    </div>
    <div class="app-chart-wrap mb-4">
        <h2 class="font-semibold mb-3">Configuración del ticket</h2>
        <div class="grid md:grid-cols-2 gap-3">
            <input id="telefonoLocal" class="app-input w-full" placeholder="Número de teléfono">
            <input id="transferAlias" class="app-input w-full" placeholder="Alias de transferencia">
            <input id="transferCbu" class="app-input w-full" placeholder="CBU (22 dígitos)">
            <input id="transferHolder" class="app-input w-full" placeholder="Titular de cuenta (opcional)">
            <input id="transferTaxId" class="app-input w-full md:col-span-2" placeholder="CUIT/CUIL del titular (opcional)">
            <button id="savePhone" class="app-btn md:col-span-2">Guardar configuración de ticket y pagos</button>
        </div>
        <p class="text-xs mt-2 text-neutral-700">Se imprimirá en: <code>storage/app/public/comprobantes</code></p>
    </div>
    <section class="app-chart-wrap">
        <h2 class="font-semibold mb-3">Fluctuación de cantidad por producto</h2>
        <div id="adminCharts"></div>
    </section>
</div>
@endsection
@section('scripts')
<script>
async function renderAdminCharts() {
    const el = document.getElementById('adminCharts');
    const params = new URLSearchParams({
        start: document.getElementById('startDate').value,
        end: document.getElementById('endDate').value,
    });
    const response = await fetch(`/admin/graficas/data?${params.toString()}`);
    const data = await response.json();

    if (!data.series.length) {
        el.innerHTML = '<p class="text-neutral-700">No hay productos para mostrar en el período seleccionado.</p>';
        return;
    }

    const width = 980, height = 380, padding = 58;
    const colors = ['#1f3b2d', '#5f5f5f', '#395b8a', '#7a4f95', '#a35d2f', '#2f7f6f'];
    const allValues = data.series.flatMap((product) => product.puntos.map((point) => Number(point.cantidad || 0)));
    const maxValue = Math.max(...allValues, 1);
    const minValue = Math.min(...allValues, 0);
    const span = Math.max(maxValue - minValue, 1);
    const steps = data.series[0].puntos.length;
    const stepX = steps > 1 ? (width - padding * 2) / (steps - 1) : 0;

    const toPoints = (points) => points.map((point, idx) => {
        const x = padding + (idx * stepX);
        const y = height - padding - (((point.cantidad - minValue) / span) * (height - padding * 2));
        return { x, y, ...point };
    });

    const lines = data.series.map((product, idx) => {
        const points = toPoints(product.puntos);
        const polyline = points.map((point) => `${point.x},${point.y}`).join(' ');
        const color = colors[idx % colors.length];

        return {
            producto: product.producto,
            color,
            points,
            polyline,
        };
    });

    const yTicks = 5;
    const tickValues = Array.from({ length: yTicks + 1 }, (_, i) => minValue + ((span / yTicks) * i));
    const gridLines = tickValues.map((value) => {
        const y = height - padding - (((value - minValue) / span) * (height - padding * 2));
        return `<g><line x1="${padding}" y1="${y}" x2="${width - padding}" y2="${y}" class="app-chart-grid-line"></line><text x="${padding - 10}" y="${y + 4}" text-anchor="end" class="app-chart-axis-text">${Math.round(value)}</text></g>`;
    }).join('');
    const xLabels = data.series[0].puntos.map((point, idx) => {
        if (idx % Math.ceil(steps / 6) !== 0 && idx !== steps - 1) return '';
        const x = padding + (idx * stepX);
        return `<text x="${x}" y="${height - padding + 20}" text-anchor="middle" class="app-chart-axis-text">${point.fecha.slice(5)}</text>`;
    }).join('');

    el.innerHTML = `<article class="rounded-lg border border-neutral-300 bg-neutral-100 p-3">
        <div class="app-chart-scroll">
            <svg viewBox="0 0 ${width} ${height}" class="app-chart-svg" role="img" aria-label="Fluctuación de stock por producto en el tiempo">
                ${gridLines}
                <line x1="${padding}" y1="${height - padding}" x2="${width - padding}" y2="${height - padding}" class="app-chart-axis"></line>
                <line x1="${padding}" y1="${padding}" x2="${padding}" y2="${height - padding}" class="app-chart-axis"></line>
                ${lines.map((line) => `<polyline points="${line.polyline}" fill="none" stroke="${line.color}" stroke-width="2.5"></polyline>`).join('')}
                ${lines.map((line) => line.points.map((point) => `<circle cx="${point.x}" cy="${point.y}" r="3" fill="${line.color}"><title>${line.producto} · ${point.fecha}: ${point.cantidad}</title></circle>`).join('')).join('')}
                ${xLabels}
            </svg>
        </div>
        <div class="app-chart-legend">
            ${lines.map((line) => `<div><span class="inline-block h-2 w-6 mr-2 align-middle" style="background:${line.color}"></span>${line.producto}</div>`).join('')}
        </div>
    </article>`;
}
function setRange(days) {
    const end = new Date();
    const start = new Date();
    start.setDate(end.getDate() - (days - 1));
    document.getElementById('startDate').value = start.toISOString().slice(0, 10);
    document.getElementById('endDate').value = end.toISOString().slice(0, 10);
}
async function loadPhoneConfig(){
    const response = await fetch('/admin/configuracion');
    const data = await response.json();
    document.getElementById('telefonoLocal').value = data.telefono_local || '';
    document.getElementById('transferAlias').value = data.transfer_alias || '';
    document.getElementById('transferCbu').value = data.transfer_cbu || '';
    document.getElementById('transferHolder').value = data.transfer_account_holder || '';
    document.getElementById('transferTaxId').value = data.transfer_account_tax_id || '';
}

document.getElementById('savePhone').addEventListener('click', async () => {
    const telefono_local = document.getElementById('telefonoLocal').value.trim();
    const transfer_alias = document.getElementById('transferAlias').value.trim();
    const transfer_cbu = document.getElementById('transferCbu').value.trim();
    const transfer_account_holder = document.getElementById('transferHolder').value.trim();
    const transfer_account_tax_id = document.getElementById('transferTaxId').value.trim();
    if (!telefono_local) return alert('Ingrese un teléfono válido.');
    if (transfer_cbu && !/^\d{22}$/.test(transfer_cbu)) return alert('El CBU debe tener 22 dígitos numéricos.');
    const response = await fetch('/admin/configuracion/telefono', {
        method: 'PUT',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
        body: JSON.stringify({ telefono_local, transfer_alias, transfer_cbu, transfer_account_holder, transfer_account_tax_id }),
    });
    if (!response.ok) return alert('No se pudo guardar la configuración.');
    alert('Configuración guardada.');
});

setRange(30);
loadPhoneConfig();
renderAdminCharts();
document.getElementById('applyRange').addEventListener('click', renderAdminCharts);
document.querySelectorAll('[data-range]').forEach((btn) => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('[data-range]').forEach((x) => x.classList.remove('app-btn-active'));
        btn.classList.add('app-btn-active');
        setRange(Number(btn.dataset.range));
        loadPhoneConfig();
renderAdminCharts();
    });
});
</script>
@endsection
