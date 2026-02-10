<!-- src/components/Footer.vue -->
<template>
  <footer class="main-footer">
    <div class="container">
      <div class="footer-grid">
        <!-- Columna 1: Contacto -->
        <div class="footer-column">
          <h3>CONTACTO</h3>
          <ul class="contact-list">
            <li><span>📞</span> Teléfono: (+34) 657 55 33 77</li>
            <li><span>💬</span> WhatsApp: (+34) 657 55 33 77</li>          
          </ul>
        </div>

        <!-- Columna 2: Dirección -->
        <div class="footer-column">
          <h3>DIRECCIÓN</h3>
          <p>          
            C. Nueva, 11<br>
            41808  Villanueva del Ariscal (Sevilla)
          </p>
        </div>

        <!-- Columna 3: Síguenos -->
        <div class="footer-column">
          <h3>SÍGUENOS</h3>
          <ul class="social-list">
            <li>
              <a href="https://www.facebook.com/tuusuario" target="_blank" rel="noopener">
                <span>📘</span> Facebook
              </a>
            </li>
            <li>
              <a href="https://twitter.com/tuusuario" target="_blank" rel="noopener">
                <span>𝕏</span> Twitter
              </a>
            </li>
            <li>
              <a href="https://www.instagram.com/rgutierrez212" target="_blank" rel="noopener">
                <span>📸</span> Instagram
              </a>
            </li>
          </ul>
        </div>
      </div>

      <!-- Pie inferior -->
      <div class="footer-bottom">
        <p>
          SG Salón Experience © {{ currentYear }} &nbsp; • &nbsp;
          <a href="#aviso-legal">Aviso Legal</a> &nbsp; • &nbsp;
          <a href="#privacidad">Política de Privacidad</a> &nbsp; • &nbsp;
          <a href="#cookies">Aviso de Cookies</a>
        </p>
      </div>
    </div>
  </footer>
</template>

<script>
import { ref, onMounted } from 'vue';

export default {
  setup() {
    const currentYear = ref(new Date().getFullYear());

    return {
      currentYear
    };
  }
};
</script>