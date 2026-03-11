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
            <input type="text" v-model="form.nombre" placeholder="Ej: Juan Pérez">
          </div>
          <div class="input-group">
            <label>Correo Electrónico</label>
            <input type="email" v-model="form.email" placeholder="correo@ejemplo.com">
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
            <button 
              @click="guardarUsuario" 
              class="btn-save" 
              :disabled="formularioInvalido"
            >
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
            <th>Estado</th>
            <th class="text-right">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="u in usuariosPaginados" :key="u.id" :class="{ 'servicio-desactivado': u.activo == 0 }">
            <td data-label="ID">#{{ u.id }}</td>
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
const search = ref('');
const rolFilter = ref('all');
const mostrarForm = ref(false);
const currentPage = ref(1);
const itemsPerPage = 8;
const form = ref({ rol: 'usuario', activo: 1 });
const selectedFile = ref(null);

const cargarUsuarios = async () => {
  const res = await fetch('/backend/api/gestion_usuarios.php', { credentials: 'include' });
  usuarios.value = await res.json();
};

// Validación: devuelve true si el formulario está INCOMPLETO
const formularioInvalido = computed(() => {
  return !form.value.nombre || 
         !form.value.email || 
         !form.value.telefono || 
         !form.value.fecha_nacimiento || 
         !form.value.rol;
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
  form.value = { rol: 'usuario', activo: 1, nombre: '', email: '', telefono: '', especialidad: '' };
  selectedFile.value = null;
  mostrarForm.value = true;
};

const editarUsuario = (u) => {
  form.value = { ...u };
  mostrarForm.value = true;
};

const handleFileUpload = (e) => { selectedFile.value = e.target.files[0]; };

const toggleActivo = async (u) => {
    const nuevoEstado = u.activo == 1 ? 0 : 1;
    const formData = new FormData();
    formData.append('id', u.id);
    formData.append('activo', nuevoEstado);
    // Necesitamos enviar los campos mínimos requeridos por tu API
    formData.append('nombre', u.nombre);
    formData.append('email', u.email);
    formData.append('rol', u.rol);
    formData.append('telefono', u.telefono);


    try {
        const res = await fetch('/backend/api/gestion_usuarios.php', {
            method: 'POST',
            credentials: 'include',
            body: formData
        });
        if (res.ok) u.activo = nuevoEstado;
    } catch (e) { console.error(e); }
};

const guardarUsuario = async () => {
  const formData = new FormData();
  Object.keys(form.value).forEach(key => {
      if (form.value[key] !== null) formData.append(key, form.value[key]);
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

onMounted(cargarUsuarios);
</script>

<style scoped>
/* COPIAMOS EXACTAMENTE EL ESTILO DE SERVICIOS PARA MANTENER COHERENCIA */
.section-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 30px;
  padding-bottom: 10px;
  border-bottom: 1px solid #f0f0f0;
}

.header-left .breadcrumb {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.8rem;
  color: #999;
  margin-bottom: 8px;
  text-transform: uppercase;
}

.breadcrumb .current { color: #e75480; font-weight: 600; }

.title-container { display: flex; align-items: center; gap: 15px; }
.title-container h1 { margin: 0; font-size: 1.8rem; color: #2c3e50; font-weight: 800; }

.badge-count {
  background: #fdf2f5;
  color: #e75480;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  border: 1px solid #f9dbe5;
}

.btn-nuevo {
  background: linear-gradient(135deg, #e75480 0%, #c13660 100%);
  color: white; border: none; padding: 12px 24px; border-radius: 10px;
  font-weight: 600; display: flex; align-items: center; gap: 10px;
  box-shadow: 0 4px 15px rgba(231, 84, 128, 0.3); cursor: pointer;
}

.header-actions { display: flex; gap: 15px; align-items: center; }
.filters { display: flex; gap: 10px; }
.search-box { position: relative; }
.search-box input { padding: 10px 10px 10px 35px; border: 1px solid #ddd; border-radius: 8px; width: 250px; }
.search-box i { position: absolute; left: 12px; top: 12px; color: #999; }
.status-select { padding: 10px; border-radius: 8px; border: 1px solid #ddd; background: white; }

/* TABLA */
.table-card { background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; }
.custom-table { width: 100%; border-collapse: collapse; }
.custom-table th { background: #f8f9fa; padding: 15px; text-align: left; color: #888; font-size: 0.75rem; text-transform: uppercase; }
.custom-table td { padding: 15px; border-bottom: 1px solid #eee; vertical-align: middle; }

/* CELDAS ESPECIALES */
.service-info-cell { display: flex; align-items: center; gap: 12px; }
.icon-wrapper { padding: 4px; border-radius: 50%; }
.circular { border-radius: 50%; width: 40px !important; height: 40px !important; object-fit: cover; }
.text-wrapper { display: flex; flex-direction: column; }
.service-name { font-weight: 700; color: #2c3e50; }
.service-desc { font-size: 0.75rem; color: #94a3b8; }

.contact-cell { display: flex; flex-direction: column; gap: 4px; }
.email-text { font-size: 0.85rem; color: #4a5568; }
.phone-text { font-size: 0.8rem; color: #e75480; font-weight: 600; }

/* PILLS */
.pill-badge { padding: 4px 10px; border-radius: 20px; font-size: 0.65rem; font-weight: 700; }
.pill-admin { background: #fff1f2; color: #e11d48; }
.pill-empleado { background: #eff6ff; color: #2563eb; }
.pill-usuario { background: #f8fafc; color: #64748b; }

/* SWITCH */
.switch { position: relative; display: inline-block; width: 40px; height: 20px; }
.switch input { opacity: 0; width: 0; height: 0; }
.slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 20px; }
.slider:before { position: absolute; content: ""; height: 14px; width: 14px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
input:checked + .slider { background-color: #28a745; }
input:checked + .slider:before { transform: translateX(20px); }

/* FORMULARIO */
.form-card {
  background: white; padding: 30px; border-radius: 16px; margin-bottom: 30px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08); border: 1px solid #f0f0f0;
  animation: slideDown 0.4s ease-out;
}
@keyframes slideDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }

.form-card h3 { margin-top: 0; margin-bottom: 25px; color: #2c3e50; border-left: 4px solid #e75480; padding-left: 15px; }

.form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
.input-group { display: flex; flex-direction: column; gap: 8px; }
.input-group label { font-size: 0.85rem; font-weight: 600; color: #666; }
.input-group input, .input-group select { padding: 12px; border: 1px solid #e0e0e0; border-radius: 10px; }
.full-width { grid-column: 1 / -1; }

.file-custom {
  display: block; padding: 12px; border: 1px dashed #e75480; color: #e75480;
  text-align: center; border-radius: 10px; cursor: pointer; background: #fff5f8;
}
#avatar-file { display: none; }

.form-actions { grid-column: 1 / -1; display: flex; justify-content: flex-end; gap: 12px; margin-top: 10px; }
.btn-save { background: #2ecc71; color: white; border: none; padding: 12px 30px; border-radius: 10px; font-weight: 600; cursor: pointer; }
.btn-cancel { background: #f1f2f6; color: #57606f; border: none; padding: 12px 30px; border-radius: 10px; font-weight: 600; cursor: pointer; }

/* RESPONSIVE TABLE (IPHONE/MÓVIL) */
@media (max-width: 768px) {
  .section-header { flex-direction: column; align-items: flex-start; }
  .header-actions { width: 100%; flex-direction: column; }
  .filters { flex-direction: column; width: 100%; }
  .search-box input { width: 100%; }
  
  .custom-table thead { display: none; }
  .custom-table tr { display: block; border: 1px solid #eee; border-radius: 12px; margin-bottom: 15px; padding: 10px; }
  .custom-table td { display: flex; justify-content: space-between; align-items: center; text-align: right; padding: 10px 5px; border-bottom: 1px solid #f9f9f9; }
  .custom-table td::before { content: attr(data-label); font-weight: bold; color: #999; font-size: 0.7rem; text-transform: uppercase; }
  .service-info-cell { justify-content: flex-end; }
}

.pagination { padding: 20px; display: flex; justify-content: center; align-items: center; gap: 20px; }
.btn-page { padding: 8px 16px; border-radius: 8px; border: 1px solid #ddd; background: white; cursor: pointer; }
.btn-page:disabled { opacity: 0.4; }

.btn-save:disabled {
  background: #ccc;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
  opacity: 0.7;
}

/* Opcional: un efecto visual de "bloqueado" */
.btn-disabled {
  filter: grayscale(1);
}
</style>