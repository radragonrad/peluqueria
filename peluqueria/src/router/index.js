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
import ReportarIncidencia from '../views/ReportarIncidencia.vue'; // 2. Crea esta vista



// Otros componentes
import ContactoView from '../components/ContactoSection.vue'
import GaleriaView from '../components/GaleriaSection.vue'
import SobreNosotrosView from '../components/SobreNosotrosSection.vue'
import RestablecerPassword from '../views/ResetPasswordView.vue'


const routes = [
  {
    path: '/', component: InicioView,
    meta: {
      title: 'Essencia Barber Study | Peluquería y Barbería en Villanueva del Ariscal',
      description: 'Especialistas en cortes degradados, color y cuidado de barba en Villanueva del Ariscal. Reserva tu cita online y vive la experiencia Hair Studio.'
    }
  }, // 3. La raíz debe ser Inicio, no App
  { path: '/inicio', redirect: '/' },   // Opcional: redirigir /inicio a /
  {
    path: '/login', component: LoginView,
    meta: {
      title: 'Reserva tu Cita Online | Essencia Barber Study',
      description: 'Accede o regístrate para reservar tu cita en Essencia Barber Study, tu peluquería y barbería en Villanueva del Ariscal.'
    }
  },
  {
    path: '/contacto', component: ContactoView,
    meta: {
      title: 'Contacto y Ubicación | Essencia Barber Study',
      description: 'Encuéntranos en C. San Jose, 9, Villanueva del Ariscal. Horario, teléfono y cómo llegar a Essencia Barber Study.'
    }
  },
  {
    path: '/galeria', component: GaleriaView,
    meta: {
      title: 'Galería de Cortes y Trabajos | Essencia Barber Study',
      description: 'Descubre ejemplos reales de nuestros cortes de pelo, degradados y arreglos de barba en Villanueva del Ariscal.'
    }
  },
  {
    path: '/servicios', component: ServiciosView,
    meta: {
      title: 'Servicios de Peluquería y Barbería | Essencia Barber Study',
      description: 'Cortes degradados, arreglo de barba, coloración y más. Consulta todos nuestros servicios y reserva tu cita online en Villanueva del Ariscal.'
    }
  },
  {
    path: '/sobrenostros', component: SobreNosotrosView,
    meta: {
      title: 'Sobre Nosotros | Essencia Barber Study',
      description: 'Conoce al equipo de Essencia Barber Study, especialistas en estilismo masculino y barbería clásica en Villanueva del Ariscal.'
    }
  },
  { path: '/mis-reservas', component: MisReservas, meta: { noindex: true } },
  { path: '/aviso-legal', component: AvisoLegal, meta: { title: 'Aviso Legal | Essencia Barber Study' } },
  { path: '/privacidad', component: Privacidad, meta: { title: 'Política de Privacidad | Essencia Barber Study' } },
  { path: '/cookies', component: Cookies, meta: { title: 'Política de Cookies | Essencia Barber Study' } },
  { path: '/promociones', component: promociones, meta: { noindex: true } },
  { path: '/restablecer-password', component: RestablecerPassword, meta: { noindex: true } },
  { path: '/reportar-incidencia', component: ReportarIncidencia, meta: { noindex: true } },

  
  // 4. AÑADIR LA RUTA DE ADMIN
  {
    path: '/admin',
    component: AdminDashboard,
    meta: { requiresAdmin: true, hideLayout: true, noindex: true }
  },
  {
    path: '/mis-reservas',
    name: 'mis-reservas',
    component: MisReservas, // Usamos el componente importado
    meta: { requiresAuth: true, noindex: true } // Opcional: Si quieres que solo entren logueados
  },
  {
    path: '/promociones',
    name: 'Promociones',
    component: promociones, // Usamos el componente importado
    meta: { requiresAuth: true, noindex: true } // Opcional: Si quieres que solo entren logueados
  },
];

const DEFAULT_TITLE = 'Essencia Barber Study | Peluquería y Barbería en Villanueva del Ariscal';
const DEFAULT_DESCRIPTION = 'Especialistas en cortes degradados, color y cuidado de barba en Villanueva del Ariscal. Reserva tu cita online y vive la experiencia Hair Studio.';
const SITE_URL = 'https://rgutierrezhairstudio.com';

function setMetaTag(selector, attr, content) {
  let el = document.querySelector(selector);
  if (!el) return;
  el.setAttribute(attr, content);
}

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
    if (rol === 'admin' || rol === 'empleado') {
      next();
    } else {
      alert("Acceso restringido.");
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

// SEO: título, meta description, og:tags, canonical y noindex por ruta
router.afterEach((to) => {
  const title = to.meta.title || DEFAULT_TITLE;
  const description = to.meta.description || DEFAULT_DESCRIPTION;

  document.title = title;

  setMetaTag('meta[name="description"]', 'content', description);
  setMetaTag('meta[property="og:title"]', 'content', title);
  setMetaTag('meta[property="og:description"]', 'content', description);
  setMetaTag('meta[property="og:url"]', 'content', `${SITE_URL}${to.path}`);
  setMetaTag('link[rel="canonical"]', 'href', `${SITE_URL}${to.path}`);

  let robotsTag = document.querySelector('meta[name="robots"]');
  if (to.meta.noindex) {
    if (!robotsTag) {
      robotsTag = document.createElement('meta');
      robotsTag.setAttribute('name', 'robots');
      document.head.appendChild(robotsTag);
    }
    robotsTag.setAttribute('content', 'noindex, nofollow');
  } else if (robotsTag) {
    robotsTag.remove();
  }
});

export default router;