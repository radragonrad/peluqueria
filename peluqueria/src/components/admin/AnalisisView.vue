<template>
  <div class="analisis-view">    
    <header class="section-header">
      <div class="header-left">
        <div class="breadcrumb">
          <span>Panel de Control</span> 
          <i class="fas fa-chevron-right"></i> 
          <span class="current">Análisis</span>
        </div>
        <div class="title-container">
          <h1>Análisis de productividad</h1>
          <span class="badge-count">Reporte Detallado</span>
        </div>
      </div>

      <div class="header-actions">
        <button @click="abrirResumen" class="btn-resumen">
    <i class="fas fa-chart-pie"></i> Ver Resumen Global
  </button>
        <div class="filters">
          <div class="date-filter-box">
            <label>Desde</label>
            <input type="date" v-model="filtros.inicio" class="status-select">
          </div>
          <div class="date-filter-box">
            <label>Hasta</label>
            <input type="date" v-model="filtros.fin" class="status-select">
          </div>
          <button @click="cargarAnalisis" class="btn-goto-agendar">
            <i class="fas fa-search"></i> Filtrar
          </button>
        </div>
      </div>
    </header>

    <!-- REJILLA DE PRODUCTIVIDAD -->
    <div v-if="analisisData.length > 0" class="semanas-container">
      <div v-for="(semana, index) in analisisData" :key="index" class="semana-block">
        <h3 class="semana-titulo">{{ semana.titulo }}</h3>
        
        <div class="semana-grid">
          <div v-for="dia in semana.dias" :key="dia.fecha" class="dia-columna" :class="{ 'dia-cerrado': dia.abierto === 0 }">
            
            <!-- Cabecera del día -->
            <div class="dia-header">
              <span class="dia-nombre">{{ dia.dia_nombre }}</span>
              <span class="dia-fecha">{{ dia.fecha_f }}</span>
              <div class="dia-stats">
                <i class="fas fa-calendar-check"></i> {{ dia.total_citas }}
              </div>
            </div>

            <!-- Tramos de tiempo -->
            <div class="tramos-container">
              <template v-if="dia.abierto === 1">
                <div 
                  v-for="tramo in dia.tramos" 
                  :key="tramo.hora"
                  class="tramo-celda"
                  :class="tramo.ocupado ? 'tramo-ocupado' : 'tramo-vacio'"
                  :title="tramo.hora + (tramo.ocupado ? ' - Ocupado' : ' - Libre')"
                >
                  <span class="tramo-hora">{{ tramo.hora }}</span>
                  <!-- Pintamos el número solo si es mayor a 0 -->
                <span v-if="tramo.cantidad > 0" class="badge-cantidad">
                  {{ tramo.cantidad }}
                </span>
                </div>
              </template>
              <div v-else class="mensaje-cerrado">
                <i class="fas fa-moon"></i>
                <span>Cerrado</span>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>

    <!-- Estado de carga o vacío -->
    <div v-else class="no-data">
      <i class="fas fa-chart-area"></i>
      <p>Selecciona un rango de fechas para analizar la productividad</p>
    </div>
  </div>

    <!-- Modal de Resumen -->
<!-- Modal de Resumen -->
<div v-if="mostrarModal" class="modal-overlay" @click.self="mostrarModal = false">
  <!-- AGREGAMOS EL CONTENEDOR BLANCO QUE FALTABA -->
  <div class="modal-content">
    
    <!-- ENCABEZADO ESTILIZADO -->
    <div class="modal-header-simple">
      <button @click="mostrarModal = false" class="btn-close-top">&times;</button>
      <h2 class="resumen-title">Resumen de Ocupación Global</h2>
      
      <div class="total-reservas-wrapper">
        <i class="fas fa-calendar-check icon-pink"></i>
        <span class="total-number">{{ totalReservasRango }}</span>
      </div>
      
      <p class="resumen-subtitle">Total de impactos por tramo en el rango seleccionado</p>
    </div>

    <!-- CUERPO DEL MODAL: AQUÍ PINTAMOS LOS TRAMOS -->
    <div class="resumen-columna">
      <div 
        v-for="tramo in datosResumen" 
        :key="tramo.hora" 
        class="tramo-celda" 
        :class="[
          tramo.ocupado ? 'tramo-ocupado' : 'tramo-vacio',
          { 'tramo-minimo': tramo.cantidad === cantidadMinimaGlobal },
          { 'tramo-maximo': tramo.cantidad === cantidadMaximaGlobal && tramo.cantidad !== cantidadMinimaGlobal }
        ]"
      >
        <!-- Icono de cama si es el tramo con menos reservas -->
        <i v-if="tramo.cantidad === cantidadMinimaGlobal" class="fas fa-bed tramo-icon-valle"></i>
        <i v-if="tramo.cantidad === cantidadMaximaGlobal && tramo.cantidad !== cantidadMinimaGlobal" 
            class="fas fa-fire tramo-icon-pico"></i>
        <span class="tramo-hora">{{ tramo.hora }}</span>
        
        <!-- Badge con el número de reservas en ese tramo -->
        <span v-if="tramo.cantidad > 0" class="badge-cantidad">
          {{ tramo.cantidad }}
        </span>
      </div>
    </div>

  </div>
</div>
</template>

<script>
export default {
  data() {
    return {
      mostrarModal: false,
      datosResumen: [],
      totalReservasRango: 0,
      filtros: {
        inicio: new Date().toISOString().substr(0, 10),
        fin: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().substr(0, 10)
      },
      analisisData: []
    }
  },
  computed: {
    // NUEVA PROPIEDAD COMPUTADA
    cantidadMinimaGlobal() {
      // Si no hay datos, devolvemos null
      if (!this.datosResumen || this.datosResumen.length === 0) return null;
      
      // Creamos un array solo con las cantidades [5, 12, 0, 8, ...]
      const cantidades = this.datosResumen.map(tramo => tramo.cantidad);
      
      // Encontramos el valor mínimo usando Math.min y el operador spread
      return Math.min(...cantidades);
    },
    cantidadMaximaGlobal() {
      if (!this.datosResumen || this.datosResumen.length === 0) return null;
      const cantidades = this.datosResumen.map(tramo => tramo.cantidad);
      const max = Math.max(...cantidades);
      // Solo resaltamos si el máximo es mayor que 0
      return max > 0 ? max : null;
    }
  },
  methods: {
    async cargarAnalisis() {
      try {
        const response = await fetch(`/backend/api/get_analisis_detallado.php?inicio=${this.filtros.inicio}&fin=${this.filtros.fin}`);
        const data = await response.json();
        if (data.error) {
          console.error(data.error);
        } else {
          this.analisisData = data;
        }
      } catch (error) {
        console.error("Error cargando análisis:", error);
      }
    },
    async abrirResumen() {
      this.mostrarModal = true;
      try {
        const resp = await fetch(`/backend/api/get_analisis_resumen.php?inicio=${this.filtros.inicio}&fin=${this.filtros.fin}`);
        const data = await resp.json();
        
        // Asignamos según la nueva estructura del PHP
        this.datosResumen = data.tramos;
        this.totalReservasRango = data.total_reservas;
      } catch (error) {
        console.error("Error:", error);
      }
    }
  },
  mounted() {
    this.cargarAnalisis();
  }
}
</script>

<style scoped>
/* Reutilizamos los estilos del header anterior y añadimos la rejilla */
.semanas-container {
  display: flex;
  flex-direction: column;
  gap: 40px;
  margin-top: 20px;
}

.semana-titulo {
  font-size: 0.9rem;
  color: #e75480;
  text-transform: uppercase;
  font-weight: 700;
  letter-spacing: 1px;
  margin-bottom: 15px;
  border-left: 4px solid #e75480;
  padding-left: 10px;
}


.dia-columna {
  background: white;
  border-radius: 12px;
  border: 1px solid #eee;
  overflow: hidden;
  transition: transform 0.2s;
}

.dia-columna:hover {
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.dia-header {
  padding: 12px;
  background: #fcfcfc;
  border-bottom: 1px solid #eee;
  text-align: center;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.dia-nombre {
  font-weight: 800;
  color: #2c3e50;
  font-size: 1rem;
}

.dia-fecha {
  font-size: 0.75rem;
  color: #999;
}

.dia-stats {
  margin-top: 5px;
  font-size: 0.85rem;
  color: #e75480;
  font-weight: 600;
}

/* TRAMOS */
.tramos-container {
  padding: 10px;
  display: flex;
  flex-direction: column;
  gap: 4px;
  min-height: 100px;
}

.tramo-celda {
  height: 28px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.7rem;
  transition: all 0.2s;
  cursor: default;
}

.tramo-vacio {
  background-color: #f8fafc;
  border: 1px solid #edf2f7;
  color: #cbd5e0;
}

.tramo-vacio:hover {
  background-color: #ffffff;
  border-color: #e75480;
  color: #e75480;
}

.tramo-ocupado {
  background-color: #e75480;
  color: white;
  font-weight: 700;
  box-shadow: 0 2px 4px rgba(231, 84, 128, 0.2);
}

.tramo-hora {
  pointer-events: none;
}

/* ESTADOS ESPECIALES */
.dia-cerrado {
  opacity: 0.6;
  background: #f5f5f5;
}

.mensaje-cerrado {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px 0;
  color: #bbb;
  gap: 10px;
  font-size: 0.8rem;
}

.no-data {
  text-align: center;
  padding: 100px;
  color: #ccc;
}

.no-data i { font-size: 4rem; margin-bottom: 20px; }

/* Responsive */
@media (max-width: 1200px) {
  .semana-grid { grid-template-columns: repeat(4, 1fr); }
}
@media (max-width: 768px) {
  .semana-grid { grid-template-columns: repeat(2, 1fr); }
}

.analisis-view { padding: 20px; font-family: 'Inter', sans-serif; background: #fbfcfd; }
.section-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px; border-bottom: 1px solid #f0f0f0; padding-bottom: 15px; }
.breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 0.75rem; color: #999; text-transform: uppercase; margin-bottom: 5px; }
.breadcrumb .current { color: #e75480; font-weight: 600; }
.title-container { display: flex; align-items: center; gap: 12px; margin-top: 5px; }
.title-container h1 { margin: 0; font-size: 1.6rem; font-weight: 800; color: #1e293b; }


/* --- 3. FILTROS Y BÚSQUEDA (IMPUTS MODERNOS) --- */
.header-actions {
  display: flex;
  gap: 12px;
  align-items: center;
}

.filters {
  display: flex;
  gap: 8px;
  align-items: center;
}

.date-filter-box {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.date-filter-box label {
  font-size: 0.65rem;
  color: #a0aec0; /* Un gris sutil para la etiqueta */
  text-transform: uppercase;
  font-weight: 700;
  margin-left: 5px;
}

/* ESTILO IDÉNTICO A LOS INPUTS DE RESERVAS */
.status-select {
  padding: 8px 12px;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  background: white;
  font-size: 0.8rem;
  color: #334155;
  outline: none;
  transition: 0.2s; /* Transición suave */
}

/* Efecto focus rosa, idéntico a Reservas */
.status-select:focus {
  border-color: #e75480;
  box-shadow: 0 0 0 3px rgba(231, 84, 128, 0.1);
}

/* --- 4. BOTÓN "FILTRAR" (IDÉNTICO A "NUEVA RESERVA") --- */
.btn-goto-agendar {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background-color: #e75480; /* Fondo rosa */
  color: white;
  padding: 8px 14px;
  border-radius: 8px;
  border: none;
  font-size: 0.75rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 2px 4px rgba(231, 84, 128, 0.15); /* Sombra sutil */
  white-space: nowrap;
}

/* Efectos de interacción heredados de Reservas */
.btn-goto-agendar:hover {
  background-color: #d6436f; /* Un rosa ligeramente más oscuro al pasar el ratón */
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(231, 84, 128, 0.25);
}

/* --- 5. ESTILOS PROPIOS DE LA REJILLA DE ANÁLISIS --- */
.semanas-container { margin-top: 25px; display: flex; flex-direction: column; gap: 30px; }
.semana-titulo { font-size: 0.8rem; color: #e75480; text-transform: uppercase; font-weight: 800; border-left: 3px solid #e75480; padding-left: 10px; margin-bottom: 15px; }
.semana-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; align-items: start; }

.dia-columna { background: white; border-radius: 12px; border: 1px solid #edf2f7; overflow: hidden; }
.dia-header { padding: 10px; background: #f8fafc; border-bottom: 1px solid #edf2f7; text-align: center; }
.dia-nombre { font-weight: 800; color: #1e293b; font-size: 0.9rem; }
.dia-fecha { font-size: 0.7rem; color: #94a3b8; }
.dia-stats { margin-top: 4px; font-size: 0.8rem; color: #e75480; font-weight: 600; }

.tramos-container { padding: 8px; display: flex; flex-direction: column; gap: 3px; }
.tramo-celda { height: 26px; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; transition: 0.2s; }
.tramo-vacio { background-color: #fbfcfd; border: 1px solid #f1f5f9; color: #cbd5e0; }
.tramo-vacio:hover { background-color: white; border-color: #fecdd3; color: #e75480; }
.tramo-ocupado { background-color: #e75480; color: white; font-weight: 700; box-shadow: 0 1px 3px rgba(231, 84, 128, 0.2); }

.dia-cerrado { opacity: 0.5; background: #fbfcfd; }
.mensaje-cerrado { text-align: center; padding: 20px 0; color: #bbb; font-size: 0.7rem; }
.no-data { text-align: center; padding: 80px; color: #ccc; }
.tramo-celda {
  height: 28px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center; /* Centra el contenido por defecto */
  font-size: 0.7rem;
  transition: all 0.2s;
  cursor: default;
  position: relative; /* Necesario para posicionar el badge absolutamente */
  padding: 0 5px; /* Pequeño padding lateral de seguridad */
}

/* Estilos de color (se mantienen igual) */
.tramo-vacio {
  background-color: #f8fafc;
  border: 1px solid #edf2f7;
  color: #cbd5e0;
}

.tramo-ocupado {
  background-color: #e75480; /* Tu rosa */
  color: white;
  font-weight: 700;
  box-shadow: 0 2px 4px rgba(231, 84, 128, 0.2);
}

/* La hora centrada */
.tramo-hora {
  font-family: 'Inter', sans-serif;
  letter-spacing: 0.5px;
}

/* El badge a la derecha */
.badge-cantidad {
  position: absolute; /* Lo sacamos del flujo normal */
  right: 6px; /* Lo pegamos a la derecha con un margen */
  top: 50%; /* Centrado verticalmente */
  transform: translateY(-50%); /* Ajuste preciso de centrado vertical */
  
  background: rgba(255, 255, 255, 0.35); /* Blanco muy transparente y elegante */
  color: white;
  
  border-radius: 50%; /* Forma circular */
  width: 18px; /* Compacto */
  height: 18px;
  
  display: flex;
  align-items: center;
  justify-content: center;
  
  font-size: 10px; /* Tipografía pequeña y clara */
  font-weight: 800; /* Bien negrita */
  box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.2); /* Sutil borde interno */
}

/* Botón Resumen */
.btn-resumen {
  background: #1e293b;
  color: white;
  border: none;
  padding: 8px 15px;
  border-radius: 8px;
  font-size: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  margin-right: 10px;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 25px;
  border-radius: 15px;
  width: 350px; /* Estrecho como una columna de día */
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.resumen-info {
  font-size: 0.75rem;
  color: #64748b;
  text-align: center;
  margin-bottom: 15px;
}

.resumen-columna {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.btn-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #94a3b8;
}

/* Color para el tramo menos popular (Amarillo Valle) */
.tramo-minimo {
  background-color: #fef3c7 !important; /* Amarillo suave */
  border-color: #fcd34d !important; /* Borde amarillo */
  color: #92400e !important; /* Texto marrón */
  box-shadow: 0 0 0 2px rgba(251, 191, 36, 0.2) !important;
}

/* Ajuste para el badge dentro del tramo amarillo */
.tramo-minimo .badge-cantidad {
  background: rgba(146, 64, 14, 0.15) !important;
  color: #92400e !important;
}

/* Icono valle (opcional) */
/* --- MODIFICADO: Icono Valle a la izquierda MÁS SEPARADO --- */
.tramo-icon-valle {
  position: absolute;
  left: 15px; /* ¡MÁS SEPARADO! Antes estaba a 5px u 8px */
  top: 50%;
  transform: translateY(-50%);
  font-size: 0.8rem;
  color: #f59e0b; /* Amarillo dorado */
  pointer-events: none; /* Evita que el icono interfiera con clics */
}
.header-content {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.total-badge {
  background: #f1f5f9;
  color: #475569;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  width: fit-content;
}

.total-badge i {
  color: #e75480; /* El rosa de tu marca */
}

/* Ajuste del título para que no se vea gigante */
.modal-header h3 {
  margin: 0;
  font-size: 1.1rem;
  color: #1e293b;
}

.modal-header-simple {
  text-align: center;
  position: relative;
  padding-top: 10px;
  margin-bottom: 25px;
}

/* Título */
.resumen-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 8px;
}

/* Contenedor del número (Estilo imagen) */
.total-reservas-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  margin-bottom: 15px;
}

.icon-pink {
  color: #e75480; /* Tu rosa */
  font-size: 1.2rem;
}

.total-number {
  color: #e75480; /* Tu rosa */
  font-size: 1.3rem;
  font-weight: 700;
}

/* Subtítulo gris pequeño */
.resumen-subtitle {
  font-size: 0.75rem;
  color: #64748b;
  margin: 0;
}

/* Botón cerrar en la esquina superior derecha */
.btn-close-top {
  position: absolute;
  top: -20px;
  right: -5px;
  background: none;
  border: none;
  font-size: 1.5rem;
  color: #94a3b8;
  cursor: pointer;
  padding: 5px;
}

.btn-close-top:hover {
  color: #64748b;
}

/* Color para el tramo con MÁS reservas (Azul/Violeta Eléctrico) */
.tramo-maximo {
  background-color: #4f46e5 !important; /* Un azul violeta intenso */
  border-color: #4338ca !important;
  color: white !important;
  font-weight: 800 !important;
  box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.3) !important;
}

/* Icono para la hora pico */
.tramo-icon-pico {
  position: absolute;
  left: 15px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 0.8rem;
  color: #fbbf24; /* Color fuego/amarillo para el icono sobre el fondo azul */
}

/* Ajuste del badge cuando el fondo es azul oscuro */
.tramo-maximo .badge-cantidad {
  background: rgba(255, 255, 255, 0.9) !important;
  color: #4f46e5 !important;
}
</style>