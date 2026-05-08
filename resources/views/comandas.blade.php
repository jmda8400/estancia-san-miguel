@extends('layouts.internal')
@section('title', 'Comandas')
@section('internal_title', 'Sistema de comandas')

@section('internal_content')
<div class="mb-3 flex gap-2">
    <button id="tabComandas" class="app-btn app-btn-active" onclick="switchTab('comandas')">Comandas</button>
    <button id="tabHistorial" class="app-btn" onclick="switchTab('historial')">Historial</button>
</div>

<div id="comandasTab" class="orders-layout">
    <div class="panel h-full flex flex-col">
        <div class="flex items-center justify-between gap-2 flex-wrap mb-3">
            <h2 class="panel-title">Mesas</h2>
            <div class="flex items-center gap-2 flex-wrap">
                <button class="btn btn-secondary text-xs sm:text-sm" onclick="addComanda()">+ Agregar comanda</button>
                <button class="btn btn-danger text-xs sm:text-sm" onclick="removeLastComanda()">- Quitar comanda</button>
            </div>
        </div>
        <div id="tables" class="tables-grid flex-1 min-h-[20rem] max-h-[calc(100vh-16rem)] overflow-y-auto pr-1"></div>
    </div>

    <div class="panel h-full flex flex-col min-h-[30rem] max-h-[calc(100vh-14rem)]">
        <h2 id="selectedTitle" class="panel-title mb-3">Seleccione una mesa</h2>
        <div id="productsList" class="space-y-3 flex-1 overflow-y-auto pr-1 min-h-[16rem]"></div>
        <div class="orders-summary mt-3 pt-3 flex items-center justify-between gap-3">
            <p id="selectedTotal" class="text-sm font-semibold text-emerald-950">Total: $0.00</p>
            <button id="chargeBtn" class="hidden btn btn-primary btn-charge" onclick="cobrarComanda()">Cobrar</button>
        </div>
    </div>

    <div id="addProductCard" class="panel add-product-panel hidden">
        <h2 class="panel-title mb-3">Agregar productos a comanda</h2>
        <form id="newProductForm" class="grid gap-3">
            <div class="field">
                <label for="stockItem" class="field-label">Producto</label>
                <select id="stockItem" class="app-input mt-1 w-full" required></select>
            </div>
            <div class="field">
                <label for="productQty" class="field-label">Cantidad</label>
                <input id="productQty" type="number" min="1" value="1" class="app-input mt-1 w-full" required>
            </div>
            <div class="field">
                <label for="productNotes" class="field-label">Notas</label>
                <input id="productNotes" class="app-input mt-1 w-full" placeholder="Notas">
            </div>
            <button class="btn btn-primary btn-add-comanda w-full">Agregar a comanda</button>
        </form>
    </div>
</div>

<div id="historialTab" class="hidden panel">
    <h2 class="panel-title mb-3">Historial de comandas cobradas</h2>
    <div id="historialList" class="space-y-2"></div>
    <div class="mt-4 flex items-center justify-between">
        <button id="prevPage" class="btn btn-secondary" onclick="changePage(-1)">Anterior</button>
        <span id="pageInfo" class="text-sm text-emerald-950"></span>
        <button id="nextPage" class="btn btn-secondary" onclick="changePage(1)">Siguiente</button>
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
const totalEl = document.getElementById('selectedTotal');

function renderComandas() {
 const visible = state.comandas;
 tablesEl.innerHTML = visible.map(c => {
    const totalMesa = c.productos.reduce((acc, p) => acc + ((Number(p.precio) || 0) * p.cantidad), 0);
    return `<div class="table-card ${state.selectedComandaId===c.id?'active':''}">
        <button class="table-card-select" onclick="selectComanda(${c.id})">
            <p class="table-card-title">${c.nombre ?? ('Mesa ' + c.mesa_numero)}</p>
            <p class="table-card-meta">${c.productos.length} producto(s)</p>
            <p class="table-card-total">$${totalMesa.toFixed(2)}</p>
        </button>
        <button class="btn btn-danger btn-compact table-card-remove mt-2" onclick="removeComanda(${c.id})">Quitar</button>
    </div>`;
 }).join('');
 const comanda = state.comandas.find(c=>c.id===state.selectedComandaId);
 if(!comanda){titleEl.textContent='Seleccione una mesa'; productsEl.innerHTML=''; totalEl.textContent='Total: $0.00'; chargeBtn.classList.add('hidden'); document.getElementById('addProductCard').classList.add('hidden'); return;}
 titleEl.textContent = `Productos de ${comanda.nombre ?? ('Mesa ' + comanda.mesa_numero)}`;
 chargeBtn.classList.remove('hidden');
 document.getElementById('addProductCard').classList.remove('hidden');
 const totalComanda = comanda.productos.reduce((acc, p) => acc + ((Number(p.precio) || 0) * p.cantidad), 0);
 totalEl.textContent = `Total: $${totalComanda.toFixed(2)}`;
 productsEl.innerHTML = comanda.productos.length
    ? comanda.productos.map(p=>`<div class="order-item">
        <div class="order-item-head">
            <div class="min-w-0">
                <p class="order-item-title truncate">${p.nombre}</p>
                <p class="order-item-price">$${(Number(p.precio) || 0).toFixed(2)} c/u</p>
            </div>
            <p class="order-item-subtotal">$${((Number(p.precio) || 0) * p.cantidad).toFixed(2)}</p>
        </div>
        <div class="order-item-controls mt-2">
            <input type="number" min="1" value="${p.cantidad}" onchange="updateProducto(${p.id},{cantidad:this.value})" class="app-input text-sm">
            <input value="${p.notas??''}" onchange="updateProducto(${p.id},{notas:this.value})" class="app-input text-sm" placeholder="Notas">
            <button class="btn btn-danger btn-product-delete text-xs" title="Quitar producto" onclick="deleteProducto(${p.id})">Eliminar</button>
        </div>
    </div>`).join('')
    : `<div class="h-full min-h-[14rem] flex items-center justify-center">
        <p class="text-sm text-emerald-900 text-center">Esta comanda no tiene productos aún.</p>
      </div>`;
}

function renderHistorial() {
    const historialEl = document.getElementById('historialList');
    if (state.historial.data.length === 0) {
        historialEl.innerHTML = '<p class="text-emerald-900">No hay comandas cobradas todavía.</p>';
    } else {
        historialEl.innerHTML = state.historial.data.map(h => `
            <details class="rounded-2xl border border-emerald-200 bg-emerald-50 p-3">
                <summary class="cursor-pointer">${h.nombre ?? ('Mesa ' + h.mesa_numero)} · ${new Date(h.cobrada_en).toLocaleString()}</summary>
                <div class="mt-2 space-y-1 text-sm text-emerald-950">
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
    document.getElementById('tabComandas').className = `app-btn ${tab === 'comandas' ? 'app-btn-active' : ''}`;
    document.getElementById('tabHistorial').className = `app-btn ${tab === 'historial' ? 'app-btn-active' : ''}`;
    if (tab === 'historial') refreshHistorial(state.historial.current_page);
};

window.selectComanda=(id)=>{state.selectedComandaId=id;renderComandas();}
window.addComanda=async()=>{
 const response = await fetch('/comandas',{method:'POST',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}});
 if(!response.ok){ const data = await response.json(); alert(data.message ?? 'No se pudo crear la comanda.'); return; }
 await refreshComandas();
}
window.removeLastComanda=async()=>{
 const last = state.comandas[state.comandas.length - 1];
 if(!last){ return; }
 await removeComanda(last.id);
}
window.removeComanda=async(id)=>{
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
    document.getElementById('stockItem').innerHTML = state.stock.map(s => `<option value="${s.id}">${s.producto} (${s.cantidad})</option>`).join('');
}

async function refreshHistorial(page = 1) {
    const response = await fetch(`/comandas/historial/data?page=${page}`);
    const data = await response.json();
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
