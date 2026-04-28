<?php
namespace es\ucm\fdi\aw\usuarios;
use es\ucm\fdi\aw\Formulario;

class FormularioBorrarPedido extends Formulario
{
    private $numeroPedido;

    public function __construct($numeroPedido)
    {
        parent::__construct('formBorrarPedido', ['urlRedireccion' => 'listarPedidos.php']);
        $this->numeroPedido = (int)$numeroPedido;
    }

    protected function generaCamposFormulario(&$datos)
    {
        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores, 'error');

        return <<<EOF
        $htmlErroresGlobales
        <input type="hidden" name="numeroPedido" value="{$this->numeroPedido}">
        <p>¿Estás seguro de que quieres eliminar este pedido?</p>
        <div>
            <button type="submit" name="borrar" class="button-estandar">Sí</button>
            <a href="listarPedidos.php" class="button-estandar">No</a>
        </div>
        EOF;
    }

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];
        $num = (int)($datos['numeroPedido'] ?? 0);
        $rol = $_SESSION['rol'] ?? '';
        $usuario = $_SESSION['user'] ?? '';

        if ($num <= 0) {
            $this->errores[] = 'Número de pedido no válido.';
            return;
        }

        if ($usuario === '') {
            $this->errores[] = 'Sesión no válida.';
            return;
        }

        if (!in_array($rol, ['Cliente', 'Gerente'], true)) {
            $this->errores[] = 'No tienes permisos para borrar pedidos.';
            return;
        }

        $pedido = Pedido::buscaPorNumero($num);
        if (!$pedido) {
            $this->errores[] = 'El pedido no existe.';
            return;
        }

        if ($rol === 'Cliente' && ($pedido['cliente'] ?? '') !== $usuario) {
            $this->errores[] = 'No tienes permisos para borrar este pedido.';
            return;
        }

        if ($rol === 'Cliente' && !Pedido::clientePuedeCancelarEstado((string)($pedido['estado'] ?? ''))) {
            $this->errores[] = 'No puedes cancelar un pedido que ya está en cocina o finalizado.';
            return;
        }

        $exito = ($rol === 'Cliente')
            ? Pedido::borrar($num, $usuario)
            : Pedido::borrar($num);

        if (!$exito) {
            $this->errores[] = 'No se pudo borrar el pedido de la base de datos.';
        }
    }
}
