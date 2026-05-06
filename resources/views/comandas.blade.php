@extends('layout')
@section('title', 'Comandas')
@section('content')
<h1 class="text-2xl font-semibold mb-4">Sistema de comandas</h1>

<div class="mb-4 flex gap-2">
    <button id="tabComandas" class="app-btn app-btn-active" onclick="switchTab('comandas')">Comandas</button>
    <button id="tabHistorial" class="app-btn" onclick="switchTab('historial')">Historial</button>
    <button id="tabGraficas" class="app-btn" onclick="switchTab('graficas')">Graficas</button>
</div>

<div id="comandasTab" class="grid gap-4 md:grid-cols-2">
    <div class="app-card">
        <div class="flex items-center justify-between mb-3 gap-2 flex-wrap">
            <h2 class="font-semibold">Mesas</h2>
            <div class="flex items-center gap-2">
            <button class="app-btn" onclick="addComanda()">+ Agregar comanda</button>
            <button class="app-btn" onclick="removeLastComanda()">- Quitar comanda</button>
            </div>
        </div>
        <div id="tables" class="grid grid-cols-2 sm:grid-cols-3 gap-2"></div>
    </div>
    <div class="app-card">
        <h2 id="selectedTitle" class="font-semibold mb-3">Seleccione una mesa</h2>
        <div id="productsList" class="space-y-2"></div>
        <div class="flex justify-end mt-4">
            <button id="chargeBtn" class="hidden app-btn app-charge-btn" onclick="cobrarComanda()">Cobrar</button>
        </div>
    </div>
</div>

<div id="addProductCard" class="app-card mt-4 hidden">
    <h2 class="font-semibold mb-3">Agregar productos a comanda</h2>
    <form id="newProductForm" class="grid gap-2 md:grid-cols-4">
        <select id="stockItem" class="app-input" required></select>
        <input id="productQty" type="number" min="1" value="1" class="app-input" required>
        <input id="productNotes" class="app-input" placeholder="Notas">
        <button class="app-btn">Agregar a comanda</button>
    </form>
</div>

<div id="historialTab" class="hidden app-card">
    <h2 class="font-semibold mb-3">Historial de comandas cobradas</h2>
    <div id="historialList" class="space-y-2"></div>
    <div class="mt-4 flex items-center justify-between">
        <button id="prevPage" class="app-btn" onclick="changePage(-1)">Anterior</button>
        <span id="pageInfo" class="text-sm text-amber-950"></span>
        <button id="nextPage" class="app-btn" onclick="changePage(1)">Siguiente</button>
    </div>
</div>

<div id="graficasTab" class="hidden app-card">
    <h2 class="font-semibold mb-3">Consumo de comandas en el tiempo</h2>
    <div id="comandasChart" class="app-chart-wrap"></div>
</div>
@endsection
@section('scripts')
<script>
let state = { selectedComandaId: null, comandas: @json($comandas), activeTab: 'comandas', stock: [], historial: { data: [], current_page: 1, last_page: 1, total: 0 }, chartData: [] };
const tablesEl = document.getElementById('tables');
const productsEl = document.getElementById('productsList');
const titleEl = document.getElementById('selectedTitle');
const chargeBtn = document.getElementById('chargeBtn');

function renderComandas() {
 const visible = state.comandas;
 tablesEl.innerHTML = visible.map(c => `<div class="app-table-btn ${state.selectedComandaId===c.id?'active':''}"><button class="w-full text-left" onclick="selectComanda(${c.id})">${c.nombre ?? ('Mesa ' + c.mesa_numero)}<br><small>${c.productos.length} productos</small></button><button class="app-remove-table-btn mt-2" onclick="removeComanda(${c.id})">Quitar</button></div>`).join('');
 const comanda = state.comandas.find(c=>c.id===state.selectedComandaId);
 if(!comanda){titleEl.textContent='Seleccione una mesa'; productsEl.innerHTML=''; chargeBtn.classList.add('hidden'); document.getElementById('addProductCard').classList.add('hidden'); return;}
 titleEl.textContent = `Productos de ${comanda.nombre ?? ('Mesa ' + comanda.mesa_numero)}`;
 chargeBtn.classList.remove('hidden');
 document.getElementById('addProductCard').classList.remove('hidden');
 productsEl.innerHTML = comanda.productos.map(p=>`<div class="rounded-lg border border-amber-200 bg-amber-50 p-3"><div class="flex flex-wrap gap-2 items-center"><strong class="min-w-44">${p.nombre}</strong><input type="number" min="1" value="${p.cantidad}" onchange="updateProducto(${p.id},{cantidad:this.value})" class="w-18 app-input"><input value="${p.notas??''}" onchange="updateProducto(${p.id},{notas:this.value})" class="app-input flex-1" placeholder="Notas"><button class="app-trash-btn" title="Quitar producto" onclick="deleteProducto(${p.id})">🗑️ Eliminar</button></div></div>`).join('');
}

function renderHistorial() {
    const historialEl = document.getElementById('historialList');
    if (state.historial.data.length === 0) {
        historialEl.innerHTML = '<p class="text-amber-900">No hay comandas cobradas todavía.</p>';
    } else {
        historialEl.innerHTML = state.historial.data.map(h => `
            <details class="rounded border border-amber-200 bg-amber-50 p-3">
                <summary class="cursor-pointer">${h.nombre ?? ('Mesa ' + h.mesa_numero)} · ${new Date(h.cobrada_en).toLocaleString()}</summary>
                <div class="mt-2 space-y-1 text-sm text-amber-950">
                    ${h.productos.map(p => `<div>${p.cantidad}x ${p.nombre}${p.notas ? ` — ${p.notas}` : ''}</div>`).join('') || '<div>Sin productos.</div>'}
                </div>
            </details>
        `).join('');
    }
    document.getElementById('pageInfo').textContent = `Página ${state.historial.current_page} de ${state.historial.last_page} · ${state.historial.total} comandas`;
    document.getElementById('prevPage').disabled = state.historial.current_page <= 1;
    document.getElementById('nextPage').disabled = state.historial.current_page >= state.historial.last_page;
}

function renderComandasChart() {
    const chartEl = document.getElementById('comandasChart');
    if (!state.chartData.length) {
        chartEl.innerHTML = '<p class="text-amber-900">No hay comandas cobradas para graficar.</p>';
        return;
    }
    const pointsByDate = state.chartData.reduce((acc, item) => {
        const key = new Date(item.cobrada_en).toLocaleDateString();
        acc[key] = (acc[key] ?? 0) + item.productos.reduce((sum, prod) => sum + Number(prod.cantidad || 0), 0);
        return acc;
    }, {});
    const labels = Object.keys(pointsByDate);
    const values = Object.values(pointsByDate);
    const maxValue = Math.max(...values, 1);
    const width = 720;
    const height = 260;
    const padding = 36;
    const stepX = labels.length > 1 ? (width - padding * 2) / (labels.length - 1) : 0;
    const points = values.map((value, idx) => {
        const x = padding + (idx * stepX);
        const y = height - padding - ((value / maxValue) * (height - padding * 2));
        return { x, y, value, label: labels[idx] };
    });
    const polyline = points.map(point => `${point.x},${point.y}`).join(' ');
    chartEl.innerHTML = `
        <svg viewBox="0 0 ${width} ${height}" class="app-chart-svg" role="img" aria-label="Gráfico de consumo de comandas">
            <line x1="${padding}" y1="${height - padding}" x2="${width - padding}" y2="${height - padding}" class="app-chart-axis"></line>
            <line x1="${padding}" y1="${padding}" x2="${padding}" y2="${height - padding}" class="app-chart-axis"></line>
            <polyline points="${polyline}" class="app-chart-line"></polyline>
            ${points.map(point => `<circle cx="${point.x}" cy="${point.y}" r="4" class="app-chart-dot"><title>${point.label}: ${point.value} consumos</title></circle>`).join('')}
        </svg>
        <div class="app-chart-legend">
            ${points.map(point => `<span>${point.label}: <strong>${point.value}</strong></span>`).join('')}
        </div>
    `;
}

window.switchTab = (tab) => {
    state.activeTab = tab;
    document.getElementById('comandasTab').classList.toggle('hidden', tab !== 'comandas');
    document.getElementById('addProductCard').classList.toggle('hidden', tab !== 'comandas');
    document.getElementById('historialTab').classList.toggle('hidden', tab !== 'historial');
    document.getElementById('graficasTab').classList.toggle('hidden', tab !== 'graficas');
    document.getElementById('tabComandas').className = `app-btn ${tab === 'comandas' ? 'app-btn-active' : ''}`;
    document.getElementById('tabHistorial').className = `app-btn ${tab === 'historial' ? 'app-btn-active' : ''}`;
    document.getElementById('tabGraficas').className = `app-btn ${tab === 'graficas' ? 'app-btn-active' : ''}`;
    if (tab === 'historial') refreshHistorial(state.historial.current_page);
    if (tab === 'graficas') refreshHistorial(1, true);
};

window.selectComanda=(id)=>{state.selectedComandaId=id;renderComandas();}
window.addComanda=async()=>{
 const response = await fetch('/comandas',{method:'POST',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}});
 if(!response.ok){ const data = await response.json(); alert(data.message ?? 'No se pudo crear la comanda.'); return; }
 await refreshComandas();
}
window.removeLastComanda=async()=>{
 const last = state.comandas[state.comandas.length - 1];
 if(!last){ alert('No hay comandas para quitar.'); return; }
 await removeComanda(last.id);
}
window.removeComanda=async(id)=>{
 if(!confirm('¿Seguro que querés quitar esta comanda?')) return;
 const response = await fetch(`/comandas/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}});
 if(!response.ok){ const data = await response.json(); alert(data.message ?? 'No se pudo quitar la comanda.'); return; }
 if(state.selectedComandaId===id) state.selectedComandaId = null;
 await refreshComandas();
}
async function refreshComandas(){
 const c = await fetch('/comandas/data');
 state.comandas = await c.json();
 renderComandas();
}

async function refreshStock(){
    const response = await fetch('/stock/data');
    state.stock = await response.json();
    document.getElementById('stockItem').innerHTML = state.stock.map(s => `<option value="${s.id}">${s.producto} (${s.cantidad} ${s.unidad})</option>`).join('');
}

async function refreshHistorial(page = 1, forChart = false) {
    const response = await fetch(`/comandas/historial/data?page=${page}`);
    const data = await response.json();
    if (forChart) {
        state.chartData = data.data;
        renderComandasChart();
        return;
    }
    state.historial = data;
    renderHistorial();
}

window.changePage = (delta) => {
    const nextPage = state.historial.current_page + delta;
    if (nextPage < 1 || nextPage > state.historial.last_page) return;
    refreshHistorial(nextPage);
};

window.updateProducto=async(id,data)=>{await fetch(`/productos/${id}`,{method:'PUT',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify(data)}); refreshComandas();}
window.deleteProducto=async(id)=>{await fetch(`/productos/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}}); refreshComandas();}
window.cobrarComanda=async()=>{
 if(!state.selectedComandaId){return;}
 await fetch(`/comandas/${state.selectedComandaId}/cobrar`,{method:'POST',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}});
 await refreshComandas();
 if (state.activeTab === 'historial') refreshHistorial();
};

document.getElementById('newProductForm').onsubmit=async(e)=>{
 e.preventDefault();
 if(!state.selectedComandaId){ alert('Seleccione una comanda primero.'); return; }
 const response = await fetch('/productos',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({comanda_id:state.selectedComandaId,stock_id:stockItem.value,cantidad:productQty.value,notas:productNotes.value})});
 if(!response.ok){ const data = await response.json(); alert(data.message ?? 'No se pudo agregar el producto.'); return; }
 e.target.reset(); productQty.value=1;
 await refreshComandas();
 await refreshStock();
};

refreshComandas();
refreshStock();
</script>
@endsection
