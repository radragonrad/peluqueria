<!-- src/App.vue -->
<template>
  <div id="app">
    <Header v-if="!$route.meta.hideLayout" />

    <router-view />

    <Footer v-if="!$route.meta.hideLayout" />

    <ReservaModal v-if="store.state.modalAbierto" />
  </div>
</template>

<script>
import Header from './components/Header.vue';
import InicioSection from './components/InicioSection.vue';
import SobreNosotrosSection from './components/SobreNosotrosSection.vue';
import ServicesSection from './components/ServicesSection.vue';
import GaleriaSection from './components/GaleriaSection.vue';
import ContactoSection from './components/ContactoSection.vue';
import Footer from './components/Footer.vue';
import ReservaModal from './components/ReservaModal.vue';
import { useStore } from './store.js';
import { onMounted } from 'vue';

export default {
  components: {
    Header,
    InicioSection,
    SobreNosotrosSection,
    ServicesSection,
    GaleriaSection,
    ContactoSection,
    Footer,
    ReservaModal
  },
  setup() {
    const store = useStore();

    onMounted(() => {
        // Miramos si hay un carnet en el disco duro
        const logueado = localStorage.getItem('usuarioLogueado');
        const id = localStorage.getItem('userId');
        const rol = localStorage.getItem('rol');

        if (logueado === 'true' && id) {
            // Si existe, "engañamos" a la App para que sepa que ya estamos dentro
            store.setUsuarioLogueado(true, '', id);
            // Si tu store guarda el rol, también lo ponemos
            if (store.state) store.state.rol = rol;
        }
    });
    
    return { store };
  }
  
};
</script>