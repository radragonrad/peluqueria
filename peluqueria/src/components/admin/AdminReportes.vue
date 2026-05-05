<template>
  <div class="reportes-view">
    <header class="section-header">
      <div class="header-content">       
        <p class="subtitle">Estado actual de la peluquería al {{ fechaHoy }}</p>
      </div>
      <button @click="cargarReporte" class="btn-refresh">
        <i class="fas fa-sync-alt"></i> Actualizar
      </button>
    </header>
    
    <div class="stats-grid">
      <div class="stat-card highlight">
        <div class="stat-icon"><i class="fas fa-calendar-day"></i></div>
        <div class="stat-info">
          <span class="label">Citas Hoy</span>
          <span class="value">{{ reporte.stats?.hoy || 0 }}</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-users"></i></div>
        <div class="stat-info">
          <span class="label">Total Clientes</span>
          <span class="value">{{ reporte.stats?.clientes || 0 }}</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
          <span class="label">Citas Semanales</span>
          <span class="value">{{ totalSemana }}</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon yellow"><i class="fas fa-star"></i></div>
        <div class="stat-info">
          <span class="label">Servicio Estrella</span>
          <span class="value small-value">{{ reporte.stats?.estrella || '---' }}</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-user-check"></i></div>
        <div class="stat-info">
          <span class="label">Usuarios Activos</span>
          <span class="value">{{ reporte.stats?.usuarios_activos || 0 }}</span>
        </div>
      </div>
    </div>

    <div class="report-section main-grid">
      <div class="chart-container card">
        <h4 class="card-title"><i class="fas fa-chart-bar"></i> Citas Próximos 7 Días</h4>
        <div class="bar-chart">
          <div v-for="dia in proximos7Dias" :key="dia.fecha" class="bar-wrapper">
            <div class="bar-label-top">{{ dia.cantidad }}</div>
            <div 
              class="bar" 
              :style="{ height: calcularAltura(dia.cantidad) }"
              :class="{ 'is-today': dia.esHoy }"
            ></div>
            <div class="bar-label-bottom">
              <span class="day-name">{{ dia.nombreDia }}</span>
              <span class="day-date">{{ dia.soloFecha }}</span>
            </div>
          </div>
        </div>
      </div>

      <div class="table-card card">
        <div class="card-header-flex">
          <h4 class="card-title"><i class="fas fa-clock"></i> Últimas Reservas</h4>
          <span class="badge">Próximas</span>
        </div>
        <div class="table-responsive">
          <table class="custom-table">
            <thead>
              <tr>
                <th>Cliente</th>
                <th>Servicio</th>
                <th>Hora</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="c in reporte.proximas_reservas" :key="c.id">
                <td class="font-bold">{{ c.cliente }}</td>
                <td><span class="service-tag">{{ c.servicio }}</span></td>
                <td class="text-pink">{{ c.hora }}</td>
              </tr>
              <tr v-if="!reporte.proximas_reservas?.length">
                <td colspan="3" class="text-center text-muted">No hay citas próximas</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="ranking-card card">
  <h4 class="card-title"><i class="fas fa-trophy"></i> Top Servicios</h4>
  <div class="ranking-list">
    <div v-for="(serv, index) in reporte.ranking_servicios" :key="index" class="ranking-item">
      <div class="rank-number">{{ index + 1 }}</div>
      <div class="rank-info">
        <span class="rank-name">{{ serv.nombre }}</span>
        <span class="rank-count">{{ serv.total }} citas</span>
      </div>
      <div class="rank-price">{{ serv.precio }}€</div>
    </div>
  </div>
</div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';

const reporte = ref({ 
  stats: { hoy: 0, clientes: 0 }, 
  proximas_reservas: [],
  distribucion_semanal: [] // Esperamos esto del backend: {fecha: '2023-10-01', cantidad: 5}
});

const fechaHoy = new Date().toLocaleDateString('es-ES', { 
  weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' 
});

const cargarReporte = async () => {
  try {
    const res = await fetch('/backend/api/get_reporte_general.php', { credentials: 'include' });
    const data = await res.json();
    reporte.value = data;
  } catch (error) {
    console.error("Error reporte:", error);
  }
};

// Lógica para el mini gráfico de barras
const proximos7Dias = computed(() => {
  // Si el backend no envía distribución, simulamos o manejamos el vacío
  return reporte.value.distribucion_semanal || [];
});

const totalSemana = computed(() => {
  return proximos7Dias.value.reduce((acc, current) => acc + current.cantidad, 0);
});

const calcularAltura = (cantidad) => {
  if (cantidad === 0) return '5%';
  const max = Math.max(...proximos7Dias.value.map(d => d.cantidad), 1);
  return `${(cantidad / max) * 80 + 5}%`; // Mínimo 5% para que se vea algo
};

onMounted(cargarReporte);
</script>

<style scoped>
.reportes-view { padding: 10px; }

/* Header */
.section-header { 
  display: flex; 
  justify-content: space-between; 
  align-items: center; 
  margin-bottom: 30px; 
}
.subtitle { color: #888; margin-top: 5px; }

/* Tarjetas de Estadísticas */
.stats-grid { 
  display: grid; 
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); 
  gap: 20px; 
  margin-bottom: 30px; 
}

.stat-card {
  background: white;
  padding: 20px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  gap: 20px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.03);
}

.stat-icon {
  width: 50px; height: 50px;
  border-radius: 12px;
  background: #fff0f5;
  color: #e75480;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}
.stat-icon.blue { background: #eef2ff; color: #4f46e5; }
.stat-icon.green { background: #f0fdf4; color: #16a34a; }

.stat-info .label { color: #888; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; }
.stat-info .value { font-size: 1.8rem; font-weight: 800; color: #1a1a1a; display: block; }

/* Grid principal: Gráfico y Tabla */
.main-grid {
  display: grid;
  grid-template-columns: 1.5fr 1fr;
  gap: 25px;
}

@media (max-width: 1100px) {
  .main-grid { grid-template-columns: 1fr; }
}

.card {
  background: white;
  border-radius: 16px;
  padding: 20px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.03);
}

.card-title {
  margin: 0 0 20px 0;
  font-size: 1.1rem;
  color: #333;
  display: flex;
  align-items: center;
  gap: 10px;
}

/* Gráfico de Barras Manual */
.bar-chart {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  height: 250px;
  padding-top: 20px;
}

.bar-wrapper {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  height: 100%;
}

.bar {
  width: 60%;
  background: #fce7f3;
  border-radius: 8px 8px 4px 4px;
  transition: all 0.5s ease;
  position: relative;
  max-width: 40px;
}

.bar:hover { background: #e75480; }
.bar.is-today { background: #e75480; }

.bar-label-top { font-weight: bold; font-size: 0.9rem; color: #e75480; }
.bar-label-bottom { text-align: center; display: flex; flex-direction: column; }
.day-name { font-size: 0.75rem; font-weight: bold; color: #333; }
.day-date { font-size: 0.7rem; color: #999; }

/* Tabla */
.service-tag {
  background: #f3f4f6;
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 0.75rem;
  color: #4b5563;
}

.text-pink { color: #e75480; font-weight: bold; }

.btn-refresh {
  background: white;
  border: 1px solid #ddd;
  padding: 8px 16px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: 0.2s;
}
.btn-refresh:hover { background: #f9f9f9; border-color: #e75480; color: #e75480; }

.badge {
  background: #e75480;
  color: white;
  font-size: 0.7rem;
  padding: 2px 8px;
  border-radius: 10px;
  text-transform: uppercase;
}

.card-header-flex {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.stat-icon.yellow { background: #fffbeb; color: #f59e0b; }
.stat-icon.purple { background: #f5f3ff; color: #7c3aed; }
.small-value { font-size: 1.2rem !important; text-transform: uppercase; }

.ranking-list { display: flex; flex-direction: column; gap: 15px; }

.ranking-item {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 10px;
  background: #fcfcfc;
  border-radius: 12px;
  border: 1px solid #f0f0f0;
}

.rank-number {
  width: 28px; height: 28px;
  background: #e75480;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 0.8rem;
}

.rank-info { flex: 1; display: flex; flex-direction: column; }
.rank-name { font-weight: 700; color: #333; font-size: 0.9rem; }
.rank-count { font-size: 0.75rem; color: #888; }
.rank-price { font-weight: 800; color: #e75480; }

h1 {
    font-size: 3.2em;
    line-height: 1.1;
    color: #000000;
}
</style>