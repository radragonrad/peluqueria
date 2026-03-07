// js/App.js
import Header from './components/Header.vue';
import ServicesSection from './components/ServicesSection.vue';
import Footer from './components/Footer.vue';
import ReservaModal from './components/ReservaModal.vue';
import { useStore } from './store.js';

export default {
  components: {
    Header,
    ServicesSection,
    Footer,
    ReservaModal
  },
  setup() {
    const store = useStore();
    
    return {
      store
    };
  },
  template: `
    <Header />
    <ServicesSection />
    <Footer />
    <ReservaModal v-if="store.modalAbierto" />
  `
};