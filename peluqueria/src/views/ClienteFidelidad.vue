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
    
    // Adaptación por si la estructura cambia
    if (data.promociones) {
      misPromos.value = data.promociones;
      datosUsuario.value = data.usuario;
    } else {
      misPromos.value = data;
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
      <div v-for="promo in misPromos" :key="promo.id" class="tarjeta-fidelidad">
        
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
            <div class="sellos-wrapper" v-if="promo.tipo === 'VISITAS'" :class="{ 'tarjeta-completada': promo.mis_sellos >= promo.cupones_necesarios }">
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

              <div class="completado-overlay" v-if="promo.mis_sellos >= promo.cupones_necesarios">
                <i class="fas fa-gift"></i>
                <span>¡PREMIO LISTO!</span>
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
            </div>
          </div>

          <div class="tarjeta-footer">
            <span><i class="far fa-calendar-alt"></i> {{ promo.fecha_fin }}</span>
            <div class="progreso-texto" v-if="promo.tipo === 'VISITAS'">
              {{ promo.mis_sellos }} / {{ promo.cupones_necesarios }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.fidelidad-container { 
  padding: 20px; 
  max-width: 600px; 
  margin: 0 auto;
}

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
}

.logo-wrapper img {
  width: 100%;
  height: auto;
  filter: drop-shadow(0 4px 6px rgba(0,0,0,0.2));
}

.tarjeta-col-der {
  flex: 1;
  padding: 20px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  min-width: 0; /* Evita que el contenido desborde el flex */
}

.tarjeta-info h3 { margin: 0; font-size: 1.2rem; color: #fff; }
.tarjeta-info p { font-size: 0.85rem; color: #a0aec0; margin-bottom: 10px; }

/* SELLOS EN HORIZONTAL */
.sellos-grid {
  display: flex;
  flex-wrap: wrap; /* Para que bajen si son muchos */
  gap: 8px;
  justify-content: flex-start;
}

.sello-slot {
  width: 35px;
  height: 35px;
  border: 2px dashed #4a5568;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #4a5568;
  font-size: 0.9rem;
}

.sello-active {
  background: white;
  border: none;
  color: #e75480;
  transform: scale(1.05);
}

/* COMPLETADO OVERLAY */
.tarjeta-completada .sellos-grid { opacity: 0.1; }
.completado-overlay {
  position: absolute;
  top: 0; left: 0; width: 100%; height: 100%;
  background: rgba(231, 84, 128, 0.9);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  z-index: 2;
  border-radius: 0 20px 20px 0;
}
.completado-overlay i { font-size: 2rem; margin-bottom: 5px; }

/* OTROS ESTILOS */
.promo-valor { font-size: 2.2rem; font-weight: 900; color: #e75480; }
.promo-valor span { font-size: 0.9rem; color: #a0aec0; }
.etiqueta-info { font-size: 0.8rem; margin-top: 5px; }

.tarjeta-footer {
  margin-top: 15px;
  padding-top: 10px;
  border-top: 1px solid rgba(255,255,255,0.1);
  display: flex;
  justify-content: space-between;
  font-size: 0.7rem;
  color: #718096;
}

.loader-container { text-align: center; padding: 50px; }
.spinner {
  width: 40px; height: 40px;
  border: 4px solid #f3f3f3; border-top: 4px solid #e75480;
  border-radius: 50%; animation: spin 1s linear infinite;
  margin: 0 auto;
}
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>