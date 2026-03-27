<template>
  <div class="servicios-view">
    <header class="section-header">
      <div class="header-left">
        <div class="breadcrumb">
          <span>Panel de Control</span> 
          <i class="fas fa-chevron-right"></i> 
          <span class="current">Reservas</span>
        </div>
        <div class="title-container">
          <h1>Agenda de Citas</h1>
          <span class="badge-count">{{ reservasFiltradas.length }} citas encontradas</span>
        </div>
      </div>

      <div class="header-actions">
        <button @click="refrescarManual" class="btn-refresh" :class="{ 'spinning': cargandoRefresco }" title="Refrescar datos">
          <i class="fas fa-sync-alt"></i>
        </button>
        <div class="filters">
          <div class="date-filter-box">
            <input type="date" v-model="dateFilter" class="status-select" @change="currentPage = 1">
            <button v-if="dateFilter" @click="dateFilter = ''; currentPage = 1" class="btn-clear-date">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <select v-model="peluqueroFilter" class="status-select" @change="currentPage = 1">
            <option value="all">Todos los peluqueros</option>
            <option v-for="p in listaPeluqueros" :key="p" :value="p">{{ p }}</option>
          </select>
          <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" v-model="search" placeholder="Buscar cliente..." @input="currentPage = 1">
          </div>
          
          <select v-model="statusFilter" class="status-select" @change="currentPage = 1">
            <option value="all">Todos los estados</option>
            <option value="PENDIENTE">Pendientes</option>            
            <option value="COMPLETADA">Completadas</option>
            <option value="ANULADA LOCAL">Canceladas</option>
          </select>
        </div>
      </div>
    </header>

    <div class="table-card">
      <table class="custom-table">
        <thead>
          <tr>
            <th>Fecha / Hora</th>
            <th>Cliente</th>
            <th>Perfil Cliente</th>
            <th>Peluquero</th>
            <th>Servicio</th>
            <th>Precio</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="r in reservasPaginadas" :key="r.id" :class="getRowClass(r)">
            <td data-label="Fecha / Hora">
              <div class="date-display">
                <span class="font-bold">{{ formatearFecha(r.fecha) }}</span>
                <span class="text-muted"><i class="far fa-clock"></i> {{ r.hora.substring(0,5) }}</span>
              </div>
            </td>
            <td data-label="Cliente">
              <div class="user-info">
                <div class="avatar-mini">{{ r.cliente_nombre.charAt(0) }}</div>
                <div>
                  <div class="font-bold">{{ r.cliente_nombre }}</div>
                  <div class="text-muted small">{{ r.cliente_telefono }}</div>
                </div>
              </div>
            </td>

            <td data-label="Perfil Cliente">
              <div class="tags-container-table">
                <span v-for="tag in r.etiquetas" :key="tag.nombre" 
                      class="tag-mini-agenda" :style="{ backgroundColor: tag.color }">
                  {{ tag.nombre }}
                </span>
                <span v-if="!r.etiquetas?.length" class="no-tags-muted">-</span>
              </div>
            </td>

            <td data-label="Peluquero">
              <div class="peluquero-info">
                <i class="fas fa-cut"></i>
                <span>{{ r.peluquero_nombre || 'No asignado' }}</span>
              </div>
            </td>
            <td data-label="Servicio">
              <div class="service-tag-display">
                <img :src="`/img-icons/${r.servicio_icono}`" class="table-icon" />
                <span>{{ r.servicio_nombre }}</span>
              </div>
            </td>
            <td data-label="Precio"> <span class="price-text">{{ r.precio }}€</span></td>

            <td data-label="Estado">
              <div class="status-payment-container">
                  <span :class="['status-badge', obtenerClaseFinal(r)]">
                      {{ r.estado === 'ANULADA WEB' ? 'Anulada Web' : obtenerTextoFinal(r) }}
                  </span>
                  </div>
            </td>

            <td data-label="Acciones" class="actions">
                <div v-if="!esFechaBloqueada(r.fecha)" class="action-buttons">
                    <template v-if="r.estado !== 'COMPLETADA' && r.estado !== 'ANULADA LOCAL'">
                        <button @click="completarCita(r)" class="btn-action check" title="Validar Cita">
                            <i class="fas fa-check-circle"></i>
                        </button>
                        <button @click="prepararAnulacion(r)" class="btn-action cancel" title="Anular Cita">
                            <i class="fas fa-times-circle"></i>
                        </button>
                    </template>
                    <button v-else @click="revertirEstado(r)" class="btn-action undo" title="Restablecer">
                        <i class="fas fa-history"></i>
                        <span class="undo-text">Deshacer</span>
                    </button>
                </div>
                <div v-else class="action-buttons">
                    <i class="fas fa-lock muted-icon" title="Historial bloqueado"></i>
                </div>
            </td>
          </tr>
          <tr v-if="reservasPaginadas.length === 0">
            <td colspan="8" class="no-results">No hay reservas que coincidan con los filtros.</td>
          </tr>
        </tbody>
      </table>

      <div class="pagination" v-if="totalPages > 1">
        <button :disabled="currentPage === 1" @click="currentPage--" class="btn-page">
          <i class="fas fa-chevron-left"></i> Anterior
        </button>
        <span class="page-info">Página {{ currentPage }} de {{ totalPages }}</span>
        <button :disabled="currentPage === totalPages" @click="currentPage++" class="btn-page">
          Siguiente <i class="fas fa-chevron-right"></i>
        </button>
      </div>
    </div>
  </div>

  <div v-if="mostrarModalPago" class="modal-overlay" @click.self="mostrarModalPago = false">
    <div class="modal-content payment-modal-modern">
      <div class="modal-header-pago">
        <h3>¿Cómo ha pagado el cliente?</h3>
        <p class="cliente-nombre-pago">{{ reservaParaCompletar?.cliente_nombre }}</p>
      </div>
      <div class="payment-grid">
        <button v-for="metodo in metodosPago" :key="metodo.id"
          @click="metodoPagoSeleccionado = metodo.id"
          :class="['payment-option-btn', { 'active': metodoPagoSeleccionado === metodo.id }]">
          <div class="icon-circle"><i :class="metodo.icono"></i></div>
          <span>{{ metodo.nombre }}</span>
        </button>
      </div>
      <div class="modal-actions-horizontal">
        <button @click="confirmarPagoYCompletar" class="btn-confirm-action btn-finalizar">
          Finalizar Cita <i class="fas fa-arrow-right"></i>
        </button>
        <button @click="mostrarModalPago = false" class="btn-cancel-modal">Cancelar</button>
      </div>
    </div>
  </div>

  <div v-if="mostrarModalCancel" class="modal-overlay" @click.self="mostrarModalCancel = false">
    <div class="modal-content">
      <h3>Anular Cita</h3>
      <p>Cliente: <strong>{{ reservaSeleccionada?.cliente_nombre }}</strong></p>
      <div class="form-group-alt">
        <label>Motivo de la anulación:</label>
        <textarea v-model="motivoCancelacion" placeholder="Ej: No puede asistir..."></textarea>
      </div>
      <div class="modal-actions">
        <button @click="confirmarAnulacion" class="btn-confirm">Confirmar Anulación</button>
        <button @click="mostrarModalCancel = false" class="btn-close">Cerrar</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, onUnmounted } from 'vue';

// --- ESTADO ---
const reservas = ref([]);
const search = ref('');
const statusFilter = ref('all');
const peluqueroFilter = ref('all');
const dateFilter = ref('');
const currentPage = ref(1);
const itemsPerPage = 8;
const cargandoRefresco = ref(false);
let timerRefresco = null;

// --- MODALES ---
const mostrarModalPago = ref(false);
const mostrarModalCancel = ref(false);
const reservaParaCompletar = ref(null);
const reservaSeleccionada = ref(null);
const metodoPagoSeleccionado = ref('efectivo');
const motivoCancelacion = ref('');

const metodosPago = [
  
  { id: 'efectivo', nombre: 'Efectivo', icono: 'fas fa-money-bill-wave' },
  { id: 'bizzum', nombre: 'Bizzum', icono: 'fas fa-mobile-alt' },
  { id: 'tarjeta', nombre: 'Tarjeta', icono: 'fas fa-credit-card' },
  { id: 'deuda', nombre: 'Deuda', icono: 'fas fa-user-clock' }
];

// --- FUNCIONES CORE ---
const cargarReservas = async () => {
  cargandoRefresco.value = true;
  try {
    const res = await fetch('/backend/api/gestion_reservas.php', { credentials: 'include' });
    reservas.value = await res.json();
  } catch (e) {
    console.error("Error:", e);
  } finally {
    setTimeout(() => { cargandoRefresco.value = false; }, 500);
  }
};

const getRowClass = (r) => {
  const claseBase = obtenerClaseFinal(r);
  return {
    'fila-proxima': claseBase === 'proximo' && r.estado !== 'COMPLETADA' && r.estado !== 'ANULADA LOCAL',
    'fila-completada': r.estado === 'COMPLETADA',
    'fila-cancelada': r.estado === 'ANULADA LOCAL',
    'fila-bloqueada': esFechaBloqueada(r.fecha)
  };
};

const obtenerClaseFinal = (reserva) => {
  if (reserva.estado === 'COMPLETADA') return 'completada';
  
  // CORRECCIÓN: Si el estado incluye "ANULADA" (ya sea LOCAL o WEB), marcar como cancelada
  if (reserva.estado && reserva.estado.includes('ANULADA')) return 'cancelada';

  const ahora = new Date();
  const fechaCita = new Date(`${reserva.fecha}T${reserva.hora}`);
  const diffTiempo = fechaCita - ahora;
  const diffDias = diffTiempo / (1000 * 60 * 60 * 24);

  if (diffTiempo < 0) return 'finalizada'; 
  if (diffDias <= 7) return 'proximo';
  return 'futuro';
};

const obtenerTextoFinal = (reserva) => {
  const etiquetas = {
    'completada': 'Completada',
    'cancelada': 'Anulada',
    'finalizada': 'Finalizada',
    'proximo': 'Próxima Cita',
    'futuro': 'Futuro'
  };
  return etiquetas[obtenerClaseFinal(reserva)];
};

const completarCita = (reserva) => {
  reservaParaCompletar.value = reserva;
  metodoPagoSeleccionado.value = 'efectivo';
  mostrarModalPago.value = true;
};

const confirmarPagoYCompletar = async () => {
  try {
    await fetch('/backend/api/gestion_reservas.php', {
      method: 'POST',
      credentials: 'include',
      body: JSON.stringify({ 
        id: reservaParaCompletar.value.id, 
        estado: 'COMPLETADA',
        metodo_pago: metodoPagoSeleccionado.value 
      })
    });
    reservaParaCompletar.value.estado = 'COMPLETADA';
    reservaParaCompletar.value.metodo_pago = metodoPagoSeleccionado.value;
    mostrarModalPago.value = false;
  } catch (e) { console.error(e); }
};

const prepararAnulacion = (reserva) => {
  reservaSeleccionada.value = reserva;
  motivoCancelacion.value = '';
  mostrarModalCancel.value = true;
};

const confirmarAnulacion = async () => {
  if (!motivoCancelacion.value.trim()) return alert("Indica un motivo");
  try {
    await fetch('/backend/api/gestion_reservas.php', {
      method: 'POST',
      credentials: 'include',
      body: JSON.stringify({ 
        id: reservaSeleccionada.value.id, 
        estado: 'ANULADA LOCAL', 
        motivo: motivoCancelacion.value 
      })
    });
    reservaSeleccionada.value.estado = 'ANULADA LOCAL';
    mostrarModalCancel.value = false;
  } catch (e) { console.error(e); }
};

const revertirEstado = async (reserva) => {
  if (confirm(`¿Restablecer cita de ${reserva.cliente_nombre}?`)) {
    try {
      await fetch('/backend/api/gestion_reservas.php', {
        method: 'POST',
        credentials: 'include',
        body: JSON.stringify({ id: reserva.id, estado: 'PENDIENTE', motivo: null, metodo_pago: null })
      });
      reserva.estado = 'PENDIENTE';
      reserva.metodo_pago = null;
    } catch (e) { console.error(e); }
  }
};

// --- COMPUTED FILTROS ---
const listaPeluqueros = computed(() => {
  const nombres = reservas.value.map(r => r.peluquero_nombre).filter(n => n);
  return [...new Set(nombres)];
});

const reservasFiltradas = computed(() => {
  return reservas.value.filter(r => {
    const matchesSearch = r.cliente_nombre.toLowerCase().includes(search.value.toLowerCase()) ||
                          r.servicio_nombre.toLowerCase().includes(search.value.toLowerCase());
    
    // CORRECCIÓN AQUÍ:
    let matchesStatus = true;
    if (statusFilter.value === 'ANULADA LOCAL') {
        // Si filtramos por canceladas, mostramos LOCAL y WEB
        matchesStatus = r.estado.includes('ANULADA');
    } else if (statusFilter.value !== 'all') {
        matchesStatus = r.estado === statusFilter.value;
    }

    const matchesDate = !dateFilter.value || r.fecha === dateFilter.value;
    const matchesPeluquero = peluqueroFilter.value === 'all' || r.peluquero_nombre === peluqueroFilter.value;
    
    return matchesSearch && matchesStatus && matchesDate && matchesPeluquero;
  });
});

const totalPages = computed(() => Math.ceil(reservasFiltradas.value.length / itemsPerPage));
const reservasPaginadas = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return reservasFiltradas.value.slice(start, start + itemsPerPage);
});

// --- UTILIDADES ---
const esFechaBloqueada = (fecha) => {
  const limite = new Date();
  limite.setDate(limite.getDate() - 2);
  return new Date(fecha) < limite;
};

const formatearFecha = (f) => new Date(f).toLocaleDateString('es-ES', { day: '2-digit', month: 'short' });
const obtenerIconoPago = (m) => ({ efectivo: 'fas fa-money-bill-wave', bizzum: 'fas fa-mobile-alt', tarjeta: 'fas fa-credit-card', deuda: 'fas fa-user-clock' }[m]);
const refrescarManual = () => {
  if (cargandoRefresco.value) return; // Evita múltiples clics
  cargarReservas();
};

onMounted(() => {
  cargarReservas();
  timerRefresco = setInterval(() => { if (document.visibilityState === 'visible') cargarReservas(); }, 180000);
});
onUnmounted(() => { if (timerRefresco) clearInterval(timerRefresco); });

</script>

<style scoped>
/* ETIQUETAS */
.tags-container-table { display: flex; flex-wrap: wrap; gap: 4px; max-width: 140px; }
.tag-mini-agenda { font-size: 0.65rem; padding: 2px 8px; border-radius: 10px; color: white; font-weight: 800; text-transform: uppercase; white-space: nowrap; }
.no-tags-muted { color: #cbd5e0; font-size: 0.8rem; }

/* HEADER & FILTROS */
.section-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; border-bottom: 1px solid #f0f0f0; padding-bottom: 10px; }
.header-left .breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 0.8rem; color: #999; text-transform: uppercase; }
.breadcrumb .current { color: #e75480; font-weight: 600; }
.title-container h1 { margin: 0; font-size: 1.8rem; font-weight: 800; color: #2c3e50; }
.badge-count { background: #fdf2f5; color: #e75480; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; border: 1px solid #f9dbe5; }

.header-actions { display: flex; gap: 15px; align-items: center; }
.filters { display: flex; gap: 10px; }
.status-select { padding: 8px; border-radius: 6px; border: 1px solid #ddd; background: white; font-size: 0.85rem; }
.search-box { position: relative; }
.search-box input { padding: 8px 10px 8px 30px; border: 1px solid #ddd; border-radius: 6px; width: 200px; }
.search-box i { position: absolute; left: 10px; top: 10px; color: #999; }

/* TABLA */
.table-card { background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; }
.custom-table { width: 100%; border-collapse: collapse; }
.custom-table th { background: #f8f9fa; padding: 15px; text-align: left; color: #888; font-size: 0.75rem; text-transform: uppercase; }
.custom-table td { padding: 12px; border-bottom: 1px solid #f2f2f2; vertical-align: middle; font-size: 0.85rem; }

/* FILAS ESPECIALES */
.fila-proxima { border-left: 4px solid #d69e2e; background-color: #fffdf5; }
.fila-completada { border-left: 4px solid #2ecc71; }
.fila-cancelada { border-left: 4px solid #e74c3c; opacity: 0.8; }
.fila-bloqueada { background-color: #f8fafc; opacity: 0.6; }

/* ELEMENTOS TABLA */
.user-info { display: flex; align-items: center; gap: 10px; }
.avatar-mini { width: 30px; height: 30px; background: #e75480; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; }
.service-tag-display { display: flex; align-items: center; gap: 8px; background: #f1f5f9; padding: 4px 10px; border-radius: 12px; width: fit-content; font-size: 0.75rem; }
.table-icon { width: 16px; height: 16px; }
.status-badge { padding: 4px 10px; border-radius: 6px; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; }
.status-badge.completada { background: #e6fffa; color: #23a35a; }
.status-badge.cancelada { background: #fff5f5; color: #e74c3c; }
.status-badge.proximo { background: #fffaf0; color: #d69e2e; }

/* ACCIONES */
.action-buttons { display: flex; gap: 10px; align-items: center; }
.btn-action { background: none; border: none; font-size: 1.2rem; cursor: pointer; transition: 0.2s; }
.btn-action.check { color: #2ecc71; }
.btn-action.cancel { color: #e74c3c; }
.btn-action.undo { font-size: 0.9rem; color: #3498db; background: #f0f7ff; padding: 4px 8px; border-radius: 6px; }

/* MODALES */
.modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.modal-content { background: white; padding: 30px; border-radius: 16px; width: 90%; max-width: 450px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); }
.payment-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin: 20px 0; }
.payment-option-btn { padding: 15px; border: 2px solid #f1f5f9; border-radius: 12px; background: #f8fafc; cursor: pointer; transition: 0.3s; display: flex; flex-direction: column; align-items: center; gap: 10px; }
.payment-option-btn.active { border-color: #e75480; background: #fdf2f5; }
.payment-option-btn i { font-size: 1.5rem; color: #64748b; }
.payment-option-btn.active i { color: #e75480; }

.btn-confirm-action { width: 100%; background: #2ecc71; color: white; border: none; padding: 14px; border-radius: 10px; font-weight: 700; cursor: pointer; }
.btn-cancel-modal { width: 100%; background: #f1f5f9; color: #64748b; border: none; padding: 12px; border-radius: 10px; margin-top: 10px; cursor: pointer; }
/* --- CONTENEDORES BASE --- */
.servicios-view { padding: 20px; font-family: 'Inter', sans-serif; }

/* --- ETIQUETAS DE CLIENTE (COLUMNA NUEVA) --- */
.tags-container-table { display: flex; flex-wrap: wrap; gap: 4px; max-width: 140px; }
.tag-mini-agenda { 
  font-size: 0.65rem; 
  padding: 2px 8px; 
  border-radius: 10px; 
  color: white; 
  font-weight: 800; 
  text-transform: uppercase; 
  white-space: nowrap; 
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.no-tags-muted { color: #cbd5e0; font-size: 0.8rem; font-style: italic; }

/* --- HEADER Y FILTROS --- */
.section-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; border-bottom: 1px solid #f0f0f0; padding-bottom: 15px; }
.header-left .breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 0.8rem; color: #999; text-transform: uppercase; margin-bottom: 5px; }
.breadcrumb .current { color: #e75480; font-weight: 600; }
.title-container h1 { margin: 0; font-size: 1.8rem; font-weight: 800; color: #2c3e50; }
.badge-count { background: #fdf2f5; color: #e75480; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; border: 1px solid #f9dbe5; margin-left: 10px; vertical-align: middle; }

.header-actions { display: flex; gap: 15px; align-items: center; }
.filters { display: flex; gap: 10px; align-items: center; }
.status-select { padding: 10px; border-radius: 8px; border: 1px solid #e2e8f0; background: white; font-size: 0.85rem; color: #4a5568; outline: none; }
.search-box { position: relative; }
.search-box input { padding: 10px 10px 10px 35px; border: 1px solid #e2e8f0; border-radius: 8px; width: 220px; transition: 0.3s; }
.search-box input:focus { border-color: #e75480; box-shadow: 0 0 0 3px rgba(231, 84, 128, 0.1); }
.search-box i { position: absolute; left: 12px; top: 12px; color: #a0aec0; }

/* --- TABLA --- */
.table-card { background: white; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); overflow: hidden; border: 1px solid #edf2f7; }
.custom-table { width: 100%; border-collapse: collapse; }
.custom-table th { background: #f8fafc; padding: 18px 15px; text-align: left; color: #718096; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 2px solid #edf2f7; }
.custom-table td { padding: 15px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; font-size: 0.9rem; color: #2d3748; }

/* --- FILAS SEGÚN ESTADO --- */
.fila-proxima { background-color: #fffaf0; border-left: 4px solid #ed8936 !important; }
.fila-completada { border-left: 4px solid #48bb78 !important; }
.fila-cancelada { border-left: 4px solid #f56565 !important; opacity: 0.7; }
.fila-bloqueada { background-color: #f7fafc; opacity: 0.6; pointer-events: none; }

/* --- MODALES (ESTILO UNIFICADO) --- */
.modal-overlay { 
  position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
  background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(4px);
  display: flex; align-items: center; justify-content: center; z-index: 2000; 
}

.modal-content { 
  background: white; padding: 30px; border-radius: 20px; width: 90%; max-width: 450px; 
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); position: relative;
}

/* Modal Cancelar Específico */
.modal-content h3 { color: #1e293b; font-size: 1.4rem; margin-bottom: 10px; font-weight: 800; }
.form-group-alt { margin-top: 20px; text-align: left; }
.form-group-alt label { display: block; margin-bottom: 8px; font-weight: 600; color: #64748b; font-size: 0.9rem; }
.form-group-alt textarea { 
  width: 100%; min-height: 100px; padding: 12px; border-radius: 12px; border: 1px solid #e2e8f0;
  background: #f8fafc; font-family: inherit; resize: none; outline: none; transition: 0.3s;
}
.form-group-alt textarea:focus { border-color: #f56565; box-shadow: 0 0 0 3px rgba(245, 101, 101, 0.1); }

/* Modal Pago Específico */
.modal-header-pago { text-align: center; margin-bottom: 25px; }
.cliente-nombre-pago { font-size: 1.1rem; color: #e75480; font-weight: 700; margin: 5px 0; }
.payment-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin: 20px 0; }

.payment-option-btn { 
  padding: 20px 10px; border: 2px solid #f1f5f9; border-radius: 16px; 
  background: #fff; cursor: pointer; transition: 0.3s ease;
  display: flex; flex-direction: column; align-items: center; gap: 12px;
}
.payment-option-btn:hover { border-color: #cbd5e1; background: #f8fafc; }
.payment-option-btn.active { border-color: #e75480; background: #fff1f2; transform: translateY(-3px); }
/* --- ICONOS DENTRO DEL MODAL DE PAGO --- */
.payment-option-btn .icon-circle { 
  width: 50px; 
  height: 50px; 
  border-radius: 50%; 
  background: #f1f5f9; /* Fondo gris suave por defecto */
  display: flex; 
  align-items: center; 
  justify-content: center; 
  transition: all 0.3s ease;
}

/* El icono "i" por defecto es gris */
.payment-option-btn .icon-circle i {
  font-size: 1.4rem;
  color: #64748b; 
}

/* CUANDO EL BOTÓN ESTÁ ACTIVO (SELECCIONADO) */
.payment-option-btn.active .icon-circle { 
  background: #e75480; /* Fondo rosa */
  box-shadow: 0 4px 10px rgba(231, 84, 128, 0.3);
}

/* FORZAMOS EL ICONO A SER BLANCO CUANDO ESTÁ ACTIVO */
.payment-option-btn.active .icon-circle i { 
  color: white !important; 
  transform: scale(1.1); /* Un pequeño efecto de aumento */
}

/* Texto debajo del icono */
.payment-option-btn span {
  font-weight: 700;
  font-size: 0.9rem;
  color: #64748b;
}

.payment-option-btn.active span {
  color: #e75480;
}

/* BOTONES DE ACCIÓN EN MODAL */
.modal-actions, .modal-actions-horizontal { margin-top: 25px; display: flex; flex-direction: column; gap: 10px; }

.btn-confirm { 
  background: #f56565; color: white; padding: 14px; border: none; border-radius: 12px; 
  font-weight: 700; cursor: pointer; transition: 0.3s; 
}
.btn-confirm:hover { background: #e53e3e; transform: translateY(-2px); }

.btn-finalizar { 
  background: #48bb78; color: white; padding: 14px; border: none; border-radius: 12px; 
  font-weight: 700; cursor: pointer; transition: 0.3s; display: flex; align-items: center; justify-content: center; gap: 10px;
}
.btn-finalizar:hover { background: #38a169; transform: translateY(-2px); }

.btn-close, .btn-cancel-modal { 
  background: #f1f5f9; color: #64748b; padding: 12px; border: none; border-radius: 12px; 
  font-weight: 600; cursor: pointer; transition: 0.2s;
}
.btn-close:hover, .btn-cancel-modal:hover { background: #e2e8f0; color: #1e293b; }

/* PAGINACIÓN */
.pagination { padding: 20px; display: flex; justify-content: center; align-items: center; gap: 20px; background: #f8fafc; }
.btn-page { padding: 8px 16px; border-radius: 8px; border: 1px solid #e2e8f0; background: white; cursor: pointer; font-size: 0.85rem; font-weight: 600; transition: 0.2s; }
.btn-page:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-page:not(:disabled):hover { border-color: #e75480; color: #e75480; }
/* Animación del botón refrescar */
.btn-refresh {
  background: white;
  border: 1px solid #e2e8f0;
  color: #64748b;
  width: 40px;
  height: 40px;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-refresh:hover {
  background: #f8fafc;
  color: #e75480;
  border-color: #e75480;
}

.btn-refresh.spinning i {
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
/* Contenedor que alinea badge y pago */
.status-payment-container {
  display: flex;
  flex-direction: column; /* Uno debajo del otro */
  align-items: center;
  gap: 5px;
}

/* Fila pequeña para el icono + texto del pago */
.payment-info-row {
  display: flex;
  align-items: center;
  gap: 4px;
  background: #f1f5f9; /* Un gris muy suave de fondo */
  padding: 2px 8px;
  border-radius: 4px;
  color: #64748b; /* Color de texto suave */
}

.payment-icon-small {
  font-size: 0.75rem;
  color: #2ecc71; /* Verde para indicar que está pagado */
}

.payment-text-small {
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: capitalize; /* "efectivo" -> "Efectivo" */
}
</style>