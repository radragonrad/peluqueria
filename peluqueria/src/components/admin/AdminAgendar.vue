<template>
  <div class="servicios-view admin-agendar">
    <header class="section-header">
      <div class="header-left">
        <div class="breadcrumb">
          <span>Panel de Control</span> 
          <i class="fas fa-chevron-right"></i> 
          <span class="current">Nueva Cita Local</span>
        </div>
        <div class="title-container">
          <h1 class="main-title">Agendar Cita Presencial</h1>
        </div>
      </div>
    </header>

    <div class="booking-summary" v-if="pasoActual > 1">
      <div class="summary-item" @click="irAPaso(1)" :class="{ 'active-summary': pasoActual === 1, 'clickable': pasoActual > 1 }">
        <span class="summary-label">Cliente</span>
        <span class="summary-value">{{ clienteSeleccionado?.nombre || 'Seleccionar...' }}</span>
      </div>
      <div class="summary-item" @click="pasoActual > 2 && irAPaso(2)" :class="{ 'active-summary': pasoActual === 2, 'clickable': pasoActual > 2 }">
        <span class="summary-label">Servicio</span>
        <span class="summary-value">{{ servicioSeleccionado?.nombre || 'Esperando...' }}</span>
      </div>
      <div class="summary-item" @click="pasoActual > 3 && irAPaso(3)" :class="{ 'active-summary': pasoActual === 3, 'clickable': pasoActual > 3 }">
        <span class="summary-label">Peluquero</span>
        <span class="summary-value">{{ peluqueroSeleccionado?.nombre || 'Cualquiera' }}</span>
      </div>
    </div>

    <div class="agendar-container">
      
      <div class="step-card animate-fade" v-if="pasoActual === 1">
        <div class="step-header">
          <span class="step-number">1</span>
          <h3 class="step-title">Datos del Cliente</h3>
        </div>
        
        <div class="form-header-flex">
            <label class="input-label">Buscar Cliente Registrado</label>
            <button @click="mostrarModalNuevoCliente = true" class="btn-text-action">
                <i class="fas fa-user-plus"></i> ¿Nuevo Cliente?
            </button>
        </div>

        <div class="search-client-wrapper">
          <div class="search-box-full">
            <i class="fas fa-search"></i>
            <input 
              type="text" 
              v-model="busqueda" 
              placeholder="Escribe nombre o teléfono..."
              @input="buscarClientes"
            >
            <ul v-if="clientesFiltrados.length" class="results-list">
              <li v-for="c in clientesFiltrados" :key="c.id" @click="seleccionarCliente(c)">
                <div class="res-info">
                  <span class="res-name">{{ c.nombre }}</span>
                  <span class="res-phone"><i class="fas fa-phone-alt"></i> {{ c.telefono }}</span>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <div class="step-card animate-fade" v-if="pasoActual === 2">
        <div class="step-header">
          <button @click="irAPaso(1)" class="btn-back-step"><i class="fas fa-arrow-left"></i></button>
          <span class="step-number">2</span>
          <h3 class="step-title">Seleccionar Servicio</h3>
        </div>
        <div class="services-grid">
          <div 
            v-for="s in servicios" 
            :key="s.id" 
            class="service-item"
            :class="{ selected: reserva.servicio_id === s.id }"
            @click="seleccionarServicio(s)"
          >
            <img :src="`/img-icons/${s.icono}`" class="service-icon">
            <span class="service-name-text">{{ s.nombre }}</span>
            <span class="service-price-text">{{ s.precio }}€</span>
          </div>
        </div>
      </div>

<div class="step-card animate-fade" v-if="pasoActual === 3">
  <div class="step-header">
    <button @click="irAPaso(2)" class="btn-back-step"><i class="fas fa-arrow-left"></i></button>
    <span class="step-number">3</span>
    <h3 class="step-title">¿Quién realizará el servicio?</h3>
  </div>
  <div class="staff-grid">
    <div class="staff-card" :class="{ 'selected-staff': reserva.peluquero_id === null }" @click="seleccionarPeluquero({id: null, nombre: 'Cualquiera'})">
      <div class="staff-avatar-placeholder"><i class="fas fa-users"></i></div>
      <span class="staff-name">Cualquiera</span>
    </div>
    
    <div v-for="p in peluqueros" :key="p.id" 
         class="staff-card" 
         :class="{ 'selected-staff': reserva.peluquero_id === p.id }"
         @click="seleccionarPeluquero(p)">
      <img v-if="p.foto" :src="`/uploads/staff/${p.foto}`" class="staff-img">
      <div v-else class="staff-avatar-placeholder">{{ p.nombre.charAt(0) }}</div>
      <span class="staff-name">{{ p.nombre }}</span>
    </div>
  </div>
</div>

      <div class="step-card animate-fade" v-if="pasoActual === 4">
        <div class="step-header">
            <button @click="irAPaso(3)" class="btn-back-step"><i class="fas fa-arrow-left"></i></button>
            <span class="step-number">4</span>
            <h3 class="step-title">Fecha y hora</h3>
        </div>

        <div class="calendar-web-style">
            <div class="calendar-header">
                <button @click="cambiarMes(-1)" class="btn-nav">&lt;</button>
                <span class="month-name">{{ nombreMesActual }} {{ anioActual }}</span>
                <button @click="cambiarMes(1)" class="btn-nav">&gt;</button>
            </div>

            <div class="calendar-days-grid">
                <span v-for="dia in ['L', 'M', 'M', 'J', 'V', 'S', 'D']" :key="dia" class="day-label">{{ dia }}</span>
            </div>

            <div class="calendar-grid">
                <div v-for="empty in primerDiaMes" :key="'empty-'+empty" class="day-cell empty"></div>
                <div 
                    v-for="n in diasEnMes" 
                    :key="n" 
                    class="day-cell"
                    :class="{ 
                        'selected': reserva.fecha === formatearFecha(n),
                        'today': esHoy(n),
                        'disabled': esPasado(n)
                    }"
                    @click="!esPasado(n) && seleccionarDia(n)"
                >
                    <span class="day-number">{{ n }}</span>
                    <span class="day-status" v-if="esDiaCerrado(n)">Cerrado</span>
                </div>
            </div>
        </div>

        <div v-if="reserva.fecha && !cargandoHoras" class="time-selection-area">
            <h4 class="time-title">Huecos para el {{ reserva.fecha }}</h4>
            <div class="time-grid">
                <button 
                    v-for="h in horasDisponibles" 
                    :key="h" 
                    class="time-btn"
                    :class="{ selected: reserva.hora === h }"
                    @click="reserva.hora = h"
                >
                    {{ h.substring(0, 5) }}
                </button>
            </div>
            <p v-if="!horasDisponibles.length" class="no-slots-text">No hay turnos disponibles.</p>
        </div>
        <div v-if="cargandoHoras" class="loader-text">Consultando disponibilidad...</div>

        <div class="footer-actions" v-if="reserva.hora">
          <button @click="confirmarCitaLocal" class="btn-confirm-booking">
            <i class="fas fa-check-circle"></i> CONFIRMAR CITA
          </button>
        </div>
      </div>
    </div>

    <div v-if="mostrarModalNuevoCliente" class="modal-overlay">
      <div class="modal-content admin-modal">
        <h3 class="modal-title">Nuevo Cliente</h3>
        <div class="form-group-modal">
          <label class="modal-label">Nombre</label>
          <input type="text" v-model="nuevoCliente.nombre">
        </div>
        <div class="form-group-modal">
          <label class="modal-label">Teléfono</label>
          <input type="tel" v-model="nuevoCliente.telefono">
        </div>
        <div class="modal-actions-stack">
          <button @click="registrarCliente" class="btn-save-client">Registrar y Continuar</button>
          <button @click="mostrarModalNuevoCliente = false" class="btn-cancel-client">Cancelar</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';

const pasoActual = ref(1);
const busqueda = ref('');
const clientes = ref([]);
const clientesFiltrados = ref([]);
const clienteSeleccionado = ref(null);
const servicios = ref([]);
const servicioSeleccionado = ref(null);
const peluqueros = ref([]);
const peluqueroSeleccionado = ref(null);
const horasDisponibles = ref([]);
const cargandoHoras = ref(false);
const mostrarModalNuevoCliente = ref(false);
const nuevoCliente = ref({ nombre: '', telefono: '' });
const fechaCalendario = ref(new Date());

const reserva = ref({
  cliente_id: null,
  servicio_id: null,
  peluquero_id: null,
  fecha: new Date().toISOString().split('T')[0],
  hora: null
});

const cargarDatos = async () => {
  try {
    const [resS, resC, resP] = await Promise.all([
      fetch('/backend/api/gestion_servicios.php', { credentials: 'include' }),
      fetch('/backend/api/obtener_clientes.php', { credentials: 'include' }),
      fetch('/backend/api/obtener_peluqueros.php', { credentials: 'include' })
    ]);
    servicios.value = await resS.json();
    clientes.value = await resC.json();
    peluqueros.value = await resP.json();
  } catch (e) { console.error(e); }
};

const irAPaso = (n) => { pasoActual.value = n; };

const seleccionarCliente = (c) => {
  clienteSeleccionado.value = c;
  reserva.value.cliente_id = c.id;
  pasoActual.value = 2;
};

const seleccionarServicio = (s) => {
  servicioSeleccionado.value = s;
  reserva.value.servicio_id = s.id;
  pasoActual.value = 3;
};

const seleccionarPeluquero = (p) => {
  peluqueroSeleccionado.value = p;
  reserva.value.peluquero_id = p.id;
  pasoActual.value = 4;
  cargarHorasDisponibles();
};

const buscarClientes = () => {
  if (busqueda.value.length < 2) { clientesFiltrados.value = []; return; }
  const s = busqueda.value.toLowerCase();
  clientesFiltrados.value = clientes.value.filter(c => 
    (c.nombre?.toLowerCase().includes(s)) || (c.telefono?.includes(s))
  ).slice(0, 5);
};

const cargarHorasDisponibles = async () => {
  if (!reserva.value.fecha || !reserva.value.servicio_id) return;
  cargandoHoras.value = true;
  try {
    const pId = reserva.value.peluquero_id || 1;
    const resp = await fetch(`/backend/api/obtener_horas.php?fecha=${reserva.value.fecha}&servicio_id=${reserva.value.servicio_id}&peluquero_id=${pId}`);
    horasDisponibles.value = await resp.json();
  } finally { cargandoHoras.value = false; }
};

// --- LÓGICA CALENDARIO ---
const nombreMesActual = computed(() => fechaCalendario.value.toLocaleString('es-ES', { month: 'long' }));
const anioActual = computed(() => fechaCalendario.value.getFullYear());
const diasEnMes = computed(() => new Date(anioActual.value, fechaCalendario.value.getMonth() + 1, 0).getDate());
const primerDiaMes = computed(() => {
  let f = new Date(anioActual.value, fechaCalendario.value.getMonth(), 1).getDay();
  return f === 0 ? 6 : f - 1;
});
const cambiarMes = (o) => fechaCalendario.value = new Date(fechaCalendario.value.setMonth(fechaCalendario.value.getMonth() + o));
const formatearFecha = (d) => {
  const m = (fechaCalendario.value.getMonth() + 1).toString().padStart(2, '0');
  return `${anioActual.value}-${m}-${d.toString().padStart(2, '0')}`;
};
const seleccionarDia = (n) => {
  reserva.value.fecha = formatearFecha(n);
  reserva.value.hora = null;
  cargarHorasDisponibles();
};
const esHoy = (d) => {
  const h = new Date();
  return h.getDate() === d && h.getMonth() === fechaCalendario.value.getMonth() && h.getFullYear() === anioActual.value;
};
const esPasado = (d) => {
  const f = new Date(anioActual.value, fechaCalendario.value.getMonth(), d);
  const h = new Date(); h.setHours(0,0,0,0);
  return f < h;
};
const esDiaCerrado = (d) => new Date(anioActual.value, fechaCalendario.value.getMonth(), d).getDay() === 0;

const confirmarCitaLocal = async () => {
  const res = await fetch('/backend/api/guardar_reserva_admin.php', {
    method: 'POST',
    body: JSON.stringify(reserva.value)
  });
  if ((await res.json()).success) { alert("¡Cita agendada!"); location.reload(); }
};

onMounted(cargarDatos);
</script>
<style scoped>
.admin-agendar { 
    max-width: 750px; 
    margin: 0 auto; 
    padding: 20px;
    color: #1e293b; 
}

/* 1. RESUMEN SUPERIOR */
.booking-summary {
    display: flex;
    background: white;
    border-radius: 12px;
    margin-bottom: 20px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
}
.summary-item {
    flex: 1;
    padding: 10px 15px;
    border-right: 1px solid #f1f5f9;
}
.summary-item:last-child { border-right: none; }
.summary-label { display: block; font-size: 0.65rem; color: #94a3b8; font-weight: 800; text-transform: uppercase; }
.summary-value { font-size: 0.9rem; font-weight: 700; color: #e75480; }

/* 2. TARJETAS DE PASOS */
.step-card { 
    background: white; 
    border-radius: 16px; 
    padding: 25px; 
    border: 1px solid #e2e8f0; 
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
}
.step-header { display: flex; align-items: center; margin-bottom: 20px; }
.step-number { 
    background: #e75480; color: white; width: 28px; height: 28px; 
    border-radius: 50%; display: flex; align-items: center; 
    justify-content: center; font-weight: bold; margin-right: 12px;
    flex-shrink: 0;
}

/* 3. GRID DE SERVICIOS (Solución a iconos gigantes) */
.services-grid { 
    display: grid; 
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); 
    gap: 15px; 
}
.service-item { 
    border: 2px solid #f1f5f9; 
    border-radius: 12px; 
    padding: 15px; 
    text-align: center; 
    cursor: pointer;
    transition: all 0.2s;
}
.service-item:hover { border-color: #e75480; background: #fff5f8; }
.service-item.selected { border-color: #e75480; background: #fdf2f5; }

/* Tamaño controlado para los iconos */
.service-icon { 
    width: 50px; 
    height: 50px; 
    object-fit: contain; 
    margin-bottom: 10px;
    display: block;
    margin-left: auto;
    margin-right: auto;
}
.service-name-text { display: block; font-weight: 700; font-size: 0.9rem; margin-bottom: 4px; }
.service-price-text { color: #e75480; font-weight: 800; font-size: 0.85rem; }

/* 4. CALENDARIO (Solución a descuadre de días) */
.calendar-web-style { 
    background: #f8fafc; 
    border-radius: 12px; 
    padding: 15px;
}
.calendar-header { 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
    margin-bottom: 15px; 
}
.calendar-days-grid { 
    display: grid; 
    grid-template-columns: repeat(7, 1fr); 
    text-align: center;
    margin-bottom: 5px;
}
.day-label { font-size: 0.7rem; font-weight: 800; color: #94a3b8; }

.calendar-grid { 
    display: grid; 
    grid-template-columns: repeat(7, 1fr); 
    gap: 8px; 
}
.day-cell { 
    aspect-ratio: 1 / 1; 
    display: flex; 
    flex-direction: column; 
    align-items: center; 
    justify-content: center; 
    background: white; 
    border: 1px solid #e2e8f0; 
    border-radius: 8px; 
    cursor: pointer;
    font-size: 0.9rem;
}
.day-cell.selected { background: #e75480 !important; color: white; border-color: #e75480; }
.day-cell.disabled { opacity: 0.3; cursor: not-allowed; background: #f1f5f9; }
.day-status { font-size: 0.6rem; color: #ef4444; }

/* 5. GRID DE HORAS */
.time-grid { 
    display: grid; 
    grid-template-columns: repeat(auto-fill, minmax(90px, 1fr)); 
    gap: 10px; 
    margin-top: 15px; 
}
.time-btn { 
    padding: 10px; 
    border: 1px solid #e2e8f0; 
    border-radius: 8px; 
    background: white; 
    font-weight: 700;
    cursor: pointer;
}
.time-btn.selected { background: #2ecc71; color: white; border-color: #2ecc71; }

/* 6. STAFF / PELUQUEROS */
.staff-grid { 
    display: grid; 
    grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); 
    gap: 15px; 
}
.staff-card { 
    border: 2px solid #f1f5f9; 
    padding: 15px; 
    border-radius: 12px; 
    text-align: center; 
    cursor: pointer; 
}
.staff-img { 
    width: 60px; height: 60px; border-radius: 50%; object-fit: cover; margin-bottom: 8px; 
}
</style>