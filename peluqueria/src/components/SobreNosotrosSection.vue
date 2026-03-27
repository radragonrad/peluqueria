<!-- src/components/SobreNosotrosSection.vue -->
<template>
  <section id="sobre-nosotros" class="service-section">
    <div class="container">
      <h2>Sobre Nosotros</h2>
      <div class="service-content">
        <div class="service-text">
          <p>Aquí no hacemos “cortes de catálogo”.</p>
          <p>Cada corte nace de una conversación: ¿qué llevas puesto hoy? ¿vas al instituto, al trabajo o a salir por la noche? ¿te gusta llevarlo corto, con volumen, despeinado o impecable?</p>
          <p>Nos adaptamos a tu estilo, a tu rutina y a lo que tú quieras proyectar. Usamos herramientas limpias, productos de calidad y sobre todo… <strong>escuchamos</strong>.</p>
          <p>No importa si es la primera vez que vienes o si ya eres de los que repiten cada mes: aquí no hay prisas, ni miradas raras. Solo buen rollo, consejos útiles y un resultado que te haga sentir bien contigo mismo.</p>
          <p>Y si no sabes muy bien qué quieres… ¡tranquilo! Te ayudamos a encontrar ese look que te representa, sin inventos, sin modas pasajeras que no van contigo.</p>
          <p><strong>Porque un buen corte no solo cambia tu pelo… cambia tu día.</strong></p>
        </div>
        <div class="service-image">
          <img src="/assets/mi-salon.jpeg" class="img-salon-vintage" alt="Interior de Essencia Barber Study" />
        </div>
      </div>
    </div>
  </section>
</template>
<style scoped>
/* ... tus otros estilos ... */

.img-salon-vintage {
  /* --- ESTILO BASE: NOIR PROFUNDO (Mantenido) --- */

  /* 1. Eliminamos el color casi por completo (90% gris, 10% color sutil) */
  filter: grayscale(0.9);

  /* 2. Oscurecemos y contrastamos para crear drama (Brillo 70%, Contraste 140%) */
  /* Nota: He unificado tus filtros en una sola línea para mayor claridad y rendimiento */
  filter: grayscale(0.9) brightness(0.7) contrast(1.4) blur(0.2px);

  /* 4. Viñeteado Profundo (Sombra interior potente en bordes) */
  box-shadow: inset 0 0 120px 30px rgba(0,0,0,0.8);
  
  /* Aseguramos que el box-shadow inset se vea (la imagen debe estar en un contenedor) */
  display: block;
  max-width: 100%;
  height: auto;
  border-radius: 12px;
  
  /* Transición suave para todos los cambios (filtro y sombra) */
  transition: all 0.5s ease;
}

/* --- EFECTO HOVER: ILUMINACIÓN POTENCIADA --- */
.img-salon-vintage:hover {
  /* CAMBIOS AQUÍ: */
  /* 1. Recuperamos color: Bajamos grayscale de 0.9 a 0.5 (50% color original) */
  /* 2. Damos luz: Subimos brightness de 0.7 a 1.1 (110% de brillo, un poco más que el original) */
  /* 3. Suavizamos contraste: Bajamos de 1.4 a 1.2 para una luz más natural */
  /* 4. Quitamos blur: Volvemos a la nitidez original */
  filter: grayscale(0.5) brightness(1.0) contrast(1.2) blur(0px);

  /* 5. Reducimos viñeteado: Hacemos la sombra de bordes mucho más sutil y transparente */
  /* Bajamos de 120px/30px/0.8 a 60px/10px/0.4 */
  box-shadow: inset 0 0 60px 10px rgba(0,0,0,0.4);
}

/* Ajuste necesario para el contenedor para que el box-shadow inset funcione */
.service-image {
  position: relative;
  overflow: hidden;
  border-radius: 12px;
  background-color: black; /* Fondo negro por si la imagen no llena todo */
}
</style>