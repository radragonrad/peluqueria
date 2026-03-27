<template>
  <section id="contacto" class="contact-section">
    <div class="container">
      <div class="header-content">
        <span class="subtitle">¿DÓNDE ESTAMOS?</span>
        <h2>INFORMACIÓN Y CONTACTO</h2>
        <div class="divider"></div>
      </div>

      <div class="contact-grid">
        <div class="info-card">
          <div class="icon-box"><i class="fas fa-phone-alt"></i></div>
          <h3>Llámanos</h3>
          <p>Atención inmediata</p>
          <a href="tel:+34657553377" class="card-link">657 55 33 77</a>
          <span class="card-subtext">info@rgutierrezhairstudio.com</span>
        </div>

        <div class="info-card schedule-card">
          <div class="icon-box"><i class="fas fa-clock"></i></div>
          <h3>Nuestro Horario</h3>
          
          <div v-if="loading" class="loading-dots"><span></span><span></span><span></span></div>
          
          <ul v-else class="modern-schedule">
            <li v-for="item in listaHorarios" :key="item.id_dia" :class="{ 'is-closed': item.abierto == 0 }">
              <span class="day">{{ item.dia_semana }}</span>
              <span class="line"></span>
              <span class="hours">
                <template v-if="item.abierto == 1">
                  {{ formatTime(item.h_apertura_1) }} - {{ formatTime(item.h_cierre_1) }}
                </template>
                <template v-else>Cerrado</template>
              </span>
            </li>
          </ul>

          <div v-if="avisosCierre.length > 0" class="mini-alerts">
            <div v-for="(aviso, index) in avisosCierre" :key="index" :class="['alert-item', aviso.tipo]">
              {{ aviso.texto }}
            </div>
          </div>
        </div>

        <div class="info-card map-card">
          <div class="icon-box"><i class="fas fa-map-marker-alt"></i></div>
          <h3>Ubicación</h3>
          <p>C. San Jose, 9, Villanueva del Ariscal</p>
          <div class="mini-map-container">
            <img src="../assets/mapa-ubicacion.png" alt="Ubicación R. Gutiérrez Hair Studio" class="map-image">
          </div>
          <a href="https://www.google.com/maps/search/?api=1&query=C.+San+Jose%2C+9%2C+41808+Villanueva+del+Ariscal%2C+Sevilla" target="_blank" class="btn-maps">CÓMO LLEGAR</a>
        </div>
      </div>

      <div class="form-section-wrapper">
        <div class="form-header">
          <h3>Envíanos un mensaje</h3>
          <p>Si tienes alguna consulta específica, rellena este formulario.</p>
        </div>

        <form id="contactForm" class="wide-form" action="/backend/api/contact.php" method="POST">
          <div class="form-grid">
            <div class="input-group">
              <label>Nombre completo</label>
              <input type="text" name="nombre" placeholder="Tu nombre" required />
            </div>
            <div class="input-group">
              <label>Teléfono</label>
              <input type="tel" name="telefono" placeholder="600 000 000" required />
            </div>
            <div class="input-group">
              <label>Email</label>
              <input type="email" name="email" placeholder="tu@email.com" required />
            </div>
            <div class="input-group">
              <label>Servicio de interés</label>
              <select name="servicio" required>
                <option value="" disabled selected>Selecciona un servicio</option>
                <option value="Corte">Corte clásico</option>
                <option value="Barba">Arreglo de barba</option>
                <option value="Otro">Otro consulta</option>
              </select>
            </div>
          </div>
          
          <div class="input-group full-width">
            <label>Tu mensaje</label>
            <textarea name="mensaje" rows="4" placeholder="¿En qué podemos ayudarte?"></textarea>
          </div>

          <div class="form-actions">
            <label class="checkbox-label">
              <input type="checkbox" name="privacidad" required />
              <span>Acepto la <router-link to="/privacidad">política de privacidad</router-link></span>
            </label>
            <button type="submit" class="btn-send">
              ENVIAR FORMULARIO <i class="fas fa-paper-plane"></i>
            </button>
          </div>
        </form>
      </div>
    </div>
  </section>
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
      return time.substring(0, 5);
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
              mensaje = `${diaEtiqueta} cerrado (${ex.descripcion})`;
            } else if (parseInt(ex.solo_tramo) === 1) {
              mensaje = `${diaEtiqueta} cerrado de ${formatTime(ex.h_inicio)} a ${formatTime(ex.h_fin)} (${ex.descripcion})`;
            }
            return { texto: mensaje, tipo: ex.fecha === hoyLocal ? 'hoy' : 'manana' };
          });
      } catch (error) {
        console.error("Error cargando horarios en contacto:", error);
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
.contact-section { padding: 80px 0; background-color: #050505; color: white; }

.header-content { text-align: center; margin-bottom: 50px; }
.subtitle { color: #e75480; font-size: 0.8rem; letter-spacing: 4px; font-weight: 700; }
.divider { width: 50px; height: 2px; background: #e75480; margin: 15px auto; }

/* --- ESTILO DE CARDS (MANTENIDO) --- */
.contact-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 25px;
  margin-bottom: 80px;
}

.info-card {
  background: #111;
  padding: 40px 25px;
  border-radius: 15px;
  text-align: center;
  border: 1px solid #222;
  transition: all 0.4s ease;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.info-card:hover { transform: translateY(-10px); border-color: #e75480; }

.icon-box {
  width: 55px; height: 55px;
  background: rgba(188, 150, 103, 0.1);
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 20px; color: #e75480; font-size: 1.4rem;
}

.card-link { color: #e75480; font-size: 1.2rem; font-weight: bold; text-decoration: none; margin-top: 10px; }
.card-subtext { font-size: 0.8rem; color: #666; margin-top: 5px; }

/* Horario con puntos */
.modern-schedule { list-style: none; padding: 0; width: 100%; margin-top: 15px; }
.modern-schedule li { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; font-size: 0.8rem; }
.modern-schedule .day { font-weight: bold; color: #fff; width: 35px; text-align: left; }
.modern-schedule .line { flex-grow: 1; border-bottom: 1px dotted #333; margin: 0 10px; }
.modern-schedule .hours { color: #e75480; font-weight: 500; }

.mini-map-container { width: 100%; height: 100px; margin: 15px 0; border-radius: 10px; overflow: hidden; border: 1px solid #333; }
.btn-maps { background: #e75480; color: black; padding: 8px 15px; border-radius: 5px; font-size: 0.7rem; font-weight: bold; text-decoration: none; }

/* --- NUEVO FORMULARIO ANCHO ABAJO --- */
.form-section-wrapper {
  background: #111;
  padding: 50px;
  border-radius: 20px;
  border: 1px solid #222;
}

.form-header { margin-bottom: 30px; text-align: left; }
.form-header h3 { color: #e75480; font-size: 1.6rem; margin-bottom: 10px; }
.form-header p { color: #888; }

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
  margin-bottom: 20px;
}

.input-group { display: flex; flex-direction: column; gap: 8px; }
.input-group label { font-size: 0.8rem; color: #e75480; font-weight: bold; text-transform: uppercase; }

input, select, textarea {
  background: #0a0a0a;
  border: 1px solid #333;
  padding: 12px 15px;
  color: white;
  border-radius: 8px;
  font-family: inherit;
}

input:focus, select:focus, textarea:focus { border-color: #e75480; outline: none; }

.full-width { grid-column: span 2; margin-top: 10px; }

.form-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 30px;
  padding-top: 20px;
  border-top: 1px solid #222;
}

.checkbox-label { display: flex; align-items: center; gap: 10px; font-size: 0.85rem; color: #888; }
.checkbox-label a { color: #e75480; text-decoration: none; }

.btn-send {
  background: #e75480;
  color: black;
  padding: 15px 35px;
  border: none;
  border-radius: 8px;
  font-weight: bold;
  cursor: pointer;
  transition: 0.3s;
  display: flex; align-items: center; gap: 10px;
}

.btn-send:hover { background: white; transform: scale(1.02); }

@media (max-width: 992px) {
  .contact-grid { grid-template-columns: 1fr; }
  .form-grid { grid-template-columns: 1fr; }
  .full-width { grid-column: span 1; }
  .form-actions { flex-direction: column; gap: 20px; text-align: center; }
  .form-section-wrapper { padding: 30px; }
}

.map-image {
  width: 100%;
  height: 100%;
  object-fit: cover; /* Esto hace que la imagen rellene el hueco sin deformarse */
  display: block;
}
</style>