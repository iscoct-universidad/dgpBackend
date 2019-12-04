<?php
	class Actividad{
		public $id_actividad;
		public $nombre;
		public $fecha;
		public $localizacion;
		public $descripcion;
		public $cerrada;
		public $imagen;
		public $id_creador;
		public $etiquetas = array();
		public $participantes = array();
		public $mensajes_chat = array();
		public $valoraciones = array();
		public $tipo;

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
		public function constructFromAssociativeArray($associativeArray){
			$this->id_actividad=$associativeArray['id_actividad'];
			$this->nombre=$associativeArray['nombre'];
			$this->fecha=$associativeArray['fecha'];
			$this->localizacion=$associativeArray['localizacion'];
			$this->descripcion=$associativeArray['descripcion'];
			$this->id_creador=$associativeArray['id_creador'];
			$this->cerrada=$associativeArray['cerrada'];
			$this->imagen=$associativeArray['imagen'];
			$this->tipo=$associativeArray['tipo'];
		}
	}
?>
