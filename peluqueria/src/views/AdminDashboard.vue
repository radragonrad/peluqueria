<template>
  <div class="admin-container">
    <button class="menu-toggle" @click="menuAbierto = !menuAbierto" aria-label="Abrir menú">
      <i class="fas" :class="menuAbierto ? 'fa-times' : 'fa-bars'"></i>
    </button>

    <div v-if="menuAbierto" class="menu-overlay" @click="menuAbierto = false"></div>

    <aside class="sidebar" :class="{ 'is-open': menuAbierto }">
      <div class="sidebar-brand">
        <i class="fas fa-cut"></i>
        {{ nombreUsuario }}
      </div>

      <div class="peluquero-switcher" v-if="peluqueros.length > 0">
        <p class="switcher-label"><i class="fas fa-user-tie"></i> Gestionando</p>
        <select v-model="peluqueroActivoId" class="select-peluquero">
          <option v-for="p in peluqueros" :key="p.id" :value="p.id">{{ p.nombre }}</option>
        </select>
      </div>

      <nav class="sidebar-menu">

        <template v-for="(comp, key) in todasEtiquetas" :key="key">
          <template v-if="!seccionesPermitidas || seccionesPermitidas.includes(key)">
            <button
              @click="cambiarSeccion(key)"
              :class="{ active: seccionActiva === key }"
            >
              <i :class="iconos[key]"></i>
              {{ comp }}
            </button>
            <div v-if="key === 'caja'" class="menu-divider"></div>
          </template>
        </template>

      </nav>

      <div class="sidebar-footer">
        <button @click="logout" class="btn-logout">
          <i class="fas fa-sign-out-alt"></i> 
          <span>Cerrar Sesión</span>
        </button>
      </div>
    </aside>

    <main class="main-content">      
      <component :is="componenteActual" @cambiar-seccion="cambiarSeccion($event)"/>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, provide, watch } from 'vue';
import { useRouter } from 'vue-router';
import AdminReportes from '../components/admin/AdminReportes.vue';
import AdminInformes from '../components/admin/AdminInformes.vue';
import AdminUsuarios from '../components/admin/AdminUsuarios.vue';
import AdminServicios from '../components/admin/AdminServicios.vue';
// import AdminReservas from '../components/admin/AdminReservas.vue';
import AdminHorarios from '../components/admin/AdminHorarios.vue';
import AdminHorariosPeluquero from '../components/admin/AdminHorariosPeluquero.vue';
import AdminDiasExcepciones from '../components/admin/AdminDiasExcepciones.vue';
// import AdminAgendar from '../components/admin/AdminAgendar.vue';
import Analisis from '../components/admin/AnalisisView.vue';
import Caja from '../components/admin/AdminCaja.vue';
import Etiquetas from '../components/admin/AdminEtiquetas.vue';
import Promociones from '../components/admin/AdminPromos.vue';
import CitasView from '../components/admin/CitasView.vue'; // Cambia el origen al nuevo archivo

const router = useRouter();

const rolUsuario = ref(localStorage.getItem('rol') ?? 'usuario');
const nombreUsuario = ref(localStorage.getItem('nombre') || localStorage.getItem('usuario') || 'Usuario');

const seccionActiva = ref('reportes');
const menuAbierto = ref(false);

const peluqueros = ref([]);
const peluqueroActivo = ref(null);

// Secciones accesibles cuando el contexto es "empleado" (el usuario logueado
// es empleado, o el peluquero seleccionado en el switcher lo es): pueden ver
// y gestionar sus propias citas y su propio horario, pero nada más.
const seccionesPermitidas = computed(() => {
  const esContextoEmpleado = rolUsuario.value === 'empleado' || peluqueroActivo.value?.rol === 'empleado';
  return esContextoEmpleado ? ['citas', 'horariosPeluquero'] : null; // null = sin restricción
});

const peluqueroActivoId = computed({
  get: () => peluqueroActivo.value?.id ?? null,
  set: (id) => {
    peluqueroActivo.value = peluqueros.value.find(p => p.id == id) ?? null;
  }
});

// Al cambiar de peluquero, ajustar sección activa
watch(peluqueroActivo, (nuevo) => {
  const permitido = (rolUsuario.value === 'empleado' || nuevo?.rol === 'empleado')
    ? ['citas', 'horariosPeluquero']
    : null;

  if (permitido && !permitido.includes(seccionActiva.value)) {
    seccionActiva.value = 'citas';
  } else if (!permitido && seccionActiva.value === 'citas' && rolUsuario.value !== 'empleado') {
    seccionActiva.value = 'reportes';
  }
});

provide('peluqueroActivo', peluqueroActivo);

onMounted(async () => {
  rolUsuario.value = localStorage.getItem('rol') ?? 'usuario';
  nombreUsuario.value = localStorage.getItem('nombre') || localStorage.getItem('usuario') || 'Usuario';

  try {
    const res = await fetch('/backend/api/obtener_peluqueros.php');
    const data = await res.json();
    peluqueros.value = Array.isArray(data) ? data : (data.peluqueros ?? []);
    if (peluqueros.value.length > 0) {
      const userId = parseInt(localStorage.getItem('userId'));
      const propio = peluqueros.value.find(p => p.usuario_id == userId);
      peluqueroActivo.value = propio ?? peluqueros.value[0];
      seccionActiva.value = seccionesPermitidas.value ? seccionesPermitidas.value[0] : 'reportes';
    }
  } catch (e) {
    console.error('Error al cargar peluqueros:', e);
  }
});

const todasEtiquetas = {
  reportes: 'Reportes',
  citas: 'Citas',
  analisis: 'Análisis',
  caja: 'Caja',
  usuarios: 'Usuarios',
  servicios: 'Servicios',
  horarios: 'Horarios',
  horariosPeluquero: 'Horarios Peluqueros',
  excepciones: 'Excepciones',
  etiquetas: 'Etiquetas',
  Promociones: 'Promociones',
  informes: 'Informes'
};


const iconos = {
  reportes: 'fas fa-chart-line',
  usuarios: 'fas fa-users',
  servicios: 'fas fa-concierge-bell',
  // reservas: 'fas fa-calendar-check',
  horarios: 'fas fa-clock',
  horariosPeluquero: 'fas fa-user-clock',
  citas: 'fas fa-calendar-check',
  excepciones: 'fas fa-calendar-times',
  // agendar: 'fas fa-calendar-check',
  analisis: 'fas fa-chart-pie',
  caja: 'fas fa-cash-register',
  etiquetas: 'fas fa-tags',
  Promociones: 'fas fa-ticket-alt',
  informes: 'fas fa-file-alt'
};

const componentes = {
  reportes: AdminReportes,
  usuarios: AdminUsuarios,
  servicios: AdminServicios,
  // reservas: AdminReservas,
  citas: CitasView,
  horarios: AdminHorarios,
  horariosPeluquero: AdminHorariosPeluquero,
  excepciones: AdminDiasExcepciones,
  // agendar: AdminAgendar,
  analisis: Analisis,
  caja: Caja,
  etiquetas: Etiquetas,
  Promociones: Promociones,
  informes: AdminInformes
};

const componenteActual = computed(() => componentes[seccionActiva.value]);

const cambiarSeccion = (key) => {
  if (seccionesPermitidas.value && !seccionesPermitidas.value.includes(key)) return;
  seccionActiva.value = key;
  menuAbierto.value = false;
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
  height: 100dvh;
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

.sidebar-menu {
  flex: 1;
  min-height: 0;
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
}

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
  padding: 5px;
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
    height: 100dvh; /* Altura visible real en Safari/iPad */
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

.tag-actions {
  display: flex;
  gap: 8px;
}

.btn-edit-tag {
  background: none;
  border: none;
  color: #64748b;
  cursor: pointer;
  transition: 0.2s;
}

.btn-edit-tag:hover {
  color: #3498db;
}

.btn-cancel-edit {
  width: 100%;
  margin-top: 8px;
  background: #f1f5f9;
  color: #475569;
  border: none;
  padding: 10px;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 600;
}

.btn-cancel-edit:hover {
  background: #e2e8f0;
}
.menu-divider {
  height: 2px;
  background-color: rgba(255, 255, 255, 0.1); /* Color suave sobre fondo negro */
  margin: 15px 20px; /* Espaciado arriba/abajo y a los lados */
  border: none;
}

/* Si el contenedor .sidebar-menu tiene flex-direction: column, esto lo mantendrá alineado */
.sidebar-menu {
  display: flex;
  flex-direction: column;
}

.peluquero-switcher {
  padding: 0 20px 16px;
  border-bottom: 1px solid #333;
}

.switcher-label {
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: #888;
  margin: 0 0 8px 0;
  display: flex;
  align-items: center;
  gap: 6px;
}

.select-peluquero {
  width: 100%;
  background: #2b2b2b;
  border: 1px solid #444;
  color: #fff;
  padding: 8px 10px;
  border-radius: 6px;
  font-size: 0.88rem;
  cursor: pointer;
  outline: none;
  appearance: auto;
}

.select-peluquero:focus {
  border-color: #e75480;
}
</style>