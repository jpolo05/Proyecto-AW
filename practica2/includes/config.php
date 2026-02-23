<?php

/**
 * Parámetros de conexión a la BD
 */
define('BD_HOST', 'localhost');
define('BD_NAME', 'awp2');
define('BD_USER', 'root');
define('BD_PASS', '');

/**
 * Parámetros de configuración utilizados para generar las URLs y las rutas a ficheros en la aplicación
 */
define('RAIZ_APP', __DIR__);
$url_base = str_replace($_SERVER['DOCUMENT_ROOT'], '', __DIR__);
$url_base = str_replace('\\', '/', $url_base); // Normaliza barras en Windows
$url_base = rtrim($url_base, '/'); // Limpia la barra final si existe

define('RUTA_APP', $url_base);
define('RUTA_IMGS', RUTA_APP.'img/');
define('RUTA_CSS', RUTA_APP.'css/');
define('RUTA_JS', RUTA_APP.'js/');

/**
 * Configuración del soporte de UTF-8, localización (idioma y país) y zona horaria
 */
ini_set('default_charset', 'UTF-8');
setLocale(LC_ALL, 'es_ES.UTF.8');
date_default_timezone_set('Europe/Madrid');