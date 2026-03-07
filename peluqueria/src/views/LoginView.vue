<!-- src/views/LoginView.vue -->
<template>
  <div class="container" style="max-width: 500px; margin: 5rem auto; padding: 2rem; background: #121212; border-radius: 16px; color: white;">
    <h2 style="text-align: center; margin-bottom: 2rem;">{{ modo === 'login' ? 'Iniciar sesión' : 'Crear cuenta' }}</h2>

    <form @submit.prevent="modo === 'login' ? iniciarSesion() : registrarse()">
      
      <div v-if="modo === 'registro'">
        <input 
          type="text" 
          v-model="nombre" 
          placeholder="Nombre completo" 
          required 
          class="input-field"
          style="width: 100%; padding: 0.75rem; margin-bottom: 1rem; border: 1px solid #333; background: #1a1a1a; color: white; border-radius: 8px;"
        />
        <input 
          type="tel" 
          v-model="telefono" 
          placeholder="Teléfono (ej: 600123456)" 
          required 
          class="input-field"
          style="width: 100%; padding: 0.75rem; margin-bottom: 1rem; border: 1px solid #333; background: #1a1a1a; color: white; border-radius: 8px;"
        />
        <label style="display: block; font-size: 0.8rem; color: #888; margin-bottom: 0.3rem;">Fecha de nacimiento</label>
        <input 
          type="date" 
          v-model="fechaNacimiento" 
          required 
          class="input-field"
          style="width: 100%; padding: 0.75rem; margin-bottom: 1rem; border: 1px solid #333; background: #1a1a1a; color: white; border-radius: 8px;"
        />
      </div>

      <input 
        type="email" 
        v-model="email" 
        placeholder="Correo electrónico" 
        required 
        class="input-field"
        style="width: 100%; padding: 0.75rem; margin-bottom: 1rem; border: 1px solid #333; background: #1a1a1a; color: white; border-radius: 8px;"
      />
     <input 
        type="password" 
        v-model="password" 
        placeholder="Contraseña" 
        required 
        class="input-field"
        style="width: 100%; padding: 0.75rem; border: 1px solid #333; background: #1a1a1a; color: white; border-radius: 8px;"
      />

      <div v-if="modo === 'registro' && password.length > 0" class="password-hints">
        <p :class="{ 'text-success': passwordCriteria?.longitud }">
          {{ passwordCriteria?.longitud ? '✅' : '❌' }} Mínimo 8 caracteres
        </p>
        <p :class="{ 'text-success': passwordCriteria?.mayuscula }">
          {{ passwordCriteria?.mayuscula ? '✅' : '❌' }} Una mayúscula
        </p>
        <p :class="{ 'text-success': passwordCriteria?.numero }">
          {{ passwordCriteria?.numero ? '✅' : '❌' }} Un número
        </p>
        <p :class="{ 'text-success': passwordCriteria?.especial }">
          {{ passwordCriteria?.especial ? '✅' : '❌' }} Carácter especial (!@#$)
        </p>
      </div>
      
      <button 
        type="submit" 
        :class="['btn-submit', { 'btn-disabled': !formularioValido || cargando }]"
        :disabled="!formularioValido || cargando">
        {{ cargando ? 'Procesando...' : (modo === 'login' ? 'Entrar' : 'Crear cuenta') }}
      </button>
          </form>
      <p v-if="successMessage" style="color: #4caf50; text-align: center; margin-bottom: 1rem;">
        {{ successMessage }}
      </p>
    <p v-if="error" class="error" style="color: #f44336; margin-top: 0.5rem; text-align: center;">{{ error }}</p>

    <p class="toggle" style="margin-top: 1.5rem; text-align: center;">
      {{ modo === 'login' ? '¿No tienes cuenta?' : '¿Ya tienes cuenta?' }}
      <a href="#" @click.prevent="cambiarModo" style="color: #e75480; text-decoration: none; font-weight: bold;">
        {{ modo === 'login' ? 'Regístrate' : 'Inicia sesión' }}
      </a>
    </p>
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
    };

    const formularioValido = computed(() => {
      if (modo.value === 'login') {
        return email.value.includes('@') && password.value.length > 0;
      }
      return (
        nombre.value.trim().length > 2 &&
        telefono.value.trim().length >= 9 &&
        fechaNacimiento.value !== '' &&
        email.value.includes('@') &&
        passwordEsSegura.value // <--- Ahora depende de la seguridad
      );
    });

    const estilosBoton = computed(() => {
      const desactivado = cargando.value || !formularioValido.value;
      return {
        width: '100%',
        padding: '0.75rem',
        background: desactivado ? '#555' : '#e75480',
        color: 'white',
        border: 'none',
        borderRadius: '8px',
        fontWeight: 'bold',
        cursor: desactivado ? 'not-allowed' : 'pointer',
        transition: 'all 0.3s ease'
      };
    });

    const iniciarSesion = async () => {
      cargando.value = true;
      error.value = '';
      try {
        const res = await fetch('/backend/api/auth.php', { // Ajustado a tu ruta de backend
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
          
          store.setUsuarioLogueado(true, data.email, data.id);

          router.push(data.rol === 'admin' ? '/admin' : '/servicios');
        } else {
          error.value = data.message || 'Credenciales incorrectas.';
        }
      } catch (err) {
        error.value = 'Error de conexión.';
      } finally {
        cargando.value = false;
      }
    };

    // Objeto con los criterios de seguridad
    const passwordCriteria = computed(() => {
      // Inicializamos valores por defecto para que nunca sea undefined
      const pass = password.value || ''; 
      return {
        longitud: pass.length >= 8,
        mayuscula: /[A-Z]/.test(pass),
        numero: /[0-9]/.test(pass),
        especial: /[!@#$%^&*(),.?":{}|<>]/.test(pass)
      };
    });

    // Comprobar si cumple TODOS los criterios (para el botón)
    const passwordEsSegura = computed(() => {
      const c = passwordCriteria.value;
      return c.longitud && c.mayuscula && c.numero && c.especial;
    });

  const registrarse = async () => {
    cargando.value = true;
    error.value = '';
    successMessage.value = ''; // Limpiamos mensajes previos

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
        // 1. Mostrar mensaje de éxito
        successMessage.value = '¡Registro casi listo! Revisa tu email para activar la cuenta.';

        // 2. Limpiar TODOS los campos
        nombre.value = '';
        telefono.value = '';
        fechaNacimiento.value = '';
        email.value = '';    // También limpiamos el email
        password.value = '';

        // 3. Cambiar a modo login
        modo.value = 'login';

        // 4. OPCIONAL: Quitar el mensaje de éxito después de 8 segundos
        setTimeout(() => {
          successMessage.value = '';
        }, 8000);

      } else {
        error.value = data.message || 'Error al registrarse.';
      }
    } catch (err) {
      error.value = 'No se pudo conectar con el servidor.';
    } finally {
      cargando.value = false;
    }
  };
  

    return {
      modo, email, password, nombre, telefono, fechaNacimiento,
      successMessage,
      cargando, error,
      cambiarModo, iniciarSesion, registrarse, formularioValido, passwordCriteria
    };
  }
};
</script>
<style scoped>
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
  color: #ff4d4d; /* Rojo por defecto */
  transition: color 0.3s ease;
}

.password-hints .text-success {
  color: #4caf50; /* Verde cuando cumple */
}

/* Estilo para el input cuando está enfocado */
.input-field:focus {
  outline: none;
  border-color: #e75480;
}
</style>