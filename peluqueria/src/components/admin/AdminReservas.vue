<template>
  <div class="servicios-view">
    <header class="section-header">
      <div class="header-left">
        <div class="breadcrumb">
          <span>Panel de Control</span> 
          <i class="fas fa-chevron-right"></i> 
          <span class="current">Reservas</span>
        </div>
        <div class="title-container">
          <h1>Agenda de Citas</h1>
          <span class="badge-count">{{ reservasFiltradas.length }} citas encontradas</span>
        </div>
      </div>

      <div class="header-actions">
        <div class="filters">
          <div class="date-filter-box">
            <input type="date" v-model="dateFilter" class="status-select" @change="currentPage = 1">
            <button v-if="dateFilter" @click="dateFilter = ''; currentPage = 1" class="btn-clear-date">
              <i class="fas fa-times"></i>
            </button>
          </div>

          <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" v-model="search" placeholder="Buscar cliente..." @input="currentPage = 1">
          </div>
          
          <select v-model="statusFilter" class="status-select" @change="currentPage = 1">
            <option value="all">Todos los estados</option>
            <option value="PENDIENTE">Pendientes</option>            
            <option value="COMPLETADA">Completadas</option>
            <option value="ANULADA LOCAL">Canceladas</option>
          </select>
        </div>
      </div>
    </header>

    <div class="table-card">
      <table class="custom-table">
        <thead>
          <tr>
            <th>Fecha / Hora</th>
            <th>Cliente</th>
            <th>Servicio</th>
            <th>Precio</th>
            <th>Duración</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="r in reservasPaginadas" :key="r.id" :class="{ 'fila-proxima': obtenerClaseFinal(r) === 'proximo' && r.estado !== 'COMPLETADA' && r.estado !== 'cancelada','fila-completada': r.estado === 'COMPLETADA','fila-bloqueada': esFechaBloqueada(r.fecha)}">
            <td data-label="Fecha / Hora">
              <div class="date-display">
                <span class="font-bold">{{ formatearFecha(r.fecha) }}</span>
                <span class="text-muted"><i class="far fa-clock"></i> {{ r.hora.substring(0,5) }}</span>
              </div>
            </td>
            <td data-label="Cliente">
              <div class="user-info">
                <div class="avatar-mini">{{ r.cliente_nombre.charAt(0) }}</div>
                <div>
                  <div class="font-bold">{{ r.cliente_nombre }}</div>
                  <div class="text-muted small">{{ r.cliente_telefono }}</div>
                </div>
              </div>
            </td>
            <td data-label="Servicio">
              <div class="service-tag-display">
                <img :src="`/img-icons/${r.servicio_icono}`" class="table-icon" />
                <span>{{ r.servicio_nombre }}</span>
              </div>
            </td>
            <td data-label="Precio"> <span class="price-text">{{ r.precio }}€</span>
            </td>

            <td data-label="Duración"> <span class="duration-text"><i class="fas fa-hourglass-half"></i> {{ r.duracion }}'</span>
            </td>
            <td data-label="Estado">
                <span :class="['status-badge', obtenerClaseFinal(r)]">
                    {{ obtenerTextoFinal(r) }}
                </span>
            </td>
            <td data-label="Acciones" class="actions">
                <template v-if="!esFechaBloqueada(r.fecha)">
                    
                    <div v-if="r.estado !== 'completada' && r.estado !== 'cancelada'" class="action-buttons">
                    <button @click="completarCita(r)" class="btn-action check" title="Validar Cita">
                        <i class="fas fa-check-circle"></i>
                    </button>
                    <button @click="prepararAnulacion(r)" class="btn-action cancel" title="Anular Cita">
                        <i class="fas fa-times-circle"></i>
                    </button>
                    </div>

                    <div v-else class="action-buttons">
                    <button @click="revertirEstado(r)" class="btn-action undo" title="Restablecer">
                        <i class="fas fa-history"></i>
                        <span class="undo-text">Deshacer</span>
                    </button>
                    </div>

                </template>

                <template v-else>
                    <div class="action-buttons">
                    <i class="fas fa-lock" style="color: #cbd5e0; font-size: 0.9rem;" title="Historial bloqueado"></i>
                    </div>
                </template>
            </td>
          </tr>
          <tr v-if="reservasPaginadas.length === 0">
            <td colspan="5" class="no-results">No hay reservas que coincidan con los filtros.</td>
          </tr>
        </tbody>
      </table>

      <div class="pagination" v-if="totalPages > 1">
        <button :disabled="currentPage === 1" @click="currentPage--" class="btn-page">
          <i class="fas fa-chevron-left"></i> Anterior
        </button>
        <span class="page-info">Página {{ currentPage }} de {{ totalPages }}</span>
        <button :disabled="currentPage === totalPages" @click="currentPage++" class="btn-page">
          Siguiente <i class="fas fa-chevron-right"></i>
        </button>
      </div>
    </div>
  </div>
  <div v-if="mostrarModalCancel" class="modal-overlay">
    <div class="modal-content">
    <h3>Anular Cita</h3>
    <p>Cliente: <strong>{{ reservaSeleccionada?.cliente_nombre }}</strong></p>
    
    <div class="form-group-alt">
      <label>Motivo de la anulación:</label>
      <textarea v-model="motivoCancelacion" placeholder="Ej: El cliente no puede asistir..."></textarea>
    </div>
    
    <div class="modal-actions">
      <button @click="confirmarAnulacion" class="btn-confirm">Confirmar Anulación</button>
      <button @click="mostrarModalCancel = false" class="btn-close">Cerrar</button>
    </div>
  </div>
</div>

<div v-if="mostrarConfirmar" class="modal-overlay">
  <div class="modal-content confirm-modal">
    <div class="confirm-icon" :class="confirmConfig.tipo">
      <i :class="confirmConfig.icono"></i>
    </div>
    <h3>{{ confirmConfig.titulo }}</h3>
    <p>{{ confirmConfig.mensaje }}</p>
    
    <div class="modal-actions-horizontal">
      <button @click="ejecutarAccionConfirmada" class="btn-confirm-action">
        Confirmar
      </button>
      <button @click="mostrarConfirmar = false" class="btn-cancel-modal">
        Cancelar
      </button>
    </div>
  </div>
</div>

</template>

<script setup>
import { ref, onMounted, computed } from 'vue';

const reservas = ref([]);
const search = ref('');
const statusFilter = ref('all');
const currentPage = ref(1);
const itemsPerPage = 8;
const mostrarModalCancel = ref(false);
const reservaSeleccionada = ref(null);
const motivoCancelacion = ref('');
const dateFilter = ref('');

const cargarReservas = async () => {
  try {
    const res = await fetch('/backend/api/gestion_reservas.php', { credentials: 'include' });
    reservas.value = await res.json();
  } catch (e) {
    console.error("Error cargando reservas:", e);
  }
};

// Función para completar cita (Validar)
const mostrarConfirmar = ref(false);
const confirmConfig = ref({ titulo: '', mensaje: '', icono: '', tipo: '', accion: null });

// Nueva función para completar cita sin el confirm feo
const completarCita = (reserva) => {
  confirmConfig.value = {
    titulo: 'Completar Cita',
    mensaje: `¿Vas a marcar la cita de ${reserva.cliente_nombre} como finalizada?`,
    icono: 'fas fa-check-circle',
    tipo: 'success', // Para el color verde
    accion: async () => {
      await enviarEstado(reserva.id, 'COMPLETADA');
      reserva.estado = 'COMPLETADA';
    }
  };
  mostrarConfirmar.value = true;
};

// Función genérica para ejecutar lo que el usuario acepte
const ejecutarAccionConfirmada = async () => {
  if (confirmConfig.value.accion) {
    await confirmConfig.value.accion();
  }
  mostrarConfirmar.value = false;
};

// Abrir modal de anulación
const prepararAnulacion = (reserva) => {
  reservaSeleccionada.value = reserva;
  motivoCancelacion.value = '';
  mostrarModalCancel.value = true;
};

const revertirEstado = async (reserva) => {
  if (confirm(`¿Quieres volver a poner la cita de ${reserva.cliente_nombre} como pendiente?`)) {
    try {
      // Al revertir, también limpiamos el motivo de cancelación en la DB
      await enviarEstado(reserva.id, 'pendiente', null);
      
      // Actualizamos la vista localmente
      reserva.estado = 'pendiente';
      reserva.motivo_cancelacion = null;
    } catch (e) {
      console.error("Error al revertir:", e);
    }
  }
};

// Confirmar anulación desde el modal
const confirmarAnulacion = async () => {
  if (!motivoCancelacion.value.trim()) return alert("Por favor, indica un motivo");
  
  await enviarEstado(reservaSeleccionada.value.id, 'ANULADA LOCAL', motivoCancelacion.value);
  reservaSeleccionada.value.estado = 'ANULADA LOCAL';
  reservaSeleccionada.value.motivo_cancelacion = motivoCancelacion.value;
  
  mostrarModalCancel.value = false;
};

// Función auxiliar para el fetch
const enviarEstado = async (id, estado, motivo = null) => {
  await fetch('/backend/api/gestion_reservas.php', {
    method: 'POST',
    credentials: 'include',
    body: JSON.stringify({ id, estado, motivo })
  });
};

const reservasFiltradas = computed(() => {
  return reservas.value.filter(r => {
    // Filtro por texto (Nombre o Servicio)
    const matchesSearch = r.cliente_nombre.toLowerCase().includes(search.value.toLowerCase()) ||
                          r.servicio_nombre.toLowerCase().includes(search.value.toLowerCase());

    // Filtro por Estado
    const matchesStatus = statusFilter.value === 'all' || r.estado === statusFilter.value;

    // Filtro por Fecha (Nuevo)
    const matchesDate = !dateFilter.value || r.fecha === dateFilter.value;

    return matchesSearch && matchesStatus && matchesDate;
  });
});

// ESTADO
const calcularClaseTiempo = (fechaReserva, horaReserva, estadoReal) => {
  // 1. Si el estado ya es completada o cancelada, mandamos ese estado directamente
  if (estadoReal === 'completada' || estadoReal === 'cancelada') {
    return estadoReal;
  }

  const ahora = new Date();
  const fechaCita = new Date(`${fechaReserva}T${horaReserva}`);
  const diffTiempo = fechaCita - ahora;
  const diffDias = diffTiempo / (1000 * 60 * 60 * 24);

  // 2. Si no, calculamos por tiempo
  if (diffTiempo < 0) {
    return 'finalizada'; 
  } else if (diffDias <= 7) {
    return 'proximo';
  } else {
    return 'futuro';
  }
};

const calcularTextoTiempo = (fechaReserva, horaReserva, estadoReal) => {
  // Si el estado es completada o cancelada, mostramos el texto tal cual
  if (estadoReal === 'completada') return 'Completada';
  if (estadoReal === 'cancelada') return 'Cancelada';

  // Si no, usamos la lógica de tiempo
  const clase = calcularClaseTiempo(fechaReserva, horaReserva, estadoReal);
  const etiquetas = {
    'finalizada': 'Finalizada',
    'proximo': 'Próxima Cita',
    'futuro': 'Futuras Citas'
  };
  return etiquetas[clase] || clase;
};


const totalPages = computed(() => Math.ceil(reservasFiltradas.value.length / itemsPerPage));

const reservasPaginadas = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return reservasFiltradas.value.slice(start, start + itemsPerPage);
});

const obtenerClaseFinal = (reserva) => {
  // PRIORIDAD 1: El estado manual (Completada o Cancelada)
  if (reserva.estado === 'COMPLETADA') return 'completada';
  if (reserva.estado === 'ANULADA LOCAL') return 'cancelada';

  // PRIORIDAD 2: El tiempo (solo si no está cerrada)
  const ahora = new Date();
  const fechaCita = new Date(`${reserva.fecha}T${reserva.hora}`);
  const diffTiempo = fechaCita - ahora;
  const diffDias = diffTiempo / (1000 * 60 * 60 * 24);

  if (diffTiempo < 0) return 'finalizada'; 
  if (diffDias <= 7) return 'proximo';
  return 'futuro';
};

const obtenerTextoFinal = (reserva) => {
  const clase = obtenerClaseFinal(reserva);
  const etiquetas = {
    'completada': 'Completada',
    'cancelada': 'Anulada',
    'finalizada': 'Finalizada',
    'proximo': 'Próxima Cita',
    'futuro': 'Futuro'
  };
  return etiquetas[clase];
};

const cambiarEstado = async (reserva, nuevoEstado) => {
  try {
    const res = await fetch('/backend/api/gestion_reservas.php', {
      method: 'POST',
      credentials: 'include',
      body: JSON.stringify({ id: reserva.id, estado: nuevoEstado })
    });
    if (res.ok) reserva.estado = nuevoEstado;
  } catch (e) {
    console.error(e);
  }
};

const esFechaBloqueada = (fechaReserva) => {
  const ahora = new Date();
  ahora.setHours(0, 0, 0, 0); // Normalizamos a las 00:00 de hoy
  
  // Calculamos la fecha de anteayer
  const anteayer = new Date(ahora);
  anteayer.setDate(ahora.getDate() - 2);
  
  const fechaCita = new Date(fechaReserva);
  fechaCita.setHours(0, 0, 0, 0);

  // Si la cita es menor que anteayer, está bloqueada
  return fechaCita < anteayer;
};

const formatearFecha = (fecha) => {
  return new Date(fecha).toLocaleDateString('es-ES', { 
    day: '2-digit', 
    month: 'short', 
    year: 'numeric' 
  });
};

onMounted(cargarReservas);
</script>

<style scoped>
/* REUTILIZAMOS TUS ESTILOS DE ADMIN SERVICIOS */
.section-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; padding-bottom: 10px; border-bottom: 1px solid #f0f0f0; }
.header-left .breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 0.8rem; color: #999; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 1px; }
.breadcrumb .current { color: #e75480; font-weight: 600; }
.title-container { display: flex; align-items: center; gap: 15px; }
.title-container h1 { margin: 0; font-size: 1.8rem; color: #2c3e50; font-weight: 800; }
.badge-count { background: #fdf2f5; color: #e75480; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; border: 1px solid #f9dbe5; }

.header-actions { display: flex; gap: 15px; align-items: center; }
.filters { display: flex; gap: 10px; }
.search-box { position: relative; }
.search-box input { padding: 8px 10px 8px 30px; border: 1px solid #ddd; border-radius: 6px; width: 220px; }
.search-box i { position: absolute; left: 10px; top: 10px; color: #999; }
.status-select { padding: 8px; border-radius: 6px; border: 1px solid #ddd; background: white; }

.table-card { background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; }
.custom-table { width: 100%; border-collapse: collapse; }
.custom-table th { background: #f8f9fa; padding: 15px; text-align: left; color: #888; font-size: 0.8rem; text-transform: uppercase; }
.custom-table td {
  padding: 10px 12px; /* Reducido de 15px */
  border-bottom: 1px solid #f2f2f2;
  vertical-align: middle;
  font-size: 0.85rem; /* Fuente más pequeña para todo el contenido */
}
.font-bold {
  font-weight: 600;
  font-size: 0.85rem; 
  color: #2c3e50;
}
/* ESTILOS ESPECÍFICOS PARA RESERVAS */
.date-display { display: flex; flex-direction: column; line-height: 1.4; margin-left: 2%; }
.text-muted {
  color: #888;
  font-size: 0.75rem; /* Aún más pequeño para crear jerarquía */
}
.small {
  font-size: 0.75rem;
}
.avatar-mini { width: 30px; height: 30px; background: #e75480; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.8rem; }
.user-info { display: flex; align-items: center; gap: 10px; }
.service-tag-display {
  display: flex;
  align-items: center;
  background: #f1f5f9;
  padding: 3px 10px; /* Más estrecho */
  border-radius: 12px;
  font-size: 0.75rem; /* Texto de servicio más pequeño */
  width: fit-content;
  gap: 6px;
}
.table-icon {
  width: 16px; /* Icono más pequeño */
  height: 16px;
}

/* BADGES DE ESTADO */
.status-badge { padding: 4px 10px; border-radius: 6px; font-size: 0.7rem; font-weight: 800; }
.status-badge.pendiente { background: #fff3cd; color: #856404; }
.status-badge.confirmada { background: #d4edda; color: #155724; }
.status-badge.completada {
  background: #f0fff4;
  color: #27ae60;
  border: 1px solid #c6f6d5;
}
.status-badge.cancelada {
  background: #fff5f5;
  color: #e74c3c;
  border: 1px solid #fed7d7;
}
/* Fila Completada (Sutilmente diferente para que no brille como la próxima) */
.custom-table tr.fila-completada td:first-child {
  border-left: 4px solid #27ae60; /* Indicador lateral verde */
}
/* Fila Cancelada */
.custom-table tr.fila-cancelada td:first-child {
  border-left: 4px solid #e74c3c;
}
.action-select-simple { padding: 6px; border-radius: 6px; border: 1px solid #ddd; font-size: 0.8rem; background: #fff; cursor: pointer; }

/* RESPONSIVE MODO TARJETA (Copiado de tu AdminServicios) */
@media (max-width: 768px) {
  /* 1. Ajuste del Header para evitar solapamiento */
  .section-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 15px;
    padding-top: 60px; /* Espacio para el botón de menú */
    border-bottom: none;
  }

  .header-actions {
    width: 100%;
    flex-direction: column;
    gap: 12px;
  }

  .filters {
    flex-direction: column;
    width: 100%;
    gap: 10px;
  }

  /* Inputs a ancho completo */
  .search-box, .status-select, .date-filter-box, .search-box input {
    width: 100% !important;
  }

  /* 2. Transformación de la Tabla a Tarjetas */
  .custom-table thead {
    display: none;
  }

  .custom-table tr {
    display: block;
    margin-bottom: 20px;
    padding: 15px;
    border: 1px solid #edf2f7;
    border-radius: 16px;
    background: #fff;
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
  }

  /* Estilos para las filas especiales en móvil */
  .custom-table tr.fila-proxima {
    border-left: 6px solid #d69e2e !important;
  }
  .custom-table tr.fila-completada {
    border-left: 6px solid #2ecc71 !important;
  }

  .custom-table td {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0 !important;
    border-bottom: 1px solid #f8f9fa;
    text-align: right;
  }

  .custom-table td:last-child {
    border-bottom: none;
    padding-top: 15px !important;
    justify-content: center; /* Botones de acción centrados al final */
  }

  /* Labels a la izquierda */
  .custom-table td::before {
    content: attr(data-label);
    font-weight: 700;
    color: #94a3b8;
    font-size: 0.7rem;
    text-transform: uppercase;
    text-align: left;
  }

  /* 3. Ajustes de contenido interno */
  .date-display {
    align-items: flex-end;
    margin: 0;
  }

  .user-info {
    justify-content: flex-end;
    gap: 8px;
  }

  .service-tag-display {
    margin: 0;
    background: #f8fafc;
  }

  .action-buttons {
    width: 100%;
    justify-content: center;
    gap: 20px;
  }

  .btn-action {
    font-size: 1.5rem; /* Iconos más grandes para el pulgar */
  }

  .status-badge {
    font-size: 0.75rem;
    padding: 5px 12px;
  }
}

.avatar-mini {
  width: 26px; /* Reducido de 30px */
  height: 26px;
  background: #e75480;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 0.7rem;
}

.status-badge {
  padding: 3px 8px; /* Badge más compacta */
  border-radius: 4px;
  font-size: 0.65rem; /* Texto de estado muy pequeño y nítido */
  font-weight: 700;
}

.action-select-simple {
  padding: 4px 6px; /* Selector de acciones más discreto */
  border-radius: 6px;
  border: 1px solid #ddd;
  font-size: 0.75rem;
  background: #fff;
}

/* Estilos pequeños y nítidos para los nuevos estados temporales */
.status-badge {
  padding: 3px 8px;
  border-radius: 4px;
  font-size: 0.65rem;
  font-weight: 700;
  text-transform: uppercase;
  display: inline-block;
}

/* Gris para citas que ya terminaron */
.status-badge.pasado {
  background: #edf2f7;
  color: #718096;
  border: 1px solid #e2e8f0;
}

/* Dorado/Naranja para citas de esta semana (Urgente) */
.status-badge.proximo {
  background: #fffaf0;
  color: #d69e2e;
  border: 1px solid #fef3c7;
}

/* Azul para citas lejanas */
.status-badge.futuro {
  background: #ebf8ff;
  color: #3182ce;
  border: 1px solid #bee3f8;
}

/* Estilo para la fila resaltada (Próxima cita) */
.custom-table tr.fila-proxima {
  background-color: #fffdf5; /* Un amarillo crema casi imperceptible */
  transition: all 0.3s ease;
}

/* Añadimos un indicador lateral de "urgencia" */
.custom-table tr.fila-proxima td:first-child {
  border-left: 4px solid #d69e2e; /* Borde dorado/naranja */
  position: relative;
}

/* Efecto hover especial para las próximas */
.custom-table tr.fila-proxima:hover {
  background-color: #fff9e6;
}

/* Opcional: Si quieres que el texto del nombre también resalte un poco en estas filas */
.fila-proxima .font-bold {
  color: #b45309; /* Un tono marrón/dorado oscuro */
}

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

/* Estilos del Modal */
.modal-overlay {
  position: fixed;
  top: 0; left: 0; width: 100%; height: 100%;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 25px;
  border-radius: 12px;
  width: 90%;
  max-width: 400px;
}

.modal-content textarea {
  width: 100%;
  height: 100px;
  margin: 15px 0;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 8px;
}

.modal-actions {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.btn-confirm { background: #e74c3c; color: white; border: none; padding: 10px; border-radius: 8px; font-weight: bold; cursor: pointer; }
.btn-close { background: #f1f2f6; color: #57606f; border: none; padding: 10px; border-radius: 8px; cursor: pointer; }

/* ELIMINAMOS EL RESALTADO AMARILLO SI ESTÁ COMPLETADA */
.custom-table tr.fila-proxima {
  background-color: #fffdf5;
}

/* Si la fila tiene ambas clases, el estilo de completada debe mandar */
.custom-table tr.fila-completada {
  background-color: #ffffff !important; /* Fondo blanco limpio */
  opacity: 1;
}

.custom-table tr.fila-completada td:first-child {
  border-left: 4px solid #2ecc71 !important; /* Borde verde fuerte */
}

/* Badge de completada más llamativa */
.status-badge.completada {
  background: #e6fffa;
  color: #23a35a;
  border: 1px solid #b2f5ea;
  font-weight: 800;
}

.btn-action.undo {
  color: #3498db;
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 1rem;
  background: #f0f7ff;
  padding: 4px 8px;
  border-radius: 6px;
  border: 1px solid #d6e9f7;
}

.btn-action.undo:hover {
  background: #e1effe;
  transform: scale(1.05);
}

.undo-text {
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
}

/* Ajuste para que los botones de acción se vean alineados */
.action-buttons {
  display: flex;
  gap: 12px;
  justify-content: flex-end; /* Alineado a la derecha en la celda */
  align-items: center;
}

/* Aplicamos un estilo visual a las filas que ya son historia antigua */
.custom-table tr.fila-bloqueada {
  opacity: 0.7;
  background-color: #fcfcfc;
}

.custom-table tr.fila-bloqueada:hover {
  background-color: #f8f9fa;
}

.date-filter-box {
  position: relative;
  display: flex;
  align-items: center;
}

.btn-clear-date {
  position: absolute;
  right: 10px;
  background: none;
  border: none;
  color: #e75480;
  cursor: pointer;
  font-size: 0.8rem;
}

/* Ajuste para que el input date se parezca a tus otros selects */
input[type="date"].status-select {
  font-family: inherit;
  color: #4a5568;
  padding: 7px 10px;
}

/* --- MODAL DE CONFIRMACIÓN --- */
.confirm-modal {
  text-align: center;
  padding: 40px 30px;
  max-width: 350px;
}

.confirm-icon {
  width: 70px;
  height: 70px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  margin: 0 auto 20px;
}

.confirm-icon.success {
  background: #e6fffa;
  color: #2ecc71;
}

.confirm-modal h3 {
  margin-bottom: 10px;
  color: #2c3e50;
}

.confirm-modal p {
  color: #64748b;
  font-size: 0.9rem;
  margin-bottom: 25px;
}

.modal-actions-horizontal {
  display: flex;
  flex-direction: column; /* En móvil uno sobre otro, en PC podemos cambiarlo */
  gap: 10px;
}

.btn-confirm-action {
  background: #2ecc71; /* O el color que prefieras según el tipo */
  color: white;
  border: none;
  padding: 14px;
  border-radius: 12px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-cancel-modal {
  background: #f1f5f9;
  color: #64748b;
  border: none;
  padding: 12px;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
}

@media (min-width: 768px) {
  .modal-actions-horizontal {
    flex-direction: row-reverse;
    justify-content: center;
  }
  .btn-confirm-action, .btn-cancel-modal {
    flex: 1;
  }
}

/* --- CORRECCIÓN VISUAL MODAL ANULACIÓN --- */

.modal-content h3 {
  color: #1e293b !important; /* Azul muy oscuro, casi negro */
  font-weight: 800;
  margin-bottom: 15px;
  display: block;
}

.modal-content p {
  color: #475569 !important; /* Gris oscuro para el texto del cliente */
  font-size: 0.95rem;
  margin-bottom: 15px;
}

.modal-content strong {
  color: #1e293b; /* Nombre del cliente bien marcado */
}

.modal-content label {
  display: block;
  color: #64748b; /* Gris medio para el label */
  font-size: 0.85rem;
  font-weight: 700;
  margin-bottom: 8px;
  text-transform: uppercase;
}

.modal-content textarea {
  width: 100%;
  background-color: #f8fafc; /* Fondo ligeramente gris para que se vea el campo */
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 12px;
  color: #1e293b; /* Texto que escribe el usuario en oscuro */
  font-family: inherit;
  resize: none;
}

.modal-content textarea::placeholder {
  color: #94a3b8; /* Color del placeholder */
}

/* Botones del modal de anulación */
.modal-actions {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-top: 20px;
}

.btn-confirm {
  background: #ef4444; /* Rojo para anulación */
  color: white !important;
  border: none;
  padding: 14px;
  border-radius: 12px;
  font-weight: 700;
  cursor: pointer;
}

.btn-close {
  background: #f1f5f9;
  color: #475569 !important;
  border: none;
  padding: 12px;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
}

</style>