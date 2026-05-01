<?php
namespace es\ucm\fdi\aw\usuarios;

//Clase para seguridad y permisos
class Auth
{
    //Metodo para obetener el token de la sesion
    public static function getCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); //Si no existe lo crea
        }
        return $_SESSION['csrf_token'];
    }

    //Comprueba si el token recibido desde un formulario es válido
    public static function validaCsrfToken(?string $token): bool
    {
        $sessionToken = $_SESSION['csrf_token'] ?? '';
        return is_string($token) && $sessionToken !== '' && hash_equals($sessionToken, $token);
    }

    //Comprueba si el usuario tiene permiso suficiente para entrar en una página
    public static function verificarAcceso($rolMinimoRequerido)
    {
        //Comprueba si hay sesión de usuario
        if (!isset($_SESSION['user'])) {
            header('Location: '.RUTA_APP.'login.php'); //Si no la hay redirige a login
            exit;
        }

        //Jerarquia de roles (mayor numero = mas permisos)
        $prioridades = [
            'Cliente' => 1,
            'Camarero' => 2,
            'Cocinero' => 3,
            'Gerente' => 4,
        ];

        $rolUsuario = $_SESSION['rol'] ?? 'Cliente'; //Obtiene el rol del usuario actual, si no existe asume cliente

        //Comprueba que los roles sean validos
        if (!isset($prioridades[$rolUsuario]) || !isset($prioridades[$rolMinimoRequerido])) {
            header('Location: '.RUTA_APP.'error.php?error='.rawurlencode('rol inválido'));
            exit;
        }

        //Comrprueba permisos segun los numeros de la jerarquia
        if ($prioridades[$rolUsuario] < $prioridades[$rolMinimoRequerido]) {
            header('Location: '.RUTA_APP.'error.php?error=permiso%20insuficiente');
            exit;
        }
    }
}
