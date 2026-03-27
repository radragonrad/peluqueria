<template>
  <section id="galeria" class="gallery-section">
    <div class="container">
      <div class="header-galeria">
        <span class="subtitle">PORTFOLIO</span>
        <h2 class="section-title">NUESTROS TRABAJOS</h2>
        <div class="divider"></div>
      </div>

      <div class="gallery-grid">
        <div 
          class="gallery-item" 
          v-for="(img, index) in imagenes" 
          :key="index"
          @click="abrirFoto(img)"
        >
          <div class="image-wrapper">
            <img :src="img.src" :alt="img.alt" loading="lazy" />
            <div class="gallery-overlay">
              <div class="overlay-content">
                <i class="fas fa-expand"></i>
                <span>AMPLIAR CORTE</span>
                <p>{{ img.alt }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <Transition name="fade">
      <div v-if="fotoSeleccionada" class="lightbox-overlay" @click="cerrarFoto">
        <button class="close-lightbox" @click="cerrarFoto">
          <i class="fas fa-times"></i>
        </button>
        <div class="lightbox-content" @click.stop>
          <img :src="fotoSeleccionada.src" :alt="fotoSeleccionada.alt" />
          <div class="lightbox-caption">
            <h3>{{ fotoSeleccionada.alt }}</h3>
          </div>
        </div>
      </div>
    </Transition>
  </section>
</template>

<script>
export default {
  data() {
    return {
      fotoSeleccionada: null,
      imagenes: [
        { src: '/assets/galeria/corte-01.jpg', alt: 'Fade con pelo largo y texturizado' },
        { src: '/assets/galeria/corte-02.jpg', alt: 'Corte con flequillo corto y degradado' },
        { src: '/assets/galeria/corte-03.jpg', alt: 'Degradado bajo con contorno definido' },
        { src: '/assets/galeria/corte-04.jpg', alt: 'Corte rizado con degradado medio' },
        { src: '/assets/galeria/corte-05.jpg', alt: 'Quiff texturizado con degradado alto' },
        { src: '/assets/galeria/corte-06.jpg', alt: 'Corte rizado con flequillo y degradado' },
        { src: '/assets/galeria/corte-07.jpg', alt: 'Corte texturizado con degradado medio' },
        { src: '/assets/galeria/corte-08.jpg', alt: 'Corte con efecto mojado y ondas naturales' }
      ]
    };
  },
  methods: {
    abrirFoto(img) {
      this.fotoSeleccionada = img;
      document.body.style.overflow = 'hidden';
    },
    cerrarFoto() {
      this.fotoSeleccionada = null;
      document.body.style.overflow = 'auto';
    }
  }
};
</script>

<style scoped>
.gallery-section {
  padding: 80px 20px;
  background-color: #0a0a0a;
}

.header-galeria {
  text-align: center;
  margin-bottom: 50px;
}

.subtitle {
  color: #e75480;
  font-size: 0.8rem;
  letter-spacing: 4px;
  font-weight: 700;
}

.section-title {
  color: white;
  font-size: 2.5rem;
  margin-top: 10px;
  letter-spacing: 2px;
}

.divider {
  width: 60px;
  height: 3px;
  background: #e75480;
  margin: 20px auto;
}

/* Grid */
.gallery-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
}

.gallery-item {
  position: relative;
  overflow: hidden;
  border-radius: 15px;
  aspect-ratio: 4 / 5;
  background: #111;
  cursor: pointer;
  /* Estabilizador de hardware */
  transform: translateZ(0);
}

.image-wrapper {
  width: 100%;
  height: 100%;
  position: relative;
}

.gallery-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  /* Evita temblores al escalar */
  backface-visibility: hidden;
  will-change: transform;
  transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

/* Overlay (AMPLIAR CORTE) */
.gallery-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.4s ease;
  backdrop-filter: blur(4px);
  z-index: 2;
}

.overlay-content {
  text-align: center;
  color: white;
  padding: 20px;
  transform: translateY(15px);
  transition: transform 0.4s ease;
}

.overlay-content i {
  color: #e75480;
  font-size: 1.8rem;
  margin-bottom: 10px;
}

.overlay-content span {
  display: block;
  font-size: 0.75rem;
  letter-spacing: 2px;
  font-weight: bold;
}

/* Efectos HOVER unificados */
.gallery-item:hover img {
  transform: scale(1.05);
}

.gallery-item:hover .gallery-overlay {
  opacity: 1;
}

.gallery-item:hover .overlay-content {
  transform: translateY(0);
}

/* LIGHTBOX (MODAL) */
.lightbox-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.95);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  cursor: zoom-out;
  backdrop-filter: blur(10px);
}

.lightbox-content {
  position: relative;
  max-width: 90%;
  max-height: 85vh;
  cursor: default;
}

.lightbox-content img {
  max-width: 100%;
  max-height: 75vh;
  object-fit: contain;
  border-radius: 8px;
  box-shadow: 0 0 40px rgba(0,0,0,0.5);
  border: 1px solid rgba(231, 84, 128, 0.2);
}

.close-lightbox {
  position: absolute;
  top: 25px;
  right: 25px;
  background: none;
  border: none;
  color: white;
  font-size: 2.5rem;
  cursor: pointer;
  z-index: 10000;
}

.lightbox-caption {
  color: white;
  text-align: center;
  margin-top: 15px;
}

.lightbox-caption h3 {
  color: #e75480;
  font-size: 1rem;
  font-weight: 400;
}

/* Animación de apertura */
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* Responsive móvil */
@media (max-width: 768px) {
  /* 1. Forzamos a que el overlay sea capaz de recibir eventos de toque */
  .gallery-overlay {
    opacity: 0;
    pointer-events: auto; /* IMPORTANTE: permite detectar el toque */
    background: rgba(0, 0, 0, 0.6);
    transition: opacity 0.3s ease;
  }

  /* 2. El truco para móviles: simulamos el hover con 'active' y 'focus' */
  /* Al tocar la imagen, el overlay se pondrá en opacity 1 inmediatamente */
  .gallery-item:active .gallery-overlay,
  .gallery-item:hover .gallery-overlay {
    opacity: 1 !important;
  }

  /* 3. Ajustamos el contenido para que no "baile" en el móvil */
  .overlay-content {
    transform: translateY(0) !important; /* Quitamos el movimiento de subida */
    opacity: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
  }

  .overlay-content i {
    font-size: 1.5rem;
    margin-bottom: 5px;
    color: #e75480;
  }

  .overlay-content span {
    font-size: 0.7rem;
    font-weight: bold;
    text-transform: uppercase;
  }

  /* Ocultamos el texto largo para que no ensucie la pantalla del móvil */
  .overlay-content p {
    display: none;
  }
}
</style>