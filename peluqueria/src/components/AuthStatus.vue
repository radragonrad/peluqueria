<template>
  <div class="auth-wrapper">
    <template v-if="store.state.usuarioLogueado">
      <div class="auth-group">
        <router-link to="/mis-reservas" class="link-reservas">
          Mis Reservas
        </router-link>
        
        <span class="user-email" v-if="!isMobile">{{ store.state.emailUsuario }}</span>
        
        <button @click="cerrarSesion" class="btn-logout" title="Cerrar sesión">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M10 3H6a2 2 0 0 0-2 2v14c0 1.1.9 2 2 2h4"></path>
          <polyline points="16 17 21 12 16 7"></polyline>
          <line x1="21" y1="12" x2="9" y2="12"></line>
        </svg>
        </button>
      </div>
    </template>

    <template v-else>
      <router-link to="/login" class="btn-cita">
        Reserva tu cita
      </router-link>
    </template>
  </div>
</template>

<script>
import { useStore } from '../store.js';
import { ref, onMounted, onBeforeUnmount } from 'vue';

export default {
  setup() {
    const store = useStore();
    const isMobile = ref(window.innerWidth <= 768);

    const checkSize = () => { isMobile.value = window.innerWidth <= 768; };
    onMounted(() => window.addEventListener('resize', checkSize));
    onBeforeUnmount(() => window.removeEventListener('resize', checkSize));

    const cerrarSesion = async () => {
      try {
        await fetch('/backend/api/logout.php', {
          method: 'POST',
          credentials: 'include'
        });
        store.setUsuarioLogueado(false, '', null);
        localStorage.clear(); // Limpiamos todo de una vez
        window.location.href = '/inicio';
      } catch (error) {
        console.error('Error al cerrar sesión:', error);
      }
    };

    return { store, cerrarSesion, isMobile };
  }
};
</script>

<style scoped>
.auth-wrapper {
  display: flex;
  align-items: center;
}

.auth-group {
  display: flex;
  align-items: center;
  gap: 15px;
}

.user-email {
  color: #888;
  font-size: 0.85rem;
}

.link-reservas {
  color: white !important;
  text-decoration: none;
  font-weight: bold;
  font-size: 0.9rem;
}

.link-reservas:hover {
  color: #e75480 !important;
}

.btn-logout {
  background: none;
  border: none;
  color: #e75480; /* Tu color rosa/fucsia */
  cursor: pointer;
  padding: 5px;
  display: flex;
  align-items: center;
  transition: transform 0.2s;
}

.btn-logout:hover {
  transform: scale(1.1);
}

/* Botón "Reserva tu cita" */
.btn-cita {
  background: transparent;
    border: 2px solid white;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    font-weight: bold;
    transition: all 0.3s;
}

.btn-cita:hover {
  background-color: #ffffff;
  color: black;
}

/* Ajustes para Móvil */
@media (max-width: 768px) {
  .auth-group {
    flex-direction: column;
    gap: 20px;
  }
  
  .link-reservas, .btn-cita {
    font-size: 1.4rem !important;
  }

  .btn-logout {
    border: 1px solid #e75480;
    padding: 10px 30px;
    border-radius: 50px;
  }
}
</style>