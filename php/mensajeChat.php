<?php
	class MensajeChat{
        public $id_mensaje;
        public $id_actividad;
		public $nombre;
		public $apellido1;
		public $apellido2;
		public $fecha;
		public $tipo;
		public $contenido;

		public function constructFromAssociativeArray($associativeArray){
            $this->id_mensaje=$associativeArray['id_mensaje'];
            $this->id_actividad=$associativeArray['id_actividad'];
			$this->nombre=$associativeArray['nombre'];
			$this->apellido1=$associativeArray['apellido1'];
			$this->apellido2=$associativeArray['apellido2'];
			$this->fecha=$associativeArray['fecha'];
			$this->tipo=$associativeArray['tipo'];
			$this->contenido=$associativeArray['contenido'];
		}
	}
?>