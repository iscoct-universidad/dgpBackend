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
		public $imagen;
		public $gustos = array();

		public function construct2($rol, $nombre, $apellido1, $apellido2, $DNI, $fecha_nacimiento, $localidad, $email, $telefono, $aspiraciones, $observaciones,$password,$imagen,$gustos){
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
			$this->imagen=$imagen;
			$this->gustos=$gustos;
		}

		public function construct_participante($id, $rol, $nombre, $apellido1, $apellido2){
			$this->id=$id;
			$this->rol=$rol;
			$this->nombre=$nombre;
			$this->apellido1=$apellido1;
			$this->apellido2=$apellido2;
		}
	}
?>
