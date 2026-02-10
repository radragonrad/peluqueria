<!-- js/components/Header.vue -->
<template>
  <header class="header">
    <div class="logo-container">
      <img src="../../assets/logo.png" alt="Logo" class="logo" />
    </div>
    <nav class="nav-menu">
      <ul>
        <li><a href="#inicio">Inicio</a></li>
        <li><a href="#sobre-nosotros">Sobre nosotros</a></li>
        <li><a href="#servicios">Servicios</a></li>
        <li><a href="#galeria">Galería</a></li>
        <li><a href="#contacto">Contacto y horario</a></li>
        <AuthStatus />
      </ul>
    </nav>
    <div class="social-icons">
      <a href="https://www.instagram.com/rgutierrez212" target="_blank" rel="noopener" aria-label="Instagram">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
          <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
          <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>
        </svg>
      </a>
    </div>
  </header>
</template>

<script>
import AuthStatus from './AuthStatus.vue';

export default {
  components: { AuthStatus }
};
</script>