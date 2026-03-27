<script setup>
import { ref, onMounted, computed } from 'vue';

const promociones = ref([]);
const mostrarModal = ref(false);
const intentoEnvio = ref(false);
const editando = ref(false);
const cargando = ref(false);
const etiquetasDisponibles = ref([]);

// --- NUEVOS REFS PARA EL MODAL DE AVISO ---
const mostrarModalAviso = ref(false);
const mensajeAviso = ref({ titulo: '', cuerpo: '', tipo: 'error', accion: null });

const nuevaPromo = ref({
  id: null,
  nombre: '',
  descripcion: '',
  tipo: 'VISITAS', 
  cupones_necesarios: 5,
  valor_descuento: null, 
  etiqueta_id: null,   
  fecha_inicio: new Date().toISOString().split('T')[0],
  fecha_fin: '',
  activa: 1
});

// Función para disparar el modal de aviso
const dispararAviso = (titulo, cuerpo, tipo = 'error') => {
  mensajeAviso.value = { titulo, cuerpo, tipo, accion: null };
  mostrarModalAviso.value = true;
};

// Nueva función para sustituir al confirm() nativo
const solicitarConfirmacion = (titulo, cuerpo, callback) => {
  mensajeAviso.value = { 
    titulo, 
    cuerpo, 
    tipo: 'confirm', 
    accion: callback 
  };
  mostrarModalAviso.value = true;
};

const cargarEtiquetas = async () => {
  try {
    const res = await fetch('/backend/api/etiquetas.php');
    etiquetasDisponibles.value = await res.json();
  } catch (e) { console.error("Error cargando etiquetas:", e); }
};

const formularioValido = computed(() => {
  const p = nuevaPromo.value;
  const fechasOk = p.fecha_inicio && p.fecha_fin && p.fecha_fin >= p.fecha_inicio;
  const nombreDescOk = p.nombre.trim() !== '' && p.descripcion.trim() !== '';
  
  let tipoOk = false;
  if (p.tipo === 'VISITAS') tipoOk = p.cupones_necesarios > 0;
  if (p.tipo === 'RECOMENDADO') tipoOk = true;
  if (p.tipo === 'PORCENTAJE') tipoOk = p.valor_descuento > 0;
  if (p.tipo === 'ETIQUETA') tipoOk = p.etiqueta_id !== null;

  return nombreDescOk && fechasOk && tipoOk;
});

const errorFechas = computed(() => {
  if (!nuevaPromo.value.fecha_inicio || !nuevaPromo.value.fecha_fin) return false;
  return nuevaPromo.value.fecha_fin < nuevaPromo.value.fecha_inicio;
});

const cargarPromos = async () => {
  try {
    const res = await fetch('/backend/api/get_promociones.php');
    promociones.value = await res.json();
  } catch (e) { console.error("Error al cargar promociones:", e); }
};

const abrirModalNuevo = () => {
  editando.value = false;
  intentoEnvio.value = false;
  nuevaPromo.value = {
    id: null, nombre: '', descripcion: '', cupones_necesarios: 5,
    fecha_inicio: new Date().toISOString().split('T')[0],
    fecha_fin: '', tipo: 'VISITAS', activa: 1
  };
  mostrarModal.value = true;
};

const prepararEdicion = (promo) => {
  editando.value = true;
  intentoEnvio.value = false;
  nuevaPromo.value = { ...promo };
  mostrarModal.value = true;
};

const guardarPromocion = async () => {
  intentoEnvio.value = true; 
  if (!formularioValido.value) return;

  cargando.value = true;
  try {
    const payload = {
      ...nuevaPromo.value,
      cupones_necesarios: nuevaPromo.value.tipo === 'VISITAS' ? nuevaPromo.value.cupones_necesarios : null,
      valor_descuento: nuevaPromo.value.tipo === 'PORCENTAJE' ? nuevaPromo.value.valor_descuento : null,
      etiqueta_id: nuevaPromo.value.tipo === 'ETIQUETA' ? nuevaPromo.value.etiqueta_id : null
    };

    const res = await fetch('/backend/api/operaciones_promos.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    
    const data = await res.json();
    if (data.status === 'success') {
      mostrarModal.value = false;
      await cargarPromos();
    } else {
      dispararAviso("No se pudo guardar", data.message);
    }
  } catch (e) { 
    dispararAviso("Error de sistema", "No se pudo conectar con el servidor.");
  } finally { cargando.value = false; }
};

const eliminarPromocion = (id) => {
  // En lugar de if(!confirm...), llamamos a nuestro modal
  solicitarConfirmacion(
    "¿Eliminar promoción?", 
    "Esta acción no se puede deshacer y afectará a los usuarios que la tengan activa.",
    async () => {
      try {
        const res = await fetch(`/backend/api/operaciones_promos.php?id=${id}`, {
          method: 'DELETE'
        });
        const data = await res.json();

        if (data.status === 'success') {
          await cargarPromos();
          mostrarModalAviso.value = false; // Cerramos el modal tras éxito
        } else {
          // Si el PHP dice que no se puede (por las etiquetas), disparamos error
          dispararAviso("Acción Denegada", data.message, "warning");
        }
      } catch (e) {
        dispararAviso("Error", "Error de conexión al intentar eliminar.");
      }
    }
  );
};

onMounted(() => {
  cargarPromos();
  cargarEtiquetas();
});
</script>

<template>
  <div class="promociones-view">
    <header class="section-header">
      <div class="header-left">
        <div class="breadcrumb">PANEL DE CONTROL > PROMOCIONES</div>
        <h1><i class="fas fa-ticket-alt"></i> Panel de Fidelización</h1>
      </div>
      <button @click="abrirModalNuevo" class="btn-nueva-promo">
        <i class="fas fa-plus-circle"></i> Crear Promoción
      </button>
    </header>

    <div class="tabla-container">
      <table class="custom-table">
        <thead>
          <tr>
            <th>NOMBRE Y DESCRIPCIÓN</th>
            <th>META</th>
            <th>TIPO</th>
            <th>VIGENCIA</th>
            <th>ESTADO</th>
            <th class="text-center">ACCIONES</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in promociones" :key="p.id">
            <td class="col-main">
              <div class="promo-info">
                <span class="promo-title">{{ p.nombre }}</span>
                <span class="promo-subtitle">{{ p.descripcion }}</span>
              </div>
            </td>
            <td>
                <div v-if="p.tipo === 'VISITAS'" class="meta-badge sellos">
                    <i class="fas fa-stamp"></i> {{ p.cupones_necesarios }} sellos
                </div>
                <div v-if="p.tipo === 'PORCENTAJE'" class="meta-badge porcentaje">
                    <i class="fas fa-percent"></i> {{ p.valor_descuento }}
                </div>
                <div v-if="p.tipo === 'ETIQUETA'" class="meta-badge etiqueta">
                    <i class="fas fa-tag"></i> {{ p.nombre_etiqueta || 'Sin etiqueta' }}
                </div>
                <div v-if="p.tipo === 'RECOMENDADO'" class="meta-badge recomendado">
                    <i class="fas fa-user-plus"></i> Por Invitado
                </div>
            </td>
            <td><span class="type-tag">{{ p.tipo }}</span></td>
            <td class="col-dates">
              <div class="date-item"><strong>Desde:</strong> {{ p.fecha_inicio }}</div>
              <div class="date-item"><strong>Hasta:</strong> {{ p.fecha_fin }}</div>
            </td>
            <td>
              <span :class="['status-pill', p.estado_calculado.toLowerCase()]">
                {{ p.estado_calculado }}
              </span>
            </td>
            <td class="text-center actions-cell">
              <button @click="prepararEdicion(p)" class="btn-action edit"><i class="fas fa-edit"></i></button>
              <button @click="eliminarPromocion(p.id)" class="btn-action delete"><i class="fas fa-trash"></i></button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <Transition name="fade">
      <div v-if="mostrarModal" class="modal-overlay" @click.self="mostrarModal = false">
        <div class="modal-content promo-modal">
          <div class="modal-header">
            <div class="header-title">
              <i class="fas fa-gift"></i>
              <h3>{{ editando ? 'Editar Promoción' : 'Nueva Promoción' }}</h3>
            </div>
            <button @click="mostrarModal = false" class="btn-close">&times;</button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label>Título de la promoción *</label>
              <input v-model="nuevaPromo.nombre" :class="{ 'input-error': intentoEnvio && !nuevaPromo.nombre }">
            </div>
            <div class="form-group">
              <label>Descripción *</label>
              <textarea v-model="nuevaPromo.descripcion" rows="2" :class="{ 'input-error': intentoEnvio && !nuevaPromo.descripcion }"></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Tipo</label>
                    <select v-model="nuevaPromo.tipo" class="select-premium">
                        <option value="VISITAS">Por Visitas (Sellos)</option>
                        <option value="RECOMENDADO">Por Recomendación</option>
                        <option value="PORCENTAJE">Porcentaje de Descuento</option>
                        <option value="ETIQUETA">Por Etiqueta de Cliente</option>
                    </select>
                </div>
                <div v-if="nuevaPromo.tipo === 'VISITAS'" class="form-group animate-in">
                    <label>Sellos</label>
                    <input type="number" v-model="nuevaPromo.cupones_necesarios">
                </div>
                <div v-if="nuevaPromo.tipo === 'ETIQUETA'" class="form-group animate-in">
                    <label>Etiqueta</label>
                    <select v-model="nuevaPromo.etiqueta_id">
                        <option v-for="tag in etiquetasDisponibles" :key="tag.id" :value="tag.id">{{ tag.nombre }}</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Inicio</label>
                <input type="date" v-model="nuevaPromo.fecha_inicio">
              </div>
              <div class="form-group">
                <label>Fin</label>
                <input type="date" v-model="nuevaPromo.fecha_fin" :class="{ 'input-error': errorFechas }">
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button @click="guardarPromocion" class="btn-confirm-promo" :disabled="cargando">
              {{ cargando ? 'Guardando...' : 'Confirmar' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <Transition name="fade">
      <div v-if="mostrarModalAviso" class="modal-overlay alert-z" @click.self="mostrarModalAviso = false">
        <div class="modal-content modal-aviso">
          <div class="modal-body text-center">
            <div :class="['icon-circle', mensajeAviso.tipo]">
              <i v-if="mensajeAviso.tipo === 'error'" class="fas fa-times"></i>
              <i v-else-if="mensajeAviso.tipo === 'confirm'" class="fas fa-question"></i>
              <i v-else class="fas fa-exclamation"></i>
            </div>
            
            <h3 class="aviso-titulo">{{ mensajeAviso.titulo }}</h3>
            <p class="aviso-cuerpo">{{ mensajeAviso.cuerpo }}</p>

            <div v-if="mensajeAviso.tipo === 'confirm'" class="flex-buttons">
                <button @click="mostrarModalAviso = false" class="btn-cancelar">Cancelar</button>
                <button @click="mensajeAviso.accion" class="btn-eliminar-confirm">Sí, eliminar</button>
            </div>
            <button v-else @click="mostrarModalAviso = false" class="btn-entendido">Entendido</button>
          </div>
        </div>
      </div>
    </Transition>

  </div>
</template>

<style scoped>
.promociones-view { padding: 25px; background: #f8fafc; min-height: 100vh; }
.section-header { margin-bottom: 25px; display: flex; justify-content: space-between; align-items: flex-end; }
.btn-nueva-promo { background: #e75480; color: white; border: none; padding: 12px 20px; border-radius: 10px; font-weight: 700; cursor: pointer; }

/* Header estilo Agenda */
.section-header { margin-bottom: 25px; display: flex; justify-content: space-between; align-items: flex-end; }
.breadcrumb { font-size: 0.7rem; color: #e75480; font-weight: 700; margin-bottom: 5px; }
.section-header h1 { font-size: 1.8rem; color: #1e293b; margin: 0; }
.btn-nueva-promo {
  background: #e75480; color: white; border: none; padding: 12px 20px;
  border-radius: 10px; font-weight: 700; cursor: pointer; transition: 0.3s;
}

/* Tabla Estilo Profesional */
.tabla-container { background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); overflow: hidden; }
.custom-table { width: 100%; border-collapse: collapse; }
.custom-table th { 
  background: #f8fafc; padding: 15px; text-align: left; 
  font-size: 0.75rem; color: #94a3b8; border-bottom: 1px solid #f1f5f9;
}
.custom-table td { padding: 15px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }

.promo-title { display: block; font-weight: 700; color: #1e293b; }
.promo-subtitle { font-size: 0.8rem; color: #64748b; }

.meta-badge { 
  background: #f0fdf4; color: #16a34a; padding: 5px 10px; 
  border-radius: 8px; font-size: 0.85rem; font-weight: 700;
  display: inline-flex; align-items: center; gap: 6px;
}

.type-tag { background: #eff6ff; color: #2563eb; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; }

.status-pill { padding: 4px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; }
.status-pill.activa { background: #dcfce7; color: #15803d; }
.status-pill.caducada { background: #fee2e2; color: #b91c1c; }

.col-dates { font-size: 0.8rem; color: #64748b; }

/* Botones Acción */
.actions-cell { display: flex; gap: 8px; justify-content: center; }
.btn-action { 
  width: 32px; height: 32px; border-radius: 8px; border: none; 
  cursor: pointer; transition: 0.2s; display: flex; align-items: center; justify-content: center;
}
.btn-action.edit { background: #f0f9ff; color: #0ea5e9; }
.btn-action.delete { background: #fff1f2; color: #f43f5e; }
.btn-action:hover { transform: scale(1.1); }

/* Modal Corregido */
.modal-overlay {
  position: fixed; top: 0; left: 0; width: 100%; height: 100%;
  background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(4px);
  display: flex; align-items: center; justify-content: center; z-index: 9999;
}
.alert-z { z-index: 10000; } /* Por encima del modal de edición si fuera necesario */
.promo-modal { background: white; width: 95%; max-width: 500px; border-radius: 20px; overflow: hidden; }
.modal-header { padding: 20px; background: #f8fafc; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; }
.header-title { display: flex; align-items: center; gap: 10px; color: #e75480; }
.header-title h3 { margin: 0; color: #1e293b; }
.modal-body { padding: 20px; }
.form-group { margin-bottom: 15px; }
.form-group label { display: block; font-size: 0.8rem; font-weight: 700; color: #475569; margin-bottom: 5px; }
.form-group input, .form-group textarea, .form-group select {
  width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none;
}
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
.input-error { border-color: #f43f5e !important; background: #fff1f2; }
.error-msg-small { color: #f43f5e; font-size: 0.75rem; margin-top: -10px; margin-bottom: 10px; }

.modal-footer { padding: 20px; border-top: 1px solid #f1f5f9; }
.btn-confirm-promo {
  width: 100%; padding: 12px; background: #1e293b; color: white;
  border: none; border-radius: 10px; font-weight: 700; cursor: pointer; transition: 0.3s;
}
.btn-confirm-promo:hover:not(:disabled) { background: #e75480; }
.btn-confirm-promo:disabled { opacity: 0.5; cursor: not-allowed; }

.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.animate-in {
  animation: fadeInDown 0.3s ease-out;
}

@keyframes fadeInDown {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}

.select-premium {
  border: 2px solid #e75480 !important;
  background: #fff5f8 !important;
  font-weight: 700;
  color: #e75480;
}

/* Badge especial para la tabla cuando es por etiqueta */
.tag-badge {
  background: #f3e8ff;
  color: #7e22ce;
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 0.7rem;
  font-weight: 700;
}
.meta-badge.sellos { background: #f0fdf4; color: #16a34a; }
.meta-badge.porcentaje { background: #fff7ed; color: #c2410c; }
.meta-badge.etiqueta { background: #f5f3ff; color: #7c3aed; }
.meta-badge.recomendado { background: #ecfeff; color: #0891b2; }

.meta-badge i { font-size: 0.8rem; }

/* ESTILOS DEL MODAL DE AVISO */
.modal-aviso {
  background: white;
  width: 90%;
  max-width: 380px;
  border-radius: 24px;
  padding: 30px;
  text-align: center;
  box-shadow: 0 20px 40px rgba(0,0,0,0.2);
}
.icon-circle {
  width: 60px; height: 60px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 20px; font-size: 1.5rem;
}
.icon-circle.error { background: #fee2e2; color: #ef4444; }
.icon-circle.warning { background: #fff7ed; color: #f97316; }

.aviso-titulo { margin-bottom: 10px; color: #1e293b; font-weight: 800; }
.aviso-cuerpo { color: #64748b; font-size: 0.95rem; line-height: 1.5; margin-bottom: 25px; }

.btn-entendido {
  width: 100%; padding: 12px; background: #1e293b; color: white;
  border: none; border-radius: 12px; font-weight: 700; cursor: pointer;
}
.btn-entendido:hover { background: #e75480; }

.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>