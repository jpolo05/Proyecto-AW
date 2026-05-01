<?php
namespace es\ucm\fdi\aw\usuarios;
use es\ucm\fdi\aw\Aplicacion; //Usa la clase Aplicacion (para obetener conexion con la BD)

//Clase que contiene las operaciones CRUD de categorias
class Categoria
{ //Todos metodos estaticos, se usan sin crear objeto

    //Devuelve un array con las categorias
    public static function listar(): array {
        $conn = Aplicacion::getInstance()->getConexionBd(); //Obtiene conexion con la BD

        $sql = "SELECT id, nombre, descripcion, imagen FROM categorias ORDER BY id"; //Consulta SQL para obtener todas las categorías, ordenadas por id
        $res = mysqli_query($conn, $sql); //Ejecuta la consulta

        //Si la consulta falla devuelve un array vacio
        if (!$res) {
            return []; 
        }

        //Lee cada fila de la consulta y la mete en $out
        $out = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $out[] = $row;
        }

        mysqli_free_result($res); //Libera el resultado de la consulta
        return $out; //Devuelve todas las categorais
    }

    //Crea una nueva categoria (devuelve true si se ha creado bien y false si ha fallado)
    public static function crear(string $nombre, string $descripcion, ?string $imagen = null): bool
    {
        $conn = Aplicacion::getInstance()->getConexionBd(); //Conexion con la BD
        $sql = 'INSERT INTO categorias (nombre, descripcion, imagen) VALUES (?, ?, ?)'; //Prepara una consulta para insertar una categoría
        $stmt = mysqli_prepare($conn, $sql); //Prepara la consulta (seguridad)
        if (!$stmt) {
            return false; //Si no se puede preparar devuelve false
        }

        $imagen = ($imagen !== null && $imagen !== '') ? $imagen : null; //La imagen es opcional (puede ser null)
        mysqli_stmt_bind_param($stmt, 'sss', $nombre, $descripcion, $imagen); //Asocia los valores reales a los ? de la consulta
        $ok = mysqli_stmt_execute($stmt); //Ejecuta el insert
        mysqli_stmt_close($stmt); //Cierra la consulta
        return $ok; //Devuelve si ha ido bien o no
    }

    //Busca una categoría concreta por su id (devuelve un array si la encuentra y null si no)
    public static function buscaPorId(int $id): ?array
    {
        $conn = Aplicacion::getInstance()->getConexionBd(); //Conexion con la BD
        $sql = 'SELECT id, nombre, descripcion, imagen FROM categorias WHERE id = ? LIMIT 1'; //Busca una categoría que coincida con el id
        $stmt = mysqli_prepare($conn, $sql); //Prepara la consulta (seguridad)
        if (!$stmt) {
            return null; //Si no se puede preparar devuelve false
        }

        mysqli_stmt_bind_param($stmt, 'i', $id); //Asocia el valor de $id como entero al ?
        mysqli_stmt_execute($stmt); //Ejecuta la consulta
        $res = mysqli_stmt_get_result($stmt); //Obtiene el resultado de la consulta
        $fila = $res ? mysqli_fetch_assoc($res) : null; //Si hay resultado, obtiene una fila como array asociativo, sino null
        mysqli_stmt_close($stmt); //Cierra la consulta
        mysqli_free_result($res); ////Libera el resultado de la consulta

        return $fila ?: null; //Devuelve el array o null
    }

    //Borra una categoría de la base de datos
    public static function borrar(int $id): bool
    {
        $conn = Aplicacion::getInstance()->getConexionBd(); //Conexion con la BD
        $sql = 'DELETE FROM categorias WHERE id = ?'; //Consulta para borrar la categoria cuyo id coincida
        $stmt = mysqli_prepare($conn, $sql); //Prepara la consulta (seguridad)
        if (!$stmt) {
            return false; //Si no se puede preparar devuelve false
        }

        mysqli_stmt_bind_param($stmt, 'i', $id); //Asocia el valor de $id como entero al ?
        $ok = mysqli_stmt_execute($stmt); //Ejecuta el borrado
        mysqli_stmt_close($stmt); //Cierra la consulta
        return $ok; //Devuelve si ha ido bien o no
    }

    //Actualiza una categoría existente
    public static function actualizar(int $id, string $nombre, string $descripcion, ?string $imagen = null): bool
    {
        $conn = Aplicacion::getInstance()->getConexionBd(); //Conexion con la BD
        $sql = 'UPDATE categorias SET nombre = ?, descripcion = ?, imagen = ? WHERE id = ?'; //Actualiza los campos de la categoría cuyo id coincida
        $stmt = mysqli_prepare($conn, $sql); //Prepara la consulta (seguridad)
        if (!$stmt) {
            return false; //Si no se puede preparar devuelve false
        }

        $imagen = ($imagen !== null && $imagen !== '') ? $imagen : null; //La imagen es opcional (puede ser null)
        mysqli_stmt_bind_param($stmt, 'sssi', $nombre, $descripcion, $imagen, $id); //Asocia los valores a la consulta
        $ok = mysqli_stmt_execute($stmt); //Ejecuta el update
        mysqli_stmt_close($stmt); //Cierra la consulta
        return $ok; //Devuelve si ha ido bien o no
    }
}

