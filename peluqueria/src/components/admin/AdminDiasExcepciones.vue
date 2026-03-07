<template>
  <div class="servicios-view">
    <header class="section-header">
      <div class="header-left">
        <div class="breadcrumb">
          <span>Panel de Control</span> 
          <i class="fas fa-chevron-right"></i> 
          <span class="current">Días Excepcionales</span>
        </div>
        <div class="title-container">
          <h1>Días de Cierre y Festivos</h1>
          <span class="badge-count">{{ excepcionesFiltradas.length }} fechas encontradas</span>
        </div>
      </div>

      <div class="header-actions">
        <div class="filters">
          <div class="search-box">
            <i class="fas fa-search"></i>
            <input 
              type="text" 
              v-model="search" 
              placeholder="Buscar fecha o motivo..." 
              @input="currentPage = 1"
            >
          </div>
          <select v-model="statusFilter" class="status-select" @change="currentPage = 1">
            <option value="all">Todos los cierres</option>
            <option value="0">Día Completo</option>
            <option value="1">Tramo Parcial</option>
          </select>
        </div>
        
        <button @click="abrirFormulario()" class="btn-nuevo">
          <i class="fas fa-plus-circle"></i> Añadir Excepción
        </button>
      </div>
    </header>

    <transition name="slide">
      <div v-if="mostrarForm" class="form-card">
        <h3>{{ excepcionEdit.id ? 'Editar Excepción' : 'Nueva Excepción' }}</h3>
        <div class="form-grid">
          
          <div class="input-group">
            <label>Fecha del cierre</label>
            <input type="date" v-model="excepcionEdit.fecha">
          </div>

          <div class="input-group">
            <label>Motivo / Descripción</label>
            <input type="text" v-model="excepcionEdit.descripcion" placeholder="Ej: Festivo local o Día libre">
          </div>

          <div class="input-group">
            <label>Tipo de cierre</label>
            <select v-model="excepcionEdit.solo_tramo" class="status-select-form">
              <option :value="0">Cerrar Día Completo</option>
              <option :value="1">Cerrar Solo un Tramo</option>
            </select>
          </div>

          <div class="form-grid tramo-container" v-if="excepcionEdit.solo_tramo == 1">
            <div class="input-group">
              <label>Hora Inicio</label>
              <input type="time" v-model="excepcionEdit.h_inicio">
            </div>
            <div class="input-group">
              <label>Hora Fin</label>
              <input type="time" v-model="excepcionEdit.h_fin">
            </div>
          </div>

          <div class="form-actions">
            <button @click="guardarExcepcion" class="btn-save">Guardar Cambios</button>
            <button @click="mostrarForm = false" class="btn-cancel">Cancelar</button>
          </div>
        </div>
        <div v-if="errorConflictos" class="alert-danger">
          <i class="fas fa-exclamation-triangle"></i>{{ errorConflictos }}</div>
      </div>
    </transition>

    <div class="table-card">
      <table class="custom-table">
        <thead>
          <tr>
            <th>FECHA / DÍA</th>
            <th>MOTIVO / DESCRIPCIÓN</th>
            <th>TIPO DE CIERRE</th>
            <th>FRANJA HORARIA</th>
            <th class="text-right">ACCIONES</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="e in excepcionesPaginadas" :key="e.id">
            <td data-label="FECHA">
              <div class="user-info">
                <div class="avatar-mini"><i class="fas fa-calendar-day"></i></div>
                <div>
                  <div class="font-bold text-dark">{{ formatearFecha(e.fecha) }}</div>
                  <div class="date-sub">{{ obtenerDiaSemana(e.fecha) }}</div>
                </div>
              </div>
            </td>
            <td data-label="MOTIVO">
              <span class="reason-text">{{ e.descripcion || 'Día libre' }}</span>
            </td>
            <td data-label="TIPO">
              <span :class="['pill-badge', e.solo_tramo == 1 ? 'pill-tramo' : 'pill-completo']">
                {{ e.solo_tramo == 1 ? 'TRAMO PARCIAL' : 'DÍA COMPLETO' }}
              </span>
            </td>
            <td data-label="HORARIO">
              <div class="duration-text">
                <i :class="e.solo_tramo == 1 ? 'far fa-clock' : 'fas fa-ban'"></i>
                {{ e.solo_tramo == 1 ? `${e.h_inicio.substring(0,5)} - ${e.h_fin.substring(0,5)}` : 'Todo el día' }}
              </div>
            </td>
            <td data-label="Acciones" class="actions">
                
                    
                    <div class="action-buttons">       
                      <button @click="eliminarExcepcion(e)" class="btn-action cancel" title="Anular Cita">
                        <i class="fas fa-times-circle"></i>
                      </button>
                    </div>

                
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

const excepciones = ref([]);
const search = ref('');
const statusFilter = ref('all');
const mostrarForm = ref(false);
const excepcionEdit = ref({ fecha: '', descripcion: '', solo_tramo: 0, h_inicio: '15:00', h_fin: '20:00', cerrado: 1 });
const currentPage = ref(1);
const itemsPerPage = 8;

const cargarExcepciones = async () => {
  const res = await fetch('/backend/api/gestion_excepciones.php', { credentials: 'include' });
  excepciones.value = await res.json();
};

// LÓGICA DE FILTRADO (Buscador + Tipo de Cierre)
const excepcionesFiltradas = computed(() => {
  return excepciones.value.filter(e => {
    const matchesSearch = e.fecha.includes(search.value) || 
                          (e.descripcion && e.descripcion.toLowerCase().includes(search.value.toLowerCase()));
    const matchesStatus = statusFilter.value === 'all' || parseInt(e.solo_tramo) === parseInt(statusFilter.value);
    return matchesSearch && matchesStatus;
  });
});

const totalPages = computed(() => Math.ceil(excepcionesFiltradas.value.length / itemsPerPage));

const excepcionesPaginadas = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return excepcionesFiltradas.value.slice(start, start + itemsPerPage);
});

const abrirFormulario = (excepcion = null) => {
  if (excepcion) {
    excepcionEdit.value = { ...excepcion };
  } else {
    excepcionEdit.value = { fecha: '', descripcion: '', solo_tramo: 0, h_inicio: '15:00', h_fin: '20:00', cerrado: 1 };
  }
  mostrarForm.value = true;
};

const guardarExcepcion = async () => {
  if (!excepcionEdit.value.fecha) {
    alert("Por favor, selecciona una fecha.");
    return;
  }

  // 1. Verificar si hay citas para esa fecha/tramo antes de guardar
  try {
    const checkRes = await fetch(`/backend/api/verificar_conflictos_cierre.php`, {
      method: 'POST',
      credentials: 'include',
      body: JSON.stringify({
        fecha: excepcionEdit.value.fecha,
        solo_tramo: excepcionEdit.value.solo_tramo,
        h_inicio: excepcionEdit.value.h_inicio,
        h_fin: excepcionEdit.value.h_fin
      })
    });
    
    const checkData = await checkRes.json();

    if (checkData.has_appointments) {
      // INFORMAR AL USUARIO: No se puede cerrar porque hay trabajo
      alert(`⚠️ No se puede aplicar el cierre: Hay ${checkData.count} cita(s) programada(s) para esa fecha/horario. Por favor, cancela o mueve las citas primero.`);
      return; // Detenemos la ejecución
    }

    // 2. Si no hay conflictos, procedemos a guardar
    const res = await fetch('/backend/api/gestion_excepciones.php', {
      method: 'POST',
      credentials: 'include',
      body: JSON.stringify(excepcionEdit.value)
    });
    
    const data = await res.json();
    if (data.success) {
      mostrarForm.value = false;
      cargarExcepciones();
    }
  } catch (error) {
    console.error("Error al validar conflictos:", error);
    alert("Hubo un error al verificar la disponibilidad del día.");
  }
};

const eliminarExcepcion = async (e) => {
  if (!confirm(`¿Eliminar la excepción del ${e.fecha}?`)) return;
  await fetch(`/backend/api/gestion_excepciones.php?id=${e.id}`, { method: 'DELETE', credentials: 'include' });
  cargarExcepciones();
};

const formatearFecha = (f) => new Date(f).toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' });
const obtenerDiaSemana = (f) => new Date(f).toLocaleDateString('es-ES', { weekday: 'long' });

onMounted(cargarExcepciones);
</script>

<style scoped>
/* REUTILIZAMOS TUS ESTILOS DE SERVICIOS */

/* Importamos los estilos exactos que me pasaste */
.section-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; padding-bottom: 10px; border-bottom: 1px solid #f0f0f0; }
.header-left .breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 0.8rem; color: #999; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 1px; }
.breadcrumb .current { color: #e75480; font-weight: 600; }
.title-container { display: flex; align-items: center; gap: 15px; }
.title-container h1 { margin: 0; font-size: 1.8rem; color: #2c3e50; font-weight: 800; }
.badge-count { background: #fdf2f5; color: #e75480; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; border: 1px solid #f9dbe5; }
.btn-nuevo { background: linear-gradient(135deg, #e75480 0%, #c13660 100%); color: white; border: none; padding: 12px 24px; border-radius: 10px; font-weight: 600; display: flex; align-items: center; gap: 10px; box-shadow: 0 4px 15px rgba(231, 84, 128, 0.3); cursor: pointer; }

/* Estilos de Tabla */
.table-card { background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; }
.custom-table { width: 100%; border-collapse: collapse; }
.custom-table th { background: #f8f9fa; padding: 15px; text-align: left; color: #888; font-size: 0.8rem; text-transform: uppercase; }
.custom-table td { padding: 15px; border-bottom: 1px solid #eee; }

/* Estilos de Formulario */
.form-card { background: white; padding: 30px; border-radius: 16px; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08); border: 1px solid #f0f0f0; }
.form-grid { display: grid; grid-template-columns: 2fr 2fr 1.5fr; gap: 20px; }
.input-group { display: flex; flex-direction: column; gap: 8px; }
.label-mini { font-size: 0.75rem; font-weight: 700; color: #888; text-transform: uppercase; }
.status-select-full { padding: 12px; border-radius: 10px; border: 1px solid #e0e0e0; background: #fdfdfd; }

/* Badges de estado específicos */
.pill-badge { padding: 4px 12px; border-radius: 6px; font-size: 0.7rem; font-weight: 800; }
.pill-tramo { background: #fffbeb; color: #d97706; }
.pill-completo { background: #fef2f2; color: #dc2626; }

/* Otros */
.user-info { display: flex; align-items: center; gap: 12px; }
.avatar-mini { width: 35px; height: 35px; background: #fdf2f5; color: #e75480; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
.date-sub { font-size: 0.75rem; color: #999; text-transform: capitalize; }
.btn-icon.delete { color: #dc2626; background: #fee2e2; border: none; padding: 8px; border-radius: 6px; cursor: pointer; }
.btn-icon.delete:hover { background: #fecaca; }

/* Paginación */
.pagination { padding: 15px; display: flex; justify-content: center; align-items: center; gap: 15px; border-top: 1px solid #eee; }
.btn-page { padding: 5px 12px; border-radius: 4px; border: 1px solid #ddd; background: white; cursor: pointer; }

/* COPIAMOS EXACTAMENTE EL ESTILO DE TU ADMINSERVICIOS */

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 30px;
  padding-bottom: 10px;
  border-bottom: 1px solid #f0f0f0;
}

/* FILTROS E INPUTS ESTILO SERVICIOS */
.search-box { position: relative; }
.search-box input {
  padding: 10px 15px 10px 35px;
  border: 1px solid #e0e0e0;
  border-radius: 10px;
  font-size: 0.9rem;
  width: 260px;
  transition: all 0.3s;
}
.search-box input:focus {
  border-color: #e75480;
  box-shadow: 0 0 0 4px rgba(231, 84, 128, 0.1);
  outline: none;
}
.search-box i { position: absolute; left: 12px; top: 12px; color: #94a3b8; }

.status-select {
  padding: 10px;
  border-radius: 10px;
  border: 1px solid #e0e0e0;
  background-color: white;
  color: #475569;
  font-size: 0.9rem;
  cursor: pointer;
}

/* FORMULARIO ESTILO FORM-CARD */
.form-card {
  background: white;
  padding: 30px;
  border-radius: 16px;
  margin-bottom: 30px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
  border: 1px solid #f0f0f0;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
}

.tramo-container {
  grid-column: span 3;
  background: #fcfcfc;
  padding: 15px;
  border-radius: 12px;
  border: 1px dashed #e0e0e0;
}

.input-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.input-group label {
  font-size: 0.75rem;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.input-group input, .status-select-form {
  padding: 12px 15px;
  border: 1px solid #e0e0e0;
  border-radius: 10px;
  font-size: 0.95rem;
  background: #fdfdfd;
  transition: all 0.3s;
}

.input-group input:focus {
  border-color: #e75480;
  background: white;
  box-shadow: 0 0 0 4px rgba(231, 84, 128, 0.1);
  outline: none;
}

.form-actions {
  grid-column: span 3;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 10px;
}

/* BOTÓN DE ELIMINAR CIRCULAR ROJO */
.btn-action-delete {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #ef4444;
  color: white;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: 0.2s;
}
.btn-action-delete:hover {
  transform: scale(1.1);
  background: #dc2626;
}

/* BADGES */
.pill-badge {
  padding: 5px 12px;
  border-radius: 6px;
  font-size: 0.7rem;
  font-weight: 800;
}
.pill-tramo { background: #fffbeb; color: #d97706; border: 1px solid #fef3c7; }
.pill-completo { background: #fef2f2; color: #dc2626; border: 1px solid #fee2e2; }

/* TRANSICIÓN */
.slide-enter-active, .slide-leave-active { transition: all 0.4s ease; }
.slide-enter-from, .slide-leave-to { opacity: 0; transform: translateY(-20px); }
.header-actions { display: flex; gap: 15px; align-items: center; }
.filters { display: flex; gap: 10px; }
.search-box { position: relative; }
.search-box input { padding: 8px 10px 8px 30px; border: 1px solid #ddd; border-radius: 6px; }
.search-box i { position: absolute; left: 10px; top: 10px; color: #999; }

/* Botones de acción */
.action-buttons {
  display: flex;
  gap: 10px;
  justify-content: flex-start;
}

.btn-action {
  background: none;
  border: none;
  font-size: 1.2rem;
  cursor: pointer;
  transition: transform 0.2s;
}

.btn-action.check { color: #2ecc71; }
.btn-action.cancel { color: #e74c3c; }
.btn-action:hover { transform: scale(1.2); }
.alert-danger {
  background: #fef2f2;
  color: #dc2626;
  padding: 12px;
  border-radius: 8px;
  border: 1px solid #fee2e2;
  margin-bottom: 20px;
  font-size: 0.85rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 10px;
}

td[data-label="ID"][data-v-750ce601], td[data-label="Precio"][data-v-750ce601], td[data-label="Duración"][data-v-750ce601] {
    font-weight: 600;
    color: rgb(74, 85, 104);
}
</style>