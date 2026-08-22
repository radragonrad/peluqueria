<!-- src/components/InicioSection.vue -->
<template>
  <section id="inicio" class="hero-section">
    <div class="hero-content">
      <h1>Essencia Barber Study</h1>
      <h2>Peluquería y Barbería en Villanueva del Ariscal</h2>
      <div v-if="!store.state.usuarioLogueado" id="auth-status">
        <router-link to="/login" style="color:white; text-decoration:none;" class="cta-button">Reserva tu cita</router-link>
      </div>
       
    </div>
  </section>
</template>


<script>
import { useStore } from '../store.js';

export default{
  setup(){
    const store = useStore()
    return {
      store
    };
  }
}
</script>