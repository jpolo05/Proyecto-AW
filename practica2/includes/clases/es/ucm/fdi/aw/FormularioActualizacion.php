<?php
namespace es\ucm\fdi\aw;

class FormularioActualizacion extends Formulario
{
    public function __construct()
    {
        parent::__construct('formActualizacionUsuario');
    }

    protected function generaCamposFormulario(&$datos)
    {
        $user = $_SESSION['user'] ?? '';
        $nombre = $datos['nombre'] ?? ($_SESSION['nombre'] ?? '');
        $apellidos = $datos['apellidos'] ?? ($_SESSION['apellidos'] ?? '');
        $email = $datos['email'] ?? ($_SESSION['email'] ?? '');
        $rol = $datos['rol'] ?? ($_SESSION['rol'] ?? 'Cliente');
        $user = htmlspecialchars((string)$user, ENT_QUOTES, 'UTF-8');
        $nombre = htmlspecialchars((string)$nombre, ENT_QUOTES, 'UTF-8');
        $apellidos = htmlspecialchars((string)$apellidos, ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars((string)$email, ENT_QUOTES, 'UTF-8');
        $rol = htmlspecialchars((string)$rol, ENT_QUOTES, 'UTF-8');
        $imagen = $datos['imagen'] ?? ($_SESSION['imagen'] ?? 'default.jpg');
        $imagenBase = basename((string)$imagen);
        $isAdmin = $rol === 'Gerente';

        $editandoPass = isset($_GET['editarPass']) && $_GET['editarPass'] == 1;
        $estadoInput = $editandoPass ? '' : 'disabled';
        $estadoBoton = $editandoPass ? 'disabled' : '';
        $enlaceBoton = $editandoPass ? '' : 'actualizarUsuarios.php?editarPass=1';

        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores, 'error');
        $erroresCampos = self::generaErroresCampos(
            ['nombre', 'apellidos', 'email', 'password', 'password_confirm', 'rol'],
            $this->errores,
            'span',
            ['class' => 'error']
        );

        if (!$isAdmin) {
            $selectRol = "<label>Rol asignado: </label><span>$rol</span><input type='hidden' name='rol' value='$rol' />";
        } else {
            $selectRol = '';
            foreach (['Cliente', 'Cocinero', 'Camarero', 'Gerente'] as $r) {
                $checked = ($r === $rol) ? 'checked' : '';
                $selectRol .= "<label><input type='radio' name='rol' value='$r' $checked /> $r</label> ";
            }
        }

        $selDefault = $imagenBase === 'default.jpg' ? 'selected' : '';
        $selA1 = $imagenBase === 'avatar1.jpg' ? 'selected' : '';
        $selA2 = $imagenBase === 'avatar2.jpg' ? 'selected' : '';
        $selA3 = $imagenBase === 'avatar3.jpg' ? 'selected' : '';

        return <<<EOF
        $htmlErroresGlobales
        <fieldset>
            <legend><strong>Edita tu perfil: $user</strong></legend>
            <div>
                <label for="nombre">Nombre:</label>
                <input id="nombre" type="text" name="nombre" value="$nombre" required />
                {$erroresCampos['nombre']}
            </div>
            <div>
                <label for="apellidos">Apellidos:</label>
                <input id="apellidos" type="text" name="apellidos" value="$apellidos" required />
                {$erroresCampos['apellidos']}
            </div>
            <div>
                <label for="email">Email:</label>
                <input id="email" type="email" name="email" value="$email" required />
                {$erroresCampos['email']}
            </div>
            <div>
                <label for="password">Password (dejar en blanco para no cambiar):</label>
                <input id="password" type="password" name="password" $estadoInput />
                {$erroresCampos['password']}
            </div>
            <div>
                <label for="password_confirm">Confirme contrasena:</label>
                <input id="password_confirm" type="password" name="password_confirm" $estadoInput />
                {$erroresCampos['password_confirm']}
            </div>
            <a href="$enlaceBoton" type="button" $estadoBoton>Editar contrasena</a>
            <div>
                $selectRol
                {$erroresCampos['rol']}
            </div>
            <div>
                <label for="imagen">Imagen:</label>
                <select name="imagen" id="imagen">
                    <option value="default.jpg" $selDefault>Imagen por defecto</option>
                    <option value="avatar1.jpg" $selA1>Avatar 1</option>
                    <option value="avatar2.jpg" $selA2>Avatar 2</option>
                    <option value="avatar3.jpg" $selA3>Avatar 3</option>
                </select>
            </div>
            <div>
                <button type="reset" name="limpiar" class="button-estandar">Reset</button>
                <button type="submit" name="update" class="button-estandar">Actualizar</button>
            </div>
        </fieldset>
        EOF;
    }

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];

        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            $this->errores[] = 'Sesion no valida.';
            return;
        }

        $nombre = trim($datos['nombre'] ?? '');
        $apellidos = trim($datos['apellidos'] ?? '');
        $email = trim($datos['email'] ?? '');
        $pass1 = $datos['password'] ?? '';
        $pass2 = $datos['password_confirm'] ?? '';
        $imagen = $datos['imagen'] ?? ($_SESSION['imagen'] ?? 'default.jpg');
        $rolSesion = $_SESSION['rol'] ?? 'Cliente';
        $rol = $datos['rol'] ?? $rolSesion;

        if ($nombre === '') {
            $this->errores['nombre'] = 'El nombre es obligatorio.';
        }
        if ($apellidos === '') {
            $this->errores['apellidos'] = 'Los apellidos son obligatorios.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errores['email'] = 'Email invalido.';
        }

        if ($rolSesion !== 'Gerente') {
            $rol = $rolSesion;
        } elseif (!in_array($rol, ['Cliente', 'Cocinero', 'Camarero', 'Gerente'], true)) {
            $this->errores['rol'] = 'Rol invalido.';
        }

        if ($pass1 !== '' || $pass2 !== '') {
            if ($pass1 !== $pass2) {
                $this->errores['password_confirm'] = 'Las contrasenas no coinciden.';
            }
            $hash = password_hash($pass1, PASSWORD_DEFAULT);
        } else {
            $hash = null;
        }

        if (count($this->errores) > 0) {
            return;
        }

        $exito = Usuario::crearEditar($user, $email, $nombre, $apellidos, $hash, $rol, $imagen);
        if (!$exito) {
            $this->errores[] = 'No se pudo actualizar el usuario.';
            return;
        }

        $_SESSION['nombre'] = $nombre;
        $_SESSION['apellidos'] = $apellidos;
        $_SESSION['email'] = $email;
        $_SESSION['rol'] = $rol;
        $usuarioActualizado = Usuario::buscaUsuario($user);
        $_SESSION['imagen'] = $usuarioActualizado ? $usuarioActualizado->getImagen() : $imagen;
        $_SESSION['isAdmin'] = ($rol === 'Gerente');

        $this->urlRedireccion = RUTA_APP.'includes/vistas/usuarios/visualizarUsuarios.php';
    }
}

