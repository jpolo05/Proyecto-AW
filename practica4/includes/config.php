<?php
use es\ucm\fdi\aw\Aplicacion; //Usa la clase Aplicacion

/**
 * Autoload (funcion sacada del ejercicio 2)
 */
spl_autoload_register(function ($class) {

    $prefix = 'es\\ucm\\fdi\\aw\\';
    $base_dir = __DIR__ . '/clases/es/ucm/fdi/aw/';

    $len = strlen($prefix);

    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);

    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

/**
 * Parámetros de conexión a la BD
 */
define('BD_HOST', 'localhost');
define('BD_NAME', 'awp4');
define('BD_USER', 'awp4');
define('BD_PASS', 'awp4');

/**
 * Parámetros de configuración utilizados para generar las URLs y las rutas a ficheros en la aplicación
 */
define('RAIZ_APP', __DIR__);
define('RUTA_APP', '/Proyecto-AW/practica4/');
define('RUTA_IMGS', RUTA_APP.'img/');
define('RUTA_CSS', RUTA_APP.'css/');
define('RUTA_JS', RUTA_APP.'js/');

/**
 * Pimienta para contraseñas
 */
define('AUTH_PASSWORD_PEPPER', 'miApp');

/**
 * Configuración del soporte de UTF-8, localización (idioma y país) y zona horaria
 */
ini_set('default_charset', 'UTF-8');
setLocale(LC_ALL, 'es_ES.UTF.8');
date_default_timezone_set('Europe/Madrid');

// Inicializa la aplicación
$app = Aplicacion::getInstance(); //No usamos new Aplicacion() ya que solo debe haber 1 objeto (instancia) de este tipo
$app->init(['host'=>BD_HOST, 'bd'=>BD_NAME, 'user'=>BD_USER, 'pass'=>BD_PASS]);

/**
 * @see http://php.net/manual/en/function.register-shutdown-function.php
 * @see http://php.net/manual/en/language.types.callable.php
 */
register_shutdown_function([$app, 'shutdown']); //Registra una funcion que se ejecutara cuando se cierre la pagina
