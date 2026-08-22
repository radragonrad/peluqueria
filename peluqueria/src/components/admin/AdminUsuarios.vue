<template>
  <div class="usuarios-view">
    <header class="section-header">
      <div class="header-left">
        <div class="breadcrumb">
          <span>Panel de Control</span> 
          <i class="fas fa-chevron-right"></i> 
          <span class="current">Usuarios</span>
        </div>
        <div class="title-container">
          <h1>Gestión de Clientes y Staff</h1>
          <span class="badge-count">{{ usuariosFiltrados.length }} registrados</span>
        </div>
      </div>

      <div class="header-actions">
        <div class="filters">
          <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" v-model="search" placeholder="Buscar por nombre o email..." @input="currentPage = 1">
          </div>
          <select v-model="rolFilter" class="status-select" @change="currentPage = 1">
            <option value="all">Todos los roles</option>
            <option value="usuario">Clientes</option>
            <option value="empleado">Staff / Empleados</option>
            <option value="admin">Administradores</option>
          </select>
        </div>
        
        <button @click="abrirNuevoUsuario()" class="btn-nuevo">
          <i class="fas fa-user-plus"></i> Añadir Nuevo
        </button>
      </div>
    </header>

    <transition name="slide">
      <div v-if="mostrarForm" class="form-card">
        <h3>{{ form.id ? 'Editar Perfil de Usuario' : 'Nuevo Registro de Usuario' }}</h3>

        <div class="form-grid">
          <div class="input-group">
            <label>Nombre Completo</label>
            <input type="text" v-model="form.nombre" placeholder="Ej: Juan Pérez" autocomplete="off">
          </div>
          <div class="input-group">
            <label>Correo Electrónico</label>
            <input type="email" v-model="form.email" placeholder="correo@ejemplo.com" autocomplete="off">
          </div>
          <div class="input-group">
            <label>Teléfono</label>
            <input type="text" v-model="form.telefono" placeholder="600 000 000">
          </div>
          <div class="input-group">
            <label>Fecha de Nacimiento</label>
            <input type="date" v-model="form.fecha_nacimiento">
          </div>
          
          <div class="input-group" :class="{ 'full-width': form.rol === 'usuario' }">
            <label>Rol en el Sistema</label>
            <select v-model="form.rol" class="role-select-input">
              <option value="usuario">Cliente</option>
              <option value="empleado">Empleado (Staff)</option>
              <option value="admin">Administrador</option>
            </select>
          </div>

          <div v-if="form.id" class="input-group full-width etiquetas-edit-section">
            <label><i class="fas fa-tags"></i> Etiquetas asignadas</label>
            <div class="tags-wrapper-edit">
              <div class="current-tags">
                <span v-for="tag in form.etiquetas" :key="tag.id" 
                      :style="{ backgroundColor: tag.color }" class="tag-pill-edit">
                  {{ tag.nombre }}
                  <button @click="quitarEtiqueta(tag.id)" class="btn-remove-tag">&times;</button>
                </span>
                <span v-if="!form.etiquetas?.length" class="no-tags-text">Sin etiquetas</span>
              </div>
              <select @change="asignarEtiqueta($event)" class="select-add-tag">
                <option value="">+ Añadir etiqueta...</option>
                <option v-for="t in todasLasEtiquetas" :key="t.id" :value="t.id">
                  {{ t.nombre }}
                </option>
              </select>
            </div>
          </div>

          <transition name="fade">
            <div v-if="form.rol !== 'usuario'" class="staff-wrapper full-width">
              <div class="form-grid-inner">
                <div class="input-group">
                  <label>Especialidad / Cargo</label>
                  <input type="text" v-model="form.especialidad" placeholder="Ej: Experto en Degradados">
                </div>
                <div class="input-group">
                  <label>Foto de Perfil</label>
                  <div class="file-input-wrapper">
                    <input type="file" @change="handleFileUpload" accept="image/*" id="avatar-file">
                    <label for="avatar-file" class="file-custom">
                      <i class="fas fa-camera"></i> {{ selectedFile ? selectedFile.name : 'Seleccionar Imagen' }}
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </transition>

          <div class="form-actions">
            <button @click="guardarUsuario" class="btn-save" :disabled="formularioInvalido">
              <i class="fas fa-check-circle"></i> Guardar Cambios
            </button>
            <button @click="mostrarForm = false" class="btn-cancel">Cancelar</button>
          </div>
        </div>        
      </div>
    </transition>

    <div class="table-card">
      <table class="custom-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Usuario / Cliente</th>
            <th>Contacto</th>
            <th>Rol</th>
            <th>Etiquetas</th>
            <th>Estado</th>
            <th class="text-right">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="u in usuariosPaginados" :key="u.id" :class="{ 'servicio-desactivado': u.activo == 0 }">
            <td data-label="ID" class="id-cell">#{{ u.id }}</td>
            <td data-label="Usuario">
              <div class="service-info-cell">
                <div class="icon-wrapper">
                  <img v-if="u.avatar" :src="`/uploads/avatares/${u.avatar}`" class="table-icon circular" />
                  <img v-else :src="u.generado === 'web' ? '/uploads/avatares/default-avatar-web.png' : '/uploads/avatares/default-avatar-local.png'" class="table-icon circular" />
                </div>
                <div class="text-wrapper">
                  <span class="service-name">{{ u.nombre }}</span>
                  <span class="service-desc">@{{ u.usuario || 'cliente_local' }}</span>
                </div>
              </div>
            </td>
            <td data-label="Contacto">
              <div class="contact-cell">
                <span class="email-text"><i class="far fa-envelope"></i> {{ u.email }}</span>
                <span class="phone-text" v-if="u.telefono"><i class="fas fa-phone-alt"></i> {{ u.telefono }}</span>
              </div>
            </td>
            <td data-label="Rol">
              <span :class="['pill-badge', `pill-${u.rol}`]">
                {{ u.rol === 'admin' ? 'ADMIN' : u.rol === 'empleado' ? 'STAFF' : 'CLIENTE' }}
              </span>
            </td>
            <td data-label="Etiquetas">
              <div class="tags-container-table">
                <span v-for="tag in u.etiquetas" 
                      :key="tag.id" 
                      class="tag-mini" 
                      :style="{ backgroundColor: tag.color }">
                  {{ tag.nombre }}
                </span>
              </div>
            </td>
            <td data-label="Estado">
              <label class="switch">
                <input type="checkbox" :checked="u.activo == 1" @change="toggleActivo(u)">
                <span class="slider"></span>
              </label>
            </td>
            <td data-label="Acciones" class="actions text-right">
              <button @click="editarUsuario(u)" class="btn-icon edit"><i class="fas fa-edit"></i></button>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="pagination" v-if="totalPages > 1">
        <button :disabled="currentPage === 1" @click="currentPage--" class="btn-page">Anterior</button>
        <span class="page-info">Página {{ currentPage }} de {{ totalPages }}</span>
        <button :disabled="currentPage === totalPages" @click="currentPage++" class="btn-page">Siguiente</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';

const usuarios = ref([]);
const todasLasEtiquetas = ref([]);
const search = ref('');
const rolFilter = ref('all');
const mostrarForm = ref(false);
const currentPage = ref(1);
const itemsPerPage = 8;
const form = ref({ rol: 'usuario', activo: 1, etiquetas: [] });
const selectedFile = ref(null);

const cargarUsuarios = async () => {
  const res = await fetch('/backend/api/gestion_usuarios.php', { credentials: 'include' });
  usuarios.value = await res.json();
};

const cargarMaestroEtiquetas = async () => {
  const res = await fetch('/backend/api/etiquetas.php');
  todasLasEtiquetas.value = await res.json();
};

const formularioInvalido = computed(() => {
  return !form.value.nombre || !form.value.email || !form.value.rol;
});

const usuariosFiltrados = computed(() => {
  return usuarios.value.filter(u => {
    const matchesSearch = (u.nombre || '').toLowerCase().includes(search.value.toLowerCase()) || 
                          (u.email || '').toLowerCase().includes(search.value.toLowerCase());
    const matchesRol = rolFilter.value === 'all' || u.rol === rolFilter.value;
    return matchesSearch && matchesRol;
  });
});

const totalPages = computed(() => Math.ceil(usuariosFiltrados.value.length / itemsPerPage));
const usuariosPaginados = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return usuariosFiltrados.value.slice(start, start + itemsPerPage);
});

const abrirNuevoUsuario = () => {
  form.value = { rol: 'usuario', activo: 1, nombre: '', email: '', telefono: '', especialidad: '', etiquetas: [] };
  selectedFile.value = null;
  mostrarForm.value = true;
};

const editarUsuario = (u) => {
  form.value = { ...u, etiquetas: u.etiquetas || [] };
  mostrarForm.value = true;
};

// GESTIÓN DE ETIQUETAS EN EL FORMULARIO
const asignarEtiqueta = async (event) => {
  const etiquetaId = event.target.value;
  if (!etiquetaId || !form.value.id) return;

  await fetch('/backend/api/clientes_etiquetas.php', {
    method: 'POST',
    body: JSON.stringify({ cliente_id: form.value.id, etiqueta_id: etiquetaId })
  });
  
  await cargarUsuarios(); // Recargar datos maestros
  // Actualizar el formulario local para ver el cambio inmediato
  const userActualizado = usuarios.value.find(u => u.id === form.value.id);
  if (userActualizado) form.value.etiquetas = userActualizado.etiquetas;
  event.target.value = ""; 
};

const quitarEtiqueta = async (etiquetaId) => {
  await fetch(`/backend/api/clientes_etiquetas.php?cliente_id=${form.value.id}&etiqueta_id=${etiquetaId}`, {
    method: 'DELETE'
  });
  await cargarUsuarios();
  const userActualizado = usuarios.value.find(u => u.id === form.value.id);
  if (userActualizado) form.value.etiquetas = userActualizado.etiquetas;
};

const handleFileUpload = (e) => { selectedFile.value = e.target.files[0]; };

const toggleActivo = async (u) => {
    const estadoAnterior = u.activo;
    const nuevoEstado = u.activo == 1 ? 0 : 1;
    const formData = new FormData();
    formData.append('id', u.id);
    formData.append('activo', nuevoEstado);

    u.activo = nuevoEstado;

    try {
        const res = await fetch('/backend/api/gestion_usuarios.php', {
            method: 'POST',
            credentials: 'include',
            body: formData
        });
        const data = await res.json();
        if (!res.ok || data.success === false) throw new Error(data.error || 'Error al guardar el estado');
    } catch (e) {
        // Si el backend no lo ha guardado, deshacemos el cambio visual
        u.activo = estadoAnterior;
        console.error(e);
        alert('No se ha podido cambiar el estado del usuario.');
    }
};

const guardarUsuario = async () => {
  const formData = new FormData();
  Object.keys(form.value).forEach(key => {
      if (key !== 'etiquetas' && form.value[key] !== null) {
          formData.append(key, form.value[key]);
      }
  });
  if (selectedFile.value) formData.append('avatar', selectedFile.value);

  await fetch('/backend/api/gestion_usuarios.php', {
    method: 'POST',
    credentials: 'include',
    body: formData
  });
  mostrarForm.value = false;
  cargarUsuarios();
};

onMounted(() => {
  cargarUsuarios();
  cargarMaestroEtiquetas();
});
</script>

<style scoped>
/* Estilos anteriores se mantienen... */

/* NUEVOS ESTILOS PARA ETIQUETAS */
.etiquetas-edit-section {
  background: #fdf2f5;
  padding: 15px;
  border-radius: 12px;
  border: 1px solid #f9dbe5;
}

.tags-wrapper-edit {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.current-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.tag-pill-edit {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 4px 12px;
  border-radius: 20px;
  color: white;
  font-size: 0.8rem;
  font-weight: 700;
}

.btn-remove-tag {
  background: rgba(0,0,0,0.2);
  border: none;
  color: white;
  border-radius: 50%;
  width: 18px;
  height: 18px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.select-add-tag {
  padding: 8px;
  border-radius: 8px;
  border: 1px solid #ddd;
  font-size: 0.85rem;
}

.no-tags-text {
  color: #999;
  font-style: italic;
  font-size: 0.85rem;
}

/* Estilos de tabla */
.tags-container-table { display: flex; flex-wrap: wrap; gap: 4px; }
.tag-mini { font-size: 0.7rem; padding: 2px 8px; border-radius: 10px; color: white; font-weight: bold; white-space: nowrap; }

/* REUTILIZADOS DEL CÓDIGO ANTERIOR */
.section-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; padding-bottom: 10px; border-bottom: 1px solid #f0f0f0; }
.header-left .breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 0.8rem; color: #999; margin-bottom: 8px; text-transform: uppercase; }
.breadcrumb .current { color: #e75480; font-weight: 600; }
.title-container { display: flex; align-items: center; gap: 15px; }
.title-container h1 { margin: 0; font-size: 1.8rem; color: #2c3e50; font-weight: 800; }
.badge-count { background: #fdf2f5; color: #e75480; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; border: 1px solid #f9dbe5; }
.btn-nuevo { background: linear-gradient(135deg, #e75480 0%, #c13660 100%); color: white; border: none; padding: 12px 24px; border-radius: 10px; font-weight: 600; display: flex; align-items: center; gap: 10px; box-shadow: 0 4px 15px rgba(231, 84, 128, 0.3); cursor: pointer; }
.header-actions { display: flex; gap: 15px; align-items: center; }
.filters { display: flex; gap: 10px; }
.search-box { position: relative; }
.search-box input { padding: 10px 10px 10px 35px; border: 1px solid #ddd; border-radius: 8px; width: 250px; }
.search-box i { position: absolute; left: 12px; top: 12px; color: #999; }
.status-select { padding: 10px; border-radius: 8px; border: 1px solid #ddd; background: white; }
.table-card { background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; }
.custom-table { width: 100%; border-collapse: collapse; }
.custom-table th { background: #f8f9fa; padding: 15px; text-align: left; color: #888; font-size: 0.75rem; text-transform: uppercase; }
.custom-table td { padding: 15px; border-bottom: 1px solid #eee; vertical-align: middle; }
.service-info-cell { display: flex; align-items: center; gap: 12px; }
.circular { border-radius: 50%; width: 40px !important; height: 40px !important; object-fit: cover; }
.text-wrapper { display: flex; flex-direction: column; }
.service-name { font-weight: 700; color: #2c3e50; }
.service-desc { font-size: 0.75rem; color: #94a3b8; }
.pill-badge { padding: 4px 10px; border-radius: 20px; font-size: 0.65rem; font-weight: 700; }
.pill-admin { background: #fff1f2; color: #e11d48; }
.pill-empleado { background: #eff6ff; color: #2563eb; }
.pill-usuario { background: #f8fafc; color: #64748b; }
.form-card { background: white; padding: 30px; border-radius: 16px; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08); border: 1px solid #f0f0f0; }
.form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
.input-group { display: flex; flex-direction: column; gap: 8px; }
.full-width { grid-column: 1 / -1; }
.btn-save { background: #2ecc71; color: white; border: none; padding: 12px 30px; border-radius: 10px; font-weight: 600; cursor: pointer; }
.btn-cancel { background: #f1f2f6; color: #57606f; border: none; padding: 12px 30px; border-radius: 10px; font-weight: 600; cursor: pointer; }
.switch { position: relative; display: inline-block; width: 40px; height: 20px; }
.switch input { opacity: 0; width: 0; height: 0; }
.slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 20px; }
.slider:before { position: absolute; content: ""; height: 14px; width: 14px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
input:checked + .slider { background-color: #28a745; }
input:checked + .slider:before { transform: translateX(20px); }
/* --- FORMULARIO DE USUARIO (CORREGIDO) --- */
.form-card {
  background: white;
  padding: 30px;
  border-radius: 16px;
  margin-bottom: 30px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
  border: 1px solid #f0f0f0;
  animation: slideDown 0.4s ease-out;
}

@keyframes slideDown {
  from { opacity: 0; transform: translateY(-20px); }
  to { opacity: 1; transform: translateY(0); }
}

.form-card h3 {
  margin-top: 0;
  margin-bottom: 25px;
  color: #2c3e50;
  border-left: 4px solid #e75480;
  padding-left: 15px;
  font-size: 1.2rem;
}

/* Rejilla principal del formulario */
.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr); /* Dos columnas */
  gap: 20px;
}

.input-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.input-group label {
  font-size: 0.85rem;
  font-weight: 700;
  color: #4a5568;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.input-group input, 
.input-group select {
  padding: 12px;
  border: 2px solid #f1f5f9;
  border-radius: 10px;
  background: #f8fafc;
  transition: 0.3s;
  font-size: 0.95rem;
}

.input-group input:focus, 
.input-group select:focus {
  border-color: #e75480;
  background: white;
  outline: none;
  box-shadow: 0 0 0 4px rgba(231, 84, 128, 0.1);
}

/* Clases de utilidad para el ancho */
.full-width {
  grid-column: 1 / -1;
}

/* --- SECCIÓN STAFF (PELUQUEROS) --- */
.staff-wrapper {
  background: #fdf2f5;
  padding: 20px;
  border-radius: 12px;
  border: 1px solid #f9dbe5;
  margin-top: 10px;
}

.form-grid-inner {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
}

/* --- SECCIÓN ETIQUETAS DENTRO DEL FORM --- */
.etiquetas-edit-section {
  background: #f8fafc;
  padding: 15px;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
}

.tags-wrapper-edit {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.current-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  min-height: 30px;
}

.tag-pill-edit {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px 14px;
  border-radius: 20px;
  color: white;
  font-size: 0.85rem;
  font-weight: 700;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.btn-remove-tag {
  background: rgba(0,0,0,0.2);
  border: none;
  color: white;
  border-radius: 50%;
  width: 18px;
  height: 18px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  transition: 0.2s;
}

.btn-remove-tag:hover {
  background: rgba(0,0,0,0.5);
}

/* --- BOTONES DE ACCIÓN --- */
.form-actions {
  grid-column: 1 / -1;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid #f1f5f9;
}

.btn-save {
  background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
  color: white;
  border: none;
  padding: 14px 30px;
  border-radius: 12px;
  font-weight: 700;
  cursor: pointer;
  transition: 0.3s;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-save:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(46, 204, 113, 0.3);
}

.btn-cancel {
  background: #f1f5f9;
  color: #64748b;
  border: none;
  padding: 14px 30px;
  border-radius: 12px;
  font-weight: 700;
  cursor: pointer;
}

/* Transiciones de Vue */
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.slide-enter-active, .slide-leave-active { transition: all 0.4s ease; }
.slide-enter-from, .slide-leave-to { opacity: 0; transform: translateY(-30px); }

/* --- RESPONSIVE --- */

/* Tablets (iPad) y pantallas medianas */
@media (max-width: 1100px) {
  .section-header {
    flex-direction: column;
    align-items: stretch;
    gap: 15px;
  }

  .header-actions {
    flex-wrap: wrap;
  }

  .filters {
    flex: 1;
    flex-wrap: wrap;
  }

  .search-box {
    flex: 1;
    min-width: 200px;
  }

  .search-box input {
    width: 100%;
    box-sizing: border-box;
  }

  .status-select {
    flex: 1;
    min-width: 150px;
  }

  .btn-nuevo {
    justify-content: center;
  }
}

/* Móviles y tablets estrechas: tabla → tarjetas apiladas */
@media (max-width: 768px) {
  .title-container h1 { font-size: 1.4rem; }

  .header-actions {
    flex-direction: column;
    align-items: stretch;
  }

  .btn-nuevo { width: 100%; }

  .custom-table thead { display: none; }

  .custom-table,
  .custom-table tbody,
  .custom-table tr,
  .custom-table td {
    display: block;
    width: 100%;
    box-sizing: border-box;
  }

  .custom-table tr {
    padding: 12px 16px;
    border-bottom: 1px solid #eee;
  }

  .custom-table td {
    padding: 8px 0;
    border: none;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
  }

  .custom-table td::before {
    content: attr(data-label);
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 700;
    color: #94a3b8;
    flex-shrink: 0;
  }

  .id-cell {
    background: none;
    padding: 8px 0;
  }

  .contact-cell,
  .tags-container-table {
    justify-content: flex-end;
    text-align: right;
  }

  .contact-cell { align-items: flex-end; }

  .actions.text-right { justify-content: space-between; }

  .pagination {
    flex-wrap: wrap;
    gap: 10px;
  }
}

/* Móviles estrechos */
@media (max-width: 600px) {
  .form-grid, .form-grid-inner {
    grid-template-columns: 1fr;
  }

  .form-card { padding: 20px; }

  .form-actions {
    flex-direction: column-reverse;
  }

  .btn-save, .btn-cancel {
    width: 100%;
    justify-content: center;
  }

  .email-text { word-break: break-all; }
}

.id-cell {
  font-family: 'Monaco', 'Consolas', monospace; /* Fuente tipo código */
  color: #94a3b8; /* Gris suave para que no distraiga */
  font-size: 0.85rem;
  font-weight: 600;
  background: #f8fafc; /* Un fondo muy sutil */
  padding: 2px 6px;
  border-radius: 4px;
}

/* 2. Contenedor de Contacto (Email + Teléfono) */
.contact-cell {
  display: flex;
  flex-direction: column;
  gap: 4px; /* Espacio entre email y teléfono */
}

/* Estilo para el Email */
.email-text {
  font-size: 0.9rem;
  color: #1e293b; /* Azul muy oscuro para legibilidad máxima */
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 8px;
}

.email-text i {
  color: #e75480; /* El rosa de tu marca para el icono */
  font-size: 0.85rem;
  width: 16px; /* Ancho fijo para que los textos se alineen */
}

/* 3. Estilo para el Número de Teléfono */
.phone-text {
  font-size: 0.8rem;
  color: #64748b; /* Gris intermedio */
  display: flex;
  align-items: center;
  gap: 8px;
}

.phone-text i {
  color: #94a3b8; /* Icono más discreto que el del email */
  font-size: 0.8rem;
  width: 16px;
}

/* Efecto Hover para la fila (opcional) */
tr:hover .email-text {
  color: #e75480; /* El email se ilumina al pasar el ratón */
  transition: color 0.2s ease;
}
</style>