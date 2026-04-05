<script setup>
import { ref, onMounted } from 'vue';
import { useStore } from '../store';

const store = useStore();
const misPromos = ref([]);
const datosUsuario = ref(null); 
const cargando = ref(true);

const cargarDatosFidelidad = async () => {
  const userId = store.state.userId || localStorage.getItem('userId');
  
  if (!userId) {
    console.error("No se encontró el ID de usuario");
    cargando.value = false;
    return;
  }

  try {
    const res = await fetch(`/backend/api/get_cupones_cliente.php?user_id=${userId}`);
    const data = await res.json();
    
    if (data.promociones) {
      misPromos.value = data.promociones.map(promo => ({
        ...promo,
        mis_sellos: parseInt(promo.mis_sellos) || 0,
        cupones_necesarios: parseInt(promo.cupones_necesarios) || 0,
        premios_canjeados: parseInt(promo.premios_canjeados) || 0 
      }));
      datosUsuario.value = data.usuario;
    } else {
      misPromos.value = Array.isArray(data) ? data : [];
    }
    
  } catch (e) {
    console.error("Error cargando fidelidad", e);
  } finally {
    cargando.value = false;
  }
};

onMounted(cargarDatosFidelidad);
</script>

<template>
  <div class="fidelidad-container">
    <header class="client-header" v-if="datosUsuario">
      <h2>¡Hola, {{ datosUsuario.nombre }}!</h2>
      <p>Tus ventajas exclusivas en Essencia Barber Study</p>
    </header>

    <div v-if="cargando" class="loader-container">
      <div class="spinner"></div>
      <p>Cargando tus premios...</p>
    </div>

    <div v-else class="tarjetas-grid">
      <div v-for="promo in misPromos" 
           :key="promo.id" 
           class="tarjeta-fidelidad" 
           :class="{ 'promo-gastada': promo.premios_canjeados > 0 }">
        
        <div class="tarjeta-col-izq">
          <div class="logo-wrapper">
            <img src="/assets/bg-logo.png" alt="Essencia Logo">
          </div>
        </div>

        <div class="tarjeta-col-der">
          <div class="tarjeta-info">
            <h3>{{ promo.nombre }}</h3>
            <p>{{ promo.descripcion }}</p>
          </div>

          <div class="tarjeta-content">
            <div class="sellos-wrapper" v-if="promo.tipo === 'VISITAS'">
              <div class="sellos-grid">
                <div 
                  v-for="n in parseInt(promo.cupones_necesarios)" 
                  :key="n" 
                  class="sello-slot"
                  :class="{ 'sello-active': n <= promo.mis_sellos }"
                >
                  <i :class="n <= promo.mis_sellos ? 'fas fa-check' : 'fas fa-star'"></i>
                </div>
              </div>

              <div class="completado-overlay overlay-listo" 
                  v-if="promo.mis_sellos >= promo.cupones_necesarios && promo.premios_canjeados == 0">
                <i class="fas fa-gift"></i>
                <span>¡PREMIO LISTO!</span>
              </div>

              <div class="completado-overlay overlay-canjeado" 
                  v-else-if="promo.premios_canjeados > 0">
                <i class="fas fa-check-circle"></i>
                <span>CANJEADO</span>
              </div>
            </div>

            <div v-else class="beneficio-directo">
              <div class="promo-valor" v-if="promo.tipo === 'PORCENTAJE'">
                {{ promo.valor_descuento }}% <span>DTO</span>
              </div>
              
              <div class="promo-valor" v-else-if="promo.tipo === 'ETIQUETA'">
                <i class="fas fa-crown"></i> <span>VIP</span>
              </div>

              <p v-if="promo.tipo === 'ETIQUETA'" class="etiqueta-info">
                Beneficio para: <strong>{{ promo.nombre_etiqueta }}</strong>
              </p>

              <div class="completado-overlay overlay-canjeado" 
                  v-if="promo.premios_canjeados > 0">
                <i class="fas fa-check-circle"></i>
                <span>CANJEADO</span>
              </div>
            </div>
          </div>

          <div class="tarjeta-footer">
            <span><i class="far fa-calendar-alt"></i> {{ promo.fecha_fin }}</span>
            <div class="progreso-texto">
              {{ promo.premios_canjeados > 0 ? 'Utilizado' : (promo.tipo === 'VISITAS' ? `${promo.mis_sellos} / ${promo.cupones_necesarios}` : 'Disponible') }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.fidelidad-container { padding: 20px; max-width: 600px; margin: 0 auto; }
.client-header { text-align: center; margin-bottom: 30px; }
.client-header h2 { color: #2d3748; font-weight: 800; }

.tarjeta-fidelidad {
  display: flex;
  background: #1a202c;
  color: white;
  border-radius: 20px;
  margin-bottom: 25px;
  position: relative;
  box-shadow: 0 10px 25px rgba(0,0,0,0.2);
  border: 1px solid rgba(255,255,255,0.1);
  overflow: hidden;
  min-height: 180px;
}

.tarjeta-col-izq {
  flex: 0 0 35%;
  background: #e75480;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  transition: background 0.3s ease;
}

.logo-wrapper img { width: 100%; height: auto; }

.tarjeta-col-der {
  flex: 1;
  padding: 20px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  position: relative;
}

.tarjeta-info h3 { margin: 0; font-size: 1.2rem; }
.tarjeta-info p { font-size: 0.85rem; color: #a0aec0; }

.sellos-grid { display: flex; flex-wrap: wrap; gap: 8px; }

.sello-slot {
  width: 35px; height: 35px;
  border: 2px dashed #4a5568;
  border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
}

.sello-active { background: white; border: none; color: #e75480; }

/* OVERLAYS */
.completado-overlay {
  position: absolute;
  top: 0; 
  left: 0; 
  width: 100%; 
  height: 100%;
  display: flex; 
  flex-direction: column;
  align-items: center; 
  justify-content: center;
  z-index: 10;
  /* Importante: que coincida con el borde de la tarjeta derecha */
  border-radius: 0 20px 20px 0; 
}

/* Estado: Premio ganado pero no usado */
.overlay-listo {
  /* Fondo rosa muy sutil (0.35 de opacidad) */
  background: rgba(231, 84, 128, 0.35) !important; 
  color: white;
  
  /* Efecto de cristal esmerilado potente */
  backdrop-filter: blur(2px) !important;
  -webkit-backdrop-filter: blur(2px) !important;
  
  /* Un borde sutil para definir el área */
  border-left: 1px solid rgba(255, 255, 255, 0.2);
}

/* Forzamos a que los sellos y el contenido de abajo se vean casi al 100% */
.tarjeta-fidelidad.promo-lista .tarjeta-content {
  opacity: 1;
}

/* Mejoramos el texto para que resalte sobre el fondo transparente */
.overlay-listo i {
  font-size: 3rem;
  margin-bottom: 10px;
  /* Sombra para que el icono "flote" */
  filter: drop-shadow(0 4px 8px rgba(0,0,0,0.5));
}

.overlay-listo span {
  font-size: 1.2rem;
  font-weight: 900;
  text-transform: uppercase;
  letter-spacing: 3px;
  /* Sombra de texto negra para asegurar legibilidad sobre los sellos */
  text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.8); 
}

/* Estado: Canjeado (Casi transparente con check verde) */
.overlay-canjeado {
  background: rgba(26, 32, 44, 0.6) !important;
  backdrop-filter: blur(2px);
  -webkit-backdrop-filter: blur(2px);
  color: #2ecc71;
}

/* Estado: Premio ya gastado (como el VIP) */
.overlay-canjeado {
  background: rgba(26, 32, 44, 0.9);
  color: #2ecc71; /* Verde éxito */
}

.completado-overlay i { font-size: 2.5rem; margin-bottom: 8px; }
.completado-overlay span { font-weight: 900; letter-spacing: 1px; }

/* Estilo para tarjeta gastada */
.promo-gastada { opacity: 0.85; }
.promo-gastada .tarjeta-col-izq { background: #4a5568; }

.tarjeta-footer {
  margin-top: 15px; padding-top: 10px;
  border-top: 1px solid rgba(255,255,255,0.1);
  display: flex; justify-content: space-between;
  font-size: 0.75rem; color: #718096;
}

.promo-valor { font-size: 2.2rem; font-weight: 900; color: #e75480; }
.etiqueta-info { font-size: 0.8rem; margin-top: 5px; }

.loader-container { text-align: center; padding: 50px; }
.spinner {
  width: 40px; height: 40px;
  border: 4px solid #f3f3f3; border-top: 4px solid #e75480;
  border-radius: 50%; animation: spin 1s linear infinite;
  margin: 0 auto;
}
@keyframes spin { 100% { transform: rotate(360deg); } }
</style>