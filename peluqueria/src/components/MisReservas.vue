<template>
  <div class="mis-reservas-container">
    <h2><i class="fas fa-calendar-alt"></i> Mis Reservas</h2>

    <div v-if="cargando" class="loading">Cargando tus citas...</div>

    <div v-else-if="reservas.length === 0" class="no-reservas">
      <p>Aún no tienes ninguna reserva.</p>
      <router-link to="/servicios" class="btn-reserva-ahora">Reservar mi primera cita</router-link>
    </div>

    <div v-else class="reservas-grid">
      <div v-for="reserva in reservas" :key="reserva.id" class="reserva-card">
        <div class="reserva-header">
          <span class="fecha">{{ formatearFecha(reserva.fecha) }}</span>
          <span class="hora">{{ reserva.hora.substring(0,5) }} hs</span>
        </div>
        <div class="reserva-body">
          <h4>{{ reserva.servicio }}</h4>
          <p><i class="fas fa-user-tie"></i> Con: {{ reserva.peluquero }}</p>
          <p class="precio">{{ reserva.precio }}€</p>
        </div>
        <div class="reserva-footer">
            <span class="status-tag" :class="esPasada(reserva.fecha) ? 'pasada' : 'proxima'">
                {{ esPasada(reserva.fecha) ? 'Finalizada' : 'Próxima' }}
            </span>

            <button 
                v-if="!esPasada(reserva.fecha)" 
                @click="confirmarAnulacion(reserva.id)" 
                class="btn-anular"
            >
                <i class="fas fa-times"></i> Anular
            </button>
            </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useStore } from '../store';

const store = useStore();
const reservas = ref([]);
const cargando = ref(true);

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

const formatearFecha = (fechaStr) => {
  const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
  return new Date(fechaStr).toLocaleDateString('es-ES', opciones);
};

const esPasada = (fechaStr) => {
  return new Date(fechaStr) < new Date().setHours(0,0,0,0);
};

const confirmarAnulacion = async (reservaId) => {
  if (!confirm("¿Estás seguro de que deseas anular esta cita? Esta acción no se puede deshacer.")) return;

  const userId = store.state.userId || localStorage.getItem('userId');

  try {
    const res = await fetch('/api/cancelar_reserva.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        reserva_id: reservaId,
        user_id: userId
      })
    });

    const data = await res.json();

    if (data.success) {
      alert(data.message);
      // Actualizamos la lista local para que desaparezca la cita sin recargar la página
      reservas.value = reservas.value.filter(r => r.id !== reservaId);
    } else {
      alert("Error: " + data.message);
    }
  } catch (error) {
    console.error("Error al anular:", error);
    alert("No se pudo procesar la anulación.");
  }
};

onMounted(cargarReservas);
</script>

<style scoped>
.mis-reservas-container { padding: 40px; max-width: 1000px; margin: 0 auto; }
.reservas-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 30px; }
.reserva-card { background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; transition: transform 0.3s; }
.reserva-card:hover { transform: translateY(-5px); }
.reserva-header { background: #f8f9fa; padding: 15px; display: flex; justify-content: space-between; font-weight: bold; color: #e75480; }
.reserva-body { padding: 20px; }
.reserva-body h4 { margin: 0 0 10px 0; font-size: 1.2rem; }
.precio { color: #e75480; font-weight: bold; font-size: 1.1rem; margin-top: 10px; }
.status-tag { padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; }
.status-tag.proxima { background: #e3f2fd; color: #1976d2; }
.status-tag.pasada { background: #f5f5f5; color: #999; }

.reserva-footer {
  padding: 15px 20px;
  background: #fdfdfd;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-top: 1px solid #f5f5f5;
}

.btn-anular {
  background: none;
  border: 1px solid #ff4d6d;
  color: #ff4d6d;
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-anular:hover {
  background: #ff4d6d;
  color: white;
  box-shadow: 0 4px 10px rgba(255, 77, 109, 0.2);
}

.btn-anular i {
  margin-right: 4px;
}

</style>