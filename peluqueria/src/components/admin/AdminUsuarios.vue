<template>
  <div class="servicios-view">
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
            <input 
              type="text" 
              v-model="search" 
              placeholder="Buscar..." 
              @input="currentPage = 1"
            >
          </div>
          <select v-model="rolFilter" class="status-select" @change="currentPage = 1">
            <option value="all">Todos</option>
            <option value="usuario">Clientes</option>
            <option value="admin">Admins</option>
            <option value="empleado">Staff</option>
          </select>
        </div>
        
        <button @click="abrirNuevoUsuario" class="btn-nuevo">
          <i class="fas fa-user-plus"></i> <span class="btn-text">Nuevo</span>
        </button>
      </div>
    </header>

    <transition name="slide">
      <div v-if="mostrarForm" class="form-card">
        <div class="form-header-inner">
          <i class="fas" :class="form.id ? 'fa-user-edit' : 'fa-user-plus'"></i>
          <h3>{{ form.id ? 'Editar Usuario' : 'Nuevo Registro Local' }}</h3>
        </div>
        
        <div class="form-grid">
          <div class="form-section-title">Información Personal</div>
          <div class="input-group">
            <label>Nombre Real</label>
            <input v-model="form.nombre" placeholder="Nombre completo">
          </div>
          <div class="input-group">
            <label>Email</label>
            <input type="email" v-model="form.email" placeholder="correo@ejemplo.com">
          </div>
          <div class="input-group">
            <label>Teléfono</label>
            <input v-model="form.telefono" placeholder="600000000">
          </div>
          <div class="input-group">
            <label>Fecha Nacimiento</label>
            <input type="date" v-model="form.fecha_nacimiento">
          </div>

          <div class="form-section-title">Rol y Permisos</div>
          <div class="input-group full-width">
            <label>Tipo de Usuario</label>
            <select v-model="form.rol" class="role-select">
              <option value="usuario">Cliente</option>
              <option value="admin">Administrador</option>
              <option value="empleado">Empleado</option>
            </select>
          </div>

          <div v-if="form.rol === 'admin' || form.rol === 'empleado'" class="staff-extra-fields">
            <div class="input-group">
              <label>Especialidad</label>
              <input v-model="form.especialidad" placeholder="Ej: Experto en barbas">
            </div>
            <div class="input-group">
              <label>Avatar / Foto Perfil</label>
              <div class="file-input-wrapper">
                <input type="file" @change="handleFileUpload" accept="image/*" id="avatar-file">
                <label for="avatar-file" class="file-custom">
                  <i class="fas fa-upload"></i> {{ selectedFile ? selectedFile.name : 'Imagen' }}
                </label>
              </div>
            </div>
          </div>
        </div>

        <div class="form-actions">
          <button @click="mostrarForm = false" class="btn-cancel">Cancelar</button>
          <button @click="guardarUsuario" class="btn-save">
            <i class="fas fa-check"></i> {{ form.id ? 'Actualizar' : 'Guardar' }}
          </button>
        </div>
      </div>
    </transition>

    <div class="table-card" v-if="!mostrarForm || form.id"> 
      <table class="custom-table">
        <thead>
          <tr>
            <th>CLIENTE</th>            
            <th class="hide-mobile">CONTACTO</th>
            <th class="hide-mobile">ROL</th>
            <th>ESTADO</th>
            <th class="text-right">ACCIONES</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="u in usuariosPaginados" :key="u.id" :class="{'usuario-desactivado': u.activo == 0}">
            <td data-label="CLIENTE">
              <div class="user-info">
                <div class="avatar-mini">
                  <img v-if="u.avatar" :src="`/img-icons/${u.avatar}`" alt="Avatar">
                  <img v-else 
                    :src="u.generado === 'web' ? '/img-icons/default-avatar-web.png' : '/img-icons/default-avatar-local.png'" 
                    class="default-icon"
                  >
                </div>
                <div>
                  <div class="font-bold text-dark">{{ u.nombre }}</div>
                  <div class="text-muted small">@{{ u.usuario }}</div>
                  <div class="mobile-only-info">
                    <span class="small-role">{{ u.rol }}</span>
                  </div>
                </div>
              </div>
            </td>
            
            <td class="hide-mobile" data-label="CONTACTO">
              <div class="contact-info">
                <span class="block small"><i class="far fa-envelope"></i> {{ u.email }}</span>
                <span class="block small"><i class="fas fa-phone-alt"></i> {{ u.telefono || '---' }}</span>
              </div>
            </td>

            <td class="hide-mobile" data-label="ROL">
              <span :class="['pill-badge', `pill-${u.rol}`]">
                {{ u.rol === 'admin' ? 'ADMIN' : u.rol === 'empleado' ? 'STAFF' : 'CLIENTE' }}
              </span>
            </td>

            <td data-label="ESTADO">
              <div :class="['toggle-status', { active: u.activo == 1 }]" @click="toggleActivo(u)">
                <div class="switch"></div>
                <span class="status-text">{{ u.activo == 1 ? 'Activo' : 'Off' }}</span>
              </div>
            </td>

            <td class="actions text-right">
              <button @click="editarUsuario(u)" class="btn-action-edit">
                <i class="fas fa-edit"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="pagination" v-if="totalPages > 1">
        <button :disabled="currentPage === 1" @click="currentPage--" class="btn-page">
          <i class="fas fa-chevron-left"></i>
        </button>
        <span class="page-info">{{ currentPage }} / {{ totalPages }}</span>
        <button :disabled="currentPage === totalPages" @click="currentPage++" class="btn-page">
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';

// Estados
const usuarios = ref([]);
const search = ref('');
const rolFilter = ref('all');
const mostrarForm = ref(false);
const currentPage = ref(1);
const itemsPerPage = 7;
const form = ref({ rol: 'usuario', activo: 1 });
const selectedFile = ref(null);

// Carga de datos
const cargarUsuarios = async () => {
  try {
    const res = await fetch('/backend/api/gestion_usuarios.php', { credentials: 'include' });
    usuarios.value = await res.json();
  } catch (e) { console.error("Error cargando usuarios:", e); }
};

// Lógica de Filtrado
const usuariosFiltrados = computed(() => {
  return usuarios.value.filter(u => {
    const matchesSearch = (u.nombre || '').toLowerCase().includes(search.value.toLowerCase()) || 
                          (u.email || '').toLowerCase().includes(search.value.toLowerCase()) ||
                          (u.usuario || '').toLowerCase().includes(search.value.toLowerCase());
    const matchesRol = rolFilter.value === 'all' || u.rol === rolFilter.value;
    return matchesSearch && matchesRol;
  });
});

// Lógica de Paginación
const totalPages = computed(() => Math.ceil(usuariosFiltrados.value.length / itemsPerPage));
const usuariosPaginados = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return usuariosFiltrados.value.slice(start, start + itemsPerPage);
});

// Acciones
const abrirNuevoUsuario = () => {
  form.value = { rol: 'usuario', activo: 1, nombre: '', email: '', usuario: '', telefono: '', fecha_nacimiento: '' };
  selectedFile.value = null;
  mostrarForm.value = true;
};

const editarUsuario = (u) => {
  form.value = { ...u, password: '' };
  mostrarForm.value = true;
};

const handleFileUpload = (e) => {
  selectedFile.value = e.target.files[0];
};

const toggleActivo = async (u) => {
    const nuevoEstado = u.activo == 1 ? 0 : 1;
    const formData = new FormData();
    // Enviamos solo los campos necesarios para el cambio de estado rápido
    formData.append('id', u.id);
    formData.append('activo', nuevoEstado);
    formData.append('usuario', u.usuario);
    formData.append('nombre', u.nombre);
    formData.append('email', u.email);
    formData.append('rol', u.rol);
    formData.append('telefono', u.telefono || '');
    formData.append('fecha_nacimiento', u.fecha_nacimiento || '');

    try {
        const res = await fetch('/backend/api/gestion_usuarios.php', {
            method: 'POST',
            credentials: 'include',
            body: formData
        });
        if (res.ok) u.activo = nuevoEstado;
    } catch (e) { console.error("Error al cambiar estado:", e); }
};

const guardarUsuario = async () => {
  const formData = new FormData();
  Object.keys(form.value).forEach(key => {
      if (form.value[key] !== null) formData.append(key, form.value[key]);
  });
  if (selectedFile.value) formData.append('avatar', selectedFile.value);

  try {
    const res = await fetch('/backend/api/gestion_usuarios.php', {
      method: 'POST',
      credentials: 'include',
      body: formData
    });
    if (res.ok) {
      mostrarForm.value = false;
      cargarUsuarios();
    }
  } catch (e) { console.error("Error al guardar:", e); }
};

onMounted(cargarUsuarios);
</script>
<style scoped>
/* --- RESPONSIVE BASICS --- */
.section-header { 
  display: flex; 
  flex-wrap: wrap; 
  justify-content: space-between; 
  align-items: center; 
  gap: 20px;
  margin-bottom: 30px; 
}

.header-actions { 
  display: flex; 
  flex-wrap: wrap; 
  gap: 10px; 
  width: 100%;
}

@media (min-width: 768px) {
  .header-actions { width: auto; }
}

.filters { display: flex; gap: 8px; flex: 1; }
.search-box { flex: 1; position: relative; }
.search-box input { width: 100%; padding: 10px 10px 10px 35px; border-radius: 8px; border: 1px solid #ddd; }
.search-box i { position: absolute; left: 12px; top: 12px; color: #999; }

/* --- FORMULARIO RESPONSIVE --- */
.form-grid { 
  display: grid; 
  grid-template-columns: 1fr; 
  gap: 15px; 
}

@media (min-width: 768px) {
  .form-grid { grid-template-columns: 1fr 1fr; }
  .full-width { grid-column: span 2; }
}

.staff-extra-fields {
  grid-column: 1 / -1;
  display: grid;
  grid-template-columns: 1fr;
  gap: 15px;
}

@media (min-width: 768px) {
  .staff-extra-fields { grid-template-columns: 1fr 1fr; }
}

/* --- TABLA RESPONSIVE (MAGIA CSS) --- */
@media (max-width: 768px) {
  .hide-mobile { display: none; }
  
  .btn-text { display: none; } /* Solo icono en móvil para ahorrar espacio */
  
  .custom-table thead { display: none; } /* Escondemos cabecera */
  
  .custom-table tr { 
    display: block; 
    border: 1px solid #eee; 
    margin-bottom: 10px; 
    border-radius: 12px;
    padding: 10px;
    background: #fff;
  }
  
  .custom-table td { 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
    border: none; 
    padding: 8px 5px;
    text-align: right;
  }

  .custom-table td::before {
    content: attr(data-label);
    font-weight: 800;
    font-size: 0.7rem;
    color: #999;
    text-transform: uppercase;
  }

  .user-info { text-align: left; }
  .status-text { display: none; } /* Solo el switch en móvil */
}

/* --- ESTILOS VISUALES MEJORADOS --- */
.table-card { background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
.custom-table { width: 100%; border-collapse: collapse; }
.custom-table th { padding: 15px; text-align: left; color: #888; font-size: 0.75rem; background: #fafafa; }
.custom-table td { padding: 15px; border-bottom: 1px solid #f0f0f0; }

.avatar-mini { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; border: 2px solid #e75480; }
.avatar-mini img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }

.pill-badge { padding: 4px 10px; border-radius: 20px; font-size: 0.65rem; font-weight: 700; }
.pill-admin { background: #fff1f2; color: #e11d48; }
.pill-empleado { background: #eff6ff; color: #2563eb; }
.pill-usuario { background: #f8fafc; color: #64748b; }

.btn-nuevo { 
  background: #e75480; 
  color: white; 
  border: none; 
  padding: 10px 18px; 
  border-radius: 8px; 
  font-weight: 600; 
  display: flex; 
  align-items: center; 
  gap: 8px; 
}

.btn-action-edit { 
  background: #f1f5f9; 
  color: #64748b; 
  border: none; 
  width: 35px; 
  height: 35px; 
  border-radius: 8px; 
  transition: 0.3s;
}
.btn-action-edit:hover { background: #e75480; color: white; }

.toggle-status { display: flex; align-items: center; gap: 8px; }
.switch { width: 34px; height: 18px; background: #cbd5e0; border-radius: 10px; position: relative; }
.active .switch { background: #48bb78; }
.switch::after { content: ''; position: absolute; width: 14px; height: 14px; background: white; border-radius: 50%; top: 2px; left: 2px; transition: 0.3s; }
.active .switch::after { left: 18px; }

.mobile-only-info { margin-top: 4px; display: none; }
@media (max-width: 768px) { .mobile-only-info { display: block; } }
.small-role { font-size: 0.6rem; background: #eee; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; }
</style>