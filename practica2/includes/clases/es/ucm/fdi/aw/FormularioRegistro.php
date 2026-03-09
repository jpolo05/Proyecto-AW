<?php
namespace es\ucm\fdi\aw;

class FormularioRegistro extends Formulario
{
    public function __construct()
    {
        parent::__construct('formRegistro', [
        'urlRedireccion' => RUTA_APP.'login.php',
        'enctype' => 'multipart/form-data'
    ]);
    }

    protected function generaCamposFormulario(&$datos)
    {
        $nombreUsuario = $datos['nombreUsuario'] ?? '';
        $nombre = $datos['nombre'] ?? '';
        $apellidos = $datos['apellidos'] ?? '';
        $email = $datos['email'] ?? '';
        $imagen = $datos['imagen'] ?? 'default.jpg';

        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores);
        $erroresCampos = self::generaErroresCampos(
            ['nombreUsuario', 'nombre', 'apellidos', 'email', 'password', 'password2'],
            $this->errores,
            'span',
            ['class' => 'error']
        );

        return <<<EOF
        <div class="login-container">
            $htmlErroresGlobales
            <fieldset>
                <legend>Datos Usuario</legend>
                <div>
                    <label for="nombreUsuario">Usuario:</label>
                    <input id="nombreUsuario" type="text" name="nombreUsuario" value="$nombreUsuario" required>
                    {$erroresCampos['nombreUsuario']}
                </div>
                <div>
                    <label for="nombre">Nombre:</label>
                    <input id="nombre" type="text" name="nombre" value="$nombre" required>
                    {$erroresCampos['nombre']}
                </div>
                <div>
                    <label for="apellidos">Apellidos:</label>
                    <input id="apellidos" type="text" name="apellidos" value="$apellidos" required>
                    {$erroresCampos['apellidos']}
                </div>
                <div>
                    <label for="email">Email:</label>
                    <input id="email" type="email" name="email" value="$email" required>
                    {$erroresCampos['email']}
                </div>
                <div>
                    <label for="password">Contrasena:</label>
                    <input id="password" type="password" name="password" required>
                    {$erroresCampos['password']}
                </div>
                <div>
                    <label for="password2">Reintroduce la contrasena:</label>
                    <input id="password2" type="password" name="password2" required>
                    {$erroresCampos['password2']}
                </div>
                <div>
                    <label for="imagen">Imagen:</label>
                    <select name="imagen" id="imagen">
                        <option value="default.jpg">Imagen por defecto</option>
                        <option value="avatar1.jpg">Avatar 1</option>
                        <option value="avatar2.jpg">Avatar 2</option>
                        <option value="avatar3.jpg">Avatar 3</option>
                    </select>
                </div>
                <div>
                    <label for="imagenURL">Sube tu foto:</label>
                    <input id="imagenURL" type="file" name="imagenURL">
                </div>
                <div>
                    <button type="reset" name="limpiar" class="button-estandar">Reset</button>
                    <button type="submit" name="registro" class="button-estandar">Crear cuenta</button>
                </div>
            </fieldset>
        </div>
        EOF;
    }

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];

        $nombreUsuario = trim($datos['nombreUsuario'] ?? '');
        $nombreUsuario = filter_var($nombreUsuario, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (!$nombreUsuario || mb_strlen($nombreUsuario) < 3) {
            $this->errores['nombreUsuario'] = 'El usuario debe tener al menos 3 caracteres.';
        }

        $nombre = trim($datos['nombre'] ?? '');
        $nombre = filter_var($nombre, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (!$nombre || mb_strlen($nombre) < 2) {
            $this->errores['nombre'] = 'El nombre debe tener al menos 2 caracteres.';
        }

        $apellidos = trim($datos['apellidos'] ?? '');
        $apellidos = filter_var($apellidos, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (!$apellidos || mb_strlen($apellidos) < 2) {
            $this->errores['apellidos'] = 'Los apellidos deben tener al menos 2 caracteres.';
        }

        $email = trim($datos['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errores['email'] = 'Debes introducir un email valido.';
        }

        $password = trim($datos['password'] ?? '');
        if (!$password || mb_strlen($password) < 5) {
            $this->errores['password'] = 'El password debe tener al menos 5 caracteres.';
        }

        $password2 = trim($datos['password2'] ?? '');
        if (!$password2 || $password !== $password2) {
            $this->errores['password2'] = 'Los passwords deben coincidir.';
        }

        if (count($this->errores) === 0) {
            if (Usuario::buscaUsuario($nombreUsuario)) {
                $this->errores[] = 'El usuario ya existe.';
                return;
            }

            $imagen = $datos['imagen'] ?? 'default.jpg';
            $usuario = Usuario::crea($nombreUsuario, $password, $nombre, $apellidos, $email, 'Cliente', $imagen);
            if (!$usuario) {
                $this->errores[] = 'No se pudo registrar el usuario.';
                return;
            }

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
