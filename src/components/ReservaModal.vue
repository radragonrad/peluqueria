<!-- src/components/ReservaModal.vue -->
<template>
  <div id="reserva-modal" style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.85); z-index:1000; padding:20px; box-sizing:border-box;">
    <div style="max-width:800px; margin:0 auto; background:#121212; border-radius:16px; overflow:hidden;">
      <!-- Cabecera -->
      <div style="display:flex; justify-content:space-between; align-items:center; padding:1.5rem; border-bottom:1px solid #333;">
        <h3 style="color:#e75480; font-size:1.5rem; margin:0;" v-if="vista === 'profesionales'">Profesionales disponibles</h3>
        <h3 style="color:#e75480; font-size:1.5rem; margin:0;" v-else>Selecciona Fecha y hora</h3>
        <button @click="cerrarModal" style="background:none; border:none; color:#aaa; font-size:1.5rem; cursor:pointer;">×</button>
      </div>

      <!-- Pantalla 1: Profesionales -->
      <div v-if="vista === 'profesionales'" style="padding:2rem;">
        <div style="display:flex; flex-direction:column; gap:1.5rem;">
          <div 
            v-for="p in store.peluqueros" 
            :key="p.id"
            @click="seleccionarPeluquero(p)"
            style="
              display:flex; align-items:center; gap:12px; padding:1.5rem; 
              background:rgba(255,255,255,0.05); border-radius:12px; 
              cursor:pointer; transition:all 0.3s;
              border:2px solid transparent;
            "
            :class="{ 'selected': p.id === store.peluqueroSeleccionado?.id }"
          >
            <div style="width:70px; height:70px; border-radius:50%; overflow:hidden; flex-shrink:0;">
              <img :src="getAvatarByNombre(p.nombre)" 
                   alt="Foto de {{ p.nombre }}" 
                   style="width:100%; height:100%; object-fit:cover;">
            </div>
            <div>
              <strong style="color:white; display:block;">{{ p.nombre }}</strong>
              <small style="color:#aaa; font-size:0.85rem;">{{ p.especialidad }}</small>
            </div>
          </div>
        </div>
      </div>

      <!-- Pantalla 2: Resumen + Calendario -->
      <div v-else style="padding:2rem;">
        <!-- Resumen -->
        <div style="background:rgba(255,255,255,0.05); border-radius:12px; padding:1.5rem; margin-bottom:1.5rem;">
          <h4 style="color:white; margin-bottom:1rem; font-size:1.2rem;">Resumen</h4>
          
          <div style="margin-bottom:1rem;">
            <strong style="color:#e75480;">{{ store.servicioActual?.nombre || 'Servicio' }}</strong>
            <p style="color:#aaa; font-size:0.9rem; margin-top:0.5rem;">
              {{ store.servicioActual?.descripcion || '' }}
            </p>
          </div>

          <div style="margin-top:1rem;">
            <h5 style="color:#aaa; font-size:0.9rem; margin-bottom:0.5rem;">DESGLOSE DE COSTOS</h5>
            <div style="display:flex; justify-content:space-between; margin-bottom:0.5rem;">
              <span style="color:#ccc;">{{ store.servicioActual?.nombre || 'Corte' }}</span>
              <span style="color:white;">{{ store.servicioActual?.precio ? parseFloat(store.servicioActual.precio).toFixed(2) + '€' : '12.00€' }}</span>
            </div>
          </div>

          <div style="border-top:1px solid #333; padding-top:1rem;">
            <div style="display:flex; justify-content:space-between; font-weight:bold; font-size:1.1rem;">
              <span style="color:#ccc;">Precio Total</span>
              <span style="color:#e75480;">{{ calcularTotal }}€</span>
            </div>
          </div>
        </div>

        <!-- Botones de navegación -->
        <div style="display:flex; justify-content:space-between; margin-top:2rem;">
          <button @click="volverProfesionales" style="display:flex; align-items:center; gap:8px; background:none; border:none; color:#e75480; cursor:pointer;">
            ← Atrás
          </button>
          <button 
            @click="confirmarReserva" 
            :disabled="!store.fechaSeleccionada || !store.horaSeleccionada"
            :style="{ opacity: (store.fechaSeleccionada && store.horaSeleccionada) ? 1 : 0.5, cursor: (store.fechaSeleccionada && store.horaSeleccionada) ? 'pointer' : 'not-allowed' }"
            style="background:#e75480; color:white; border:none; padding:0.75rem 2rem; border-radius:8px; font-weight:bold;"
          >
            Confirmar Reserva
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useStore } from '../store.js';

export default {
  setup() {
    const store = useStore();
    const vista = ref('profesionales');

    // Avatares en Base64
    const getAvatarByNombre = (nombre) => {
      const nombreLimpio = nombre.toLowerCase();
      if (nombreLimpio.includes('emmanuel')) {
        return 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNTAiIGhlaWdodD0iMTUwIiB2aWV3Qm94PSIwIDAgMTUwIDE1MCI+PGNpcmNsZSBjeD0iNzUiIGN5PSI3NSIgcj0iNzAiIGZpbGw9IiM0ZTczZGYiLz48dGV4dCB4PSI3NSIgeT0iODAiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxOCIgZmlsbD0id2hpdGUiPkVtbWFudWVsPC90ZXh0Pjwvc3ZnPg==';
      } else if (nombreLimpio.includes('hector') || nombreLimpio.includes('rúben')) {
        return 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNTAiIGhlaWdodD0iMTUwIiB2aWV3Qm94PSIwIDAgMTUwIDE1MCI+PGNpcmNsZSBjeD0iNzUiIGN5PSI3NSIgcj0iNzAiIGZpbGw9IiMyOGE3NDUiLz48dGV4dCB4PSI3NSIgeT0iODAiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxOCIgZmlsbD0id2hpdGUiPkhldG9yPC90ZXh0Pjwvc3ZnPg==';
      } else {
        return 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNTAiIGhlaWdodD0iMTUwIiB2aWV3Qm94PSIwIDAgMTUwIDE1MCI+PGNpcmNsZSBjeD0iNzUiIGN5PSI3NSIgcj0iNzAiIGZpbGw9IiM2Yzc1N2QiLz48dGV4dCB4PSI3NSIgeT0iODAiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxOCIgZmlsbD0id2hpdGUiPkFnZW50ZTwvdGV4dD48L3N2Zz4=';
      }
    };

    // Calcular total
    const calcularTotal = computed(() => {
      if (!store.servicioActual) return '12.00';
      return parseFloat(store.servicioActual.precio).toFixed(2);
    });

    // Cargar peluqueros al montar
    onMounted(async () => {
      try {
        const res = await fetch('http://localhost/peluqueria/backend/api/get_peluqueros.php');
        const data = await res.json();
        store.setPeluqueros(data);
      } catch (error) {
        console.error('Error al cargar peluqueros:', error);
      }
    });

    // Métodos
    const seleccionarPeluquero = (p) => {
      store.seleccionarPeluquero(p);
      vista.value = 'calendario';
    };

    const volverProfesionales = () => {
      vista.value = 'profesionales';
      store.seleccionarFecha(null);
      store.seleccionarHora(null);
    };

    const cerrarModal = () => {
      store.cerrarModal();
      vista.value = 'profesionales';
    };

    const confirmarReserva = async () => {
      if (!store.peluqueroSeleccionado || !store.servicioActual) return;

      try {
        const res = await fetch('http://localhost/peluqueria/backend/api/crear_reserva.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            servicio_id: store.servicioActual.id,
            peluquero_id: store.peluqueroSeleccionado.id,
            fecha: store.fechaSeleccionada,
            hora: store.horaSeleccionada
          })
        });
        
        const data = await res.json();
        if (res.ok) {
          alert('¡Reserva confirmada!');
          cerrarModal();
          window.location.reload();
        } else {
          alert(data.message || 'Error al reservar');
        }
      } catch (err) {
        alert('Error de conexión');
      }
    };

    return {
      store,
      vista,
      getAvatarByNombre,
      calcularTotal,
      seleccionarPeluquero,
      volverProfesionales,
      cerrarModal,
      confirmarReserva
    };
  }
};
</script>