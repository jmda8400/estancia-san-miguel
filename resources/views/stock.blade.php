@extends('layout')
@section('title', 'Stock')
@section('content')
<h1 class="text-2xl font-semibold mb-4">Vista de stock</h1>

<div class="mb-4 flex gap-2">
    <button id="tabStock" class="app-btn app-btn-active" onclick="switchTab('stock')">Stock</button>
    <button id="tabHistorial" class="app-btn" onclick="switchTab('historial')">Historial</button>
    <button id="tabGraficas" class="app-btn" onclick="switchTab('graficas')">Graficas</button>
</div>

<div id="stockTab" class="app-card">
    <table class="w-full mt-1 text-sm app-list-table">
        <thead><tr><th class="text-left py-2">Producto</th><th class="text-left py-2">Cantidad</th><th class="text-left py-2">Unidad</th><th></th></tr></thead>
        <tbody class="text-zinc-900" id="stockBody"></tbody>
    </table>
    <button class="mt-4 app-btn" onclick="addRow()">+ Agregar fila</button>
</div>

<div id="historialTab" class="hidden app-card">
    <h2 class="font-semibold mb-3">Historial de cambios de stock</h2>
    <div id="stockHistoryList" class="space-y-2"></div>
</div>

<div id="graficasTab" class="hidden app-card">
    <h2 class="font-semibold mb-3">Variación de stock en el tiempo</h2>
    <div id="stockChart" class="app-chart-wrap"></div>
</div>
@endsection
@section('scripts')
<script>
let stockItems = @json($stockItems);

function rowTemplate(item){
    return `<tr><td class="py-2">${item.id ? item.producto : `<input class='app-input w-full' placeholder='Producto' id='p_${item.tmpId}'>`}</td><td>${item.id ? `<input type='number' min='0' value='${item.cantidad}' class='app-input w-24' onchange='updateStock(${item.id}, this.value)'>` : `<input type='number' min='0' value='0' class='app-input w-24' id='c_${item.tmpId}'>`}</td><td>${item.id ? item.unidad : `<input class='app-input w-full' placeholder='Unidad' id='u_${item.tmpId}'>`}</td><td class='text-right'>${item.id ? `<button class='app-btn' onclick='removeStock(${item.id})'>Quitar</button>` : `<button class='app-btn' onclick='saveRow(${item.tmpId})'>Guardar</button>`}</td></tr>`;
}

function renderStock(){
    document.getElementById('stockBody').innerHTML = stockItems.map(rowTemplate).join('');
}

window.addRow = () => {
    stockItems.push({ tmpId: Date.now() + Math.floor(Math.random() * 1000) });
    renderStock();
};

window.saveRow = async (tmpId) => {
    const producto = document.getElementById(`p_${tmpId}`).value;
    const cantidad = document.getElementById(`c_${tmpId}`).value;
    const unidad = document.getElementById(`u_${tmpId}`).value;
    await fetch('/stock',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({producto,cantidad,unidad})});
    await refreshStock();
    await refreshHistory();
};

window.removeStock = async (id) => {
    await fetch(`/stock/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}});
    await refreshStock();
    await refreshHistory();
};

window.updateStock=async(id,cantidad)=>{
    await fetch(`/stock/${id}`,{method:'PUT',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({cantidad})});
    await refreshStock();
    await refreshHistory();
}

async function refreshStock(){
    const response = await fetch('/stock/data');
    stockItems = await response.json();
    renderStock();
}

async function refreshHistory(){
    const response = await fetch('/stock/historial/data');
    const data = await response.json();
    const labels = { suma: 'Suma', resta: 'Resta', agregado: 'Agregado', quitado: 'Quitado' };
    document.getElementById('stockHistoryList').innerHTML = data.map(h => `<div class='rounded border border-amber-200 bg-amber-50 p-3'><strong>${labels[h.accion]}</strong> · ${h.producto} · ${h.cantidad} ${h.unidad}<div class='text-sm text-amber-950'>${new Date(h.created_at).toLocaleString()}</div></div>`).join('') || '<p class="text-amber-900">No hay movimientos.</p>';
}

function renderStockChart() {
    const chartEl = document.getElementById('stockChart');
    if (!stockItems.length) {
        chartEl.innerHTML = '<p class="text-amber-900">No hay datos de stock para graficar.</p>';
        return;
    }
    const maxValue = Math.max(...stockItems.map(item => Number(item.cantidad) || 0), 1);
    const width = 720;
    const height = 260;
    const padding = 36;
    const stepX = stockItems.length > 1 ? (width - padding * 2) / (stockItems.length - 1) : 0;

    const points = stockItems.map((item, idx) => {
        const value = Number(item.cantidad) || 0;
        const x = padding + (idx * stepX);
        const y = height - padding - ((value / maxValue) * (height - padding * 2));
        return { x, y, value, label: item.producto };
    });

    const polyline = points.map(point => `${point.x},${point.y}`).join(' ');
    chartEl.innerHTML = `
        <svg viewBox="0 0 ${width} ${height}" class="app-chart-svg" role="img" aria-label="Gráfico de stock">
            <line x1="${padding}" y1="${height - padding}" x2="${width - padding}" y2="${height - padding}" class="app-chart-axis"></line>
            <line x1="${padding}" y1="${padding}" x2="${padding}" y2="${height - padding}" class="app-chart-axis"></line>
            <polyline points="${polyline}" class="app-chart-line"></polyline>
            ${points.map(point => `<circle cx="${point.x}" cy="${point.y}" r="4" class="app-chart-dot"><title>${point.label}: ${point.value}</title></circle>`).join('')}
        </svg>
        <div class="app-chart-legend">
            ${points.map(point => `<span>${point.label}: <strong>${point.value}</strong></span>`).join('')}
        </div>
    `;
}

window.switchTab = (tab) => {
    document.getElementById('stockTab').classList.toggle('hidden', tab !== 'stock');
    document.getElementById('historialTab').classList.toggle('hidden', tab !== 'historial');
    document.getElementById('graficasTab').classList.toggle('hidden', tab !== 'graficas');
    document.getElementById('tabStock').className = `app-btn ${tab === 'stock' ? 'app-btn-active' : ''}`;
    document.getElementById('tabHistorial').className = `app-btn ${tab === 'historial' ? 'app-btn-active' : ''}`;
    document.getElementById('tabGraficas').className = `app-btn ${tab === 'graficas' ? 'app-btn-active' : ''}`;
    if (tab === 'historial') refreshHistory();
    if (tab === 'graficas') renderStockChart();
};

renderStock();
refreshHistory();
</script>
@endsection
