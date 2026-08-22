<?php
header("Content-Type: application/xml; charset=utf-8");
require_once 'private/config/db.php'; // Tu conexión

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

// 1. Páginas estáticas
$paginas = [
    'https://rgutierrezhairstudio.com/' => '1.0',
    'https://rgutierrezhairstudio.com/servicios' => '0.9',
    'https://rgutierrezhairstudio.com/contacto' => '0.9',
    'https://rgutierrezhairstudio.com/galeria' => '0.7',
    'https://rgutierrezhairstudio.com/sobrenostros' => '0.7',
    'https://rgutierrezhairstudio.com/login' => '0.5',
];

foreach ($paginas as $url => $prioridad) {
    echo "<url><loc>$url</loc><priority>$prioridad</priority></url>";
}

// 2. Páginas de servicios (Dinámicas)
// Si en el futuro tienes páginas individuales por servicio, las añadirías aquí con un bucle de tu base de datos.

echo '</urlset>';
?>