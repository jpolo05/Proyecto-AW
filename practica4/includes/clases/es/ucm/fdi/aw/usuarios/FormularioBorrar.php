<?php
namespace es\ucm\fdi\aw\usuarios;
use es\ucm\fdi\aw\Formulario;

class FormularioBorrar extends Formulario
{
    public function __construct()
    {
        parent::__construct('formBorrarUsuario');
    }

    protected function generaCamposFormulario(&$datos)
    {
        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores, 'error');

        return <<<EOF
        $htmlErroresGlobales
        <p>¿Estas seguro de que quieres eliminar tu cuenta para siempre?</p>
        <div>
            <button type="submit" name="borrar" class="button-estandar">Si</button>
            <button type="submit" name="cancelar" class="button-estandar">No</button>
        </div>
        EOF;
    }

    //<a href="visualizarUsuarios.php" class="button-estandar">No</a>

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];

        if (isset($datos['cancelar'])){
            $this->urlRedireccion = 'visualizarUsuarios.php';
            return;
        }

        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            $this->errores[] = 'Sesión no válida.';
            return;
        }

        $exito = Usuario::borrar($user);
        if (!$exito) {
            $this->errores[] = 'No se pudo borrar el usuario.';
            return;
        }

        session_destroy();
        $this->urlRedireccion = RUTA_APP.'registro.php';
    }
}

