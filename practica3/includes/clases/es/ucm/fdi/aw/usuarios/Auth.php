<?php
namespace es\ucm\fdi\aw\usuarios;

class Auth
{
    public static function getCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validaCsrfToken(?string $token): bool
    {
        $sessionToken = $_SESSION['csrf_token'] ?? '';
        return is_string($token) && $sessionToken !== '' && hash_equals($sessionToken, $token);
    }

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
