<?php
namespace es\ucm\fdi\aw\usuarios;
use es\ucm\fdi\aw\Aplicacion; //Usa la clase Aplicacion

//Se encarga de manejar las ofertas
class Oferta {

    //Crea una nueva oferta
    public static function crear(string $nombre, string $descripcion, ?string $comienzo, ?string $fin, float $descuento, array $productos, array $cantidades): bool
    {
        $conn = Aplicacion::getInstance()->getConexionBd(); //Conexion a la BD

        // 1. Insertar la oferta
        $sql = 'INSERT INTO ofertas (nombre, descripcion, comienzo, fin, descuento) VALUES (?, ?, ?, ?, ?)'; //Prepara una consulta para insertar una oferta (? = hueco para valor)

        $stmt = mysqli_prepare($conn, $sql); //Prepara la consulta (seguridad)
        if (!$stmt) {
            return false; //Devuelve false si no se puede preparar
        }

        mysqli_stmt_bind_param($stmt, 'ssssd', $nombre, $descripcion, $comienzo, $fin, $descuento); //Asocia los valores a sus ? correspondientes (huecos)
        if (!mysqli_stmt_execute($stmt)) { //Si no consigue ejecutar la consulta y obtener el resultado
            mysqli_stmt_close($stmt); //Cierra consulta
            return false;
        }

        $idOferta = mysqli_insert_id($conn); //Para insertar despues las lineas de la oferta
        mysqli_stmt_close($stmt); //Cierra la consulta

        // 2. Insertar las líneas de oferta
        $sqlLinea = 'INSERT INTO lineas_oferta (id_oferta, producto, cantidad) VALUES (?, ?, ?)'; //Prepara una consulta para insertar una oferta (? = hueco para valor)
        foreach ($productos as $index => $productoId) { //Recorre los productos
            if (empty($productoId)) {
                continue; // Salta productos no seleccionados
            }

            //Si la cantidad existe, es numerica y mayor que 0 usa esa cantidad, sino usa 1
            $cantidad = isset($cantidades[$index]) && is_numeric($cantidades[$index]) && (int)$cantidades[$index] > 0 ? (int)$cantidades[$index] : 1;

            $stmtLinea = mysqli_prepare($conn, $sqlLinea); //Prepara consulta (seguridad)
            if (!$stmtLinea) {
                continue; //Salta si no puede preparar
            }

            mysqli_stmt_bind_param($stmtLinea, 'iii', $idOferta, $productoId, $cantidad); //Asocia los valores a sus ? correspondientes (huecos)
            mysqli_stmt_execute($stmtLinea); //Ejecuta consulta
            mysqli_stmt_close($stmtLinea); //Cierra consulta
        }

        return true;
    }

    //Devuelve las ofertas con sus productos asociados
    public static function listar(): array {
        $conn = Aplicacion::getInstance()->getConexionBd(); //Conexion a la BD

        //Consulta principal (junta 3 tablas):
        //ofertas → datos generales de la oferta
        //lineas_oferta → qué productos forman la oferta y en qué cantidad
        //productos → nombre del producto
        $sql = 'SELECT o.id, o.nombre, o.descripcion, o.comienzo, o.fin, o.descuento,
                       p.nombre AS producto_nombre, lo.cantidad
                FROM ofertas o
                LEFT JOIN lineas_oferta lo ON lo.id_oferta = o.id --Con LEFT JOIN, puede listar una oferta aunque todavia no tenga productos asociados
                LEFT JOIN productos p ON p.id = lo.producto
                ORDER BY o.id, p.nombre';

        $res = mysqli_query($conn, $sql); //Ejecuta la consulta
        if (!$res) {
            return []; //Si la consulta falla devuelve un array vacio
        }

        $out = [];
        while ($row = mysqli_fetch_assoc($res)) { //Lee cada fila de la consulta y la mete en $out
            $id = (int) $row['id']; //Agrupa por id (una oferta puede tener varios productos, la misma oferta puede aparecer en varias filas)
            if (!isset($out[$id])) { //Si todavia no habia creado esa oferta en $out, la crea
                $out[$id] = [
                    'id' => $id,
                    'nombre' => $row['nombre'],
                    'descripcion' => $row['descripcion'],
                    'comienzo' => $row['comienzo'],
                    'fin' => $row['fin'],
                    'descuento' => $row['descuento'],
                    'lineas' => [],
                ];
            }
            if (!empty($row['producto_nombre'])) { //Añade productos dentro de lineas
                $out[$id]['lineas'][] = [
                    'producto' => $row['producto_nombre'],
                    'cantidad' => (int) ($row['cantidad'] ?? 1),
                ];
            }
        }

        mysqli_free_result($res); //Libera el resultado de la consulta
        return array_values($out); //Devuelve las ofertas como array
    }

    //Busca una oferta concreta por su id
    public static function buscaPorId(int $id): ?array
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        
        // 1. Consulta para obtener los datos principales de la oferta
        $sql = 'SELECT id, nombre, descripcion, comienzo, fin, descuento
                FROM ofertas
                WHERE id = ? LIMIT 1';
                
        $stmt = mysqli_prepare($conn, $sql); //Prepara la consulta (seguridad)
        if (!$stmt) {
            return null; //Devuelve false si no se puede preparar
        }

        mysqli_stmt_bind_param($stmt, 'i', $id); //Asocia el valor de id a su ? correspondiente (hueco)
        mysqli_stmt_execute($stmt); //Ejecuta la consulta y obtiene el resultado
        $res = mysqli_stmt_get_result($stmt); //Guarda resultado
        $fila = $res ? mysqli_fetch_assoc($res) : null; //Si hay resultado, obtiene una fila como array asociativo (sino null)
        mysqli_stmt_close($stmt); //Cierra la consulta

        mysqli_free_result($res); //Libera resultado

        if (!$fila) {
            return null; //Si no encuentra la oferta devuelve null
        }

        // 2. Consulta para obtener las líneas asociadas (busca los productos de esa oferta)
        $fila['lineas'] = [];
        $sqlLineas = 'SELECT p.id AS idProd, p.nombre, lo.cantidad 
                    FROM lineas_oferta lo
                    INNER JOIN productos p ON p.id = lo.producto
                    WHERE lo.id_oferta = ?
                    ORDER BY p.nombre';
        
        $stmtLineas = mysqli_prepare($conn, $sqlLineas); //Prepara la consulta (seguridad)
        if ($stmtLineas) {
            mysqli_stmt_bind_param($stmtLineas, 'i', $id); //Asocia el valor de id a su ? correspondiente (hueco)
            mysqli_stmt_execute($stmtLineas); //Ejecuta la consulta y obtiene el resultado
            $resLineas = mysqli_stmt_get_result($stmtLineas); //Guarda el resultado

            if ($resLineas) {
                while ($linea = mysqli_fetch_assoc($resLineas)) {
                    $fila['lineas'][] = [ //Mete los resultados de la consulta en el array
                        'idProd' => (int) $linea['idProd'],
                        'producto' => $linea['nombre'],
                        'cantidad' => (int) $linea['cantidad'], // Ahora la variable existe
                    ];
                }
            }
            mysqli_stmt_close($stmtLineas); //Cierra consulta
            mysqli_free_result($resLineas); //Libera resultado
        }

        return $fila;
    }

    //Borra una oferta
    public static function borrar(int $id): bool
    {
        $conn = Aplicacion::getInstance()->getConexionBd(); //Conexion a la BD

        $sql = 'DELETE FROM lineas_oferta WHERE id_oferta = ?'; //Borra sus lineas (productos asociados a esa oferta)
        $stmt = mysqli_prepare($conn, $sql); //Prepara consulta (seguridad)
        if (!$stmt) {
            return false; //Devuelve false si no se puede preparar
        }

        mysqli_stmt_bind_param($stmt, 'i', $id); //Asocia el valor de id a su ? correspondiente (hueco)
        $result = mysqli_stmt_execute($stmt); //Ejecuta la consulta y obtiene el resultado
        mysqli_stmt_close($stmt); //Cierra consulta

        $sql = 'DELETE FROM ofertas WHERE id = ?'; //Borra las ofertas
        $stmt = mysqli_prepare($conn, $sql); //Prepara consulta (seguridad)
        if (!$stmt) {
            return false; //Devuelve false si no se puede preparar
        }

        mysqli_stmt_bind_param($stmt, 'i', $id); //Asocia el valor de id a su ? correspondiente (hueco)
        $result = mysqli_stmt_execute($stmt); //Ejecuta la consulta y obtiene el resultado
        mysqli_stmt_close($stmt); //Cierra consulta

        return $result !== false; //Devuelve si se ha podido borrar
    }

    //Devuelve solo las ofertas que están activas actualmente
    public static function obtenerOfertasActivas(): array {

        $conn = Aplicacion::getInstance()->getConexionBd(); //Conexion a la BD
        $hoy = date('Y-m-d H:i:s'); //Obtiene fecha actual
        
        //Consulta si la oferta esta activa segun fecha
        $sql = 'SELECT o.id, o.descuento 
                FROM ofertas o 
                WHERE (o.comienzo <= ? OR o.comienzo IS NULL) 
                AND (o.fin >= ? OR o.fin IS NULL)';
                
        $stmt = mysqli_prepare($conn, $sql); //Prepara la consulta (seguridad)
        mysqli_stmt_bind_param($stmt, 'ss', $hoy, $hoy); ////Asocia los valores a sus ? correspondientes (huecos)
        mysqli_stmt_execute($stmt); //Ejecuta la consulta
        $res = mysqli_stmt_get_result($stmt); //Obtiene el resultado
        
        $ofertas = [];
        while ($fila = mysqli_fetch_assoc($res)) {
            $ofertas[] = self::buscaPorId((int)$fila['id']); //Por cada oferta activa encontrada, llama a buscaPorId (devuelve oferta completa)
        }
        mysqli_free_result($res); //Libera la consulta
        return $ofertas;
    }

    //Actualiza una oferta existente
    public static function actualizar(int $id, string $nombre, string $descripcion, ?string $comienzo, ?string $fin, float $descuento, array $productos, array $cantidades): bool
    {
        $conn = Aplicacion::getInstance()->getConexionBd(); //Conexion con la BD

        $sql = 'UPDATE ofertas SET nombre = ?, descripcion = ?, comienzo = ?, fin = ?, descuento = ? WHERE id = ?'; //Mete ? (huecos) en los datos principales
        $stmt = mysqli_prepare($conn, $sql); //Prepara consulta
        if (!$stmt) {
            return false; //Devuelve false si no se puede preparar
        }

        mysqli_stmt_bind_param($stmt, 'ssssdi', $nombre, $descripcion, $comienzo, $fin, $descuento, $id); //Asocia los nuevos valores a sus ? correspondientes (huecos)
        if (!mysqli_stmt_execute($stmt)) { //Si no consigue ejecutar la consulta
            mysqli_stmt_close($stmt); //Cierra consulta
            return false; //Devuelve false
        }
        mysqli_stmt_close($stmt); //Cierra consulta

        $sqlDelete = 'DELETE FROM lineas_oferta WHERE id_oferta = ?'; //Borra todas las lineas antiguas
        $stmtDelete = mysqli_prepare($conn, $sqlDelete); //Prepara consulta
        if ($stmtDelete) { //Si consigue prepararla
            mysqli_stmt_bind_param($stmtDelete, 'i', $id); //Asocia el valor de id a su ? correspondiente (hueco)
            mysqli_stmt_execute($stmtDelete); //Ejecuta la consulta
            mysqli_stmt_close($stmtDelete); //Cierra consulta
        }

        $sqlLinea = 'INSERT INTO lineas_oferta (id_oferta, producto, cantidad) VALUES (?, ?, ?)'; //Mete ? (huecos) en los datos de la linea
        foreach ($productos as $index => $productoId) { //Recorre todos los productos
            if (empty($productoId)) {
                continue; //Si esta vacio, continua
            }

            //Si la cantidad existe, es numerica y mayor que 0 usa esa cantidad, sino usa 1
            $cantidad = isset($cantidades[$index]) && is_numeric($cantidades[$index]) && (int)$cantidades[$index] > 0 ? (int)$cantidades[$index] : 1;

            $stmtLinea = mysqli_prepare($conn, $sqlLinea); //Prepara consulta (seguridad)
            if (!$stmtLinea) {
                continue; //Salta si no puede preparar
            }

            mysqli_stmt_bind_param($stmtLinea, 'iii', $id, $productoId, $cantidad); //Asocia los valores a sus ? correspondientes (huecos)
            mysqli_stmt_execute($stmtLinea); //Ejecuta consulta
            mysqli_stmt_close($stmtLinea); //Cierra consulta
        }

        return true;
    }
}

