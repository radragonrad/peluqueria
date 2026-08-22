-- Añade el campo de control para saber a qué clientes ya se les ha
-- pedido una reseña por email, y evitar reenvíos automáticos duplicados.
ALTER TABLE `usuarios`
  ADD COLUMN `resena_email_enviado` TINYINT(1) NOT NULL DEFAULT 0 AFTER `fecha_nacimiento`;
