<?php
namespace es\ucm\fdi\aw\usuarios;
use es\ucm\fdi\aw\Aplicacion; //Usa la clase Aplicacion

//Gestiona los productos
class Producto {

    //Crea un nuevo producto
    public static function crear(string $nombre, string $descripcion, ?int $idCategoria, float $precioBase, int $iva, bool $disponible, bool $ofertado, ?string $imagen = null): bool  
    {
        $conn = Aplicacion::getInstance()->getConexionBd(); //Conexion a la BD

        $usaCategoria = $idCategoria !== null && $idCategoria > 0; //Si tiene categoria la usa, sino usa null
        $sql = $usaCategoria
            ? 'INSERT INTO productos (nombre, descripcion, id_categoria, precio_base, iva, disponible, ofertado, imagen)
               VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            : 'INSERT INTO productos (nombre, descripcion, id_categoria, precio_base, iva, disponible, ofertado, imagen)
               VALUES (?, ?, NULL, ?, ?, ?, ?, ?)'; //Prepara una consulta para insertar una oferta (? = hueco para valor), en funcion de si tiene o no categoria
        $stmt = mysqli_prepare($conn, $sql); //Prepara la consulta (seguridad)
        if (!$stmt) {
            return false; //Devuelve false si no se puede preparar
        }

        //Convierte booleanos a enteros
        $disponibleInt = $disponible ? 1 : 0;
        $ofertadoInt = $ofertado ? 1 : 0;

        $imagen = ($imagen !== null && $imagen !== '') ? $imagen : null; //Convierte imagen vacia a null

        if ($usaCategoria) { //Si tiene categoria
            mysqli_stmt_bind_param( //Asocia los valores a sus ? correspondientes (huecos)
                $stmt,
                'ssidiiis',
                $nombre,
                $descripcion,
                $idCategoria,
                $precioBase,
                $iva,
                $disponibleInt,
                $ofertadoInt,
                $imagen
            );
        } else { //Si no tiene
            mysqli_stmt_bind_param( //Asocia los valores a sus ? correspondientes (huecos)
                $stmt,
                'ssdiiis',
                $nombre,
                $descripcion,
                $precioBase,
                $iva,
                $disponibleInt,
                $ofertadoInt,
                $imagen
            );
        }

        $ok = mysqli_stmt_execute($stmt); //Ejecuta el insert
        mysqli_stmt_close($stmt); //Cierra consulta
        return $ok; //Devuelve si ha ido bien o no
    }
    
    //Devuelve una lista de productos (listar(true) devolvera solo los productos que se pueden mostrar al cliente en la carta)
    public static function listar(bool $soloOfertados = false): array {

        $conn = Aplicacion::getInstance()->getConexionBd(); //Conexion a la BD

        //Consulta principal
        $sql = 'SELECT p.id, p.nombre, p.descripcion, p.id_categoria, p.precio_base, p.iva, p.disponible, p.ofertado, p.imagen, c.nombre AS categoria
                FROM productos p
                LEFT JOIN categorias c ON p.id_categoria = c.id'; //Con LEFT JOIN obtenemos tambien el nombre de la categoria

        if ($soloOfertados) { //Filtra solo los que se pueden mostrar al cliente en la carta
            $sql .= ' WHERE p.ofertado = 1 AND p.disponible = 1';
        }

        $sql .= ' ORDER BY p.id_categoria, p.nombre'; //Ordena
        $res = mysqli_query($conn, $sql); //Ejecuta la consulta
        if (!$res) {
            return []; //Si la consulta falla devuelve un array vacio
        }

        $out = [];
        while ($row = mysqli_fetch_assoc($res)) { //Lee cada fila de la consulta y la mete en $out
            $out[] = $row;
        }

        mysqli_free_result($res); //Libera resultado
        return $out; //Devuelve lista de productos
    }

    //Devuelve una version mas simple de los productos
    public static function listarNombres(bool $soloOfertados = false): array {

        $conn = Aplicacion::getInstance()->getConexionBd(); //Conexion a la BD

        //Consulta solo con datos basicos
        $sql = 'SELECT p.id, p.nombre, p.id_categoria, p.precio_base, p.iva
                FROM productos p';

        if ($soloOfertados) { //Filtra solo los que se pueden mostrar al cliente en la carta
            $sql .= ' WHERE p.ofertado = 1 AND p.disponible = 1';
        }

        $sql .= ' ORDER BY p.id_categoria ASC, p.nombre ASC'; //Ordena

        $res = mysqli_query($conn, $sql); //Ejecuta la consulta
        if (!$res) {
            return []; //Si la consulta falla devuelve un array vacio
        }

        $out = [];
        while ($row = mysqli_fetch_assoc($res)) { //Lee cada fila de la consulta y la mete en $out
            $out[] = [
                'id' => (int) $row['id'],
                'nombre' => $row['nombre'],
                'precio_base' => (float) $row['precio_base'],
                'iva' => (int) $row['iva']
            ];
        }

        mysqli_free_result($res); //Libera resultado
        return $out; //Devuelve lista productos simple
    }

    //Busca un producto concreto por su id
    public static function buscaPorId(int $id): ?array
    {
        $conn = Aplicacion::getInstance()->getConexionBd();//Conexion a la BD

        //Consulta filtrando por id
        $sql = 'SELECT p.id, p.nombre, p.descripcion, p.id_categoria, p.precio_base, p.iva, p.disponible, p.ofertado, p.imagen, c.nombre AS categoria
                FROM productos p
                LEFT JOIN categorias c ON p.id_categoria = c.id
                WHERE p.id = ? LIMIT 1';

        $stmt = mysqli_prepare($conn, $sql); //Prepara la consulta (seguridad)
        if (!$stmt) {
            return null; //Devuelve null si no se puede preparar
        }

        mysqli_stmt_bind_param($stmt, 'i', $id); //Asocia el valor de id a su ? correspondiente (hueco)
        mysqli_stmt_execute($stmt); //Ejecuta la consulta y obtiene el resultado
        $res = mysqli_stmt_get_result($stmt); //Guarda resultado
        $fila = $res ? mysqli_fetch_assoc($res) : null; //Si hay resultado, obtiene una fila como array asociativo (sino null)
        mysqli_stmt_close($stmt); //Cierra consulta
        mysqli_free_result($res); //Libera resultado

        return $fila ?: null; //Si no encuentra el producto devuelve null
    }

    //Actualiza un producto existente
    public static function actualizar(int $id, string $nombre, string $descripcion, ?int $idCategoria, float $precioBase, int $iva, bool $disponible, bool $ofertado, ?string $imagen = null): bool
    {
        $conn = Aplicacion::getInstance()->getConexionBd(); //Conexion a la BD

        $usaCategoria = $idCategoria !== null && $idCategoria > 0; //Si tiene categoria la usa, sino usa null
        $sql = $usaCategoria
            ? 'UPDATE productos
               SET nombre = ?, descripcion = ?, id_categoria = ?, precio_base = ?, iva = ?, disponible = ?, ofertado = ?, imagen = ?
               WHERE id = ?'
            : 'UPDATE productos
               SET nombre = ?, descripcion = ?, id_categoria = NULL, precio_base = ?, iva = ?, disponible = ?, ofertado = ?, imagen = ?
               WHERE id = ?'; //Mete ? (huecos) en los datos principales, en funcion de si tiene categoria o no
        $stmt = mysqli_prepare($conn, $sql); //Prepara la consulta (seguridad)
        if (!$stmt) {
            return false; //Devuelve false si no se puede preparar
        }

        //Convierte booleanos a enteros
        $disponibleInt = $disponible ? 1 : 0;
        $ofertadoInt = $ofertado ? 1 : 0;

        $imagen = ($imagen !== null && $imagen !== '') ? $imagen : null; //Convierte imagen vacia a null

        if ($usaCategoria) { //Si tiene categoria
            mysqli_stmt_bind_param( //Asocia los valores a sus ? correspondientes (huecos)
                $stmt,
                'ssidiiisi',
                $nombre,
                $descripcion,
                $idCategoria,
                $precioBase,
                $iva,
                $disponibleInt,
                $ofertadoInt,
                $imagen,
                $id
            );
        } else { //Si no tiene categoria
            mysqli_stmt_bind_param( //Asocia los valores a sus ? correspondientes (huecos)
                $stmt,
                'ssdiiisi',
                $nombre,
                $descripcion,
                $precioBase,
                $iva,
                $disponibleInt,
                $ofertadoInt,
                $imagen,
                $id
            );
        }

        $ok = mysqli_stmt_execute($stmt); //Ejecuta el update
        mysqli_stmt_close($stmt); //Cierra consulta
        return $ok; //Devuelve si ha ido bien o no
    }

    //Desoferta el producto (no lo borra)
    public static function desofertar(int $id): bool
    {
        $conn = Aplicacion::getInstance()->getConexionBd(); //Conexion a la BD

        $sql = 'UPDATE productos SET ofertado = 0 WHERE id = ?'; //Marcar como no ofertado
        $stmt = mysqli_prepare($conn, $sql); //Prepara la consulta (seguridad)
        if (!$stmt) {
            return false; //Devuelve false si no se puede preparar
        }

        mysqli_stmt_bind_param($stmt, 'i', $id); //Asocia el valor de id a su ? correspondiente (hueco)
        $ok = mysqli_stmt_execute($stmt); //Ejecuta el update
        mysqli_stmt_close($stmt); //Cierra consulta
        return $ok; //Devuelve si ha ido bien o no
    }

    //Busca solo el nombre de un producto por su id
    public static function nombre($id): string {

        $conn = Aplicacion::getInstance()->getConexionBd(); //Conexion a la BD

        $sql = "SELECT nombre FROM productos WHERE id = ?"; //Consulta filtrando por id

        $stmt = mysqli_prepare($conn, $sql);//Prepara la consulta (seguridad)
        if (!$stmt) {
            return ""; //Devuelve vacio si no se puede preparar
        }

        mysqli_stmt_bind_param($stmt, "i", $id); //Asocia el valor de id a su ? correspondiente (hueco)
        mysqli_stmt_execute($stmt); //Ejecuta consulta
        $res = mysqli_stmt_get_result($stmt); //Guarda resultado
        $fila = mysqli_fetch_assoc($res); //Obtiene una fila como array asociativo
        mysqli_stmt_close($stmt); //Cierra consulta
        mysqli_free_result($res); //Libera resultado

        return $fila['nombre'] ?? ""; //Si encuentra producto lo devuelve, sino cadena vacia
    }

    //Lista productos de una categoría concreta
    public static function listarPorCategoria(int $idCategoria, bool $soloOfertados = false): array
    {
        $conn = Aplicacion::getInstance()->getConexionBd(); //Conexion a la BD

        //Consulta de productos segun categoria
        $sql = 'SELECT p.id, p.nombre, p.descripcion, p.id_categoria, p.precio_base, p.iva, p.disponible, p.ofertado, p.imagen, c.nombre AS categoria
                FROM productos p
                LEFT JOIN categorias c ON p.id_categoria = c.id
                WHERE p.id_categoria = ?';

        if ($soloOfertados) { //Filtra solo los que se pueden mostrar al cliente en la carta
            $sql .= ' AND p.ofertado = 1 AND p.disponible = 1';
        }

        $sql .= ' ORDER BY p.id'; //Ordena por id
        $stmt = mysqli_prepare($conn, $sql); //Prepara la consulta (seguridad)
        if (!$stmt) {
            return []; //Devuelve vacio si no se puede preparar
        }

        mysqli_stmt_bind_param($stmt, 'i', $idCategoria); //Asocia el valor de id a su ? correspondiente (hueco)
        mysqli_stmt_execute($stmt); //Ejecuta la consulta
        $res = mysqli_stmt_get_result($stmt); //Guarda resultado
        if (!$res) {
            mysqli_stmt_close($stmt); //Cierra consulta en caso de error
            return [];
        }

        $out = [];
        while ($row = mysqli_fetch_assoc($res)) { //Lee cada fila de la consulta y la mete en $out
            $out[] = $row;
        }

        mysqli_stmt_close($stmt); //Cierra consulta
        mysqli_free_result($res); //Libera resultado
        return $out; //Devuelve array
    }
}

