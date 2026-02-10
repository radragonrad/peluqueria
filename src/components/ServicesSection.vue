<!-- js/components/ServicesSection.vue -->
<template>
  <section id="servicios" class="services-section">
    <div class="container">
      <h2 class="section-title">Servicios</h2>
      <p class="section-subtitle">Cortes Profesionales</p>
      
      <div class="services-grid">
        <div 
          v-for="servicio in store.servicios" 
          :key="servicio.id" 
          class="service-card"
        >
          <div class="icon" v-html="servicio.icono"></div>
          <h3>{{ servicio.nombre }}</h3>
          <p>{{ servicio.descripcion || 'Descripción no disponible' }}</p>
          
          <div v-if="store.usuarioLogueado" style="margin-top:1rem;">
            <div class="service-price" style="font-weight:bold; color:#e75480; margin-bottom:0.5rem;">
              {{ parseFloat(servicio.precio).toFixed(2) }}€
            </div>
            <button 
              @click="abrirReserva(servicio)"
              style="background:#e75480; color:white; border:none; padding:0.5rem 1rem; border-radius:6px; cursor:pointer;"
            >
              Reservar
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import { onMounted } from 'vue';
import { useStore } from '../store.js';

export default {
  setup() {
    const store = useStore();

    // Cargar servicios al montar
    onMounted(async () => {
    try {
        const res = await fetch('/api/get_servicios.php');
        const data = await res.json();
        store.setServicios(data); // ← Usa la mutación
    } catch (error) {
        console.error('Error al cargar servicios:', error);
    }
    });

    const abrirReserva = (servicio) => {
      store.abrirModal(servicio);
    };

    return {
      store,
      abrirReserva
    };
  }
};
</script>