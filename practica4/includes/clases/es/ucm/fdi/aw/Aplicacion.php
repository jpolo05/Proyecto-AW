<?php
namespace es\ucm\fdi\aw;

//Clase que mantiene el estado global de la aplicación
class Aplicacion
{
	private static $instancia;
	
	//Devuele la unica instancia de Aplicacion
	public static function getInstance() {
		if (  !self::$instancia instanceof self) {
			self::$instancia = new static(); //Si todavia no existe una instancia la crea
		}
		return self::$instancia;
	}

	private $bdDatosConexion; //Almacena los datos de configuración de la BD
	
	private $inicializada = false; //Almacena si la Aplicacion ya ha sido inicializada
	
	private $conn; //Conexión a la BD
	
	//Evita que se pueda instanciar la clase desde fuera con new
	private function __construct()
	{
	}
	
	/**
	 * Inicializa la aplicación.
     *
     * Opciones de conexión a la BD:
     * <table>
     *   <thead>
     *     <tr>
     *       <th>Opción</th>
     *       <th>Descripción</th>
     *     </tr>
     *   </thead>
     *   <tbody>
     *     <tr>
     *       <td>host</td>
     *       <td>IP / dominio donde se encuentra el servidor de BD.</td>
     *     </tr>
     *     <tr>
     *       <td>bd</td>
     *       <td>Nombre de la BD que queremos utilizar.</td>
     *     </tr>
     *     <tr>
     *       <td>user</td>
     *       <td>Nombre de usuario con el que nos conectamos a la BD.</td>
     *     </tr>
     *     <tr>
     *       <td>pass</td>
     *       <td>Contraseña para el usuario de la BD.</td>
     *     </tr>
     *   </tbody>
     * </table>
	 * 
	 * @param array $bdDatosConexion datos de configuración de la BD
	 */

	//Metodo para inicializar la aplicacion
	public function init($bdDatosConexion)
	{
        if ( ! $this->inicializada ) {
    	    $this->bdDatosConexion = $bdDatosConexion;
    		$this->inicializada = true;
			if (session_status() !== PHP_SESSION_ACTIVE) { //Comprueba si la sesión PHP todavía no está activa
				$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'); //Comprueba si la web se está usando con HTTPS (treu/false)
				session_set_cookie_params([ //Configura la cookie de sesion (lo que permite que el navegador recuerde al usuario entre páginas)
					'lifetime' => 0, //La sesión dura hasta cerrar el navegador
					'path' => '/', //La cookie vale para toda la web
					'domain' => '', 
					'secure' => $isHttps,
					'httponly' => true, //JS no puede leerla
					'samesite' => 'Lax', //Para proteccion
				]);
				session_start();
			}
        }
	}
	
	//Cierre de la aplicacion
	public function shutdown()
	{
	    $this->compruebaInstanciaInicializada(); //Comprueba que la aplicación esté inicializada
	    if ($this->conn !== null && ! $this->conn->connect_errno) { //Si hay conexión abierta con la base de datos, la cierra
	        $this->conn->close();
	    }
	}
	
	//Comprueba si la aplicación está inicializada. Si no lo está muestra un mensaje y termina la ejecución
	private function compruebaInstanciaInicializada()
	{
	    if (! $this->inicializada ) {
	        echo "Aplicacion no inicializa";
	        exit();
	    }
	}
	
	//Devuelve una conexión a la BD. Se encarga de que exista como mucho una conexión a la BD por petición
	public function getConexionBd()
	{
	    $this->compruebaInstanciaInicializada(); //Comprueba que la aplicación esté inicializada

		//Si aun no hay conexion, la crea
		if (! $this->conn ) {
			$bdHost = $this->bdDatosConexion['host']; //Datos de conexion guardados en init()
			$bdUser = $this->bdDatosConexion['user'];
			$bdPass = $this->bdDatosConexion['pass'];
			$bd = $this->bdDatosConexion['bd'];
			
			$conn = new \mysqli($bdHost, $bdUser, $bdPass, $bd); //Crea una conexión a MySQL usando mysqli
			if ( $conn->connect_errno ) { //Comprueba si ha habido error conectando
				echo "Error de conexión a la BD ({$conn->connect_errno}):  {$conn->connect_error}";
				exit();
			}
			if ( ! $conn->set_charset("utf8mb4")) { //Comprueba si usa utf8mb4 
				echo "Error al configurar la BD ({$conn->errno}):  {$conn->error}";
				exit();
			}
			$this->conn = $conn; //Guarda la conexión dentro del objeto Aplicacion
		}
		return $this->conn; //Devuelve la conexión para que otras clases puedan hacer consultas SQL
	}
}
