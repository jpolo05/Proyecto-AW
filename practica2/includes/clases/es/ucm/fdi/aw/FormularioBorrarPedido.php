<?php
namespace es\ucm\fdi\aw;

class FormularioBorrarPedido extends Formulario
{
    public function __construct()
    {
        parent::__construct('formBorrarPedido');
    }

    protected function generaCamposFormulario(&$datos)
    {
        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores, 'error');

        return <<<EOF
        $htmlErroresGlobales
        <p>¿Estas seguro de que quieres eliminar este pedido?</p>
        <div>
            <button type="submit" name="borrar" class="button-estandar">Si</button>
            <a href="perfil.php"><button type="button" class="button-estandar">No</button></a>
        </div>
        EOF;
    }

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];

        $id = $_SESSION['id'] ?? null;
        if (!$user) {
            $this->errores[] = 'Pedido no valido.';
            return;
        }

        $exito = Pedido::borrar($id);
        if (!$exito) {
            $this->errores[] = 'No se pudo borrar el pedido.';
            return;
        }

        $this->urlRedireccion = RUTA_APP.'pedidos.php';
    }
}
