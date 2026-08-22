<template>
  <div class="servicios-view">
    <header class="section-header">
      <div class="header-left">
        <div class="breadcrumb">
          <span>Panel de Control</span>
          <i class="fas fa-chevron-right"></i>
          <span class="current">Horarios por Peluquero</span>
        </div>
        <div class="title-container">
          <h1>Horario de {{ peluqueroActivo?.nombre ?? 'Peluquero' }}</h1>
        </div>
      </div>
    </header>

    <div v-if="!peluqueroActivo" class="aviso-sin-peluquero">
      Selecciona un peluquero en el menú lateral para configurar su horario.
    </div>

    <div v-else class="table-card mb-30">
      <div class="card-header-inner">
        <h3><i class="fas fa-user-clock"></i> Jornada Partida — {{ peluqueroActivo.nombre }}</h3>
        <p class="hint-text">Si un día aparece como "Cerrado", este peluquero no atenderá citas ese día, aunque la tienda esté abierta.</p>
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
import { ref, inject, watch, onMounted } from 'vue';

const peluqueroActivo = inject('peluqueroActivo', ref(null));

const horarioSemanal = ref([]);

const cargarDatos = async () => {
  if (!peluqueroActivo.value?.id) {
    horarioSemanal.value = [];
    return;
  }
  try {
    const res = await fetch(`/backend/api/gestion_horarios_peluquero.php?peluquero_id=${peluqueroActivo.value.id}`, { credentials: 'include' });
    const data = await res.json();
    horarioSemanal.value = data.horarios ?? [];
  } catch (e) { console.error(e); }
};

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
    const res = await fetch('/backend/api/gestion_horarios_peluquero.php', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'include',
      body: JSON.stringify(horario)
    });
    const data = await res.json();
    if (data.success) {
      console.log(`Actualizado: ${horario.dia_semana}`);
    }
  } catch (e) { console.error("Error al guardar:", e); }
};

watch(() => peluqueroActivo.value?.id, cargarDatos);

onMounted(cargarDatos);
</script>

<style scoped>
/* REUTILIZAMOS TUS ESTILOS DE HORARIOS */
.servicios-view { padding: 30px; background: #f8fafc; min-height: 100vh; }
.section-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
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

.breadcrumb i { font-size: 0.6rem; }
.breadcrumb .current { color: #e75480; font-weight: 600; }

.title-container { display: flex; align-items: center; gap: 15px; }
.title-container h1 { margin: 0; font-size: 1.8rem; color: #2c3e50; font-weight: 800; }

.aviso-sin-peluquero {
  background: white;
  border: 1px dashed #e2e8f0;
  border-radius: 16px;
  padding: 40px;
  text-align: center;
  color: #64748b;
  font-weight: 600;
}

.table-card { background: white; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.03); overflow: hidden; border: 1px solid #edf2f7; margin-bottom: 30px; }
.card-header-inner { padding: 20px; border-bottom: 1px solid #f1f5f9; }
.card-header-inner h3 { margin: 0 0 6px 0; color: #1e293b; font-size: 1.1rem; }
.hint-text { margin: 0; font-size: 0.8rem; color: #94a3b8; }

.custom-table { width: 100%; border-collapse: collapse; }
.custom-table th { background: #fcfcfd; padding: 15px; text-align: left; color: #64748b; font-size: 0.75rem; text-transform: uppercase; }
.custom-table td { padding: 12px 15px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }

.day-label { font-weight: 700; color: #1e293b; text-transform: capitalize; }

.time-container-wrapper { display: flex; align-items: center; gap: 10px; }
.time-group { display: flex; gap: 5px; align-items: center; }
.time-input.small { padding: 5px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.85rem; width: 90px; color: #475569; }

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
.btn-clear-time:hover { background: #fee2e2; color: #ef4444; border-color: #fecaca; }

.btn-undo { display: flex; align-items: center; gap: 6px; background: #fff; border: 1px solid #e2e8f0; padding: 6px 12px; border-radius: 8px; font-size: 0.7rem; font-weight: 700; color: #e75480; cursor: pointer; }

.status-text { font-size: 0.7rem; font-weight: 700; margin-left: 8px; text-transform: uppercase; }
.text-open { color: #2ecc71; }
.text-closed { color: #94a3b8; }
.italic { font-style: italic; font-size: 0.8rem; }
.text-muted { color: #94a3b8; }

.switch { position: relative; display: inline-block; width: 34px; height: 18px; vertical-align: middle; }
.switch input { opacity: 0; width: 0; height: 0; }
.slider { position: absolute; cursor: pointer; inset: 0; background-color: #cbd5e0; transition: .4s; border-radius: 34px; }
.slider:before { position: absolute; content: ""; height: 12px; width: 12px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
input:checked + .slider { background-color: #e75480; }
input:checked + .slider:before { transform: translateX(16px); }

.fila-bloqueada { background-color: #f8fafc; opacity: 0.7; }

/* --- RESPONSIVE DESIGN --- */
@media (max-width: 992px) {
  .servicios-view { padding: 15px; }

  .custom-table,
  .custom-table thead,
  .custom-table tbody,
  .custom-table th,
  .custom-table td,
  .custom-table tr {
    display: block;
  }

  .custom-table thead tr {
    position: absolute;
    top: -9999px;
    left: -9999px;
  }

  .custom-table tr {
    border: 1px solid #edf2f7;
    border-radius: 12px;
    margin-bottom: 15px;
    background: white;
    padding: 10px;
  }

  .custom-table td {
    border: none;
    position: relative;
    padding-left: 45% !important;
    text-align: left;
    min-height: 45px;
    display: flex;
    align-items: center;
  }

  .custom-table td::before {
    position: absolute;
    left: 15px;
    width: 40%;
    white-space: nowrap;
    font-weight: 700;
    color: #64748b;
    font-size: 0.75rem;
    text-transform: uppercase;
  }

  .custom-table td:nth-of-type(1) { background: #f8fafc; border-radius: 8px; margin-bottom: 10px; padding-left: 15px !important; }
  .custom-table td:nth-of-type(1)::before { content: ""; }
  .custom-table td:nth-of-type(2)::before { content: "Estado"; }
  .custom-table td:nth-of-type(3)::before { content: "Mañana"; }
  .custom-table td:nth-of-type(4)::before { content: "Tarde"; }
  .custom-table td:nth-of-type(5)::before { content: "Acción"; }

  .time-container-wrapper { width: 100%; justify-content: flex-start; }
  .time-group { flex-wrap: wrap; }
  .time-input.small { width: 80px; font-size: 0.8rem; }

  .text-right {
    text-align: left !important;
    justify-content: flex-end;
    border-top: 1px solid #f1f5f9;
    margin-top: 5px;
  }

  .btn-undo { width: 100%; justify-content: center; padding: 12px; }
}

@media (max-width: 480px) {
  .title-container h1 { font-size: 1.4rem; }
  .header-left .breadcrumb { font-size: 0.7rem; }
  .time-input.small { width: 75px; }
}
</style>
