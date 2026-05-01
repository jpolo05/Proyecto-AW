<?php
namespace es\ucm\fdi\aw\usuarios;
use es\ucm\fdi\aw\Formulario; //Usa la clase Formulario

//Formulario para editar el perfil de usuario
class FormularioActualizacion extends Formulario //Hereda de Formulario
{
    //Llamada al constructor de la clase padre
    public function __construct()
    {
        parent::__construct('formActualizacionUsuario', ['enctype' => 'multipart/form-data']); //multipart/form-data se usa para poder subir archivos (foto perfil)
    }

    //Genera el HTML del formulario
    protected function generaCamposFormulario(&$datos)
    {
        //Datos
        $user = $_SESSION['user'] ?? '';
        $nombre = $datos['nombre'] ?? ($_SESSION['nombre'] ?? '');
        $apellidos = $datos['apellidos'] ?? ($_SESSION['apellidos'] ?? '');
        $email = $datos['email'] ?? ($_SESSION['email'] ?? '');
        $rol = $datos['rol'] ?? ($_SESSION['rol'] ?? 'Cliente');

        //Convierte caracteres especiales antes de meterlos en HTML (seguridad)
        $user = htmlspecialchars((string)$user, ENT_QUOTES, 'UTF-8');
        $nombre = htmlspecialchars((string)$nombre, ENT_QUOTES, 'UTF-8');
        $apellidos = htmlspecialchars((string)$apellidos, ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars((string)$email, ENT_QUOTES, 'UTF-8');
        $rol = htmlspecialchars((string)$rol, ENT_QUOTES, 'UTF-8');
        $imagen = $datos['imagen'] ?? ($_SESSION['imagen'] ?? 'default.jpg');

        $imagenBase = basename((string)$imagen); //Obtiene el nombre de la imagen del usuario
        $isAdmin = $rol === 'Gerente'; //Comrpueba si es gerente

        //Controla si se puede o no editar la contraseña (para evitar cambiarla por accidente)
        $editandoPass = isset($_GET['editarPass']) && $_GET['editarPass'] == 1;
        $estadoInput = $editandoPass ? '' : 'disabled';
        $estadoBoton = $editandoPass ? 'disabled' : '';
        $enlaceBoton = $editandoPass ? '' : 'actualizarUsuarios.php?editarPass=1';

        //Manejo de errores
        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores, 'error'); //Genera los errores generales
        $erroresCampos = self::generaErroresCampos( //Genera errores para campos concretos
            ['nombre', 'apellidos', 'email', 'password', 'password_confirm', 'rol'],
            $this->errores,
            'span',
            ['class' => 'error']
        );

        //Selector de rol (si es gerente puede seleccionar roles)
        if (!$isAdmin) {
            $selectRol = "<label>Rol asignado: </label><span>$rol</span><input type='hidden' name='rol' value='$rol'>";
        } else {
            $selectRol = '';
            foreach (['Cliente', 'Cocinero', 'Camarero', 'Gerente'] as $r) {
                $checked = ($r === $rol) ? 'checked' : '';
                $selectRol .= "<label><input type='radio' name='rol' value='$r' $checked> $r</label> ";
            }
        }

        //Seleccion de imagen de avatar predeterminada
        $selDefault = $imagenBase === 'default.jpg' ? 'selected' : '';
        $selA1 = $imagenBase === 'avatar1.jpg' ? 'selected' : '';
        $selA2 = $imagenBase === 'avatar2.jpg' ? 'selected' : '';
        $selA3 = $imagenBase === 'avatar3.jpg' ? 'selected' : '';

        //Devuelve el HTML correspondiente
        return <<<EOF
        $htmlErroresGlobales
        <fieldset>
            <legend><strong>Edita tu perfil: $user</strong></legend>
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
                <label for="password">Password (dejar en blanco para no cambiar):</label>
                <input id="password" type="password" name="password" $estadoInput>
                {$erroresCampos['password']}
            </div>
            <div>
                <label for="password_confirm">Confirme contraseña:</label>
                <input id="password_confirm" type="password" name="password_confirm" $estadoInput>
                {$erroresCampos['password_confirm']}
            </div>
            <a href="$enlaceBoton" class="button-estandar" $estadoBoton>Editar contraseña</a>
            <div>
                $selectRol
                {$erroresCampos['rol']}
            </div>
            <div>
                <label for="imagen">Imagen:</label>
                <select name="imagen" id="imagen">
                    <option value="">Mantener imagen actual</option>
                    <option value="default.jpg" $selDefault>Imagen por defecto</option>
                    <option value="avatar1.jpg" $selA1>Avatar 1</option>
                    <option value="avatar2.jpg" $selA2>Avatar 2</option>
                    <option value="avatar3.jpg" $selA3>Avatar 3</option>
                </select>
            </div>
            <div>
                <label for="imagenURL">Sube tu foto:</label>
                <input id="imagenURL" type="file" name="imagenURL">
            </div>
            <div>
                <button type="reset" name="limpiar" class="button-estandar">Reset</button>
                <button type="submit" name="update" class="button-estandar">Actualizar</button>
                <button type="submit" name="cancelar" class="button-estandar">Cancelar</button>
            </div>
        </fieldset>
        EOF;
    }

    //Procesamiento tras enviar el formulario
    protected function procesaFormulario(&$datos)
    {
        $this->errores = []; //Vacia errores anteriores

        //Si se pulsa cancelar no actualiza nada
        if(isset($datos['cancelar'])){
            $this->urlRedireccion = RUTA_APP.'includes/vistas/usuarios/visualizarUsuarios.php'; //Redireccion a visualizarUsuarios
            return;
        }

        //Comprobacion de sesion (si no hay usuario en sesion no se actualiza perfil)
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            $this->errores[] = 'Sesión no válida.';
            return;
        }

        //Recoleccion de datos enviados
        $nombre = trim($datos['nombre'] ?? '');
        $apellidos = trim($datos['apellidos'] ?? '');
        $email = trim($datos['email'] ?? '');
        $pass1 = $datos['password'] ?? '';
        $pass2 = $datos['password_confirm'] ?? '';
        $imagen = $datos['imagen'] ?? ($_SESSION['imagen'] ?? 'default.jpg');
        $rolSesion = $_SESSION['rol'] ?? 'Cliente';
        $rol = $datos['rol'] ?? $rolSesion;

        //Validaciones (campos obligatorios)
        if ($nombre === '') {
            $this->errores['nombre'] = 'El nombre es obligatorio.';
        }
        if ($apellidos === '') {
            $this->errores['apellidos'] = 'Los apellidos son obligatorios.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { //Comprueba email formato valido
            $this->errores['email'] = 'Email inválido.';
        }

        //Comprobacion selector de rol (si no es gerente no puede)
        if ($rolSesion !== 'Gerente') {
            $rol = $rolSesion;
        } elseif (!in_array($rol, ['Cliente', 'Cocinero', 'Camarero', 'Gerente'], true)) {
            $this->errores['rol'] = 'Rol inválido.'; //Comprueba si el rol elegido esta permitido
        }

        //Cambio de contraseña
        if ($pass1 !== '' || $pass2 !== '') {
            if ($pass1 !== $pass2) {
                $this->errores['password_confirm'] = 'Las contraseñas no coinciden.'; //Si no coinciden
            }
            $hash = $pass1; //Cambia la contraseña
        } else {
            $hash = null; //Hash a null hace que no se cambie la contraseña
        }

        //Subida de imagen (avatar)
        if (isset($_FILES['imagenURL']) && $_FILES['imagenURL']['error'] !== UPLOAD_ERR_NO_FILE) { //Comprueba si se ha subido una imagen
            if ($_FILES['imagenURL']['error'] !== UPLOAD_ERR_OK) {
                $this->errores[] = 'Error al subir la imagen.'; //Error de subida
            } else {
                $archivo = $_FILES['imagenURL'];
                $mimesPermitidos = [ //Formatos permitidos
                    'image/jpeg' => 'jpg', 
                    'image/png' => 'png',
                ];

                //Comprobaciones
                if (!is_uploaded_file($archivo['tmp_name'])) {
                    $this->errores[] = 'Fichero de subida no valido.';
                } elseif ($archivo['size'] > 2000000) {
                    $this->errores[] = 'La imagen es demasiado grande (maximo 2MB).';
                } else {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeReal = $finfo ? finfo_file($finfo, $archivo['tmp_name']) : false;
                    if ($finfo) {
                        finfo_close($finfo);
                    }
                    if ($mimeReal === false || !isset($mimesPermitidos[$mimeReal])) {
                        $this->errores[] = 'Formato de imagen no permitido (solo JPG o PNG).';
                    } elseif (@getimagesize($archivo['tmp_name']) === false) {
                        $this->errores[] = 'El archivo subido no es una imagen valida.';
                    } else {
                        $extensionSegura = $mimesPermitidos[$mimeReal];
                        $nuevoNombre = uniqid('img_', true) . '.' . $extensionSegura;

                        $rutaRelativaDestino = 'img/uploads/usuarios/' . $nuevoNombre;
                        $rutaDestinoFisica = dirname(RAIZ_APP) . '/' . $rutaRelativaDestino;

                        if (move_uploaded_file($archivo['tmp_name'], $rutaDestinoFisica)) {
                            $imagen = $rutaRelativaDestino; //Si todo va bien se actualiza la ruta (nueva imagen)
                        } else {
                            $this->errores[] = 'Error al guardar la imagen. Revisa los permisos de la carpeta.';
                        }
                    }
                }
            }
        }

        //Si hay errores se sale (no actualiza)
        if (count($this->errores) > 0) {
            return;
        }

        //Llamada a crearEditar para guardar los cambios
        $exito = Usuario::crearEditar($user, $email, $nombre, $apellidos, $hash, $rol, $imagen);
        if (!$exito) {
            $this->errores[] = 'No se pudo actualizar el usuario.';
            return;
        }

        //Actualizacion de los datos guardados en la sesion
        $_SESSION['nombre'] = $nombre;
        $_SESSION['apellidos'] = $apellidos;
        $_SESSION['email'] = $email;
        $_SESSION['rol'] = $rol;
        $usuarioActualizado = Usuario::buscaUsuario($user);
        $_SESSION['imagen'] = $usuarioActualizado ? $usuarioActualizado->getImagen() : $imagen;
        $_SESSION['isAdmin'] = ($rol === 'Gerente');

        $this->urlRedireccion = RUTA_APP.'includes/vistas/usuarios/visualizarUsuarios.php'; //Si todo va bien redirige a visualizarUsuarios
    }
}


