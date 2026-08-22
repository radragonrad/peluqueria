<template>
  <div class="informes-container">

    <!-- ── Pantalla de inicio ── -->
    <template v-if="!seccionActiva">
      <h2>Informes</h2>
      <p class="subtitulo">Selecciona un informe para comenzar.</p>
      <div class="cards-grid">

        <button class="card-informe disponible" @click="seccionActiva = 'clientes_frecuentes'">
          <i class="fas fa-crown"></i>
          <h3>Clientes Frecuentes</h3>
          <p>Top de clientes por visitas, gasto, frecuencia y servicio favorito.</p>
          <span class="card-cta">Ver informe <i class="fas fa-arrow-right"></i></span>
        </button>

        <button class="card-informe disponible" @click="seccionActiva = 'cumpleanos'">
          <i class="fas fa-birthday-cake"></i>
          <h3>Cumpleaños</h3>
          <p>Clientes con cumpleaños hoy y próximos. Envía felicitaciones personalizadas.</p>
          <span class="card-cta">Ver cumpleaños <i class="fas fa-arrow-right"></i></span>
        </button>

        <button class="card-informe disponible" @click="seccionActiva = 'resenas'">
          <i class="fab fa-google"></i>
          <h3>Solicitar Reseñas</h3>
          <p>Clientes con visitas completadas. Pídeles una reseña en Google.</p>
          <span class="card-cta">Ver reseñas <i class="fas fa-arrow-right"></i></span>
        </button>

        <div class="card-informe proximamente">
          <i class="fas fa-file-invoice-dollar"></i>
          <h3>Ingresos por periodo</h3>
          <p>Evolución de facturación filtrable por fechas.</p>
          <span class="badge-prox">Próximamente</span>
        </div>

        <div class="card-informe proximamente">
          <i class="fas fa-user-clock"></i>
          <h3>Servicios por empleado</h3>
          <p>Rendimiento y volumen de citas por peluquero.</p>
          <span class="badge-prox">Próximamente</span>
        </div>

        <div class="card-informe proximamente">
          <i class="fas fa-star"></i>
          <h3>Servicios más solicitados</h3>
          <p>Ranking de servicios por número de reservas.</p>
          <span class="badge-prox">Próximamente</span>
        </div>

      </div>
    </template>

    <!-- ── Informe: Clientes Frecuentes ── -->
    <template v-else-if="seccionActiva === 'clientes_frecuentes'">

    <div class="informe-header">
      <button class="btn-volver" @click="seccionActiva = null">
        <i class="fas fa-arrow-left"></i> Informes
      </button>
      <h2><i class="fas fa-crown"></i> Clientes Frecuentes</h2>
    </div>

    <div class="informe-card">
      <div class="card-header">
        <div class="card-title">
          <i class="fas fa-crown"></i>
          <h3>Clientes Frecuentes</h3>
        </div>
        <div class="card-controls">
          <label>Mostrar top</label>
          <select v-model="limit" @change="cargar">
            <option :value="10">10</option>
            <option :value="20">20</option>
            <option :value="50">50</option>
            <option :value="0">Todos</option>
          </select>
          <button class="btn-leyenda" @click="mostrarLeyenda = true" title="Ver leyenda">
            <i class="fas fa-question-circle"></i>
          </button>
        </div>
      </div>

      <!-- Modal leyenda -->
      <teleport to="body">
        <div v-if="mostrarLeyenda" class="modal-overlay" @click.self="mostrarLeyenda = false">
          <div class="modal-leyenda">
            <div class="modal-header">
              <h4><i class="fas fa-book-open"></i> Leyenda de columnas</h4>
              <button class="btn-cerrar" @click="mostrarLeyenda = false">
                <i class="fas fa-times"></i>
              </button>
            </div>
            <div class="modal-body">

              <div class="seccion">
                <h5>Estado del cliente</h5>
                <div class="leyenda-item">
                  <span class="chip-estado estado-activo">Activo</span>
                  <p>Última visita hace menos de 60 días. Cliente recurrente y presente.</p>
                </div>
                <div class="leyenda-item">
                  <span class="chip-estado estado-riesgo">En riesgo</span>
                  <p>Entre 61 y 120 días sin visitar. Podría necesitar un recordatorio.</p>
                </div>
                <div class="leyenda-item">
                  <span class="chip-estado estado-perdido">Perdido</span>
                  <p>Más de 120 días sin visitar. Cliente inactivo.</p>
                </div>
              </div>

              <div class="seccion">
                <h5>Visitas y anulaciones</h5>
                <div class="leyenda-item">
                  <span class="chip chip-verde">12</span>
                  <p><strong>Visitas</strong> — número de reservas con estado <em>Completada</em>.</p>
                </div>
                <div class="leyenda-item">
                  <span class="chip chip-rojo">2</span>
                  <p><strong>Anuladas</strong> — reservas canceladas, tanto desde el local como desde la web.</p>
                </div>
                <div class="leyenda-item">
                  <span class="tasa-alta">35%</span>
                  <p><strong>% Anulación</strong> — porcentaje de anuladas sobre el total de reservas. Verde ≤10 %, naranja hasta 30 %, rojo si supera el 30 %.</p>
                </div>
              </div>

              <div class="seccion">
                <h5>Gasto</h5>
                <div class="leyenda-item">
                  <strong>Gasto total</strong>
                  <p>Suma del precio de todos los servicios completados por el cliente.</p>
                </div>
                <div class="leyenda-item">
                  <strong>Gasto medio</strong>
                  <p>Gasto total dividido entre el número de visitas completadas.</p>
                </div>
              </div>

              <div class="seccion">
                <h5>Frecuencia</h5>
                <div class="leyenda-item">
                  <strong>Frecuencia</strong>
                  <p>Intervalo medio en días entre visitas completadas consecutivas.</p>
                </div>
                <div class="leyenda-item">
                  <strong>Sin visitar</strong>
                  <p>Días transcurridos desde la última visita completada hasta hoy. El color coincide con el estado del cliente.</p>
                </div>
              </div>

              <div class="seccion">
                <h5>Preferencias</h5>
                <div class="leyenda-item">
                  <span class="tag-servicio">Corte</span>
                  <p><strong>Servicio favorito</strong> — el servicio que más veces ha reservado, independientemente del estado de la cita.</p>
                </div>
                <div class="leyenda-item">
                  <span class="tag-peluquero">Rubén</span>
                  <p><strong>Peluquero</strong> — el empleado que más veces le ha atendido en citas completadas.</p>
                </div>
              </div>

              <div class="seccion">
                <h5>Fidelidad</h5>
                <div class="leyenda-item">
                  <span class="chip chip-cupones">3</span>
                  <p><strong>Cupones</strong> — sellos disponibles actualmente en el programa de fidelidad (no incluye los ya canjeados).</p>
                </div>
              </div>

            </div>
          </div>
        </div>
      </teleport>

      <!-- Resumen KPIs -->
      <div v-if="!cargando && !error && clientes.length" class="kpis">
        <div class="kpi">
          <span class="kpi-valor">{{ clientes.length }}</span>
          <span class="kpi-label">Clientes</span>
        </div>
        <div class="kpi">
          <span class="kpi-valor">{{ totalVisitas }}</span>
          <span class="kpi-label">Visitas totales</span>
        </div>
        <div class="kpi">
          <span class="kpi-valor">{{ formatEuro(gastoTotal) }}</span>
          <span class="kpi-label">Facturación acumulada</span>
        </div>
        <div class="kpi">
          <span class="kpi-valor kpi-riesgo">{{ clientesEnRiesgo }}</span>
          <span class="kpi-label">En riesgo / perdidos</span>
        </div>
      </div>

      <div v-if="cargando" class="estado-carga">
        <i class="fas fa-spinner fa-spin"></i> Cargando...
      </div>

      <div v-else-if="error" class="estado-error">
        <i class="fas fa-exclamation-circle"></i> {{ error }}
      </div>

      <div v-else-if="clientes.length === 0" class="estado-vacio">
        <i class="fas fa-users"></i>
        <p>No hay datos de clientes aún.</p>
      </div>

      <div v-else class="tabla-wrapper">
        <table>
          <thead>
            <tr>
              <th class="col-pos">#</th>
              <th>Cliente</th>
              <th class="col-estado">Estado</th>
              <th class="col-num sortable" @click="ordenarPor('completadas')">
                Visitas <i :class="iconOrden('completadas')"></i>
              </th>
              <th class="col-num hide-sm">Anuladas</th>
              <th class="col-num hide-sm sortable" @click="ordenarPor('tasa_anulacion')">
                % Anul. <i :class="iconOrden('tasa_anulacion')"></i>
              </th>
              <th class="col-money sortable" @click="ordenarPor('gasto_total')">
                Gasto total <i :class="iconOrden('gasto_total')"></i>
              </th>
              <th class="col-money hide-md">Gasto medio</th>
              <th class="col-freq hide-md">Frecuencia</th>
              <th class="col-freq hide-md sortable" @click="ordenarPor('dias_sin_visitar')">
                Sin visitar <i :class="iconOrden('dias_sin_visitar')"></i>
              </th>
              <th class="hide-lg">Servicio favorito</th>
              <th class="hide-lg">Peluquero</th>
              <th class="col-num hide-lg">Cupones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(c, i) in clientesOrdenados" :key="c.id" :class="{ top3: sortBy === 'completadas' && i < 3 }">

              <td class="col-pos">
                <span class="badge-pos" :class="`pos-${i + 1}`">{{ i + 1 }}</span>
              </td>

              <td>
                <div class="cliente-info">
                  <span class="cliente-nombre">{{ c.nombre }}</span>
                  <span class="cliente-tel">{{ c.telefono || '—' }}</span>
                </div>
              </td>

              <td class="col-estado">
                <span class="chip-estado" :class="estadoClase(c.dias_sin_visitar)">
                  {{ estadoLabel(c.dias_sin_visitar) }}
                </span>
              </td>

              <td class="col-num">
                <span class="chip chip-verde">{{ c.completadas }}</span>
              </td>

              <td class="col-num hide-sm">
                <span class="chip chip-rojo">{{ c.anuladas }}</span>
              </td>

              <td class="col-num hide-sm">
                <span :class="tasaClase(tasaAnulacion(c))">
                  {{ tasaAnulacion(c) }}%
                </span>
              </td>

              <td class="col-money">
                <strong>{{ formatEuro(c.gasto_total) }}</strong>
              </td>

              <td class="col-money hide-md">
                {{ formatEuro(c.gasto_medio) }}
              </td>

              <td class="col-freq hide-md">
                <span v-if="c.frecuencia_dias">cada {{ c.frecuencia_dias }}d</span>
                <span v-else class="text-muted">—</span>
              </td>

              <td class="col-freq hide-md">
                <span v-if="c.dias_sin_visitar !== null" :class="diasClase(c.dias_sin_visitar)">
                  {{ c.dias_sin_visitar }}d
                </span>
                <span v-else class="text-muted">—</span>
              </td>

              <td class="hide-lg">
                <span v-if="c.servicio_favorito" class="tag-servicio">{{ c.servicio_favorito }}</span>
                <span v-else class="text-muted">—</span>
              </td>

              <td class="hide-lg">
                <span v-if="c.peluquero_favorito" class="tag-peluquero">{{ c.peluquero_favorito }}</span>
                <span v-else class="text-muted">—</span>
              </td>

              <td class="col-num hide-lg">
                <span v-if="c.cupones_actuales > 0" class="chip chip-cupones">
                  {{ c.cupones_actuales }}
                </span>
                <span v-else class="text-muted">—</span>
              </td>

            </tr>
          </tbody>
        </table>
      </div>
    </div>

    </template><!-- fin clientes_frecuentes -->

    <!-- ── Sección: Cumpleaños ── -->
    <template v-else-if="seccionActiva === 'cumpleanos'">

      <div class="informe-header">
        <button class="btn-volver" @click="seccionActiva = null">
          <i class="fas fa-arrow-left"></i> Informes
        </button>
        <h2><i class="fas fa-birthday-cake"></i> Cumpleaños</h2>
      </div>

      <!-- Toast -->
      <teleport to="body">
        <div v-if="toast.msg" class="toast-cumpl" :class="'toast-' + toast.tipo">
          <i :class="toast.tipo === 'ok' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'"></i>
          {{ toast.msg }}
        </div>
      </teleport>

      <div v-if="cargandoCumpl" class="estado-carga">
        <i class="fas fa-spinner fa-spin"></i> Cargando...
      </div>
      <div v-else-if="errorCumpl" class="estado-error">
        <i class="fas fa-exclamation-circle"></i> {{ errorCumpl }}
      </div>
      <template v-else>

        <!-- Hoy -->
        <div class="informe-card">
          <div class="card-header">
            <div class="card-title">
              <i class="fas fa-birthday-cake"></i>
              <h3>Hoy &mdash; {{ fechaHoyFormateada }}</h3>
              <span v-if="cumpleanos.total_hoy > 0" class="badge-count">{{ cumpleanos.total_hoy }}</span>
            </div>
            <button
              v-if="pendientesHoy > 0"
              class="btn-enviar-todos"
              :disabled="enviandoTodos"
              @click="enviarCumpleanos(null)"
            >
              <i :class="enviandoTodos ? 'fas fa-spinner fa-spin' : 'fas fa-paper-plane'"></i>
              {{ enviandoTodos ? 'Enviando...' : `Enviar a todos (${pendientesHoy})` }}
            </button>
          </div>

          <div v-if="!cumpleanos.hoy.length" class="estado-vacio">
            <i class="fas fa-birthday-cake"></i>
            <p>No hay cumpleaños hoy.</p>
          </div>
          <div v-else class="tabla-wrapper">
            <table>
              <thead>
                <tr>
                  <th>Cliente</th>
                  <th class="col-num">Edad</th>
                  <th class="col-email">Email</th>
                  <th class="col-estado">Estado email</th>
                  <th class="col-accion"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="u in cumpleanos.hoy" :key="u.id">
                  <td>
                    <div class="cliente-info">
                      <span class="cliente-nombre">{{ u.nombre }}</span>
                      <span class="cliente-tel">{{ u.telefono || '—' }}</span>
                    </div>
                  </td>
                  <td class="col-num">{{ u.edad }} años</td>
                  <td class="col-email"><span class="email-text">{{ u.email }}</span></td>
                  <td class="col-estado">
                    <span class="chip-estado" :class="u.email_enviado ? 'estado-activo' : 'estado-riesgo'">
                      <i :class="u.email_enviado ? 'fas fa-check' : 'fas fa-clock'" style="font-size:0.7rem;"></i>
                      {{ u.email_enviado ? 'Enviado' : 'Pendiente' }}
                    </span>
                  </td>
                  <td class="col-accion">
                    <button
                      v-if="!u.email_enviado"
                      class="btn-enviar-uno"
                      :disabled="enviandoIds.includes(u.id)"
                      @click="enviarCumpleanos([u.id])"
                    >
                      <i :class="enviandoIds.includes(u.id) ? 'fas fa-spinner fa-spin' : 'fas fa-paper-plane'"></i>
                      {{ enviandoIds.includes(u.id) ? '' : 'Enviar' }}
                    </button>
                    <span v-else class="text-muted" style="font-size:0.8rem;">—</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Próximos 7 días -->
        <div v-if="cumpleanos.proximos.length" class="informe-card" style="margin-top:20px;">
          <div class="card-header">
            <div class="card-title">
              <i class="fas fa-calendar-alt"></i>
              <h3>Próximos 7 días</h3>
              <span class="badge-count">{{ cumpleanos.proximos.length }}</span>
            </div>
          </div>
          <div class="tabla-wrapper">
            <table>
              <thead>
                <tr>
                  <th>Cliente</th>
                  <th class="col-num">Fecha</th>
                  <th class="col-num">Cumple</th>
                  <th class="col-email">Email</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="u in cumpleanos.proximos" :key="u.id">
                  <td>
                    <div class="cliente-info">
                      <span class="cliente-nombre">{{ u.nombre }}</span>
                      <span class="cliente-tel">{{ u.telefono || '—' }}</span>
                    </div>
                  </td>
                  <td class="col-num"><strong>{{ u.dia_mes }}</strong></td>
                  <td class="col-num">{{ u.edad }} años</td>
                  <td class="col-email"><span class="email-text">{{ u.email }}</span></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </template>
    </template><!-- fin cumpleanos -->

    <!-- ── Sección: Solicitar Reseñas ── -->
    <template v-else-if="seccionActiva === 'resenas'">

      <div class="informe-header">
        <button class="btn-volver" @click="seccionActiva = null">
          <i class="fas fa-arrow-left"></i> Informes
        </button>
        <h2><i class="fab fa-google"></i> Solicitar Reseñas</h2>
      </div>

      <!-- Toast -->
      <teleport to="body">
        <div v-if="toastResena.msg" class="toast-cumpl" :class="'toast-' + toastResena.tipo">
          <i :class="toastResena.tipo === 'ok' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'"></i>
          {{ toastResena.msg }}
        </div>
      </teleport>

      <div v-if="cargandoResenas" class="estado-carga">
        <i class="fas fa-spinner fa-spin"></i> Cargando...
      </div>
      <div v-else-if="errorResenas" class="estado-error">
        <i class="fas fa-exclamation-circle"></i> {{ errorResenas }}
      </div>
      <template v-else>

        <div class="informe-card">
          <div class="card-header">
            <div class="card-title">
              <i class="fab fa-google"></i>
              <h3>Clientes con visitas completadas</h3>
              <span v-if="clientesResena.length" class="badge-count">{{ clientesResena.length }}</span>
            </div>
            <button
              v-if="pendientesResena > 0"
              class="btn-enviar-todos"
              :disabled="enviandoTodosResena"
              @click="enviarResena(null)"
            >
              <i :class="enviandoTodosResena ? 'fas fa-spinner fa-spin' : 'fas fa-paper-plane'"></i>
              {{ enviandoTodosResena ? 'Enviando...' : `Enviar a todos (${pendientesResena})` }}
            </button>
          </div>

          <div v-if="!clientesResena.length" class="estado-vacio">
            <i class="fab fa-google"></i>
            <p>No hay clientes con visitas completadas todavía.</p>
          </div>
          <div v-else class="tabla-wrapper">
            <table>
              <thead>
                <tr>
                  <th>Cliente</th>
                  <th class="col-num">Visitas</th>
                  <th class="col-num">Última visita</th>
                  <th class="col-email">Email</th>
                  <th class="col-estado">Estado email</th>
                  <th class="col-accion"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="u in clientesResena" :key="u.id">
                  <td>
                    <div class="cliente-info">
                      <span class="cliente-nombre">{{ u.nombre }}</span>
                      <span class="cliente-tel">{{ u.telefono || '—' }}</span>
                    </div>
                  </td>
                  <td class="col-num">{{ u.visitas_completadas }}</td>
                  <td class="col-num">{{ formatFecha(u.ultima_visita) }}</td>
                  <td class="col-email"><span class="email-text">{{ u.email }}</span></td>
                  <td class="col-estado">
                    <span class="chip-estado" :class="u.resena_email_enviado ? 'estado-activo' : 'estado-riesgo'">
                      <i :class="u.resena_email_enviado ? 'fas fa-check' : 'fas fa-clock'" style="font-size:0.7rem;"></i>
                      {{ u.resena_email_enviado ? 'Enviado' : 'Pendiente' }}
                    </span>
                  </td>
                  <td class="col-accion">
                    <button
                      class="btn-enviar-uno"
                      :disabled="enviandoIdsResena.includes(u.id)"
                      @click="enviarResena([u.id])"
                    >
                      <i :class="enviandoIdsResena.includes(u.id) ? 'fas fa-spinner fa-spin' : 'fas fa-paper-plane'"></i>
                      {{ enviandoIdsResena.includes(u.id) ? '' : (u.resena_email_enviado ? 'Reenviar' : 'Enviar') }}
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </template>
    </template><!-- fin resenas -->

  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const seccionActiva = ref(null);
const clientes = ref([]);
const cargando = ref(true);
const error = ref(null);
const limit = ref(20);
const mostrarLeyenda = ref(false);
const sortBy = ref('completadas');
const sortDir = ref('desc');

const cargar = async () => {
  cargando.value = true;
  error.value = null;
  try {
    const res = await fetch(`/backend/api/get_clientes_frecuentes.php?limit=${limit.value}`, {
      credentials: 'include'
    });
    const data = await res.json();
    if (!data.success) throw new Error(data.error || 'Error desconocido');
    clientes.value = data.clientes;
  } catch (e) {
    error.value = e.message;
  } finally {
    cargando.value = false;
  }
};

// --- KPIs agregados ---
const totalVisitas = computed(() =>
  clientes.value.reduce((s, c) => s + Number(c.completadas), 0)
);
const gastoTotal = computed(() =>
  clientes.value.reduce((s, c) => s + Number(c.gasto_total), 0)
);
const clientesEnRiesgo = computed(() =>
  clientes.value.filter(c => c.dias_sin_visitar > 60).length
);

// --- Helpers ---
const formatEuro = (v) =>
  Number(v).toLocaleString('es-ES', { style: 'currency', currency: 'EUR' });

const tasaAnulacion = (c) => {
  if (!c.total_reservas) return 0;
  return Math.round((c.anuladas / c.total_reservas) * 100);
};

const estadoLabel = (dias) => {
  if (dias === null) return '—';
  if (dias <= 60) return 'Activo';
  if (dias <= 120) return 'En riesgo';
  return 'Perdido';
};

const estadoClase = (dias) => {
  if (dias === null) return '';
  if (dias <= 60) return 'estado-activo';
  if (dias <= 120) return 'estado-riesgo';
  return 'estado-perdido';
};

const diasClase = (dias) => {
  if (dias <= 60) return 'dias-ok';
  if (dias <= 120) return 'dias-riesgo';
  return 'dias-perdido';
};

const tasaClase = (tasa) => {
  if (tasa <= 10) return 'tasa-ok';
  if (tasa <= 30) return 'tasa-media';
  return 'tasa-alta';
};

// --- Ordenación ---
const ordenarPor = (campo) => {
  if (sortBy.value === campo) {
    sortDir.value = sortDir.value === 'desc' ? 'asc' : 'desc';
  } else {
    sortBy.value = campo;
    sortDir.value = 'desc';
  }
};

const iconOrden = (campo) => {
  if (sortBy.value !== campo) return 'fas fa-sort sort-inactivo';
  return sortDir.value === 'desc' ? 'fas fa-sort-down sort-activo' : 'fas fa-sort-up sort-activo';
};

const clientesOrdenados = computed(() => {
  const campo = sortBy.value;
  const dir = sortDir.value === 'desc' ? -1 : 1;

  return [...clientes.value].sort((a, b) => {
    let va, vb;

    if (campo === 'tasa_anulacion') {
      va = tasaAnulacion(a);
      vb = tasaAnulacion(b);
    } else {
      va = Number(a[campo]) ?? 0;
      vb = Number(b[campo]) ?? 0;
    }

    if (va === vb) return 0;
    return va > vb ? dir : -dir;
  });
});

// ─── Sección Cumpleaños ───────────────────────────────────────────────────────
const cumpleanos    = ref({ hoy: [], proximos: [], total_hoy: 0, pendientes_hoy: 0 });
const cargandoCumpl = ref(false);
const errorCumpl    = ref(null);
const enviandoIds   = ref([]);
const enviandoTodos = ref(false);
const toast         = ref({ msg: '', tipo: '' });
let toastTimer      = null;

const fechaHoyFormateada = computed(() =>
  new Date().toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
);

const pendientesHoy = computed(() =>
  (cumpleanos.value.hoy || []).filter(u => !u.email_enviado).length
);

const cargarCumpleanos = async () => {
  cargandoCumpl.value = true;
  errorCumpl.value = null;
  try {
    const res = await fetch('/backend/api/get_cumpleanos.php', { credentials: 'include' });
    const data = await res.json();
    if (!data.success) throw new Error(data.error || 'Error desconocido');
    cumpleanos.value = data;
  } catch (e) {
    errorCumpl.value = e.message;
  } finally {
    cargandoCumpl.value = false;
  }
};

const mostrarToast = (msg, tipo = 'ok') => {
  if (toastTimer) clearTimeout(toastTimer);
  toast.value = { msg, tipo };
  toastTimer = setTimeout(() => { toast.value = { msg: '', tipo: '' }; }, 4000);
};

const enviarCumpleanos = async (ids = null) => {
  if (ids) {
    ids.forEach(id => enviandoIds.value.push(id));
  } else {
    enviandoTodos.value = true;
  }
  try {
    const body = ids
      ? { mode: 'seleccionados', ids }
      : { mode: 'todos' };
    const res = await fetch('/backend/api/cumpleanos_cron.php', {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body),
    });
    const data = await res.json();
    if (!data.success) throw new Error(data.error || 'Error al enviar');
    const n = data.enviados;
    mostrarToast(
      n === 0
        ? 'No había emails pendientes'
        : `${n} email${n !== 1 ? 's' : ''} enviado${n !== 1 ? 's' : ''} correctamente`
    );
    await cargarCumpleanos();
  } catch (e) {
    mostrarToast(e.message, 'error');
  } finally {
    if (ids) {
      ids.forEach(id => {
        const idx = enviandoIds.value.indexOf(id);
        if (idx > -1) enviandoIds.value.splice(idx, 1);
      });
    } else {
      enviandoTodos.value = false;
    }
  }
};

// ─── Sección Reseñas ──────────────────────────────────────────────────────────
const clientesResena       = ref([]);
const cargandoResenas      = ref(false);
const errorResenas         = ref(null);
const enviandoIdsResena    = ref([]);
const enviandoTodosResena  = ref(false);
const toastResena          = ref({ msg: '', tipo: '' });
let toastResenaTimer       = null;

const pendientesResena = computed(() =>
  clientesResena.value.filter(u => !u.resena_email_enviado).length
);

const formatFecha = (f) => {
  if (!f) return '—';
  return new Date(f).toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

const cargarResenas = async () => {
  cargandoResenas.value = true;
  errorResenas.value = null;
  try {
    const res = await fetch('/backend/api/get_clientes_resena.php', { credentials: 'include' });
    const data = await res.json();
    if (!data.success) throw new Error(data.error || 'Error desconocido');
    clientesResena.value = data.clientes;
  } catch (e) {
    errorResenas.value = e.message;
  } finally {
    cargandoResenas.value = false;
  }
};

const mostrarToastResena = (msg, tipo = 'ok') => {
  if (toastResenaTimer) clearTimeout(toastResenaTimer);
  toastResena.value = { msg, tipo };
  toastResenaTimer = setTimeout(() => { toastResena.value = { msg: '', tipo: '' }; }, 4000);
};

const enviarResena = async (ids = null) => {
  if (ids) {
    ids.forEach(id => enviandoIdsResena.value.push(id));
  } else {
    enviandoTodosResena.value = true;
  }
  try {
    const body = ids
      ? { mode: 'seleccionados', ids }
      : { mode: 'todos' };
    const res = await fetch('/backend/api/enviar_resena.php', {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body),
    });
    const data = await res.json();
    if (!data.success) throw new Error(data.error || 'Error al enviar');
    const n = data.enviados;
    mostrarToastResena(
      n === 0
        ? 'No había emails pendientes'
        : `${n} email${n !== 1 ? 's' : ''} enviado${n !== 1 ? 's' : ''} correctamente`
    );
    await cargarResenas();
  } catch (e) {
    mostrarToastResena(e.message, 'error');
  } finally {
    if (ids) {
      ids.forEach(id => {
        const idx = enviandoIdsResena.value.indexOf(id);
        if (idx > -1) enviandoIdsResena.value.splice(idx, 1);
      });
    } else {
      enviandoTodosResena.value = false;
    }
  }
};

watch(seccionActiva, (val) => {
  if (val === 'clientes_frecuentes' && !clientes.value.length) cargar();
  if (val === 'cumpleanos') cargarCumpleanos();
  if (val === 'resenas') cargarResenas();
});
</script>

<style scoped>
.informes-container {
  padding: 24px;
  width: 100%;
  box-sizing: border-box;
}

h2 {
  font-size: 1.5rem;
  color: #1a1a1a;
  margin-bottom: 20px;
}

/* --- Tarjeta --- */
.informe-card {
  background: white;
  border-radius: 14px;
  box-shadow: 0 2px 12px rgba(0,0,0,0.07);
  overflow: hidden;
}

.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 24px;
  border-bottom: 1px solid #f1f5f9;
  flex-wrap: wrap;
  gap: 12px;
}

.card-title {
  display: flex;
  align-items: center;
  gap: 10px;
}

.card-title i { font-size: 1.2rem; color: #e75480; }
.card-title h3 { margin: 0; font-size: 1.1rem; color: #1a1a1a; }

.card-controls {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.9rem;
  color: #64748b;
}

.card-controls select {
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 5px 10px;
  font-size: 0.9rem;
  cursor: pointer;
  outline: none;
}

/* --- KPIs --- */
.kpis {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  border-bottom: 1px solid #f1f5f9;
}

.kpi {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 18px 12px;
  border-right: 1px solid #f1f5f9;
  gap: 4px;
}

.kpi:last-child { border-right: none; }

.kpi-valor {
  font-size: 1.4rem;
  font-weight: 700;
  color: #1e293b;
}

.kpi-riesgo { color: #f59e0b; }

.kpi-label {
  font-size: 0.75rem;
  color: #94a3b8;
  text-align: center;
}

/* --- Estados de carga --- */
.estado-carga,
.estado-error,
.estado-vacio {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 48px 24px;
  color: #94a3b8;
}

.estado-error { color: #ef4444; }
.estado-vacio i { font-size: 2rem; }

/* --- Tabla --- */
.tabla-wrapper {
  overflow-x: auto;
  width: 100%;
}

table {
  min-width: 1000px;
  width: 100%;
  border-collapse: collapse;
  font-size: 0.88rem;
}

thead th {
  background: #f8f9fa;
  color: #64748b;
  font-weight: 600;
  padding: 11px 14px;
  text-align: left;
  white-space: nowrap;
  text-transform: uppercase;
  font-size: 0.72rem;
  letter-spacing: 0.04em;
  user-select: none;
}

thead th.sortable {
  cursor: pointer;
}

thead th.sortable:hover {
  color: #e75480;
  background: #fdf2f6;
}

.sort-inactivo { color: #d1d5db; margin-left: 4px; }
.sort-activo   { color: #e75480; margin-left: 4px; }

tbody tr {
  border-bottom: 1px solid #f1f5f9;
  transition: background 0.15s;
}

tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: #fdf2f6; }
tbody tr.top3 { background: #fff8fb; }

td {
  padding: 12px 14px;
  color: #334155;
  vertical-align: middle;
}

/* Columnas fijas */
.col-pos  { width: 48px; text-align: center; }
.col-num  { width: 80px; text-align: center; }
.col-estado { width: 100px; }
.col-money  { width: 110px; text-align: right; white-space: nowrap; }
.col-freq   { width: 100px; text-align: center; white-space: nowrap; }

/* --- Badge posición --- */
.badge-pos {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  font-weight: 700;
  font-size: 0.8rem;
  background: #f1f5f9;
  color: #64748b;
}

.badge-pos.pos-1 { background: #ffd700; color: #7a5a00; }
.badge-pos.pos-2 { background: #c0c0c0; color: #4a4a4a; }
.badge-pos.pos-3 { background: #cd7f32; color: #fff; }

/* --- Datos cliente --- */
.cliente-info { display: flex; flex-direction: column; gap: 2px; }
.cliente-nombre { font-weight: 600; color: #1e293b; }
.cliente-tel { font-size: 0.78rem; color: #94a3b8; }

/* --- Chips genéricos --- */
.chip {
  display: inline-block;
  padding: 2px 9px;
  border-radius: 20px;
  font-weight: 700;
  font-size: 0.82rem;
}

.chip-verde   { background: #dcfce7; color: #16a34a; }
.chip-rojo    { background: #fee2e2; color: #dc2626; }
.chip-cupones { background: #fef3c7; color: #d97706; }

/* --- Chip estado --- */
.chip-estado {
  display: inline-block;
  padding: 3px 9px;
  border-radius: 20px;
  font-size: 0.78rem;
  font-weight: 600;
}

.estado-activo  { background: #dcfce7; color: #16a34a; }
.estado-riesgo  { background: #fef3c7; color: #d97706; }
.estado-perdido { background: #fee2e2; color: #dc2626; }

/* --- Días sin visitar --- */
.dias-ok      { color: #16a34a; font-weight: 600; }
.dias-riesgo  { color: #d97706; font-weight: 600; }
.dias-perdido { color: #dc2626; font-weight: 600; }

/* --- Tasa anulación --- */
.tasa-ok    { color: #16a34a; }
.tasa-media { color: #d97706; }
.tasa-alta  { color: #dc2626; font-weight: 700; }

/* --- Tags servicio / peluquero --- */
.tag-servicio {
  background: #f0f4ff;
  color: #4f46e5;
  border-radius: 6px;
  padding: 2px 8px;
  font-size: 0.8rem;
  font-weight: 500;
  white-space: nowrap;
}

.tag-peluquero {
  background: #f0fdf4;
  color: #15803d;
  border-radius: 6px;
  padding: 2px 8px;
  font-size: 0.8rem;
  font-weight: 500;
  white-space: nowrap;
}

.text-muted { color: #cbd5e1; }

/* --- Landing: cabecera del informe --- */
.informe-header {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.informe-header h2 {
  margin: 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.informe-header h2 i { color: #e75480; }

.btn-volver {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: none;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 7px 14px;
  font-size: 0.88rem;
  color: #64748b;
  cursor: pointer;
  transition: 0.2s;
  white-space: nowrap;
}

.btn-volver:hover {
  border-color: #e75480;
  color: #e75480;
}

/* --- Landing: grid de tarjetas --- */
.subtitulo {
  color: #64748b;
  margin-bottom: 24px;
  margin-top: -8px;
}

.cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 20px;
}

.card-informe {
  background: white;
  border-radius: 14px;
  padding: 28px 22px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  text-align: center;
  box-shadow: 0 2px 10px rgba(0,0,0,0.06);
  border: 2px solid transparent;
  transition: 0.2s;
}

.card-informe i {
  font-size: 2rem;
  color: #e75480;
}

.card-informe h3 {
  font-size: 1rem;
  color: #1e293b;
  margin: 0;
}

.card-informe p {
  font-size: 0.83rem;
  color: #64748b;
  margin: 0;
  line-height: 1.5;
}

.card-informe.disponible {
  cursor: pointer;
  border-color: #fce7ef;
}

.card-informe.disponible:hover {
  border-color: #e75480;
  box-shadow: 0 4px 18px rgba(231,84,128,0.15);
  transform: translateY(-2px);
}

.card-cta {
  margin-top: 4px;
  font-size: 0.82rem;
  font-weight: 600;
  color: #e75480;
  display: flex;
  align-items: center;
  gap: 5px;
}

.card-informe.proximamente {
  opacity: 0.6;
}

.badge-prox {
  font-size: 0.75rem;
  background: #f1f5f9;
  color: #94a3b8;
  border-radius: 20px;
  padding: 3px 10px;
  font-weight: 600;
}

/* --- Botón leyenda --- */
.btn-leyenda {
  background: none;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 5px 9px;
  cursor: pointer;
  color: #94a3b8;
  font-size: 1rem;
  line-height: 1;
  transition: 0.2s;
}

.btn-leyenda:hover {
  color: #e75480;
  border-color: #e75480;
}

/* --- Modal --- */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.45);
  z-index: 2000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
}

.modal-leyenda {
  background: white;
  border-radius: 14px;
  width: 100%;
  max-width: 560px;
  max-height: 85vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 8px 32px rgba(0,0,0,0.18);
  overflow: hidden;
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 18px 22px;
  border-bottom: 1px solid #f1f5f9;
  flex-shrink: 0;
}

.modal-header h4 {
  margin: 0;
  font-size: 1rem;
  color: #1e293b;
  display: flex;
  align-items: center;
  gap: 8px;
}

.modal-header h4 i { color: #e75480; }

.btn-cerrar {
  background: none;
  border: none;
  font-size: 1.1rem;
  color: #94a3b8;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 6px;
  transition: 0.2s;
}

.btn-cerrar:hover { color: #e75480; background: #fdf2f6; }

.modal-body {
  overflow-y: auto;
  padding: 20px 22px;
  display: flex;
  flex-direction: column;
  gap: 22px;
}

.seccion h5 {
  margin: 0 0 10px 0;
  font-size: 0.78rem;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: #94a3b8;
  font-weight: 700;
}

.leyenda-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 6px 0;
  border-bottom: 1px solid #f8fafc;
}

.leyenda-item:last-child { border-bottom: none; }

.leyenda-item > :first-child {
  flex-shrink: 0;
  min-width: 80px;
  text-align: center;
}

.leyenda-item p {
  margin: 0;
  font-size: 0.85rem;
  color: #475569;
  line-height: 1.5;
}

/* --- Cumpleaños: badge count --- */
.badge-count {
  background: #fce7ef;
  color: #e75480;
  border-radius: 20px;
  padding: 2px 9px;
  font-size: 0.78rem;
  font-weight: 700;
}

/* --- Cumpleaños: botón enviar todos --- */
.btn-enviar-todos {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  background: #16a34a;
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 8px 16px;
  font-size: 0.88rem;
  font-weight: 600;
  cursor: pointer;
  transition: 0.2s;
}

.btn-enviar-todos:hover:not(:disabled) {
  background: #15803d;
}

.btn-enviar-todos:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

/* --- Cumpleaños: botón enviar individual --- */
.btn-enviar-uno {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: #f0fdf4;
  color: #16a34a;
  border: 1px solid #bbf7d0;
  border-radius: 6px;
  padding: 5px 10px;
  font-size: 0.82rem;
  font-weight: 600;
  cursor: pointer;
  transition: 0.2s;
  white-space: nowrap;
}

.btn-enviar-uno:hover:not(:disabled) {
  background: #dcfce7;
}

.btn-enviar-uno:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

/* --- Columnas tabla cumpleaños --- */
.col-accion { width: 90px; text-align: center; }
.col-email  { max-width: 220px; }
.email-text {
  font-size: 0.82rem;
  color: #64748b;
  word-break: break-all;
}

/* --- Toast cumpleaños --- */
.toast-cumpl {
  position: fixed;
  bottom: 28px;
  right: 28px;
  z-index: 9999;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 14px 20px;
  border-radius: 10px;
  font-size: 0.92rem;
  font-weight: 600;
  box-shadow: 0 4px 20px rgba(0,0,0,0.15);
  animation: slide-in-toast 0.25s ease;
}

.toast-ok    { background: #16a34a; color: #fff; }
.toast-error { background: #dc2626; color: #fff; }

@keyframes slide-in-toast {
  from { opacity: 0; transform: translateY(12px); }
  to   { opacity: 1; transform: translateY(0); }
}

/* --- Responsive --- */
@media (max-width: 1100px) {
  .hide-lg { display: none; }
}

@media (max-width: 800px) {
  .hide-md { display: none; }
  .kpis { grid-template-columns: repeat(2, 1fr); }
  .kpi:nth-child(2) { border-right: none; }
}

@media (max-width: 560px) {
  .hide-sm { display: none; }
  .informes-container { padding: 12px; }
}
</style>
