<?php
namespace es\ucm\fdi\aw\usuarios;
use es\ucm\fdi\aw\Formulario; //Usa la clase Formulario

//Formulario pequeño para que un cocinero o gerente pueda marcar como preparada una línea de un pedido
class FormularioActualizaLineaPedido extends Formulario //Hereda de Formulario
{
    //Atributos privados
    private $numPedido;
    private $idProd;
    
    //Constructor
    public function __construct($numPedido, $idProd)
    {
        parent::__construct('formActualizaLinea_' . $numPedido . '_' . $idProd, [ //Constructor de la clase padre
            'urlRedireccion' => RUTA_APP . "includes/vistas/pedidos/visualizarPedido.php?numeroPedido=$numPedido&accion=cocinar" //Cuando el formulario se procesa correctamente redirige otra vez a la vista del pedido
        ]);
        $this->numPedido = (int)$numPedido;
        $this->idProd = (int)$idProd;
    }

    //Genera los campos internos del formulario
    protected function generaCamposFormulario(&$datos)
    {
        return <<<EOF
        <input type="hidden" name="numPedido" value="{$this->numPedido}">
        <input type="hidden" name="idProd" value="{$this->idProd}">
        <button type="submit" class="button-estandar">Listo</button>
        EOF;
    }

    //Metodo que se ejecuta al pulsar el boton listo
    protected function procesaFormulario(&$datos)
    {
        $this->errores = []; //Vacia la lista de errores
        $rol = $_SESSION['rol'] ?? ''; //Obtiene el rol del usuario

        //Comprueba que sea cocinero o gerente
        if (!in_array($rol, ['Cocinero', 'Gerente'], true)) {
            $this->errores[] = "No tienes permisos para actualizar líneas de pedido.";
            return;
        }

        //Llamada al metodo actualizarEstadoLinea para actualizar en la BD
        $ok = Pedido::actualizarEstadoLinea($this->numPedido, $this->idProd);
        if (!$ok) {
            $this->errores[] = "No se pudo actualizar la línea del pedido.";
        }
    }
}
