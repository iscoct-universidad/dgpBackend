<?php
	class Usuario{
		private $id;
		private $rol;
		private $nombre;
		private $apellido1;
		private $apellido2;
		private $DNI;
		private $fecha_nacimiento;
		private $localidad;
		private $email;
		private $telefono;
		private $aspiraciones;
		private $observaciones;

		public function __construct($mail, $conexion){
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
		}

		public function construct2($id, $rol, $nombre, $apellido1, $apellido2, $DNI, $fecha_nacimiento, $localidad, $email, $telefono, $aspiraciones, $observaciones){
			$this->id=$id;
			$this->rol=$rol;
			$this->nombre=$nombre;
			$this->apellido1=$apellido1;
			$this->apellido2=$apellido2;
			$this->DNI=$DNI;
			$this->fecha_nacimiento=$fecha_nacimiento;
			$this->localidad=$localidad;
			$this->email=$email;
			$this->telefono=$telefono;
			$this->aspiraciones=$aspiraciones;
			$this->observaciones=$observaciones;
		}

		public function getId(){
			return $this->id;
		}
		
		public function getRol(){
			return $this->rol;
		}
		
		public function getNombre(){
			return $this->nombre;
		}
		
		public function getApellido1(){
			return $this->apellido1;
		}
		
		public function getApellido2(){
			return $this->apellido2;
		}
		
		public function getDNI(){
			return $this->DNI;
		}
		
		public function getFecha_nacimiento(){
			return $this->fecha_nacimiento;
		}
		
		public function getLocalidad(){
			return $this->localidad;
		}
		
		public function getEmail(){
			return $this->email;
		}
		
		public function getTelefono(){
			return $this->telefono;
		}
		
		public function getAspiraciones(){
			return $this->aspiraciones;
		}
		
		public function getObservaciones(){
			return $this->observaciones;
		}


		public function setId($id){
			$this->id=$id;
		}
		
		public function setRol($rol){
			$this->rol=$rol;
		}
		
		public function setNombre($nombre){
			$this->nombre=$nombre;
		}
		
		public function setApellido1($apellido1){
			$this->apellido1=$apellido1;
		}
		
		public function setApellido2($apellido2){
			$this->apellido2=$apellido2;
		}
		
		public function setDNI($DNI){
			$this->DNI=$DNI;
		}
		
		public function setFecha_nacimiento($fecha_nacimiento){
			$this->fecha_nacimiento=$fecha_nacimiento;
		}
		
		public function setLocalidad($localidad){
			$this->localidad=$localidad;
		}
		
		public function setEmail($email){
			$this->email=$email;
		}
		
		public function setTelefono($telefono){
			$this->telefono=$telefono;
		}
		
		public function setAspiraciones($aspiracione){
			$this->aspiraciones=$aspiraciones;
		}
		
		public function setObservaciones($observaciones){
			$this->observaciones=$observaciones;
		}
	}
?>