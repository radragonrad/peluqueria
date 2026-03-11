<template>
  <div class="servicios-view">
    <header class="section-header">
    <div class="header-left">
        <div class="breadcrumb">
        <span>Panel de Control</span> 
        <i class="fas fa-chevron-right"></i> 
        <span class="current">Servicios</span>
        </div>
        <div class="title-container">
        <h1>Catálogo de Servicios</h1>
        <span class="badge-count">{{ serviciosFiltrados.length }} servicios encontrados</span>
        </div>
    </div>

    <div class="header-actions">
        <div class="filters">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" v-model="search" placeholder="Buscar servicio..." @input="currentPage = 1">
        </div>
        <select v-model="statusFilter" class="status-select" @change="currentPage = 1">
            <option value="all">Todos los estados</option>
            <option value="1">Activos</option>
            <option value="0">Desactivados</option>
        </select>
        </div>
        
        <button @click="abrirFormulario()" class="btn-nuevo">
        <i class="fas fa-plus-circle"></i> Añadir Nuevo
        </button>
    </div>
    </header>

    <div v-if="mostrarForm" class="form-card">
      <h3>{{ servicioEdit.id ? 'Editar Servicio' : 'Nuevo Servicio' }}</h3>
      <div class="form-grid">
        <input type="text" v-model="servicioEdit.nombre" placeholder="Nombre del servicio">
        <input type="number" v-model="servicioEdit.precio" placeholder="Precio (€)">
        <input type="number" v-model="servicioEdit.duracion_min" placeholder="Duración (min)">
        <textarea v-model="servicioEdit.descripcion" placeholder="Descripción breve del servicio..." rows="2"></textarea>
        
        <div class="icon-selector-container">
          <label>Selecciona un Icono:</label>
          <div class="icon-grid">
            <div 
              v-for="icon in listaIconos" 
              :key="icon"
              class="icon-option"
              :class="{ selected: servicioEdit.icono === icon }"
              @click="seleccionarIcono(icon)"
            >
              <img :src="`/img-icons/${icon}`" :alt="icon">
              <span class="icon-name">{{ limpiarNombre(icon) }}</span>
            </div>
          </div>
        </div>

        <div class="form-actions">
          <button @click="guardarServicio" class="btn-save">Guardar</button>
          <button @click="mostrarForm = false" class="btn-cancel">Cancelar</button>
        </div>
      </div>
    </div>

    <div class="table-card">
      <table class="custom-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Servicio</th>
            <th>Precio</th>
            <th>Duración</th>            
            <th>Estado</th> 
            <th>Acciones</th>
          </tr>
        </thead>
    <tbody>
  <tr v-for="s in serviciosPaginados" :key="s.id" :class="{ 'servicio-desactivado': !parseInt(s.activo) }">
    <td data-label="ID">#{{ s.id }}</td>
    <td data-label="Servicio">
      <div class="user-info">
        <img :src="`/img-icons/${s.icono}`" class="table-icon" />
        <span class="service-name">{{ s.nombre }}</span>
        <span v-if="s.descripcion" class="service-desc">{{ s.descripcion.substring(0, 40) }}...</span>
      </div>
    </td>
    <td data-label="Precio">{{ s.precio }} €</td>
    <td data-label="Duración">{{ s.duracion_min }} min</td>
    <td data-label="Estado">
      <label class="switch">
        <input type="checkbox" :checked="parseInt(s.activo) === 1" @change="toggleEstado(s)">
        <span class="slider"></span>
      </label>
    </td>
    <td data-label="Acciones" class="actions">
      <button @click="abrirFormulario(s)" class="btn-icon edit"><i class="fas fa-edit"></i></button>
    </td>
  </tr>
</tbody>
      </table>

      <div class="pagination" v-if="totalPages > 1">
        <button :disabled="currentPage === 1" @click="currentPage--" class="btn-page">Anterior</button>
        <span>Página {{ currentPage }} de {{ totalPages }}</span>
        <button :disabled="currentPage === totalPages" @click="currentPage++" class="btn-page">Siguiente</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';

const servicios = ref([]);
const search = ref('');
const statusFilter = ref('all');
const mostrarForm = ref(false);
const servicioEdit = ref({ nombre: '', descripcion: '', icono: 'corte.svg', precio: '', duracion_min: '', activo: 1 });
const currentPage = ref(1);
const itemsPerPage = 8;

const cargarServicios = async () => {
  const res = await fetch('/backend/api/gestion_servicios.php', { credentials: 'include' });
  servicios.value = await res.json();
};

// LÓGICA DE FILTRADO (Buscador + Estado)
const serviciosFiltrados = computed(() => {
  return servicios.value.filter(s => {
    const matchesSearch = s.nombre.toLowerCase().includes(search.value.toLowerCase()) ||
                         (s.descripcion && s.descripcion.toLowerCase().includes(search.value.toLowerCase()));
    const matchesStatus = statusFilter.value === 'all' || parseInt(s.activo) === parseInt(statusFilter.value);
    return matchesSearch && matchesStatus;
  });
});

const totalPages = computed(() => Math.ceil(serviciosFiltrados.value.length / itemsPerPage));

const serviciosPaginados = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return serviciosFiltrados.value.slice(start, start + itemsPerPage);
});

const listaIconos = ref(['corte.svg', 'barba.svg', 'lavado.svg', 'secado.svg', 'tinte.svg', 'facial.svg', 'corte_clasico.svg', 'corte_barba.svg', 'arreglo_barba.svg', 'decoloracion.svg', 'mechas.svg', 'servicio_vip.svg']);

const seleccionarIcono = (nombreIcono) => { servicioEdit.value.icono = nombreIcono; };

const limpiarNombre = (n) => n.replace('.svg', '').charAt(0).toUpperCase() + n.slice(1).replace('.svg', '');

const abrirFormulario = (servicio = null) => {
  if (servicio) {
    servicioEdit.value = { ...servicio };
  } else {
    servicioEdit.value = { nombre: '', descripcion: '', precio: '', duracion_min: '', icono: 'corte.svg', activo: 1 };
  }
  mostrarForm.value = true;
};

const guardarServicio = async () => {
  await fetch('/backend/api/gestion_servicios.php', {
    method: 'POST',
    credentials: 'include',
    body: JSON.stringify(servicioEdit.value)
  });
  mostrarForm.value = false;
  cargarServicios();
};

const toggleEstado = async (servicio) => {
  const nuevoEstado = parseInt(servicio.activo) === 1 ? 0 : 1;
  try {
    const res = await fetch('/backend/api/gestion_servicios.php', {
      method: 'POST',
      credentials: 'include',
      body: JSON.stringify({ ...servicio, activo: nuevoEstado })
    });
    const data = await res.json();
    if (data.success) servicio.activo = nuevoEstado;
  } catch (e) { console.error(e); }
};

onMounted(cargarServicios);
</script>

<style scoped>
/* Layout y Filtros */
.section-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end; /* Alinea al fondo para un look más moderno */
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
  letter-spacing: 1px;
}

.breadcrumb i {
  font-size: 0.6rem;
}

.breadcrumb .current {
  color: #e75480;
  font-weight: 600;
}

.title-container {
  display: flex;
  align-items: center;
  gap: 15px;
}

.title-container h1 {
  margin: 0;
  font-size: 1.8rem;
  color: #2c3e50;
  font-weight: 800;
}

.badge-count {
  background: #fdf2f5;
  color: #e75480;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  border: 1px solid #f9dbe5;
}

/* Ajuste del botón para que parezca más premium */
.btn-nuevo {
  background: linear-gradient(135deg, #e75480 0%, #c13660 100%);
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 10px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 10px;
  box-shadow: 0 4px 15px rgba(231, 84, 128, 0.3);
  transition: transform 0.2s, box-shadow 0.2s;
}

.btn-nuevo:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(231, 84, 128, 0.4);
}
.header-actions { display: flex; gap: 15px; align-items: center; }
.filters { display: flex; gap: 10px; }
.search-box { position: relative; }
.search-box input { padding: 8px 10px 8px 30px; border: 1px solid #ddd; border-radius: 6px; }
.search-box i { position: absolute; left: 10px; top: 10px; color: #999; }
.status-select { padding: 8px; border-radius: 6px; border: 1px solid #ddd; }

/* Tabla e Iconos */
.table-card { background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; }
.custom-table { width: 100%; border-collapse: collapse; }
.custom-table th { background: #f8f9fa; padding: 15px; text-align: left; color: #888; font-size: 0.8rem; }
.custom-table td { padding: 15px; border-bottom: 1px solid #eee; }
.table-icon { width: 24px; height: 24px; margin-right: 10px; vertical-align: middle; }
.servicio-desactivado { opacity: 0.5; background: #f9f9f9; }

/* Paginación */
.pagination { padding: 15px; display: flex; justify-content: center; align-items: center; gap: 15px; border-top: 1px solid #eee; }
.btn-page { padding: 5px 12px; border-radius: 4px; border: 1px solid #ddd; background: white; cursor: pointer; }
.btn-page:disabled { opacity: 0.5; cursor: not-allowed; }

/* Formulario e Icon Selector */
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
.form-grid textarea { grid-column: span 2; padding: 10px; border: 1px solid #ddd; border-radius: 6px; }
.icon-selector-container { grid-column: span 2; }
.icon-grid {
  display: grid;
  /* Esto crea tantas columnas como quepan, mínimo 80px cada una */
  grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); 
  gap: 10px;
  margin-top: 10px;
}

.icon-option {
  width: 100%; /* Que ocupe el ancho de su celda de la rejilla */
  height: 80px; 
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  border: 1px solid #ddd;
  border-radius: 12px;
  padding: 5px;
}

.icon-option img { width: 30px; height: 30px; }
.icon-name { font-size: 0.7rem; color: #666; }
.icon-option.selected { border-color: #e75480; background: #fff5f8; }

/* Switch y Botones */
.switch { position: relative; display: inline-block; width: 40px; height: 20px; }
.switch input { opacity: 0; width: 0; height: 0; }
.slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 20px; }
.slider:before { position: absolute; content: ""; height: 14px; width: 14px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
input:checked + .slider { background-color: #28a745; }
input:checked + .slider:before { transform: translateX(20px); }
.btn-nuevo { background: #e75480; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: bold; cursor: pointer; }
.btn-save { background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; }
.btn-cancel { background: #6c757d; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; }

/* --- CONTENEDOR DEL FORMULARIO (TARJETA) --- */
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
  font-size: 1.4rem;
  border-left: 4px solid #e75480;
  padding-left: 15px;
}

/* --- GRID DEL FORMULARIO --- */
.form-grid {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr; /* Nombre ancho, precio y duración cortos */
  gap: 20px;
}

.form-grid input, 
.form-grid textarea {
  width: 100%;
  padding: 12px 15px;
  border: 1px solid #e0e0e0;
  border-radius: 10px;
  font-size: 0.95rem;
  transition: all 0.3s;
  background: #fdfdfd;
}

.form-grid input:focus, 
.form-grid textarea:focus {
  outline: none;
  border-color: #e75480;
  background: white;
  box-shadow: 0 0 0 4px rgba(231, 84, 128, 0.1);
}

/* Campos que ocupan todo el ancho */
.form-grid textarea,
.icon-selector-container,
.form-actions {
  grid-column: span 3;
}

/* --- SELECTOR DE ICONOS --- */
.icon-selector-container {
  margin-top: 10px;
  padding: 20px;
  background: #fcfcfc;
  border-radius: 12px;
  border: 1px dashed #ddd;
}

.icon-selector-container label {
  display: block;
  margin-bottom: 15px;
  font-weight: 600;
  color: #666;
  font-size: 0.9rem;
}

.icon-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
  gap: 12px;
}

.icon-option {
  height: 90px;
  border: 2px solid #fff;
  background: white;
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 2px 5px rgba(0,0,0,0.03);
}

.icon-option img {
  width: 32px;
  height: 32px;
  transition: transform 0.2s;
}

.icon-name {
  font-size: 0.7rem;
  font-weight: 500;
  color: #777;
}

.icon-option:hover {
  background: #fffafa;
  border-color: #f9dbe5;
}

.icon-option.selected {
  border-color: #e75480;
  background: #fff5f8;
  transform: scale(1.05);
}

.icon-option.selected .icon-name {
  color: #e75480;
  font-weight: 700;
}

/* --- BOTONES DE ACCIÓN --- */
.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 10px;
}

.btn-save {
  background: #2ecc71;
  color: white;
  border: none;
  padding: 12px 30px;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.3s;
}

.btn-save:hover { background: #27ae60; }

.btn-cancel {
  background: #f1f2f6;
  color: #57606f;
  border: none;
  padding: 12px 30px;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
}

.btn-cancel:hover { background: #dfe4ea; }

@media (max-width: 768px) {
  /* 1. Evitar que el botón de menú pise el título */
  .section-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 20px;
    padding-top: 60px; /* Espacio para el botón flotante del menú */
    position: relative;
  }

  .header-left {
    width: 100%;
  }

  .title-container {
    flex-wrap: wrap; /* Permite que el badge baje si no hay sitio */
    gap: 10px;
  }

  .title-container h1 {
    font-size: 1.5rem;
    width: 100%; /* El título ocupa su línea */
  }

  .badge-count {
    display: inline-block;
    margin-bottom: 10px;
  }

  /* 2. Ajuste de los filtros */
  .header-actions {
    width: 100%;
    flex-direction: column;
    gap: 12px;
  }

  .filters {
    flex-direction: column;
    width: 100%;
  }

  .search-box input, .status-select, .btn-nuevo {
    width: 100% !important;
    box-sizing: border-box;
  }

  /* 3. CORRECCIÓN DE LA TABLA (Basado en tu imagen 2) */
  .custom-table thead {
    display: none; /* Seguimos ocultando el header en móvil */
  }

  .custom-table tr {
    display: block;
    margin-bottom: 15px;
    padding: 15px;
    border: 1px solid #eee;
    border-radius: 12px;
    background: #fff;
  }

  .custom-table td {
    display: flex;
    justify-content: space-between; /* Label a la izquierda, Dato a la derecha */
    align-items: center;
    padding: 10px 0 !important;
    border-bottom: 1px solid #f8f9fa;
    text-align: right;
  }

  .custom-table td:last-child {
    border-bottom: none;
    justify-content: center; /* Botón de editar centrado abajo */
    padding-top: 15px !important;
  }

  /* El label (ID, PRECIO, etc) */
  .custom-table td::before {
    content: attr(data-label);
    font-weight: 700;
    color: #94a3b8;
    font-size: 0.75rem;
    text-transform: uppercase;
    text-align: left;
  }

  /* 4. ARREGLO DEL ICONO Y NOMBRE (user-info) */
  .user-info {
    display: flex;
    flex-direction: row; /* Icono y texto en línea */
    align-items: center;
    justify-content: flex-end; /* Todo hacia la derecha del label */
    gap: 10px;
    max-width: 65%; /* Para que no pise el label */
  }

  .table-icon {
    width: 24px;
    height: 24px;
    margin: 0; /* Quitamos márgenes antiguos */
  }

  .service-name {
    font-size: 0.9rem;
    text-align: right;
  }

  .service-desc {
    display: none; /* En móvil ocultamos la descripción larga para no ensuciar */
  }
}
/* Estilo para el precio */
.price-text {
  font-weight: 700;
  color: #2d3748;
  font-size: 0.85rem;
}

/* Estilo para la duración */
.duration-text {
  color: #718096;
  font-size: 0.8rem;
  display: flex;
  align-items: center;
  gap: 4px;
}

.duration-text i {
  font-size: 0.7rem;
  color: #cbd5e0;
}

/* Icono de bloqueo */
.lock-icon {
  color: #cbd5e0;
  font-size: 0.85rem;
}

/* --- AJUSTE PARA IPHONE (MÓVIL) --- */
@media (max-width: 768px) {
  /* Aseguramos que las nuevas filas tengan su label en el lateral */
  .custom-table td[data-label="PRECIO"],
  .custom-table td[data-label="DURACIÓN"] {
    justify-content: flex-end;
  }
}

/* NUEVOS ESTILOS PARA CORREGIR LA VISIBILIDAD */

.service-info-cell {
  display: flex;
  align-items: center;
  gap: 12px;
  min-width: 200px; /* Asegura que el nombre tenga espacio */
}

.icon-wrapper {
  background: #fdf2f5;
  padding: 8px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.table-icon {
  width: 28px;
  height: 28px;
  object-fit: contain;
}

.text-wrapper {
  display: flex;
  flex-direction: column;
  text-align: left;
}

.service-name {
  font-weight: 700;
  color: #2c3e50;
  font-size: 0.95rem;
  line-height: 1.2;
}

.service-desc {
  font-size: 0.75rem;
  color: #94a3b8;
  margin-top: 2px;
  padding-left: 1%;
}

/* Ajuste general de la tabla */
.custom-table td {
  padding: 12px 15px;
  vertical-align: middle;
}

.font-bold {
  font-weight: 700;
  color: #2c3e50;
}

/* Corrección para que el precio y duración se vean mejor */
td[data-label="ID"], 
td[data-label="Precio"], 
td[data-label="Duración"] {
  font-weight: 600;
  color: #4a5568;
}
</style>