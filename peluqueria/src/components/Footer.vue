<template>
  <footer class="main-footer">
    <div class="container">
      <div class="footer-grid">
        
        <div class="footer-column brand">
          <img src="../assets/bg-logo.png" alt="RG Hair Studio" class="footer-logo-img">
          <p class="brand-text">
            Expertos en estilismo masculino y barbería clásica. Tu imagen, nuestra firma.
          </p>
          <div class="social-pills">
            <a href="https://www.instagram.com/essenciabarberstudy/" target="_blank" class="social-pill instagram">
              <i class="fab fa-instagram"></i>
            </a>
            <a href="https://www.tiktok.com/@rgutierrez212" target="_blank" class="social-pill tiktok">
              <i class="fab fa-tiktok"></i>
            </a>
            <a href="https://wa.me/34657553377" target="_blank" class="social-pill whatsapp">
              <i class="fab fa-whatsapp"></i>
            </a>
          </div>
        </div>

        <div class="footer-column">
          <h3 class="column-title">HORARIO</h3>
          <ul class="schedule-list">
            <li v-if="loading" class="loading-text">Cargando horario...</li>

            <li v-for="item in listaHorarios" :key="item.id_dia" class="schedule-item">
              <span class="day-label">{{ item.dia_semana }}:</span>
              
              <div class="time-block">
                <template v-if="item.abierto == 1">
                  <span class="time-text">
                    {{ formatTime(item.h_apertura_1) }} - {{ formatTime(item.h_cierre_1) }}
                  </span>
                  <span v-if="item.h_apertura_2 && item.h_apertura_2 !== '00:00:00'" class="time-text second-shift">
                    / {{ formatTime(item.h_apertura_2) }} - {{ formatTime(item.h_cierre_2) }}
                  </span>
                </template>
                <span v-else class="closed-label">Cerrado</span>
              </div>
            </li>
          </ul>
        <div v-if="avisosCierre.length > 0" class="footer-notice">
          <div v-for="(aviso, index) in avisosCierre" :key="index" :class="aviso.tipo">
            * {{ aviso.texto }}
          </div>
        </div>
          
        </div>

        <div class="footer-column">
          <h3 class="column-title">CONTACTO</h3>
          <div class="contact-info">
            <p><i class="fas fa-map-marker-alt"></i> C. San Jose, 9, 41808 Villanueva del Ariscal, Sevilla</p>
            <p><i class="fas fa-phone-alt"></i> +34 657 55 33 77</p>        
            <router-link to="/login" class="btn-footer">RESERVAR CITA</router-link>   
          </div>
        </div>

        <div class="footer-column map-column">
          <h3 class="column-title">UBICACIÓN</h3>
          <div class="map-container">
            <a href="https://www.google.com/maps/search/?api=1&query=C.+San+Jose%2C+9%2C+41808+Villanueva+del+Ariscal%2C+Sevilla" target="_blank" rel="noopener">
              <img src="../assets/mapa-ubicacion.png" alt="Ubicación R. Gutiérrez Hair Studio" class="map-image">
              <div class="map-overlay">
                <i class="fas fa-map-marker-alt"></i>
                <span>Ver en Google Maps</span>
              </div>
            </a>
          </div>  
        </div>
      </div>

      <div class="footer-bottom">
        <div class="bottom-content">
          <p>© {{ currentYear }} RG HAIR STUDIO - Todos los derechos reservados</p>
          <div class="legal-links">
            <router-link to="/aviso-legal">Aviso Legal</router-link>
            <router-link to="/privacidad">Privacidad</router-link>
            <router-link to="/cookies">Cookies</router-link>
          </div>
          
        </div>
      </div>
    </div>
  </footer>
</template>

<script>
import { ref, onMounted } from 'vue';
export default {
  setup() {
    const listaHorarios = ref([]);
    const loading = ref(true);
    const avisosCierre = ref([]);

    const formatTime = (time) => {
      if (!time) return '';
      return time.substring(0, 5); // Corta "09:30:00" a "09:30"
    };

    const cargarHorarios = async () => {
      try {
        const response = await fetch('/backend/api/get_horario_tienda.php');
        const data = await response.json();

        listaHorarios.value = data.semanal || [];

        const hoyLocal = new Date().toLocaleDateString('sv-SE');
        const mananaObj = new Date();
        mananaObj.setDate(mananaObj.getDate() + 1);
        const mananaLocal = mananaObj.toLocaleDateString('sv-SE');

        avisosCierre.value = (data.excepciones || [])
          .filter(ex => ex.fecha === hoyLocal || ex.fecha === mananaLocal)
          .map(ex => {
            const diaEtiqueta = (ex.fecha === hoyLocal) ? "Hoy" : "Mañana";
            let mensaje = "";

            if (parseInt(ex.cerrado) === 1) {
              // CASO 1: Cierre total
              mensaje = `${diaEtiqueta} cerrado ( ${ex.descripcion} )`;
            } else if (parseInt(ex.solo_tramo) === 1) {
              // CASO 2: Cierre parcial (usa h_inicio y h_fin)
              const inicio = formatTime(ex.h_inicio);
              const fin = formatTime(ex.h_fin);
              mensaje = `${diaEtiqueta} cerrado de ${inicio} a ${fin} ( ${ex.descripcion} )`;
            }

            return {
              texto: mensaje,
              tipo: ex.fecha === hoyLocal ? 'hoy' : 'manana'
            };
          });

      } catch (error) {
        console.error("Error al procesar excepciones:", error);
      } finally {
        loading.value = false;
      }
    };

    onMounted(cargarHorarios);

    return { listaHorarios, loading, formatTime, avisosCierre };
  }
};
</script>

<style scoped>
.main-footer {
  background-color: #0c0c0c;
  color: #fff;
  padding: 80px 0 30px;
  font-family: 'Inter', sans-serif;
}

.footer-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 50px;
  margin-bottom: 60px;
}

/* Títulos con estilo dorado sutil */
.column-title {
  font-size: 0.9rem;
  font-weight: 700;
  letter-spacing: 2px;
  margin-bottom: 25px;
  color: #e75480;
  position: relative;
}

.column-title::after {
  content: '';
  position: absolute;
  bottom: -8px;
  left: 0;
  width: 30px;
  height: 2px;
  background: #e75480;
}

/* Branding Column */
.footer-logo-img { max-width: 160px; margin-bottom: 20px; }
.brand-text { color: #888; line-height: 1.6; font-size: 0.95rem; margin-bottom: 20px; }

/* Redes Sociales en Círculos (Estilo Ref 1) */
.social-pills { display: flex; gap: 12px; }
.social-pill {
  width: 40px; height: 40px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  background: #1a1a1a; color: #fff; transition: 0.3s;
}
.social-pill:hover { background: #c5a059; transform: translateY(-3px); }

/* Horarios */
.schedule-list {
  list-style: none;
  padding: 0;
  margin-top: 15px;
}

.schedule-item {
  display: flex;
  margin-bottom: 0px; /* Espacio uniforme entre filas */
  font-family: 'Montserrat', sans-serif; /* O la fuente que uses en tu web */
}

.day-label {
  color: #fff;
  font-weight: 700;
  font-size: 0.9rem;
  text-transform: uppercase;
  width: 110px; /* Ajusta este valor para alinear todas las horas en una misma columna */
  flex-shrink: 0; /* Evita que el nombre del día se encoja */
}

.time-block {
  display: flex;
  flex-wrap: wrap; /* Si el horario es muy largo, baja a la siguiente línea */
  gap: 5px;
}

.time-text {
  color: #c5a059; /* Dorado */
  font-size: 0.9rem;
  font-weight: 500;
}

.second-shift {
  color: #c5a059;
  opacity: 0.8;
}

.closed-label {
  color: #888;
  font-style: italic;
  font-size: 0.9rem;
}

.column-title {
  color: #e75480;
  font-size: 1.1rem;
  margin-bottom: 20px;
  position: relative;
  display: inline-block;
}

/* Línea decorativa bajo el título como en tu imagen */
.column-title::after {
  content: "";
  position: absolute;
  left: 0;
  bottom: -5px;
  width: 30px;
  height: 2px;
  background: #e75480;
}

.btn-footer {
  display: inline-block;
  margin-top: 15px;
  padding: 10px 15px;
  border: 1px solid #fff;
  color: #fff;
  text-decoration: none;
  font-size: 0.75rem;
  font-weight: bold;
  transition: 0.3s;
}

.btn-footer:hover {
  background: #fff;
  color: #000;
}

/* Pie inferior */
.footer-bottom { border-top: 1px solid #1a1a1a; padding-top: 30px; }
.bottom-content { 
  display: flex; justify-content: space-between; align-items: center;
  flex-wrap: wrap; gap: 20px; color: #555; font-size: 0.8rem;
}
.legal-links a { color: #555; text-decoration: none; margin: 0 10px; }
.legal-links a:hover { color: #c5a059; }
.design-by strong { color: #fff; }

@media (max-width: 768px) {
  .bottom-content { flex-direction: column; text-align: center; }
}

/* Estilo para el Contenedor del Mapa */
.mini-map {
  overflow: hidden;   /* Para que el mapa respete el redondeo de bordes */
  border-radius: 8px; /* Bordes suavemente redondeados */
  border: 1px solid #1a1a1a;
  line-height: 0;     /* Elimina espacios extraños debajo del iframe */
}

.mini-map iframe {
  filter: grayscale(0.5) invert(0.9) contrast(1.2); /* Opcional: efecto oscuro para que pegue con el footer negro */
  transition: filter 0.3s ease;
}

.mini-map:hover iframe {
  filter: grayscale(0); /* Al pasar el ratón se ve el color original */
}

.schedule-list {
  list-style: none;
  padding: 0;
}

.schedule-list li {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  margin-bottom: 0px;
  font-size: 0.9rem;
  color: #ccc;
  position: relative;
}

/* Línea punteada decorativa */
.schedule-list li::after {
  content: "";
  flex: 1;
  border-bottom: 1px dotted #444;
  margin: 0 10px;
  order: 1;
}

.schedule-list span {
  order: 0;
  color: #fff;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
}

/* Las horas van al final */
.schedule-list li .time-text {
  order: 2;
  color: #c5a059; /* Dorado de tus referencias */
}

.exception-alert {
  background: rgba(255, 77, 77, 0.1); /* Fondo rojizo muy sutil */
  border-left: 4px solid #ff4d4d;
  padding: 10px 15px;
  margin-bottom: 20px;
  border-radius: 4px;
  animation: fadeIn 0.5s ease-in;
}

.hoy {
  color: #ff4d4d;
  font-weight: 800;
  font-size: 0.85rem;
  letter-spacing: 0.5px;
  text-transform: uppercase;
}

.manana {
  color: #ffa500;
  font-weight: 700;
}

.icon {
  margin-right: 8px;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-5px); }
  to { opacity: 1; transform: translateY(0); }
}

.map-container {
  position: relative;
  width: 100%;
  height: 150px;
  border-radius: 12px;
  overflow: hidden;
  
  transition: transform 0.3s ease;
}

.map-container:hover {
  transform: scale(1.03);
}

.map-image {
  width: 100%;
  height: 100%;
  object-fit: cover; /* Esto hace que la imagen rellene el hueco sin deformarse */
  display: block;
}

.map-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(26, 26, 26, 0.4); /* Oscurece un poco la imagen */
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  color: white;
  font-weight: bold;
  font-size: 0.9rem;
  transition: background 0.3s;
}

.map-container:hover .map-overlay {
  background: rgba(188, 150, 103, 0.4); /* Cambia al dorado al pasar el ratón */
}

.map-overlay i {
  font-size: 1.5rem;
  margin-bottom: 5px;
}

.address-text {
  margin-top: 10px;
  font-size: 0.85rem;
  color: #ccc;
  text-align: center;
}
</style>