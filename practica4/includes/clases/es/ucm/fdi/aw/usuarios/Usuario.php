<?php
namespace es\ucm\fdi\aw\usuarios;
use es\ucm\fdi\aw\Aplicacion; //Usa la clase Aplicacion

class Usuario
{
    //Roles principales
    public const ADMIN_ROLE = 'Gerente';
    public const USER_ROLE = 'Cliente';

    //Datos del usuario
    private $nombreUsuario;
    private $password;
    private $nombre;
    private $apellidos;
    private $email;
    private $rol;
    private $imagen;
    private $bistroCoins;

    //Constructor privado para crear usuarios desde la propia clase
    private function __construct($nombreUsuario, $password, $nombre, $apellidos, $email, $rol, $imagen, $bistroCoins)
    {
        $this->nombreUsuario = $nombreUsuario; //Guarda nombre de usuario
        $this->password = $password; //Guarda contraseña cifrada
        $this->nombre = $nombre; //Guarda nombre
        $this->apellidos = $apellidos; //Guarda apellidos
        $this->email = $email; //Guarda email
        $this->rol = $rol; //Guarda rol
        $this->imagen = $imagen; //Guarda imagen
        $this->bistroCoins = (int)$bistroCoins; //Guarda BistroCoins
    }

    //Comprueba login de usuario
    public static function login($nombreUsuario, $password)
    {
        $usuario = self::buscaUsuario($nombreUsuario); //Busca usuario
        if ($usuario && $usuario->compruebaPassword($password)) { //Comprueba contraseña
            return $usuario;
        }
        return false; //Login incorrecto
    }

    //Crea un usuario nuevo
    public static function crea($nombreUsuario, $password, $nombre, $apellidos, $email, $rol = self::USER_ROLE, $imagen = null)
    {
        $conn = Aplicacion::getInstance()->getConexionBd(); //Obtiene conexion a la BD
        $sql = "INSERT INTO usuarios (user, email, nombre, apellidos, contrasena, rol, imagen, bistroCoins) VALUES (?, ?, ?, ?, ?, ?, ?, 0)";
        $stmt = mysqli_prepare($conn, $sql); //Prepara insercion
        if (!$stmt) { //Si falla
            return false;
        }

        $hash = self::hashPassword($password); //Cifra contraseña
        $imagen = self::normalizaImagen($imagen); //Normaliza ruta de imagen
        mysqli_stmt_bind_param($stmt, 'sssssss', $nombreUsuario, $email, $nombre, $apellidos, $hash, $rol, $imagen); //Asocia datos
        $ok = mysqli_stmt_execute($stmt); //Ejecuta insercion
        mysqli_stmt_close($stmt); //Cierra statement

        return $ok ? self::buscaUsuario($nombreUsuario) : false; //Devuelve usuario creado
    }

    //Busca un usuario por su nombre de usuario
    public static function buscaUsuario($nombreUsuario)
    {
        $conn = Aplicacion::getInstance()->getConexionBd(); //Obtiene conexion a la BD
        $sql = "SELECT user, email, nombre, apellidos, contrasena, rol, imagen, bistroCoins FROM usuarios WHERE user = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql); //Prepara consulta
        if (!$stmt) { //Si falla
            return false;
        }

        mysqli_stmt_bind_param($stmt, 's', $nombreUsuario); //Asocia usuario
        mysqli_stmt_execute($stmt); //Ejecuta consulta
        $res = mysqli_stmt_get_result($stmt); //Obtiene resultado
        $fila = mysqli_fetch_assoc($res); //Recoge fila
        mysqli_stmt_close($stmt); //Cierra statement
        mysqli_free_result($res); //Libera resultado

        if (!$fila) { //Si no existe
            return false;
        }

        return new Usuario( //Crea objeto usuario
            $fila['user'],
            $fila['contrasena'],
            $fila['nombre'],
            $fila['apellidos'],
            $fila['email'],
            $fila['rol'],
            $fila['imagen'],
            $fila['bistroCoins']
        );
    }

    //Lista usuarios para gestion
    public static function listar(): array
    {
        $conn = Aplicacion::getInstance()->getConexionBd(); //Obtiene conexion a la BD
        $sql = 'SELECT user, email, nombre, rol, bistroCoins FROM usuarios ORDER BY user';
        $res = mysqli_query($conn, $sql); //Ejecuta consulta

        if (!$res) { //Si falla
            return [];
        }

        $out = []; //Array de usuarios
        while ($row = mysqli_fetch_assoc($res)) { //Recorre usuarios
            $out[] = $row; //Añade usuario
        }
        mysqli_free_result($res); //Libera resultado

        return $out; //Devuelve usuarios
    }

    //Crea o edita un usuario desde el panel
    public static function crearEditar($user, $email, $nombre, $apellidos, $contrasena, $rol, $imagen): bool
    {
        $conn = Aplicacion::getInstance()->getConexionBd(); //Obtiene conexion a la BD
        $imagen = self::normalizaImagen($imagen); //Normaliza imagen

        if ($contrasena === null) { //Si no se cambia la contraseña
            $sql = 'UPDATE usuarios SET email = ?, nombre = ?, apellidos = ?, rol = ?, imagen = ? WHERE user = ?';
            $stmt = mysqli_prepare($conn, $sql); //Prepara actualizacion
            if (!$stmt) { //Si falla
                return false;
            }

            mysqli_stmt_bind_param($stmt, 'ssssss', $email, $nombre, $apellidos, $rol, $imagen, $user); //Asocia datos
        } else { //Si se crea o cambia contraseña
            $hash = self::hashPassword($contrasena); //Cifra contraseña
            $sql = "INSERT INTO usuarios (user, email, nombre, apellidos, contrasena, rol, imagen)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                      email = VALUES(email),
                      nombre = VALUES(nombre),
                      apellidos = VALUES(apellidos),
                      contrasena = VALUES(contrasena),
                      rol = VALUES(rol),
                      imagen = VALUES(imagen)";
            $stmt = mysqli_prepare($conn, $sql); //Prepara insercion o actualizacion
            if (!$stmt) { //Si falla
                return false;
            }

            mysqli_stmt_bind_param($stmt, 'sssssss', $user, $email, $nombre, $apellidos, $hash, $rol, $imagen); //Asocia datos
        }

        $ok = mysqli_stmt_execute($stmt); //Ejecuta consulta
        mysqli_stmt_close($stmt); //Cierra statement
        return $ok; //Devuelve resultado
    }

    //Actualiza el rol de un usuario
    public static function actualizarRol($user, $rol): bool
    {
        $conn = Aplicacion::getInstance()->getConexionBd(); //Obtiene conexion a la BD
        $sql = 'UPDATE usuarios SET rol = ? WHERE user = ?';
        $stmt = mysqli_prepare($conn, $sql); //Prepara actualizacion
        if (!$stmt) { //Si falla
            return false;
        }

        mysqli_stmt_bind_param($stmt, 'ss', $rol, $user); //Asocia rol y usuario
        $ok = mysqli_stmt_execute($stmt); //Ejecuta actualizacion
        mysqli_stmt_close($stmt); //Cierra statement
        return $ok; //Devuelve resultado
    }

    //Actualiza los BistroCoins de un usuario
    public static function actualizaBistroCoins($user, int $bistroCoins): bool
    {
        if ($user === '' || $bistroCoins < 0) { //Comprueba datos
            return false;
        }

        $conn = Aplicacion::getInstance()->getConexionBd(); //Obtiene conexion a la BD
        $sql = 'UPDATE usuarios SET bistroCoins = ? WHERE user = ?';
        $stmt = mysqli_prepare($conn, $sql); //Prepara actualizacion
        if (!$stmt) { //Si falla
            return false;
        }

        mysqli_stmt_bind_param($stmt, 'is', $bistroCoins, $user); //Asocia coins y usuario
        $ok = mysqli_stmt_execute($stmt); //Ejecuta actualizacion
        mysqli_stmt_close($stmt); //Cierra statement
        return $ok; //Devuelve resultado
    }

    //Suma BistroCoins a un usuario
    public static function sumaBistroCoins($user, int $cantidad): bool
    {
        if ($user === '' || $cantidad <= 0) { //Comprueba datos
            return false;
        }

        $conn = Aplicacion::getInstance()->getConexionBd(); //Obtiene conexion a la BD
        $sql = 'UPDATE usuarios SET bistroCoins = bistroCoins + ? WHERE user = ?';
        $stmt = mysqli_prepare($conn, $sql); //Prepara actualizacion
        if (!$stmt) { //Si falla
            return false;
        }

        mysqli_stmt_bind_param($stmt, 'is', $cantidad, $user); //Asocia cantidad y usuario
        $ok = mysqli_stmt_execute($stmt); //Suma coins
        mysqli_stmt_close($stmt); //Cierra statement
        return $ok; //Devuelve resultado
    }

    //Resta BistroCoins a un usuario
    public static function restaBistroCoins($user, int $cantidad): bool
    {
        if ($user === '' || $cantidad <= 0) { //Comprueba datos
            return false;
        }

        $conn = Aplicacion::getInstance()->getConexionBd(); //Obtiene conexion a la BD
        $sql = 'UPDATE usuarios SET bistroCoins = bistroCoins - ? WHERE user = ? AND bistroCoins >= ?';
        $stmt = mysqli_prepare($conn, $sql); //Prepara actualizacion
        if (!$stmt) { //Si falla
            return false;
        }

        mysqli_stmt_bind_param($stmt, 'isi', $cantidad, $user, $cantidad); //Asocia datos
        $ok = mysqli_stmt_execute($stmt); //Resta coins
        if ($ok) { //Si no falla la consulta
            $ok = mysqli_stmt_affected_rows($stmt) > 0; //Comprueba que se restaron
        }
        mysqli_stmt_close($stmt); //Cierra statement
        return $ok; //Devuelve resultado
    }

    //Borra un usuario
    public static function borrar($user): bool
    {
        $conn = Aplicacion::getInstance()->getConexionBd(); //Obtiene conexion a la BD
        $sql = 'DELETE FROM usuarios WHERE user = ?';
        $stmt = mysqli_prepare($conn, $sql); //Prepara borrado
        if (!$stmt) { //Si falla
            return false;
        }

        mysqli_stmt_bind_param($stmt, 's', $user); //Asocia usuario
        $ok = mysqli_stmt_execute($stmt); //Ejecuta borrado
        mysqli_stmt_close($stmt); //Cierra statement
        return $ok; //Devuelve resultado
    }

    //Devuelve la ruta de inicio segun el rol
    public static function rutaPorRol($rol): string
    {
        switch ($rol) { //Comprueba rol
            case 'Gerente':
                return RUTA_APP.'admin.php'; //Panel gerente
            case 'Cocinero':
                return RUTA_APP.'includes/vistas/paneles/cocinero.php'; //Panel cocina
            case 'Camarero':
                return RUTA_APP.'includes/vistas/paneles/camarero.php'; //Panel camarero
            default:
                return RUTA_APP.'index.php'; //Inicio cliente
        }
    }

    //Cifra la contraseña con pepper
    private static function hashPassword($password)
    {
        return password_hash(self::passwordConPepper($password), PASSWORD_DEFAULT);
    }

    //Añade pepper a la contraseña
    private static function passwordConPepper($password): string
    {
        $pepper = defined('AUTH_PASSWORD_PEPPER') ? (string)AUTH_PASSWORD_PEPPER : ''; //Obtiene pepper si existe
        return (string)$password.$pepper; //Devuelve contraseña con pepper
    }

    //Devuelve el nombre de usuario
    public function getNombreUsuario()
    {
        return $this->nombreUsuario;
    }

    //Devuelve el nombre
    public function getNombre()
    {
        return $this->nombre;
    }

    //Devuelve los apellidos
    public function getApellidos()
    {
        return $this->apellidos;
    }

    //Devuelve el email
    public function getEmail()
    {
        return $this->email;
    }

    //Devuelve el rol
    public function getRol()
    {
        return $this->rol;
    }

    //Devuelve la imagen
    public function getImagen()
    {
        return $this->imagen;
    }

    //Devuelve los BistroCoins
    public function getBistroCoins()
    {
        return $this->bistroCoins;
    }

    //Comprueba si el usuario tiene un rol
    public function tieneRol($rol)
    {
        return $this->rol === $rol;
    }

    //Comprueba contraseña del usuario
    public function compruebaPassword($password)
    {
        return password_verify(self::passwordConPepper($password), $this->password);
    }

    //Normaliza la imagen del usuario
    private static function normalizaImagen($imagen): string
    {
        if ($imagen === null || $imagen === '' || $imagen === 'propia') { //Si no hay imagen personalizada
            return 'img/uploads/usuarios/default.jpg';
        }

        if (preg_match('/^https?:\\/\\//', $imagen) === 1) { //Si es una URL externa
            return $imagen;
        }

        $map = ['default.jpg', 'avatar1.jpg', 'avatar2.jpg', 'avatar3.jpg']; //Avatares permitidos
        if (in_array($imagen, $map, true)) { //Si es un avatar del sistema
            return 'img/uploads/usuarios/'.$imagen;
        }

        return ltrim($imagen, '/'); //Devuelve ruta sin barra inicial
    }
}
