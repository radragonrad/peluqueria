// js/store.js
import { reactive, readonly } from 'vue';

// Estado privado
const state = reactive({
  usuarioLogueado: false,
  emailUsuario: '',
  servicios: [],
  peluqueros: [],
  modalAbierto: false,
  servicioActual: null,
  peluqueroSeleccionado: null,
  fechaSeleccionada: null,
  horaSeleccionada: null
});

// Métodos públicos
const mutations = {
  setUsuarioLogueado(logado, email = '') {
    state.usuarioLogueado = logado;
    state.emailUsuario = email;
  },

  setServicios(servicios) {
    state.servicios = servicios;
  },

  setPeluqueros(peluqueros) {
    state.peluqueros = peluqueros;
  },

  abrirModal(servicio) {
    state.servicioActual = servicio;
    state.modalAbierto = true;
  },

  cerrarModal() {
    state.modalAbierto = false;
    state.peluqueroSeleccionado = null;
    state.fechaSeleccionada = null;
    state.horaSeleccionada = null;
  },

  seleccionarPeluquero(peluquero) {
    state.peluqueroSeleccionado = peluquero;
  },

  seleccionarFecha(fecha) {
    state.fechaSeleccionada = fecha;
  },

  seleccionarHora(hora) {
    state.horaSeleccionada = hora;
  }
};

// Exportar estado (solo lectura) + métodos
export const useStore = () => {
  return {
    ...readonly(state),
    ...mutations
  };
};