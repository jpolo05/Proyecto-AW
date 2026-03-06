<?php
namespace es\ucm\fdi\aw;

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
            <a href="perfil.php"><button type="button" class="button-estandar">No</button></a>
        </div>
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

        $exito = Usuario::borrar($user);
        if (!$exito) {
            $this->errores[] = 'No se pudo borrar el usuario.';
            return;
        }

        session_destroy();
        $this->urlRedireccion = RUTA_APP.'registro.php';
    }
}
