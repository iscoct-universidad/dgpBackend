<?php
	class MensajeChat{
        public $id_mensaje;
        public $id_actividad;
		public $id_participante;
		public $fecha;
		public $tipo;
		public $contenido;

		public function constructFromAssociativeArray($associativeArray){
            $this->id_mensaje=$associativeArray['id_mensaje'];
            $this->id_actividad=$associativeArray['id_actividad'];
			$this->id_participante=$associativeArray['id_participante'];
			$this->fecha=$associativeArray['fecha'];
			$this->tipo=$associativeArray['tipo'];
			$this->texto=$associativeArray['contenido'];
		}
	}
?>