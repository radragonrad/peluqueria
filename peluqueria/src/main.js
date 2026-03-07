// src/main.js
import { createApp } from 'vue';
import './style.css';
import App from './App.vue';
import router from './router'
import { useStore } from './store.js';

const app = createApp(App);

const store = useStore();

const savedSession = localStorage.getItem('user_session');
if (savedSession) {
  const session = JSON.parse(savedSession);
  // Restauramos los valores en el store
  store.setUsuarioLogueado(session.usuarioLogueado, session.email);
}

// Plugin para scroll suave
app.config.globalProperties.$scrollTo = (sectionId) => {
  const element = document.getElementById(sectionId);
  if (element) {
    window.scrollTo({
      top: element.offsetTop - 80,
      behavior: 'smooth'
    });
  }
};

app.use(router).mount('#app');