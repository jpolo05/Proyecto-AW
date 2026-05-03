<?php
namespace es\ucm\fdi\aw\usuarios;
use es\ucm\fdi\aw\Formulario; //Usa la clase Formulario

class FormularioLogin extends Formulario //Hereda de Formulario
{
    //Constructor
    public function __construct()
    {
        parent::__construct('formLogin'); //Constructor de la clase padre
    }

    //Metodo que genera el contenido interno del formulario
    protected function generaCamposFormulario(&$datos)
    {
        $nombreUsuario = $datos['nombreUsuario'] ?? ''; //Recupera el nombre de usuario escrito antes (por si se pone mal la contraseña)
        $nombreUsuario = htmlspecialchars((string)$nombreUsuario, ENT_QUOTES, 'UTF-8'); //Evita que se introduzca HTML (seguridad)

        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores); //Genera los errores generales
        $erroresCampos = self::generaErroresCampos(['nombreUsuario', 'password'], $this->errores, 'span', ['class' => 'error']); //Genera los errores concretos de cada campo

        //Devuelve el HTML correspondiente
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

        <div class="buttons-estandar">
            <button type="reset" name="limpiar" class="button-estandar">Limpiar</button>
            <button type="submit" name="login" class="button-estandar">Entrar</button>
        </div>
        EOF;
    }

    //Metodo que se ejecuta despues de pulsar entrar
    protected function procesaFormulario(&$datos)
    {
        $this->errores = []; //Vacia errores

        $nombreUsuario = trim($datos['nombreUsuario'] ?? ''); //Recoge el nombre de usuario enviado
        $nombreUsuario = filter_var($nombreUsuario, FILTER_SANITIZE_FULL_SPECIAL_CHARS); //Quita caracteres especiales

        if (!$nombreUsuario) {
            $this->errores['nombreUsuario'] = 'El nombre de usuario no puede estar vacio.';
        }

        $password = trim($datos['password'] ?? ''); //Recoge contraseña enviada
        $password = filter_var($password, FILTER_SANITIZE_FULL_SPECIAL_CHARS); //Quita caracteres especiales

        if (!$password) {
            $this->errores['password'] = 'El password no puede estar vacio.';
        }

        if (count($this->errores) === 0) { //Si no hay errores
            $usuario = Usuario::login($nombreUsuario, $password); //Llama al metodo login (busca el usuario en la BD)
            if (!$usuario) { //Si no encuentra al usuario en la BD
                $this->errores[] = 'El usuario o el password no coinciden.';
                return;
            }

            session_regenerate_id(true); //Cambia el id de sesion (seguridad)

            //Guarda los datos de usuario en la sesion
            $_SESSION['login'] = true;
            $_SESSION['user'] = $usuario->getNombreUsuario();
            $_SESSION['nombre'] = $usuario->getNombre();
            $_SESSION['apellidos'] = $usuario->getApellidos();
            $_SESSION['email'] = $usuario->getEmail();
            $_SESSION['rol'] = $usuario->getRol();
            $_SESSION['imagen'] = $usuario->getImagen();
            $_SESSION['bistroCoins'] = $usuario->getBistroCoins();

            $this->urlRedireccion = Usuario::rutaPorRol($usuario->getRol()); //Redirige segun rol
        }
    }
}
