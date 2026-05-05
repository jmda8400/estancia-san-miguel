@extends('layout')
@section('title', 'Comandas')
@section('styles')
<style>
.grid { display:grid; grid-template-columns: 1fr 1fr; gap:16px; }
.tables-grid { display:grid; grid-template-columns: repeat(auto-fill,minmax(120px,1fr)); gap:10px; margin-top:12px; }
.table-btn { border:1px solid #d1d5db; background:#fff; border-radius:8px; padding:12px; text-align:left; }
.table-btn.active { border-color:#2563eb; background:#dbeafe; }
.item-row { display:flex; gap:8px; align-items:center; margin:6px 0; }
.item-row input { width:70px; }
@media (max-width: 800px) { .grid { grid-template-columns: 1fr; } }
</style>
@endsection
@section('content')
<h1>Sistema de comandas</h1>
<div class="grid">
    <div class="card">
        <h2>Mesas</h2>
        <label>Cantidad de mesas:
            <input id="tableCount" type="number" min="1" max="50" value="10">
        </label>
        <button id="buildTables">Generar</button>
        <div id="tables" class="tables-grid"></div>
    </div>

    <div class="card">
        <h2 id="selectedTitle">Seleccione una mesa</h2>
        <div id="ordersList"></div>
    </div>

    <div class="card" style="grid-column:1 / -1;">
        <h2>Crear pedido</h2>
        <form id="newOrderForm">
            <input id="orderName" placeholder="Producto" required>
            <input id="orderQty" type="number" min="1" value="1" required>
            <input id="orderNotes" placeholder="Notas (opcional)">
            <button type="submit">Agregar a mesa seleccionada</button>
        </form>
        <small>Primero seleccione una mesa para poder cargar pedidos.</small>
    </div>
</div>
@endsection
@section('scripts')
<script>
const state = { selectedTable: null, tables: {} };
const tablesEl = document.getElementById('tables');
const ordersEl = document.getElementById('ordersList');
const selectedTitleEl = document.getElementById('selectedTitle');

function buildTables() {
  const count = Number(document.getElementById('tableCount').value || 0);
  tablesEl.innerHTML = '';
  for (let i = 1; i <= count; i++) {
    if (!state.tables[i]) state.tables[i] = [];
    const btn = document.createElement('button');
    btn.className = 'table-btn' + (state.selectedTable === i ? ' active' : '');
    btn.innerHTML = `<strong>Mesa ${i}</strong><br><small>${state.tables[i].length} pedidos</small>`;
    btn.onclick = () => { state.selectedTable = i; render(); };
    tablesEl.appendChild(btn);
  }
}

function renderOrders() {
  if (!state.selectedTable) {
    selectedTitleEl.textContent = 'Seleccione una mesa';
    ordersEl.innerHTML = '<p>No hay mesa seleccionada.</p>';
    return;
  }
  selectedTitleEl.textContent = `Pedidos de Mesa ${state.selectedTable}`;
  const orders = state.tables[state.selectedTable] || [];
  if (!orders.length) {
    ordersEl.innerHTML = '<p>Sin pedidos cargados.</p>';
    return;
  }
  ordersEl.innerHTML = orders.map((order, index) => `
    <div class="item-row">
      <strong>${order.name}</strong>
      <input type="number" min="1" value="${order.qty}" onchange="editQty(${index}, this.value)">
      <input type="text" value="${order.notes || ''}" onchange="editNotes(${index}, this.value)">
      <button onclick="removeOrder(${index})">Quitar</button>
    </div>
  `).join('');
}

function render() { buildTables(); renderOrders(); }
function editQty(index, qty) { state.tables[state.selectedTable][index].qty = Number(qty) || 1; render(); }
function editNotes(index, notes) { state.tables[state.selectedTable][index].notes = notes; render(); }
function removeOrder(index) { state.tables[state.selectedTable].splice(index, 1); render(); }

document.getElementById('buildTables').onclick = render;
document.getElementById('newOrderForm').onsubmit = (e) => {
  e.preventDefault();
  if (!state.selectedTable) return alert('Seleccione una mesa primero.');
  state.tables[state.selectedTable].push({
    name: document.getElementById('orderName').value,
    qty: Number(document.getElementById('orderQty').value),
    notes: document.getElementById('orderNotes').value,
  });
  e.target.reset();
  document.getElementById('orderQty').value = 1;
  render();
};
render();
</script>
@endsection
