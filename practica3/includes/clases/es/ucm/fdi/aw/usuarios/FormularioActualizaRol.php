<?php
namespace es\ucm\fdi\aw\usuarios;
use es\ucm\fdi\aw\Formulario;

class FormularioActualizaRol extends Formulario
{
    private $usuarioObjetivo;
    private $rolActual;

    public function __construct($usuarioObjetivo, $rolActual)
    {
        $this->usuarioObjetivo = $usuarioObjetivo;
        $this->rolActual = $rolActual;
        parent::__construct('formActualizaRol_'.$usuarioObjetivo, ['urlRedireccion' => RUTA_APP.'includes/vistas/usuarios/listarUsuarios.php']);
    }

    protected function generaCamposFormulario(&$datos)
    {
        $rol = $datos['nuevoRol'] ?? $this->rolActual;
        $erroresCampos = self::generaErroresCampos(['nuevoRol'], $this->errores, 'span', ['class' => 'error']);

        $options = '';
        foreach (['Cliente', 'Cocinero', 'Camarero', 'Gerente'] as $r) {
            $selected = ($r === $rol) ? 'selected' : '';
            $options .= "<option value='$r' $selected>$r</option>";
        }

        return <<<EOF
        <input type="hidden" name="user" value="{$this->usuarioObjetivo}">
        <select name="nuevoRol">
            $options
        </select>
        {$erroresCampos['nuevoRol']}
        <button type="submit" class="button-estandar">Guardar</button>
        EOF;
    }

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];
        $rolSesion = $_SESSION['rol'] ?? '';
        if ($rolSesion !== 'Gerente') {
            $this->errores[] = 'No tienes permisos para actualizar roles.';
            return;
        }

        $rol = $datos['nuevoRol'] ?? '';

        if (!in_array($rol, ['Cliente', 'Cocinero', 'Camarero', 'Gerente'], true)) {
            $this->errores['nuevoRol'] = 'Rol no valido.';
            return;
        }

        $ok = Usuario::actualizarRol($this->usuarioObjetivo, $rol);
        if (!$ok) {
            $this->errores[] = 'No se pudo actualizar el rol.';
        }
    }
}
