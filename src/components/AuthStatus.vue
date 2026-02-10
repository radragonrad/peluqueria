<!-- js/components/AuthStatus.vue -->
<template>
  <li v-if="store.usuarioLogueado" id="auth-status">
    <span style="color:white; margin-right:10px;">{{ store.emailUsuario }}</span>
    <a href="logout.php" style="color:#e75480; text-decoration:underline;">Cerrar sesión</a>
  </li>
  <li v-else id="auth-status" class="btn-reserva">
    <a href="login.html" style="color:white;">Reserva tu cita</a>
  </li>
</template>

<script>
import { useStore } from '../store.js';

export default {
  setup() {
    const store = useStore();
    
    // Verificar autenticación al montar
    fetch('./backend/api/check_auth.php')
      .then(res => res.json())
      .then(data => {
        if (data.logged_in && data.rol === 'usuario') {
          store.setUsuarioLogueado(true, data.email);
        } else {
          store.setUsuarioLogueado(false);
        }
      })
      .catch(() => store.setUsuarioLogueado(false));

    return { store };
  }
};
</script>