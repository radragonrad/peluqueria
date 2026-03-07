// src/router/index.js
import { createRouter, createWebHistory } from 'vue-router';
import { useStore } from '../store.js'; // 1. ¡IMPORTANTE IMPORTARLO!

// Vistas
import LoginView from '../views/LoginView.vue';
import InicioView from '../components/InicioSection.vue';
import ServiciosView from '../components/ServicesSection.vue';
import MisReservas from '../components/MisReservas.vue';
import AdminDashboard from '../views/AdminDashboard.vue'; // 2. Crea esta vista

// Otros componentes
import ContactoView from '../components/ContactoSection.vue'
import GaleriaView from '../components/GaleriaSection.vue'
import SobreNosotrosView from '../components/SobreNosotrosSection.vue'

const routes = [
  { path: '/', component: InicioView }, // 3. La raíz debe ser Inicio, no App
  { path: '/inicio', redirect: '/' },   // Opcional: redirigir /inicio a /
  { path: '/login', component: LoginView },
  { path: '/contacto', component: ContactoView },
  { path: '/galeria', component: GaleriaView },
  { path: '/servicios', component: ServiciosView },
  { path: '/sobrenostros', component: SobreNosotrosView },
  { path: '/mis-reservas', component: MisReservas },
  
  // 4. AÑADIR LA RUTA DE ADMIN
  { 
    path: '/admin', 
    component: AdminDashboard,
    meta: { requiresAdmin: true, hideLayout: true }
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

// Guardia de navegación
router.beforeEach((to, from, next) => {
  // Obtenemos el store aquí dentro
  const store = useStore(); 
  const rol = localStorage.getItem('rol'); 

  // Verificamos si la ruta requiere admin (usando la meta propiedad)
  if (to.matched.some(record => record.meta.requiresAdmin)) {
    if (rol === 'admin') {
      next();
    } else {
      alert("Acceso restringido a administradores.");
      next('/login');
    }
  } else {
    next();
  }
});

export default router;