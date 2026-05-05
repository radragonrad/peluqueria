<template>
  <div class="container" style="max-width: 500px; margin: 5rem auto; padding: 2rem; background: #121212; border-radius: 16px; color: white;">
    <h2 style="text-align: center; margin-bottom: 2rem;">Nueva contraseña</h2>

    <div v-if="!completado">
      <p style="color: #888; text-align: center; margin-bottom: 1.5rem;">
        Introduce tu nueva contraseña segura para acceder a tu cuenta.
      </p>

      <form @submit.prevent="restablecer">
        <input 
          type="password" 
          v-model="password" 
          placeholder="Nueva contraseña" 
          required 
          class="input-field"
          style="width: 100%; padding: 0.75rem; border: 1px solid #333; background: #1a1a1a; color: white; border-radius: 8px;"
        />

        <div v-if="password.length > 0" class="password-hints" style="margin-top: 1rem;">
          <p :class="{ 'text-success': passwordCriteria.longitud }">
            {{ passwordCriteria.longitud ? '✅' : '❌' }} Mínimo 8 caracteres
          </p>
          <!-- <p :class="{ 'text-success': passwordCriteria.mayuscula }">
            {{ passwordCriteria.mayuscula ? '✅' : '❌' }} Una mayúscula
          </p>
          <p :class="{ 'text-success': passwordCriteria.numero }">
            {{ passwordCriteria.numero ? '✅' : '❌' }} Un número
          </p>
          <p :class="{ 'text-success': passwordCriteria.especial }">
            {{ passwordCriteria.especial ? '✅' : '❌' }} Carácter especial (!@#$)
          </p> -->
        </div>

        <button 
          type="submit" 
          :class="['btn-submit', { 'btn-disabled': !passwordEsSegura || cargando }]"
          :disabled="!passwordEsSegura || cargando"
          style="margin-top: 1.5rem;">
          {{ cargando ? 'Guardando...' : 'Cambiar contraseña' }}
        </button>
      </form>
    </div>

    <div v-else style="text-align: center;">
      <p style="color: #4caf50; font-size: 1.2rem; margin-bottom: 1.5rem;">
        ¡Contraseña actualizada correctamente!
      </p>
      <button @click="irAlLogin" class="btn-submit">
        Ir al Inicio de Sesión
      </button>
    </div>

    <p v-if="error" class="error" style="color: #f44336; margin-top: 1rem; text-align: center;">{{ error }}</p>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';

export default {
  setup() {
    const route = useRoute();
    const router = useRouter();

    const password = ref('');
    const cargando = ref(false);
    const error = ref('');
    const completado = ref(false);
    const token = ref('');

    onMounted(() => {
      // Extraemos el token de la URL (?token=xxxx)
      token.value = route.query.token;
      if (!token.value) {
        error.value = "Token no válido o ausente.";
      }
    });

    const passwordCriteria = computed(() => {
      const pass = password.value || ''; 
      return {
        longitud: pass.length >= 8,
        mayuscula: /[A-Z]/.test(pass),
        numero: /[0-9]/.test(pass),
        especial: /[!@#$%^&*(),.?":{}|<>]/.test(pass)
      };
    });

    const passwordEsSegura = computed(() => {
      const c = passwordCriteria.value;
      return c.longitud;
    });

    const restablecer = async () => {
      if (!token.value) return;

      cargando.value = true;
      error.value = '';

      try {
        const res = await fetch('/backend/api/auth.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            action: 'reset_password',
            token: token.value,
            password: password.value
          })
        });

        const data = await res.json();

        if (data.success) {
          completado.value = true;
        } else {
          error.value = data.message || 'No se pudo restablecer la contraseña.';
        }
      } catch (err) {
        error.value = 'Error de conexión con el servidor.';
      } finally {
        cargando.value = false;
      }
    };

    const irAlLogin = () => router.push('/login');

    return {
      password, cargando, error, completado,
      passwordCriteria, passwordEsSegura, restablecer, irAlLogin
    };
  }
};
</script>

<style scoped>
/* Copiamos tus estilos de LoginView para mantener consistencia */
.btn-submit {
  width: 100%;
  padding: 0.75rem;
  background: #e75480;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: bold;
  cursor: pointer;
  transition: 0.3s;
}
.btn-disabled {
  background: #555 !important;
  cursor: not-allowed;
  opacity: 0.7;
}
.password-hints {
  background: #1a1a1a;
  padding: 0.8rem;
  border-radius: 8px;
  border: 1px solid #333;
}
.password-hints p {
  font-size: 0.75rem;
  margin: 0.2rem 0;
  color: #ff4d4d;
}
.password-hints .text-success {
  color: #4caf50;
}
.input-field:focus {
  outline: none;
  border-color: #e75480;
}
</style>