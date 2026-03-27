<template>
  <div class="etiquetas-view">
    <header class="section-header">
      <h1>Gestión de Etiquetas</h1>
      <p class="text-muted">Crea etiquetas personalizadas para organizar a tus clientes</p>
    </header>

    <div class="etiquetas-grid">
      <div class="card create-card">
       <h3>{{ editandoId ? 'Editar Etiqueta' : 'Nueva Etiqueta' }}</h3>
        <div class="form-group">
          <label>Nombre</label>
          <input v-model="nuevaEtiqueta.nombre" placeholder="Ej: VIP, Recurrente...">
        </div>
        <div class="form-group">
          <label>Color</label>
          <input type="color" v-model="nuevaEtiqueta.color" class="color-picker">
        </div>
        <div class="form-actions">
    <button @click="guardarEtiqueta" class="btn-save" :disabled="!nuevaEtiqueta.nombre">
      {{ editandoId ? 'Actualizar' : 'Guardar Etiqueta' }}
    </button>
    <button v-if="editandoId" @click="cancelarEdicion" class="btn-cancel-edit">
      Cancelar
    </button>
  </div>
      </div>

      <div class="card list-card">
        <h3>Etiquetas Existentes</h3>
        <div class="tags-list">
            <div v-for="tag in etiquetas" :key="tag.id" class="tag-item">
                <span class="tag-badge" :style="{ backgroundColor: tag.color }">
                    {{ tag.nombre }}
                </span>
                <div class="tag-actions">
                    <button @click="prepararEdicion(tag)" class="btn-edit-tag">
                    <i class="fas fa-edit"></i>
                    </button>
                    <button @click="abrirModalBorrar(tag)" class="btn-delete-tag">
                    <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            
          <p v-if="etiquetas.length === 0" class="empty">No hay etiquetas creadas.</p>
        </div>
      </div>
    </div>

    

    <div v-if="modalBorrar.abierto" class="modal-overlay">
      <div class="modal-content confirm-modal">
        <div class="modal-icon-warning">
          <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h3>¿Eliminar etiqueta?</h3>
        <p>
          Estás a punto de borrar la etiqueta <strong>"{{ modalBorrar.tagNombre }}"</strong>. 
          Se eliminará de todos los clientes que la tengan asignada.
        </p>
        <div class="modal-actions-confirm">
          <button @click="modalBorrar.abierto = false" class="btn-cancel-modal">
            No, mantener
          </button>
          <button @click="confirmarBorrado" class="btn-confirm-delete">
            Sí, eliminar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const etiquetas = ref([]);
const nuevaEtiqueta = ref({ nombre: '', color: '#3498db' });
const editandoId = ref(null); // Guardará el ID si estamos editando

const modalBorrar = ref({
  abierto: false,
  tagId: null,
  tagNombre: ''
});

const prepararEdicion = (tag) => {
  editandoId.value = tag.id;
  nuevaEtiqueta.value = { nombre: tag.nombre, color: tag.color };
};

const cancelarEdicion = () => {
  editandoId.value = null;
  nuevaEtiqueta.value = { nombre: '', color: '#3498db' };
};

const guardarEtiqueta = async () => {
  if (!nuevaEtiqueta.value.nombre) return;
  
  const url = '/backend/api/etiquetas.php';
  // Si hay editandoId, usamos PUT o enviamos el ID en el POST
  const metodo = editandoId.value ? 'PUT' : 'POST';
  const body = editandoId.value 
    ? { ...nuevaEtiqueta.value, id: editandoId.value } 
    : nuevaEtiqueta.value;

  try {
    await fetch(url, {
      method: metodo,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body)
    });
    
    cancelarEdicion(); // Limpia el formulario y el estado de edición
    await cargarEtiquetas();
  } catch (e) {
    console.error("Error al guardar:", e);
  }
};

const cargarEtiquetas = async () => {
  try {
    const res = await fetch('/backend/api/etiquetas.php');
    etiquetas.value = await res.json();
  } catch (e) {
    console.error("Error al cargar etiquetas:", e);
  }
};

const crearEtiqueta = async () => {
  if (!nuevaEtiqueta.value.nombre) return;
  
  try {
    await fetch('/backend/api/etiquetas.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(nuevaEtiqueta.value)
    });
    nuevaEtiqueta.value = { nombre: '', color: '#3498db' };
    await cargarEtiquetas();
  } catch (e) {
    console.error("Error al crear etiqueta:", e);
  }
};

const abrirModalBorrar = (tag) => {
  modalBorrar.value = {
    abierto: true,
    tagId: tag.id,
    tagNombre: tag.nombre
  };
};

const confirmarBorrado = async () => {
  if (!modalBorrar.value.tagId) return;
  
  try {
    await fetch(`/backend/api/etiquetas.php?id=${modalBorrar.value.tagId}`, { 
      method: 'DELETE' 
    });
    modalBorrar.value.abierto = false;
    await cargarEtiquetas();
  } catch (e) {
    console.error("Error al borrar:", e);
  }
};

onMounted(cargarEtiquetas);
</script>

<style scoped>
/* Contenedor Principal */
.etiquetas-view {
  padding: 20px 40px;
  background: #f8fafc;
  min-height: 100vh;
  box-sizing: border-box;
}

/* Header */
.section-header {
  margin-bottom: 30px;
}

.section-header h1 {
  font-size: 1.8rem;
  color: #1e293b;
  margin: 0;
  font-weight: 800;
}

.text-muted {
  color: #64748b;
  font-size: 0.9rem;
  margin-top: 5px;
}

/* Grid Layout */
.etiquetas-grid {
  display: grid;
  grid-template-columns: 350px 1fr; /* Columna izquierda fija, derecha flexible */
  gap: 30px;
  align-items: start;
}

/* Tarjetas (Cards) */
.card {
  background: white;
  padding: 25px;
  border-radius: 20px;
  border: 1px solid #f1f5f9;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
}

.card h3 {
  margin-top: 0;
  margin-bottom: 20px;
  font-size: 1.1rem;
  color: #334155;
  font-weight: 700;
  border-bottom: 2px solid #f1f5f9;
  padding-bottom: 10px;
}

/* Formulario */
.form-group {
  margin-bottom: 15px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group label {
  font-weight: 700;
  font-size: 0.85rem;
  color: #475569;
  text-transform: uppercase;
}

.form-group input {
  padding: 12px;
  border-radius: 10px;
  border: 2px solid #f1f5f9;
  background: #f8fafc;
  outline: none;
  transition: 0.3s;
}

.form-group input:focus {
  border-color: #2ecc71;
  background: white;
}

/* Color Picker */
.color-picker {
  height: 50px;
  padding: 5px !important;
  cursor: pointer;
}

/* Botón Guardar */
.btn-save {
  width: 100%;
  padding: 14px;
  background: #2ecc71;
  color: white;
  border: none;
  border-radius: 12px;
  font-weight: 800;
  cursor: pointer;
  transition: 0.3s;
  box-shadow: 0 4px 12px rgba(46, 204, 113, 0.2);
  margin-top: 10px;
}

.btn-save:hover:not(:disabled) {
  transform: translateY(-2px);
  filter: brightness(1.1);
}

.btn-save:disabled {
  background: #cbd5e1;
  cursor: not-allowed;
}

/* Lista de Etiquetas */
.tags-list {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}

.tag-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 12px;
  background: #f8fafc;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  transition: 0.2s;
}

.tag-badge {
  padding: 4px 12px;
  border-radius: 8px;
  color: white;
  font-weight: 700;
  font-size: 0.85rem;
  text-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.btn-delete-tag {
  background: none;
  border: none;
  color: #94a3b8;
  cursor: pointer;
  font-size: 0.9rem;
  transition: 0.2s;
}

.btn-delete-tag:hover {
  color: #ef4444;
}

.empty {
  color: #94a3b8;
  font-style: italic;
  text-align: center;
  width: 100%;
}

/* --- ESTILOS MODAL BORRAR --- */
.confirm-modal {
  background: white;
  padding: 30px;
  border-radius: 24px;
  width: 90%;
  max-width: 400px;
  text-align: center;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.modal-icon-warning {
  font-size: 3rem;
  color: #f59e0b; /* Color ámbar/aviso */
  margin-bottom: 15px;
}

.confirm-modal h3 {
  font-size: 1.4rem;
  color: #1e293b;
  margin-bottom: 10px;
  border: none; /* Quitamos el borde que tienen los otros h3 */
}

.confirm-modal p {
  color: #64748b;
  line-height: 1.5;
  margin-bottom: 25px;
}

.modal-actions-confirm {
  display: flex;
  gap: 12px;
}

.btn-confirm-delete {
  flex: 1;
  padding: 12px;
  background: #ef4444; /* Rojo */
  color: white;
  border: none;
  border-radius: 12px;
  font-weight: 700;
  cursor: pointer;
  transition: 0.2s;
}

.btn-confirm-delete:hover {
  background: #dc2626;
}

.btn-cancel-modal {
  flex: 1;
  padding: 12px;
  background: #f1f5f9;
  color: #475569;
  border: none;
  border-radius: 12px;
  font-weight: 700;
  cursor: pointer;
}

.btn-cancel-modal:hover {
  background: #e2e8f0;
}
</style>