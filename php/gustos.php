<?php
	class Gustos{
        public $id_usuario;
		public $gustos=array();
		public $num_gustos;

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
		
		public function addGusto($Gusto){
			$gustos[]=$gusto;
		}

		public function getGusto($index){
			return $this->actividades[$index];
		}
	}
?>