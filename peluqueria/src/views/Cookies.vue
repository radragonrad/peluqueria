<template>
  <div class="legal-container">
    <div class="legal-content">
      <h1>Política de Cookies</h1>
      <p class="date">Última actualización: 14 de marzo de 2026</p>

      <section>
        <h2>¿Qué son las cookies?</h2>
        <p>
          Las cookies y tecnologías similares son documentos de texto muy pequeños o fragmentos de código que a menudo contienen un código de identificación único. Cuando visitas un sitio web o utilizas una aplicación móvil, un ordenador le pide permiso a tu ordenador o dispositivo móvil para guardar este archivo en tu ordenador o dispositivo móvil y obtener acceso a la información. La información recopilada a través de cookies y tecnologías similares puede incluir la fecha y hora de la visita y la forma en que utilizas un sitio web o una aplicación móvil en particular.
        </p>
      </section>

      <section>
        <h2>¿Por qué utilizamos cookies?</h2>
        <p>
          Las cookies aseguran que durante tu visita a nuestra tienda en línea permanezcas conectado, que todos los artículos permanezcan almacenados en tu carrito de compras, que puedas comprar de forma segura y que el sitio web siga funcionando sin problemas. Las cookies también aseguran que podamos ver cómo se utiliza nuestro sitio web y cómo podemos mejorarlo. Además, dependiendo de tus preferencias, nuestras propias cookies pueden ser utilizadas para presentarte anuncios específicos que coincidan con tus intereses personales.
        </p>
      </section>

      <section>
        <h2>Tipos de Cookies que usamos:</h2>

        <div class="cookie-category">
          <h3>Necesaria</h3>
          <p>Las cookies necesarias son cruciales para las funciones básicas del sitio web y el sitio web no funcionará de la forma prevista sin ellas. Estas cookies no almacenan ningún dato de identificación personal.</p>
          <div class="table-wrapper">
            <table>
              <thead>
                <tr>
                  <th>Cookie</th>
                  <th>Duración</th>
                  <th>Descripción</th>
                </tr>
              </thead>
              <tbody>
                <tr><td>_csrfToken</td><td>1 hora</td><td>No description available.</td></tr>
                <tr><td>INGRESSCOOKIE</td><td>sesión</td><td>Esta cookie se utiliza para equilibrar la carga y mantener la sesión.</td></tr>
                <tr><td>cookieyes-consent</td><td>1 año</td><td>Recuerda las preferencias de consentimiento de los usuarios.</td></tr>
                <tr><td>ASP.NET_SessionId</td><td>sesión</td><td>Almacena los datos de la sesión durante la visita.</td></tr>
                <tr><td>rc::a</td><td>Nunca</td><td>Google recaptcha para protección contra bots/spam.</td></tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="cookie-category">
          <h3>Funcional</h3>
          <p>Las cookies funcionales ayudan a realizar ciertas funcionalidades, como compartir el contenido del sitio web en plataformas de redes sociales.</p>
          <div class="table-wrapper">
            <table>
              <thead>
                <tr>
                  <th>Cookie</th>
                  <th>Duración</th>
                  <th>Descripción</th>
                </tr>
              </thead>
              <tbody>
                <tr><td>yt-remote-device-id</td><td>Nunca</td><td>YouTube: almacena preferencias de vídeo.</td></tr>
                <tr><td>CultureInfo</td><td>1 mes</td><td>Almacena la preferencia de idioma/cultura.</td></tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="cookie-category">
          <h3>Analítica</h3>
          <p>Ayudan a proporcionar información sobre métricas, el número de visitantes, el porcentaje de rebote, la fuente de tráfico, etc.</p>
          <div class="table-wrapper">
            <table>
              <thead>
                <tr>
                  <th>Cookie</th>
                  <th>Duración</th>
                  <th>Descripción</th>
                </tr>
              </thead>
              <tbody>
                <tr><td>_ga</td><td>1 año 1 mes</td><td>Google Analytics: cálculo de datos de visitantes y campañas.</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <section class="consent-section">
        <h2>Gestionar tus preferencias y consentimiento de Cookies</h2>
        <p>Puedes actualizar tus preferencias de consentimiento de cookies en cualquier momento clicando el botón abajo o revocar tu consentimiento.</p>
        <button class="btn-cookies" @click="openCookieSettings">
          Gestionar mis Consentimientos de Cookies 🍪🥛
        </button>
      </section>

      <section>
        <h2>Navegadores de Internet</h2>
        <p>Puedes bloquear o eliminar cookies a través de la configuración de tu navegador:</p>
        <ul class="browser-links">
          <li><a href="https://support.google.com/chrome/answer/95647" target="_blank">Chrome</a></li>
          <li><a href="https://help.apple.com/safari/mac/10.0/es.lproj/sfri11471.html" target="_blank">Safari</a></li>
          <li><a href="https://support.mozilla.org/es/kb/cookies-informacion-que-los-sitios-web-guardan-en-" target="_blank">Firefox</a></li>
          <li><a href="https://support.microsoft.com/es-es/topic/ver-las-cookies-en-microsoft-edge-a7d95376-f2cd-8e4a-25dc-1de753474879" target="_blank">Edge</a></li>
        </ul>
      </section>
    </div>
  </div>
</template>

<script setup>
const openCookieSettings = () => {
  // Aquí puedes vincular la función de tu banner de cookies (CookieYes o similar)
  console.log("Abriendo panel de cookies...");
};
</script>

<style scoped>
.legal-container {
  padding: 120px 20px 60px;
  background-color: #0f0f0f;
  min-height: 100vh;
  color: #bbb;
}
.legal-content {
  max-width: 900px;
  margin: 0 auto;
  line-height: 1.8;
}
h1 { color: #e75480; font-size: 2rem; border-bottom: 1px solid #e75480; padding-bottom: 10px; margin-bottom: 30px; }
h2 { color: #e75480; font-size: 1.4rem; margin-top: 40px; }
h3 { color: #fff; margin-bottom: 10px; }
.date { font-style: italic; color: #666; margin-bottom: 20px; }

.cookie-category { margin-bottom: 30px; }

.table-wrapper {
  overflow-x: auto;
  margin: 20px 0;
  border: 1px solid #333;
  border-radius: 8px;
}

table {
  width: 100%;
  border-collapse: collapse;
  background: #1a1a1a;
  color: #ddd;
  font-size: 0.9rem;
}

th, td {
  padding: 12px 15px;
  text-align: left;
  border-bottom: 1px solid #333;
}

th { background-color: #222; color: #e75480; }

.btn-cookies {
  background: transparent;
  border: 2px solid #e75480;
  color: #e75480;
  padding: 12px 25px;
  font-weight: bold;
  cursor: pointer;
  border-radius: 50px;
  transition: all 0.3s;
  margin: 20px 0;
}

.btn-cookies:hover {
  background: #e75480;
  color: #000;
}

.browser-links {
  display: flex;
  flex-wrap: wrap;
  gap: 15px;
  list-style: none;
  padding: 0;
}

.browser-links a {
  color: #e75480;
  text-decoration: none;
  background: #1a1a1a;
  padding: 5px 15px;
  border-radius: 4px;
}
</style>