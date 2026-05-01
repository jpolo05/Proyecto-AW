<?php
namespace es\ucm\fdi\aw;
use es\ucm\fdi\aw\usuarios\Auth; //Usa la clase Auth

abstract class Formulario //Clase abstracta (otras clases heredan de ella)
{
    //Metodo que crea una lista HTML con errores generales
    protected static function generaListaErroresGlobales($errores = [], $classAtt = '')
    {
        $clavesErroresGlobales = array_filter(array_keys($errores), function ($elem) {
            return is_numeric($elem);
        });

        if (count($clavesErroresGlobales) === 0) {
            return '';
        }

        $html = "<ul class=\"$classAtt\">";
        foreach ($clavesErroresGlobales as $clave) {
            $html .= "<li>$errores[$clave]</li>";
        }
        $html .= '</ul>';

        return $html;
    }

    //Metodo que crea el HTML de un error concreto, si no existe error para ese campo, devuelve vacío
    protected static function createMensajeError($errores = [], $idError = '', $htmlElement = 'span', $atts = [])
    {
        if (!isset($errores[$idError])) {
            return '';
        }

        $att = '';
        foreach ($atts as $key => $value) {
            $att .= "$key=\"$value\" ";
        }

        return "<$htmlElement $att>{$errores[$idError]}</$htmlElement>";
    }

    //Metodo que genera los mensajes de error para varios campos
    protected static function generaErroresCampos($campos, $errores, $htmlElement = 'span', $atts = [])
    {
        $erroresCampos = [];
        foreach ($campos as $campo) {
            $erroresCampos[$campo] = self::createMensajeError($errores, $campo, $htmlElement, $atts);
        }
        return $erroresCampos;
    }

    //Atributos
    protected $formId;
    protected $method; //Metodo de envio del formulario
    protected $action; //Pagina a la que se envia el formulario
    protected $classAtt; //Clase CSS del formulario
    protected $enctype; //Tipo de codificacion
    protected $urlRedireccion; //Pagina a la que redirige
    protected $errores;

    //Constructor
    public function __construct($formId, $opciones = [])
    {
        $this->formId = $formId;

        $opcionesPorDefecto = [
            'action' => null,
            'method' => 'POST',
            'class' => null,
            'enctype' => null,
            'urlRedireccion' => null,
        ];
        $opciones = array_merge($opcionesPorDefecto, $opciones); //Mezcla las opciones por defecto con las que le pasan

        $this->action = $opciones['action'];
        $this->method = $opciones['method'];
        $this->classAtt = $opciones['class'];
        $this->enctype = $opciones['enctype'];
        $this->urlRedireccion = $opciones['urlRedireccion'];

        if (!$this->action) {
            $this->action = htmlspecialchars($_SERVER['REQUEST_URI']); //Si no se indica action, usa la página actual
        }
    }

    public function gestiona()
    {
        //Leem los datos de POST o de GET segun como este configurado el formulario
        $datos = &$_POST;
        if (strcasecmp('GET', $this->method) === 0) {
            $datos = &$_GET;
        }

        $this->errores = [];

        if (!$this->formularioEnviado($datos)) {
            return $this->generaFormulario(); //Si el usuario acaba de entrar en la página, muestra el formulario vacío
        }

        if (strcasecmp('GET', $this->method) !== 0) { ///Si el formulario no es GET
            $token = $datos['csrfToken'] ?? null; //Extrae el token
            if (!Auth::validaCsrfToken($token)) { 
                $this->errores[] = 'Token CSRF inválido o ausente.'; //Comprueba el token CSRF para evitar envios maliciosos desde otra pagina
                return $this->generaFormulario($datos); 
            }
        }

        $this->procesaFormulario($datos); //Procesa formulario (lo redefine cada formulario concreto)
        $esValido = count($this->errores) === 0;

        if (!$esValido) {
            return $this->generaFormulario($datos); //Si hay errores, vuelve a mostrar el formulario con los datos introducidos
        }

        if ($this->urlRedireccion !== null) {
            header("Location: {$this->urlRedireccion}"); //Si no hay errores, redirige a la pagina correspondiente
            exit();
        }

        return '';
    }

    //Genera los campos concretos del formulario
    protected function generaCamposFormulario(&$datos)
    {
        return ''; //Vacio (las clases hijas lo sobreescriben)
    }

    //Procesa los datos (clases hija lo sobreescriben)
    protected function procesaFormulario(&$datos)
    {
    }

    //Comprueba si el formulario enviado es este formulario (puede haber varios formularios en una misma página)
    protected function formularioEnviado(&$datos)
    {
        return isset($datos['formId']) && $datos['formId'] == $this->formId;
    }

    //Genera el formulario completo
    protected function generaFormulario(&$datos = [])
    {
        $htmlCamposFormularios = $this->generaCamposFormulario($datos); //Pide los campos concretos
        $csrfToken = Auth::getCsrfToken(); //Genera un token CSRF

        $classAtt = $this->classAtt != null ? "class=\"{$this->classAtt}\"" : '';
        $enctypeAtt = $this->enctype != null ? "enctype=\"{$this->enctype}\"" : '';

        //Devuelve el HTML con los campos concretos del formulario
        return <<<EOS
        <form method="{$this->method}" action="{$this->action}" id="{$this->formId}" {$classAtt} {$enctypeAtt}>
            <input type="hidden" name="formId" value="{$this->formId}">
            <input type="hidden" name="csrfToken" value="{$csrfToken}">
            $htmlCamposFormularios
        </form>
        EOS;
    }
}
