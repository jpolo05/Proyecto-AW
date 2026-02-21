<?php
require_once 'includes/config.php';

$v = $_GET['v'] ?? 'categorias/listar';
$f = 'includes/vistas/' . $v . '.php';

if (!file_exists($f)) die("Vista no encontrada");
require $f;