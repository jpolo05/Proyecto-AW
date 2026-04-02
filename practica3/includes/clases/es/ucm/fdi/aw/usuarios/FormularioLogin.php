<?php
namespace es\ucm\fdi\aw\usuarios;
use es\ucm\fdi\aw\Formulario;

class FormularioLogin extends Formulario
{
    public function __construct()
    {
        parent::__construct('formLogin');
    }

    protected function generaCamposFormulario(&$datos)
    {
        $nombreUsuario = $datos['nombreUsuario'] ?? '';
        $nombreUsuario = htmlspecialchars((string)$nombreUsuario, ENT_QUOTES, 'UTF-8');

        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores);
        $erroresCampos = self::generaErroresCampos(['nombreUsuario', 'password'], $this->errores, 'span', ['class' => 'error']);

        return <<<EOF
        $htmlErroresGlobales
        <div class="contenedor-login">
        
            <div class="seccion-titulo">
                <h2>Iniciar sesión</h2>
            </div>

            <div class="campo-login">
                <label for="nombreUsuario">Nombre de usuario:</label>
                <input id="nombreUsuario" type="text" name="nombreUsuario" value="$nombreUsuario">
                {$erroresCampos['nombreUsuario']}
            </div>
            
            <div class="campo-login">
                <label for="password">Contraseña:</label>
                <input id="password" type="password" name="password" placeholder="••••••••">
                {$erroresCampos['password']}
            </div>
        </div>

        <div class="botones-ordenar">
            <button type="reset" name="limpiar" class="button-estandar">Limpiar</button>
            <button type="submit" name="login" class="button-estandar">Entrar</button>
        </div>
        EOF;
    }

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];

        $nombreUsuario = trim($datos['nombreUsuario'] ?? '');
        $nombreUsuario = filter_var($nombreUsuario, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (!$nombreUsuario) {
            $this->errores['nombreUsuario'] = 'El nombre de usuario no puede estar vacio.';
        }

        $password = trim($datos['password'] ?? '');
        $password = filter_var($password, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (!$password) {
            $this->errores['password'] = 'El password no puede estar vacio.';
        }

        if (count($this->errores) === 0) {
            $usuario = Usuario::login($nombreUsuario, $password);
            if (!$usuario) {
                $this->errores[] = 'El usuario o el password no coinciden.';
                return;
            }

            session_regenerate_id(true);
            $_SESSION['login'] = true;
            $_SESSION['user'] = $usuario->getNombreUsuario();
            $_SESSION['nombre'] = $usuario->getNombre();
            $_SESSION['apellidos'] = $usuario->getApellidos();
            $_SESSION['email'] = $usuario->getEmail();
            $_SESSION['rol'] = $usuario->getRol();
            $_SESSION['imagen'] = $usuario->getImagen();

            $this->urlRedireccion = Usuario::rutaPorRol($usuario->getRol());
        }
    }
}
