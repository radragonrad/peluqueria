<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-content-google">
      <div class="modal-header-google">
        <div class="header-title">
          <span class="material-icons">person_add</span>
          <h3>Nuevo Cliente</h3>
        </div>
        <button class="btn-close-icon" @click="$emit('close')">
          <span class="material-icons">close</span>
        </button>
      </div>

      <div class="modal-body-google">
        <div class="input-group-google">
          <label>Nombre Completo</label>
          <div class="input-wrapper">
            <span class="material-icons">badge</span>
            <input 
              type="text" 
              v-model="form.nombre" 
              placeholder="Ej. Juan Pérez" 
              ref="inputNombre"
            />
          </div>
        </div>

        <div class="input-group-google">
          <label>Teléfono</label>
          <div class="input-wrapper">
            <span class="material-icons">phone</span>
            <input 
              type="tel" 
              v-model="form.telefono" 
              placeholder="600 000 000" 
            />
          </div>
        </div>

        <div class="input-group-google">
          <label>Fecha de Nacimiento</label>
          <div class="input-wrapper">
            <span class="material-icons">cake</span>
            <input 
              type="date" 
              v-model="form.fecha_nacimiento" 
            />
          </div>
        </div>

        <div class="input-group-google">
          <label>Correo Electrónico (Opcional)</label>
          <div class="input-wrapper">
            <span class="material-icons">email</span>
            <input 
              type="email" 
              v-model="form.email" 
              placeholder="cliente@correo.com" 
            />
          </div>
        </div>
      </div>

      <!-- Toast de notificación -->
      <transition name="toast-fade">
        <div v-if="toast.visible" :class="['toast-notify', toast.tipo]">
          <span class="material-icons toast-icon">{{ toast.tipo === 'exito' ? 'check_circle' : 'error' }}</span>
          <span class="toast-msg">{{ toast.mensaje }}</span>
        </div>
      </transition>

      <div class="modal-footer-google">
        <button class="btn-cancel" @click="$emit('close')" :disabled="cargando">
          Cancelar
        </button>
        <button class="btn-save-google" 
            @click="guardarUsuario" 
            :disabled="cargando || !form.nombre.trim()"
            :class="{ 'btn-disabled': !form.nombre.trim() }">
          {{ cargando ? 'Guardando...' : 'Guardar Cliente' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';

const emit = defineEmits(['close', 'usuario-creado']);
const cargando = ref(false);
const inputNombre = ref(null);

const toast = reactive({ visible: false, mensaje: '', tipo: 'exito' });
let toastTimer = null;

const mostrarToast = (mensaje, tipo = 'exito', duracion = 3000) => {
  if (toastTimer) clearTimeout(toastTimer);
  toast.mensaje = mensaje;
  toast.tipo = tipo;
  toast.visible = true;
  if (tipo === 'exito') {
    toastTimer = setTimeout(() => { toast.visible = false; }, duracion);
  }
};

const form = reactive({
  nombre: '',
  telefono: '',
  email: '',
  fecha_nacimiento: '' // Inicializado vacío
});

onMounted(() => {
  inputNombre.value?.focus();
});

const guardarUsuario = async () => {
  // Validación de seguridad adicional
  if (!form.nombre.trim()) return;

  // El teléfono lo podemos dejar como opcional o validarlo también:
  // if (!form.telefono.trim()) { alert('El teléfono es necesario'); return; }

  cargando.value = true;
  try {
    const resp = await fetch('/backend/api/registrar_cliente_admin.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(form)
    });
    
    const data = await resp.json();

    if (data.success) {
      mostrarToast('Cliente creado correctamente', 'exito');
      setTimeout(() => { emit('usuario-creado', data.nombre_cliente); emit('close'); }, 1200);
    } else {
      mostrarToast('Error: ' + (data.error || 'No se pudo guardar el usuario'), 'error');
    }
  } catch (error) {
    mostrarToast('Error de conexión con el servidor', 'error');
  } finally {
    cargando.value = false;
  }
};
</script>

<style scoped>
/* Mantener los mismos estilos anteriores */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(60, 64, 67, 0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 3000;
}

.modal-content-google {
  background: white;
  width: 100%;
  max-width: 420px;
  border-radius: 8px;
  box-shadow: 0 12px 15px 0 rgba(0,0,0,0.24);
  overflow: hidden;
  font-family: 'Roboto', Arial, sans-serif;
}

.modal-header-google {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 24px;
  border-bottom: 1px solid #dadce0;
}

.header-title {
  display: flex;
  align-items: center;
  gap: 12px;
  color: #3c4043;
}

.header-title h3 {
  margin: 0;
  font-size: 18px;
  font-weight: 400;
}

.btn-close-icon {
  background: none;
  border: none;
  color: #5f6368;
  cursor: pointer;
  padding: 4px;
  border-radius: 50%;
  display: flex;
}

.btn-close-icon:hover {
  background-color: #f1f3f4;
}

.modal-body-google {
  padding: 8px 0;
}

.input-group-google {
  padding: 10px 24px; /* Un poco más compacto para que quepan todos los campos */
}

.input-group-google label {
  display: block;
  font-size: 12px;
  font-weight: 500;
  color: #5f6368;
  margin-bottom: 4px;
}

.input-wrapper {
  display: flex;
  align-items: center;
  border: 1px solid #dadce0;
  border-radius: 4px;
  padding: 8px 12px;
  transition: border-color 0.2s;
}

.input-wrapper:focus-within {
  border: 2px solid #1a73e8;
  padding: 7px 11px;
}

.input-wrapper .material-icons {
  font-size: 20px;
  color: #5f6368;
}

.input-wrapper input {
  border: none;
  outline: none;
  width: 100%;
  margin-left: 12px;
  font-size: 14px;
  color: #3c4043;
  background: transparent;
}

.modal-footer-google {
  padding: 16px 24px;
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  border-top: 1px solid #dadce0;
  background-color: #f8f9fa;
}

.btn-cancel {
  background: none;
  border: none;
  padding: 8px 16px;
  color: #5f6368;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  border-radius: 4px;
}

.btn-cancel:hover {
  background-color: #f1f3f4;
}

.btn-save-google {
  background-color: #1a73e8;
  color: white;
  border: none;
  padding: 8px 24px;
  border-radius: 4px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  box-shadow: 0 1px 2px rgba(60,64,67,0.3);
}

.btn-save-google:hover {
  background-color: #1765cc;
}

.btn-save-google:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

/* Añade esto a tu sección de <style scoped> */
.btn-save-google:disabled {
  background-color: #e8eaed; /* Gris claro estilo Google */
  color: #9aa0a6;           /* Texto gris suave */
  cursor: not-allowed;
  box-shadow: none;
}

/* Opcional: un efecto visual de "campo obligatorio" en el borde */
.input-wrapper.error {
  border-color: #d93025;
}

/* ── Toast de notificación ── */
.toast-notify {
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 0 24px 12px;
  padding: 11px 16px;
  border-radius: 6px;
  font-size: 13.5px;
  font-weight: 500;
  box-shadow: 0 2px 8px rgba(0,0,0,0.12);
}

.toast-notify.exito {
  background: #e6f4ea;
  color: #1e7e34;
  border-left: 4px solid #34a853;
}

.toast-notify.error {
  background: #fce8e6;
  color: #c5221f;
  border-left: 4px solid #d93025;
}

.toast-icon {
  font-size: 20px;
  flex-shrink: 0;
}

.toast-msg {
  line-height: 1.4;
}

/* Animación suave de entrada/salida */
.toast-fade-enter-active,
.toast-fade-leave-active {
  transition: opacity 0.25s ease, transform 0.25s ease;
}
.toast-fade-enter-from,
.toast-fade-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}
</style>