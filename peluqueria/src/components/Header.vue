<template>
  <header class="header">
    <div class="logo-container">
      <img src="/assets/bg-logo.png" alt="Logo" class="logo" />
    </div>
    
    <button 
      class="mobile-menu-toggle" 
      @click="toggleMenu"
      aria-label="Menu"
    >
      <span v-if="!isMenuOpen">☰</span>
      <span v-else>✕</span>
    </button>
    
    <nav class="nav-menu" :class="{ 'mobile-open': isMenuOpen }" @click="closeMenu">
      <ul>
        <li><router-link to="/inicio">Inicio</router-link></li>
        <li><router-link to="/sobrenostros">Nosotros</router-link></li>
        <li><router-link to="/servicios">Servicios</router-link></li>
        <li><router-link to="/galeria">Galería</router-link></li>
        <li><router-link to="/contacto">Contacto</router-link></li>
        <li class="auth-item"><AuthStatus /></li>
      </ul>
    </nav>
    
    <div class="social-icons" v-if="!isMobile">
      <a href="https://www.instagram.com/essenciabarberstudy/" target="_blank" rel="noopener">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
      </a>
    </div>
  </header>
</template>

<script>
import AuthStatus from './AuthStatus.vue';

export default {
  components: { AuthStatus },
  data() {
    return {
      isMenuOpen: false,
      isMobile: false
    };
  },
  mounted() {
    this.checkScreenSize();
    window.addEventListener('resize', this.checkScreenSize);
  },
  beforeUnmount() {
    window.removeEventListener('resize', this.checkScreenSize);
  },
  methods: {
    toggleMenu() {
      this.isMenuOpen = !this.isMenuOpen;
    },
    closeMenu() {
      this.isMenuOpen = false;
    },
    checkScreenSize() {
      this.isMobile = window.innerWidth <= 768;
      if (!this.isMobile) this.isMenuOpen = false;
    }
  }
};
</script>

<style scoped>
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 2rem;
  background-color: #000;
  color: white;
  position: sticky; /* Cambiado a sticky para que acompañe el scroll si quieres */
  top: 0;
  z-index: 2000;
  height: 80px; /* Un poco más de altura para evitar colapsos */
}
.logo { height: 60px; object-fit: contain; }

.nav-menu {
  flex-grow: 1; /* Esto hace que el menú ocupe el espacio central */
  display: flex;
  justify-content: center;
}

.nav-menu ul {
  display: flex;
  list-style: none;
  gap: 1.2rem; /* Espaciado ajustado */
  margin: 0;
  padding: 0;
  align-items: center;
}

.nav-menu a {
  color: white;
  text-decoration: none;
  font-size: 0.95rem;
  text-transform: uppercase;
  letter-spacing: 1px;
  transition: color 0.3s;
}

.nav-menu a:hover, .router-link-active { 
  color: #e75480; /* Dorado elegante */
}

.mobile-menu-toggle {
  display: none;
  background: none;
  border: none;
  color: white;
  font-size: 1.8rem;
  z-index: 2001;
}

@media (max-width: 1024px) {
  .nav-menu ul { gap: 0.8rem; }
  .nav-menu a { font-size: 0.85rem; }
}

/* RESPONSIVE MÓVIL */
@media (max-width: 768px) {
  .mobile-menu-toggle {
    display: block;
  }
  
  .nav-menu {
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    background: rgba(0, 0, 0, 0.98);
    display: flex;
    justify-content: center;
    align-items: center;
    transform: translateX(100%); /* Escondido a la derecha */
    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  }
  
  .nav-menu.mobile-open {
    transform: translateX(0); /* Entra a pantalla */
  }
  
  .nav-menu ul {
    flex-direction: column;
    text-align: center;
    gap: 2rem;
  }

  .nav-menu a {
    font-size: 1.5rem;
  }

  .auth-item {
    margin-top: 1rem;
  }
}
</style>