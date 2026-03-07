import { reactive } from 'vue';

const state = reactive({
  usuarioLogueado: false,
  emailUsuario: '',
  userId: localStorage.getItem('userId') || null,
  servicios: [],
  peluqueros: [],
  modalAbierto: false,
  servicioSeleccionado: null,
  peluqueroSeleccionado: null,
  fechaSeleccionada: null,
  horaSeleccionada: null
});

export const useStore = () => {

  const abrirModal = (servicio) => {
    state.servicioSeleccionado = servicio;
    state.modalAbierto = true;
  };

  const cerrarSesionLimpiar = () => {
    state.usuarioLogueado = false;
    state.userId = null;
    state.emailUsuario = '';
    localStorage.removeItem('userId');
    localStorage.removeItem('emailUsuario');
    localStorage.removeItem('usuarioLogueado');
  };

  const cerrarModal = () => {
    state.modalAbierto = false;
    state.peluqueroSeleccionado = null;
    state.fechaSeleccionada = null;
    state.horaSeleccionada = null;
  };

  const setUsuarioLogueado = (logado, email = '', id = null) => {
    state.usuarioLogueado = logado;
    state.userEmail = email;
    state.userId = id;

    if (logado) {
      // ESTA LÍNEA ES LA QUE ESCRIBE EN EL DISCO
      localStorage.setItem('user_session', JSON.stringify({ 
        usuarioLogueado: true, 
        email: email,
        id: id
      }));
      
    } else {
      localStorage.removeItem('user_session');
    }
  };

  const setServicios = (serviciosData) => {
    // state.servicios = serviciosData; // ✅ Ahora es reactivo
    state.servicios = [...serviciosData];
  };

  const setPeluqueros = (peluquerosData) => {
    // state.peluqueros = peluquerosData;
    state.peluqueros = [...peluquerosData];
  };

  return {
    state,
    abrirModal,
    cerrarModal,
    setUsuarioLogueado,
    setServicios,
    setPeluqueros,
    cerrarSesionLimpiar
  };
};
