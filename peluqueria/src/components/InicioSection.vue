<!-- src/components/InicioSection.vue -->
<template>
  <section id="inicio" class="hero-section">
    <div class="hero-content">
      <h1>RÚBEN PELUQUEROS</h1>
      <h2>TU PELUQUERÍA EN VILLANUEVA DEL ARISCAL</h2>
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