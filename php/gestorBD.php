<?php
	class gestorBD{
		//Clase para modificar la base de datos
		private $conexion;

		//Fucnión que realiza la conexión con la BD
		public function __construct(){
			$host='localhost';
			$user='dgp_user';
			$pass='12341234#Sql';
			$DB='dgp_db';
			$this->conexion = new mysqli ($host, $user, $pass, $DB);
			$this->conexion->set_charset("utf8");
			if ($this->conexion->connect_error){
		    	echo "Conexion a BD fallida";
		    	exit();
			}
		}

		public function close(){
			mysqli_close($this->conexion);
		}

		//Añade la actividad '$actividad' a la tabla, si no hay ya una actividad con ese nombre
		public function regActividad($actividad){
			$comprobar="SELECT * FROM actividades WHERE nombre=" . $actividad->nombre;
			$resultado=mysqli_query($this->conexion, $comprobar);
			if(mysqli_num_rows($resultado)>0){
				echo "Error al registrar: La actividad ya existe.";
				exit();
			}
			else if(is_null($actividad->nombre) or is_null($actividad->fecha) or is_null($actividad->localizacion) or is_null($actividad->descripcion)){
				echo "Error al registrar actividad: hay campos obligatorios vacíos.";
				exit();
			}
			else{
				$envio = "INSERT INTO actividades (id_voluntario, id_socio, nombre, fecha, localizacion, descripcion, puntuacion, cerrada)
					VALUES ($actividad->id_voluntario, $actividad->id_socio, $actividad->nombre, $actividad->fecha,
					$actividad->localizacion, $actividad->descripcion, $actividad->puntuacion, $actividad->cerrada)";
				mysqli_query($this->conexion, $envio);
			}
		}

		//Añade el usuario '$usuario' a la tabla, si no hay ya un usuario con ese email
		public function regUsuario($usuario){
			$consulta1=$this->conexion->prepare("SELECT * FROM usuario WHERE email=?");
			$consulta1->bind_param("s",$usuario->email);
			$consulta1->execute();
			if($consulta1->get_result()->num_rows){
				echo "Error al registrar: El usuario ya existe.";
				return false;
			}
			else if(is_null($usuario->rol) or is_null($usuario->nombre) or is_null($usuario->apellido1) or is_null($usuario->apellido2) or 
					is_null($usuario->DNI) or is_null($usuario->fecha_nacimiento) or is_null($usuario->localidad) or is_null($usuario->email) or 
					is_null($usuario->telefono) or is_null($usuario->password)){
				echo "Error al registrar usuario: Hay campos obligatorios vacíos.";
				return false;
			}
			else if ($this->comprobarRolAdministrador($_SESSION['id_usuario'])){
				echo "Error al registrar usuario: El usuario no es administrador.";
				return false;
			}
			else if (!($usuario->rol=='administrador' or $usuario->rol=='voluntario' or $usuario->rol=='socio')){
				echo "Error al registrar usuario: El rol del usuario no es válido.";
				return false;				
			}
			else{
				$exito=false;
				$consulta=$this->conexion->prepare("INSERT INTO usuario (rol, nombre, apellido1, apellido2, DNI, fecha_nacimiento, localidad, email, telefono, aspiraciones, observaciones, password)
				 VALUES (?,?,?,?,?,?,?,?,?,?,?,?);");
				$consulta->bind_param("ssssssssisss",$usuario->rol,$usuario->nombre, $usuario->apellido1, $usuario->apellido2, 
							$usuario->DNI,$usuario->fecha_nacimiento,$usuario->localidad,$usuario->email, $usuario->telefono,
							$usuario->aspiraciones, $usuario->observaciones, $usuario->password);
				$consulta->execute();
				$exito=$consulta->affected_rows;
				$usuario->id = $this->getIdUsuario($usuario->email);
				$num_gustos = sizeof($usuario->gustos,0);
				for ($i=0;$i<$num_gustos;$i++){
					$exito_gusto=$this->regGusto($usuario,$usuario->gustos[$i]);
					$exito=($exito && $exito_gusto);
				}
				return $exito;
			}
		}

		public function getIdUsuario ($email){
			$consulta=$this->conexion->prepare("SELECT id FROM usuario WHERE email=?;");
			$consulta->bind_param("s",$email);
			$consulta->execute();
			$fila_resultado = $consulta->get_result()->fetch_assoc();
			return $fila_resultado['id'];
		}

		public function comprobarRolAdministrador($id){
			$consulta=$this->conexion->prepare("SELECT rol FROM usuario WHERE id=?");
			$consulta->bind_param("i",$id);
			$consulta->execute();
			$fila_resultado = $consulta->get_result()->fetch_assoc();
			if($fila_resultado['rol']=="administrador")	return true;
			else return false;
		}

		//Función para identificar al usuario
		public function identificarUsuario ($usuario){
			$consulta=$this->conexion->prepare("SELECT * FROM usuario WHERE email= ? AND password= ?;");
			$consulta->bind_param("ss",$usuario->email,$usuario->password);
			$consulta->execute();
			if($consulta->get_result()->num_rows){ 
				$_SESSION['id_usuario']=$this->getIdUsuario($usuario->email);
				return true;
			}
			else return false;
		}

		//Añade el gusto '$gusto' al usuario '$usuario'
		public function regGusto($usuario, $gusto){

			$consulta=$this->conexion->prepare("SELECT * FROM gustos WHERE id_usuario=? AND gusto=?");
			$consulta->bind_param("is",$usuario->id,$gusto);
			$consulta->execute();
			if($consulta->get_result()->num_rows){
				echo "Error al registrar: Gusto ya registrado para ese usuario.";
				return false;
			}
			else if(is_null($gusto)){
				echo "Error al registrar gusto: Hay campos obligatorios vacíos.";
				return false;
			}
			else{
				$consulta=$this->conexion->prepare("INSERT INTO gustos (id_usuario, gusto) VALUES (?,?);");
				$consulta->bind_param("is",$usuario->id,$gusto);
				$consulta->execute();
				return ($consulta->affected_rows!=-1);
			}
		}

		//Modifica el usuario '$usuario', buscándolo por su id y cambiando el resto a los valores del objeto
		public function updateUsuario($usuario){
			$consulta=$this->conexion->prepare("SELECT * FROM usuario WHERE id=?;");
			$consulta->bind_param("i",$usuario->id);
			$consulta->execute();
			if($consulta->get_result()->num_rows==0){
				echo "Error al modificar: No tienen permiso para modificar al usuario.";
				return false;
			}
			else if(is_null($usuario->rol) or is_null($usuario->nombre) or is_null($usuario->apellido1) or is_null($usuario->apellido2) or 
					is_null($usuario->DNI) or is_null($usuario->fecha_nacimiento) or is_null($usuario->localidad) or is_null($usuario->email) or 
					is_null($usuario->telefono) or is_null($usuario->password)){
				echo "Error al modificar usuario: Hay campos obligatorios vacíos.";
				return false;
			}
			else if (!($usuario->rol=='administrador' or $usuario->rol=='voluntario' or $usuario->rol=='socio')){
				echo "Error al registrar usuario: El rol del usuario no es válido.";
				return false;				
			}
			else{
				$consulta=$this->conexion->prepare("UPDATE usuario SET rol=?, nombre=?, apellido1=?,
					apellido2=?, DNI=?, fecha_nacimiento=?,
					localidad=?, email=?, telefono=?, aspiraciones=?,
					observaciones=?, password=? WHERE id=?;");
				$consulta->bind_param("ssssssssisssi",$usuario->rol,$usuario->nombre, $usuario->apellido1, $usuario->apellido2, 
					$usuario->DNI,$usuario->fecha_nacimiento,$usuario->localidad,$usuario->email, $usuario->telefono,
					$usuario->aspiraciones, $usuario->observaciones, $usuario->password,$usuario->id);
				
				$consulta->execute();

				$exito=$consulta->affected_rows != -1;
				/*
					Para actualizar los gustos del usuario, lo más fácil es borrar todos
					 y añadir los nuevos.
				*/
				$this->deleteAllGustos($usuario->id);
				$num_gustos = sizeof($usuario->gustos,0);
				for ($i=0;$i<$num_gustos;$i++){
					$exito_gusto=$this->regGusto($usuario,$usuario->gustos[$i]);
					$exito=$exito && $exito_gusto;
				}
				return $exito;
			}
		}

		/*//Modifica la actividad '$actividad', buscándola por su id y cambiando el resto a los valores del objeto
		public function updateActividad($actividad){
			$comprobar="SELECT * FROM actividad WHERE id_actividad=" . $actividad->id_actividad;
			$resultado=mysqli_query($conexion, $comprobar);
			if(mysqli_num_rows($resultado)<=0){
				echo "Error al modificar: La actividad no existe.";
				exit();
			}
			else if(is_null($actividad->nombre) or is_null($actividad->fecha) or is_null($actividad->localizacion) or is_null($actividad->descripcion)
					or is_null($actividad->cerrada) ){
				echo "Error al registrar actividad: hay campos obligatorios vacíos.";
				exit();
			}
			else{
				$cambio="UPDATE actividad SET id_voluntario=$actividad->id_voluntario, id_socio=$actividad->id_socio, nombre=$actividad->nombre,
					fecha=$actividad->fecha, localizacion=$actividad->localizacion, descripcion=$actividad->descripcion, puntuacion=$actividad->puntuacion,
					cerrada=$actividad->cerrada WHERE id=$actividad->id_actividad";
				
				mysqli_query($this->conexion, $cambio);
			}
		}*/

		//Apuntarse a la actividad
		public function apuntarseActividad($actividad){
			$consulta=$this->conexion->prepare("SELECT rol FROM usuario WHERE id=?");
			$consulta->bind_param("i",$_SESSION['id_usuario']);
			$consulta->execute();
			$fila_resultado = $consulta->get_result()->fetch_assoc();
			if ($fila_resultado['rol']=='socio'){
				$actividad->id_socio=$_SESSION['id_usuario'];
				$consulta=$this->conexion->prepare("UPDATE actividad SET id_socio=? WHERE id_actividad=? AND id_socio IS NULL;");
				$consulta->bind_param("ii",$actividad->id_socio,$actividad->id_actividad);
				$consulta->execute();
				return ($consulta->affected_rows==1);
			}
			else{
				$actividad->id_voluntario=$_SESSION['id_usuario'];
				$consulta=$this->conexion->prepare("UPDATE actividad SET id_voluntario=? WHERE id_actividad=? AND id_voluntario IS NULL;");
				$consulta->bind_param("ii",$actividad->id_voluntario,$actividad->id_actividad);
				$consulta->execute();
				return ($consulta->affected_rows==1);
			}
		}

		//ProponerFechaHora
		public function proponerFechaLocalizacion($actividad){
			
		}

		//Borra el usuario "$usuario", buscándolo por su id
		public function deleteUsuario($usuario){
			$this->deleteAllActividades($usuario->id);
			$this->deleteAllGustos($usuario->id);
			$consulta=$this->conexion->prepare("DELETE FROM usuario WHERE id=? ;");
			$consulta->bind_param("i",$usuario->id);
			$consulta->execute();
			return ($consulta->affected_rows==1);
		}

		//Borra la actividad "$actividad", buscándola por su id
		public function deleteActividad($actividad){
			$comprobar="SELECT * FROM actividad WHERE id_actividad=" . $actividad->id_actividad;
			$resultado=mysqli_query($this->conexion, $comprobar);
			if(mysqli_num_rows($resultado)<=0){
				echo "Error al borrar: La actividad no existe.";
				exit();
			}
			else{
				$envio = "DELETE FROM actividad WHERE id_actividad=" . $actividad->id_actividad;
				mysqli_query($this->conexion, $envio);
			}
		}


		//Borrar todos los gustos de un usuario.
		public function deleteAllGustos($id_usuario){
			$consulta=$this->conexion->prepare("DELETE FROM gustos WHERE id_usuario=?;");
			$consulta->bind_param("i",$id_usuario);
			$consulta->execute();		
		}

		//Borra todas las actividades del usuario.
		public function deleteAllActividades($id_usuario){
			$consulta=$this->conexion->prepare("DELETE FROM actividad WHERE id_voluntario=? or id_socio=?;");
			$consulta->bind_param("ii",$id_usuario,$id_usuario);
			$consulta->execute();
		}

		public function getActividad($actividad){
			$consulta=$this->conexion->prepare("SELECT id_actividad,id_voluntario,id_socio,nombre,
			fecha,localizacion,descripcion,puntuacion,cerrada FROM actividad WHERE id_actividad=?;");
			$consulta->bind_param("i",$actividad->id_actividad);
			$consulta->execute();
			$fila=$consulta->get_result()->fetch_assoc();
			$actividad->id_actividad=$fila['id_actividad'];
			$actividad->id_voluntario=$fila['id_voluntario'];
			$actividad->id_socio=$fila['id_socio'];
			$actividad->nombre=$fila['nombre'];
			$actividad->fecha=$fila['fecha'];
			$actividad->localizacion=$fila['localizacion'];
			$actividad->descripcion=$fila['descripcion'];
			$actividad->puntuacion=$fila['puntuacion'];
			$actividad->cerrada=$fila['cerrada'];
			return $actividad;
		}

		/*
		//Borra el gusto "$gusto", buscándolo por la combinación de usuario y gusto
		public function deleteGusto($gusto){
			$comprobar="SELECT * FROM gustos WHERE id_usuario=" . $gusto->id_usuario . " AND gusto=" . $gusto->gusto;
			$resultado=mysqli_query($this->conexion, $comprobar);
			if(mysqli_num_rows($resultado)<=0){
				echo "Error al borrar: No existe la combinación de gusto y usuario.";
				exit();
			}
			else{
				$envio = "DELETE FROM gustos WHERE id_usuario=" . $gusto->id_usuario . " AND gusto=" . $gusto->gusto;
				mysqli_query($this->conexion, $envio);
			}
		}
		*/

	}
?>
