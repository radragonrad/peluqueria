<template>
  <main>
    <InicioSection />
    <SobreNosotrosSection />
    <ServicesSection />
    <GaleriaSection />
    <ContactoSection />
  </main>
</template>

<script>
import { onMounted } from 'vue';
import { useStore } from '../store.js';
import InicioSection from '../components/InicioSection.vue';
import SobreNosotrosSection from '../components/SobreNosotrosSection.vue';
import ServicesSection from '../components/ServicesSection.vue';
import GaleriaSection from '../components/GaleriaSection.vue';
import ContactoSection from '../components/ContactoSection.vue';

export default {
  components: {
    InicioSection,
    SobreNosotrosSection,
    ServicesSection,
    GaleriaSection,
    ContactoSection
  },
  setup() {
    const store = useStore();

    // Verificar autenticación al cargar la página
    onMounted(async () => {
      try {
        const res = await fetch('/api/check_auth.php', {
          credentials: 'include'
        });
        
        if (res.ok) {
          const data = await res.json();
          if (data.logged_in && data.rol === 'usuario') {
            store.setUsuarioLogueado(true, data.email, data.usuario);
          } else {
            store.setUsuarioLogueado(false, '');
          }
        } else {
          store.setUsuarioLogueado(false, '');
        }
      } catch (error) {
        console.error('Error al verificar autenticación:', error);
        store.setUsuarioLogueado(false, '');
      }
    });

    return {
      // No necesitas devolver nada si no usas variables en el template
    };
  }
};
</script>