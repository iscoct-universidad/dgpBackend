<?php
	include('actividad.php');
	class Actividades{
		//clase que recupera una lista con todas las actividades de la tabla
		private $actividades=array();
		private $num_actividades;

		public function __construct($conexion){	
			$seleccion = "SELECT * FROM actividad";
			$resultado = mysqli_query ($conexion, $seleccion);
			$n=mysqli_num_rows($resultado);
			$this->num_actividades=$n;
			for($i=0;$i<$n;$i++){
				$fila= mysqli_fetch_assoc ($resultado);
				$this->actividades[$i]=new Actividad();
				$this->actividades[$i]->construct2($fila['id_actividad'], $fila['id_voluntario'],
					$fila['id_socio'], $fila['nombre'], $fila['fecha'], $fila['localizacion'],
					$fila['descripcion'], $fila['puntuacion']);
			}
		}

		public function addActividad($actividad){
			$actividades[]=$actividad;
		}

		public function getActividad($index){
			return $this->actividades[$index];
		}

		public function getNumActividades(){
			return $this->num_actividades;
		}
	}
?>