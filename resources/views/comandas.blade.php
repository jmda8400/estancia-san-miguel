@extends('layout')
@section('title', 'Comandas')
@section('content')
<h1 class="text-2xl font-semibold mb-4">Sistema de comandas</h1>

<div class="mb-4 flex gap-2">
    <button id="tabComandas" class="rounded px-4 py-2 bg-zinc-100 text-zinc-900" onclick="switchTab('comandas')">Comandas</button>
    <button id="tabHistorial" class="rounded px-4 py-2 bg-zinc-600 text-zinc-100" onclick="switchTab('historial')">Historial</button>
</div>

<div id="comandasTab" class="grid gap-4 md:grid-cols-2">
    <div class="rounded-xl border border-zinc-500 bg-zinc-700 p-4">
        <h2 class="font-semibold mb-3">Mesas</h2>
        <div id="tables" class="grid grid-cols-2 sm:grid-cols-3 gap-2"></div>
    </div>
    <div class="rounded-xl border border-zinc-500 bg-zinc-700 p-4">
        <h2 id="selectedTitle" class="font-semibold mb-3">Seleccione una mesa</h2>
        <div id="productsList" class="space-y-2"></div>
        <div class="flex justify-end mt-4">
            <button id="chargeBtn" class="hidden rounded bg-green-600 hover:bg-green-500 text-white px-4 py-2" onclick="cobrarComanda()">Cobrar</button>
        </div>
    </div>
</div>

<div id="addProductCard" class="rounded-xl border border-zinc-500 bg-zinc-700 p-4 mt-4">
    <h2 class="font-semibold mb-3">Agregar productos a comanda</h2>
    <form id="newProductForm" class="grid gap-2 md:grid-cols-5">
        <select id="comandaId" class="rounded border border-zinc-500 bg-zinc-600 px-3 py-2" required>
            <option value="">Comanda</option>
        </select>
        <select id="stockItem" class="rounded border border-zinc-500 bg-zinc-600 px-3 py-2" required></select>
        <input id="productQty" type="number" min="1" value="1" class="rounded border border-zinc-500 bg-zinc-600 px-3 py-2" required>
        <input id="productNotes" class="rounded border border-zinc-500 bg-zinc-600 px-3 py-2" placeholder="Notas">
        <button class="rounded bg-zinc-100 text-zinc-900 px-3 py-2">Agregar a comanda</button>
    </form>
</div>

<div id="historialTab" class="hidden rounded-xl border border-zinc-500 bg-zinc-700 p-4">
    <h2 class="font-semibold mb-3">Historial de comandas cobradas</h2>
    <div id="historialList" class="space-y-2"></div>
    <div class="mt-4 flex items-center justify-between">
        <button id="prevPage" class="rounded bg-zinc-600 px-3 py-2" onclick="changePage(-1)">Anterior</button>
        <span id="pageInfo" class="text-sm text-zinc-300"></span>
        <button id="nextPage" class="rounded bg-zinc-600 px-3 py-2" onclick="changePage(1)">Siguiente</button>
    </div>
</div>
@endsection
@section('scripts')
<script>
let state = { selectedComandaId: null, comandas: @json($comandas), activeTab: 'comandas', stock: [], historial: { data: [], current_page: 1, last_page: 1, total: 0 } };
const tablesEl = document.getElementById('tables');
const productsEl = document.getElementById('productsList');
const titleEl = document.getElementById('selectedTitle');
const chargeBtn = document.getElementById('chargeBtn');

function renderComandas() {
 tablesEl.innerHTML = state.comandas.map(c => `<button class="text-left rounded border px-3 py-2 ${state.selectedComandaId===c.id?'border-zinc-100 bg-zinc-700':'border-zinc-500 bg-zinc-600'}" onclick="selectComanda(${c.id})">${c.nombre ?? ('Mesa ' + c.mesa_numero)}<br><small>${c.productos.length} productos</small></button>`).join('');
 document.getElementById('comandaId').innerHTML = `<option value="">Comanda</option>` + state.comandas.map(c => `<option value="${c.id}">${c.nombre ?? ('Mesa ' + c.mesa_numero)}</option>`).join('');
 const comanda = state.comandas.find(c=>c.id===state.selectedComandaId);
 if(!comanda){titleEl.textContent='Seleccione una mesa'; productsEl.innerHTML=''; chargeBtn.classList.add('hidden'); return;}
 titleEl.textContent = `Productos de ${comanda.nombre ?? ('Mesa ' + comanda.mesa_numero)}`;
 chargeBtn.classList.remove('hidden');
 productsEl.innerHTML = comanda.productos.map(p=>`<div class="flex gap-2 items-center"><strong>${p.nombre}</strong><input type="number" min="1" value="${p.cantidad}" onchange="updateProducto(${p.id},{cantidad:this.value})" class="w-16 rounded border border-zinc-500 bg-zinc-600 px-2 py-1"><input value="${p.notas??''}" onchange="updateProducto(${p.id},{notas:this.value})" class="rounded border border-zinc-500 bg-zinc-600 px-2 py-1"><button class="rounded px-2 py-1 bg-zinc-700 hover:bg-zinc-600" title="Quitar producto" onclick="deleteProducto(${p.id})">🗑️</button></div>`).join('');
}

function renderHistorial() {
    const historialEl = document.getElementById('historialList');
    if (state.historial.data.length === 0) {
        historialEl.innerHTML = '<p class="text-zinc-300">No hay comandas cobradas todavía.</p>';
    } else {
        historialEl.innerHTML = state.historial.data.map(h => `
            <details class="rounded border border-zinc-700 bg-zinc-600 p-3">
                <summary class="cursor-pointer">${h.nombre ?? ('Mesa ' + h.mesa_numero)} · ${new Date(h.cobrada_en).toLocaleString()}</summary>
                <div class="mt-2 space-y-1 text-sm text-zinc-200">
                    ${h.productos.map(p => `<div>${p.cantidad}x ${p.nombre}${p.notas ? ` — ${p.notas}` : ''}</div>`).join('') || '<div>Sin productos.</div>'}
                </div>
            </details>
        `).join('');
    }
    document.getElementById('pageInfo').textContent = `Página ${state.historial.current_page} de ${state.historial.last_page} · ${state.historial.total} comandas`;
    document.getElementById('prevPage').disabled = state.historial.current_page <= 1;
    document.getElementById('nextPage').disabled = state.historial.current_page >= state.historial.last_page;
}

window.switchTab = (tab) => {
    state.activeTab = tab;
    document.getElementById('comandasTab').classList.toggle('hidden', tab !== 'comandas');
    document.getElementById('addProductCard').classList.toggle('hidden', tab !== 'comandas');
    document.getElementById('historialTab').classList.toggle('hidden', tab !== 'historial');
    document.getElementById('tabComandas').className = `rounded px-4 py-2 ${tab === 'comandas' ? 'bg-zinc-100 text-zinc-900' : 'bg-zinc-600 text-zinc-100'}`;
    document.getElementById('tabHistorial').className = `rounded px-4 py-2 ${tab === 'historial' ? 'bg-zinc-100 text-zinc-900' : 'bg-zinc-600 text-zinc-100'}`;
    if (tab === 'historial') refreshHistorial(state.historial.current_page);
};

window.selectComanda=(id)=>{state.selectedComandaId=id;renderComandas();}
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

async function refreshHistorial(page = 1) {
    const response = await fetch(`/comandas/historial/data?page=${page}`);
    state.historial = await response.json();
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
 const response = await fetch('/productos',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({comanda_id:comandaId.value,stock_id:stockItem.value,cantidad:productQty.value,notas:productNotes.value})});
 if(!response.ok){ const data = await response.json(); alert(data.message ?? 'No se pudo agregar el producto.'); return; }
 e.target.reset(); productQty.value=1;
 await refreshComandas();
 await refreshStock();
};

refreshComandas();
refreshStock();
</script>
@endsection
