// src/router/index.js
import { createRouter, createWebHistory } from 'vue-router';
import { useStore } from '../store.js'; // 1. ¡IMPORTANTE IMPORTARLO!

// Vistas
import LoginView from '../views/LoginView.vue';
import InicioView from '../components/InicioSection.vue';
import ServiciosView from '../components/ServicesSection.vue';
import MisReservas from '../components/MisReservas.vue';
import AdminDashboard from '../views/AdminDashboard.vue'; // 2. Crea esta vista
import AvisoLegal from '../views/AvisoLegal.vue'; // 2. Crea esta vista
import Privacidad from '../views/Privacidad.vue'; // 2. Crea esta vista
import Cookies from '../views/Cookies.vue'; // 2. Crea esta vista
import promociones from '../views/ClienteFidelidad.vue'; // 2. Crea esta vista



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
  { path: '/aviso-legal', component: AvisoLegal },
  { path: '/privacidad', component: Privacidad },
  { path: '/cookies', component: Cookies },
  { path: '/promociones', component: promociones },

  
  // 4. AÑADIR LA RUTA DE ADMIN
  { 
    path: '/admin', 
    component: AdminDashboard,
    meta: { requiresAdmin: true, hideLayout: true }
  },
  { 
    path: '/mis-reservas', 
    name: 'mis-reservas',
    component: MisReservas, // Usamos el componente importado
    meta: { requiresAuth: true } // Opcional: Si quieres que solo entren logueados
  },
  { 
    path: '/promociones', 
    name: 'Promociones',
    component: promociones, // Usamos el componente importado
    meta: { requiresAuth: true } // Opcional: Si quieres que solo entren logueados
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

router.beforeEach((to, from, next) => {
  // 1. Revisamos si la ruta a la que va requiere autenticación
  const rutaProtegida = to.matched.some(record => record.meta.requiresAuth);
  
  // 2. Revisamos si hay un token o sesión activa en el localStorage
  // (Asegúrate de que cuando hagas login guardes algo como 'user-token')
  const usuarioLogueado = localStorage.getItem('user-session'); 

  if (rutaProtegida && !usuarioLogueado) {
    // Si intenta entrar a una ruta protegida y no está logueado, al login
    next('/login');
  } else {
    // En cualquier otro caso, le dejamos pasar
    next();
  }
});

export default router;