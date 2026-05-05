<template>
  <div class="mis-reservas-container">
    <div class="header-reservas">
      <span class="subtitle">GESTIÓN DE CITAS</span>
      <h2>MIS RESERVAS</h2>
      <div class="divider"></div>
    </div>

    <div v-if="cargando" class="loading-container">
      <div class="spinner"></div>
      <p>Buscando tus citas...</p>
    </div>

    <div v-else-if="reservas.length === 0" class="no-reservas">
      <div class="no-reservas-icon"><i class="fas fa-calendar-times"></i></div>
      <h3>No tienes citas pendientes</h3>
      <p>Luce tu mejor versión hoy mismo.</p>
      <router-link to="/servicios" class="btn-primary-rg">RESERVAR MI CITA</router-link>
    </div>

    <div v-else class="reservas-grid">
      <div v-for="reserva in reservas" :key="reserva.id" 
          class="reserva-card" 
          :class="{ 
            'card-pasada': esPasada(reserva.fecha, reserva.hora) || reserva.estado === 'COMPLETADA',
            'card-anulada': reserva.estado.includes('ANULADA') 
          }">
        <div class="card-ticket-edge"></div>
        
        <div class="reserva-header">
          <div class="fecha-badge">
            <span class="dia-num">{{ new Date(reserva.fecha).getDate() }}</span>
            <span class="mes-text">{{ new Date(reserva.fecha).toLocaleString('es-ES', { month: 'short' }).toUpperCase() }}</span>
          </div>
          <div class="hora-info">
            <i class="far fa-clock"></i> {{ reserva.hora.substring(0,5) }} hs
          </div>
        </div>

        <div class="reserva-body">
          <span class="servicio-tag">{{ reserva.servicio }}</span>
          <div class="professional-info">
            <i class="fas fa-cut"></i>
            <span>Peluquero: <strong>{{ reserva.peluquero }}</strong></span>
          </div>
          <div class="precio-row">
            <span class="label">Total servicio:</span>
            <span class="precio">{{ reserva.precio }}€</span>
          </div>
        </div>

        <div class="reserva-footer">
          <span class="status-indicator" :class="reserva.estado.toLowerCase().replace(' ', '-')">
            <i class="fas" :class="getIconoEstado(reserva.estado, reserva.fecha, reserva.hora)"></i>
            {{ getTextoEstado(reserva.estado, reserva.fecha, reserva.hora) }}
          </span>

          <div v-if="reserva.estado === 'PENDIENTE' && !esPasada(reserva.fecha, reserva.hora)">
            <button 
              v-if="puedeAnular(reserva.fecha, reserva.hora)" 
              @click="confirmarAnulacion(reserva)" 
              class="btn-anular-minimal"
            >
              CANCELAR
            </button>
            <span v-else class="lock-notice" title="Plazo de cancelación expirado">
              <i class="fas fa-lock"></i> Fuera de plazo
            </span>
          </div>
        </div>
      </div>
    </div>

    <Transition name="fade">
      <div v-if="modal.show" class="modal-overlay" @click.self="cerrarModal">
        <div class="modal-content">
          <div class="modal-icon" :class="modal.tipo">
            <i :class="modal.icono"></i>
          </div>
          <h3>{{ modal.titulo }}</h3>
          <p>{{ modal.mensaje }}</p>
          
          <div class="modal-actions">
            <template v-if="modal.tipo === 'confirm'">
              <button @click="cerrarModal" class="btn-cancel">VOLVER</button>
              <button @click="ejecutarAnulacion" class="btn-confirm">ANULAR CITA</button>
            </template>
            <template v-else>
              <button @click="cerrarModal" class="btn-confirm">ENTENDIDO</button>
            </template>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>


<script setup>
import { ref, onMounted, reactive } from 'vue';
import { useStore } from '../store';

const store = useStore();
const reservas = ref([]);
const cargando = ref(true);

const modal = reactive({
  show: false,
  titulo: '',
  mensaje: '',
  tipo: 'confirm',
  icono: 'fas fa-exclamation-triangle',
  reservaId: null
});

// LÓGICA DE TIEMPO (90 MINUTOS)
const puedeAnular = (fechaStr, horaStr) => {
  const citaString = `${fechaStr}T${horaStr}`;
  const fechaCita = new Date(citaString);
  const ahora = new Date();
  const diferenciaMinutos = (fechaCita - ahora) / (1000 * 60);
  return diferenciaMinutos > 90;
};

// DETERMINAR SI LA CITA YA OCURRIÓ
const esPasada = (fechaStr, horaStr) => {
  const fechaCita = new Date(`${fechaStr}T${horaStr}`);
  return fechaCita < new Date();
};

const getTextoEstado = (estado, fecha, hora) => {
  if (estado.includes('ANULADA')) return 'Cancelada';
  if (estado === 'COMPLETADA' || esPasada(fecha, hora)) return 'Finalizada';
  return 'Confirmada';
};

const getIconoEstado = (estado, fecha, hora) => {
  if (estado.includes('ANULADA')) return 'fa-times-circle';
  if (estado === 'COMPLETADA' || esPasada(fecha, hora)) return 'fa-check-circle';
  return 'fa-calendar-check';
};

const cargarReservas = async () => {
  const userId = store.state.userId || localStorage.getItem('userId');
  try {
    const res = await fetch(`/backend/api/get_mis_reservas.php?user_id=${userId}`);
    reservas.value = await res.json();
  } catch (error) {
    console.error("Error cargando reservas", error);
  } finally {
    cargando.value = false;
  }
};

const confirmarAnulacion = (reserva) => {
  // Ahora reserva es el objeto completo, por lo que podemos leer reserva.fecha
  if (!puedeAnular(reserva.fecha, reserva.hora)) {
    mostrarAviso("Plazo expirado", "No puedes anular con menos de 1.5h de antelación.", "error");
    return;
  }

  modal.titulo = "¿Anular reserva?";
  modal.mensaje = `Vas a cancelar tu cita de ${reserva.servicio}.`;
  modal.tipo = 'confirm';
  modal.icono = 'fas fa-calendar-times';
  modal.reservaId = reserva.id;
  modal.show = true;
};

const ejecutarAnulacion = async () => {
  const id = modal.reservaId;
  cerrarModal();
  const userId = store.state.userId || localStorage.getItem('userId');

  try {
    const res = await fetch('/backend/api/cancelar_reserva.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ reserva_id: id, user_id: userId })
    });

    const data = await res.json();
    if (data.success) {
      reservas.value = reservas.value.filter(r => r.id !== id);
      mostrarAviso("Éxito", "Cita anulada correctamente.", "success");
    } else {
      mostrarAviso("Error", data.message, "error");
    }
  } catch (error) {
    mostrarAviso("Error", "Error de conexión.", "error");
  }
};

const mostrarAviso = (titulo, mensaje, tipo) => {
  modal.titulo = titulo;
  modal.mensaje = mensaje;
  modal.tipo = tipo;
  modal.icono = tipo === 'error' ? 'fas fa-lock' : 'fas fa-check-circle';
  modal.show = true;
};

const cerrarModal = () => { modal.show = false; };

onMounted(cargarReservas);
</script>



<style scoped>
.mis-reservas-container {
  padding: 60px 20px;
  max-width: 1100px;
  margin: 0 auto;
  min-height: 80vh;
}

/* Header Estilo Contacto */
.header-reservas { text-align: center; margin-bottom: 50px; }
.subtitle { color: #e75480; font-size: 0.8rem; letter-spacing: 4px; font-weight: 700; }
.divider { width: 50px; height: 2px; background: #e75480; margin: 15px auto; }
h2 { color: white; letter-spacing: 2px; }

/* Grid y Card Estilo Ticket */
.reservas-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 30px;
}

.reserva-card {
  background: #111;
  border-radius: 20px;
  position: relative;
  border: 1px solid #222;
  transition: all 0.3s ease;
  overflow: hidden;
}

.reserva-card:hover {
  transform: translateY(-8px);
  border-color: #e75480;
}

/* Efecto de borde lateral de color */
.card-ticket-edge {
  position: absolute;
  left: 0; top: 0; bottom: 0;
  width: 5px;
  background: #e75480;
}

/* Header de la Card */
.reserva-header {
  padding: 25px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px dashed #333;
}

.fecha-badge {
  display: flex;
  flex-direction: column;
  align-items: center;
  line-height: 1;
}

.dia-num { font-size: 2rem; font-weight: 800; color: white; }
.mes-text { font-size: 0.8rem; color: #e75480; font-weight: bold; }

.hora-info {
  background: rgba(231, 84, 128, 0.1);
  color: #e75480;
  padding: 8px 15px;
  border-radius: 50px;
  font-weight: bold;
  font-size: 0.9rem;
}

/* Body de la Card */
.reserva-body { padding: 25px; }

.servicio-tag {
  display: block;
  font-size: 1.3rem;
  font-weight: 700;
  color: white;
  margin-bottom: 15px;
}

.professional-info {
  display: flex;
  align-items: center;
  gap: 10px;
  color: #888;
  font-size: 0.95rem;
  margin-bottom: 20px;
}

.professional-info i { color: #e75480; }

.precio-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #1a1a1a;
  padding: 12px 15px;
  border-radius: 10px;
}

.precio-row .label { color: #666; font-size: 0.8rem; }
.precio-row .precio { color: white; font-weight: 800; font-size: 1.2rem; }

/* Footer de la Card */
.reserva-footer {
  padding: 20px 25px;
  background: rgba(255, 255, 255, 0.02);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.status-indicator {
  font-size: 0.8rem;
  font-weight: bold;
  text-transform: uppercase;
  display: flex;
  align-items: center;
  gap: 6px;
}

.status-indicator.proxima { color: #4caf50; }
.status-indicator.pasada { color: #666; }

/* Botón Anular */
.btn-anular-minimal {
  background: transparent;
  border: none;
  color: #ff4d6d;
  font-weight: bold;
  font-size: 0.75rem;
  letter-spacing: 1px;
  cursor: pointer;
  padding: 5px 0;
  border-bottom: 1px solid transparent;
  transition: 0.3s;
}

.btn-anular-minimal:hover {
  border-color: #ff4d6d;
  letter-spacing: 2px;
}

/* Estado Pasada */
.card-pasada { opacity: 0.6; filter: grayscale(0.5); }
.card-pasada .card-ticket-edge { background: #444; }

/* Sin Reservas */
.no-reservas {
  text-align: center;
  padding: 50px;
  background: #111;
  border-radius: 20px;
  border: 1px solid #222;
}

.no-reservas-icon { font-size: 4rem; color: #333; margin-bottom: 20px; }

.btn-primary-rg {
  display: inline-block;
  margin-top: 20px;
  background: #e75480;
  color: white;
  padding: 15px 30px;
  border-radius: 50px;
  text-decoration: none;
  font-weight: bold;
  transition: 0.3s;
}

.btn-primary-rg:hover { transform: scale(1.05); background: white; color: #e75480; }

@media (max-width: 768px) {
  .reservas-grid { grid-template-columns: 1fr; }
}

/* --- ESTILOS DEL MODAL --- */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.85);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  backdrop-filter: blur(5px);
}

.modal-content {
  background: #1a1a1a;
  padding: 40px;
  border-radius: 25px;
  width: 90%;
  max-width: 400px;
  text-align: center;
  border: 1px solid #333;
  box-shadow: 0 20px 40px rgba(0,0,0,0.5);
}

.modal-icon {
  width: 70px;
  height: 70px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 20px;
  font-size: 1.8rem;
}

.modal-icon.confirm, .modal-icon.error {
  background: rgba(231, 84, 128, 0.1);
  color: #e75480;
}

.modal-icon.success {
  background: rgba(76, 175, 80, 0.1);
  color: #4caf50;
}

.modal-content h3 {
  color: white;
  margin-bottom: 10px;
  font-size: 1.5rem;
}

.modal-content p {
  color: #aaa;
  font-size: 0.95rem;
  line-height: 1.5;
  margin-bottom: 30px;
}

.modal-actions {
  display: flex;
  gap: 15px;
  justify-content: center;
}

.btn-cancel {
  background: transparent;
  border: 1px solid #444;
  color: white;
  padding: 12px 25px;
  border-radius: 50px;
  cursor: pointer;
  font-weight: bold;
  transition: 0.3s;
}

.btn-confirm {
  background: #e75480;
  border: none;
  color: white;
  padding: 12px 25px;
  border-radius: 50px;
  cursor: pointer;
  font-weight: bold;
  transition: 0.3s;
}

.btn-confirm:hover { transform: scale(1.05); box-shadow: 0 5px 15px rgba(231, 84, 128, 0.3); }
.btn-cancel:hover { background: #333; }

/* Animación de entrada */
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.lock-notice {
  font-size: 0.7rem;
  color: #666;
  font-weight: 600;
  text-transform: uppercase;
  display: flex;
  align-items: center;
  gap: 5px;
  background: rgba(255, 255, 255, 0.05);
  padding: 4px 10px;
  border-radius: 4px;
}

/* Estado Anulada */
.anulada-web, .anulada-local {
  color: #ef4444 !important; /* Rojo */
}

.card-anulada {
  opacity: 0.7;
  filter: grayscale(0.5);
}

.card-anulada .servicio-tag {
  text-decoration: line-through;
  background: #fee2e2;
  color: #ef4444;
}

/* Estado Pendiente / Confirmada */
.pendiente {
  color: #4ade80 !important; /* Verde como tu botón de finalizar */
}

/* Estado Completada */
.completada {
  color: #64748b !important;
}
</style>