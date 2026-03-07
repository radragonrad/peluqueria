<template>
  <div class="servicios-view">
    <header class="section-header">
      <div class="header-left">
        <div class="breadcrumb">
          <span>Panel de Control</span> 
          <i class="fas fa-chevron-right"></i> 
          <span class="current">Configuración de Horarios</span>
        </div>
        <div class="title-container">
          <h1>Gestión de Horarios</h1>
        </div>
      </div>
    </header>

    <div class="table-card mb-30">
      <div class="card-header-inner">
        <h3><i class="fas fa-clock"></i> Horario de Apertura (Jornada Partida)</h3>
      </div>
      <table class="custom-table">
        <thead>
          <tr>
            <th>Día</th>
            <th>Estado</th>
            <th>Turno Mañana</th>
            <th>Turno Tarde</th>
            <th class="text-right">Acción</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="h in horarioSemanal" :key="h.id_dia" :class="{'fila-bloqueada': !h.abierto}">
            <td class="day-label">{{ h.dia_semana }}</td>
            <td>
              <label class="switch">
                <input 
                  type="checkbox" 
                  v-model="h.abierto" 
                  :true-value="1" 
                  :false-value="0"
                  @change="actualizarHorario(h)"
                >
                <span class="slider round"></span>
              </label>
              <span class="status-text" :class="h.abierto ? 'text-open' : 'text-closed'">
                {{ h.abierto ? 'Abierto' : 'Cerrado' }}
              </span>
            </td>
            
            <td>
              <div class="time-container-wrapper" v-if="h.abierto">
                <div class="time-group">
                  <input type="time" v-model="h.h_apertura_1" class="time-input small">
                  <input type="time" v-model="h.h_cierre_1" class="time-input small">
                </div>
                <button @click="limpiarTurno(h, 1)" class="btn-clear-time" title="Limpiar mañana">
                  <i class="fas fa-eraser"></i>
                </button>
              </div>
              <span v-else class="text-muted italic">Sin servicio</span>
            </td>

            <td>
              <div class="time-container-wrapper" v-if="h.abierto">
                <div class="time-group">
                  <input type="time" v-model="h.h_apertura_2" class="time-input small">
                  <input type="time" v-model="h.h_cierre_2" class="time-input small">
                </div>
                <button @click="limpiarTurno(h, 2)" class="btn-clear-time" title="Limpiar tarde">
                  <i class="fas fa-eraser"></i>
                </button>
              </div>
              <span v-else class="text-muted italic">Sin servicio</span>
            </td>

            <td class="text-right">
              <button @click="actualizarHorario(h)" class="btn-undo">
                <i class="fas fa-save"></i> <span>Guardar</span>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const horarioSemanal = ref([]);
const excepciones = ref([]);
const mostrarModal = ref(false);
const nuevaExcepcion = ref({ fecha: '', descripcion: '' });


const cargarDatos = async () => {
  try {
    const res = await fetch('/backend/api/gestion_horarios.php', { credentials: 'include' });
    const data = await res.json();
    horarioSemanal.value = data.horarios;
  } catch (e) { console.error(e); }
};



const abrirModalExcepcion = () => {
  nuevaExcepcion.value = { fecha: '', descripcion: '' };
  mostrarModal.value = true;
};

const guardarExcepcion = async () => {
  if (!nuevaExcepcion.value.fecha) return alert("Selecciona una fecha");
  try {
    await fetch('/backend/api/gestion_horarios.php', {
      method: 'POST',
      body: JSON.stringify(nuevaExcepcion.value)
    });
    cargarDatos();
    mostrarModal.value = false;
  } catch (e) { console.error(e); }
};

const eliminarExcepcion = async (id) => {
  if (confirm("¿Eliminar este día de cierre?")) {
    await fetch(`/backend/api/gestion_horarios.php?id=${id}`, { method: 'DELETE' });
    cargarDatos();
  }
};

const formatearFecha = (f) => new Date(f).toLocaleDateString('es-ES', { 
  weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' 
});

const limpiarTurno = (horario, turno) => {
  if (turno === 1) {
    horario.h_apertura_1 = null;
    horario.h_cierre_1 = null;
  } else {
    horario.h_apertura_2 = null;
    horario.h_cierre_2 = null;
  }
};

const actualizarHorario = async (horario) => {
  try {
    const res = await fetch('/backend/api/gestion_horarios.php', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(horario)
    });
    const data = await res.json();
    
    // Si quieres un feedback visual menos intrusivo que un alert
    if(data.success) {
      console.log(`Actualizado: ${horario.dia_semana}`);
    }
  } catch (e) { console.error("Error al guardar:", e); }
};

onMounted(cargarDatos);
</script>

<style scoped>
/* REUTILIZAMOS TUS ESTILOS DE SERVICIOS */
.servicios-view { padding: 30px; background: #f8fafc; min-height: 100vh; }
.section-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end; /* Alinea al fondo para un look más moderno */
  margin-bottom: 30px;
  padding-bottom: 10px;
  border-bottom: 1px solid #f0f0f0;
}

.header-left .breadcrumb {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.8rem;
  color: #999;
  margin-bottom: 8px;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.breadcrumb i {
  font-size: 0.6rem;
}

.breadcrumb .current {
  color: #e75480;
  font-weight: 600;
}

.title-container {
  display: flex;
  align-items: center;
  gap: 15px;
}

.title-container h1 {
  margin: 0;
  font-size: 1.8rem;
  color: #2c3e50;
  font-weight: 800;
}
.table-card { background: white; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.03); overflow: hidden; border: 1px solid #edf2f7; margin-bottom: 30px; }
.card-header-inner { padding: 20px; border-bottom: 1px solid #f1f5f9; }
.card-header-inner h3 { margin: 0; color: #1e293b; font-size: 1.1rem; }
.flex-between { display: flex; justify-content: space-between; align-items: center; }

.custom-table { width: 100%; border-collapse: collapse; }
.custom-table th { background: #fcfcfd; padding: 15px; text-align: left; color: #64748b; font-size: 0.75rem; text-transform: uppercase; }
.custom-table td { padding: 12px 15px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }

/* INPUTS DE TIEMPO */
.day-label { font-weight: 700; color: #1e293b; text-transform: capitalize; }
.time-input { padding: 6px 10px; border: 1px solid #e2e8f0; border-radius: 8px; color: #475569; font-weight: 600; }

/* BOTONES */
.btn-add-exception { background: #e75480; color: white; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 700; cursor: pointer; transition: 0.3s; }
.btn-add-exception:hover { background: #d44370; transform: translateY(-2px); }
.btn-undo { display: flex; align-items: center; gap: 6px; background: #fff; border: 1px solid #e2e8f0; padding: 6px 12px; border-radius: 8px; font-size: 0.7rem; font-weight: 700; color: #e75480; cursor: pointer; }

.service-pill { background: #fef2f2; color: #dc2626; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; }
.font-bold { font-weight: 700; color: #1e293b; }

/* MODAL */
.modal-overlay { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; z-index: 9999; }
.modal-content { background: white; border-radius: 20px; width: 400px; padding: 25px; }
.modal-input { width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 10px; margin-top: 5px; }
.form-group { margin-bottom: 15px; }
.form-group label { font-size: 0.8rem; font-weight: 700; color: #64748b; }
.modal-buttons { display: flex; flex-direction: column; gap: 10px; margin-top: 20px; }

/* Estilos anteriores + nuevos para el Switch y Time Inputs */
.time-group { display: flex; gap: 5px; align-items: center; }
.time-input.small { padding: 4px; font-size: 0.8rem; width: 85px; }
.status-text { font-size: 0.7rem; font-weight: 700; margin-left: 8px; text-transform: uppercase; }

/* Switch de Apertura */
.switch { position: relative; display: inline-block; width: 34px; height: 18px; vertical-align: middle; }
.switch input { opacity: 0; width: 0; height: 0; }
.slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e0; transition: .4s; border-radius: 34px; }
.slider:before { position: absolute; content: ""; height: 12px; width: 12px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
input:checked + .slider { background-color: #e75480; }
input:checked + .slider:before { transform: translateX(16px); }

.fila-bloqueada { background-color: #f8fafc; opacity: 0.7; }
/* Estructura para alinear el botón de limpiar al lado de los inputs */
.time-container-wrapper {
  display: flex;
  align-items: center;
  gap: 10px;
}

.time-group {
  display: flex;
  gap: 4px;
}

.time-input.small {
  padding: 5px;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-size: 0.85rem;
  width: 90px;
  color: #475569;
}

/* Estilo del botón de limpiar */
.btn-clear-time {
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  color: #94a3b8;
  width: 28px;
  height: 28px;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.btn-clear-time:hover {
  background: #fee2e2;
  color: #ef4444;
  border-color: #fecaca;
}

/* Switch y estados */
.switch { position: relative; display: inline-block; width: 34px; height: 18px; vertical-align: middle; }
.switch input { opacity: 0; width: 0; height: 0; }
.slider { position: absolute; cursor: pointer; inset: 0; background-color: #cbd5e0; transition: .4s; border-radius: 34px; }
.slider:before { position: absolute; content: ""; height: 12px; width: 12px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
input:checked + .slider { background-color: #e75480; }
input:checked + .slider:before { transform: translateX(16px); }

.status-text { font-size: 0.75rem; font-weight: 700; color: #64748b; margin-left: 8px; }
.fila-bloqueada { background-color: #f8fafc; opacity: 0.8; }
.day-label { font-weight: 700; color: #1e293b; }

/* Mantengo tus estilos anteriores y añado detalles de color */
.text-open { color: #2ecc71; }
.text-closed { color: #94a3b8; }
.italic { font-style: italic; font-size: 0.8rem; }

/* ... Estilos de Switch y Tabla que ya teníamos ... */
.time-container-wrapper { display: flex; align-items: center; gap: 10px; }
.time-input.small { padding: 5px; border: 1px solid #e2e8f0; border-radius: 6px; width: 90px; }
.btn-clear-time { background: #f1f5f9; border: 1px solid #e2e8f0; color: #94a3b8; width: 28px; height: 28px; border-radius: 6px; cursor: pointer; }
.btn-clear-time:hover { background: #fee2e2; color: #ef4444; }

.switch { position: relative; display: inline-block; width: 34px; height: 18px; }
.switch input { opacity: 0; width: 0; height: 0; }
.slider { position: absolute; cursor: pointer; inset: 0; background-color: #cbd5e0; transition: .4s; border-radius: 34px; }
.slider:before { position: absolute; content: ""; height: 12px; width: 12px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
input:checked + .slider { background-color: #e75480; }
input:checked + .slider:before { transform: translateX(16px); }
</style>