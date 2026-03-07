<!-- src/components/ServicesSection.vue -->
<template>
  <section id="servicios" class="services-section">
    <div class="container">
      <h2 class="section-title">Servicios</h2>
      <p class="section-subtitle">Cortes Profesionales</p>
      
      <!-- Mensaje de carga -->
      <div v-if="loading" class="loading">
        Cargando servicios...
      </div>
      
      <!-- Mensaje de error -->
      <div v-else-if="error" class="error">
        {{ error }}
      </div>
      
      <!-- Lista de servicios -->
      <div v-else class="services-grid">
        
        <div 
          v-for="servicio in store.state.servicios" 
          :key="servicio.id" 
          class="service-card"
        >
          <div class="icon">
            <img :src="`/img-icons/${servicio.icono}`" :alt="servicio.nombre" class="service-svg-icon">
          </div>
          <h3>{{ servicio.nombre }}</h3>
          <p>{{ servicio.descripcion || 'Descripción no disponible' }}</p>
          
          <div v-if="store.state.usuarioLogueado" style="margin-top:1rem;">
            <div class="service-price" style="font-weight:bold; color:#e75480; margin-bottom:0.5rem;">
              {{ parseFloat(servicio.precio).toFixed(2) }}€
            </div>
            
            <button @click="abrirReserva(servicio)" class="btn-reserva" style="background:#e75480; color:white; border:none; padding:0.5rem 1rem; border-radius:5px; cursor:pointer; width:100%; font-weight:bold;">Reservar Cita</button>
          </div>
          <div v-else style="margin-top:1rem;">
            <router-link to="/login" style="color:#e75480; font-size:0.9rem; text-decoration:none;">
              Inicia sesión para reservar
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useStore } from '../store.js';

export default {
  setup() {
    const store = useStore();
    const loading = ref(true);
    const error = ref(null);

    onMounted(async () => {
      try {

        const response = await fetch('/backend/api/get_servicios.php');
        const data = await response.json();
        
        
        store.setServicios(data); // ✅ Ahora es reactivo
        
        loading.value = false;
        
        
      } catch (err) {
        console.error('Error:', err);
        error.value = 'Error al cargar servicios';
        loading.value = false;
      }
    });

    const abrirReserva = (servicio) => {
      store.abrirModal(servicio);
    };

    return {
      store,
      loading,
      error,
      abrirReserva
    };
  }
};
</script>

<style scoped>
.loading, .error {
  text-align: center;
  padding: 2rem;
  color: #f0f0f0;
}

.error {
  color: #ff6b6b;
}
</style>