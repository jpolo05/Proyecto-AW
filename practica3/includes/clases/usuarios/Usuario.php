<?php
namespace es\ucm\fdi\aw;

class Usuario
{
    public const ADMIN_ROLE = 'Gerente';
    public const USER_ROLE = 'Cliente';

    private $nombreUsuario;
    private $password;
    private $nombre;
    private $apellidos;
    private $email;
    private $rol;
    private $imagen;

    private function __construct($nombreUsuario, $password, $nombre, $apellidos, $email, $rol, $imagen)
    {
        $this->nombreUsuario = $nombreUsuario;
        $this->password = $password;
        $this->nombre = $nombre;
        $this->apellidos = $apellidos;
        $this->email = $email;
        $this->rol = $rol;
        $this->imagen = $imagen;
    }

    public static function login($nombreUsuario, $password)
    {
        $usuario = self::buscaUsuario($nombreUsuario);
        if ($usuario && $usuario->compruebaPassword($password)) {
            return $usuario;
        }
        return false;
    }

    public static function crea($nombreUsuario, $password, $nombre, $apellidos, $email, $rol = self::USER_ROLE, $imagen = null)
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $sql = "INSERT INTO usuarios (user, email, nombre, apellidos, contrasena, rol, imagen) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            return false;
        }

        $hash = self::hashPassword($password);
        $imagen = self::normalizaImagen($imagen);
        mysqli_stmt_bind_param($stmt, 'sssssss', $nombreUsuario, $email, $nombre, $apellidos, $hash, $rol, $imagen);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $ok ? self::buscaUsuario($nombreUsuario) : false;
    }

    public static function buscaUsuario($nombreUsuario)
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $sql = "SELECT user, email, nombre, apellidos, contrasena, rol, imagen FROM usuarios WHERE user = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            return false;
        }

        mysqli_stmt_bind_param($stmt, 's', $nombreUsuario);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $fila = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);

        if (!$fila) {
            return false;
        }

        return new Usuario(
            $fila['user'],
            $fila['contrasena'],
            $fila['nombre'],
            $fila['apellidos'],
            $fila['email'],
            $fila['rol'],
            $fila['imagen']
        );
    }

    public static function listar(): array
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $sql = 'SELECT user, email, nombre, rol FROM usuarios ORDER BY user';
        $res = mysqli_query($conn, $sql);

        if (!$res) {
            return [];
        }

        $out = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $out[] = $row;
        }
        mysqli_free_result($res);

        return $out;
    }

    public static function crearEditar($user, $email, $nombre, $apellidos, $contrasena, $rol, $imagen): bool
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $imagen = self::normalizaImagen($imagen);

        if ($contrasena === null) {
            $sql = 'UPDATE usuarios SET email = ?, nombre = ?, apellidos = ?, rol = ?, imagen = ? WHERE user = ?';
            $stmt = mysqli_prepare($conn, $sql);
            if (!$stmt) {
                return false;
            }

            mysqli_stmt_bind_param($stmt, 'ssssss', $email, $nombre, $apellidos, $rol, $imagen, $user);
        } else {
            $sql = "INSERT INTO usuarios (user, email, nombre, apellidos, contrasena, rol, imagen)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                      email = VALUES(email),
                      nombre = VALUES(nombre),
                      apellidos = VALUES(apellidos),
                      contrasena = VALUES(contrasena),
                      rol = VALUES(rol),
                      imagen = VALUES(imagen)";
            $stmt = mysqli_prepare($conn, $sql);
            if (!$stmt) {
                return false;
            }

            mysqli_stmt_bind_param($stmt, 'sssssss', $user, $email, $nombre, $apellidos, $contrasena, $rol, $imagen);
        }

        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }

    public static function actualizarRol($user, $rol): bool
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $sql = 'UPDATE usuarios SET rol = ? WHERE user = ?';
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            return false;
        }

        mysqli_stmt_bind_param($stmt, 'ss', $rol, $user);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }

    public static function borrar($user): bool
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $sql = 'DELETE FROM usuarios WHERE user = ?';
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            return false;
        }

        mysqli_stmt_bind_param($stmt, 's', $user);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }

    public static function rutaPorRol($rol): string
    {
        switch ($rol) {
            case 'Gerente':
                return RUTA_APP.'admin.php';
            case 'Cocinero':
                return RUTA_APP.'includes/vistas/paneles/cocinero.php';
            case 'Camarero':
                return RUTA_APP.'includes/vistas/paneles/camarero.php';
            default:
                return RUTA_APP.'index.php';
        }
    }

    private static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function getNombreUsuario()
    {
        return $this->nombreUsuario;
    }

    public function getNombre()
    {
        return $this->nombre;
    }

    public function getApellidos()
    {
        return $this->apellidos;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getRol()
    {
        return $this->rol;
    }

    public function getImagen()
    {
        return $this->imagen;
    }

    public function tieneRol($rol)
    {
        return $this->rol === $rol;
    }

    public function compruebaPassword($password)
    {
        return password_verify($password, $this->password);
    }

    private static function normalizaImagen($imagen): string
    {
        if ($imagen === null || $imagen === '' || $imagen === 'propia') {
            return 'img/uploads/usuarios/default.jpg';
        }

        if (preg_match('/^https?:\\/\\//', $imagen) === 1) {
            return $imagen;
        }

        $map = ['default.jpg', 'avatar1.jpg', 'avatar2.jpg', 'avatar3.jpg'];
        if (in_array($imagen, $map, true)) {
            return 'img/uploads/usuarios/'.$imagen;
        }

        return ltrim($imagen, '/');
    }
}
