@extends('layouts.internal')
@section('title', 'Comandas')
@section('internal_title', 'Sistema de comandas')

@section('internal_content')
<div class="mb-3 app-internal-tabs">
    <button id="tabComandas" class="app-internal-tab-btn app-btn-active" onclick="switchTab('comandas')">Comandas</button>
    <button id="tabHistorial" class="app-internal-tab-btn" onclick="switchTab('historial')">Historial</button>
    <button id="tabCaja" class="app-internal-tab-btn" onclick="switchTab('caja')">Cierre de Caja</button>
</div>

<div id="comandasTab" class="orders-layout">
    <div class="panel h-full flex flex-col">
        <div class="flex items-center justify-between gap-2 flex-wrap mb-3">
            <h2 class="panel-title">Comandas</h2>
            <div class="flex items-center gap-2 flex-wrap">
                <button class="btn btn-secondary text-xs sm:text-sm" onclick="openComandaForm()">+ Agregar comanda</button>
                
            </div>
        </div>
        <div id="tables" class="tables-grid flex-1 min-h-[20rem] max-h-[calc(100vh-16rem)] overflow-y-auto pr-1"></div>
    </div>

    <div class="panel h-full flex flex-col min-h-[30rem] max-h-[calc(100vh-14rem)]">
        <h2 id="selectedTitle" class="panel-title mb-3">Seleccione una mesa</h2>
        <div id="productsList" class="space-y-3 flex-1 overflow-y-auto pr-1 min-h-[16rem]"></div>
        <div class="orders-summary mt-3 pt-3 flex items-center justify-between gap-3">
            <p id="selectedTotal" class="text-sm font-semibold text-emerald-950">Total: $ 0</p>
            <div class="flex items-center gap-2">
                                <button id="chargeBtn" class="hidden btn btn-primary btn-charge" onclick="cobrarComanda()">Cobrar</button>
            </div>
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

<div id="addComandaCard" class="panel add-product-panel hidden mt-3">
    <h2 class="panel-title mb-3">Nueva comanda</h2>
    <form id="newComandaForm" class="grid gap-3">
        <input id="comandaNombre" class="app-input" placeholder="Nombre del cliente" required>
        <input id="comandaDocumento" class="app-input" placeholder="Documento (opcional)">
        <input id="comandaTelefono" class="app-input" placeholder="Teléfono (opcional)">
        <input id="comandaDetalle" class="app-input" placeholder="Detalle adicional (opcional)">
        <div class="flex gap-2">
            <button type="button" class="btn btn-secondary w-full" onclick="closeComandaForm()">Cancelar</button>
            <button class="btn btn-primary w-full">Guardar comanda</button>
        </div>
    </form>
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
<div id="cajaTab" class="hidden panel space-y-4">
    <div class="flex items-center justify-between gap-2 mb-1 flex-wrap">
        <h2 class="panel-title">Cierre de caja</h2>
        <div class="flex gap-2">
            <button class="btn btn-secondary" onclick="abrirCierreCajaPanel()">Cerrar caja</button>
            
        </div>
    </div>
    <div id="cajaResumen" class="space-y-3 text-emerald-950"></div>
<div id="cierreCajaPanel" class="hidden rounded-xl border border-emerald-300 bg-emerald-50 p-4 space-y-3">
    <h3 class="font-semibold text-emerald-950">Confirmar cierre de caja</h3>
    <p class="text-sm text-emerald-800">Completá los datos para generar el comprobante de cierre.</p>
    <div class="grid md:grid-cols-3 gap-2">
        <input id="cierreTurno" class="app-input" placeholder="Turno" value="Noche">
        <input id="cierreResponsable" class="app-input" placeholder="Responsable" value="Caja">
        <input id="cierreObservaciones" class="app-input" placeholder="Observaciones (opcional)">
    </div>
    <div class="flex gap-2">
        <button class="btn btn-secondary" onclick="cancelarCierreCajaPanel()">Cancelar</button>
        <button class="btn btn-primary" onclick="confirmarCierreCaja()">Confirmar cierre</button>
    </div>
    <p id="cierreCajaEstado" class="text-sm text-emerald-900"></p>
</div>

</div>

@endsection
@section('scripts')
<script>
let state = { selectedComandaId: null, comandas: @json($comandas), activeTab: 'comandas', stock: [], historial: { data: [], current_page: 1, last_page: 1, total: 0 }, caja: { comandas_page: 1, productos_page: 1, cierres_page: 1 } };
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
            <p class="table-card-title">${c.nombre}</p>
            <p class="table-card-meta">${c.productos.length} producto(s)</p>
            <p class="table-card-total">${formatArs(totalMesa)}</p>
        </button>
        <button class="btn btn-danger text-xs mt-2" onclick="deleteComanda(${c.id})">Quitar</button>
    </div>`;
 }).join('');
 const comanda = state.comandas.find(c=>c.id===state.selectedComandaId);
 if(!comanda){titleEl.textContent='Seleccione una comanda'; productsEl.innerHTML=''; totalEl.textContent='Total: $ 0'; chargeBtn.classList.add('hidden'); document.getElementById('addProductCard').classList.add('hidden'); return;}
 titleEl.textContent = `Productos de ${comanda.nombre}`;
 chargeBtn.classList.remove('hidden');
 document.getElementById('addProductCard').classList.remove('hidden');
 const totalComanda = comanda.productos.reduce((acc, p) => acc + ((Number(p.precio) || 0) * p.cantidad), 0);
 totalEl.textContent = `Total: ${formatArs(totalComanda)}`;
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
            <button class="btn btn-danger btn-product-delete btn-remove-comandera text-xs" title="Quitar producto" onclick="deleteProducto(${p.id})">Eliminar</button>
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
    document.getElementById('cajaTab').classList.toggle('hidden', tab !== 'caja');
    document.getElementById('tabComandas').className = `app-internal-tab-btn ${tab === 'comandas' ? 'app-btn-active' : ''}`;
    document.getElementById('tabHistorial').className = `app-internal-tab-btn ${tab === 'historial' ? 'app-btn-active' : ''}`;
    document.getElementById('tabCaja').className = `app-internal-tab-btn ${tab === 'caja' ? 'app-btn-active' : ''}`;
    if (tab === 'historial') refreshHistorial(state.historial.current_page);
    if (tab === 'caja') refreshCaja();
};

window.selectComanda=(id)=>{state.selectedComandaId=id;renderComandas();}
window.openComandaForm=()=>document.getElementById('addComandaCard').classList.remove('hidden');
window.closeComandaForm=()=>{document.getElementById('addComandaCard').classList.add('hidden');document.getElementById('newComandaForm').reset();};

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
window.changeCajaPage = (group, delta) => {
    const key = `${group}_page`;
    const next = (state.caja[key] || 1) + delta;
    if (next < 1) return;
    state.caja[key] = next;
    refreshCaja();
};

async function refreshCaja() {
    const query = new URLSearchParams(state.caja).toString();
    const response = await fetch('/comandas/cierre/data?' + query);
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
        <div class="rounded-xl border border-emerald-200 p-3"><h3 class="font-semibold mb-2">Productos vendidos</h3><table class='w-full text-sm'><tr><th class='text-left'>Producto</th><th>Cant.</th><th class='text-right'>Total</th></tr>${(caja.productos_vendidos?.data||[]).map(p=>`<tr><td>${p.producto}</td><td class='text-center'>${p.cantidad}</td><td class='text-right'>${formatArs(p.total)}</td></tr>`).join('')}</table><div class='flex justify-between mt-2'><button class='btn btn-secondary text-xs' ${caja.productos_vendidos.current_page<=1?'disabled':''} onclick="changeCajaPage('productos',-1)">Anterior</button><span class='text-xs'>${caja.productos_vendidos.current_page}/${caja.productos_vendidos.last_page}</span><button class='btn btn-secondary text-xs' ${caja.productos_vendidos.current_page>=caja.productos_vendidos.last_page?'disabled':''} onclick="changeCajaPage('productos',1)">Siguiente</button></div></div>
        <div class="rounded-xl border border-emerald-200 p-3"><h3 class="font-semibold mb-2">Comandas incluidas</h3><table class='w-full text-sm'><tr><th class='text-left'>Comanda</th><th>Hora</th><th class='text-right'>Total</th></tr>${(caja.comandas_incluidas?.data||[]).map(c=>`<tr><td>${c.nombre}</td><td>${new Date(c.cobrada_en).toLocaleTimeString()}</td><td class='text-right'>${formatArs(c.total)}</td></tr>`).join('')}</table><div class='flex justify-between mt-2'><button class='btn btn-secondary text-xs' ${caja.comandas_incluidas.current_page<=1?'disabled':''} onclick="changeCajaPage('comandas',-1)">Anterior</button><span class='text-xs'>${caja.comandas_incluidas.current_page}/${caja.comandas_incluidas.last_page}</span><button class='btn btn-secondary text-xs' ${caja.comandas_incluidas.current_page>=caja.comandas_incluidas.last_page?'disabled':''} onclick="changeCajaPage('comandas',1)">Siguiente</button></div></div>
        <div class="rounded-xl border border-emerald-200 p-3"><h3 class="font-semibold mb-2">Historial de cierres</h3>${(caja.historial_cierres?.data||[]).map(c=>`<div class='flex justify-between text-sm border-b py-1'><span>${new Date(c.created_at).toLocaleString()} · ${c.turno||'-'} · ${c.responsable||'-'}</span><span>${formatArs(c.total_cobrado)} / Dif: ${formatArs(c.diferencia_efectivo||0)}</span></div>`).join('')}<div class='flex justify-between mt-2'><button class='btn btn-secondary text-xs' ${caja.historial_cierres.current_page<=1?'disabled':''} onclick="changeCajaPage('cierres',-1)">Anterior</button><span class='text-xs'>${caja.historial_cierres.current_page}/${caja.historial_cierres.last_page}</span><button class='btn btn-secondary text-xs' ${caja.historial_cierres.current_page>=caja.historial_cierres.last_page?'disabled':''} onclick="changeCajaPage('cierres',1)">Siguiente</button></div></div>`;
}
window.abrirCierreCajaPanel = () => {
 document.getElementById('cierreCajaPanel').classList.remove('hidden');
 document.getElementById('cierreCajaEstado').textContent = '';
};

window.cancelarCierreCajaPanel = () => {
 document.getElementById('cierreCajaPanel').classList.add('hidden');
 document.getElementById('cierreCajaEstado').textContent = '';
};

window.confirmarCierreCaja = async () => {
 const estadoEl = document.getElementById('cierreCajaEstado');
 estadoEl.textContent = 'Procesando cierre...';
 const payload = {
  caja_inicial: Number(document.getElementById('cajaInicial')?.value||0),
  efectivo_contado: Number(document.getElementById('efectivoContado')?.value||0),
  turno: document.getElementById('cierreTurno')?.value || 'Noche',
  responsable: document.getElementById('cierreResponsable')?.value || 'Caja',
  observaciones: document.getElementById('cierreObservaciones')?.value || ''
 };
 const res = await fetch('/comandas/cierre/cerrar',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify(payload)});
 if(!res.ok){ estadoEl.textContent = 'No se pudo cerrar caja. Verificá los datos e intentá nuevamente.'; return; }
 const data = await res.json();
 estadoEl.textContent = 'Caja cerrada correctamente.';
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

document.getElementById('newComandaForm').onsubmit=async(e)=>{e.preventDefault(); const payload={nombre:comandaNombre.value,cliente_documento:comandaDocumento.value,cliente_telefono:comandaTelefono.value,cliente_detalle:comandaDetalle.value}; const response=await fetch('/comandas',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify(payload)}); if(!response.ok){const data=await response.json();alert(data.message??'No se pudo crear la comanda.');return;} closeComandaForm(); await refreshComandas();};

refreshComandas();
refreshStock();
</script>
@endsection
