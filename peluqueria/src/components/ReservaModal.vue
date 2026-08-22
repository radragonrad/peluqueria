<template>
  <div class="modal-overlay" @click.self="cerrar">
    <div class="modal-container">
      <div class="modal-main">
        <div class="modal-header">
          <div class="header-nav">
            <button @click="volverAtras" class="btn-atras" v-if="paso > 1">← Atrás</button>
            <div v-else></div> <button @click="cerrar" class="btn-close">×</button>
          </div>
          <h2 class="header-title">
            {{ paso === 1 ? 'Peluqueros Disponibles' : 'Seleccione Fecha y hora' }}
          </h2>
        </div>

        <div v-if="paso === 1" class="agents-grid">
          <div 
            v-for="peluquero in store.state.peluqueros" 
            :key="peluquero.id"
            class="agent-card"
            @click="seleccionarPeluquero(peluquero)"
          >
            <div class="agent-img">
              <img :src="`/uploads/avatares/${peluquero.avatar}`" :alt="peluquero.nombre">
            </div>
            <div class="agent-info">
              <span>{{ peluquero.nombre }}</span>
            </div>
          </div>
        </div>

        <div v-else class="step-datetime">
          <div class="calendar-container">
            <div class="calendar-header">
              <button @click="cambiarMes(-1)" :disabled="mesActual === hoy.getMonth() && anioActual === hoy.getFullYear()"> < </button>
              <h3>{{ nombresMeses[mesActual] }} {{ anioActual }}</h3>
              <button @click="cambiarMes(1)"> > </button>
            </div>

            <div class="weekdays">
              <div>L</div><div>M</div><div>M</div><div>J</div><div>V</div><div>S</div><div>D</div>
            </div>
            <!-- PASO 2 CALENDARIO-->
            <div class="days-grid">
              <div v-for="blank in primerDiaSemana" :key="'blank'+blank" class="day-empty"></div>
              
              <div 
                v-for="dia in diasEnMes" 
                :key="dia"
                :class="{ 
                  'selected': diaSeleccionado === dia, 
                  'disabled': esDiaBloqueado(dia),
                  'is-exception': esExcepcionCierreTotal(dia),
                  'is-special': esHorarioEspecial(dia),
                  'is-closed': esDiaCerrado(dia) && !esHorarioEspecial(dia) && !esExcepcionCierreTotal(dia)
                }"
                @click="!esDiaBloqueado(dia) ? seleccionarDia(dia) : null"
              >             
                <span class="day-number">{{ dia }}</span>
                
                <small v-if="esExcepcionCierreTotal(dia)" class="text-exception">{{ obtenerDescripcionExcepcion(dia) }}</small>
                <small v-else-if="esHorarioEspecial(dia)" class="text-special">{{ obtenerDescripcionExcepcion(dia) || 'Especial' }}</small>
                <small v-else-if="esDiaCerrado(dia)" class="text-closed">Cerrado</small>
              </div>
            </div>
          </div>

       


          <div v-if="diaSeleccionado" class="hours-section">
            <p>Horarios para el {{ diaSeleccionado }} de {{ nombresMeses[mesActual] }}</p>
            <div class="hours-grid">
              <button v-for="hora in horariosDisponibles" @click="horaSeleccionada = hora" :class="{'active': horaSeleccionada === hora}">
                {{ hora }}
              </button>
            </div>
          </div>

          <div v-if="diaSeleccionado && !cargandoHoras && horariosDisponibles.length === 0" class="no-availability-message">
            <div class="info-card">              
              <p>Lo sentimos, <strong>{{ peluqueroSeleccionado?.nombre }}</strong> no tiene huecos disponibles para este servicio el día {{ diaSeleccionado }}.</p>
              <span>Prueba con otro peluquero o selecciona otra fecha.</span>
            </div>
          </div>

        </div>
      </div>

      <div class="modal-sidebar">
        <div class="sidebar-header">
          <h3>Resumen de Cita</h3>
        </div>

        <div class="summary-container">
          <div class="summary-content">
            <h4 class="service-name">{{ store.state.servicioSeleccionado?.nombre }}</h4>
                        
            <div class="summary-card" v-if="peluqueroSeleccionado">
              <div class="summary-item" v-if="peluqueroSeleccionado">
                <i class="fas fa-user-tie"></i>
                <div class="item-text">
                  <span class="label">Peluquero</span>
                  <span class="value">{{ peluqueroSeleccionado.nombre }}</span>
                </div>
              </div>

              <div class="summary-item" v-if="diaSeleccionado">
                <i class="fas fa-calendar-alt"></i>
                <div class="item-text">
                  <span class="label">Fecha</span>
                  <span class="value">{{ diaSeleccionado }} de {{ nombresMeses[mesActual] }}, {{ anioActual }}</span>
                </div>
              </div>

              <div class="summary-item" v-if="horaSeleccionada">
                <i class="fas fa-clock"></i>
                <div class="item-text">
                  <span class="label">Hora</span>
                  <span class="value">{{ horaSeleccionada }} hs</span>
                </div>
              </div>

              <div class="summary-item" v-if="peluqueroSeleccionado">
                <i class="fas fa-hourglass-half"></i>
                <div class="item-text">
                  <span class="label">Duración aprox.</span>
                <span class="value">{{ store.state.servicioSeleccionado?.duracion_min }} minutos</span>
                </div>
              </div>
              
            </div>

            <div class="price-breakdown">
              <div class="price-row">
                <span>Subtotal</span>
                <span>{{ parseFloat(store.state.servicioSeleccionado?.precio).toFixed(2) }}€</span>
              </div>
              <div class="price-row total">
                <span>Total</span>
                <span>{{ parseFloat(store.state.servicioSeleccionado?.precio).toFixed(2) }}€</span>
              </div>
            </div>
          </div>

          <div class="summary-footer">
            <button 
              v-if="paso === 2 && horaSeleccionada" 
              class="btn-confirmar"
              @click="confirmarReserva"
              :disabled="enviandoReserva" 
              :class="{ 'is-loading': enviandoReserva }"
            >
              <span v-if="!enviandoReserva">Confirmar Reserva</span>
              <span v-else>Procesando...</span>
              <i v-if="!enviandoReserva" class="fas fa-chevron-right"></i>
              <i v-else class="fas fa-spinner fa-spin"></i> <!-- Icono de carga -->
            </button>
            <p v-else class="hint-text">Complete los datos para confirmar</p>
          </div>
        </div>
      </div>      

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useStore } from '../store.js';
import { useRouter } from 'vue-router';

const router = useRouter(); // Inicializa el router
const store = useStore();
const paso = ref(1);
const loading = ref(true);
const peluqueroSeleccionado = ref(null);
const enviandoReserva = ref(false); // Nuevo estado para controlar el botón

// Estado del Calendario
const hoy = new Date();
const mesActual = ref(hoy.getMonth());
const anioActual = ref(hoy.getFullYear());
const diaSeleccionado = ref(null);
const horaSeleccionada = ref(null);
const diasCerrados = ref([]); // Guardaremos los números de los días cerrados (ej: [7] para domingo)

const nombresMeses = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", 
                      "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];

const fechasExcepciones = ref([]); // Guardará ["2026-02-28", "2026-03-01"]
const listaExcepcionesDetallada = ref([]); // Guardará ["2026-02-28", "2026-03-01"]

// 2. Cargar las excepciones al montar el componente
const cargarExcepciones = async () => {
  try {
    const res = await fetch('/backend/api/get_excepciones.php');
    const data = await res.json(); 
    // Suponiendo que el PHP ahora devuelve el objeto completo
    listaExcepcionesDetallada.value = data;
    fechasExcepciones.value = data.map(e => e.fecha);
  } catch (error) {
    console.error("Error cargando excepciones:", error);
  }
};

const volverAtras = () => {
  paso.value = 1;
  peluqueroSeleccionado.value = null; // Limpia el peluquero
  diaSeleccionado.value = null;      // Limpia el día
  horaSeleccionada.value = null;     // Limpia la hora
  horariosDisponibles.value = [];    // Limpia la lista de horas
  diasCerrados.value = [];           // Limpia el horario semanal del peluquero anterior
};

// 1. Detección de Horario Especial (AMARILLO)
const esHorarioEspecial = (dia) => {
  if (!dia) return false;
  const fechaStr = formatearFechaComparar(dia, mesActual.value, anioActual.value);
  const ex = listaExcepcionesDetallada.value.find(e => e.fecha === fechaStr);
  return ex && Number(ex.solo_tramo) === 1;
};

// 2. Detección de Festivo / Cierre Total (AZUL)
const esExcepcionCierreTotal = (dia) => {
  if (!dia) return false;
  const fechaStr = formatearFechaComparar(dia, mesActual.value, anioActual.value);
  const ex = listaExcepcionesDetallada.value.find(e => e.fecha === fechaStr);
  return ex && Number(ex.solo_tramo) === 0;
};

// Verifica si el día está en la tabla de excepciones
const esExcepcion = (dia) => {
  if (!dia) return false;
  const fechaStr = formatearFechaComparar(dia, mesActual.value, anioActual.value);
  return fechasExcepciones.value.includes(fechaStr);
};


// 3. Función para formatear fecha a YYYY-MM-DD (para comparar)
const formatearFechaComparar = (dia, mes, anio) => {
  const m = (mes + 1).toString().padStart(2, '0');
  const d = dia.toString().padStart(2, '0');
  return `${anio}-${m}-${d}`;
};

// 4. Nueva lógica de bloqueo mejorada
// 4. Nueva lógica de bloqueo mejorada (Incluye bloqueo hasta el 27 de abril)
const esDiaBloqueado = (dia) => {
  if (!dia) return true;
  
  // --- NUEVA RESTRICCIÓN DE FECHA MÍNIMA ---
  const fechaComparar = new Date(anioActual.value, mesActual.value, dia);
  const fechaApertura = new Date(2026, 3, 27); // 27 de Abril de 2026 (Mes 3 porque Enero es 0)
  
  if (fechaComparar < fechaApertura) return true;
  // -----------------------------------------

  if (esDiaPasado(dia)) return true;
  if (esExcepcionCierreTotal(dia)) return true;
  
  // Solo bloqueamos si es día cerrado habitual Y no tiene horario especial
  if (esDiaCerrado(dia) && !esHorarioEspecial(dia)) return true;
  
  return false;
};

// Horario semanal del peluquero seleccionado (no el de la tienda general).
// Si el peluquero no tiene un día configurado como abierto, ese día se marca cerrado.
const cargarConfiguracionHorario = async (peluqueroId) => {
  if (!peluqueroId) {
    diasCerrados.value = [];
    return;
  }
  try {
    const res = await fetch(`/backend/api/get_horario_tienda.php?peluquero_id=${peluqueroId}`);
    const data = await res.json();
    // Filtramos los días donde 'abierto' es 0
    diasCerrados.value = data.semanal
      .filter(d => Number(d.abierto) === 0)
      .map(d => Number(d.id_dia));
  } catch (error) {
    console.error("Error cargando configuración de días:", error);
  }
};

// Función para saber qué día de la semana es una fecha concreta (ajustado a 1=Lunes, 7=Domingo)
const obtenerNumeroDiaSemana = (dia, mes, anio) => {
  let date = new Date(anio, mes, dia);
  let day = date.getDay(); // 0 es Domingo en JS
  return day === 0 ? 7 : day;
};

const esDiaCerrado = (dia) => {
  const numDia = obtenerNumeroDiaSemana(dia, mesActual.value, anioActual.value);
  return diasCerrados.value.includes(numDia);
};

const diasEnMes = computed(() => {
  return new Date(anioActual.value, mesActual.value + 1, 0).getDate();
});

// Calcula en qué día de la semana empieza el mes (0=Dom, 1=Lun...)
const primerDiaSemana = computed(() => {
  let dia = new Date(anioActual.value, mesActual.value, 1).getDay();
  return dia === 0 ? 6 : dia - 1; // Ajuste para que empiece en Lunes
});

const obtenerDescripcionExcepcion = (dia) => {
  if (!dia) return '';
  const fechaStr = formatearFechaComparar(dia, mesActual.value, anioActual.value);
  const ex = listaExcepcionesDetallada.value.find(e => e.fecha === fechaStr);

  
  // Si existe descripción en la DB, la devuelve; si no, devuelve un texto por defecto
  return ex ? ex.descripcion : 'Festivo'; 
};


const cambiarMes = (direccion) => {
  mesActual.value += direccion;
  if (mesActual.value > 11) {
    mesActual.value = 0;
    anioActual.value++;
  } else if (mesActual.value < 0) {
    mesActual.value = 11;
    anioActual.value--;
  }
  // Al cambiar de mes, deseleccionamos el día para evitar errores
  diaSeleccionado.value = null;
  horaSeleccionada.value = null;
};

// Verifica si un día es del pasado
const esDiaPasado = (dia) => {
  const fechaComparar = new Date(anioActual.value, mesActual.value, dia);
  const fechaHoy = new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate());
  return fechaComparar < fechaHoy;
};


const horariosDisponibles = ref([]);
const cargandoHoras = ref(false);

const cargarHoras = async () => {
  
  if (!diaSeleccionado.value || !peluqueroSeleccionado.value) return;
  
  cargandoHoras.value = true;
  // Construimos la fecha YYYY-MM-DD
  const mes = (mesActual.value + 1).toString().padStart(2, '0');
  const dia = diaSeleccionado.value.toString().padStart(2, '0');
  const fechaStr = `${anioActual.value}-${mes}-${dia}`;

  try {
    const res = await fetch(
      `/backend/api/get_horas_disponibles.php?fecha=${fechaStr}&peluquero_id=${peluqueroSeleccionado.value.id}&servicio_id=${store.state.servicioSeleccionado.id}`
    );
    horariosDisponibles.value = await res.json();
  } catch (error) {
    console.error("Error cargando horas:", error);
  } finally {
    cargandoHoras.value = false;
  }
};

// Usamos un Watcher o llamamos a cargarHoras() cuando seleccionamos un día
const seleccionarDia = (dia) => {
  if (!esDiaPasado(dia)) {
    diaSeleccionado.value = dia;
    horaSeleccionada.value = null;
    cargarHoras(); // 👈 Llamada al servidor
  }
};

const seleccionarPeluquero = (peluquero) => {
  peluqueroSeleccionado.value = peluquero;
  paso.value = 2; // Saltamos al calendario
  cargarConfiguracionHorario(peluquero.id); // Horario propio de este peluquero
};

const cerrar = () => {
  store.cerrarModal();
  paso.value = 1;
};

const confirmarReserva = async () => {
  // 1. Evitar ejecuciones si ya se está enviando
  if (enviandoReserva.value) return;

  const userId = store.state.userId || localStorage.getItem('userId');

  if (!userId) {
    alert("Tu sesión ha caducado. Por favor, vuelve a iniciar sesión para reservar.");
    store.cerrarSesionLimpiar();
    cerrar();
    router.push('/login');
    return;
  }

  // Activar bloqueo
  enviandoReserva.value = true;

  const horaLimpia = horaSeleccionada.value.split(' ')[0] + ':00';
  const mes = String(mesActual.value + 1).padStart(2, '0');
  const dia = String(diaSeleccionado.value).padStart(2, '0');
  const fechaFormateada = `${anioActual.value}-${mes}-${dia}`;
  
  try {
    const res = await fetch('/backend/api/confirmar_reserva.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        user_id: userId, // Usamos la variable local validada
        servicio_id: store.state.servicioSeleccionado.id,
        peluquero_id: peluqueroSeleccionado.value.id,        
        fecha: fechaFormateada,
        hora: horaLimpia
      })
    });

    const data = await res.json();

    if (data.success) {
      alert("¡Reserva realizada! Te esperamos.");
      cerrar();
    } else {
      alert("No se pudo realizar la reserva: " + data.message);
      if (data.message.includes("sesión") || data.message.includes("auth")) {
          store.cerrarSesionLimpiar();
          cerrar();
          router.push('/login');
      }
    }
  } catch (err) {
    console.error("Error en la petición:", err);
    alert("No se pudo conectar con el servidor.");
  } finally {
    // 2. IMPORTANTE: Liberar el botón siempre, ocurra error o éxito
    enviandoReserva.value = false;
  }
};

onMounted(async () => {
  cargarExcepciones();
  try {
    const response = await fetch('/backend/api/get_peluqueros.php');
    const data = await response.json();
    store.setPeluqueros(data);
  } catch (err) {
    console.error('Error cargando peluqueros:', err);
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
/* Estilos básicos para imitar tu imagen */
.modal-overlay {
  position: fixed;
  top: 0; left: 0; width: 100%; height: 100%;
  background: rgba(0,0,0,0.8);
  display: flex; align-items: center; justify-content: center;
  z-index: 2000;
}
.modal-container {
  display: flex;
  background: white;
  border-radius: 8px;
  width: 95%; /* Un poco más ancho para aprovechar espacio */
  max-width: 1000px;
  max-height: 90vh; /* IMPORTANTE: Limita la altura al 90% de la pantalla */
  overflow: hidden; /* Evita que el contenedor padre se rompa */
  color: #333;
}

.modal-main { 
  flex: 2; 
  padding: 2rem; 
  overflow-y: auto; /* PERMITE SCROLL aquí cuando las horas son muchas */
}

/* Ajuste opcional para la cuadrícula de horas para que se vea mejor */
.hours-section {
  margin-top: 2rem;
  padding-bottom: 2rem; /* Espacio extra al final para que el scroll no corte el último botón */
}
.modal-sidebar { 
  flex: 1; 
  background: #fcfcfc; 
  border-left: 1px solid #eee; 
  padding: 2rem;
  border-radius: 0 8px 8px 0;
}
.agents-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
  gap: 1.5rem;
  margin-top: 2rem;
}
.agent-card {
  border: 1px solid #eee;
  border-radius: 8px;
  padding: 1rem;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s;
}
.agent-img {
  width: 100px;
  height: 100px;
  margin: 0 auto 1rem;
  border-radius: 50%;
  overflow: hidden; /* Corta la imagen en círculo */
  border: 3px solid #eee;
  background: #f3f3f3; /* Fondo por si la imagen es transparente */
}

.agent-img img {
  width: 100%;
  height: 100%;
  object-fit: cover; /* Ajusta la foto sin estirarla */
  display: block;
}

.agent-card.active .agent-img {
  border-color: #e75480; /* Resaltado rosa cuando se selecciona */
}

.modal-header {
margin-bottom: 1.5rem;
  border-bottom: 1px solid #eee;
  padding-bottom: 1rem;
}
.header-nav {
  display: flex;
  justify-content: space-between; /* Empuja atrás a la izq y cerrar a la der */
  align-items: center;
  width: 100%;
  
}

.header-content {
  display: flex;
  justify-content: flex-start;
}

.header-title {
  margin: 0;
  font-size: 1.8rem;
  font-weight: 700;
  color: #1a1a1a;
}

.btn-atras, .btn-close {
  background: #f8f9fa;
  border: 1px solid #e9ecef;
  border-radius: 8px;
  padding: 8px 16px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-close {
  width: 40px;
  height: 40px;
  /* font-size: 1.5rem; */
  padding: 0;
}

.btn-atras:hover, .btn-close:hover {
  background: #e75480;
  color: white;
  border-color: #e75480;
}

/** Calendario PASO 2 */
.hours-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
  margin-top: 1rem;
}

.hours-grid button {
  background: #e8f5e9; /* Verde muy clarito */
  border: 1px solid #c8e6c9;
  color: #2e7d32;
  padding: 10px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.85rem;
}

.hours-grid button.active {
  background: #2e7d32;
  color: white;
}

.days-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 5px;
  text-align: center;
}

.days-grid div {
  padding: 10px;
  cursor: pointer;
  border-radius: 4px;
}

.days-grid div.selected {
  background: #00897b; /* Color verde azulado de tu imagen */
  color: white;
}

.calendar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.weekdays {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  font-weight: bold;
  text-align: center;
  color: #888;
  margin-bottom: 10px;
}

.days-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 5px;
}

.days-grid div {
  padding: 10px;
  text-align: center;
  cursor: pointer;
  border-radius: 4px;
  transition: 0.2s;
}

.days-grid div:hover:not(.disabled) {
  background: #f0f0f0;
}

.days-grid div.selected {
  background: #00897b !important;
  color: white;
}

.days-grid div.disabled {
  color: #ccc;
  cursor: not-allowed;
}

.day-empty {
  cursor: default !important;
}

.days-grid div.is-closed {
  background-color: #fce4ec; /* Un tono rojizo suave */
  color: #d81b60;
  cursor: not-allowed;
  opacity: 0.6;
}

.text-closed {
  display: block;
  font-size: 0.6rem;
  color: #d81b60;
  margin-top: -5px;
}

/* Evitar que se resalte al pasar el ratón por encima si está cerrado */
.days-grid div.is-closed:hover {
  background-color: #fce4ec !important;
}

.days-grid div.is-exception {
  background-color: #fff3e0; /* Naranja muy suave */
  color: #ef6c00; /* Texto naranja oscuro */
  position: relative;
}

.days-grid div.is-exception::after {
  content: '📌'; /* Un icono pequeño para indicar festivo */
  font-size: 8px;
  position: absolute;
  top: 2px;
  right: 2px;
}

/* Estilo base para cada celda del día */
.days-grid div {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 8px 4px;
  border-radius: 8px; /* Bordes redondeados como en tu imagen */
  min-height: 50px;
  transition: all 0.3s ease;
}

/* 1. DÍA CERRADO (HORARIO HABITUAL - ROJO) */
.days-grid div.is-closed {
  background-color: #FFF0F3 !important; /* Rosa/Rojo muy claro */
  color: #FF4D6D; /* Texto rojo */
  cursor: not-allowed;
}

.text-closed {
  color: #FF4D6D;
  font-size: 0.65rem;
  font-weight: 500;
}

/* 2. DÍA EXCEPCIONAL (FESTIVO - NARANJA) */
.days-grid div.is-exception {
  background-color: #FFF8E1 !important; /* Naranja muy claro */
  color: #F57C00; /* Texto naranja */
  cursor: not-allowed;
  border: 1px dashed #FFE0B2; /* Opcional: borde discontinuo para diferenciar */
}

.text-exception {
  color: #F57C00;
  font-size: 0.65rem;
  font-weight: 600;
}

/* DÍAS PASADOS (GRIS MUY SUAVE) */
.days-grid div.disabled:not(.is-closed):not(.is-exception) {
  color: #D1D1D1;
  cursor: not-allowed;
}

/* DÍA SELECCIONADO (VERDE OSCURO) */
.days-grid div.selected {
  background-color: #00897B !important;
  color: white !important;
}

.days-grid div.selected small {
  color: white !important;
}

/* ESTILO AMARILLO (Horario Especial / Tramos) */
.days-grid div.is-special {
  background-color: #FFFDE7 !important; /* Amarillo muy claro */
  border: 1px solid #FFF59D !important;
  color: #FBC02D !important;
  cursor: pointer !important;
  opacity: 1 !important;
}

.text-special {
  color: #FBC02D;
  font-size: 0.65rem;
  font-weight: bold;
  display: block;
  margin-top: -5px;
}

/* Re-asegurar que el rojo funcione */
.days-grid div.is-closed {
  background-color: #FFF0F3 !important;
  color: #FF4D6D !important;
  border: 1px solid #FFCCD5 !important;
  cursor: not-allowed !important;
  opacity: 0.7;
}

/* Re-asegurar que el azul (Festivo) funcione */
.days-grid div.is-exception {
  background-color: #E3F2FD !important;
  color: #1976D2 !important;
  border: 1px solid #BBDEFB !important;
  cursor: not-allowed !important;
}

.text-exception {
  color: #1976D2;
  font-size: 0.65rem;
  font-weight: bold;
}


/* Sidebar General */
.modal-sidebar {
  padding: 24px;
  background-color: #ffffff;
  display: flex;
  flex-direction: column;
}

.sidebar-header h3 {
  font-size: 1.1rem;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: #999;
  margin-bottom: 20px;
}

/* Tarjeta de Resumen */
.summary-card {
  background: #fcfcfc;
  border: 1px solid #f0f0f0;
  border-radius: 16px;
  padding: 15px;
  margin-bottom: 25px;
}

.summary-item {
  display: flex;
  align-items: center;
  padding: 12px 0;
  border-bottom: 1px solid #f5f5f5;
}

.summary-item:last-child { border-bottom: none; }

.summary-item i {
  width: 40px;
  height: 40px;
  background: #fff0f5; /* Rosa muy suave */
  color: #e75480;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  margin-right: 15px;
}

.item-text {
  display: flex;
  flex-direction: column;
}

.item-text .label {
  font-size: 0.75rem;
  color: #aaa;
  text-transform: uppercase;
  font-weight: 600;
}

.item-text .value {
  font-size: 0.95rem;
  color: #333;
  font-weight: 600;
}

/* Desglose de Precio */
.price-breakdown {
  margin-top: 20px;
}

.price-row {
  display: flex;
  justify-content: space-between;
  color: #777;
  font-size: 0.9rem;
  margin-bottom: 8px;
}

.price-row.total {
  margin-top: 15px;
  padding-top: 15px;
  border-top: 2px solid #f0f0f0;
  color: #1a1a1a;
  font-size: 1.2rem;
  font-weight: 800;
}

.price-row.total span:last-child {
  color: #e75480;
}

/* Botón Confirmar */
.btn-confirmar {
  width: 100%;
  height: 55px;
  background: #e75480;
  color: white;
  border: none;
  border-radius: 14px;
  font-size: 1rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 25px;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 10px 20px rgba(231, 84, 128, 0.2);
}

.btn-confirmar:hover {
  background: #d43f6d;
  transform: translateY(-2px);
  box-shadow: 0 12px 25px rgba(231, 84, 128, 0.3);
}

.hint-text {
  text-align: center;
  color: #ccc;
  font-size: 0.8rem;
  margin-top: 15px;
}

.cost-breakdown { margin-top: 2rem; font-size: 0.8rem; color: #888; }
.total { color: #000; font-weight: bold; font-size: 1.1rem; margin-top: 1rem; display: flex; justify-content: space-between; }

/* --- RESPONSIVE DESIGN --- */

@media (max-width: 768px) {
  .modal-container {
    flex-direction: column; /* Apila el contenido y el resumen */
    width: 95%;
    max-width: 100%;
    height: 90vh; /* Casi toda la pantalla */
    overflow-y: auto; /* Permite scroll si el contenido es largo */
    margin-top: 20px;
  }

  .modal-main {
    padding: 1.2rem;
    flex: none;
  }

  .modal-sidebar {
    flex: none;
    border-left: none;
    border-top: 1px solid #eee;
    padding: 1.5rem;
    border-radius: 0 0 8px 8px;
    background: #fdfdfd;
  }

  .header-title {
    font-size: 1.4rem; /* Texto un poco más pequeño en móvil */
  }
}


@media (max-width: 480px) {
  /* Cuadrícula de Peluqueros: de 1 columna si es muy pequeño o 2 */
  .agents-grid {
    grid-template-columns: repeat(2, 1fr); 
    gap: 1rem;
  }

  .agent-img {
    width: 70px;
    height: 70px;
  }

  /* Horas: 2 columnas en lugar de 3 para que los botones sean grandes */
  .hours-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .hours-grid button {
    padding: 15px; /* Más espacio para el dedo */
    font-size: 1rem;
  }
}

@media (max-width: 768px) {
  .days-grid div {
    min-height: 45px;
    padding: 5px 2px;
  }

  .day-number {
    font-size: 0.9rem;
    font-weight: bold;
  }

  /* Ocultamos los textos pequeños (Cerrado, Festivo) en móvil 
     si el espacio es crítico, o los hacemos muy pequeños */
  .text-closed, .text-exception, .text-special {
    font-size: 0.55rem;
    letter-spacing: -0.5px;
  }
  
  .weekdays div {
    font-size: 0.8rem;
  }
}

.btn-confirmar:disabled {
  background: #ccc; /* Gris cuando está bloqueado */
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

/* Opcional: animación de rotación para el icono de carga */
.fa-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>