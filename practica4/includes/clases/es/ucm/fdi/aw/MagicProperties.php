<?php
namespace es\ucm\fdi\aw;

/**
 * Añade metodos magicos para que las propiedades utilicen getters y setters.
 * Si existen metodos <code>setPropiedad(x)</code> y <code>getPropiedad()</code> se puede hacer:
 * <ul>
 *  <li><code>$var->propiedad</code>, que equivale a <code>$var->getPropiedad()</code></li>
 *  <li><code>$var->propiedad = $valor</code>, que equivale a <code>$var->setPropiedad($valor)</code></li>
 * </ul>
 */
trait MagicProperties
{
    //Permite leer una propiedad llamando a su getter
    public function __get($property)
    {
        $methodName = 'get' . ucfirst($property); //Construye el nombre del getter
        if (method_exists($this, $methodName)) { //Si existe el getter
            return $this->$methodName(); //Devuelve el valor
        } else { //Si no existe
            throw new \Exception("La propiedad '$property' no está definida"); //Lanza error
        }
    }

    //Permite modificar una propiedad llamando a su setter
    public function __set($property, $value)
    {
        $methodName = 'set' . ucfirst($property); //Construye el nombre del setter
        if (method_exists($this, $methodName)) { //Si existe el setter
            $this->$methodName($value); //Asigna el valor
        } else { //Si no existe
            throw new \Exception("La propiedad '$property' no está definida"); //Lanza error
        }
    }
}
