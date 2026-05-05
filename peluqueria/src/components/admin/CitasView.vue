<template>

  <div class="calendar-container">
    <div class="calendar-nav-bar">
      <div class="nav-left">
        <button class="btn-today" @click="irHoy">Hoy</button>
        <div class="nav-arrows">
          <button class="btn-icon" @click="cambiarSemana(-7)">&lt;</button>
          <button class="btn-icon" @click="cambiarSemana(7)">&gt;</button>
        </div>

        <div class="datepicker-container">
          <h2 class="current-month">{{ nombreMesActual }}</h2>
          <input 
            type="date" 
            class="hidden-date-input" 
            @change="seleccionarFechaManual"
            :value="fechaReferencia.toISOString().split('T')[0]"
          />
          <span class="material-icons calendar-edit-icon">event</span>
        </div>
        <button class="btn-add-event" @click="abrirModalNuevaCita"  title="Nueva Cita">
          <span class="material-icons">add_circle</span>          
        </button>
        <button class="btn-add-event btn-add-user" @click="abrirModalNuevoUsuario" title="Nuevo Cliente">
        <span class="material-icons">person_add</span>
      </button>
      </div>
        
        <div class="toolbar-left">          
          <span class="citas-total-badge" v-if="citasFiltradas.length > 0">
            {{ citasFiltradas.length }} citas
          </span>
        </div>

        <div class="toolbar-right">
          <button 
            @click="refrescarAgenda" 
            class="btn-toolbar btn-refrescar" 
            :class="{ 'btn-loading': cargando }"
            title="Refrescar agenda"
          >
            <span class="material-icons" :class="{ 'icon-spin': cargando }">
              {{ cargando ? 'hourglass_top' : 'sync' }}
            </span>
          </button>

          <div class="busqueda-container">
            <span class="material-icons search-icon">search</span>
            <input 
              v-model="terminoBusqueda" 
              type="text" 
              class="input-busqueda" 
              placeholder="Buscar por cliente, teléfono o servicio..."
            />
            <span 
              v-if="terminoBusqueda" 
              class="material-icons clear-icon" 
              @click="terminoBusqueda = ''"
            >close</span>
          </div>

          <div class="filter-container-google">
            <label for="estado-filter" class="filter-label">Estado:</label>
            <select 
              id="estado-filter" 
              v-model="filtroEstado" 
              class="select-calendar-filter"
            >
              <option v-for="opt in opcionesEstado" :key="opt.value" :value="opt.value">
                {{ opt.label }}
              </option>
            </select>
          </div>
        </div>

    </div>

    <div class="calendar-header">
      <div class="time-column-header"><span class="timezone-label">GMT+02</span></div>
      <div v-for="(dia, index) in diasVisibles" :key="index" class="day-column-header">
        <span class="day-name">{{ dia.nombre }}</span>
        <div class="day-number-container">
          <span class="day-number" :class="{ 'today-circle': esHoy(dia.fechaISO) }">{{ dia.numero }}</span>
        </div>
      </div>
    </div>

    <div class="calendar-body">
      <div v-if="lineaTop > 0" class="now-indicator" :style="{ top: lineaTop + 'px' }">
        <div class="now-circle"></div>
      </div>

      <div class="time-column">
        <div v-for="h in horasLista" :key="h" class="hour-slot">
          <span class="hour-text">{{ h }}</span>
        </div>
      </div>

      <div v-for="(dia, diaIndex) in diasVisibles" :key="diaIndex" class="day-column">
        <div v-for="h in horasLista" :key="h" class="hour-grid-cell"></div>
      
        <div v-for="cita in citasFiltradas.filter(c => c.fechaOriginal === dia.fechaISO)"
            :key="cita.id"
            class="cita-card"
            :style="{ 
                top: calcularTop(cita.inicio) + 'px', 
                height: cita.duracion + 'px', 
                backgroundColor: obtenerEstiloEstado(cita.estado, cita.servicio_id).bg,
                color: obtenerEstiloEstado(cita.estado, cita.servicio_id).text,
                borderLeftColor: obtenerEstiloEstado(cita.estado, cita.servicio_id).border
            }"
            @click="abrirDetalles(cita)">
          
          <div class="cita-inner-content">
            <span class="cita-info-line">
              <strong class="cita-hora-text">{{ cita.inicio }}</strong>
              <span class="cita-cliente-name">{{ cita.cliente }}</span>
            </span>
            <span v-if="cita.duracion > 30" class="cita-servicio-text">
              {{ cita.servicio }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <Transition name="fade">
      <div v-if="citaSeleccionada" class="modal-overlay" @click.self="cerrarModal">
        <div class="modal-content">
          
          <div class="modal-header-actions">
            <template v-if="!editandoAnulacion && !editandoPago && !editandoCita">
              <button 
                v-if="citaSeleccionada.estado === 'PENDIENTE'"
                class="btn-icon-alt btn-pagar-trigger" 
                @click="prepararPago"
                title="Finalizar y Cobrar"
              >
                <span class="material-icons">point_of_sale</span>
              </button>
              
              <button 
                class="btn-icon-alt"
                @click="prepararEdicion"
                :disabled="citaSeleccionada.estado !== 'PENDIENTE'"
                :class="{ 'btn-disabled': citaSeleccionada.estado !== 'PENDIENTE' }"
                title="Modificar la Cita"
              >
                <span class="material-icons">edit</span>
              </button>

              <button 
                class="btn-icon-alt" 
                @click="prepararAnulacion"
                :disabled="citaSeleccionada.estado !== 'PENDIENTE'"
                :class="{ 'btn-disabled': citaSeleccionada.estado !== 'PENDIENTE' }"
                title="Solo se pueden anular citas pendientes"
              >
                <span class="material-icons">delete</span>
              </button>
            </template>

            <button class="btn-icon-alt" @click="cerrarModal">
              <span class="material-icons">close</span>
            </button>
          </div>

          <div class="modal-body">
            
            <div v-if="!editandoAnulacion && !editandoPago && !editandoCita" class="fade-in">
              <div class="detail-row main-title">
                <div class="color-box" :style="{ backgroundColor: obtenerColorServicio(citaSeleccionada.servicio_id) }"></div>
                <div>
                  <h3>Cita para {{ citaSeleccionada.cliente }}</h3>              
                  <p class="detail-subtitle">
                    {{ citaSeleccionada.fechaFormateada }}  ·  {{ citaSeleccionada.inicio }} – {{ calcularFin(citaSeleccionada) }}
                  </p>
                  <span v-if="citaSeleccionada.duracion" class="duracion-texto">
                    ({{ citaSeleccionada.duracion }} min)
                  </span>
                </div>
              </div>

              <div v-if="citaSeleccionada.cliente_telefono" class="detail-row">
                <span class="material-icons info-icon">phone</span>
                <span>{{ citaSeleccionada.cliente_telefono }}</span>
              </div>

              <div class="detail-row">
                <span class="material-icons info-icon">content_cut</span>
                <div class="servicio-precio-container">
                  <span>{{ citaSeleccionada.servicio }}</span>
                  <span class="cita-precio-tag">{{ citaSeleccionada.precio }}€</span>
                </div>
              </div>

              <div class="detail-row">
                <span class="material-icons info-icon">person</span>
                <span>{{ citaSeleccionada.peluquero || 'Ruben gutierrez ibañez' }}</span>
              </div>

              <div class="detail-row">
                <span class="material-icons info-icon">info</span>
                <div class="estado-container">
                  <span class="estado-label">Estado:</span>
                  <span 
                    class="estado-valor" 
                    :style="{ color: citaSeleccionada.color, backgroundColor: citaSeleccionada.color + '15' }"
                  >
                    {{ citaSeleccionada.estado }}
                  </span>
                 
                  <div v-if="citaSeleccionada.metodo_pago === 'Deuda'" class="tag-deuda-modal">
                    <span class="material-icons">payments</span>
                    PAGO PENDIENTE (DEUDA)
                  </div>
                </div>
              </div>
            </div>

            <div v-else-if="editandoPago" class="modal-pago-interna fade-in">
              <h2 class="titulo-pago-mini">Finalizar Cita</h2>
              <p class="cliente-pago-nombre-mini">{{ citaSeleccionada.cliente }}</p>

              <div class="grid-metodos-pago-horizontal">
                <div 
                  v-for="metodo in ['Efectivo', 'Bizum', 'Tarjeta', 'Deuda']" 
                  :key="metodo"
                  class="metodo-card-horizontal"
                  :class="{ 'activo': metodoPagoSeleccionado === metodo }"
                  @click="metodoPagoSeleccionado = metodo"
                >
                  <div class="icon-wrapper-mini">
                    <span class="material-icons">
                      {{ metodo === 'Efectivo' ? 'payments' : metodo === 'Bizum' ? 'smartphone' : metodo === 'Tarjeta' ? 'credit_card' : 'person_search' }}
                    </span>
                  </div>
                  <p class="metodo-texto-extra-mini">{{ metodo }}</p>
                </div>
              </div>

            <div v-if="metodoPagoSeleccionado !== 'Deuda'" class="promociones-section-mini fade-in">
              <p class="promo-label-mini">PROMOCIONES DISPONIBLES</p>
              
              <div 
                class="promo-card-mini" 
                :class="{ 'promo-activa': promocionSeleccionada === '4x1' }"
                @click="promocionSeleccionada = (promocionSeleccionada === '4x1' ? null : '4x1')"
              >
                <div class="promo-info-mini">
                  <span class="material-icons">style</span>
                  <span>4 x 1 (0/5 sellos)</span>
                </div>
                
                <div class="custom-radio-display">
                  <span class="material-icons">
                    {{ promocionSeleccionada === '4x1' ? 'check_circle' : 'radio_button_unchecked' }}
                  </span>
                </div>
              </div>
            </div>

              <div class="acciones-pago-compactas">
                <button @click="finalizarCitaConPago" class="btn-finalizar-pago-mini">
                  Finalizar <span class="material-icons">check</span>
                </button>
                <button @click="cancelarPago" class="btn-volver-mini">
                  Volver
                </button>
              </div>
            </div>

            <div v-else-if="editandoAnulacion" class="modal-anular-interna fade-in">
              <h2 class="titulo-anular">Anular Cita</h2>
              <p class="subtitulo-anular">Indica el motivo para anular la cita de <strong>{{ citaSeleccionada.cliente }}</strong></p>
              
              <div class="campo-motivo">
                <label>Motivo:</label>
                <textarea 
                  v-model="motivoAnulacion" 
                  placeholder="Ej: El cliente no puede asistir..."
                  rows="4"
                ></textarea>
              </div>

              <div class="acciones-anular">
                <button @click="confirmarAnulacion" class="btn-confirmar">
                  Confirmar Anulación
                </button>
                <button @click="cancelarAnulacion" class="btn-volver">
                  Volver
                </button>
              </div>
            </div>

          <!-- ── Vista de edición ── -->
          <div v-if="editandoCita" class="modal-editar-interna fade-in">
            <h3 class="titulo-editar">Modificar Cita</h3>
            <p class="subtitulo-editar">{{ citaSeleccionada.cliente }}</p>

            <div class="form-row-icon">
              <span class="material-icons icon-label">badge</span>
              <select v-model="citaEditada.peluquero_id" class="select-calendar-peluquero" @change="cargarHorasEdicion">
                <option :value="null" disabled>Seleccionar peluquero</option>
                <option v-for="p in peluqueros" :key="p.id" :value="p.id">{{ p.nombre }}</option>
              </select>
            </div>

            <div class="form-row-icon">
              <span class="material-icons icon-label">content_cut</span>
              <select v-model="citaEditada.servicio_id" class="select-calendar" @change="cargarHorasEdicion">
                <option :value="null" disabled>Seleccionar servicio</option>
                <option v-for="s in servicios" :key="s.id" :value="s.id">{{ s.nombre }} ({{ s.precio }}€)</option>
              </select>
            </div>

            <div class="form-row-icon">
              <span class="material-icons icon-label">event</span>
              <div class="datetime-grid">
                <div class="input-container">
                  <input type="date" v-model="citaEditada.fecha" class="input-calendar-date" @change="cargarHorasEdicion" />
                </div>
                <div class="input-container">
                  <select v-model="citaEditada.hora" class="select-calendar-pro" :disabled="cargandoHorasEdicion || horasEdicion.length === 0">
                    <option :value="null" v-if="cargandoHorasEdicion">Cargando...</option>
                    <option :value="null" v-else-if="horasEdicion.length === 0">Sin huecos disponibles</option>
                    <option :value="null" v-else disabled>Seleccionar hora</option>
                    <option v-for="h in horasEdicion" :key="h" :value="h">{{ h }}</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="acciones-editar">
              <button class="btn-confirmar" @click="guardarEdicion" :disabled="!citaEditada.hora || !citaEditada.servicio_id">
                Guardar cambios
              </button>
              <button class="btn-volver" @click="editandoCita = false">Cancelar</button>
            </div>
          </div>

          </div> </div> </div>
    </Transition>
    // Modal nueva cita
    <Transition name="fade">
<div v-if="mostrandoModalCrear" class="modal-overlay-calendar" @click.self="mostrandoModalCrear = false">
  <div class="modal-content-calendar fade-in">
    <div class="modal-header-calendar">
      <h3 class="modal-title-simple">Nueva Cita Manual</h3>
      <button class="btn-close-x" @click="mostrandoModalCrear = false">
        <span class="material-icons">close</span>
      </button>
    </div>

    <div class="modal-body-calendar">
      <div class="form-row-icon">
        <span class="material-icons icon-label">person</span>
  <div class="buscador-cliente-container">
    <input 
      type="text" 
      v-model="busquedaCliente" 
      class="input-calendar-search" 
      placeholder="Busca o selecciona un cliente..."
      @focus="mostrarListaClientes = true"
      @keyup.esc="cerrarBuscador"
      @blur="cerrarBuscadorConRetraso"
    >
    <ul v-if="mostrarListaClientes && clientesFiltrados.length > 0" class="dropdown-clientes">
      <li 
        v-for="u in clientesFiltrados" 
        :key="u.id" 
        @mousedown="seleccionarCliente(u)"
      >
        <div class="cli-info">
          <span class="cli-nombre">{{ u.nombre }}</span>
          <span class="cli-tel">{{ u.telefono }}</span>
        </div>
      </li>
    </ul>
  </div>
      </div>

      <div class="form-row-icon">
        <span class="material-icons icon-label">badge</span>
        <div class="peluquero-selector-container">
          <select 
            v-model="nuevaCita.peluquero_id" 
            class="select-calendar-peluquero"
          >
            <option :value="null" disabled>Seleccionar peluquero</option>
            <option v-for="p in peluqueros" :key="p.id" :value="p.id">
              {{ p.nombre }}
            </option>
          </select>
        
          <div class="sesion-tag" v-if="peluqueroLogueado">
            <span class="dot-active"></span>
            Asignado por sesión: <strong>{{ peluqueroLogueado.nombre }}</strong>
          </div>
        </div>
      </div>

      <div class="form-row-icon">
        <span class="material-icons icon-label">content_cut</span>
        <select v-model="nuevaCita.servicio_id" class="select-calendar">
          <option :value="null" disabled>¿Qué servicio desea?</option>
          <option v-for="s in servicios" :key="s.id" :value="s.id">            
            {{ s.nombre }} ({{ s.precio }}€) {{s.duracion_min}}min
          </option>
        </select>
      </div>

<div class="form-row-icon">
  <span class="material-icons icon-label">event</span>
  <div class="datetime-grid">
    
    <div class="input-container">
      <input 
        type="date" 
        v-model="nuevaCita.fecha" 
        class="input-calendar-date"
        required
      />
    </div>

    <div class="input-container">
      <select 
        v-model="nuevaCita.hora" 
        class="select-calendar-pro"
        :disabled="cargandoHoras || horasDisponibles.length === 0"
      >
        <option :value="null" v-if="cargandoHoras">Cargando...</option>
        <option :value="null" v-else-if="horasDisponibles.length === 0">Sin huecos</option>
        <option :value="null" v-else>Seleccionar hora</option>
        
        <option v-for="h in horasDisponibles" :key="h" :value="h">
          {{ h }}
        </option>
      </select>
    </div>

  </div>
</div>
    </div>

    <div class="modal-footer-calendar">
      <button 
        class="btn-save-calendar" 
        @click="crearReservaBackend" 
        :disabled="!nuevaCita.cliente_id || !nuevaCita.servicio_id || cargandoGuardado"
      >
        Guardar
      </button>
    </div>
  </div>
</div>
</Transition>
<NuevoUsuarioModal 
  v-if="mostrarModalUsuario" 
  @close="mostrarModalUsuario = false"
  @usuario-creado="alCrearUsuario"
/>

  <!-- Toast global de notificación -->
  <Transition name="toast-slide">
    <div v-if="toast.visible" :class="['toast-global', toast.tipo]">
      <span class="material-icons toast-icon">{{ toast.tipo === 'exito' ? 'check_circle' : 'error' }}</span>
      <span class="toast-msg">{{ toast.mensaje }}</span>
      <button class="toast-close" @click="toast.visible = false">
        <span class="material-icons" style="font-size:16px">close</span>
      </button>
    </div>
  </Transition>

  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, watch } from 'vue';
import NuevoUsuarioModal from './NuevoUsuarioModal.vue';

// ── Toast global de notificación ──
const toast = reactive({ visible: false, mensaje: '', tipo: 'exito' });
let _toastTimer = null;
const mostrarToast = (mensaje, tipo = 'exito', duracion = 3500) => {
  if (_toastTimer) clearTimeout(_toastTimer);
  toast.mensaje = mensaje;
  toast.tipo = tipo;
  toast.visible = true;
  if (tipo !== 'error-sticky') {
    _toastTimer = setTimeout(() => { toast.visible = false; }, duracion);
  }
};

const HORA_INICIO_VISUAL = 8;
const horasLista = ref(['08:00','09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00','18:00','19:00','20:00','21:00','22:00','23:00']);
const fechaReferencia = ref(new Date());
const citas = ref([]);
const lineaTop = ref(-1000);
const citaSeleccionada = ref(null); // Estado para el modal
const editandoAnulacion = ref(false);
const editandoCita = ref(false);
const citaEditada = ref({ peluquero_id: null, servicio_id: null, fecha: null, hora: null });
const horasEdicion = ref([]);
const cargandoHorasEdicion = ref(false);
const motivoAnulacion = ref('');
const terminoBusqueda = ref('');
const cargando = ref(false); // Para el estado del botón refrescar
const todasLasCitas = ref([]);
const editandoPago = ref(false);
const metodoPagoSeleccionado = ref('Efectivo');
const promocionSeleccionada = ref(null);
const creandoCita = ref(false);
const mostrandoModalCrear = ref(false);
const cargandoGuardado = ref(false);
const busquedaCliente = ref('');
const mostrarListaClientes = ref(false);
const peluqueroLogueado = ref(null);
const horasDisponibles = ref([]);
const cargandoHoras = ref(false);
const servicios = ref([]); // Servicios con sus precios
const peluqueros = ref([]); // Lista completa de peluqueros por si quieres cambiarlo
const mostrarModalExito = ref(false);
const filtroEstado = ref('PENDIENTE');
const mostrarModalUsuario = ref(false);

// --- RESPONSIVE: DETECCIÓN DE MÓVIL ---
const isMobile = ref(window.innerWidth <= 480);
const actualizarIsMobile = () => { isMobile.value = window.innerWidth <= 480; };
window.addEventListener('resize', actualizarIsMobile);


const abrirModalNuevoUsuario = () => {
  mostrarModalUsuario.value = true;
};

// Esta función se ejecuta cuando el modal nos avisa que ya guardó al cliente
const alCrearUsuario = (nuevoUsuario) => {
  // Recargamos la lista de clientes para que aparezca en el buscador del modal de cita
  cargarClientes();
  // mostrarToast('Cliente "' + nuevoUsuario + '" creado correctamente', 'exito');
};

// Opciones para el select
const opcionesEstado = [
  { value: 'PENDIENTE', label: 'Pendientes' },
  { value: 'COMPLETADA', label: 'Completadas' },
  { value: 'ANULADA WEB', label: 'Anuladas Web' },
  { value: 'ANULADA LOCAL', label: 'Anuladas Local' },
  { value: 'TODAS', label: 'Ver Todas' } // Opcional, por si quieres ver todo junto
];

// Objeto para la nueva cita
const nuevaCita = ref({
  cliente_id: null,
  servicio_id: null,
  peluquero_id: 8, // Por defecto Ruben
  fecha: new Date().toISOString().split('T')[0],
  hora: '10:00',
  estado: 'PENDIENTE'
});

const obtenerColorServicio = (servicio_id) => {
  return COLORES_SERVICIO[servicio_id]?.border || '#1a73e8';
};

const cargarHorasDisponibles = async () => {
  if (!nuevaCita.value.fecha || !nuevaCita.value.servicio_id) {
    horasDisponibles.value = [];
    return;
  }

  cargandoHoras.value = true;
  try {
    const pId = nuevaCita.value.peluquero_id || 9;
    const resp = await fetch(`/backend/api/obtener_horas.php?fecha=${nuevaCita.value.fecha}&servicio_id=${nuevaCita.value.servicio_id}&peluquero_id=${pId}`);
    const data = await resp.json();
    
    horasDisponibles.value = data;

    // IMPORTANTE: Si la hora que estaba seleccionada no está en la nueva lista, la reseteamos
    if (nuevaCita.value.hora && !horasDisponibles.value.includes(nuevaCita.value.hora)) {
      nuevaCita.value.hora = null;
    }
  } catch (e) {
    console.error("Error al obtener horas:", e);
    horasDisponibles.value = [];
  } finally {
    cargandoHoras.value = false;
  }
};



// Función para cerrar el buscador de forma segura
const cerrarBuscador = () => {
  mostrarListaClientes.value = false;
  // Si no se seleccionó nada, podemos limpiar el texto si prefieres
  if (!nuevaCita.value.cliente_id) {
    busquedaCliente.value = '';
  }
};

// Función para manejar la tecla ESC
const manejarEsc = (e) => {
  if (e.key === 'Escape') {
    cerrarBuscador();
  }
};

const cargarPeluquerosYDetectarSesion = async () => {
  try {
    const response = await fetch('/backend/api/obtener_peluqueros.php');
    const data = await response.json();
    
    // IMPORTANTE: Si tu PHP devuelve un objeto directo en lugar de un array dentro de 'data.peluqueros'
    if (data && data.id) {
      peluqueros.value = [data]; 
    } else if (Array.isArray(data)) {
      peluqueros.value = data;
    } else if (data.peluqueros) {
      peluqueros.value = data.peluqueros;
    }

    // Preselección automática del ID 9 (Jose Manuel)
    if (peluqueros.value.length > 0) {
        nuevaCita.value.peluquero_id = 9; 
    }
  } catch (error) {
    console.error("Error al cargar peluqueros:", error);
    peluqueros.value = []; // Evitamos que sea null
  }
};




// Filtramos los clientes según lo que escribas
const clientesFiltrados = computed(() => {
  if (!busquedaCliente.value) return clientes.value;
  const termino = busquedaCliente.value.toLowerCase();
  return clientes.value.filter(u => 
    u.nombre.toLowerCase().includes(termino) || 
    (u.telefono && u.telefono.includes(termino))
  );
});

// Función para seleccionar un cliente de la lista
const seleccionarCliente = (u) => {
  nuevaCita.value.cliente_id = u.id;
  busquedaCliente.value = u.nombre; // Ponemos el nombre en el input
  mostrarListaClientes.value = false; // Cerramos la lista
};



const abrirModalNuevaCita = () => {
  // Reiniciamos el objeto nuevaCita con valores por defecto
  nuevaCita.value = {
    cliente_id: null,
    servicio_id: null,
    peluquero_id: 8, // Por defecto Ruben
    fecha: new Date().toISOString().split('T')[0], // Fecha de hoy por defecto
    hora: '10:00',
    estado: 'PENDIENTE'
  };
  
  // Mostramos el modal
  mostrandoModalCrear.value = true;
};

// Listas para los selectores (deben venir de tu API)
const clientes = ref([]); // Lista de usuarios registrados


const cargarServicios = async () => {
  try {
    const response = await fetch('/backend/api/gestion_servicios.php');
    const data = await response.json();
    
    // Verificamos si la respuesta es el array directamente o viene dentro de una propiedad
    if (Array.isArray(data)) {
      servicios.value = data;
    } else if (data.servicios) {
      servicios.value = data.servicios;
    }
  } catch (error) {
    console.error("Error cargando servicios:", error);
    servicios.value = [];
  }
};

const cargarClientes = async () => {
  try {
    const response = await fetch('/backend/api/obtener_clientes.php');
    
    // Primero verificamos si la respuesta es OK
    if (!response.ok) throw new Error('Error en la red');

    const data = await response.json();
    

    // IMPORTANTE: Verifica si tu PHP devuelve 'clientes' o 'data'
    if (data && Array.isArray(data)) {
      clientes.value = data; 
    } else if (data.success && data.clientes) {
      clientes.value = data.clientes;
    } else {
      console.warn("Estructura de datos no reconocida", data);
    }
  } catch (error) {
    console.error("Error cargando clientes:", error);
  }
};


const crearReservaBackend = async () => {
// 1. Validación corregida: usamos cliente_id
  if (!nuevaCita.value.cliente_id || !nuevaCita.value.servicio_id || !nuevaCita.value.hora) {
    mostrarToast("Completa todos los campos obligatorios: Cliente, Servicio y Hora", "error");
    return;
  }

  cargandoGuardado.value = true; // Usa cargandoGuardado para el feedback del botón
  try {
    const res = await fetch('/backend/api/guardar_reserva_admin.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(nuevaCita.value)
    });

    const data = await res.json();

    if (data.success) {
      mostrandoModalCrear.value = false; // Cerramos el modal de creación
      await cargarCitas(); // Recargamos la agenda para ver la nueva cita
      mostrarToast("¡Cita guardada correctamente!", "exito");
    } else {
      mostrarToast("Error: " + (data.error || "No se pudo guardar"), "error");
    }
  } catch (error) {
    console.error("Error en la petición:", error);
    mostrarToast("Error de conexión con el servidor", "error");
  } finally {
    cargandoGuardado.value = false;
  }
};

watch(metodoPagoSeleccionado, (nuevoMetodo) => {
  if (nuevoMetodo === 'Deuda') {
    promocionSeleccionada.value = null; // Limpiamos la promo automáticamente
    console.log("Se ha seleccionado Deuda: Promo desactivada");
  }
});

// Función para cambiar a la vista de pago
const prepararPago = () => {
  editandoPago.value = true;
  metodoPagoSeleccionado.value = 'Efectivo'; // Por defecto
};

const cancelarPago = () => {
  editandoPago.value = false;
};

const prepararNuevaCita = () => {
  creandoCita.value = true;
  citaSeleccionada.value = {
    cliente: '',
    cliente_telefono: '',
    servicio_id: null,
    peluquero_id: 8, // Por defecto Ruben
    fecha: new Date().toISOString().split('T')[0], // Hoy
    hora: '10:30',
    estado: 'PENDIENTE'
  };
};

const finalizarCitaConPago = async () => {
  const datosPago = {
    id: citaSeleccionada.value.id,
    estado: 'COMPLETADA',
    metodo_pago: metodoPagoSeleccionado.value
  };

  try {
    // Reutilizamos tu función de enviar estado
    await enviarEstado(datosPago);
    
    // Actualización local para feedback inmediato
    citaSeleccionada.value.estado = 'COMPLETADA';
    
    cerrarModal();
    await cargarCitas(); // Recarga la agenda
  } catch (error) {
    console.error("Error al finalizar pago:", error);
    mostrarToast("No se pudo registrar el pago.", "error");
  }
};

const prepararAnulacion = () => {
  editandoAnulacion.value = true;
};

const cancelarAnulacion = () => {
  editandoAnulacion.value = false;
  motivoAnulacion.value = '';
};

// Dentro de <script setup>

const seleccionarFechaManual = (event) => {
  const valor = event.target.value; // Recibe "YYYY-MM-DD"
  if (!valor) return;

  const [year, month, day] = valor.split('-').map(Number);
  
  // IMPORTANTE: Al usar números (año, mes-1, día), 
  // JS crea el objeto en hora LOCAL 00:00:00, evitando saltos de día.
  const nuevaFecha = new Date(year, month - 1, day);
  
  // Forzamos que la hora sea exactamente medianoche local
  nuevaFecha.setHours(0, 0, 0, 0);
  
  fechaReferencia.value = nuevaFecha;
};

// Navegación
const nombreMesActual = computed(() => {
  const dias = diasVisibles.value;
  const inicioSemana = new Date(dias[0].fechaISO);
  const finSemana = new Date(dias[dias.length - 1].fechaISO);

  const mesInicio = inicioSemana.toLocaleString('es-ES', { month: 'long' });
  const mesFin = finSemana.toLocaleString('es-ES', { month: 'long' });
  const añoInicio = inicioSemana.getFullYear();
  const añoFin = finSemana.getFullYear();

  if (mesInicio !== mesFin) {
    if (añoInicio !== añoFin) {
      return `${mesInicio} de ${añoInicio} – ${mesFin} de ${añoFin}`;
    }
    return `${mesInicio} – ${mesFin} de ${añoInicio}`;
  }
  return `${mesInicio} de ${añoInicio}`;
});

const cambiarSemana = (dias) => {
  const nueva = new Date(fechaReferencia.value);
  // En móvil navegamos de 3 en 3, en desktop de 7 en 7
  const paso = isMobile.value ? (dias > 0 ? 3 : -3) : dias;
  nueva.setDate(nueva.getDate() + paso);
  fechaReferencia.value = nueva;
};

const irHoy = () => { fechaReferencia.value = new Date(); };

// Días de la semana
const diasSemana = computed(() => {
  const hoy = new Date(fechaReferencia.value);
  const dSemana = hoy.getDay(); 
  const dif = dSemana === 0 ? -6 : 1 - dSemana; 
  const lunes = new Date(hoy);
  lunes.setDate(hoy.getDate() + dif);

  return Array.from({ length: 7 }, (_, i) => {
    const d = new Date(lunes);
    d.setDate(lunes.getDate() + i);
    
    // SOLUCIÓN: Extraer componentes locales manualmente para evitar el desfase UTC
    const anio = d.getFullYear();
    const mes = String(d.getMonth() + 1).padStart(2, '0');
    const dia = String(d.getDate()).padStart(2, '0');
    const iso = `${anio}-${mes}-${dia}`; // Formato exacto YYYY-MM-DD local

    return {
      nombre: ['LUN', 'MAR', 'MIÉ', 'JUE', 'VIE', 'SÁB', 'DOM'][i],
      numero: d.getDate(),
      fechaISO: iso
    };
  });
});

// Vista de 3 días en móvil: ayer, hoy y mañana centrados en fechaReferencia
const diasVisibles = computed(() => {
  if (!isMobile.value) return diasSemana.value;
  const ref = new Date(fechaReferencia.value);
  return Array.from({ length: 3 }, (_, i) => {
    const d = new Date(ref);
    d.setDate(ref.getDate() + (i - 1)); // -1, 0, +1 (ayer, hoy, mañana)
    const iso = d.toISOString().split('T')[0];
    const nombres = ['DOM', 'LUN', 'MAR', 'MIÉ', 'JUE', 'VIE', 'SÁB'];
    return {
      nombre: nombres[d.getDay()],
      numero: d.getDate(),
      fechaISO: iso
    };
  });
});

const esHoy = (iso) => iso === new Date().toISOString().split('T')[0];

// Funciones del Modal
// ── Edición de cita ──
const prepararEdicion = () => {
  // Pre-rellenamos con los datos actuales de la cita
  citaEditada.value = {
    peluquero_id: citaSeleccionada.value.peluquero_id ?? null,
    servicio_id:  citaSeleccionada.value.servicio_id  ?? null,
    fecha:        citaSeleccionada.value.fechaOriginal ?? null,
    hora:         citaSeleccionada.value.inicio        ?? null,
  };
  horasEdicion.value = [];
  editandoCita.value = true;
  // Cargamos horas disponibles para los datos actuales
  cargarHorasEdicion();
};

const cargarHorasEdicion = async () => {
  const { peluquero_id, servicio_id, fecha } = citaEditada.value;
  if (!peluquero_id || !servicio_id || !fecha) return;
  cargandoHorasEdicion.value = true;
  try {
    // Pasamos exclude_reserva_id para que el backend excluya la cita actual del cálculo de bloqueos
    const resp = await fetch(
      `/backend/api/obtener_horas.php?fecha=${fecha}&servicio_id=${servicio_id}&peluquero_id=${peluquero_id}&exclude_reserva_id=${citaSeleccionada.value.id}`
    );
    const data = await resp.json();
    let horas = Array.isArray(data) ? data : [];
    // Añadimos la hora actual al listado si no está (porque el backend la excluye como bloqueo)
    const horaActual = citaSeleccionada.value.inicio; // formato HH:MM
    if (horaActual && !horas.includes(horaActual)) {
      horas = [horaActual, ...horas].sort();
    }
    horasEdicion.value = horas;
  } catch (e) {
    horasEdicion.value = [];
  } finally {
    cargandoHorasEdicion.value = false;
  }
};

const guardarEdicion = async () => {
  const { peluquero_id, servicio_id, fecha, hora } = citaEditada.value;
  if (!peluquero_id || !servicio_id || !fecha || !hora) return;

  try {
    const res = await fetch('/backend/api/guardar_reserva_admin.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        accion:       'modificar',
        id:           citaSeleccionada.value.id,
        peluquero_id,
        servicio_id,
        fecha,
        hora,
      })
    });
    const data = await res.json();
    if (data.success) {
      mostrarToast('Cita modificada correctamente', 'exito');
      cerrarModal();
      await cargarCitas();
    } else {
      mostrarToast('Error: ' + (data.error || 'No se pudo modificar'), 'error');
    }
  } catch (e) {
    mostrarToast('Error de conexión con el servidor', 'error');
  }
};

const abrirDetalles = (cita) => {
  const dia = diasVisibles.value.find(d => d.fechaISO === cita.fechaOriginal) || diasSemana.value.find(d => d.fechaISO === cita.fechaOriginal);
  citaSeleccionada.value = {
    ...cita,
    fechaFormateada: `${dia.nombre}, ${dia.numero} de ${nombreMesActual.value.split(' ')[0]}`
  };
};

const cerrarModal = () => {
  // 1. Quitamos la cita seleccionada (esto cierra el modal visualmente)
  citaSeleccionada.value = null;
  
  // 2. RESETEAMOS LOS ESTADOS INTERNOS
  // Esto garantiza que la próxima vez que abras CUALQUIER cita, 
  // aparezca siempre la vista de detalles principal.
  editandoAnulacion.value = false;
  editandoPago.value = false;
  editandoCita.value = false;
  citaEditada.value = { peluquero_id: null, servicio_id: null, fecha: null, hora: null };
  horasEdicion.value = [];
  
  // Opcional: resetear también los campos de entrada
  motivoAnulacion.value = '';
  metodoPagoSeleccionado.value = 'Efectivo';
  promocionSeleccionada.value = null;
};



// Cálculos de carga y diseño
const calcularTop = (hora) => {
  const [h, m] = hora.split(':').map(Number);
  return (h - HORA_INICIO_VISUAL) * 60 + m;
};



const cargarCitas = async () => {
  cargando.value = true;
  try {
    // Calculamos el rango de fechas visible para no traer datos innecesarios
    const dias = diasSemana.value; // Usamos siempre los 7 días base para no perder citas al cambiar de mobile a desktop
    const desde = dias[0].fechaISO;
    const hasta = dias[dias.length - 1].fechaISO;
    const response = await fetch(`/backend/api/gestion_reservas.php?desde=${desde}&hasta=${hasta}`);
    const data = await response.json();
    
    // Mapeamos los datos para que tengan la estructura que necesita el calendario
    citas.value = data.map(r => ({
      id: r.id,
      cliente: r.cliente_nombre,
      servicio: r.servicio_nombre,
      servicio_id: r.servicio_id,       // ← necesario para pre-rellenar edición
      peluquero: r.peluquero_nombre || 'Ruben gutierrez ibañez',
      peluquero_id: r.peluquero_id,     // ← necesario para pre-rellenar edición
      fechaOriginal: r.fecha,
      inicio: r.hora.substring(0, 5),
      duracion: parseInt(r.duracion) || 45,
      color: obtenerColorEstado(r.estado),
      cliente_telefono: r.cliente_telefono,
      precio: r.precio,
      estado: r.estado,
      metodo_pago: r.metodo_pago,
    }));
  } catch (e) { 
    console.error("Error al cargar:", e); 
  } finally {
    cargando.value = false;
  }
};


const calcularFin = (cita) => {
  const [h, m] = cita.inicio.split(':').map(Number);
  const totalMinutos = h * 60 + m + (cita.duracion || 45);
  const hFin = Math.floor(totalMinutos / 60);
  const mFin = totalMinutos % 60;
  return `${String(hFin).padStart(2, '0')}:${String(mFin).padStart(2, '0')}`;
};

// ── Colores por servicio (espejo del mapa de Google Calendar) ──
const COLORES_SERVICIO = {
  1: { bg: 'rgba(121, 134, 203, 0.25)', text: '#3949AB', border: 'rgb(121, 134, 203)' }, // Arreglo barba → morado/lavanda
  2: { bg: 'rgba(51, 182, 121, 0.25)',  text: '#1B7A4A', border: 'rgb(51, 182, 121)'  }, // Corte → verde
  3: { bg: 'rgba(3, 155, 229, 0.25)',   text: '#0277BD', border: 'rgb(3, 155, 229)'   }, // Corte + Barba → azul celeste
  4: { bg: 'rgba(51, 182, 121, 0.25)',  text: '#1B7A4A', border: 'rgb(51, 182, 121)'  }, // Mechas → (¿cuál es su color?)
};
const obtenerColorEstado = (estado) => {
  switch (estado.toUpperCase()) {
    case 'PENDIENTE':
      return '#1a73e8'; // Azul
    case 'COMPLETADA':
      return '#0b8043'; // Verde
    case 'ANULADA WEB':
      return '#f4511e'; // Naranja fuerte
    case 'ANULADA LOCAL':
      return '#70757a'; // Gris
    default:
      return '#039be5'; // Azul claro por defecto
  }
};

const obtenerEstiloEstado = (estado, servicio_id = null) => {
  // Si está pendiente y tiene color de servicio asignado, úsalo
  if (estado.toUpperCase() === 'PENDIENTE' && servicio_id && COLORES_SERVICIO[servicio_id]) {
    return COLORES_SERVICIO[servicio_id];
  }
  switch (estado.toUpperCase()) {
    case 'PENDIENTE':
      return { bg: '#D2E3FC', text: '#174EA6', border: '#1967D2' };
    case 'COMPLETADA':
      return { bg: '#CEEAD6', text: '#0D652D', border: '#1E8E3E' };
    case 'ANULADA WEB':
      return { bg: '#FAD2CF', text: '#A50E0E', border: '#D93025' };
    case 'ANULADA LOCAL':
      return { bg: '#E8EAED', text: '#3C4043', border: '#9AA0A6' };
    default:
      return { bg: '#E8F0FE', text: '#1967D2', border: '#4285F4' };
  }
};

const confirmarAnulacion = async () => {
  // 1. Validación de seguridad
  if (!motivoAnulacion.value.trim()) {
    mostrarToast("Introduce un motivo para la anulación.", "error");
    return;
  }

  // 2. Preparar los datos con la estructura que espera tu API
  const datosParaEnviar = { 
    id: citaSeleccionada.value.id, 
    estado: 'ANULADA LOCAL', 
    motivo: motivoAnulacion.value 
  };

  try {
    // 3. Usamos tu función existente
    await enviarEstado(datosParaEnviar);

    // 4. Actualización Visual (Frontend)
    // Cambiamos el estado localmente para que el color pase a Gris Pastel
    citaSeleccionada.value.estado = 'ANULADA LOCAL';
    
    // 5. Limpieza y cierre
    editandoAnulacion.value = false;
    motivoAnulacion.value = '';
    cerrarModal(); // Cerramos el modal de detalles

    // 6. Recargar el calendario para que la tarjeta se vea gris en el fondo
    await cargarCitas();

  } catch (error) {
    console.error("Error al procesar la anulación:", error);
    mostrarToast("Hubo un error al intentar anular la cita.", "error");
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

// --- FUNCIÓN PARA REFRESCAR LOS DATOS ---
const refrescarAgenda = async () => {
  if (cargando.value) return; // Evitar múltiples clics
  cargando.value = true;
  
  
  try {
    // Aquí llamas a tu función que hace el fetch a gestion_reservas.php?accion=get_citas o similar
    // Asegúrate de pasar el término de búsqueda si tu backend ya filtra
    await cargarCitas(); 
  } catch (error) {
    console.error("Error al refrescar:", error);
  } finally {
    cargando.value = false;
  }
};

// --- LÓGICA DE FILTRADO (FRONTEND) ---
const citasFiltradas = computed(() => {
  return citas.value.filter(cita => {
    // A. Filtro por Estado
    const coincideEstado = filtroEstado.value === 'TODAS' || cita.estado === filtroEstado.value;
    
    // B. Filtro por Texto — usamos los nombres correctos del mapeo: .cliente y .servicio
    const textoBusqueda = (busquedaCliente.value || '').toLowerCase();
    const nombre = (cita.cliente || '').toLowerCase();
    const servicio = (cita.servicio || '').toLowerCase();

    const coincideBusqueda = !textoBusqueda || 
                             nombre.includes(textoBusqueda) || 
                             servicio.includes(textoBusqueda);

    return coincideEstado && coincideBusqueda;
  });
});

// Añade esta función al script
const ocultarResultadosConRetraso = () => {
  setTimeout(() => {
    clientesFiltrados.value = [];
  }, 200);
};

const cerrarBuscadorConRetraso = () => {
  setTimeout(() => {
    cerrarBuscador();
  }, 200);
};

watch(
  () => [nuevaCita.value.fecha, nuevaCita.value.servicio_id, nuevaCita.value.peluquero_id],
  () => {
    cargarHorasDisponibles();
  }
);

// Recargamos citas cuando el usuario navega a otra semana
watch(fechaReferencia, () => {
  cargarCitas();
});

onMounted(() => {
  cargarCitas();
  cargarClientes(); 
  cargarPeluquerosYDetectarSesion();
  cargarServicios();
  setInterval(() => {
    const ahora = new Date();
    lineaTop.value = (ahora.getHours() - HORA_INICIO_VISUAL) * 60 + ahora.getMinutes();
  }, 60000);
});
</script>

<style scoped>
/* --- CONTENEDOR GENERAL --- */
.calendar-container {
  display: flex; flex-direction: column; height: 90vh;
  background: white; font-family: 'Roboto', Arial, sans-serif;
}

/* --- NAVEGACIÓN SUPERIOR --- */
.calendar-nav-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 16px;
  border-bottom: 1px solid #dadce0;
  background-color: white;
}
.nav-left {
  display: flex;
  align-items: center;
  gap: 8px; /* Espacio reducido entre elementos */
}

.nav-arrows {
  display: flex;
  align-items: center;
  margin: 0 8px; /* Separación entre el botón Hoy y el título */
}

.btn-icon {
  background: none;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  color: #5f6368;
  font-family: monospace; /* Para que < y > se vean simétricos */
  font-size: 18px;
  transition: background 0.2s;
}

.btn-today {
  border: 1px solid #dadce0; background: white; padding: 6px 16px;
  border-radius: 4px; cursor: pointer; font-weight: 500;
}

.btn-icon:hover {
  background-color: #f1f3f4;
}

.current-month {
  font-size: 14px;
  font-weight: 400;
  color: #3c4043;  
  text-transform: capitalize;
}

/* --- CABECERA DE DÍAS --- */
.calendar-header {
  display: grid; grid-template-columns: 60px repeat(7, 1fr);
  border-bottom: 1px solid #dadce0; padding: 12px 0;
  background: white; position: sticky; top: 0; z-index: 100;
}
.time-column-header { display: flex; align-items: flex-end; justify-content: center; padding-bottom: 12px; }
.timezone-label { font-size: 10px; color: #70757a; letter-spacing: 0.5px; }
.day-column-header { display: flex; flex-direction: column; align-items: center; }
.day-name { font-size: 11px; font-weight: 500; color: #70757a; text-transform: uppercase; margin-bottom: 4px; }
.day-number {
  font-size: 24px; color: #3c4043; width: 44px; height: 44px;
  display: flex; align-items: center; justify-content: center; border-radius: 50%;
}
.today-circle { background-color: #1a73e8; color: white !important; }

/* --- CUERPO DEL CALENDARIO --- */
.calendar-body {
  display: grid; grid-template-columns: 60px repeat(7, 1fr);
  overflow-y: auto; position: relative; flex-grow: 1;
}
.time-column { border-right: 1px solid #dadce0; }
.hour-slot { height: 60px; position: relative; }
.hour-text { position: absolute; top: -8px; right: 8px; font-size: 10px; color: #70757a; }
.day-column { border-right: 1px solid #e8eaed; position: relative; }
.hour-grid-cell { height: 60px; border-bottom: 1px solid #e8eaed; }

/* --- CITAS (TARJETAS) --- */
.cita-card {
  position: absolute;
  left: 2px;
  right: 2px;
  border-radius: 4px;
  padding: 2px 6px;
  color: white;
  z-index: 10;
  cursor: pointer;
  box-shadow: 0 1px 2px rgba(0,0,0,0.1);
  border-left: 3px solid rgba(0, 0, 0, 0.2);
  display: flex;
  flex-direction: column;
  overflow: hidden; /* Crucial para que no se desborde el texto */
  transition: transform 0.1s;
}
.cita-card:hover { z-index: 50;
  filter: brightness(1.05); }
.cita-hora-text { font-size: 10px; font-weight: 700; }
.cita-cliente-name { font-size: 12px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.badge-vip-pill { background-color: #fbbc04; color: #3c4043; font-size: 9px; padding: 0 4px; border-radius: 10px; }

/* --- INDICADOR HORA ACTUAL --- */
.now-indicator { position: absolute; left: 60px; right: 0; height: 2px; background: #ea4335; z-index: 50; pointer-events: none; }
.now-circle { width: 12px; height: 12px; background: #ea4335; border-radius: 50%; position: absolute; left: -6px; top: -5px; }

/* --- MODAL (ESTILO GOOGLE CALENDAR) --- */
.modal-overlay {
  position: fixed; top: 0; left: 0; width: 100%; height: 100%;
  background: rgba(0,0,0,0.1); z-index: 1000;
  display: flex; align-items: center; justify-content: center;
}
.modal-content {
  background: white; width: 448px; border-radius: 8px;
  box-shadow: 0 24px 38px 3px rgba(0,0,0,0.14), 0 9px 46px 8px rgba(0,0,0,0.12);
  padding: 8px 8px 20px 8px; animation: scaleIn 0.2s ease-out;
  transition: background-color 0.3s ease; /* Para que el cambio de color sea suave */
  
transition: background-color 0.3s ease;

  /* TRUCO MAESTRO: Usamos 'color-mix' para mezclar el color del estado con blanco.
     Esto crea el tono PASTEL exacto (15% del color original y 85% de blanco).
     Al ser una mezcla sólida, NO se ve transparente ni se ensucia con el fondo.
  */
  background-color: v-bind('citaSeleccionada ? `color-mix(in srgb, ${citaSeleccionada.color}, white 85%)` : "#ffffff"');

  /* Añadimos un borde un poco más oscuro para que el modal "salte" de la pantalla */
  border: 1px solid v-bind('citaSeleccionada ? `color-mix(in srgb, ${citaSeleccionada.color}, black 10%)` : "#dadce0"');

}
.modal-header-actions { display: flex; justify-content: flex-end; padding-bottom: 8px; }
.btn-icon-alt {
  background: none; border: none; cursor: pointer; padding: 8px;
  border-radius: 50%; color: #5f6368; display: flex; align-items: center;
}
.btn-icon-alt:hover { background: #f1f3f4; }
.btn-icon-alt .material-icons { font-size: 20px; }

.modal-body { padding: 0; }
.detail-row {
  display: flex; align-items: flex-start; gap: 16px;
  padding: 12px 16px; color: #3c4043;
}
.main-title { padding-top: 0; margin-bottom: 8px; }
.main-title h3 { margin: 0; font-size: 22px; font-weight: 400; line-height: 28px; }
.detail-subtitle { margin: 4px 0 0 0; color: #70757a; font-size: 14px; }

/* Cuadrado de color junto al título */
.color-dot { width: 14px; height: 14px; border-radius: 4px; margin-top: 8px; flex-shrink: 0; }


/* Animaciones y Transiciones */
@keyframes scaleIn {
  from { transform: scale(0.8); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.cita-inner-content {
  display: flex;
  flex-direction: column;
  height: 100%;
  pointer-events: none;
}

.cita-info-line {
  display: flex;
  align-items: center;
  gap: 4px;
  white-space: nowrap;
  overflow: hidden;
}

.cita-hora-text {
  font-size: 11px;
  font-weight: 700;
  flex-shrink: 0; /* Evita que la hora se encoja */
}

.cita-cliente-name {
  font-size: 11px;
  font-weight: 500;
  text-overflow: ellipsis;
  overflow: hidden;
}

.cita-servicio-text {
  font-size: 10px;
  opacity: 0.9;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-top: -1px;
}

.info-icon {
  color: #5f6368; /* Gris estándar de Google */
  font-size: 20px;
  width: 24px;
  flex-shrink: 0;
}

.detail-row {
  display: flex;
  align-items: center; /* Centra verticalmente icono y texto */
  gap: 16px;
  padding: 8px 16px; /* Un poco menos de padding vertical para que no ocupe tanto */
}

.servicio-precio-container {
  display: flex;
  align-items: center;
  justify-content: space-between; /* Empuja el precio a la derecha si hay espacio */
  width: 100%;
}

.cita-precio-tag {
  background-color: #f1f3f4; /* Gris muy clarito */
  color: #3c4043;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 13px;
  margin-left: 10px;
  border: 1px solid #dadce0;
}

.estado-container {
  display: flex;
  align-items: center;
  gap: 8px;
}

.estado-label {
  font-size: 14px;
  color: #70757a; /* Gris secundario */
}

.estado-valor {
  font-size: 12px;
  font-weight: 700;
  padding: 2px 10px;
  border-radius: 4px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  /* El background dinámico con '15' añade un 8% de transparencia al color original */
}

/* Ajuste general para que las filas no estén tan pegadas */
.detail-row {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 8px 16px;
}

.cita-card[style*="#70757a"], 
.cita-card[style*="#f4511e"] {
  opacity: 0.7;
  border-left-style: dashed; /* Opcional: borde discontinuo para anuladas */
}

/* --- CONTENEDOR ANULACIÓN INTERNA --- */
.modal-anular-interna {
  padding: 10px 24px 24px 24px;
}

.titulo-anular {
  font-size: 22px;
  font-weight: 500;
  color: #3c4043;
  margin: 0 0 8px 0;
}

.subtitulo-anular {
  font-size: 14px;
  color: #5f6368;
  margin-bottom: 20px;
  line-height: 1.5;
}

/* --- ESTILO DEL TEXTAREA (MOTIVO) --- */
.campo-motivo {
  margin-bottom: 24px;
}

.campo-motivo label {
  display: block;
  font-size: 13px;
  font-weight: 700;
  color: #70757a;
  margin-bottom: 8px;
}

.campo-motivo textarea {
  width: 100%;
  border: 1px solid #dadce0;
  border-radius: 8px;
  padding: 12px;
  font-family: 'Roboto', Arial, sans-serif;
  font-size: 14px;
  color: #3c4043;
  background-color: #f8fafc;
  resize: none; /* Evita que el usuario lo deforme */
  transition: border-color 0.2s, box-shadow 0.2s;
  box-sizing: border-box; /* Crucial para que el padding no lo ensanche */
}

.campo-motivo textarea:focus {
  outline: none;
  border-color: #1a73e8;
  box-shadow: 0 0 0 2px rgba(26, 115, 232, 0.2);
  background-color: white;
}

/* --- BOTONES DE ACCIÓN --- */
.acciones-anular {
  display: flex;
  justify-content: flex-end; /* Alinea los botones a la derecha como Google */
  gap: 12px;
}

/* Botón Confirmar (Rojo Pastel/Google) */
.btn-confirmar {
  background-color: #ea4335;
  color: white;
  border: none;
  padding: 10px 24px;
  border-radius: 4px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s, box-shadow 0.2s;
}

.btn-confirmar:hover {
  background-color: #d93025;
  box-shadow: 0 1px 3px rgba(60, 64, 67, 0.3);
}

/* Botón Volver (Estilo Secundario) */
.btn-volver {
  background-color: white;
  color: #1a73e8;
  border: 1px solid #dadce0;
  padding: 10px 24px;
  border-radius: 4px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-volver:hover {
  background-color: #f8f9fa;
  border-color: #d2e3fc;
}

/* Animación de entrada suave */
.fade-in {
  animation: modalFadeIn 0.3s ease-out;
}

@keyframes modalFadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Botones deshabilitados */
.btn-icon-alt:disabled {
  opacity: 0.2; /* Mucho más tenue */
  cursor: not-allowed;
  pointer-events: none;
  filter: grayscale(100%);
}

/* Opcional: añadir un estilo visual al "Badge" del estado para resaltar que es definitivo */
.estado-valor {
  font-weight: bold;
  padding: 4px 12px;
  border-radius: 4px;
  /* Si no es pendiente, le damos un toque más sólido */
  box-shadow: v-bind('citaSeleccionada?.estado !== "PENDIENTE" ? "inset 0 0 0 1px rgba(0,0,0,0.1)" : "none"');
}
/* --- ESTILOS DE LA BARRA DE HERRAMIENTAS --- */
.agenda-toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 24px;
  background-color: white;
  border-bottom: 1px solid #e0e0e0;
  margin-bottom: 20px;
  border-radius: 8px 8px 0 0;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.toolbar-left, .toolbar-right {
  display: flex;
  align-items: center;
  gap: 12px;
}

.toolbar-title {
  margin: 0;
  font-size: 24px;
  font-weight: 700;
  color: #1a202c;
}

.citas-total-badge {
  background-color: #ffe4e6; /* Rosa suave de la imagen */
  color: #c53030; /* Rojo más intenso */
  padding: 4px 10px;
  border-radius: 16px;
  font-size: 12px;
  font-weight: 600;
}

/* --- BOTONES DE LA BARRA --- */
.btn-toolbar {
  background: none;
  border: 1px solid #dadce0;
  border-radius: 8px;
  padding: 4px;
  cursor: pointer;
  color: #5f6368;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.2s, border-color 0.2s;
}

.btn-toolbar:hover {
  background-color: #f8f9fa;
  border-color: #d2e3fc;
  color: #1a73e8;
}

/* Efecto de carga (spin) */
@keyframes iconSpin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.icon-spin {
  animation: iconSpin 1.5s linear infinite;
}

.btn-loading {
  opacity: 0.6;
  pointer-events: none;
}

/* --- INPUT DE BÚSQUEDA --- */
.busqueda-container {
  position: relative;
  display: flex;
  align-items: center;
}

.search-icon {
  position: absolute;
  left: 10px;
  font-size: 20px;
  color: #dadce0; /* Color gris suave como en la imagen */
}

.input-busqueda {
  border: 1px solid #dadce0;
  border-radius: 8px;
  padding: 10px 10px 10px 36px; /* Padding izquierdo para el icono */
  font-size: 14px;
  color: #3c4043;
  width: 250px;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.input-busqueda:focus {
  outline: none;
  border-color: #1a73e8;
  box-shadow: 0 0 0 2px rgba(26, 115, 232, 0.1);
}

.input-busqueda::placeholder {
  color: #dadce0; /* Gris muy clarito como en la imagen */
}

.input-busqueda:not(:placeholder-shown) {
    border-color: #fb7185; /* Un tono rosado como tu tema */
    background-color: #fff1f2;
}

.clear-icon {
    position: absolute;
    right: 10px;
    cursor: pointer;
    font-size: 18px;
    color: #9ca3af;
}
/* Contenedor principal */
.modal-pago-interna {
  padding: 15px 15px;
  max-width: 380px;
  margin: 0 auto;
  min-height: 280px; /* Evita que el modal "salte" demasiado al ocultar promos */
}

/* Títulos */
.titulo-pago-mini {
  font-size: 20px;
  font-weight: 800;
  color: #1e293b; /* Azul muy oscuro casi negro */
  margin-bottom: 4px;
}

.cliente-pago-nombre-mini {
  font-size: 16px;
  color: #f06292; /* Rosa de la imagen */
  font-weight: 600;
  margin-bottom: 20px;
  text-transform: lowercase; /* Siguiendo el estilo de tu imagen */
}

/* Grid de Métodos */
.grid-metodos-pago-compacto {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
  margin-bottom: 20px;
}

.metodo-card-mini {
  border: 2px solid #f1f5f9;
  border-radius: 16px; /* Bordes más redondeados como la imagen */
  padding: 12px 8px;
  cursor: pointer;
  background: white;
  transition: all 0.2s ease;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
}

/* ESTADO ACTIVO (Seleccionado) */
.metodo-card-mini.activo {
  border-color: #f06292;
  background-color: #fdf2f8; /* Fondo rosado muy tenue */
}

/* Iconos de los métodos */
.icon-mini {
  font-size: 24px !important;
  padding: 10px;
  border-radius: 50%;
  background: #f8fafc;
  color: #64748b;
  transition: all 0.2s;
}

.metodo-card-mini.activo .icon-mini {
  background: #f06292;
  color: white;
  box-shadow: 0 4px 10px rgba(240, 98, 146, 0.3);
}

.metodo-texto {
  font-size: 13px;
  font-weight: 700;
  color: #64748b;
}

.metodo-card-mini.activo .metodo-texto {
  color: #f06292;
}

/* Promociones */
.promociones-section-mini {
  margin-top: 10px;
  padding-top: 15px;
  border-top: 1px dashed #e2e8f0;
  margin-bottom: 20px;
}

.promo-label-mini {
  font-size: 11px;
  font-weight: 800;
  color: #94a3b8;
  letter-spacing: 1px;
  margin-bottom: 10px;
  text-transform: uppercase;
}

.promo-card-mini {
  padding: 12px;
  background: white;
  border: 2px solid #f1f5f9;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.promo-info-mini {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 13px;
  font-weight: 700;
  color: #1e293b;
}

/* Botones de acción */
.acciones-pago-compactas {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.btn-finalizar-pago-mini {
  background-color: #4ade80; /* Verde vibrante de la imagen */
  color: white;
  border: none;
  padding: 14
}

/* Contenedor del Grid en una sola fila */
.grid-metodos-pago-horizontal {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 8px;
  margin-bottom: 20px;
}

.metodo-card-horizontal {
  border: 2px solid #f1f5f9;
  border-radius: 12px;
  padding: 10px 4px;
  cursor: pointer;
  background: white;
  display: flex;
  flex-direction: column;
  align-items: center;
  transition: all 0.2s ease;
}

.metodo-card-horizontal.activo {
  border-color: #f06292;
  background-color: #fdf2f8;
}

/* Envoltorio del icono más pequeño */
.icon-wrapper-mini {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: #f8fafc;
  margin-bottom: 6px;
}

.metodo-card-horizontal.activo .icon-wrapper-mini {
  background: #f06292;
  color: white;
}

.icon-wrapper-mini .material-icons {
  font-size: 18px !important;
}

.metodo-texto-extra-mini {
  font-size: 11px;
  font-weight: 700;
  color: #64748b;
  margin: 0;
}

.icon-wrapper-mini .material-icons {
  font-size: 18px !important; /* Icono más pequeño para que quepa */
  color: #64748b;
}


.metodo-card-horizontal.activo .material-icons {
  color: white;
}

/* Texto extra pequeño para la fila horizontal */
.metodo-texto-extra-mini {
  font-size: 11px;
  font-weight: 700;
  color: #64748b;
  margin: 0;
  white-space: nowrap;
}

.metodo-card-horizontal.activo .metodo-texto-extra-mini {
  color: #f06292;
}

/* Ajuste del modal para que no se vea vacío al ser horizontal */
.modal-pago-interna {
  padding: 15px 15px;
  max-width: 380px; /* Ampliamos ligeramente el ancho para la fila de 4 */
  margin: 0 auto;
}

.custom-radio-display .material-icons {
  color: #cbd5e1; /* Gris cuando no está marcado */
  transition: color 0.2s;
  font-size: 22px;
}

.promo-activa .custom-radio-display .material-icons {
  color: #4ade80; /* Verde cuando está marcado */
}

.promo-card-mini {
  user-select: none; /* Evita que el texto se seleccione al hacer muchos clics */
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px;
  cursor: pointer;
  border: 2px solid #f1f5f9;
  border-radius: 12px;
  transition: all 0.2s ease;
}

.promo-card-mini.promo-activa {
  border-color: #4ade80;
  background-color: #f0fdf4;
}

.estado-columna-flex {
  display: flex;
  flex-direction: column;
  gap: 6px;
  align-items: flex-start;
}

.tag-deuda-modal {
  display: flex;
  align-items: center;
  gap: 5px;
  background-color: #fef2f2; /* Fondo rojizo suave */
  color: #dc2626;            /* Texto rojo alerta */
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 800;
  border: 1px solid #fecaca;
  text-transform: uppercase;
}

.tag-deuda-modal .material-icons {
  font-size: 14px;
}

.btn-nueva-cita {
  background-color: #10b981; /* Verde esmeralda */
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  font-weight: bold;
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  margin-left: 10px;
  transition: background 0.2s;
}

.btn-nueva-cita:hover {
  background-color: #059669;
}

.form-group { margin-bottom: 15px; }
.form-group label { display: block; font-size: 12px; font-weight: bold; margin-bottom: 5px; }
.form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }

.modal-calendar-style {
  max-width: 450px;
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}

.modal-header-simple {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 15px 20px;
  border-bottom: 1px solid #eee;
}

.input-moderno {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 14px;
  background: #f9f9f9;
  transition: border-color 0.2s;
}

.input-moderno:focus {
  border-color: #1a73e8; /* Azul Google */
  outline: none;
  background: white;
}

.btn-guardar-event {
  background-color: #1a73e8;
  color: white;
  border: none;
  padding: 10px 24px;
  border-radius: 20px;
  font-weight: 600;
  cursor: pointer;
  margin: 15px 20px;
  float: right;
}

.btn-guardar-event:disabled {
  background-color: #ccc;
}

.btn-add-event {
display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    
    /* Cambios para igualar aspecto */
    background: none;            /* Quitamos el fondo verde */
    border: 1px solid #dadce0;   /* Borde gris fino */
    border-radius: 8px;          /* Menos redondeado, igual que .btn-toolbar */
    color: #5f6368;              /* Texto/Icono gris */
    
    /* Tamaño pequeño */
    padding: 4px;                /* Padding igual al toolbar */
    font-size: 0.8rem;
    
    cursor: pointer;
    margin: 0 10px;
    transition: background-color 0.2s, border-color 0.2s;
    box-shadow: none;
}

.btn-add-event:hover {
 background-color: #f1f3f4;
    border-color: #ccc;
    color: #10b981;
}

.btn-add-event:active {
  transform: translateY(0);
}

.btn-add-event .material-icons {
  font-size: 20px;
}

/* Fondo oscuro translúcido */
.modal-overlay-calendar {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

/* Contenedor del Modal */
.modal-content-calendar {
  background: white;
  width: 448px;
  border-radius: 8px;
  box-shadow: 0 24px 38px 3px rgba(0,0,0,0.14), 0 9px 46px 8px rgba(0,0,0,0.12);
  overflow: hidden;
  position: relative;
  padding: 8px 0 24px 0;
}

/* Cabecera y Botón X */
.modal-header-calendar {
  display: flex;
  justify-content: flex-end;
  padding: 8px 12px;
}

.btn-close-x {
  background: none;
  border: none;
  color: #5f6368;
  cursor: pointer;
  padding: 8px;
  border-radius: 50%;
  transition: background 0.2s;
}

.btn-close-x:hover {
  background: #f1f3f4;
}

/* Cuerpo del Formulario */
.modal-body-calendar {
  padding: 0 24px 16px 24px;
}

.input-title-calendar {
  width: 100%;
  border: none;
  border-bottom: 2px solid #1a73e8;
  font-size: 22px;
  padding: 8px 0;
  margin-bottom: 24px;
  color: #3c4043;
}

.input-title-calendar:focus {
  outline: none;
}

.form-row-icon {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 16px;
}

.icon-label {
  color: #5f6368;
  font-size: 20px;
}

.select-calendar, .input-date-calendar {
  flex: 1;
  border: 1px solid transparent;
  padding: 10px;
  border-radius: 4px;
  font-size: 14px;
  background: #f1f3f4;
  color: #3c4043;
  cursor: pointer;
}

.select-calendar:hover {
  background: #e8eaed;
}

.datetime-grid {
  display: flex;
  gap: 8px;
  flex: 1;
}

/* Botón Guardar */
.modal-footer-calendar {
  padding: 0 24px;
  display: flex;
  justify-content: flex-end;
}

.btn-save-calendar {
  background-color: #1a73e8;
  color: white;
  border: none;
  padding: 10px 24px;
  border-radius: 4px;
  font-weight: 500;
  font-size: 14px;
  cursor: pointer;
  box-shadow: 0 1px 2px rgba(60,64,67,0.3);
  transition: background 0.2s;
}

.btn-save-calendar:hover {
  background-color: #1765cc;
  box-shadow: 0 1px 3px rgba(60,64,67,0.3);
}

.modal-title-simple {
  margin: 0;
  font-size: 18px;
  color: #3c4043;
  font-weight: 500;
  flex-grow: 1;
  padding-left: 12px;
}

.modal-header-calendar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 12px 8px 12px;
  border-bottom: 1px solid #e8eaed;
  margin-bottom: 20px;
}

/* Para que el botón de guardar se vea igual que el de tu imagen */
.btn-save-calendar {
  background-color: #1a73e8; /* Azul Google */
  color: white;
  border: none;
  padding: 8px 24px;
  border-radius: 4px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-save-calendar:disabled {
  background-color: #f1f3f4;
  color: #9aa0a6;
  cursor: not-allowed;
}

.buscador-cliente-container {
  position: relative;
  flex: 1;
}

.input-calendar-search {
  width: 100%;
  border: 1px solid transparent;
  padding: 10px;
  border-radius: 4px;
  font-size: 14px;
  background: #f1f3f4;
  color: #3c4043;
  outline: none;
}

.input-calendar-search:focus {
  background: white;
  border: 1px solid #1a73e8;
  box-shadow: 0 1px 2px rgba(60,64,67,0.3);
}

.dropdown-clientes {
  position: absolute;
  top: 100%;
  left: 0;
  width: 100%;
  background: white;
  border-radius: 4px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  z-index: 100;
  max-height: 200px;
  overflow-y: auto;
  margin-top: 4px;
  padding: 0;
  list-style: none;
}

.dropdown-clientes li {
  padding: 10px 15px;
  cursor: pointer;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #f1f3f4;
}

.dropdown-clientes li:hover {
  background-color: #f8f9fa;
}

.cli-nombre {
  font-weight: 500;
  color: #3c4043;
}

.cli-tel {
  font-size: 12px;
  color: #70757a;
  margin-left: 2px;
}

.dropdown-no-results {
  position: absolute;
  top: 100%;
  width: 100%;
  background: white;
  padding: 10px;
  font-size: 13px;
  color: #70757a;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}


.user-badge-mini {
  font-size: 11px;
  color: #70757a;
  margin-top: 4px;
  padding-left: 2px;
}

.user-badge-mini strong {
  color: #1a73e8;
}

.dropdown-clientes {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: white;
  border: 1px solid #dadce0;
  border-radius: 0 0 8px 8px;
  box-shadow: 0 4px 6px rgba(32,33,36,0.28);
  z-index: 2000; /* Por encima de todo en el modal */
  max-height: 250px;
  overflow-y: auto;
  margin: 0;
  padding: 4px 0;
  list-style: none;
}

.dropdown-clientes li {
  padding: 8px 16px;
  transition: background 0.2s;
}

.dropdown-clientes li:hover {
  background-color: #f1f3f4;
}
.select-calendar-peluquero {
  background-color: #e8f0fe; /* Azul claro estilo Google para campos auto-rellenados */
  border: 1px solid #1a73e8;
  color: #1a73e8;
  font-weight: 600;
  padding: 8px;
  border-radius: 4px;
  width: 100%;
}

.select-calendar-peluquero:focus {
  background: white;
  border-color: #1a73e8;
  outline: none;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.sesion-tag {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 11px;
  color: #1a73e8;
  margin-top: 4px;
  padding-left: 5px;
}

.dot-active {
  width: 6px;
  height: 6px;
  background: #10b981; /* Verde éxito */
  border-radius: 50%;
}

.select-calendar-pro {
  width: 100%;
  padding: 10px;
  border: 1px solid #dadce0;
  border-radius: 4px;
  font-size: 14px;
  background-color: white;
  color: #3c4043;
  appearance: none; /* Quita la flecha por defecto en algunos navegadores */
  background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%235f6368'%3e%3cpath d='M7 10l5 5 5-5z'/%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 8px center;
  background-size: 20px;
}

.select-calendar-pro:focus {
  border: 2px solid #1a73e8;
  padding: 9px; /* Ajuste para compensar el borde de 2px */
  outline: none;
}

.spinner-chico {
  position: absolute;
  right: 35px;
  top: 12px;
  width: 14px;
  height: 14px;
  border: 2px solid #f3f3f3;
  border-top: 2px solid #1a73e8;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.datetime-grid {
  display: grid;
  grid-template-columns: 1fr 1fr; /* Divide el espacio en dos columnas iguales */
  gap: 12px;
  width: 100%;
}

.input-calendar-date {
  width: 100%;
  padding: 8px 10px;
  border: 1px solid #dadce0;
  border-radius: 4px;
  font-size: 14px;
  color: #3c4043;
  font-family: inherit;
}

.input-calendar-date:focus {
  border: 2px solid #1a73e8;
  padding: 7px 9px; /* Compensación de borde */
  outline: none;
}


.filter-container-google {
  display: flex;
  align-items: center;
  margin-right: 15px;
  gap: 8px;
}

.filter-label {
  font-size: 13px;
  color: #5f6368;
  font-weight: 500;
}

.select-calendar-filter {
  padding: 6px 10px;
  border: 1px solid #dadce0;
  border-radius: 4px;
  font-size: 12px;
  color: #3c4043;
  background-color: #f8f9fa;
  cursor: pointer;
  transition: background-color 0.2s;
}

.select-calendar-filter:focus {
  outline: none;
  border-color: #1a73e8;
  background-color: #fff;
}

.select-calendar-filter:hover {
  background-color: #f1f3f4;
}


/* Reutilizamos tu estilo de botón de toolbar pero aplicado al nuevo botón */
.btn-add-user[data-v-70c01807] {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    background: none;
    border: 1px solid #dadce0;
    border-radius: 8px;
    color: #5f6368;
    padding: 4px;
    cursor: pointer;
    margin-left: 5px; /* Pequeño espacio respecto al botón de cita */
    transition: background-color 0.2s, border-color 0.2s;
}

.btn-add-user[data-v-70c01807]:hover {
    background-color: #f1f3f4;
    border-color: #ccc;
    color: #1a73e8; /* Azul para diferenciar que es de usuario */
}

.btn-add-user[data-v-70c01807] .material-icons {
    font-size: 20px;
}

/* =============================================
   RESPONSIVE — TABLET (≤ 768px)
   ============================================= */
@media (max-width: 768px) {

  /* Navbar: apilamos en dos filas */
  .calendar-nav-bar {
    flex-wrap: wrap;
    gap: 8px;
    padding: 8px 10px;
  }

  .nav-left {
    flex: 1 1 100%;
    gap: 6px;
  }

  .toolbar-left {
    display: none; /* Badge de citas totales, no crítico en mobile */
  }

  .toolbar-right {
    flex: 1 1 100%;
    gap: 6px;
    justify-content: flex-start;
  }

  /* Buscador ocupa todo el ancho disponible */
  .busqueda-container {
    flex: 1;
  }

  .input-busqueda {
    width: 100%;
    min-width: 0;
  }

  /* Ocultamos label "Estado:" y dejamos solo el select */
  .filter-label {
    display: none;
  }

  /* Cabecera de días: número de día más pequeño */
  .day-number {
    font-size: 16px;
    width: 32px;
    height: 32px;
  }

  .day-name {
    font-size: 9px;
  }

  /* Columna de horas más estrecha */
  .calendar-header {
    grid-template-columns: 40px repeat(7, 1fr);
  }

  .calendar-body {
    grid-template-columns: 40px repeat(7, 1fr);
  }

  .time-column-header {
    padding-bottom: 6px;
  }

  .hour-text {
    font-size: 8px;
    right: 4px;
  }

  /* Tarjetas de cita más compactas */
  .cita-hora-text {
    font-size: 9px;
  }

  .cita-cliente-name {
    font-size: 9px;
  }

  .cita-servicio-text {
    font-size: 8px;
  }

  /* Modal a pantalla completa en tablet */
  .modal-content {
    width: 95vw;
    max-height: 90vh;
    overflow-y: auto;
  }

  .modal-content-calendar {
    width: 95vw;
    max-height: 90vh;
    overflow-y: auto;
  }
}

/* =============================================
   RESPONSIVE — MÓVIL (≤ 480px)
   ============================================= */
@media (max-width: 480px) {

  /* Contenedor principal: altura completa */
  .calendar-container {
    height: 100dvh;
  }

  /* Navbar compacta */
  .calendar-nav-bar {
    padding: 6px 8px;
  }

  .btn-today {
    padding: 5px 10px;
    font-size: 12px;
  }

  .current-month {
    font-size: 13px;
  }

  /* Móvil: 3 columnas de días */
  .calendar-header {
    grid-template-columns: 36px repeat(3, 1fr);
  }

  .calendar-body {
    grid-template-columns: 36px repeat(3, 1fr);
  }

  /* Ocultamos la etiqueta GMT en móvil para ganar espacio */
  .timezone-label {
    display: none;
  }

  /* Número de día aún más pequeño */
  .day-number {
    font-size: 13px;
    width: 26px;
    height: 26px;
  }

  .day-name {
    font-size: 8px;
  }

  /* Tarjetas de cita mínimas */
  .cita-card {
    padding: 1px 3px;
  }

  .cita-hora-text {
    font-size: 8px;
  }

  .cita-cliente-name {
    font-size: 8px;
  }

  /* Modal detalle de cita: bottom sheet compacto */
  .modal-overlay {
    align-items: flex-end;
  }

  .modal-content {
    width: 100vw;
    border-radius: 16px 16px 0 0;
    max-height: 58dvh;   /* Ocupa poco más de la mitad de la pantalla */
    overflow-y: auto;
    padding: 4px 4px 12px 4px;
  }

  /* Header de acciones más compacto */
  .modal-header-actions {
    padding-bottom: 2px;
  }

  .btn-icon-alt {
    padding: 5px;
  }

  .btn-icon-alt .material-icons {
    font-size: 18px;
  }

  /* Filas de detalle más juntas */
  .detail-row {
    padding: 5px 10px;
    gap: 10px;
  }

  /* Título principal más pequeño */
  .main-title h3 {
    font-size: 15px;
    line-height: 1.3;
  }

  .detail-subtitle {
    font-size: 12px;
    margin-top: 1px;
  }

  /* Cuadrado de color junto al título */
  .color-box {
    width: 10px;
    height: 10px;
    margin-top: 3px;
  }

  /* Iconos de info más pequeños */
  .info-icon {
    font-size: 16px;
    width: 18px;
  }

  /* Texto de filas */
  .detail-row span:not(.material-icons):not(.estado-valor):not(.estado-label),
  .detail-row .servicio-precio-container span {
    font-size: 13px;
  }

  /* Badge de precio */
  .cita-precio-tag {
    font-size: 11px;
    padding: 1px 6px;
  }

  /* Badge de estado */
  .estado-label {
    font-size: 12px;
  }

  .estado-valor {
    font-size: 10px;
    padding: 2px 7px;
  }

  .modal-overlay-calendar {
    align-items: flex-end;
  }

  .modal-content-calendar {
    width: 100vw;
    border-radius: 16px 16px 0 0;
    max-height: 92dvh;
    overflow-y: auto;
  }

  /* Título del modal más pequeño */
  .main-title h3 {
    font-size: 18px;
  }

  /* Grid de métodos de pago: 2 columnas en lugar de 4 */
  .grid-metodos-pago-horizontal {
    grid-template-columns: repeat(2, 1fr) !important;
  }

  /* Formulario nueva cita: fecha y hora en columna */
  .datetime-grid {
    grid-template-columns: 1fr !important;
  }

  /* Botones de acción de anulación en columna */
  .acciones-anular {
    flex-direction: column;
    gap: 8px;
  }

  .btn-confirmar,
  .btn-volver {
    width: 100%;
    text-align: center;
  }

  /* Buscador full width */
  .busqueda-container {
    width: 100%;
  }

  /* Ocultamos el select de estado en móvil muy pequeño (ver todas por defecto) */
  .filter-container-google {
    display: none;
  }
}

/* ── Modal edición ── */
.modal-editar-interna {
  padding: 16px 24px 8px;
}

.titulo-editar {
  font-size: 18px;
  font-weight: 600;
  color: #202124;
  margin: 0 0 4px;
}

.subtitulo-editar {
  font-size: 13px;
  color: #70757a;
  margin: 0 0 16px;
}

.acciones-editar {
  display: flex;
  gap: 10px;
  margin-top: 20px;
  padding-bottom: 8px;
}

@media (max-width: 480px) {
  .modal-editar-interna {
    padding: 12px 16px 8px;
  }
  .titulo-editar {
    font-size: 15px;
  }
  .acciones-editar {
    flex-direction: column;
  }
  .acciones-editar .btn-confirmar,
  .acciones-editar .btn-volver {
    width: 100%;
    text-align: center;
  }
}

/* ── Toast global ── */
.toast-global {
  position: fixed;
  bottom: 24px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 18px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  box-shadow: 0 4px 16px rgba(0,0,0,0.18);
  z-index: 9999;
  min-width: 280px;
  max-width: 90vw;
  pointer-events: all;
}

.toast-global.exito {
  background: #e6f4ea;
  color: #1e7e34;
  border-left: 4px solid #34a853;
}

.toast-global.error {
  background: #fce8e6;
  color: #c5221f;
  border-left: 4px solid #d93025;
}

.toast-icon {
  font-size: 20px;
  flex-shrink: 0;
}

.toast-msg {
  flex: 1;
  line-height: 1.4;
}

.toast-close {
  background: none;
  border: none;
  cursor: pointer;
  color: inherit;
  opacity: 0.6;
  padding: 0;
  display: flex;
  align-items: center;
}

.toast-close:hover { opacity: 1; }

.toast-slide-enter-active,
.toast-slide-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}
.toast-slide-enter-from,
.toast-slide-leave-to {
  opacity: 0;
  transform: translateX(-50%) translateY(16px);
}

/* Estilos para el selector de fecha rápido */
.datepicker-container {
  position: relative;
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 4px;
  transition: background 0.2s;
}

.datepicker-container:hover {
  background-color: #f1f3f4;
}

.calendar-edit-icon {
  font-size: 18px;
  color: #5f6368;
}

/* El input está invisible sobre el título para que al hacer clic en el texto se abra el calendario */
.hidden-date-input {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  opacity: 0;
  cursor: pointer;
}

.current-month {
  margin: 0;
  /* Mantenemos tu estilo original de h2 */
}

.detail-subtitle {
  /* Aseguramos que el párrafo permita elementos en línea */
  display: contents;
  align-items: center;
  flex-wrap: nowrap; /* Evita que salte a la siguiente línea */
  color: #5f6368;
  font-size: 14px;
  white-space: nowrap; /* Fuerza a que todo el texto sea una sola línea */
}

.duracion-inline {
  display: inline-flex;
  align-items: center;
  margin-left: 8px; /* Espacio a la izquierda para separarlo de la hora */
  color: #1e8e3e; /* Un verde suave (estilo Google) o mantén el gris #70757a */
  font-weight: 500;
  background-color: #e6f4ea; /* Fondo verde muy sutil */
  padding: 2px 8px;
  border-radius: 12px; /* Bordes muy redondeados tipo pastilla */
  font-size: 12px;
}

</style>