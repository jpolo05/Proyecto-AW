<?php
namespace es\ucm\fdi\aw\usuarios;
use es\ucm\fdi\aw\Formulario; //Usa la clase Formulario

//Formulario para que un usuario pueda eliminar su propia cuenta
class FormularioBorrar extends Formulario //Hereda de Formulario
{
    //Constructor
    public function __construct()
    {
        parent::__construct('formBorrarUsuario'); //Constructor de la clase padre
    }

    //Metodo que genera el contenido interno del formulario
    protected function generaCamposFormulario(&$datos)
    {
        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores, 'error'); //Genera errores generales (si los hay)

        ////Devuelve el HTML correspondiente
        return <<<EOF
        $htmlErroresGlobales
        <p>¿Estas seguro de que quieres eliminar tu cuenta para siempre?</p>
        <div>
            <button type="submit" name="borrar" class="button-estandar">Si</button>
            <button type="submit" name="cancelar" class="button-estandar">No</button>
        </div>
        EOF;
    }

    //Metodo que se ejecuta cuando se pulsa SI o NO
    protected function procesaFormulario(&$datos)
    {
        $this->errores = []; //Limpia errores

        if (isset($datos['cancelar'])){ //Si pulsa NO
            $this->urlRedireccion = 'visualizarUsuarios.php'; //Redirige
            return;
        }

        //Comprobar usuario en sesion
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            $this->errores[] = 'Sesión no válida.';
            return;
        }

        $exito = Usuario::borrar($user); //Borra usuario de la base de datos
        if (!$exito) {
            $this->errores[] = 'No se pudo borrar el usuario.';
            return;
        }

        session_destroy(); //Si el usuario se ha borrado correctamente, se destruye la sesion
        $this->urlRedireccion = RUTA_APP.'registro.php'; //Redirige
    }
}

