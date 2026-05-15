@extends('layouts.internal')
@section('title', 'Stock')
@section('internal_title', 'Vista de stock')

@section('internal_content')
<section class="stock-view mx-auto w-full max-w-[78rem]">
<div class="app-card app-stock-card">
    <div class="stock-tabs app-internal-tabs mb-4">
        <button id="tabStock" class="app-btn app-btn-pill app-btn-active" onclick="switchTab('stock')">Stock</button>
        <button id="tabHistorial" class="app-btn app-btn-pill" onclick="switchTab('historial')">Historial</button>
    </div>

<div id="stockTab">
    <div class="app-stock-table-wrap">
    <table class="w-full text-sm app-list-table">
        <thead><tr><th class="text-left">Producto</th><th class="text-left">Tipo</th><th class="text-left">Cantidad</th><th class="text-left">Precio</th><th class="text-right">Acciones</th></tr></thead>
        <tbody class="text-zinc-900" id="stockBody"></tbody>
    </table>
    </div>
    <button class="mt-4 app-btn app-stock-add-btn app-btn-pill" onclick="openProductForm()">+ Agregar producto</button>

    <div id="addProductCard" class="mt-4 hidden rounded-2xl border border-emerald-200 bg-emerald-50/50 p-4 md:p-5">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-emerald-900">Nuevo producto</h3>
        <div class="mt-4 grid gap-3 md:grid-cols-2">
            <div>
                <label for="newProducto" class="mb-1 block text-xs font-semibold text-emerald-950">Nombre del producto</label>
                <input id="newProducto" class="app-input w-full" placeholder="Ej. Fernet" />
            </div>
            <div>
                <label for="newTipo" class="mb-1 block text-xs font-semibold text-emerald-950">Tipo</label>
                <select id="newTipo" class="app-input app-stock-input w-full" onchange="toggleNewCantidad()">
                    <option value="producto">Producto con stock</option>
                    <option value="servicio">Servicio ilimitado</option>
                </select>
            </div>
            <div id="newCantidadWrap">
                <label for="newCantidad" class="mb-1 block text-xs font-semibold text-emerald-950">Cantidad</label>
                <input id="newCantidad" type="number" min="0" value="0" class="app-input app-stock-input w-full" />
            </div>
            <div>
                <label for="newPrecio" class="mb-1 block text-xs font-semibold text-emerald-950">Precio</label>
                <input id="newPrecio" type="number" min="0" step="0.01" value="0" class="app-input app-stock-input w-full" />
            </div>
        </div>
        <div class="mt-4 flex flex-wrap justify-end gap-2">
            <button class="app-btn app-btn-pill" onclick="cancelProductForm()">Cancelar</button>
            <button class="app-btn app-stock-add-btn app-btn-pill" onclick="saveProduct()">Guardar</button>
        </div>
    </div>
</div>

<div id="historialTab" class="hidden">
    <h2 class="font-semibold mb-3">Historial de cambios de stock</h2>
    <div id="stockHistoryList" class="space-y-2"></div>
    <div class="mt-4 flex items-center justify-between">
        <button id="stockPrevPage" class="app-btn app-btn-pill" onclick="changeStockHistoryPage(-1)">Anterior</button>
        <span id="stockPageInfo" class="text-sm text-emerald-950"></span>
        <button id="stockNextPage" class="app-btn app-btn-pill" onclick="changeStockHistoryPage(1)">Siguiente</button>
    </div>
</div>
</div>
</section>

@endsection
@section('scripts')
<script>
let stockItems = @json($stockItems);
let stockHistory = { data: [], current_page: 1, last_page: 1, total: 0 };

function rowTemplate(item){
    const isUnlimited = Boolean(item.ilimitado);
    const tipoControl = isUnlimited
        ? `<span class='inline-flex items-center rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-900'>Servicio ilimitado</span>`
        : `<span class='inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-900'>Producto con stock</span>`;

    const cantidadControl = isUnlimited
        ? `<span class='text-zinc-400'>—</span>`
        : `<div class='app-stock-field'><input data-field='cantidad' type='number' min='0' value='${item.cantidad}' class='app-input app-stock-input' onchange='updateStock(${item.id}, this)'></div>`;

    return `<tr><td>${item.producto}</td><td>${tipoControl}</td><td>${cantidadControl}</td><td><div class='app-stock-field'><input data-field='precio' type='number' min='0' step='0.01' value='${item.precio ?? 0}' class='app-input app-stock-input' onchange='updateStock(${item.id}, this)'></div></td><td class='text-right'><div class='app-stock-actions'><button class='app-btn app-stock-row-btn app-btn-pill' onclick='removeStock(${item.id})'>Quitar</button></div></td></tr>`;
}

function renderStock(){
    const stock = stockItems.filter(item => item.id && !item.ilimitado);
    const servicios = stockItems.filter(item => item.id && item.ilimitado);
    const grupos = [];

    if (stock.length > 0) {
        grupos.push(`<tr><td colspan="5" class="py-2 text-xs font-semibold uppercase tracking-wide text-emerald-900">Productos con stock</td></tr>`);
        grupos.push(stock.map(rowTemplate).join(''));
    }
    if (servicios.length > 0) {
        grupos.push(`<tr><td colspan="5" class="py-2 text-xs font-semibold uppercase tracking-wide text-indigo-900">Servicios ilimitados</td></tr>`);
        grupos.push(servicios.map(rowTemplate).join(''));
    }

    document.getElementById('stockBody').innerHTML = grupos.join('');
}

window.openProductForm = () => {
    document.getElementById('addProductCard').classList.remove('hidden');
    document.getElementById('newProducto').focus();
};

window.cancelProductForm = () => {
    document.getElementById('addProductCard').classList.add('hidden');
    document.getElementById('newProducto').value = '';
    document.getElementById('newTipo').value = 'producto';
    document.getElementById('newCantidad').value = 0;
    document.getElementById('newPrecio').value = 0;
    toggleNewCantidad();
};

window.saveProduct = async () => {
    const producto = document.getElementById('newProducto').value.trim();
    const tipo = document.getElementById('newTipo').value;
    const ilimitado = tipo === 'servicio';
    const cantidad = ilimitado ? 0 : document.getElementById('newCantidad').value;
    const precio = document.getElementById('newPrecio').value;
    if (!producto) return;
    await fetch('/stock',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({producto,tipo,cantidad,ilimitado,precio})});
    cancelProductForm();
    await refreshStock();
    await refreshHistory();
};

window.toggleNewCantidad = () => {
    const tipo = document.getElementById('newTipo').value;
    const wrap = document.getElementById('newCantidadWrap');
    const input = document.getElementById('newCantidad');
    const isService = tipo === 'servicio';
    wrap.classList.toggle('hidden', isService);
    input.disabled = isService;
    if (isService) input.value = 0;
};

window.removeStock = async (id) => {
    await fetch(`/stock/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}});
    await refreshStock();
    await refreshHistory();
};

window.updateStock=async(id,input)=>{
    const row = input.closest('tr');
    const isUnlimited = !row.querySelector("input[data-field='cantidad']");
    const tipo = isUnlimited ? 'servicio' : 'producto';
    const cantidadInput = row.querySelector("input[data-field='cantidad']");
    const cantidad = isUnlimited ? 0 : (cantidadInput ? cantidadInput.value : 0);
    const precio = row.querySelector("input[data-field='precio']").value;
    await fetch(`/stock/${id}`,{method:'PUT',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({tipo,cantidad,ilimitado:isUnlimited,precio})});
    await refreshStock();
    await refreshHistory();
}

async function refreshStock(){
    const response = await fetch('/stock/data');
    stockItems = await response.json();
    renderStock();
}

async function refreshHistory(page = 1){
    const response = await fetch(`/stock/historial/data?page=${page}`);
    const data = await response.json();
    stockHistory = data;
    const labels = { suma: 'Suma', resta: 'Resta', agregado: 'Agregado', quitado: 'Quitado' };
    document.getElementById('stockHistoryList').innerHTML = data.data.map(h => `<div class='rounded border border-amber-200 bg-amber-50 p-3'><strong>${labels[h.accion]}</strong> · ${h.producto} · ${h.cantidad}<div class='text-sm text-amber-950'>${new Date(h.created_at).toLocaleString()}</div></div>`).join('') || '<p class="text-amber-900">No hay movimientos.</p>';
    document.getElementById('stockPageInfo').textContent = `Página ${data.current_page} de ${data.last_page} · ${data.total} movimientos`;
    document.getElementById('stockPrevPage').disabled = data.current_page <= 1;
    document.getElementById('stockNextPage').disabled = data.current_page >= data.last_page;
}

window.changeStockHistoryPage = (delta) => {
    const nextPage = stockHistory.current_page + delta;
    if (nextPage < 1 || nextPage > stockHistory.last_page) return;
    refreshHistory(nextPage);
};

window.switchTab = (tab) => {
    document.getElementById('stockTab').classList.toggle('hidden', tab !== 'stock');
    document.getElementById('historialTab').classList.toggle('hidden', tab !== 'historial');
    document.getElementById('tabStock').className = `app-btn app-btn-pill ${tab === 'stock' ? 'app-btn-active' : ''}`;
    document.getElementById('tabHistorial').className = `app-btn app-btn-pill ${tab === 'historial' ? 'app-btn-active' : ''}`;
    if (tab === 'historial') refreshHistory();
};

renderStock();
toggleNewCantidad();
refreshHistory();
</script>
@endsection
