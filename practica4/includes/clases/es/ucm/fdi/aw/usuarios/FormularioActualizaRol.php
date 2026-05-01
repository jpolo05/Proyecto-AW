<?php
namespace es\ucm\fdi\aw\usuarios;
use es\ucm\fdi\aw\Formulario; //Usa la clase Formulario

//Formulario para que un gerente cambie el rol de un usuario desde el listado de usuarios
class FormularioActualizaRol extends Formulario //Hereda de Formulario
{
    //Atributos privados
    private $usuarioObjetivo; //Usuario al que se le quiere cambiar el rol
    private $rolActual; //Rol actual del usuario

    //Constructor
    public function __construct($usuarioObjetivo, $rolActual)
    {
        $this->usuarioObjetivo = $usuarioObjetivo;
        $this->rolActual = $rolActual;

        //Constructor de la clase padre
        parent::__construct('formActualizaRol_'.$usuarioObjetivo, ['urlRedireccion' => RUTA_APP.'includes/vistas/usuarios/listarUsuarios.php']);
    }

    //Metodo que genera el contenido interno del formulario
    protected function generaCamposFormulario(&$datos)
    {
        $rol = $datos['nuevoRol'] ?? $this->rolActual; //Si el formulario se ha enviado y hay datos, usa nuevoRol
        $erroresCampos = self::generaErroresCampos(['nuevoRol'], $this->errores, 'span', ['class' => 'error']); //Prepara mensaje de error

        //Desplegable de roles
        $options = '';
        foreach (['Cliente', 'Cocinero', 'Camarero', 'Gerente'] as $r) {
            $selected = ($r === $rol) ? 'selected' : '';
            $options .= "<option value='$r' $selected>$r</option>";
        }

        //Devuelve el HTML correspondiente
        return <<<EOF
        <input type="hidden" name="user" value="{$this->usuarioObjetivo}">
        <select name="nuevoRol">
            $options
        </select>
        {$erroresCampos['nuevoRol']}
        <button type="submit" class="button-estandar">Guardar</button>
        EOF;
    }

    //Metodo que se ejecuta al pulsar guardar
    protected function procesaFormulario(&$datos)
    {
        $this->errores = []; //Limpia errores

        //Comprueba permisos
        $rolSesion = $_SESSION['rol'] ?? '';
        if ($rolSesion !== 'Gerente') {
            $this->errores[] = 'No tienes permisos para actualizar roles.';
            return;
        }

        $rol = $datos['nuevoRol'] ?? ''; //Obtiene el rol elegido en el desplegable

        //Comprueba que el cambio sea valido
        if (!in_array($rol, ['Cliente', 'Cocinero', 'Camarero', 'Gerente'], true)) {
            $this->errores['nuevoRol'] = 'Rol no válido.';
            return;
        }

        $ok = Usuario::actualizarRol($this->usuarioObjetivo, $rol); //Actualiza el rol
        if (!$ok) {
            $this->errores[] = 'No se pudo actualizar el rol.';
        }
    }
}
