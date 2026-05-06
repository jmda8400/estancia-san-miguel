@extends('layout')
@section('title', 'Administración')
@section('content')
<div class="app-card">
    <h1 class="text-2xl font-semibold mb-2">Vista de administración</h1>
    <p class="text-amber-950 mb-4">Sección consolidada de gráficas de consumo (últimos 30 días).</p>
    <section class="app-chart-wrap">
        <h2 class="font-semibold mb-3">Graficas</h2>
        <div id="adminCharts" class="space-y-4"></div>
    </section>
</div>
@endsection
@section('scripts')
<script>
async function renderAdminCharts() {
    const el = document.getElementById('adminCharts');
    const response = await fetch('/admin/graficas/data');
    const data = await response.json();
    if (!data.length) {
        el.innerHTML = '<p class="text-amber-900">No hay consumos para los últimos 30 días.</p>';
        return;
    }

    const width = 760, height = 240, padding = 30;
    el.innerHTML = data.map(product => {
        const values = product.series.map(point => Number(point.cantidad || 0));
        const maxValue = Math.max(...values, 1);
        const stepX = product.series.length > 1 ? (width - padding * 2) / (product.series.length - 1) : 0;
        const points = values.map((value, idx) => {
            const x = padding + (idx * stepX);
            const y = height - padding - ((value / maxValue) * (height - padding * 2));
            return { x, y, value, label: product.series[idx].fecha };
        });
        const polyline = points.map(p => `${p.x},${p.y}`).join(' ');
        return `<article class="rounded-lg border border-amber-200 bg-amber-50 p-3">
            <h3 class="font-semibold mb-2">${product.producto}</h3>
            <svg viewBox="0 0 ${width} ${height}" class="app-chart-svg" role="img" aria-label="Consumo de ${product.producto}">
                <line x1="${padding}" y1="${height - padding}" x2="${width - padding}" y2="${height - padding}" class="app-chart-axis"></line>
                <line x1="${padding}" y1="${padding}" x2="${padding}" y2="${height - padding}" class="app-chart-axis"></line>
                <polyline points="${polyline}" class="app-chart-line"></polyline>
                ${points.map(p => `<circle cx="${p.x}" cy="${p.y}" r="3.5" class="app-chart-dot"><title>${p.label}: ${p.value}</title></circle>`).join('')}
            </svg>
        </article>`;
    }).join('');
}
renderAdminCharts();
</script>
@endsection
