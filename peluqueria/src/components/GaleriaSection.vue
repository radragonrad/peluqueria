<!-- src/components/GaleriaSection.vue -->
<template>
  <section id="galeria" class="gallery-section">
    <div class="container">
      <h2 class="section-title">Galería</h2>
      <p class="section-subtitle">Nuestros Trabajos</p>

      <div class="gallery-grid">
        <div class="gallery-item" v-for="(img, index) in imagenes" :key="index">
          <img :src="img.src" :alt="img.alt" loading="lazy" />
        </div>
      </div>
    </div>
  </section>
</template>

<script>
export default {
  data() {
    return {
      imagenes: [
        { src: '/assets/galeria/corte-01.jpg', alt: 'Fade con pelo largo y texturizado' },
        { src: '/assets/galeria/corte-02.jpg', alt: 'Corte con flequillo corto y degradado' },
        { src: '/assets/galeria/corte-03.jpg', alt: 'Degradado bajo con contorno definido' },
        { src: '/assets/galeria/corte-04.jpg', alt: 'Corte rizado con degradado medio' },
        { src: '/assets/galeria/corte-05.jpg', alt: 'Quiff texturizado con degradado alto' },
        { src: '/assets/galeria/corte-06.jpg', alt: 'Corte rizado con flequillo y degradado' },
        { src: '/assets/galeria/corte-07.jpg', alt: 'Corte texturizado con degradado medio' },
        { src: '/assets/galeria/corte-08.jpg', alt: 'Corte con efecto mojado y ondas naturales + degradado bajo' }
      ]
    };
  }
};
</script>