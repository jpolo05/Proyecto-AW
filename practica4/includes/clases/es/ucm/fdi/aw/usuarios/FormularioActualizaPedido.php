<?php
namespace es\ucm\fdi\aw\usuarios;
use es\ucm\fdi\aw\Formulario; //Usa la clase Formulario

//Formulario para cambiar el estado de un pedido
class FormularioActualizaPedido extends Formulario //Hereda de Formulario
{
    //Atributos privados
    private $numeroPedido;
    private $nuevoEstado;
    private $estadoActual;
    private $textoBoton;
    private $rolesPermitidos;

    //Constructor
    public function __construct(int $numeroPedido, string $nuevoEstado, array $opciones = [])
    {
        $this->numeroPedido = (int)$numeroPedido;
        $this->nuevoEstado = trim($nuevoEstado);

        //Crea un id unico para el formulario
        $estadoId = preg_replace('/[^a-z0-9]+/i', '_', $this->nuevoEstado);
        $estadoId = strtolower($estadoId ?: 'estado');
        $formId = 'formActualizaPedido_'.$this->numeroPedido.'_'.$estadoId;

        //Valores por defecto
        $opcionesPorDefecto = [
            'urlRedireccion' => RUTA_APP.'includes/vistas/paneles/cocinero.php', //Redirige al panel de cocinero
            'estadoActual' => self::estadoActualPorDefecto($this->nuevoEstado),
            'textoBoton' => self::textoBotonPorDefecto($this->nuevoEstado),
            'rolesPermitidos' => self::rolesPermitidosPorDefecto($this->nuevoEstado),
        ];
        $opciones = array_merge($opcionesPorDefecto, $opciones); //Mezcla las opciones por defecto con las opciones que se hayan pasado

        //Llamada al constructor de la clase padre
        parent::__construct($formId, [
            'urlRedireccion' => $opciones['urlRedireccion'],
        ]);

        //Guarda configuracion final
        $this->estadoActual = is_string($opciones['estadoActual']) && $opciones['estadoActual'] !== ''
            ? $opciones['estadoActual']
            : null; //Si estadoActual esta vacio, guarda null
        $this->textoBoton = (string)$opciones['textoBoton'];
        $this->rolesPermitidos = is_array($opciones['rolesPermitidos']) ? $opciones['rolesPermitidos'] : [];
    }

    //Genera el HTML interno del formulario
    protected function generaCamposFormulario(&$datos)
    {
        $estadoActualInput = $this->estadoActual ?? ''; //Usa estado actual o null

        //Convierte los textos antes de meterlos en HTML (seguridad)
        $estadoActualEscapado = htmlspecialchars($estadoActualInput, ENT_QUOTES, 'UTF-8');
        $nuevoEstadoEscapado = htmlspecialchars($this->nuevoEstado, ENT_QUOTES, 'UTF-8');
        $textoBotonEscapado = htmlspecialchars($this->textoBoton, ENT_QUOTES, 'UTF-8');

        //Devuelve el HTML  correspondiente
        return <<<EOF
        <input type="hidden" name="numeroPedido" value="{$this->numeroPedido}">
        <input type="hidden" name="nuevoEstado" value="{$nuevoEstadoEscapado}">
        <input type="hidden" name="estadoActual" value="{$estadoActualEscapado}">
        <button type="submit" class="button-estandar">{$textoBotonEscapado}</button>
        EOF;
    }


    protected function procesaFormulario(&$datos)
    {
        $this->errores = []; //Vacia errores anteriores

        $num = (int)($datos['numeroPedido'] ?? 0); //Numero de pedido enviado
        $rol = $_SESSION['rol'] ?? ''; //Rol del usuario actual

        //Comprobaciones
        if ($num <= 0 || $num !== $this->numeroPedido) {
            $this->errores[] = 'El número de pedido no es válido.';
            return;
        }
        if (!in_array($rol, $this->rolesPermitidos, true)) {
            $this->errores[] = 'No tienes permisos para actualizar el pedido.';
            return;
        }

        //Actualizaciones segun estado
        $exito = false;
        $requiereDatosCocinero = $this->nuevoEstado === Pedido::ESTADO_COCINANDO;
        $esCobroPedido = $this->nuevoEstado === Pedido::ESTADO_EN_PREPARACION;

        if ($esCobroPedido) { //Si el nuevo estado es en preparacion (caso especial)
            $exito = Pedido::cobrarYEnviarACocina($num); //Cobrar y enviar a cocina
        } elseif ($requiereDatosCocinero) { //Cuando un cocinero toma un pedido (caso especial)
            $cocinero = $_SESSION['user'] ?? '';
            $imagenCocinero = $_SESSION['imagen'] ?? 'img/uploads/usuarios/default.jpg';

            if ($this->estadoActual !== null) {
                $exito = Pedido::actualizarEstadoSi($num, $this->estadoActual, $this->nuevoEstado, $cocinero, $imagenCocinero);
            } else {
                $exito = Pedido::actualizarEstado($num, $this->nuevoEstado, $cocinero, $imagenCocinero);
            }
        } else { //Caso normal (sin datos extra)
            if ($this->estadoActual !== null) {
                $exito = Pedido::actualizarEstadoSi($num, $this->estadoActual, $this->nuevoEstado); //Actualiza ssi el pedido esta en el estado esperado
            } else {
                $exito = Pedido::actualizarEstado($num, $this->nuevoEstado); //Actualizacion normal
            }
        }

        if (!$exito) {
            $this->errores[] = 'No se pudo actualizar el estado del pedido.';
        }
    }

    //Devuelve el texto del boton segun el nuevo estado
    private static function textoBotonPorDefecto(string $nuevoEstado): string
    {
        switch ($nuevoEstado) {
            case Pedido::ESTADO_EN_PREPARACION:
                return 'Enviar a cocina';
            case Pedido::ESTADO_COCINANDO:
                return 'Tomar pedido';
            case Pedido::ESTADO_LISTO_COCINA:
                return 'Finalizar cocina';
            case Pedido::ESTADO_TERMINADO:
                return 'Marcar servido';
            case Pedido::ESTADO_ENTREGADO:
                return 'Entregar pedido';
            default:
                return 'Actualizar pedido';
        }
    }
    
    //Devuelve en que estado deberia estar segun el nuevo
    private static function estadoActualPorDefecto(string $nuevoEstado): ?string
    {
        switch ($nuevoEstado) {
            case Pedido::ESTADO_COCINANDO:
                return Pedido::ESTADO_EN_PREPARACION;
            case Pedido::ESTADO_LISTO_COCINA:
                return Pedido::ESTADO_COCINANDO;
            case Pedido::ESTADO_EN_PREPARACION:
                return Pedido::ESTADO_RECIBIDO;
            case Pedido::ESTADO_TERMINADO:
                return Pedido::ESTADO_LISTO_COCINA;
            case Pedido::ESTADO_ENTREGADO:
                return Pedido::ESTADO_TERMINADO;
            default:
                return null;
        }
    }
    
    //Devuelve que roles pueden hacer cada cambio
    private static function rolesPermitidosPorDefecto(string $nuevoEstado): array
    {
        switch ($nuevoEstado) {
            case Pedido::ESTADO_COCINANDO:
            case Pedido::ESTADO_LISTO_COCINA:
                return ['Cocinero', 'Gerente'];
            case Pedido::ESTADO_EN_PREPARACION:
            case Pedido::ESTADO_TERMINADO:
            case Pedido::ESTADO_ENTREGADO:
                return ['Camarero', 'Gerente'];
            default:
                return ['Gerente'];
        }
    }
}
