<template>
  <div class="reporte-container">
    <div class="reporte-card">
      <h2 class="titulo-reporte">Reportar una incidencia</h2>
      <p class="subtitulo">Cuéntanos qué sucede y te ayudaremos lo antes posible.</p>

      <form @submit.prevent="enviarIncidencia" class="reporte-form">
        
        <div class="input-group">
          <input 
            type="text" 
            v-model="incidencia.nombre" 
            placeholder="Nombre completo" 
            required 
            class="input-field"
          />
        </div>

        <div class="input-group">
          <input 
            type="email" 
            v-model="incidencia.email" 
            placeholder="Correo electrónico" 
            required 
            class="input-field"
          />
        </div>

        <div class="input-group">
          <textarea 
            v-model="incidencia.observaciones" 
            placeholder="Observaciones (detalla tu problema aquí...)" 
            required 
            class="input-field textarea-field"
            rows="5"
          ></textarea>
        </div>

        <button 
        type="submit" 
        :class="['btn-submit', { 'btn-disabled': !formularioValido || enviando }]"
        :disabled="!formularioValido || enviando"
      >
        {{ enviando ? 'Enviando...' : 'Enviar reporte' }}
      </button>

        <p class="toggle-link">
          <router-link to="/login" class="link-rosa">
            Volver a Iniciar sesión
          </router-link>
        </p>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const enviando = ref(false);
const incidencia = ref({
  nombre: '',
  email: '',
  observaciones: ''
});

const formularioValido = computed(() => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return (
    incidencia.value.nombre.trim().length > 0 &&
    emailRegex.test(incidencia.value.email) &&
    incidencia.value.observaciones.trim().length > 10 // Al menos 10 caracteres de explicación
  );
});
const enviarIncidencia = async () => {
  enviando.value = true;
  try {
    const response = await fetch('/backend/api/reportes.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        ...incidencia.value,
        destinatario: 'soporte@rgutierrezhairstudio.com' // Tu correo de destino
      })
    });
    
    const data = await response.json();
    if (data.success) {
      alert('Tu mensaje ha sido enviado a soporte@rgutierrezhairstudio.com. Te contactaremos pronto.');
      incidencia.value = { nombre: '', email: '', observaciones: '' };
    } else {
      alert('Error: ' + data.message);
    }
  } catch (err) {
    alert('Hubo un problema al conectar con el servidor.');
  } finally {
    enviando.value = false;
  }
};
</script>

<style scoped>
/* Contenedor principal oscuro como tu Login */
.reporte-container {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background: #000; /* Fondo negro puro como en la imagen */
  padding: 1rem;
}

.reporte-card {
  max-width: 500px;
  width: 100%;
  padding: 2.5rem;
  background: #121212; /* Gris muy oscuro */
  border-radius: 16px;
  color: white;
  text-align: center;
}

.titulo-reporte {
  font-size: 1.8rem;
  margin-bottom: 0.5rem;
  font-weight: bold;
}

.subtitulo {
  color: #888;
  font-size: 0.9rem;
  margin-bottom: 2rem;
}

.input-group {
  margin-bottom: 1.2rem;
}

/* Estilo de los inputs idéntico a tu formulario de alta */
.input-field {
  width: 100%;
  padding: 0.85rem;
  border: 1px solid #333;
  background: #1a1a1a;
  color: white;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.3s;
}

.input-field:focus {
  outline: none;
  border-color: #e75480; /* Rosa corporativo */
}

.textarea-field {
  resize: none;
  font-family: inherit;
}

/* Botón con el estilo rosa de Essencia */
.btn-submit {
  width: 100%;
  padding: 0.85rem;
  background: #444; /* Gris por defecto como en tu botón 'Crear cuenta' */
  color: #999;
  border: none;
  border-radius: 8px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s;
  font-size: 1rem;
  margin-top: 1rem;
}

.btn-submit:not(:disabled):hover {
  background: #e75480;
  color: white;
}

.btn-disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.toggle-link {
  margin-top: 1.5rem;
  font-size: 0.9rem;
}

.link-rosa {
  color: #e75480;
  text-decoration: none;
  font-weight: bold;
}

.link-rosa:hover {
  text-decoration: underline;
}
</style>