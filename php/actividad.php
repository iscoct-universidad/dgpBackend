<?php
	class Actividad{
		public $id_actividad;
		public $id_voluntario;
		public $id_socio;
		public $nombre;
		public $fecha;
		public $localizacion;
		public $descripcion;
		public $puntuacion;
		public $cerrada;
		

		/*public function __construct($id, $conexion){
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
			$this->cerrada=$fila['cerrada'];
		}*/

		public function construct2($id_actividad, $id_voluntario, $id_socio, $nombre, $fecha, $localizacion, $descripcion, $puntuacion,$cerrada){
			$this->id_actividad=$id_actividad;
			$this->id_voluntario=$id_voluntario;
			$this->id_socio=$id_socio;
			$this->nombre=$nombre;
			$this->fecha=$fecha;
			$this->localizacion=$localizacion;
			$this->descripcion=$descripcion;
			$this->puntuacion=$puntuacion;
			$this->cerrada=$cerrada;
		}
	}
?>
