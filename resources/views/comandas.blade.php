@extends('layouts.internal')
@section('title', 'Comandas')
@section('internal_title', 'Sistema de comandas')
@section('internal_plain', true)

@section('internal_tabs')
<div class="app-segmented-control">
    <button id="tabComandas" class="app-segment-btn app-segment-btn-active" onclick="switchTab('comandas')">Comandas</button>
    <button id="tabHistorial" class="app-segment-btn" onclick="switchTab('historial')">Historial</button>
    <button id="tabCaja" class="app-segment-btn" onclick="switchTab('caja')">Cierre de Caja</button>
</div>
@endsection

@section('internal_content')
<div class="space-y-6">
    <header class="space-y-1">
        <h1 class="text-2xl md:text-3xl font-semibold tracking-tight text-emerald-950">Sistema de comandas</h1>
        <p class="text-sm md:text-base text-emerald-900">Administrá comandas activas, historial y cierre de caja</p>
    </header>

    <div class="internal-tabs-wrap">@yield('internal_tabs')</div>

    <section id="comandasTab" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-12 gap-6 items-start">
        <div class="bg-white rounded-3xl border border-slate-200/70 shadow-sm p-6 min-w-0 md:col-span-1 xl:col-span-3">
            <div class="flex items-center justify-between gap-2 flex-wrap mb-4">
                <h2 class="text-lg font-semibold text-slate-900">Comandas</h2>
                <button class="btn btn-secondary btn-compact text-xs sm:text-sm" onclick="openComandaForm()">+ Agregar comanda</button>
            </div>
            <div id="tables" class="space-y-2 max-h-[32rem] overflow-y-auto pr-1"></div>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200/70 shadow-sm p-6 min-w-0 md:col-span-1 xl:col-span-5 flex flex-col">
            <h2 id="selectedTitle" class="text-lg font-semibold text-slate-900 mb-4">Seleccione una comanda</h2>
            <div id="productsList" class="space-y-3 flex-1"></div>
            <div id="selectedSummary" class="mt-4 pt-4 border-t border-slate-200 flex items-center justify-between gap-3 hidden">
                <p id="selectedTotal" class="text-base font-semibold text-emerald-950">Total: $ 0</p>
                <button id="chargeBtn" class="hidden btn btn-primary btn-charge" onclick="cobrarComanda()">Cobrar</button>
            </div>
        </div>

        <div id="addProductCard" class="bg-white rounded-3xl border border-slate-200/70 shadow-sm p-6 min-w-0 md:col-span-2 xl:col-span-4">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Agregar producto</h2>
            <div id="addProductEmptyState" class="rounded-2xl border border-slate-200 bg-slate-50 p-5 text-center text-sm text-slate-600">Seleccioná una comanda para agregar productos.</div>
            <form id="newProductForm" class="grid gap-3 hidden">
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
                <button class="btn btn-primary w-full">Agregar a comanda</button>
            </form>
        </div>
    </section>

    <div id="historialTab" class="hidden bg-white rounded-3xl border border-slate-200/70 shadow-sm p-6 min-w-0">
        <h2 class="text-lg font-semibold text-slate-900 mb-3">Historial de comandas cobradas</h2>
        <div id="historialList" class="space-y-2"></div>
        <div class="mt-4 flex items-center justify-between">
            <button id="prevPage" class="btn btn-secondary" onclick="changePage(-1)">Anterior</button>
            <span id="pageInfo" class="text-sm text-emerald-950"></span>
            <button id="nextPage" class="btn btn-secondary" onclick="changePage(1)">Siguiente</button>
        </div>
    </div>

    <div id="cajaTab" class="hidden bg-white rounded-3xl border border-slate-200/70 shadow-sm p-6 min-w-0 space-y-4">
        <div class="flex items-center justify-between gap-2 mb-1 flex-wrap">
            <h2 class="text-lg font-semibold text-slate-900">Cierre de caja</h2>
            <button class="btn btn-secondary" onclick="cerrarCaja()">Cerrar caja</button>
        </div>
        <div id="cajaResumen" class="space-y-3 text-emerald-950"></div>
    </div>
</div>

<div id="newComandaModal" class="hidden fixed inset-0 z-50 p-4 sm:p-6 flex items-center justify-center">
    <div class="absolute inset-0 bg-emerald-950/45" onclick="closeComandaForm()"></div>
    <div class="relative bg-white rounded-3xl border border-slate-200 shadow-xl w-full max-w-lg p-6">
        <h2 class="text-lg font-semibold text-slate-900 mb-3">Nueva comanda</h2>
        <form id="newComandaForm" class="grid gap-3">
            <input id="comandaNombre" class="app-input" placeholder="Nombre del cliente" required>
            <input id="comandaDocumento" class="app-input" placeholder="Documento (opcional)">
            <div class="flex gap-2 pt-1">
                <button type="button" class="btn btn-secondary w-full" onclick="closeComandaForm()">Cancelar</button>
                <button class="btn btn-primary w-full">Crear comanda</button>
            </div>
        </form>
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
const summaryEl = document.getElementById('selectedSummary');
const addProductForm = document.getElementById('newProductForm');
const addProductEmptyState = document.getElementById('addProductEmptyState');

function renderComandas() {
 const visible = state.comandas;
 tablesEl.innerHTML = visible.map(c => {
    const totalMesa = c.productos.reduce((acc, p) => acc + ((Number(p.precio) || 0) * p.cantidad), 0);
    return `<article class="rounded-2xl border ${state.selectedComandaId===c.id ? 'border-emerald-300 bg-emerald-50/70' : 'border-slate-200 bg-white'} p-3">
        <button class="w-full text-left space-y-1" onclick="selectComanda(${c.id})">
            <p class="text-sm font-semibold text-slate-900">${c.nombre}</p>
            <p class="text-xs text-slate-500">${c.productos.length} producto(s)</p>
            <p class="text-sm font-bold text-emerald-900">${formatArs(totalMesa)}</p>
        </button>
        <button class="btn btn-danger btn-compact text-xs mt-2" onclick="deleteComanda(${c.id})">Quitar</button>
    </article>`;
 }).join('');
 const comanda = state.comandas.find(c=>c.id===state.selectedComandaId);
 if(!comanda){
    titleEl.textContent='Seleccione una comanda';
    productsEl.innerHTML=`<div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 text-center"><p class="text-base font-semibold text-emerald-950">Seleccione una comanda</p><p class="mt-2 text-sm text-emerald-900">Elegí una comanda del panel izquierdo para ver sus productos y cobrar.</p></div>`;
    totalEl.textContent='Total: $ 0';
    chargeBtn.classList.add('hidden');
    summaryEl.classList.add('hidden');
    addProductForm.classList.add('hidden');
    addProductEmptyState.classList.remove('hidden');
    return;
 }
 titleEl.textContent = comanda.nombre;
 chargeBtn.classList.remove('hidden');
 summaryEl.classList.remove('hidden');
 addProductForm.classList.remove('hidden');
 addProductEmptyState.classList.add('hidden');
 const totalComanda = comanda.productos.reduce((acc, p) => acc + ((Number(p.precio) || 0) * p.cantidad), 0);
 totalEl.textContent = `Total: ${formatArs(totalComanda)}`;
 productsEl.innerHTML = comanda.productos.length
    ? comanda.productos.map(p=>`<div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-sm font-semibold text-slate-900 truncate">${p.nombre}</p>
                <p class="text-xs text-slate-500 mt-1">$${(Number(p.precio) || 0).toFixed(2)} c/u</p>
            </div>
            <p class="text-sm font-bold text-emerald-900">$${((Number(p.precio) || 0) * p.cantidad).toFixed(2)}</p>
        </div>
        <div class="mt-3 grid grid-cols-1 sm:grid-cols-[96px_minmax(0,1fr)_auto] gap-2">
            <input type="number" min="1" value="${p.cantidad}" onchange="updateProducto(${p.id},{cantidad:this.value})" class="app-input text-sm">
            <input value="${p.notas??''}" onchange="updateProducto(${p.id},{notas:this.value})" class="app-input text-sm" placeholder="Notas">
            <button class="btn btn-danger btn-compact text-xs" title="Quitar producto" onclick="deleteProducto(${p.id})">Quitar</button>
        </div>
    </div>`).join('')
    : `<p class="text-sm text-slate-500 text-center rounded-2xl border border-slate-200 bg-slate-50 p-5">Esta comanda no tiene productos aún.</p>`;
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
    document.getElementById('historialTab').classList.toggle('hidden', tab !== 'historial');
    document.getElementById('cajaTab').classList.toggle('hidden', tab !== 'caja');
    document.getElementById('tabComandas').className = `app-segment-btn ${tab === 'comandas' ? 'app-segment-btn-active' : ''}`;
    document.getElementById('tabHistorial').className = `app-segment-btn ${tab === 'historial' ? 'app-segment-btn-active' : ''}`;
    document.getElementById('tabCaja').className = `app-segment-btn ${tab === 'caja' ? 'app-segment-btn-active' : ''}`;
    if (tab === 'historial') refreshHistorial(state.historial.current_page);
    if (tab === 'caja') refreshCaja();
};

window.selectComanda=(id)=>{state.selectedComandaId=id;renderComandas();}
window.openComandaForm=()=>document.getElementById('newComandaModal').classList.remove('hidden');
window.closeComandaForm=()=>{document.getElementById('newComandaModal').classList.add('hidden');document.getElementById('newComandaForm').reset();};

async function refreshComandas(){
 const c = await fetch('/comandas/data');
 state.comandas = await c.json();
 renderComandas();
}

async function refreshStock(){
    const response = await fetch('/stock/data');
    state.stock = await response.json();
    document.getElementById('stockItem').innerHTML = state.stock.map(s => `<option value="${s.id}">${s.producto} (${s.ilimitado ? '∞' : s.cantidad})</option>`).join('');
}

async function refreshHistorial(page = 1) {
    const response = await fetch(`/comandas/historial/data?page=${page}`);
    const data = await response.json();
    state.historial = data;
    renderHistorial();
}


const formatArs = (value) => '$ ' + Number(value || 0).toLocaleString('es-AR', {minimumFractionDigits: 0, maximumFractionDigits: 0});

window.changePage = (delta) => {
    const nextPage = state.historial.current_page + delta;
    if (nextPage < 1 || nextPage > state.historial.last_page) return;
    refreshHistorial(nextPage);
};
async function refreshCaja() {
    const response = await fetch('/comandas/cierre/data');
    const caja = await response.json();
    const abiertas = caja.comandas_abiertas > 0 ? `<div class="rounded-xl border border-amber-300 bg-amber-50 p-3 text-amber-900">Atención: hay ${caja.comandas_abiertas} comandas abiertas. Revisar antes de cerrar caja.</div>` : '';
    document.getElementById('cajaResumen').innerHTML = `
        ${abiertas}
        <div class="grid gap-2 md:grid-cols-2">
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3"><strong>Total cobrado:</strong> ${formatArs(caja.total_cobrado)}</div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3"><strong>Comandas cobradas:</strong> ${caja.comandas_cobradas}</div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3"><strong>Productos cobrados:</strong> ${caja.productos_cobrados}</div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3"><strong>Comandas abiertas:</strong> ${caja.comandas_abiertas}</div>
        </div>
        <div class="rounded-xl border border-emerald-200 p-3"><h3 class="font-semibold mb-2">Control de efectivo</h3><div class="grid md:grid-cols-2 gap-2"><input id='cajaInicial' class='app-input' placeholder='Caja inicial'><input id='efectivoContado' class='app-input' placeholder='Efectivo contado'></div><div id='efectivoEsperadoTxt' class='mt-2 text-sm'>Efectivo esperado: $ 0</div><div id='diferenciaTxt' class='text-sm'>Diferencia: $ 0</div></div>
        <div class="rounded-xl border border-emerald-200 p-3"><h3 class="font-semibold mb-2">Productos vendidos</h3><table class='w-full text-sm'><tr><th class='text-left'>Producto</th><th>Cant.</th><th class='text-right'>Total</th></tr>${(caja.productos_vendidos||[]).map(p=>`<tr><td>${p.producto}</td><td class='text-center'>${p.cantidad}</td><td class='text-right'>${formatArs(p.total)}</td></tr>`).join('')}</table></div>
        <div class="rounded-xl border border-emerald-200 p-3"><h3 class="font-semibold mb-2">Comandas incluidas</h3><table class='w-full text-sm'><tr><th class='text-left'>Comanda</th><th>Hora</th><th class='text-right'>Total</th></tr>${(caja.comandas_incluidas||[]).map(c=>`<tr><td>${c.nombre}</td><td>${new Date(c.cobrada_en).toLocaleTimeString()}</td><td class='text-right'>${formatArs(c.total)}</td></tr>`).join('')}</table></div>
        <div class="rounded-xl border border-emerald-200 p-3"><h3 class="font-semibold mb-2">Historial de cierres</h3>${(caja.historial_cierres||[]).map(c=>`<div class='flex justify-between text-sm border-b py-1'><span>${new Date(c.created_at).toLocaleString()} · ${c.turno||'-'} · ${c.responsable||'-'}</span><span>${formatArs(c.total_cobrado)} / Dif: ${formatArs(c.diferencia_efectivo||0)}</span></div>`).join('')}</div>`;
}
window.cerrarCaja = async () => {
 const payload = {
  caja_inicial: Number(document.getElementById('cajaInicial')?.value||0),
  efectivo_contado: Number(document.getElementById('efectivoContado')?.value||0),
  turno: prompt('Turno','Noche')||'Noche',
  responsable: prompt('Responsable','Caja')||'Caja',
  observaciones: prompt('Observaciones','')||''
 };
 const res = await fetch('/comandas/cierre/cerrar',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify(payload)});
 if(!res.ok){ alert('No se pudo cerrar caja'); return; }
 const data = await res.json();
 alert('Caja cerrada correctamente');
 if (data.comprobante_path) window.open('/' + data.comprobante_path, '_blank');
 refreshCaja();
};


window.updateProducto=async(id,data)=>{await fetch(`/productos/${id}`,{method:'PUT',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify(data)}); refreshComandas();}
window.deleteProducto=async(id)=>{await fetch(`/productos/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}}); refreshComandas();}
window.cobrarComanda=async()=>{
 if(!state.selectedComandaId){return;}
 await fetch(`/comandas/${state.selectedComandaId}/cobrar`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}});
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

window.deleteComanda=async(id)=>{const r=await fetch(`/comandas/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}});if(!r.ok){const d=await r.json();alert(d.message||'No se pudo quitar');return;}if(state.selectedComandaId===id) state.selectedComandaId=null;refreshComandas();}

document.getElementById('newComandaForm').onsubmit=async(e)=>{e.preventDefault(); const payload={nombre:comandaNombre.value,cliente_documento:comandaDocumento.value}; const response=await fetch('/comandas',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify(payload)}); if(!response.ok){const data=await response.json();alert(data.message??'No se pudo crear la comanda.');return;} closeComandaForm(); await refreshComandas();};

refreshComandas();
refreshStock();

</script>
@endsection
