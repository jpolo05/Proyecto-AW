<?php
namespace es\ucm\fdi\aw\usuarios;
use es\ucm\fdi\aw\Formulario;

class FormularioActualizaPedido extends Formulario
{
    private $numeroPedido;
    private $nuevoEstado;
    private $estadoActual;
    private $textoBoton;
    private $rolesPermitidos;

    public function __construct(int $numeroPedido, string $nuevoEstado, array $opciones = [])
    {
        $this->numeroPedido = (int)$numeroPedido;
        $this->nuevoEstado = trim($nuevoEstado);

        $estadoId = preg_replace('/[^a-z0-9]+/i', '_', $this->nuevoEstado);
        $estadoId = strtolower($estadoId ?: 'estado');
        $formId = 'formActualizaPedido_'.$this->numeroPedido.'_'.$estadoId;

        $opcionesPorDefecto = [
            'urlRedireccion' => RUTA_APP.'includes/vistas/paneles/cocinero.php',
            'estadoActual' => self::estadoActualPorDefecto($this->nuevoEstado),
            'textoBoton' => self::textoBotonPorDefecto($this->nuevoEstado),
            'rolesPermitidos' => self::rolesPermitidosPorDefecto($this->nuevoEstado),
        ];
        $opciones = array_merge($opcionesPorDefecto, $opciones);

        parent::__construct($formId, [
            'urlRedireccion' => $opciones['urlRedireccion'],
        ]);

        $this->estadoActual = is_string($opciones['estadoActual']) && $opciones['estadoActual'] !== ''
            ? $opciones['estadoActual']
            : null;
        $this->textoBoton = (string)$opciones['textoBoton'];
        $this->rolesPermitidos = is_array($opciones['rolesPermitidos']) ? $opciones['rolesPermitidos'] : [];
    }

    protected function generaCamposFormulario(&$datos)
    {
        $estadoActualInput = $this->estadoActual ?? '';
        $estadoActualEscapado = htmlspecialchars($estadoActualInput, ENT_QUOTES, 'UTF-8');
        $nuevoEstadoEscapado = htmlspecialchars($this->nuevoEstado, ENT_QUOTES, 'UTF-8');
        $textoBotonEscapado = htmlspecialchars($this->textoBoton, ENT_QUOTES, 'UTF-8');

        return <<<EOF
        <input type="hidden" name="numeroPedido" value="{$this->numeroPedido}">
        <input type="hidden" name="nuevoEstado" value="{$nuevoEstadoEscapado}">
        <input type="hidden" name="estadoActual" value="{$estadoActualEscapado}">
        <button type="submit" class="button-estandar">{$textoBotonEscapado}</button>
        EOF;
    }

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];

        $num = (int)($datos['numeroPedido'] ?? 0);
        $rol = $_SESSION['rol'] ?? '';

        if ($num <= 0 || $num !== $this->numeroPedido) {
            $this->errores[] = 'El numero de pedido no es valido.';
            return;
        }

        if (!in_array($rol, $this->rolesPermitidos, true)) {
            $this->errores[] = 'No tienes permisos para actualizar el pedido.';
            return;
        }

        $exito = false;
        $requiereDatosCocinero = $this->nuevoEstado === Pedido::ESTADO_COCINANDO;
        if ($requiereDatosCocinero) {
            $cocinero = $_SESSION['user'] ?? '';
            $imagenCocinero = $_SESSION['imagen'] ?? 'img/uploads/usuarios/default.jpg';

            if ($this->estadoActual !== null) {
                $exito = Pedido::actualizarEstadoSi($num, $this->estadoActual, $this->nuevoEstado, $cocinero, $imagenCocinero);
            } else {
                $exito = Pedido::actualizarEstado($num, $this->nuevoEstado, $cocinero, $imagenCocinero);
            }
        } else {
            if ($this->estadoActual !== null) {
                $exito = Pedido::actualizarEstadoSi($num, $this->estadoActual, $this->nuevoEstado);
            } else {
                $exito = Pedido::actualizarEstado($num, $this->nuevoEstado);
            }
        }

        if (!$exito) {
            $this->errores[] = 'No se pudo actualizar el estado del pedido.';
        }
    }

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
