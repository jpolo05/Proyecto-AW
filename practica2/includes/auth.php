<?php
session_start();

function verificarAcceso($rolMinimoRequerido) {
    if (!isset($_SESSION['user'])) {
        header('Location: usuarios/login.php');
        exit;
    }

    $prioridades = [
        'Cliente'  => 1,
        'Camarero' => 2,
        'Cocinero' => 3,
        'Gerente'  => 4
    ];

    $rolUsuario = $_SESSION['rol'] ?? 'Cliente';

    if ($prioridades[$rolUsuario] < $prioridades[$rolMinimoRequerido]) {
        header('Location: error.php?error=permiso%20insuficiente');
        exit;
    }
}