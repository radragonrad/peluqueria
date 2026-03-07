<template>
  <div class="admin-container">
    <button class="menu-toggle" @click="menuAbierto = !menuAbierto" aria-label="Abrir menú">
      <i class="fas" :class="menuAbierto ? 'fa-times' : 'fa-bars'"></i>
    </button>

    <div v-if="menuAbierto" class="menu-overlay" @click="menuAbierto = false"></div>

    <aside class="sidebar" :class="{ 'is-open': menuAbierto }">
      <div class="sidebar-brand">
        <i class="fas fa-cut"></i>
        Admin Rúben
      </div>
      
      <nav class="sidebar-menu">
        <button 
          v-for="(comp, key) in etiquetas" 
          :key="key"
          @click="cambiarSeccion(key)" 
          :class="{ active: seccionActiva === key }"
        >
          <i :class="iconos[key]"></i>
          {{ comp }}
        </button>
      </nav>

      <div class="sidebar-footer">
        <button @click="logout" class="btn-logout">
          <i class="fas fa-sign-out-alt"></i> 
          <span>Cerrar Sesión</span>
        </button>
      </div>
    </aside>

    <main class="main-content">
      <div class="content-header">
        <h1>{{ etiquetas[seccionActiva] }}</h1>
      </div>
      <component :is="componenteActual" />
    </main>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import AdminReportes from '../components/admin/AdminReportes.vue';
import AdminUsuarios from '../components/admin/AdminUsuarios.vue';
import AdminServicios from '../components/admin/AdminServicios.vue';
import AdminReservas from '../components/admin/AdminReservas.vue';
import AdminHorarios from '../components/admin/AdminHorarios.vue';
import AdminDiasExcepciones from '../components/admin/AdminDiasExcepciones.vue';

const router = useRouter();
const seccionActiva = ref('reportes');
const menuAbierto = ref(false);

const etiquetas = {
  reportes: 'Reportes',
  usuarios: 'Usuarios',
  servicios: 'Servicios',
  reservas: 'Reservas',
  horarios: 'Horarios',
  excepciones: 'Excepciones'
};

const iconos = {
  reportes: 'fas fa-chart-line',
  usuarios: 'fas fa-users',
  servicios: 'fas fa-concierge-bell',
  reservas: 'fas fa-calendar-check',
  horarios: 'fas fa-clock',
  excepciones: 'fas fa-calendar-times'
};

const componentes = {
  reportes: AdminReportes,
  usuarios: AdminUsuarios,
  servicios: AdminServicios,
  reservas: AdminReservas,
  horarios: AdminHorarios,
  excepciones: AdminDiasExcepciones
};

const componenteActual = computed(() => componentes[seccionActiva.value]);

const cambiarSeccion = (key) => {
  seccionActiva.value = key;
  menuAbierto.value = false; // Cerrar menú automáticamente al elegir sección en móvil
};

const logout = () => {
  localStorage.clear();
  router.push('/login');
};
</script>

<style scoped>
.admin-container {
  display: flex;
  height: 100vh;
  width: 100vw;
  background-color: #f4f7f6;
  overflow: hidden;
  position: relative;
}

/* --- SIDEBAR --- */
.sidebar {
  width: 260px;
  background-color: #1a1a1a;
  color: white;
  display: flex;
  flex-direction: column;
  flex-shrink: 0;
  transition: all 0.3s ease;
  z-index: 1000;
}

.sidebar-brand {
  padding: 30px 25px;
  font-size: 1.2rem;
  font-weight: bold;
  color: #e75480;
  display: flex;
  align-items: center;
  gap: 12px;
}

.sidebar-menu { flex: 1; }

.sidebar-menu button {
  width: 100%;
  padding: 15px 25px;
  background: none;
  border: none;
  color: #ccc;
  text-align: left;
  cursor: pointer;
  transition: 0.3s;
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 0.95rem;
}

.sidebar-menu button i { width: 20px; text-align: center; }

.sidebar-menu button:hover, .sidebar-menu button.active {
  background: #2b2b2b;
  color: white;
}

.sidebar-menu button.active {
  border-left: 4px solid #e75480;
  background: linear-gradient(90deg, #2b2b2b 0%, #1a1a1a 100%);
}

.sidebar-footer { padding: 20px; border-top: 1px solid #333; }

.btn-logout {
  width: 100%;
  padding: 12px;
  background: transparent;
  border: 1px solid #e75480;
  color: #e75480;
  border-radius: 8px;
  cursor: pointer;
  font-weight: bold;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.btn-logout:hover { background: #e75480; color: white; }

/* --- CONTENIDO PRINCIPAL --- */
.main-content {
  flex: 1;
  padding: 30px;
  overflow-y: auto;
  background: #f8f9fa;
}

.content-header h1 {
  font-size: 1.8rem;
  color: #333;
  margin-bottom: 25px;
}

/* --- RESPONSIVE / MÓVIL --- */

/* Botón Hamburguesa */
.menu-toggle {
  display: none;
  position: fixed;
  top: 15px;
  right: 15px;
  z-index: 1100;
  background: #e75480;
  color: white;
  border: none;
  width: 45px;
  height: 45px;
  border-radius: 8px;
  cursor: pointer;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

@media (max-width: 992px) {
  .menu-toggle { display: block; }

  .sidebar {
    position: fixed;
    left: -260px; /* Escondido */
    top: 0;
    bottom: 0;
  }

  .sidebar.is-open {
    left: 0; /* Visible */
  }

  .menu-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 999;
  }

  .main-content {
    padding: 20px;
    padding-top: 70px; /* Espacio para el botón de menú */
  }

  .content-header h1 {
    font-size: 1.5rem;
  }
}
</style>