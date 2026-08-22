<template>
  <div class="container" style="max-width: 500px; margin: 5rem auto; padding: 2rem; background: #121212; border-radius: 16px; color: white;">
    <h2 style="text-align: center; margin-bottom: 2rem;">
      {{ modo === 'login' ? 'Iniciar sesión' : (modo === 'registro' ? 'Crear cuenta' : 'Recuperar contraseña') }}
    </h2>

    <form @submit.prevent="manejarEnvio">
      
      <div v-if="modo === 'registro'">
        <input 
          type="text" 
          v-model="nombre" 
          placeholder="Nombre completo" 
          autocomplete="name"
          required 
          class="input-field"
          style="width: 100%; padding: 0.75rem; margin-bottom: 1rem; border: 1px solid #333; background: #1a1a1a; color: white; border-radius: 8px;"
        />
        <p v-if="nombre.length > 0 && !esNombreReal(nombre)" style="color: #ff9800; font-size: 0.7rem; margin-top: -0.8rem; margin-bottom: 1rem;">
          Introduce nombre y apellido real (ej: Juan Pérez)
        </p>
        <input 
          type="tel" 
          v-model="telefono" 
          placeholder="Teléfono (ej: 600123456)" 
          autocomplete="tel"
          required 
          class="input-field"
          style="width: 100%; padding: 0.75rem; margin-bottom: 1rem; border: 1px solid #333; background: #1a1a1a; color: white; border-radius: 8px;"
        />
        <label style="display: block; font-size: 0.8rem; color: #888; margin-bottom: 0.3rem;">Fecha de nacimiento</label>
        <input 
          type="date" 
          v-model="fechaNacimiento" 
          autocomplete="fecha"
          required 
          class="input-field"
          style="width: 100%; padding: 0.75rem; margin-bottom: 1rem; border: 1px solid #333; background: #1a1a1a; color: white; border-radius: 8px;"
        />
      </div>

      <input 
        type="email" 
        v-model="email" 
        placeholder="Correo electrónico" 
        autocomplete="email"
        required 
        class="input-field"
        style="width: 100%; padding: 0.75rem; margin-bottom: 1rem; border: 1px solid #333; background: #1a1a1a; color: white; border-radius: 8px;"
      />

      <div v-if="modo !== 'recuperar'">
        <input 
          type="password" 
          v-model="password" 
          placeholder="Contraseña" 
          autocomplete="pas"
          required 
          class="input-field"
          style="width: 100%; padding: 0.75rem; border: 1px solid #333; background: #1a1a1a; color: white; border-radius: 8px;"
        />
        <div v-if="modo === 'login'" style="text-align: right; margin-top: 0.5rem;">
          <a href="#" @click.prevent="modo = 'recuperar'" style="color: #888; font-size: 0.75rem; text-decoration: none;">¿Olvidaste tu contraseña?</a>
        </div>
      </div>

      <div v-if="modo === 'registro' && password.length > 0" class="password-hints">
        <p :class="{ 'text-success': passwordCriteria?.longitud }">
          {{ passwordCriteria?.longitud ? '✅' : '❌' }} Mínimo 8 caracteres
        </p>
        <!-- <p :class="{ 'text-success': passwordCriteria?.mayuscula }">
          {{ passwordCriteria?.mayuscula ? '✅' : '❌' }} Una mayúscula
        </p>
        <p :class="{ 'text-success': passwordCriteria?.numero }">
          {{ passwordCriteria?.numero ? '✅' : '❌' }} Un número
        </p>
        <p :class="{ 'text-success': passwordCriteria?.especial }">
          {{ passwordCriteria?.especial ? '✅' : '❌' }} Carácter especial (!@#$)
        </p> -->
      </div>
      
      <button 
        type="submit" 
        :class="['btn-submit', { 'btn-disabled': !formularioValido || cargando }]"
        :disabled="!formularioValido || cargando"
        style="margin-top: 1rem;">
        {{ cargando ? 'Procesando...' : (modo === 'login' ? 'Entrar' : (modo === 'registro' ? 'Crear cuenta' : 'Enviar instrucciones')) }}
      </button>
    </form>

    <p v-if="successMessage" style="color: #4caf50; text-align: center; margin-top: 1rem; margin-bottom: 1rem;">
      {{ successMessage }}
    </p>
    <p v-if="error" class="error" style="color: #f44336; margin-top: 0.5rem; text-align: center;">{{ error }}</p>

    <p class="toggle" style="margin-top: 1.5rem; text-align: center;">
      <template v-if="modo === 'recuperar'">
        <a href="#" @click.prevent="modo = 'login'" style="color: #e75480; text-decoration: none; font-weight: bold;">Volver a Iniciar sesión</a>
      </template>
      <template v-else>
        {{ modo === 'login' ? '¿No tienes cuenta?' : '¿Ya tienes cuenta?' }}
        <a href="#" @click.prevent="cambiarModo" style="color: #e75480; text-decoration: none; font-weight: bold;">
          {{ modo === 'login' ? 'Regístrate' : 'Inicia sesión' }}
        </a>
      </template>   
    </p>
       <div class="login-footer">
      <p class="incidencia-text">
        ¿Tienes problemas para entrar?
        <router-link to="/reportar-incidencia" class="link-incidencia">
          Reportar una incidencia
        </router-link>
      </p>
    </div>
  </div>
  
</template>

<script>
import { ref, computed } from 'vue';
import { useStore } from '../store.js';
import { useRouter } from 'vue-router';

export default {
  setup() {
    const router = useRouter();
    const store = useStore();

    // Estado básico
    const modo = ref('login');
    const cargando = ref(false);
    const error = ref('');

    // Datos del formulario
    const email = ref('');
    const password = ref('');
    const nombre = ref('');
    const telefono = ref('');
    const fechaNacimiento = ref('');
    const successMessage = ref('');

    const cambiarModo = () => {
      modo.value = modo.value === 'login' ? 'registro' : 'login';
      error.value = '';
      successMessage.value = '';
    };

    const esNombreReal = (val) => {
      const nombreMinus = val.toLowerCase().trim();
      if (nombreMinus.length < 5) return false;
      const regexLetras = /^[a-zA-ZÀ-ÿ\u00f1\u00d1\s]+$/;
      if (!regexLetras.test(nombreMinus)) return false;
      // Mínimo dos palabras de al menos 2 letras
      const palabras = nombreMinus.split(/\s+/).filter(p => p.length >= 2);
      if (palabras.length < 2) return false;
      const basuraTeclado = ['asdasd', 'asdf', 'qwerty', 'zxcv'];
      if (basuraTeclado.some(b => nombreMinus.includes(b))) return false;
      if (/(.)\1\1/.test(nombreMinus)) return false;
      const prohibidos = ['admin', 'test', 'prueba', 'ficticio', 'invitado'];
      if (prohibidos.some(p => nombreMinus.includes(p))) return false;
      return true;
    };

    const telefonoValido = computed(() => {
      const tel = telefono.value.trim();
      return /^[679]\d{8}$/.test(tel);
    });

    const formularioValido = computed(() => {
      if (modo.value === 'recuperar') return email.value.includes('@');
      if (modo.value === 'login') return email.value.includes('@') && password.value.length > 0;
      
      return (
        esNombreReal(nombre.value) && 
        telefonoValido.value && 
        fechaNacimiento.value !== '' &&
        email.value.includes('@') &&
        passwordEsSegura.value
      );
    });

    const manejarEnvio = () => {
      if (modo.value === 'login') iniciarSesion();
      else if (modo.value === 'registro') registrarse();
      else enviarRecuperacion();
    };

    const iniciarSesion = async () => {
      cargando.value = true;
      error.value = '';
      try {
        const res = await fetch('/backend/api/auth.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            action: 'login',
            email: email.value,
            password: password.value
          }),
          credentials: 'include'
        });
        const data = await res.json();
        if (data.success) {
          localStorage.setItem('userId', data.id);
          localStorage.setItem('usuarioLogueado', 'true');
          localStorage.setItem('rol', data.rol);
          localStorage.setItem('usuario', data.usuario);
          localStorage.setItem('nombre', data.nombre);
          store.setUsuarioLogueado(true, data.email, data.usuario, data.id);
          router.push((data.rol === 'admin' || data.rol === 'empleado') ? '/admin' : '/servicios');
        } else {
          error.value = data.message || 'Credenciales incorrectas.';
        }
      } catch (err) {
        error.value = 'Error de conexión.';
      } finally {
        cargando.value = false;
      }
    };

    const registrarse = async () => {
      if (!esNombreReal(nombre.value)) {
        error.value = 'Por favor, introduce un nombre y apellido real.';
        return;
      }
      cargando.value = true;
      error.value = '';
      successMessage.value = '';
      try {
        const res = await fetch('/backend/api/auth.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            action: 'register',
            nombre: nombre.value,
            telefono: telefono.value,
            fecha_nacimiento: fechaNacimiento.value,
            email: email.value,
            password: password.value
          })
        });
        const data = await res.json();
        if (data.success) {
          successMessage.value = '¡Registro listo! Ya puedes logarte para pedir cita.';
          nombre.value = ''; telefono.value = ''; fechaNacimiento.value = ''; email.value = ''; password.value = '';
          modo.value = 'login';
          setTimeout(() => { successMessage.value = ''; }, 8000);
        } else {
          error.value = data.message || 'Error al registrarse.';
        }
      } catch (err) {
        error.value = 'No se pudo conectar con el servidor.';
      } finally {
        cargando.value = false;
      }
    };

    const enviarRecuperacion = async () => {
      cargando.value = true;
      error.value = '';
      successMessage.value = '';
      try {
        const res = await fetch('/backend/api/auth.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            action: 'forgot_password',
            email: email.value
          })
        });
        const data = await res.json();
        if (data.success) {
          successMessage.value = 'Si el correo existe, recibirás instrucciones para restablecer tu contraseña.';
          setTimeout(() => { modo.value = 'login'; successMessage.value = ''; }, 6000);
        } else {
          error.value = data.message || 'Error al procesar la solicitud.';
        }
      } catch (err) {
        error.value = 'Error de conexión.';
      } finally {
        cargando.value = false;
      }
    };

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
      // Solo longitud mínima, igual que lo que se muestra al usuario
      return passwordCriteria.value.longitud;
    });

    return {
      modo, email, password, nombre, telefono, fechaNacimiento,
      successMessage, cargando, error,
      cambiarModo, iniciarSesion, registrarse, manejarEnvio,
      formularioValido, passwordCriteria, esNombreReal
    };
  }
};
</script>

<style scoped>
/* Tus estilos se mantienen idénticos */
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
  margin-top: 10px;
  background: #555 !important;
  cursor: not-allowed;
  opacity: 0.7;
}

.password-hints {
  margin-top: 0.5rem;
  margin-bottom: 1rem;
  background: #1a1a1a;
  padding: 0.8rem;
  border-radius: 8px;
  border: 1px solid #333;
}

.password-hints p {
  font-size: 0.75rem;
  margin: 0.2rem 0;
  color: #ff4d4d;
  transition: color 0.3s ease;
}

.password-hints .text-success {
  color: #4caf50;
}

.input-field:focus {
  outline: none;
  border-color: #e75480;
}

/* Añade o actualiza esto en la sección style de LoginView.vue */
.login-footer {
  margin-top: 2rem;
  text-align: center;
  border-top: 1px solid #333; /* Color oscuro para que combine con tu fondo */
  padding-top: 1.5rem;
}

.incidencia-text {
  font-size: 0.85rem;
  color: #888;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  align-items: center;
}

.link-incidencia {
  color: #e75480; /* Usamos el rosa de tu botón Entrar */
  text-decoration: none;
  font-weight: bold;
  display: flex;
  align-items: center;
  gap: 0.4rem;
  transition: opacity 0.2s ease;
}

.link-incidencia:hover {
  opacity: 0.8;
  text-decoration: underline;
}

</style>