<!-- src/components/ContactoSection.vue -->
<template>
  <section id="contacto" class="contact-section">
    <div class="container">
      <div class="contact-bg" :style="{ backgroundImage: 'url(/assets/galeria/contact-bg2.jpg)' }"></div>
      <h2>CONTACTA CON NOSOTROS</h2>
      <p>En breve te responderemos para saber más sobre tu consulta</p>

      <div class="contact-content">
        <div class="contact-info">
          <h3>Tu estilo, nuestro arte</h3>
          <p>En nuestro centro, fusionamos tradición y tendencia para ofrecerte lo mejor en cuidado personal. Desde cortes clásicos y modernos en nuestra barbería, hasta tratamientos faciales y corporales personalizados para hombres y mujeres.</p>

          <div class="contact-details">
            <div class="detail-item">
              <span>📞</span>
              <strong>657 55 33 77</strong>
            </div>
            <div class="detail-item">
              <span>✉️</span>
              <strong>rgutierrez@gmail.com</strong>
            </div>
            <div class="detail-item">
              <span>📍</span>
              <strong>C. San Jose, 9, 41808 Villanueva del Ariscal, Sevilla</strong>
            </div>
            <div class="detail-item">
              <span>🕒</span>
              <strong>Horario:</strong><br>
              Lunes a viernes: 9:30 – 20:00<br>
              Sábado: 9:30 – 14:00<br>
              Domingo: Cerrado<br>           
            </div>
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1052.2295749092325!2d-6.14085775426826!3d37.39629061744004!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xd12140844ef1a31%3A0x4432a5241f4f1a40!2sC.%20San%20Jose%2C%209%2C%2041808%20Villanueva%20del%20Ariscal%2C%20Sevilla!5e0!3m2!1ses!2ses!4v1770449941186!5m2!1ses!2ses" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
          </div>
        </div>

        <div class="contact-form-wrapper">
          <form id="contactForm" class="contact-form" action="http://localhost/peluqueria/backend/contact.php" method="POST">
            <input type="text" name="nombre" placeholder="Nombre y apellido*" required />
            <input type="tel" name="telefono" placeholder="Teléfono*" required />
            <input type="email" name="email" placeholder="Email*" required />
            <select name="servicio" required>
              <option value="" disabled selected>¿En qué servicio estás interesado?*</option>
              <option value="Corte clásico">Corte clásico</option>
              <option value="Corte + barba">Corte + barba</option>
              <option value="Arreglo de barba">Arreglo de barba</option>
              <option value="Perfilado de cejas">Perfilado de cejas</option>
              <option value="Decoloración">Decoloración</option>
              <option value="Mechas">Mechas</option>
              <option value="Servicio VIP">Servicio VIP</option>
              <option value="Otro">Otro</option>
            </select>
            <textarea name="mensaje" placeholder="Mensaje" rows="4"></textarea>
            <label class="checkbox">
              <input type="checkbox" name="privacidad" required />
              He leído y acepto la <a href="#" style="color:#e75480;">política de privacidad</a>
            </label>
            <button type="submit" class="btn">ENVIAR</button>
          </form>
        </div>
      </div>
    </div>
  </section>
</template>