<template>
  <div class="analisis-view">
    <header class="card filter-header">
      <div class="filter-controls">
        <div class="input-group">
          <label>Desde</label>
          <input type="date" v-model="filtros.inicio" class="input-custom">
        </div>
        <div class="input-group">
          <label>Hasta</label>
          <input type="date" v-model="filtros.fin" class="input-custom">
        </div>
        <button @click="cargarAnalisis" class="btn-filtrar-web">
          <i class="fas fa-search"></i> Filtrar
        </button>
      </div>
    </header>

    <div class="resumen-grid" v-if="datos.resumen">
      <div class="kpi-card">
        <div class="kpi-icon pico"><i class="fas fa-crown"></i></div>
        <div class="kpi-data">
          <span class="kpi-label">Día Pico</span>
          <span class="kpi-value">{{ datos.resumen.diaPico }}</span>
        </div>
      </div>
      <div class="kpi-card">
        <div class="kpi-icon h-pico"><i class="fas fa-fire"></i></div>
        <div class="kpi-data">
          <span class="kpi-label">Hora Oro</span>
          <span class="kpi-value">{{ datos.resumen.horaPicoGral }}</span>
        </div>
      </div>
      <div class="kpi-card">
        <div class="kpi-icon valle"><i class="fas fa-bed"></i></div>
        <div class="kpi-data">
          <span class="kpi-label">Día Valle</span>
          <span class="kpi-value">{{ datos.resumen.diaValle }}</span>
        </div>
      </div>
      <div class="kpi-card">
        <div class="kpi-icon h-valle"><i class="fas fa-coffee"></i></div>
        <div class="kpi-data">
          <span class="kpi-label">Hora Valle</span>
          <span class="kpi-value">{{ datos.resumen.horaValleGral }}</span>
        </div>
      </div>
    </div>

    <div class="semanas-wrapper">
      <section v-for="(semana, idx) in semanasAgrupadas" :key="idx" class="card days-overview">
        <h4 class="semana-titulo">Semana {{ idx + 1 }}</h4>
        <div class="days-container">
          <div v-for="dia in semana" :key="dia.fecha" class="day-item">
            
            <div class="day-counts-wrapper">
              <span class="day-count validas" title="Pendientes y Completadas">
                {{ dia.cantidad }}
              </span>
              <span v-if="dia.anuladas > 0" class="day-count anuladas" title="Anuladas">
                {{ dia.anuladas }}
              </span>
            </div>

            <div class="status-dot" :class="getClaseColor(dia.cantidad)"></div>
            
            <span class="day-name">{{ dia.nombreDia }}</span>
            <span class="day-date">{{ dia.soloFecha }}</span>

            <div class="day-stats">
              <div class="stat-row pico" v-if="dia.horaPico">
                <i class="fas fa-arrow-up"></i> {{ dia.horaPico.substring(0, 5) }}
              </div>
              <div class="stat-row valle" v-if="dia.horaValle">
                <i class="fas fa-arrow-down"></i> {{ dia.horaValle.substring(0, 5) }}
              </div>
              <div class="stat-row empty" v-else>
                <span class="no-data">--:--</span>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';

const filtros = reactive({
  inicio: new Date(Date.now() - 7*24*60*60*1000).toISOString().split('T')[0],
  fin: new Date().toISOString().split('T')[0]
});

const datos = ref({ porDias: [], resumen: null });

// Computed para agrupar en filas de 7 días
const semanasAgrupadas = computed(() => {
  const res = [];
  if (!datos.value.porDias) return [];
  for (let i = 0; i < datos.value.porDias.length; i += 7) {
    res.push(datos.value.porDias.slice(i, i + 7));
  }
  return res;
});

const cargarAnalisis = async () => {
  try {
    const r = await fetch(`/backend/api/get_analisis_detallado.php?inicio=${filtros.inicio}&fin=${filtros.fin}`, {
      credentials: 'include' 
    });
    const result = await r.json();

    if (result.success === false && result.message.includes("Acceso denegado")) {
      // La sesión expiró, mandamos al login
      alert("Tu sesión ha expirado por inactividad. Por favor, inicia sesión de nuevo.");
      window.location.href = "/login.php"; 
      return;
    }

    datos.value = result;
  } catch (e) {
    console.error("Error cargando análisis:", e);
  }
};

const getClaseColor = (c) => {
  if (c === 0) return 'dot-empty';
  const nums = datos.value.porDias.map(d => d.cantidad);
  if (c === Math.max(...nums)) return 'dot-max';
  if (c === Math.min(...nums.filter(n => n > 0))) return 'dot-min';
  return 'dot-normal';
};

onMounted(cargarAnalisis);
</script>

<style scoped>
/* ESTILOS GENERALES Y RESETEOS */
.analisis-view { padding: 20px; animation: fadeIn 0.5s ease-in; }
.card { background: white; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
section { margin-top: 0 !important; } /* Corrección del margen global */

/* --- CSS RESTAURADO DE FILTROS --- */
.filter-header { padding: 15px 25px; }
.filter-controls { display: flex; align-items: flex-end; gap: 20px; flex-wrap: wrap; }
.input-group { display: flex; flex-direction: column; gap: 5px; }
.input-group label { font-size: 0.75rem; font-weight: 700; color: #888; text-transform: uppercase; }

/* Input Custom con borde original */
.input-custom {
  padding: 8px 12px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-family: inherit;
  font-size: 0.9rem;
  color: #333;
}

/* Botón Rosa Web Original */
.btn-filtrar-web {
  background-color: #e75480;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  font-weight: bold;
  cursor: pointer;
  transition: background 0.2s;
  display: flex;
  align-items: center;
  gap: 8px;
}
.btn-filtrar-web:hover { background-color: #d44470; }

/* KPIs GLOBAL */
.resumen-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px; }
.kpi-card { background: white; padding: 15px; border-radius: 12px; display: flex; align-items: center; gap: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.kpi-icon { width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
.kpi-icon.pico { background: #dcfce7; color: #16a34a; }
.kpi-icon.h-pico { background: #fce7f3; color: #e75480; }
.kpi-icon.valle { background: #fef3c7; color: #d97706; }
.kpi-icon.h-valle { background: #e0f2fe; color: #0284c7; }
.kpi-label { font-size: 0.7rem; color: #888; text-transform: uppercase; display: block; font-weight: 600; }
.kpi-value { font-weight: 800; color: #333; font-size: 1rem; }

/* --- CSS DEL GRÁFICO (CORREGIDO) --- */
.semanas-wrapper { display: flex; flex-direction: column; gap: 10px; }
.semana-titulo { font-size: 0.75rem; color: #e75480; text-transform: uppercase; margin-bottom: 15px; border-bottom: 1px dashed #eee; padding-bottom: 5px; }

/* Grid forzado a 7 columnas */
.days-container { display: grid; grid-template-columns: repeat(7, 1fr); gap: 10px; padding: 10px 0; }
.day-item { display: flex; flex-direction: column; align-items: center; gap: 8px; text-align: center; }

/* Contadores */
.day-counts-wrapper { display: flex; gap: 6px; align-items: baseline; justify-content: center; height: 28px; }
.day-count.validas { font-size: 1.4rem; font-weight: 800; color: #e75480; }
.day-count.anuladas { font-size: 0.85rem; font-weight: 600; color: #94a3b8; background: #f1f5f9; padding: 1px 6px; border-radius: 4px; text-decoration: line-through; }

/* Bolita de Estado */
.status-dot { width: 100%; max-width: 60px; height: 12px; border-radius: 10px; transition: all 0.3s ease; }
.dot-max { background: #10b981; box-shadow: 0 2px 8px rgba(16,185,129,0.2); }
.dot-min { background: #f59e0b; box-shadow: 0 2px 8px rgba(245,158,11,0.2); }
.dot-normal { background: #e75480; }
.dot-empty { background: #eee; }

/* Textos del día */
.day-name { font-weight: 700; color: #444; font-size: 0.9rem; text-transform: capitalize; }
.day-date { font-size: 0.75rem; color: #999; }

/* Horas Pico/Valle */
.day-stats { display: flex; flex-direction: column; gap: 4px; width: 100%; margin-top: 5px; }
.stat-row { font-size: 0.7rem; font-weight: 700; padding: 2px; border-radius: 4px; display: flex; align-items: center; justify-content: center; gap: 3px; }
.stat-row.pico { color: #10b981; background: rgba(16, 185, 129, 0.08); }
.stat-row.valle { color: #f59e0b; background: rgba(245, 158, 11, 0.08); }
.no-data { color: #ccc; font-size: 0.7rem; }

/* Responsive */
@media (max-width: 992px) {
  .filter-controls { flex-direction: column; align-items: stretch; gap: 10px; }
  .days-container { display: flex; overflow-x: auto; justify-content: flex-start; padding-bottom: 15px; }
  .day-item { min-width: 85px; }
}

@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
</style>