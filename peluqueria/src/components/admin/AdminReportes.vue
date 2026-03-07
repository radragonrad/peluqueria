<template>
  <div class="reportes-view">
    <header class="section-header">
      <h1>Resumen General</h1>
    </header>
    
    <div class="stats-grid">
      <div class="stat-card">
        <span class="label">Citas Hoy</span>
        <span class="value">{{ reporte.stats?.hoy || 0 }}</span>
      </div>
      <div class="stat-card">
        <span class="label">Total Clientes</span>
        <span class="value">{{ reporte.stats?.clientes || 0 }}</span>
      </div>
    </div>
    
    <div class="table-card">
      <h4 class="table-title">Próximas Citas</h4>
      <table class="custom-table">
        <thead>
          <tr>
            <th>Fecha / Hora</th>
            <th>Cliente</th>
            <th>Servicio</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="c in reporte.proximas_reservas" :key="c.id">
            <td>{{ c.fecha }} - {{ c.hora }}</td>
            <td>{{ c.cliente }}</td>
            <td>{{ c.servicio }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const reporte = ref({ stats: {}, proximas_reservas: [] });

const cargarReporte = async () => {
  try {
    const res = await fetch('/backend/api/get_reporte_general.php', { credentials: 'include' });
    reporte.value = await res.json();
  } catch (error) {
    console.error("Error reporte:", error);
  }
};

onMounted(cargarReporte);
</script>

<style scoped>
.section-header { margin-bottom: 30px; }
.section-header h1 { font-size: 1.8rem; color: #1a1a1a; }

.stats-grid { display: flex; gap: 20px; margin-bottom: 30px; }

.stat-card {
  background: white;
  padding: 25px;
  border-radius: 12px;
  flex: 1;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.stat-card .label { color: #888; display: block; margin-bottom: 10px; font-size: 0.9rem; }
.stat-card .value { font-size: 2.2rem; font-weight: bold; color: #e75480; }

.table-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.05);
  overflow: hidden;
}

.table-title { padding: 20px; margin: 0; border-bottom: 1px solid #eee; font-size: 1.1rem; }

.custom-table { width: 100%; border-collapse: collapse; }
.custom-table th {
  background: #f8f9fa;
  padding: 15px;
  text-align: left;
  font-size: 0.8rem;
  color: #888;
  text-transform: uppercase;
}
.custom-table td { padding: 15px; border-bottom: 1px solid #eee; color: #444; }
</style>