<?php
namespace es\ucm\fdi\aw;

class Auth
{
    public static function verificarAcceso($rolMinimoRequerido)
    {
        if (!isset($_SESSION['user'])) {
            header('Location: '.RUTA_APP.'login.php');
            exit;
        }

        $prioridades = [
            'Cliente' => 1,
            'Camarero' => 2,
            'Cocinero' => 3,
            'Gerente' => 4,
        ];

        $rolUsuario = $_SESSION['rol'] ?? 'Cliente';

        if (!isset($prioridades[$rolUsuario]) || !isset($prioridades[$rolMinimoRequerido])) {
            header('Location: '.RUTA_APP.'error.php?error=rol%20invalido');
            exit;
        }

        if ($prioridades[$rolUsuario] < $prioridades[$rolMinimoRequerido]) {
            header('Location: '.RUTA_APP.'error.php?error=permiso%20insuficiente');
            exit;
        }
    }
}
