@extends('layout')
@section('title', 'Administración')
@section('content')
<div class="app-card">
    <h1 class="text-2xl font-semibold mb-2">Vista de administración</h1>
    <p class="text-neutral-800 mb-4">Evolución del stock por producto: cada curva muestra cómo fluctúa la cantidad a lo largo del tiempo.</p>
    <div class="mb-4 flex items-end gap-3">
        <label for="daysSelect" class="font-medium">Período (días)</label>
        <select id="daysSelect" class="app-input w-28">
            <option value="7">7</option>
            <option value="15">15</option>
            <option value="30" selected>30</option>
            <option value="60">60</option>
            <option value="90">90</option>
        </select>
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
    const days = document.getElementById('daysSelect').value;
    const response = await fetch(`/admin/graficas/data?days=${days}`);
    const data = await response.json();

    if (!data.series.length) {
        el.innerHTML = '<p class="text-neutral-700">No hay productos para mostrar en el período seleccionado.</p>';
        return;
    }

    const width = 980, height = 340, padding = 45;
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

    el.innerHTML = `<article class="rounded-lg border border-neutral-300 bg-neutral-100 p-3">
        <div class="app-chart-scroll">
            <svg viewBox="0 0 ${width} ${height}" class="app-chart-svg" role="img" aria-label="Fluctuación de stock por producto en el tiempo">
                <line x1="${padding}" y1="${height - padding}" x2="${width - padding}" y2="${height - padding}" class="app-chart-axis"></line>
                <line x1="${padding}" y1="${padding}" x2="${padding}" y2="${height - padding}" class="app-chart-axis"></line>
                ${lines.map((line) => `<polyline points="${line.polyline}" fill="none" stroke="${line.color}" stroke-width="2.5"></polyline>`).join('')}
                ${lines.map((line) => line.points.map((point) => `<circle cx="${point.x}" cy="${point.y}" r="3" fill="${line.color}"><title>${line.producto} · ${point.fecha}: ${point.cantidad}</title></circle>`).join('')).join('')}
            </svg>
        </div>
        <div class="app-chart-legend">
            ${lines.map((line) => `<div><span class="inline-block h-2 w-6 mr-2 align-middle" style="background:${line.color}"></span>${line.producto}</div>`).join('')}
        </div>
    </article>`;
}
renderAdminCharts();
document.getElementById('daysSelect').addEventListener('change', renderAdminCharts);
</script>
@endsection
