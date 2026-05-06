@extends('layout')
@section('title', 'Administración')
@section('content')
<div class="app-card">
    <h1 class="text-2xl font-semibold mb-2">Vista de administración</h1>
    <p class="text-neutral-800 mb-4">Evolución del stock: entradas (sube) y salidas (baja) en un mismo gráfico.</p>
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
        <h2 class="font-semibold mb-3">Gráfica única de movimientos</h2>
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
        el.innerHTML = '<p class="text-neutral-700">No hay movimientos de stock para el período seleccionado.</p>';
        return;
    }

    const width = 760, height = 240, padding = 30;
    const upValues = data.series.map(point => Number(point.sube || 0));
    const downValues = data.series.map(point => Number(point.baja || 0));
    const maxValue = Math.max(...upValues, ...downValues, 1);
    const stepX = data.series.length > 1 ? (width - padding * 2) / (data.series.length - 1) : 0;

    const toPoints = (values) => values.map((value, idx) => {
            const x = padding + (idx * stepX);
            const y = height - padding - ((value / maxValue) * (height - padding * 2));
            return { x, y, value, label: data.series[idx].fecha };
        });

    const upPoints = toPoints(upValues);
    const downPoints = toPoints(downValues);
    const upPolyline = upPoints.map(p => `${p.x},${p.y}`).join(' ');
    const downPolyline = downPoints.map(p => `${p.x},${p.y}`).join(' ');

    el.innerHTML = `<article class="rounded-lg border border-neutral-300 bg-neutral-100 p-3">
        <svg viewBox="0 0 ${width} ${height}" class="app-chart-svg" role="img" aria-label="Movimientos de stock por día">
            <line x1="${padding}" y1="${height - padding}" x2="${width - padding}" y2="${height - padding}" class="app-chart-axis"></line>
            <line x1="${padding}" y1="${padding}" x2="${padding}" y2="${height - padding}" class="app-chart-axis"></line>
            <polyline points="${upPolyline}" class="app-chart-line-up"></polyline>
            <polyline points="${downPolyline}" class="app-chart-line-down"></polyline>
            ${upPoints.map(p => `<circle cx="${p.x}" cy="${p.y}" r="3.5" class="app-chart-dot-up"><title>${p.label}: sube ${p.value}</title></circle>`).join('')}
            ${downPoints.map(p => `<circle cx="${p.x}" cy="${p.y}" r="3.5" class="app-chart-dot-down"><title>${p.label}: baja ${p.value}</title></circle>`).join('')}
        </svg>
        <div class="app-chart-legend">
            <div><span class="inline-block h-2 w-6 mr-2 align-middle" style="background:#1f3b2d"></span>Sube stock (sumas)</div>
            <div><span class="inline-block h-2 w-6 mr-2 align-middle" style="background:#5f5f5f"></span>Baja stock (restas)</div>
        </div>
    </article>`;
}
renderAdminCharts();
document.getElementById('daysSelect').addEventListener('change', renderAdminCharts);
</script>
@endsection
