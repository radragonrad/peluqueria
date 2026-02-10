<!-- src/App.vue -->
<template>
  <div id="app">
    <Header />
    <main>
      <InicioSection />
      <SobreNosotrosSection />
      <ServicesSection />
      <GaleriaSection />
      <ContactoSection />
    </main>
    <Footer />
    <ReservaModal v-if="store.modalAbierto" />
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
    return { store };
  }
};
</script>