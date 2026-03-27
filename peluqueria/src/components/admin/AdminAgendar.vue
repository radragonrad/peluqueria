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
        <div class="step-header-flex">
          <div class="step-header-left">
            <span class="step-number">1</span>
            <h3 class="step-title">Datos del Cliente</h3>
          </div>
          <button @click="mostrarModalNuevoCliente = true" class="btn-new-client">
            <i class="fas fa-plus"></i> Nuevo Cliente
          </button>
        </div>
        
        <div class="search-section">
          <label class="input-label-premium">Buscar cliente en la base de datos</label>
          <div class="search-wrapper-premium">
            <i class="fas fa-search search-icon-inside"></i>
            <input 
              type="text" 
              v-model="busqueda" 
              placeholder="Nombre o número de teléfono..."
              @input="buscarClientes"
              class="input-search-premium"
            >
            
            <transition name="fade">
              <ul v-if="clientesFiltrados.length" class="results-dropdown">
                <li v-for="c in clientesFiltrados" :key="c.id" @click="seleccionarCliente(c)" class="result-item">
                  <div class="result-avatar">{{ c.nombre.charAt(0) }}</div>
                  <div class="result-content">
                    <span class="result-name">{{ c.nombre }}</span>
                    <span class="result-detail"><i class="fas fa-phone-alt"></i> {{ c.telefono }}</span>
                  </div>
                  <i class="fas fa-chevron-right arrow-select"></i>
                </li>
              </ul>
            </transition>
          </div>
        </div>
      </div>

      <div class="step-card animate-fade" v-if="pasoActual === 2">
        <div class="step-header">
          <button @click="irAPaso(1)" class="btn-back-step"><i class="fas fa-arrow-left"></i></button>
          <div class="step-header-text">
            <span class="step-number">2</span>
            <h3 class="step-title">Seleccionar Servicio</h3>
          </div>
        </div>

        <div class="services-compact-grid">
          <div 
            v-for="s in servicios" 
            :key="s.id" 
            class="service-card-mini"
            :class="{ 'selected-service': reserva.servicio_id === s.id }"
            @click="seleccionarServicio(s)"
          >
            <div class="service-icon-wrapper">
              <img :src="`/img-icons/${s.icono}`" class="service-icon-mini">
            </div>
            <div class="service-info-mini">
              <span class="service-name-mini">{{ s.nombre }}</span>
              <span class="service-price-mini">{{ s.precio }}€</span>
            </div>
            <div class="selection-indicator" v-if="reserva.servicio_id === s.id">
              <i class="fas fa-check-circle"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="step-card animate-fade" v-if="pasoActual === 3">
        <div class="step-header">
          <button @click="irAPaso(2)" class="btn-back-step"><i class="fas fa-arrow-left"></i></button>
          <div class="step-header-text">
            <span class="step-number">3</span>
            <h3 class="step-title">Peluqueros Disponibles</h3>
          </div>
        </div>

        <div class="staff-grid-professional">
          
          
          <div v-for="p in peluqueros" :key="p.id" 
              class="staff-card-web" 
              :class="{ 'selected-staff': reserva.peluquero_id === p.id }"
              @click="seleccionarPeluquero(p)">
            <div class="staff-avatar-container">
              <img v-if="p.foto" :src="`/uploads/staff/${p.foto}`" class="staff-img-web">
              <div v-else class="staff-avatar-placeholder-web">{{ p.nombre.charAt(0) }}</div>
            </div>
            <span class="staff-name-web">{{ p.nombre }}</span>
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
            <span class="icon-wrapper">
              <i class="fas fa-check"></i>
            </span>
            CONFIRMAR CITA
          </button>
        </div>
      </div>
    </div>

    <div v-if="mostrarModalNuevoCliente" class="modal-overlay">
      <div class="modal-content-premium animate-scale">
        <div class="modal-header-premium">
          <div class="header-icon">
            <i class="fas fa-user-plus"></i>
          </div>
          <h3 class="modal-title-premium">Registrar Nuevo Cliente</h3>
          <button @click="mostrarModalNuevoCliente = false" class="btn-close-modal">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <div class="modal-body-premium">
          <div class="form-grid-premium">
            <div class="form-group-premium">
              <label><i class="fas fa-user"></i> Nombre Completo</label>
              <input type="text" v-model="nuevoCliente.nombre" placeholder="Ej. Juan Pérez">
            </div>

            <div class="form-group-premium">
              <label><i class="fas fa-phone"></i> Teléfono</label>
              <input type="tel" v-model="nuevoCliente.telefono" placeholder="600 000 000">
            </div>

            <div class="form-group-premium">
              <label><i class="fas fa-envelope"></i> Correo Electrónico</label>
              <input type="email" v-model="nuevoCliente.correo" placeholder="usuario@email.com">
            </div>

            <div class="form-group-premium">
              <label><i class="fas fa-birthday-cake"></i> Fecha de Nacimiento</label>
              <input type="date" v-model="nuevoCliente.fecha_nacimiento">
            </div>
          </div>
        </div>

        <div class="modal-footer-premium">
          <button @click="mostrarModalNuevoCliente = false" class="btn-cancel-premium">Cancelar</button>
          <button @click="registrarCliente" class="btn-save-premium">
            <i class="fas fa-check"></i> Registrar y Seleccionar
          </button>
        </div>
      </div>
    </div>
  </div>
  <!-- Estructura del Modal -->
<div v-if="mostrarModalExito" class="modal-overlay">
  <div class="modal-content">
    <div class="icon-success">
      <i class="fas fa-check-circle"></i>
    </div>
    <h3>¡Cita Agendada!</h3>
    <p>La reserva se ha guardado correctamente en el sistema.</p>
    <button @click="cerrarYRecargar" class="btn-modal-ok">ENTENDIDO</button>
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
const mostrarModalExito = ref(false);
const nuevoCliente = ref({ 
  nombre: '', 
  telefono: '', 
  correo: '', 
  fecha_nacimiento: '' 
});
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
  try {
    const res = await fetch('/backend/api/guardar_reserva_admin.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(reserva.value)
    });

    const data = await res.json();

    if (data.success) {
      // En lugar de alert, activamos el modal
      mostrarModalExito.value = true;
    } else {
      alert("Error: " + (data.error || "No se pudo guardar"));
    }
  } catch (error) {
    console.error("Error en la petición:", error);
  }
};

const registrarCliente = async () => {
  // Validaciones básicas
  if (!nuevoCliente.value.nombre || !nuevoCliente.value.telefono) {
    alert("Por favor, introduce al menos nombre y teléfono.");
    return;
  }

  try {
    const response = await fetch('/backend/api/registrar_cliente_admin.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(nuevoCliente.value)
    });

    const data = await response.json();

    if (data.success) {
      // 1. Creamos el objeto de cliente con el ID que nos devuelve la base de datos
      const clienteCreado = {
        id: data.id,
        nombre: nuevoCliente.value.nombre,
        telefono: nuevoCliente.value.telefono,
        email: nuevoCliente.value.correo,
        fecha_nacimiento: nuevoCliente.value.fecha_nacimiento
      };

      // 2. Lo añadimos a nuestra lista local para que aparezca en búsquedas futuras
      clientes.value.push(clienteCreado);

      // 3. Lo seleccionamos y pasamos al paso de servicios
      seleccionarCliente(clienteCreado);

      // 4. Cerramos el modal y reseteamos el formulario
      mostrarModalNuevoCliente.value = false;
      nuevoCliente.value = { nombre: '', telefono: '', correo: '', fecha_nacimiento: '' };
      
      console.log("Cliente registrado y seleccionado:", clienteCreado);
    } else {
      alert("Error: " + data.message);
    }
  } catch (error) {
    console.error("Error al registrar cliente:", error);
    alert("No se pudo conectar con el servidor.");
  }
};

// Función para limpiar y recargar tras el éxito
const cerrarYRecargar = () => {
  mostrarModalExito.value = false;
  location.reload();
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
    padding: 10px; /* Reducido de 15px */
    max-width: 450px; /* Añadimos un ancho máximo para que no se estire */
    margin: 0 auto; /* Centramos el calendario */
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
    gap: 4px; /* Reducido de 8px */
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
    font-size: 0.8rem; /* Reducido de 0.9rem */
    padding: 2px;      /* Añadimos un pequeño padding interno */
}
.day-cell.selected { background: #e75480 !important; color: white; border-color: #e75480; }
.day-cell.disabled { opacity: 0.3; cursor: not-allowed; background: #f1f5f9; }
.day-status { 
    font-size: 0.5rem; /* Reducido de 0.6rem */
    color: #ef4444; 
    line-height: 1;    /* Forzamos que no ocupe espacio extra */
    margin-top: 1px;
}

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

/* Alineación de cabecera en el paso */
.step-header-flex {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}
.step-header-left { display: flex; align-items: center; }

/* Botón Nuevo Cliente a la derecha */
.btn-new-client {
    background: #f8fafc;
    color: #e75480;
    border: 1px solid #e75480;
    padding: 8px 16px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.3s;
}
.btn-new-client:hover {
    background: #e75480;
    color: white;
}

/* Input de búsqueda profesional */
.input-label-premium {
    display: block;
    font-size: 0.8rem;
    font-weight: 700;
    color: #64748b;
    margin-bottom: 8px;
    margin-left: 4px;
}
.search-wrapper-premium {
    position: relative;
    width: 100%;
}
.search-icon-inside {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 1.1rem;
}
.input-search-premium {
    width: 100%;
    padding: 14px 14px 14px 48px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s;
    background: #fff;
}
.input-search-premium:focus {
    border-color: #e75480;
    outline: none;
    box-shadow: 0 0 0 4px rgba(231, 84, 128, 0.1);
}

/* Dropdown de resultados */
.results-dropdown {
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    right: 0;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1);
    z-index: 1000;
    padding: 6px;
    list-style: none;
    border: 1px solid #e2e8f0;
}
.result-item {
    display: flex;
    align-items: center;
    padding: 12px;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.2s;
}
.result-item:hover {
    background: #fdf2f5;
}
.result-avatar {
    width: 35px;
    height: 35px;
    background: #e2e8f0;
    color: #64748b;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-right: 12px;
    text-transform: uppercase;
}
.result-content {
    display: flex;
    flex-direction: column;
    flex: 1;
}
.result-name {
    font-weight: 700;
    color: #1e293b;
    font-size: 0.95rem;
}
.result-detail {
    font-size: 0.8rem;
    color: #64748b;
}
.arrow-select {
    color: #cbd5e1;
    font-size: 0.8rem;
}

/* Animación de entrada para los pasos */
.animate-fade {
    animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Contenedor principal de servicios */
.services-compact-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); /* Cuadros más estrechos */
    gap: 12px;
}

/* Tarjeta de servicio más pequeña */
.service-card-mini {
    position: relative;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 12px;
    display: flex;
    align-items: center; /* Alineación horizontal */
    gap: 10px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.service-card-mini:hover {
    border-color: #e75480;
    background: #fff5f8;
    transform: translateY(-2px);
}

.service-card-mini.selected-service {
    border-color: #e75480;
    background: #fdf2f5;
    box-shadow: 0 4px 12px rgba(231, 84, 128, 0.1);
}

/* Icono mucho más pequeño y centrado */
.service-icon-wrapper {
    width: 35px;
    height: 35px;
    background: #f8fafc;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.service-icon-mini {
    width: 20px;
    height: 20px;
    object-fit: contain;
}

/* Textos compactos */
.service-info-mini {
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.service-name-mini {
    font-weight: 700;
    font-size: 0.85rem;
    color: #1e293b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.service-price-mini {
    font-weight: 800;
    font-size: 0.8rem;
    color: #e75480;
}

/* Indicador de selección (el check) */
.selection-indicator {
    position: absolute;
    top: -5px;
    right: -5px;
    color: #e75480;
    background: white;
    border-radius: 50%;
    font-size: 1rem;
    line-height: 1;
}

/* Ajuste cabecera para que no ocupe tanto */
.step-header-text {
    display: flex;
    align-items: center;
}

/* Cuadrícula de Peluqueros */
.staff-grid-professional {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 20px;
    margin-top: 10px;
}

/* Tarjeta individual (estilo imagen adjunta) */
.staff-card-web {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px 10px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.staff-card-web:hover {
    border-color: #e75480;
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.staff-card-web.selected-staff {
    border-color: #e75480;
    background: #fdf2f5;
    box-shadow: 0 0 0 2px #e75480;
}

/* Contenedor del Avatar Circular */
.staff-avatar-container {
    width: 85px;
    height: 85px;
    border-radius: 50%;
    border: 3px solid #f1f5f9;
    padding: 2px;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background: white;
}

.staff-img-web {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
}

.staff-avatar-placeholder-web {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: #f1f5f9;
    color: #94a3b8;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: bold;
    text-transform: uppercase;
}

/* Nombre del Peluquero */
.staff-name-web {
    font-size: 0.9rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.2;
    margin-top: 5px;
    word-break: break-word;
}

/* Overlay del Modal */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(15, 23, 42, 0.6); /* Fondo oscuro semitransparente */
    backdrop-filter: blur(4px); /* Efecto desenfoque */
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2000;
}

/* Contenedor del Modal */
.modal-content-premium {
    background: white;
    width: 90%;
    max-width: 500px;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

/* Cabecera del Modal */
.modal-header-premium {
    padding: 24px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    position: relative;
}
.header-icon {
    width: 40px;
    height: 40px;
    background: #fdf2f5;
    color: #e75480;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    font-size: 1.2rem;
}
.modal-title-premium {
    font-weight: 800;
    color: #1e293b;
    margin: 0;
    font-size: 1.2rem;
}
.btn-close-modal {
    position: absolute;
    right: 20px;
    background: none;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    font-size: 1.2rem;
}

/* Cuerpo del Formulario */
.modal-body-premium {
    padding: 24px;
}
.form-grid-premium {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
}
.form-group-premium label {
    display: block;
    font-size: 0.85rem;
    font-weight: 700;
    color: #64748b;
    margin-bottom: 6px;
}
.form-group-premium label i {
    width: 20px;
    color: #e75480;
}
.form-group-premium input {
    width: 100%;
    padding: 12px;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    font-size: 0.95rem;
    transition: all 0.3s;
}
.form-group-premium input:focus {
    border-color: #e75480;
    outline: none;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(231, 84, 128, 0.1);
}

/* Footer del Modal */
.modal-footer-premium {
    padding: 20px 24px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: flex-end;
    gap: 12px;
}
.btn-cancel-premium {
    background: white;
    border: 1px solid #e2e8f0;
    padding: 10px 20px;
    border-radius: 10px;
    color: #64748b;
    font-weight: 700;
    cursor: pointer;
}
.btn-save-premium {
    background: #e75480;
    border: none;
    padding: 10px 20px;
    border-radius: 10px;
    color: white;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(231, 84, 128, 0.3);
}

/* Animación */
.animate-scale {
    animation: scaleIn 0.3s ease-out;
}
@keyframes scaleIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}

/* Contenedor del botón (opcional, para el margen) */
.btn-confirmar-container {
    margin-top: 20px;
    text-align: left;
}

/* Estilo principal del botón */
.btn-confirmar {
    display: inline-flex;
    align-items: center;
    background-color: #f8f9fa; /* Gris muy claro */
    color: #000000;            /* Texto negro */
    border: 1px solid #e9ecef; /* Borde sutil */
    border-radius: 8px;        /* Bordes redondeados */
    padding: 10px 20px;
    font-size: 14px;
    font-weight: bold;
    text-transform: uppercase; /* Texto en mayúsculas */
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05); /* Sombra suave */
}

/* El icono circular del check */
.btn-confirmar i {
    background-color: #000000; /* Fondo negro para el círculo */
    color: #ffffff;            /* Check blanco */
    width: 18px;
    height: 18px;
    border-radius: 50%;        /* Círculo perfecto */
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 10px;        /* Espacio entre icono y texto */
    font-size: 10px;           /* Tamaño del check dentro del círculo */
}

/* Efecto Hover (cuando pasas el ratón) */
.btn-confirmar:hover {
    background-color: #e2e6ea;
    border-color: #dae0e5;
    transform: translateY(-1px); /* Pequeño salto hacia arriba */
}

/* Efecto Click */
.btn-confirmar:active {
    transform: translateY(0);
}

.footer-actions {
  margin-top: 20px;
  display: flex;
  justify-content: flex-start; /* Alineado a la izquierda según la foto */
}

.btn-confirm-booking {
  display: inline-flex;
  align-items: center;
  background-color: #e75480 !important;   /* Gris muy clarito */
  color: #ffffff;                 /* Texto negro */
  border: 1px solid #f0f0f0;   /* Borde casi invisible */
  border-radius: 8px;          /* Bordes redondeados suaves */
  padding: 10px 18px;
  font-size: 13px;             /* Un poco más pequeño y elegante */
  font-weight: 700;            /* Negrita */
  letter-spacing: 0.5px;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

/* El contenedor del icono (el círculo negro) */
.icon-wrapper {
  background-color: #000;      /* Fondo negro */
  color: #fff;                 /* Check blanco */
  width: 18px;
  height: 18px;
  border-radius: 50%;          /* Círculo */
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 10px;          /* Separación del texto */
}

.icon-wrapper i {
  font-size: 9px;              /* Check pequeño dentro del círculo */
}

/* Efectos */
.btn-confirm-booking:hover {
  background-color: #f1f3f5;
  transform: translateY(-1px);
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.btn-confirm-booking:active {
  transform: translateY(0);
}

/* Fondo oscuro translúcido */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px); /* Desenfoque de fondo muy moderno */
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

/* Caja del modal */
.modal-content {
  background: white;
  padding: 30px;
  border-radius: 15px;
  text-align: center;
  max-width: 400px;
  width: 90%;
  box-shadow: 0 10px 25px rgba(0,0,0,0.2);
  animation: modalIn 0.3s ease-out;
}

/* Icono de éxito verde */
.icon-success {
  font-size: 50px;
  color: #2ecc71;
  margin-bottom: 15px;
}

.modal-content h3 {
  margin: 0 0 10px 0;
  color: #333;
}

.modal-content p {
  color: #666;
  margin-bottom: 20px;
}

/* Botón de cierre del modal */
.btn-modal-ok {
  background: #000;
  color: #fff;
  border: none;
  padding: 12px 30px;
  border-radius: 8px;
  font-weight: bold;
  cursor: pointer;
  transition: background 0.3s;
}

.btn-modal-ok:hover {
  background: #333;
}

/* Animación de entrada */
@keyframes modalIn {
  from { opacity: 0; transform: translateY(-20px); }
  to { opacity: 1; transform: translateY(0); }
}

.month-name {
    font-size: 0.9rem; /* Tamaño más pequeño */
    font-weight: 700;
}

.btn-nav {
    padding: 2px 8px;
    font-size: 0.8rem;
    background: none;
    border: none;
    cursor: pointer;
    color: #64748b;
}
</style>