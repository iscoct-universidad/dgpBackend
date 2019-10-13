<?php
	class Gustos{
        private $id_usuario;
		private $gustos=array();
		private $num_gustos;

		public function __construct($id, $conexion){
			$seleccion = "SELECT * FROM gustos WHERE id_usuario=" . $id_usuario;
			$resultado = mysqli_query ($conexion, $seleccion);
			$n=mysqli_num_rows($resultado);
			$this->num_gustos=$n;

			for($i=0;$i<$n;$i++){
				$fila= mysqli_fetch_assoc ($resultado);
				$this->gustos[$i]=$fila['gusto'];
			}
		}

		public function getId_usuario(){
			return $this->id_usuario;
		}
		
		public function addGusto($Gusto){
			$gustos[]=$gusto;
		}

		public function getGusto($index){
			return $this->actividades[$index];
		}

		public function getNumGustos(){
			return $this->num_Gustos;
		}

		public function setId_usuario($id_usuario){
			$this->id_usuario=$id_usuario;
		}
	}
?>