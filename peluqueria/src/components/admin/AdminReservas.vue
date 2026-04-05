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
          <span class="badge-count">{{ reservasFiltradas.length }} citas</span>
          <button @click="$emit('cambiar-seccion', 'agendar')" class="btn-goto-agendar">
            <i class="fas fa-calendar-plus"></i> Nueva Reserva
          </button>
        </div>
      </div>

      <div class="header-actions">
        <button @click="cargarReservas" class="btn-refresh" :class="{ 'spinning': cargandoRefresco }">
          <i class="fas fa-sync-alt"></i>
        </button>
        <div class="filters">
          <div class="date-filter-box">
            <input type="date" v-model="dateFilter" class="status-select">
            <button v-if="dateFilter" @click="dateFilter = ''" class="btn-clear-date">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <select v-model="peluqueroFilter" class="status-select">
            <option value="all">Todos los peluqueros</option>
            <option v-for="p in listaPeluqueros" :key="p" :value="p">{{ p }}</option>
          </select>
          <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" v-model="search" placeholder="Buscar cliente...">
          </div>
          <select v-model="statusFilter" class="status-select">
            <option value="all">Todos los estados</option>
            <option value="PENDIENTE">Pendientes</option>            
            <option value="COMPLETADA">Completadas</option>
            <option value="ANULADA">Canceladas</option>
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
            <th>Perfil</th>
            <th>Peluquero</th>
            <th>Servicio</th>
            <th>Precio</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="r in reservasPaginadas" :key="r.id" :class="getRowClass(r)">
            <td>
              <div class="date-display">
                <span class="font-bold">{{ formatearFecha(r.fecha) }}</span>
                <span class="text-muted"><i class="far fa-clock"></i> {{ r.hora.substring(0,5) }}</span>
              </div>
            </td>
            <td>
              <div class="user-info">
                <div class="avatar-mini">{{ r.cliente_nombre.charAt(0) }}</div>
                <div>
                  <div class="font-bold">{{ r.cliente_nombre }}</div>
                  <div class="text-muted small">{{ r.cliente_telefono }}</div>
                </div>
              </div>
            </td>
            <td>
              <div class="tags-container-table">
                <span v-for="tag in r.etiquetas" :key="tag.nombre" 
                      class="tag-mini-agenda" :style="{ backgroundColor: tag.color }">
                  {{ tag.nombre }}
                </span>
                <span v-if="!r.etiquetas?.length" class="no-tags-muted">-</span>
              </div>
            </td>
            <td>
              <div class="peluquero-info">
                <i class="fas fa-cut"></i> <span>{{ r.peluquero_nombre || 'Sin asignar' }}</span>
              </div>
            </td>
            <td>
              <div class="service-tag-display">
                <img :src="`/img-icons/${r.servicio_icono}`" class="table-icon" />
                <span>{{ r.servicio_nombre }}</span>
              </div>
            </td>
            <td><span class="price-text">{{ r.precio }}€</span></td>
            <td>
              <div class="status-payment-container">
                <span :class="['status-badge', obtenerInfoEstado(r).clase]">
                  {{ obtenerInfoEstado(r).texto }}
                </span>
                <div v-if="r.metodo_pago" class="payment-info-row">
                  <i :class="[obtenerIconoPago(r.metodo_pago), 'payment-icon-small']"></i>
                  <span class="payment-text-small">{{ r.metodo_pago }}</span>
                </div>
              </div>
            </td>
            <td class="actions">
              <div v-if="!esFechaBloqueada(r.fecha)" class="action-buttons">
                <template v-if="r.estado !== 'COMPLETADA' && !r.estado.includes('ANULADA')">
                  <button @click="abrirPago(r)" class="btn-action check"><i class="fas fa-check-circle"></i></button>
                  <button @click="abrirCancelacion(r)" class="btn-action cancel"><i class="fas fa-times-circle"></i></button>
                </template>
                <button v-else @click="revertirEstado(r)" class="btn-action undo">
                  <i class="fas fa-history"></i> <span class="undo-text">Deshacer</span>
                </button>
              </div>
              <i v-else class="fas fa-lock muted-icon"></i>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="pagination" v-if="totalPages > 1">
        <button :disabled="currentPage === 1" @click="currentPage--" class="btn-page">Anterior</button>
        <span class="page-info">Página {{ currentPage }} de {{ totalPages }}</span>
        <button :disabled="currentPage === totalPages" @click="currentPage++" class="btn-page">Siguiente</button>
      </div>
    </div>

    <!-- Modal Pago -->    
    <div v-if="mostrarModalPago" class="modal-overlay" @click.self="mostrarModalPago = false">
      <div class="modal-content payment-modal-modern">
        <div class="modal-header-pago">
          <h3>Finalizar Cita</h3>
          <p class="cliente-nombre-pago">{{ reservaParaCompletar?.cliente_nombre }}</p>
        </div>
        
        <!-- Sección Métodos de Pago -->
        <div class="payment-grid">
          <button v-for="m in metodosPago" :key="m.id" 
                  @click="metodoPagoSeleccionado = m.id" 
                  :class="['payment-option-btn', { active: metodoPagoSeleccionado === m.id }]">
            <div class="icon-circle"><i :class="m.icono"></i></div>
            <span>{{ m.nombre }}</span>
          </button>
        </div>

        <!-- SECCIÓN DE FIDELIZACIÓN (NUEVO) -->         
      <div class="fidelizacion-section" v-if="promocionesDisponibles.length > 0">
        <h4 class="section-subtitle">Promociones Disponibles</h4>
        <div class="promos-list">
          <!-- Usamos promo.promocion_id o promo.id por seguridad -->
          <div v-for="promo in promocionesDisponibles" 
              :key="promo.promocion_id || promo.id" 
              class="promo-card-mini"
              :class="{ 'selected': promocionSeleccionada === (promo.promocion_id || promo.id) }"
              @click="togglePromocion(promo.promocion_id || promo.id)">
            
            <div class="promo-content">
              <div class="promo-icon-box">
                <i :class="promo.tipo === 'VISITAS' ? 'fas fa-ticket-alt' : 'fas fa-gift'"></i>
              </div>

              <div class="promo-info">
                <span class="promo-title">{{ promo.nombre || 'Promoción Especial' }}</span>
                
                <div v-if="promo.tipo === 'VISITAS'" class="sellos-wrapper">
                  <div class="sellos-row">
                    <i v-for="n in (parseInt(promo.requeridos) || 5)" :key="n" 
                      class="fas fa-star sello-item"
                      :class="{ 'active': n <= (parseInt(promo.actuales) || 0) }">
                    </i>
                    <i class="fas fa-plus-circle sello-plus"></i>
                  </div>
                  <span class="sello-counter">{{ promo.actuales || 0 }} / {{ promo.requeridos || 5 }} sellos</span>
                </div>
                
                <span v-else class="promo-description">Beneficio Directo / VIP</span>
              </div>

              <div class="promo-check">
                <i :class="promocionSeleccionada === (promo.promocion_id || promo.id) ? 'fas fa-check-circle' : 'far fa-circle'"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

        <div class="modal-actions-horizontal">
          <button @click="confirmarPago" class="btn-finalizar">
            Finalizar y Aplicar <i class="fas fa-arrow-right"></i>
          </button>
          <button @click="mostrarModalPago = false" class="btn-cancel-modal">Cancelar</button>
        </div>
      </div>
    </div>

    <!-- Modal Cancelar -->
    <div v-if="mostrarModalCancel" class="modal-overlay" @click.self="mostrarModalCancel = false">
      <div class="modal-content">
        <h3>Anular Cita</h3>
        <p>Cliente: <strong>{{ reservaSeleccionada?.cliente_nombre }}</strong></p>
        <div class="form-group-alt">
          <label>Motivo:</label>
          <textarea v-model="motivoCancelacion" placeholder="Ej: No puede asistir..."></textarea>
        </div>
        <div class="modal-actions">
          <button @click="confirmarAnulacion" class="btn-confirm">Confirmar Anulación</button>
          <button @click="mostrarModalCancel = false" class="btn-close">Cerrar</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, onUnmounted } from 'vue';
import moment from 'moment';

const hoy = moment().format('YYYY-MM-DD');
const reservas = ref([]);
const search = ref('');
const statusFilter = ref('all');
const peluqueroFilter = ref('all');
const dateFilter = ref(hoy);
const currentPage = ref(1);
const itemsPerPage = 8;
const cargandoRefresco = ref(false);
let timerRefresco = null;
const promocionesDisponibles = ref([]);
const promocionSeleccionada = ref(null);
const promocionSeleccionadaId = ref(null);

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

const cargarReservas = async () => {
  cargandoRefresco.value = true;
  try {
    const res = await fetch('/backend/api/gestion_reservas.php', { credentials: 'include' });
    reservas.value = await res.json();
  } catch (e) { console.error(e); }
  finally { setTimeout(() => cargandoRefresco.value = false, 500); }
};

const obtenerInfoEstado = (r) => {
  if (r.estado === 'COMPLETADA') return { clase: 'completada', texto: 'Completada' };
  if (r.estado.includes('ANULADA')) return { clase: 'cancelada', texto: 'Anulada' };
  const ahora = new Date();
  const fechaCita = new Date(`${r.fecha}T${r.hora}`);
  if (fechaCita < ahora) return { clase: 'finalizada', texto: 'Finalizada' };
  return (fechaCita - ahora) / 86400000 <= 7 ? { clase: 'proximo', texto: 'Próxima' } : { clase: 'futuro', texto: 'Futuro' };
};

const getRowClass = (r) => {
  const { clase } = obtenerInfoEstado(r);
  return {
    'fila-proxima': clase === 'proximo' && r.estado === 'PENDIENTE',
    'fila-completada': r.estado === 'COMPLETADA',
    'fila-cancelada': r.estado.includes('ANULADA'),
    'fila-bloqueada': esFechaBloqueada(r.fecha)
  };
};

const abrirPago = async (r) => { 
  reservaParaCompletar.value = r; 
  metodoPagoSeleccionado.value = 'efectivo'; 
  promocionSeleccionada.value = null; // Resetear siempre al abrir
  promocionesDisponibles.value = []; 
  
  mostrarModalPago.value = true; 

  try {
    const res = await fetch(`/backend/api/get_cupones_pago.php?user_id=${r.user_id}`);
    const data = await res.json();
    
    if (data.promociones) {
      promocionesDisponibles.value = data.promociones.map(p => ({
        ...p,
        // Aseguramos que promocion_id exista, si no, usamos el id normal
        promocion_id: p.promocion_id || p.id, 
        requeridos: parseInt(p.requeridos) || 0,
        actuales: parseInt(p.actuales) || 0
      }));
    }
  } catch (e) {
    console.error("Error cargando promociones:", e);
  }
};

const abrirCancelacion = (r) => { reservaSeleccionada.value = r; motivoCancelacion.value = ''; mostrarModalCancel.value = true; };

// Dentro de <script setup>

const confirmarPago = async () => {
  // 1. Buscamos el objeto de la promoción dentro del array usando el ID seleccionado
  // Nota: Usamos .value porque promocionSeleccionadaId es un ref
  
  const promoSeleccionada = promocionesDisponibles.value.find(
    p => p.id === promocionSeleccionada.value
  );

  const datosEnvio = { 
    id: reservaParaCompletar.value.id, 
    estado: 'COMPLETADA', 
    metodo_pago: metodoPagoSeleccionado.value,
    user_id: reservaParaCompletar.value.user_id,
    // Aquí usamos directamente el ID seleccionado
    promocion_id: promocionSeleccionada.value, 
    // Y aquí sacamos el tipo del objeto que encontramos arriba
    tipo_promo: promoSeleccionada ? promoSeleccionada.tipo : null
  };

  try {
    const res = await fetch('/backend/api/gestion_reservas.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' }, // Buena práctica añadir el header
      body: JSON.stringify(datosEnvio)
    });
    
    const result = await res.json();
    
    if (result.status === 'success') {
      // Actualización local de la interfaz
      reservaParaCompletar.value.estado = 'COMPLETADA';
      reservaParaCompletar.value.metodo_pago = metodoPagoSeleccionado.value;
      mostrarModalPago.value = false;
      
      // Opcional: Recargar la lista de reservas o mostrar mensaje de éxito
    } else {
      throw new Error(result.message || "Error en el servidor");
    }
  } catch (e) {
    console.error("Error al finalizar:", e);
    alert("Error al guardar los datos: " + e.message);
  }
};

const togglePromocion = (id) => {
  // Si el ID que clickamos ya es el seleccionado, lo desmarcamos (null)
  // Si es uno nuevo, marcamos ese ID
  if (promocionSeleccionada.value === id) {
    promocionSeleccionada.value = null;
  } else {
    promocionSeleccionada.value = id;
  }
};

const confirmarAnulacion = async () => {
  if (!motivoCancelacion.value.trim()) return alert("Motivo requerido");
  await enviarEstado({ id: reservaSeleccionada.value.id, estado: 'ANULADA LOCAL', motivo: motivoCancelacion.value });
  reservaSeleccionada.value.estado = 'ANULADA LOCAL';
  mostrarModalCancel.value = false;
};

const revertirEstado = async (r) => {
  if (confirm(`¿Restablecer cita? ¿Deseas también devolver el cupón/sello si se aplicó?`)) {
    await enviarEstado({ 
      id: r.id, 
      estado: 'PENDIENTE', 
      motivo: null, 
      metodo_pago: null,
      user_id: r.user_id,      // Enviamos el usuario
      revertir_cupon: true,     // <-- Nueva bandera para el backend
      promocion_id: r.promocion_id || promocionSeleccionada.value
    });
    
    // Actualización local de la interfaz
    r.estado = 'PENDIENTE';
    r.metodo_pago = null;
  }
};

const enviarEstado = async (data) => {
  try {
    await fetch('/backend/api/gestion_reservas.php', {
      method: 'POST',
      credentials: 'include',
      body: JSON.stringify(data)
    });
  } catch (e) { console.error(e); }
};

const listaPeluqueros = computed(() => [...new Set(reservas.value.map(r => r.peluquero_nombre).filter(n => n))]);

const reservasFiltradas = computed(() => {
  return reservas.value.filter(r => {
    const matchesSearch = r.cliente_nombre.toLowerCase().includes(search.value.toLowerCase()) || r.servicio_nombre.toLowerCase().includes(search.value.toLowerCase());
    const matchesStatus = statusFilter.value === 'all' || (statusFilter.value === 'ANULADA' ? r.estado.includes('ANULADA') : r.estado === statusFilter.value);
    const matchesDate = !dateFilter.value || r.fecha === dateFilter.value;
    const matchesPeluquero = peluqueroFilter.value === 'all' || r.peluquero_nombre === peluqueroFilter.value;
    return matchesSearch && matchesStatus && matchesDate && matchesPeluquero;
  });
});

const totalPages = computed(() => Math.ceil(reservasFiltradas.value.length / itemsPerPage));
const reservasPaginadas = computed(() => reservasFiltradas.value.slice((currentPage.value - 1) * itemsPerPage, currentPage.value * itemsPerPage));
const esFechaBloqueada = (f) => new Date(f) < new Date().setDate(new Date().getDate() - 2);
const formatearFecha = (f) => new Date(f).toLocaleDateString('es-ES', { day: '2-digit', month: 'short' });
const obtenerIconoPago = (m) => metodosPago.find(p => p.id === m)?.icono || 'fas fa-money-bill';

onMounted(() => {
  cargarReservas();
  timerRefresco = setInterval(() => { if (document.visibilityState === 'visible') cargarReservas(); }, 180000);
});
onUnmounted(() => clearInterval(timerRefresco));
</script>
<style scoped>
.servicios-view { padding: 20px; font-family: 'Inter', sans-serif; background: #fbfcfd; }

/* HEADER OPTIMIZADO */
.section-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px; border-bottom: 1px solid #f0f0f0; padding-bottom: 15px; }
.breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 0.75rem; color: #999; text-transform: uppercase; margin-bottom: 5px; }
.breadcrumb .current { color: #e75480; font-weight: 600; }
.title-container { display: flex; align-items: center; gap: 12px; margin-top: 5px; }
.title-container h1 { margin: 0; font-size: 1.6rem; font-weight: 800; color: #1e293b; }
.badge-count { background: #fff1f2; color: #e75480; padding: 4px 10px; border-radius: 20px; font-size: 0.7rem; border: 1px solid #fecdd3; }

.header-actions { display: flex; gap: 12px; align-items: center; }
.filters { display: flex; gap: 8px; align-items: center; }
.status-select, .search-box input { padding: 8px 12px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 0.8rem; outline: none; transition: 0.2s; }
.search-box { position: relative; }
.search-box i { position: absolute; left: 10px; top: 10px; color: #a0aec0; font-size: 0.8rem; }
.search-box input { padding-left: 30px; width: 180px; }

/* TABLA REDUCIDA */
.table-card { background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); overflow: hidden; border: 1px solid #edf2f7; }
.custom-table { width: 100%; border-collapse: collapse; table-layout: auto; }
.custom-table th { background: #f8fafc; padding: 12px 15px; text-align: left; color: #64748b; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 2px solid #edf2f7; }
.custom-table td { padding: 10px 15px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; font-size: 0.85rem; color: #334155; }

/* FILAS */
.fila-proxima { background-color: #fffdfa; border-left: 3px solid #f59e0b !important; }
.fila-completada { border-left: 3px solid #10b981 !important; }
.fila-cancelada { border-left: 3px solid #ef4444 !important; opacity: 0.7; }
.fila-bloqueada { background-color: #f8fafc; opacity: 0.6; pointer-events: none; }

/* ELEMENTOS INTERNOS */
.user-info { display: flex; align-items: center; gap: 8px; }
.avatar-mini { width: 28px; height: 28px; background: #e75480; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.75rem; }
.font-bold { font-weight: 700; color: #1e293b; }
.text-muted { color: #94a3b8; font-size: 0.75rem; }
.service-tag-display { display: flex; align-items: center; gap: 6px;  padding: 3px 8px; border-radius: 8px; font-size: 0.75rem; font-weight: 600; }
.table-icon { width: 14px; height: 14px; }
.status-badge { padding: 3px 8px; border-radius: 6px; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; }

/* ESTADOS */
.status-badge.completada { background: #dcfce7; color: #166534; }
.status-badge.cancelada { background: #fee2e2; color: #991b1b; }
.status-badge.proximo { background: #fef3c7; color: #92400e; }
.status-badge.finalizada { background: #f1f5f9; color: #475569; }

/* ACCIONES */
.btn-action { background: none; border: none; cursor: pointer; font-size: 1.1rem; transition: 0.2s; padding: 4px; }
.btn-action.check { color: #10b981; }
.btn-action.cancel { color: #ef4444; }
.btn-action.undo { font-size: 0.7rem; color: #3b82f6; background: #eff6ff; padding: 4px 8px; border-radius: 4px; font-weight: 700; }



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



.btn-goto-agendar { background: #e75480; color: white; border: none; padding: 8px 14px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 6px; }
.btn-refresh.spinning i { animation: spin 1s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

.pagination { padding: 15px; display: flex; justify-content: center; align-items: center; gap: 15px; background: #f8fafc; border-top: 1px solid #edf2f7; }
.btn-page { padding: 6px 12px; border-radius: 6px; border: 1px solid #e2e8f0; background: white; font-size: 0.75rem; font-weight: 600; cursor: pointer; }
.btn-page:disabled { opacity: 0.5; }

/* FILAS ESPECIALES */
.fila-proxima { border-left: 4px solid #d69e2e; background-color: #fffdf5; }
.fila-completada { border-left: 4px solid #2ecc71; }
.fila-cancelada { border-left: 4px solid #e74c3c; opacity: 0.8; }
.fila-bloqueada { background-color: #f8fafc; opacity: 0.6; }

/* ETIQUETAS */
.tags-container-table { display: flex; flex-wrap: wrap; gap: 4px; max-width: 140px; }
.tag-mini-agenda { font-size: 0.65rem; padding: 2px 8px; border-radius: 10px; color: white; font-weight: 800; text-transform: uppercase; white-space: nowrap; }
.no-tags-muted { color: #cbd5e0; font-size: 0.8rem; }

.date-display{
  display: grid;
}


/* --- ESTILOS DE FIDELIZACIÓN --- */
.fidelizacion-section {
  margin-top: 15px;
  padding-top: 15px;
  border-top: 1px solid #f1f5f9;
}

.section-subtitle {
  font-size: 0.75rem;
  color: #94a3b8;
  font-weight: 700;
  text-transform: uppercase;
  margin-bottom: 12px;
  letter-spacing: 0.025em;
}

.promo-card-mini {
  background: #ffffff;
  border: 2px solid #f1f5f9;
  border-radius: 12px;
  margin-bottom: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.promo-card-mini:hover {
  border-color: #cbd5e1;
}

.promo-card-mini.selected {
  border-color: #e75480;
  background: #fff1f2;
}

.promo-content {
  display: flex;
  align-items: center;
  padding: 10px 12px;
  gap: 12px;
}

.promo-icon-box {
  width: 40px;
  height: 40px;
  background: #f8fafc;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #64748b;
  font-size: 1.1rem;
}

.selected .promo-icon-box {
  background: #e75480;
  color: white;
}

.promo-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  text-align: left;
}

.promo-title {
  font-size: 0.9rem;
  font-weight: 700;
  color: #1e293b;
}

.promo-description {
  font-size: 0.75rem;
  color: #64748b;
}

/* Sellos */
.sellos-row {
  display: flex;
  gap: 4px;
  margin: 3px 0;
}

.sello-item {
  font-size: 0.7rem;
  color: #e2e8f0;
}

.sello-item.active {
  color: #f59e0b;
}

.sello-plus {
  font-size: 0.75rem;
  color: #e75480;
  margin-left: 2px;
}

.sello-counter {
  font-size: 0.65rem;
  font-weight: 600;
  color: #94a3b8;
}

.promo-check {
  color: #cbd5e1;
  font-size: 1.2rem;
}

.selected .promo-check {
  color: #e75480;
}
</style>