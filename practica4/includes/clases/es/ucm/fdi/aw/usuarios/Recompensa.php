<?php
namespace es\ucm\fdi\aw\usuarios;
use es\ucm\fdi\aw\Aplicacion; //Usa la clase Aplicacion

class Recompensa {
    //Lista todas las recompensas
    public static function listar(): array
    {
        $conn = Aplicacion::getInstance()->getConexionBd(); //Obtiene conexion a la BD
        $sql = 'SELECT r.id, r.id_producto, r.bistroCoins
                FROM recompensas r
                ORDER BY r.id ASC'; //Consulta recompensas ordenadas por id
        $res = mysqli_query($conn, $sql); //Ejecuta consulta

        if (!$res) { //Si falla
            return [];
        }

        $out = []; //Array de recompensas
        while ($row = mysqli_fetch_assoc($res)) { //Recorre resultados
            $out[] = $row; //Añade recompensa
        }
        mysqli_free_result($res); //Libera resultado

        return $out; //Devuelve recompensas
    }

    //Busca una recompensa por id
    public static function buscaPorId(int $id): ?array
    {
        $conn = Aplicacion::getInstance()->getConexionBd(); //Obtiene conexion a la BD
        $sql = 'SELECT id, id_producto, bistroCoins FROM recompensas WHERE id = ? LIMIT 1';
        $stmt = mysqli_prepare($conn, $sql); //Prepara consulta
        if (!$stmt) { //Si falla
            return null;
        }

        mysqli_stmt_bind_param($stmt, 'i', $id); //Asocia id
        mysqli_stmt_execute($stmt); //Ejecuta consulta
        $res = mysqli_stmt_get_result($stmt); //Obtiene resultado
        $fila = $res ? mysqli_fetch_assoc($res) : null; //Recoge recompensa
        mysqli_stmt_close($stmt); //Cierra statement
        mysqli_free_result($res); //Libera resultado

        return $fila ?: null; //Devuelve recompensa o null
    }

    //Crea una recompensa
    public static function crear(int $idProducto, int $bistroCoins): bool
    {
        if ($idProducto <= 0 || $bistroCoins <= 0) { //Comprueba datos
            return false;
        }

        $conn = Aplicacion::getInstance()->getConexionBd(); //Obtiene conexion a la BD
        $sql = 'INSERT INTO recompensas (id_producto, bistroCoins) VALUES (?, ?)';
        $stmt = mysqli_prepare($conn, $sql); //Prepara insercion
        if (!$stmt) { //Si falla
            return false;
        }

        mysqli_stmt_bind_param($stmt, 'ii', $idProducto, $bistroCoins); //Asocia producto y coins
        $ok = mysqli_stmt_execute($stmt); //Ejecuta insercion
        mysqli_stmt_close($stmt); //Cierra statement
        return $ok; //Devuelve resultado
    }

    //Actualiza una recompensa
    public static function actualizar(int $id, int $idProducto, int $bistroCoins): bool
    {
        if ($id <= 0 || $idProducto <= 0 || $bistroCoins <= 0) { //Comprueba datos
            return false;
        }

        $conn = Aplicacion::getInstance()->getConexionBd(); //Obtiene conexion a la BD
        $sql = 'UPDATE recompensas SET id_producto = ?, bistroCoins = ? WHERE id = ?';
        $stmt = mysqli_prepare($conn, $sql); //Prepara actualizacion
        if (!$stmt) { //Si falla
            return false;
        }

        mysqli_stmt_bind_param($stmt, 'iii', $idProducto, $bistroCoins, $id); //Asocia datos
        $ok = mysqli_stmt_execute($stmt); //Ejecuta actualizacion
        mysqli_stmt_close($stmt); //Cierra statement
        return $ok; //Devuelve resultado
    }

    //Borra una recompensa
    public static function borrar(int $id): bool
    {
        if ($id <= 0) { //Comprueba id
            return false;
        }

        $conn = Aplicacion::getInstance()->getConexionBd(); //Obtiene conexion a la BD
        $sql = 'DELETE FROM recompensas WHERE id = ?';
        $stmt = mysqli_prepare($conn, $sql); //Prepara borrado
        if (!$stmt) { //Si falla
            return false;
        }

        mysqli_stmt_bind_param($stmt, 'i', $id); //Asocia id
        $ok = mysqli_stmt_execute($stmt); //Ejecuta borrado
        mysqli_stmt_close($stmt); //Cierra statement
        return $ok; //Devuelve resultado
    }

    //Lista recompensas junto con los datos de su producto
    public static function listarConProducto(bool $soloDisponibles = false): array
    {
        $conn = Aplicacion::getInstance()->getConexionBd(); //Obtiene conexion a la BD
        $sql = 'SELECT r.id, r.id_producto, r.bistroCoins, p.nombre AS nombre_producto, p.descripcion AS descripcion_producto, p.precio_base, p.iva, p.disponible, p.ofertado
                FROM recompensas r
                INNER JOIN productos p ON p.id = r.id_producto'; //Une recompensas con productos

        if ($soloDisponibles) { //Si solo se quieren recompensas disponibles
            $sql .= ' WHERE p.disponible = 1 AND p.ofertado = 1'; //Filtra productos disponibles y ofertados
        }

        $sql .= ' ORDER BY p.nombre ASC, r.id ASC'; //Ordena por producto e id
        $res = mysqli_query($conn, $sql); //Ejecuta consulta
        if (!$res) { //Si falla
            return [];
        }

        $out = []; //Array de recompensas
        while ($row = mysqli_fetch_assoc($res)) { //Recorre resultados
            $out[] = $row; //Añade recompensa
        }
        mysqli_free_result($res); //Libera resultado
        return $out; //Devuelve recompensas
    }
}
