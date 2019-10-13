<?php
	include('usuario.php');
	class Usuarios{
		//clase que recupera una lista con todos los usuarios de la tabla
		private $usuarios=array();
		private $num_usuarios;

		public function __construct($conexion){	
			$seleccion = "SELECT * FROM usuario";
			$resultado = mysqli_query ($conexion, $seleccion);
			$n=mysqli_num_rows($resultado);
			$this->num_usuarios=$n;
			for($i=0;$i<$n;$i++){
				$fila= mysqli_fetch_assoc ($resultado);
				$this->usuarios[$i]=new Usuario();
				$this->usuarios[$i]->construct2($fila['id'], $fila['rol'], $fila['nombre'],
					$fila['apellido1'], $fila['apellido2'], $fila['DNI'], $fila['fecha_nacimiento'],
					$fila['localidad'], $fila['email'], $fila['telefono'], $fila['aspiraciones'],
					$fila['observaciones']);
			}
		}

		public function addUsuario($usuario){
			$usuarios[]=$usuario;
		}

		public function getUsuario($index){
			return $this->usuarios[$index];
		}

		public function getNumUsuarios(){
			return $this->num_usuarios;
		}
	}
?>