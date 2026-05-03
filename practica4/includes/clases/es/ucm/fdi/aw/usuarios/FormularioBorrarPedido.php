<?php
namespace es\ucm\fdi\aw\usuarios;
use es\ucm\fdi\aw\Formulario; //Usa la clase Formulario

//Formulario para borrar o cancelar un pedido
class FormularioBorrarPedido extends Formulario //Hereda de Formulario
{
    //Atributos privados
    private $numeroPedido;

    //Constructor
    public function __construct($numeroPedido)
    {
        parent::__construct('formBorrarPedido', ['urlRedireccion' => 'listarPedidos.php']); //Constructor de la clase padre
        $this->numeroPedido = (int)$numeroPedido;
    }

    //Metodo que genera el contenido interno del formulario
    protected function generaCamposFormulario(&$datos)
    {
        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores, 'error'); //Genera errores generales (si los hay)

        //Devuelve el HTML correspondiente
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

    //Metodo que se ejecuta despues de pulsar SI
    protected function procesaFormulario(&$datos)
    {
        $this->errores = []; //Vacia errores

        //Obtiene datos necesarios
        $num = (int)($datos['numeroPedido'] ?? 0);
        $rol = $_SESSION['rol'] ?? '';
        $usuario = $_SESSION['user'] ?? '';

        //Valida numero de pedido
        if ($num <= 0) {
            $this->errores[] = 'Número de pedido no válido.';
            return;
        }

        //Valida sesion
        if ($usuario === '') {
            $this->errores[] = 'Sesión no válida.';
            return;
        }

        //Comprueba permisos
        if (!in_array($rol, ['Cliente', 'Gerente'], true)) {
            $this->errores[] = 'No tienes permisos para borrar pedidos.';
            return;
        }

        //Comprueba si el pedido existe
        $pedido = Pedido::buscaPorNumero($num); //Busca el pedido con ese numero en la BD
        if (!$pedido) {
            $this->errores[] = 'El pedido no existe.';
            return;
        }

        //Comprueba que el pedido sea suyo
        if ($rol === 'Cliente' && ($pedido['cliente'] ?? '') !== $usuario) {
            $this->errores[] = 'No tienes permisos para borrar este pedido.';
            return;
        }

        //Comprueba estado del pedido (para no poder cancelar si ya esta en cocina o finalizado)
        if ($rol === 'Cliente' && !Pedido::clientePuedeCancelarEstado((string)($pedido['estado'] ?? ''))) {
            $this->errores[] = 'No puedes cancelar un pedido que ya está en cocina o finalizado.';
            return;
        }

        //Borrar
        $exito = ($rol === 'Cliente')
            ? Pedido::borrar($num, $usuario) //Para cliente
            : Pedido::borrar($num); //Para gerente (no ncesita que el pedido sea suyo)

        if (!$exito) {
            $this->errores[] = 'No se pudo borrar el pedido de la base de datos.';
        }
    }
}
