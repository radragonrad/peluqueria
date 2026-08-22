<template>
  <div class="caja-view">
    <header class="section-header">
      <div class="header-left">
        <div class="title-row">
          <h1>Gestión de Caja</h1>
          <div class="filters-caja-mini">
            <button v-for="f in periodos" :key="f.id" 
                    @click="periodoActual = f.id" 
                    :class="{ 'active': periodoActual === f.id }">
              {{ f.label }}
            </button>
          </div>
        </div>
        <p class="text-muted">Control en tiempo real de ingresos y gastos</p>
      </div>

      <div class="header-actions">
            <input type="date" v-model="fechaFiltroCustom" class="input-date">
    <button v-if="fechaFiltroCustom" @click="fechaFiltroCustom = ''; periodoActual = 'hoy'" class="btn-clear">
      <i class="fas fa-times"></i>
    </button>
        <button @click="abrirModalMovimiento('INGRESO')" class="btn-nuevo ingreso">
          <i class="fas fa-plus-circle"></i> Ingreso
        </button>
        <button @click="abrirModalMovimiento('GASTO')" class="btn-nuevo gasto">
          <i class="fas fa-minus-circle"></i> Gasto
        </button>
      </div>
    </header>

    <div class="stats-grid">
      <div class="stat-card total-ingresos">
        <div class="stat-icon"><i class="fas fa-arrow-up"></i></div>
        <div class="stat-info">
          <span>Ingresos Totales</span>
          <h3>{{ totalIngresos }}€</h3>
          <small>Ya cobrados</small>
        </div>
      </div>

      <div class="stat-card total-pendientes">
        <div class="stat-icon"><i class="fas fa-hourglass-half"></i></div>
        <div class="stat-info">
          <span>Previsto Hoy</span>
          <h3>{{ ingresosPendientes.toFixed(2) }}€</h3>
          <small>Por finalizar</small>
        </div>
      </div>

      <div class="stat-card total-gastos">
        <div class="stat-icon"><i class="fas fa-arrow-down"></i></div>
        <div class="stat-info">
          <span>Gastos Totales</span>
          <h3>{{ gastosTotales }}€</h3>
          <small>Pagos y compras</small>
        </div>
      </div>

<div class="stat-card balance" :class="{ 'negativo': parseFloat(balanceNeto) < 0 }">
  <div class="stat-icon"><i class="fas fa-wallet"></i></div>
  <div class="stat-info">
    <span>Beneficio Neto</span>
    <h3>{{ balanceNeto }}€</h3>
    <small>Efectivo real disponible</small>
  </div>
</div>
    </div>

    <div class="payment-methods-breakdown">
      <div v-for="(monto, metodo) in desglosePagos" :key="metodo" class="method-card" :class="metodo">
        <div class="method-icon"><i :class="obtenerIconoPago(metodo)"></i></div>
        <div class="method-info">
          <span class="method-name">{{ metodo }}</span>
          <span class="method-amount">{{ monto.toFixed(2) }}€</span>
        </div>
      </div>
    </div>

    <div class="table-card">
      <div class="table-header">
        <h3>Movimientos Manuales</h3>
      </div>
      <table class="custom-table">
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Concepto</th>
            <th>Categoría</th>
            <th>Método</th>
            <th>Importe</th>
            <th style="text-align: center;">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="m in movimientos" :key="m.id" :class="m.tipo.toLowerCase()">
            <td data-label="Fecha">{{ formatearFecha(m.fecha) }}</td>
            <td data-label="Concepto"><strong>{{ m.concepto }}</strong></td>
            <td data-label="Categoría"><span class="cat-tag">{{ m.categoria }}</span></td>
            <td class="metodo-pago-cell" data-label="Método">
              <i :class="obtenerIconoPago(m.metodo_pago)"></i>
              {{ m.metodo_pago }}
            </td>
            <td class="importe-col" data-label="Importe" :class="m.tipo === 'GASTO' ? 'gasto' : 'ingreso'">
              {{ m.tipo === 'GASTO' ? '-' : '+' }}{{ m.importe }}€
            </td>
            <td class="acciones-cell" data-label="Acciones" style="text-align: center;">
              <button @click="eliminarMovimiento(m.id)" class="btn-del" title="Eliminar movimiento">
                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>
          <tr v-if="movimientos.length === 0">
            <td colspan="6" class="empty-state">No hay movimientos manuales registrados.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="mostrarModal" class="modal-overlay">
      <div class="modal-content movement-modal">
        <div class="modal-header">
          <h3>Nuevo {{ nuevoMovimiento.tipo }}</h3>
          <button @click="mostrarModal = false" class="btn-close-modal">&times;</button>
        </div>
        
        <div class="modal-body">
          <div class="form-group">
            <label>Concepto</label>
            <input v-model="nuevoMovimiento.concepto" placeholder="Ej: Venta de producto o Pago Luz">
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Importe (€)</label>
              <input type="number" v-model="nuevoMovimiento.importe" step="0.01">
            </div>
            <div class="form-group">
              <label>Método de Pago</label>
              <select v-model="nuevoMovimiento.metodo_pago">
                <option value="efectivo">Efectivo</option>
                <option value="tarjeta">Tarjeta</option>
                <option value="bizzum">Bizzum</option>
              </select>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button @click="guardarMovimiento" class="btn-confirm-action" :class="nuevoMovimiento.tipo.toLowerCase()" :disabled="cargando">
            {{ cargando ? 'Guardando...' : 'Registrar ' + nuevoMovimiento.tipo }}
          </button>
          <button @click="mostrarModal = false" class="btn-cancel-modal">Cancelar</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';

// Estados
const movimientos = ref([]);
const ingresosReservas = ref(0);
const ingresosManuales = ref(0);
const gastosTotales = ref(0);
const desglosePagos = ref({});
const periodoActual = ref('hoy');
const cargando = ref(false);
const mostrarModal = ref(false);
const fechaFiltroCustom = ref('');
const ingresosPendientes = ref(0);

const nuevoMovimiento = ref({
  tipo: 'INGRESO',
  concepto: '',
  importe: 0,
  metodo_pago: 'efectivo',
  categoria: 'Venta Producto'
});


const totalIngresos = computed(() => {
  // 1. Suma bruta (Reservas + Manuales)
  const sumaBruta = parseFloat(ingresosReservas.value || 0) + parseFloat(ingresosManuales.value || 0);
  
  // 2. Buscamos la deuda para restarla de los ingresos que "ya han entrado"
  const deudaKey = Object.keys(desglosePagos.value).find(k => k.toLowerCase() === 'deuda');
  const montoDeuda = deudaKey ? parseFloat(desglosePagos.value[deudaKey]) : 0;
  
  // 3. Resultado: Dinero que físicamente ha entrado
  return (sumaBruta - montoDeuda).toFixed(2);
});

const balanceNeto = computed(() => {
  // El beneficio real es: (Ingresos cobrados + Ingresos manuales) - Gastos manuales
  const ingresosReales = parseFloat(totalIngresos.value);
  const gastos = parseFloat(gastosTotales.value || 0);
  
  const neto = ingresosReales - gastos;
  
  return neto.toFixed(2);
});

const periodos = [
  { id: 'hoy', label: 'Hoy' },
  { id: 'semana', label: 'Esta Semana' },
  { id: 'mes', label: 'Este Mes' },
  { id: 'mes_pasado', label: 'Mes Pasado' } // Nueva opción
];

const cargarDatosCaja = async () => {
  cargando.value = true;
  try {
    let url = `/backend/api/get_caja.php?`;
    if (fechaFiltroCustom.value) {
      url += `fecha=${fechaFiltroCustom.value}&periodo=dia_especifico`;
    } else {
      url += `periodo=${periodoActual.value}`;
    }

    const response = await fetch(url, { credentials: 'include' });
    const data = await response.json();
    
    // ASIGNACIONES CRÍTICAS:
    movimientos.value = data.movimientos || [];
    ingresosReservas.value = data.ingresos_reservas || 0;
    ingresosManuales.value = data.ingresos_manuales || 0; // <--- Asegúrate de esta
    gastosTotales.value = data.gastos_totales || 0;     // <--- Y DE ESTA
    desglosePagos.value = data.desglose_pagos || {};
    ingresosPendientes.value = data.ingresos_pendientes || 0;

  } catch (e) {
    console.error("Error:", e);
  } finally {
    cargando.value = false;
  }
};

const guardarMovimiento = async () => {
  if (!nuevoMovimiento.value.concepto || nuevoMovimiento.value.importe <= 0) {
    alert("Por favor, rellena todos los campos");
    return;
  }
  cargando.value = true;
  try {
    await fetch('/backend/api/operaciones_caja.php', {
      method: 'POST',
      body: JSON.stringify(nuevoMovimiento.value)
    });
    mostrarModal.value = false;
    await cargarDatosCaja();
    nuevoMovimiento.value = { tipo: 'INGRESO', concepto: '', importe: 0, metodo_pago: 'efectivo', categoria: 'Venta Producto' };
  } catch (e) {
    console.error(e);
  } finally {
    cargando.value = false;
  }
};

const eliminarMovimiento = async (id) => {
  if (!id) {
    console.error("No se ha proporcionado un ID válido");
    return;
  }

  if (!confirm("¿Seguro que quieres eliminar este registro?")) return;
  
  try {
    // Usamos URLSearchParams para asegurar que el ID viaje correctamente en la URL
    const response = await fetch(`/backend/api/operaciones_caja.php?id=${id}`, { 
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json'
      }
    });

    if (response.ok) {
      // Si el borrado es exitoso en el server, recargamos la lista
      await cargarDatosCaja();
    } else {
      const errorData = await response.json();
      alert("Error al eliminar: " + (errorData.message || "Error desconocido"));
    }
  } catch (e) {
    console.error("Error en la petición DELETE:", e);
    alert("No se pudo conectar con el servidor para eliminar");
  }
};



const obtenerIconoPago = (metodo) => {
  const iconos = {
    'efectivo': 'fas fa-money-bill-wave',
    'tarjeta': 'fas fa-credit-card',
    'bizzum': 'fas fa-mobile-alt',
    'deuda': 'fas fa-user-clock'
  };
  return iconos[metodo] || 'fas fa-coins';
};

const abrirModalMovimiento = (tipo) => {
  nuevoMovimiento.value.tipo = tipo;
  nuevoMovimiento.value.categoria = tipo === 'GASTO' ? 'Gasto Local' : 'Venta Extra';
  mostrarModal.value = true;
};

const formatearFecha = (f) => {
  return new Date(f).toLocaleDateString('es-ES', { 
    day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' 
  });
};



watch(fechaFiltroCustom, (nuevaFecha) => {
  if (nuevaFecha) {
    periodoActual.value = ''; // Desactivamos botones Hoy/Semana/Mes
    cargarDatosCaja();
  }
});

watch(periodoActual, (nuevo) => {
  if (nuevo) {
    fechaFiltroCustom.value = ''; 
    cargarDatosCaja();
  }
});

onMounted(() => cargarDatosCaja());
</script>

<style scoped>
/* Contenedor Principal */
.caja-view { 
  padding: 20px 40px; 
  width: 100%; 
  max-width: 100%; 
  background: #f8fafc; 
  min-height: 100vh;
  box-sizing: border-box;
}

/* Header Reestructurado */
.section-header { 
  display: flex; 
  justify-content: space-between; 
  align-items: center; 
  margin-bottom: 30px; 
  width: 100%;
}

.title-row { 
  display: flex; 
  align-items: center; 
  gap: 20px; 
}

.title-row h1 {
  font-size: 1.8rem;
  color: #1e293b;
  margin: 0;
}

.text-muted { 
  color: #64748b; 
  font-size: 0.9rem; 
  margin-top: 5px;
}

/* Filtros Mini (Hoy, Semana, Mes) */
.filters-caja-mini {
  display: flex;
  background: #e2e8f0;
  padding: 4px;
  border-radius: 12px;
}

.filters-caja-mini button {
  padding: 8px 16px;
  border-radius: 10px;
  border: none;
  background: transparent;
  cursor: pointer;
  font-weight: 600;
  color: #64748b;
  transition: 0.3s;
  font-size: 0.85rem;
}

.filters-caja-mini button.active {
  background: white;
  color: #e75480;
  box-shadow: 0 4px 10px rgba(0,0,0,0.05);
}

/* Acciones (Botones Superiores) */
.header-actions { display: flex; gap: 12px; align-items: center; }

.input-date { 
  padding: 10px; 
  border-radius: 10px; 
  border: 1px solid #cbd5e1; 
  outline: none;
  font-family: inherit;
}

.btn-nuevo { 
  padding: 12px 20px; 
  border-radius: 12px; 
  border: none; 
  font-weight: 700; 
  cursor: pointer; 
  color: white; 
  display: flex;
  align-items: center;
  gap: 8px;
  transition: 0.3s; 
}

.btn-nuevo.ingreso { background: #2ecc71; box-shadow: 0 4px 12px rgba(46, 204, 113, 0.2); }
.btn-nuevo.gasto { background: #e74c3c; box-shadow: 0 4px 12px rgba(231, 76, 60, 0.2); }
.btn-nuevo:hover { transform: translateY(-2px); opacity: 0.9; }

/* Grid de Estadísticas (Fila Superior) */
.stats-grid { 
  display: grid; 
  grid-template-columns: repeat(4, 1fr); 
  gap: 20px; 
  margin-bottom: 25px; 
}

.stat-card { 
  background: white; 
  padding: 20px; 
  border-radius: 20px; 
  display: flex; 
  align-items: center; 
  gap: 15px; 
  border: 1px solid #f1f5f9;
  box-shadow: 0 4px 6px rgba(0,0,0,0.02);
}

.stat-icon { 
  width: 50px; 
  height: 50px; 
  border-radius: 12px; 
  display: flex; 
  align-items: center; 
  justify-content: center; 
  font-size: 1.3rem;
  flex-shrink: 0;
}

.total-ingresos .stat-icon { background: #e6fffa; color: #2ecc71; }
.total-pendientes .stat-icon { background: #fff8e1; color: #f39c12; }
.total-gastos .stat-icon { background: #fff5f5; color: #e74c3c; }
.balance .stat-icon { background: #f0f7ff; color: #3498db; }

.stat-info span { font-size: 0.75rem; color: #94a3b8; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
.stat-info h3 { font-size: 1.5rem; margin: 2px 0; font-weight: 800; color: #1e293b; }
.stat-info small { color: #64748b; font-size: 0.75rem; }

/* Desglose por Método (Fila Media - Estilo de las imágenes) */
.payment-methods-breakdown { 
  display: grid; 
  grid-template-columns: repeat(4, 1fr); 
  gap: 20px; 
  margin-bottom: 30px; 
}

.method-card { 
  background: white; 
  padding: 18px; 
  border-radius: 16px; 
  display: flex; 
  align-items: center; 
  gap: 15px; 
  border: 1px solid #f1f5f9;
  position: relative;
  box-shadow: 0 2px 4px rgba(0,0,0,0.02);
}

/* Bordes inferiores de colores como en tu imagen */
.method-card.efectivo { border-bottom: 4px solid #2ecc71; }
.method-card.tarjeta { border-bottom: 4px solid #3498db; }
.method-card.bizzum { border-bottom: 4px solid #f1c40f; }
.method-card.deuda { border-bottom: 4px solid #e74c3c; }

.method-icon {
  width: 42px;
  height: 42px;
  background: #f8fafc;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #64748b;
  font-size: 1.1rem;
}

.method-info .method-name {
  display: block;
  font-size: 0.7rem;
  font-weight: 700;
  color: #94a3b8;
  text-transform: uppercase;
}

.method-info .method-amount {
  font-size: 1.2rem;
  font-weight: 700;
  color: #1e293b;
}

/* Tabla de Movimientos */
.table-card { 
  background: white; 
  border-radius: 16px; 
  box-shadow: 0 4px 20px rgba(0,0,0,0.05); 
  overflow: hidden;
  border: 1px solid #f1f5f9;
}

.table-header { padding: 20px; border-bottom: 1px solid #f1f5f9; }
.table-header h3 { margin: 0; font-size: 1.1rem; color: #1e293b; }

.custom-table { width: 100%; border-collapse: collapse; }
.custom-table th { 
  background: #f8fafc; 
  padding: 15px 20px; 
  text-align: left; 
  color: #94a3b8; 
  font-size: 0.75rem; 
  text-transform: uppercase; 
  letter-spacing: 1px;
}

.custom-table td { padding: 15px 20px; border-bottom: 1px solid #f8fafc; color: #475569; }

.cat-tag {
  background: #f1f5f9;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 0.75rem;
  color: #64748b;
  font-weight: 600;
}

.importe-col { font-weight: 800; font-size: 1rem; }
.importe-col.ingreso { color: #2ecc71; }
.importe-col.gasto { color: #e74c3c; }

.btn-del {
  background: #fff1f0;
  color: #ff4d4f;
  border: none;
  width: 35px;
  height: 35px;
  border-radius: 8px;
  cursor: pointer;
  transition: 0.3s;
}
.btn-del:hover { background: #ff4d4f; color: white; }

/* Estilos del Modal (Mantenidos) */
.modal-overlay {
  position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6);
  display: flex; align-items: center; justify-content: center; z-index: 1000;
  backdrop-filter: blur(4px);
}
.movement-modal {
  background: white; border-radius: 24px; width: 450px; overflow: hidden;
  box-shadow: 0 20px 40px rgba(0,0,0,0.2);
}
.modal-header { padding: 25px; background: #f8fafc; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; }
.modal-body { padding: 25px; }
.modal-footer { padding: 20px 25px; background: #f8fafc; display: flex; flex-direction: column; gap: 10px; }

/* Animación de Pulso para Previsto */
.total-pendientes .stat-icon i { animation: pulse-slow 3s infinite; }
@keyframes pulse-slow {
  0% { opacity: 1; }
  50% { opacity: 0.5; }
  100% { opacity: 1; }
}

/* --- ESTILOS DEL MODAL Y FORMULARIO (RECUPERADOS) --- */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.7); /* Oscurecemos un poco más el fondo */
  backdrop-filter: blur(6px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.movement-modal {
  background: white;
  padding: 0; /* Quitamos padding para que el header tenga su propio fondo */
  border-radius: 24px;
  width: 95%;
  max-width: 450px;
  box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.modal-header {
  padding: 25px;
  background: #f8fafc;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #e2e8f0;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.3rem;
  color: #1e293b;
  font-weight: 800;
}

.btn-close-modal {
  background: #f1f5f9;
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  color: #64748b;
  font-size: 1.2rem;
  cursor: pointer;
  transition: 0.2s;
}

.btn-close-modal:hover {
  background: #e2e8f0;
  color: #1e293b;
}

.modal-body {
  padding: 25px;
}

/* Estilos de los Inputs y Labels */
.form-group {
  margin-bottom: 20px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group label {
  font-weight: 700;
  font-size: 0.85rem;
  color: #475569;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.form-group input, 
.form-group select {
  padding: 14px;
  border-radius: 12px;
  border: 2px solid #f1f5f9; /* Bordes suaves */
  background: #f8fafc;
  font-size: 1rem;
  font-family: inherit;
  color: #1e293b;
  transition: 0.3s;
  outline: none;
}

.form-group input:focus, 
.form-group select:focus {
  border-color: #e75480; /* Color de tu marca al enfocar */
  background: white;
  box-shadow: 0 0 0 4px rgba(231, 84, 128, 0.1);
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
}

/* Footer y Botones de Acción */
.modal-footer {
  padding: 0 25px 25px 25px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.btn-confirm-action {
  width: 100%;
  padding: 16px;
  border-radius: 14px;
  border: none;
  font-weight: 800;
  font-size: 1rem;
  cursor: pointer;
  color: white;
  transition: 0.3s;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.btn-confirm-action.ingreso {
  background: #2ecc71;
  box-shadow: 0 6px 15px rgba(46, 204, 113, 0.3);
}

.btn-confirm-action.gasto {
  background: #e74c3c;
  box-shadow: 0 6px 15px rgba(231, 76, 60, 0.3);
}

.btn-confirm-action:hover {
  transform: translateY(-2px);
  filter: brightness(1.1);
}

.btn-confirm-action:disabled {
  background: #cbd5e1;
  cursor: not-allowed;
  transform: none;
}

.btn-cancel-modal {
  background: transparent;
  border: none;
  color: #94a3b8;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  padding: 10px;
  transition: 0.2s;
}

.btn-cancel-modal:hover {
  color: #64748b;
  text-decoration: underline;
}
.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.btn-clear {
  background: #f1f5f9;
  border: none;
  color: #64748b;
  cursor: pointer;
  width: 38px;
  height: 38px;
  border-radius: 10px;
  flex-shrink: 0;
  transition: 0.2s;
}

.btn-clear:hover {
  background: #e2e8f0;
  color: #1e293b;
}

/* --- RESPONSIVE --- */

/* Portátiles pequeños y tablets en horizontal */
@media (max-width: 1200px) {
  .caja-view { padding: 20px; }

  .stats-grid,
  .payment-methods-breakdown {
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
  }
}

/* Tablets (iPad vertical) */
@media (max-width: 992px) {
  .section-header {
    flex-direction: column;
    align-items: stretch;
    gap: 15px;
    margin-bottom: 20px;
  }

  .title-row {
    flex-wrap: wrap;
    gap: 12px;
  }

  .header-actions {
    flex-wrap: wrap;
  }

  .input-date {
    flex: 1;
    min-width: 150px;
  }

  .btn-nuevo {
    flex: 1;
    justify-content: center;
  }
}

/* Móviles */
@media (max-width: 768px) {
  .caja-view { padding: 15px 10px; }

  .title-row h1 { font-size: 1.4rem; }

  .filters-caja-mini {
    width: 100%;
  }

  .filters-caja-mini button {
    flex: 1;
    padding: 8px 6px;
    font-size: 0.78rem;
  }

  .stat-card { padding: 15px; border-radius: 16px; }
  .stat-info h3 { font-size: 1.25rem; }

  .method-card { padding: 12px; gap: 10px; }
  .method-info .method-amount { font-size: 1rem; }

  /* Tabla → tarjetas apiladas */
  .custom-table thead { display: none; }

  .custom-table,
  .custom-table tbody,
  .custom-table tr,
  .custom-table td {
    display: block;
    width: 100%;
    box-sizing: border-box;
  }

  .custom-table tr {
    padding: 12px 16px;
    border-bottom: 1px solid #f1f5f9;
  }

  .custom-table td {
    padding: 6px 0;
    border: none;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
  }

  .custom-table td::before {
    content: attr(data-label);
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 700;
    color: #94a3b8;
    flex-shrink: 0;
  }

  .custom-table td.empty-state {
    justify-content: center;
    padding: 20px 0;
  }

  .custom-table td.empty-state::before { content: none; }
}

/* Móviles estrechos */
@media (max-width: 480px) {
  .stats-grid { grid-template-columns: 1fr; }

  .header-actions .btn-nuevo {
    padding: 12px 10px;
    font-size: 0.9rem;
  }

  .form-row { grid-template-columns: 1fr; }
}
</style>