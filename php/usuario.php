<?php
	class Usuario{
		public $id;
		public $rol;
		public $nombre;
		public $apellido1;
		public $apellido2;
		public $DNI;
		public $fecha_nacimiento;
		public $localidad;
		public $email;
		public $password;
		public $telefono;
		public $aspiraciones;
		public $observaciones;
		public $gustos = array();

		/*public function __construct($mail, $conexion){
			$seleccion = "SELECT * FROM usuarios WHERE email=" . $mail;
			$resultado = mysqli_query ($conexion, $seleccion);
			$fila = mysqli_fetch_assoc ($resultado);

			$this->id=$fila['id'];
			$this->rol=$fila['rol'];
			$this->nombre=$fila['nombre'];
			$this->apellido1=$fila['apellido1'];
			$this->apellido2=$fila['apellido2'];
			$this->DNI=$fila['DNI'];
			$this->fecha_nacimiento=$fila['fecha_nacimiento'];
			$this->localidad=$fila['localidad'];
			$this->email=$fila['email'];
			$this->telefono=$fila['telefono'];
			$this->aspiraciones=$fila['aspiraciones'];
			$this->observaciones=$fila['observaciones'];
			$this->password=$fila['password'];
		}*/

		public function construct2($rol, $nombre, $apellido1, $apellido2, $DNI, $fecha_nacimiento, $localidad, $email, $telefono, $aspiraciones, $observaciones,$password,$gustos){
			$this->rol=$rol;
			$this->nombre=$nombre;
			$this->apellido1=$apellido1;
			$this->apellido2=$apellido2;
			$this->DNI=$DNI;
			$this->fecha_nacimiento=date($fecha_nacimiento);
			$this->localidad=$localidad;
			$this->email=$email;
			$this->telefono=$telefono;
			$this->aspiraciones=$aspiraciones;
			$this->observaciones=$observaciones;
			$this->password=$password;
			$this->gustos=$gustos;
		}
	}
?>
