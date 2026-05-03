<?php
namespace es\ucm\fdi\aw\usuarios;
use es\ucm\fdi\aw\Formulario; //Usa la clase Formulario

//Formulario para crear una cuenta nueva
class FormularioRegistro extends Formulario //Hereda de Formulario
{
    //Constructor
    public function __construct()
    {
        parent::__construct('formRegistro', [ ////Constructor de la clase padre
        'urlRedireccion' => RUTA_APP.'login.php',
        'enctype' => 'multipart/form-data'
        ]);
    }

    //Metodo que genera el contenido interno del formulario
    protected function generaCamposFormulario(&$datos)
    {
        //Recupera los datos ya escritos (por si ha habido algun error)
        $nombreUsuario = $datos['nombreUsuario'] ?? '';
        $nombre = $datos['nombre'] ?? '';
        $apellidos = $datos['apellidos'] ?? '';
        $email = $datos['email'] ?? '';

        //Evita introducir HTML (seguridad)
        $nombreUsuario = htmlspecialchars((string)$nombreUsuario, ENT_QUOTES, 'UTF-8');
        $nombre = htmlspecialchars((string)$nombre, ENT_QUOTES, 'UTF-8');
        $apellidos = htmlspecialchars((string)$apellidos, ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars((string)$email, ENT_QUOTES, 'UTF-8');

        $imagen = $datos['imagen'] ?? 'default.jpg'; //Guarda la imagen elegida en el formulario (o null)

        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores); //Genera los errores generales
        $erroresCampos = self::generaErroresCampos( //Prepara errores especificos para cada campo
            ['nombreUsuario', 'nombre', 'apellidos', 'email', 'password', 'password2'],
            $this->errores,
            'span',
            ['class' => 'error']
        );

        //Devuelve el HTML correspondiente
        return <<<EOF
        <div class="contenedor-login">
            $htmlErroresGlobales
            <div class="seccion-titulo">
                <h2>Registro</h2>
            </div>
            <div class="campo-login">
                <label for="nombreUsuario">Usuario:</label>
                <input id="nombreUsuario" type="text" name="nombreUsuario" value="$nombreUsuario" required>
                {$erroresCampos['nombreUsuario']}
            </div>  
            <div class="campo-login">
                <label for="nombre">Nombre:</label>
                <input id="nombre" type="text" name="nombre" value="$nombre" required>
                {$erroresCampos['nombre']}
            </div>
            <div class="campo-login">
                <label for="apellidos">Apellidos:</label>
                <input id="apellidos" type="text" name="apellidos" value="$apellidos" required>
                {$erroresCampos['apellidos']}
            </div>
            <div class="campo-login">
                <label for="email">Email:</label>
                <input id="email" type="email" name="email" value="$email" required>
                <span id="correoOk" class="indicador-correo">✔</span>
                <span id="correoMal" class="indicador-correo">❌</span>
                {$erroresCampos['email']}
            </div>
            <div class="campo-login">
                <label for="password">Contraseña:</label>
                <input id="password" type="password" name="password" required>
                {$erroresCampos['password']}
            </div>
            <div class="campo-login">
                <label for="password2">Confirmar contraseña:</label>
                <input id="password2" type="password" name="password2" required>
                {$erroresCampos['password2']}
            </div>
            <div class="campo-login">
                <label for="imagen">Imagen:</label>
                <select name="imagen" id="imagen">
                    <option value="default.jpg">Imagen por defecto</option>
                    <option value="avatar1.jpg">Avatar 1</option>
                    <option value="avatar2.jpg">Avatar 2</option>
                    <option value="avatar3.jpg">Avatar 3</option>
                </select>
            </div>
            <div class="campo-login">
                <label for="imagenURL">Sube tu foto:</label>
                <input id="imagenURL" type="file" name="imagenURL">
            </div>
        </div>
        <div class="buttons-estandar">
            <button type="reset" name="limpiar" class="button-estandar">Limpiar</button>
            <button type="submit" name="registro" class="button-estandar">Crear cuenta</button>
        </div>
        EOF;
    }

    //Metodo que se ejecuta despues de pulsar crear cuenta
    protected function procesaFormulario(&$datos)
    {
        $this->errores = []; //Vacia lista errores

        $nombreUsuario = trim($datos['nombreUsuario'] ?? ''); //Recoge el nombre de usuario enviado
        $nombreUsuario = filter_var($nombreUsuario, FILTER_SANITIZE_FULL_SPECIAL_CHARS); //Quita caracteres especiales

        if (!$nombreUsuario || mb_strlen($nombreUsuario) < 3) {
            $this->errores['nombreUsuario'] = 'El usuario debe tener al menos 3 caracteres.';
        }

        $nombre = trim($datos['nombre'] ?? ''); ////Recoge el nombre enviado
        $nombre = filter_var($nombre, FILTER_SANITIZE_FULL_SPECIAL_CHARS); //Quita caracteres especiales

        if (!$nombre || mb_strlen($nombre) < 2) {
            $this->errores['nombre'] = 'El nombre debe tener al menos 2 caracteres.';
        }

        $apellidos = trim($datos['apellidos'] ?? ''); //Recoge los apellidos enviados
        $apellidos = filter_var($apellidos, FILTER_SANITIZE_FULL_SPECIAL_CHARS); //Quita caracteres especiales

        if (!$apellidos || mb_strlen($apellidos) < 2) {
            $this->errores['apellidos'] = 'Los apellidos deben tener al menos 2 caracteres.';
        }

        $email = trim($datos['email'] ?? ''); //Recoge el email enviado

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errores['email'] = 'Debes introducir un email válido.';
        }

        $password = trim($datos['password'] ?? ''); //Recoge contraseña 1 enviada

        if (!$password || mb_strlen($password) < 5) {
            $this->errores['password'] = 'El password debe tener al menos 5 caracteres.';
        }

        $password2 = trim($datos['password2'] ?? ''); //Recoge contraseña 2 enviada

        if (!$password2 || $password !== $password2) {
            $this->errores['password2'] = 'Los passwords deben coincidir.';
        }

        $imagenFinal = $datos['imagen'] ?? 'default.jpg'; //Por defecto la imagen final sera la seleccionada en el <select>

        // Si el usuario ha subido un archivo propio, este tiene prioridad
        if (isset($_FILES['imagenURL']) && $_FILES['imagenURL']['error'] !== UPLOAD_ERR_NO_FILE) { //Comprobamos si ha habido error de subida
            if ($_FILES['imagenURL']['error'] !== UPLOAD_ERR_OK) {
                $this->errores[] = 'Error al subir la imagen.';
            } else {
                //Formatos permitidos
                $archivo = $_FILES['imagenURL'];
                $mimesPermitidos = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                ];

                //Comprobaciones
                if (!is_uploaded_file($archivo['tmp_name'])) {
                    $this->errores[] = 'Subida de fichero inválida.';
                } elseif ($archivo['size'] > 2000000) { // 2MB
                    $this->errores[] = 'La imagen es demasiado grande (maximo 2MB).';
                } else {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = $finfo ? finfo_file($finfo, $archivo['tmp_name']) : false;
                    if ($finfo) {
                        finfo_close($finfo);
                    }
                    $esImagenReal = @getimagesize($archivo['tmp_name']) !== false;

                    if ($mime === false || !isset($mimesPermitidos[$mime]) || !$esImagenReal) {
                        $this->errores[] = 'Formato de imagen no permitido (solo JPG o PNG).';
                    } else {
                        $extensionSegura = $mimesPermitidos[$mime];
                        $nuevoNombre = uniqid('img_', true) . '.' . $extensionSegura;

                        $rutaRelativaDestino = 'img/uploads/usuarios/' . $nuevoNombre;
                        $rutaDestinoFisica = dirname(RAIZ_APP) . '/' . $rutaRelativaDestino; //Calcula ruta

                        if (move_uploaded_file($archivo['tmp_name'], $rutaDestinoFisica)) {
                            $imagenFinal = $rutaRelativaDestino; //Guarda nueva ruta (nueva imagen)
                        } else {
                            $this->errores[] = 'Error al guardar la imagen. Revisa los permisos de la carpeta.';
                        }
                    }
                }
            }
        }

        if (count($this->errores) === 0) { //Comprueba que no haya habido errores

            if (Usuario::buscaUsuario($nombreUsuario)) {
                $this->errores[] = 'El usuario ya existe.';
                return;
            }

            $usuario = Usuario::crea($nombreUsuario, $password, $nombre, $apellidos, $email, 'Cliente', $imagenFinal); //Crea el nuevo usuario llamando al metodo crea

            if (!$usuario) {
                $this->errores[] = 'No se pudo registrar el usuario.';
                return;
            }

            session_regenerate_id(true);//Cambia el id de sesion (seguridad)

            //Guarda los datos de usuario en la sesion
            $_SESSION['login'] = true;
            $_SESSION['user'] = $usuario->getNombreUsuario();
            $_SESSION['nombre'] = $usuario->getNombre();
            $_SESSION['apellidos'] = $usuario->getApellidos();
            $_SESSION['email'] = $usuario->getEmail();
            $_SESSION['rol'] = $usuario->getRol();
            $_SESSION['imagen'] = $usuario->getImagen();

            $this->urlRedireccion = Usuario::rutaPorRol($usuario->getRol()); //Redirige segun rol
        }
    }
}


