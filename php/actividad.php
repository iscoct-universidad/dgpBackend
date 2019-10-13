<?php
	class Actividad{
		private $id_actividad;
		private $id_voluntario;
		private $id_socio;
		private $nombre;
		private $fecha;
		private $localizacion;
		private $descripcion;
		private $puntuacion;
		

		public function __construct($id, $conexion){
			$seleccion = "SELECT * FROM actividad WHERE id_actividad=" . $id;
			$resultado = mysqli_query ($conexion, $seleccion);
			$fila = mysqli_fetch_assoc ($resultado);

			$this->id_actividad=$fila['id_actividad'];
			$this->id_voluntario=$fila['id_voluntario'];
			$this->id_socio=$fila['id_socio'];
			$this->nombre=$fila['nombre'];
			$this->fecha=$fila['fecha'];
			$this->localizacion=$fila['localizacion'];
			$this->descripcion=$fila['descripcion'];
			$this->puntuacion=$fila['puntuacion'];
		}

		public function construct2($id_actividad, $id_voluntario, $id_socio, $nombre, $fecha, $localizacion, $descripcion, $puntuacion){
			$this->id_actividad=$id_actividad;
			$this->id_voluntario=$id_voluntario;
			$this->id_socio=$id_socio;
			$this->nombre=$nombre;
			$this->fecha=$fecha;
			$this->localizacion=$localizacion;
			$this->descripcion=$descripcion;
			$this->puntuacion=$puntuacion;
		}

		public function getId_actividad(){
			return $this->id_actividad;
		}
		
		public function getId_voluntario(){
			return $this->id_voluntario;
		}

		public function getId_socio(){
			return $this->id_socio;
		}

		public function getNombre(){
			return $this->nombre;
		}

		public function getFecha(){
			return $this->fecha;
		}

		public function getLocalizacion(){
			return $this->localizacion;
		}

		public function getDescripcion(){
			return $this->descripcion;
		}

		public function getPuntuacion(){
			return $this->puntuacion;
		}

		public function setId_actividad($id_actividad){
			$this->id_actividad=$id_actividad;
		}
		
		public function setId_voluntario($id_voluntario){
			$this->id_voluntario=$id_voluntario;
		}

		public function setId_socio($id_socio){
			$this->id_socio=$id_socio;
		}

		public function setNombre($nombre){
			$this->nombre=$nombre;
		}

		public function setFecha($fecha){
			$this->fecha=$fecha;
		}

		public function setLocalizacion($localizacion){
			$this->localizacion=$localizacion;
		}

		public function setDescripcion($descripcion){
			$this->descripcion=$descripcion;
		}

		public function setPuntuacion($puntuacion){
			$this->puntuacion=$puntuacion;
		}
	}
?>