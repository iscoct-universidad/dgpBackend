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
			if(is_null($actividad->nombre) or is_null($actividad->descripcion)){
				echo "Error al registrar actividad: hay campos obligatorios vacíos.";
				return false;
			}
			else{
				$exito=false;
				$consulta=$this->conexion->prepare("SELECT rol FROM usuario WHERE id=?");
				$consulta->bind_param("i",$_SESSION['id_usuario']);
				$consulta->execute();
				$fila_resultado = $consulta->get_result()->fetch_assoc();
				if ($fila_resultado['rol']=='socio'){
					$actividad->id_socio=$_SESSION['id_usuario'];
					$consulta=$this->conexion->prepare("INSERT INTO actividad (nombre, descripcion,id_socio,imagen) VALUES (?,?,?,?);");
					$consulta->bind_param("ssis",$actividad->nombre,$actividad->descripcion,$actividad->id_socio,$actividad->imagen);
				}
				else{
					$actividad->id_voluntario=$_SESSION['id_usuario'];
					$consulta=$this->conexion->prepare("INSERT INTO actividad (nombre, descripcion,id_voluntario,imagen) VALUES (?,?,?,?);");
					$consulta->bind_param("ssis",$actividad->nombre,$actividad->descripcion,$actividad->id_voluntario,$actividad->imagen);
				}
				$consulta->execute();
				echo $consulta->error;
				$exito=$consulta->affected_rows;

				$actividad->id_actividad = $this->getIdActividad($actividad->nombre,$actividad->descripcion);
				$num_etiquetas = sizeof($actividad->etiquetas,0);
				for ($i=0;$i<$num_etiquetas;$i++){
					$exito_etiqueta=$this->regEtiqueta($actividad,$actividad->etiquetas[$i]);
					$exito=($exito && $exito_etiqueta);
				}

				return $exito;		
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
					is_null($usuario->fecha_nacimiento) or is_null($usuario->localidad) or is_null($usuario->email) or 
					is_null($usuario->telefono) or is_null($usuario->password)){
				echo "Error al registrar usuario: Hay campos obligatorios vacíos.";
				return false;
			}
			else if (! $this->comprobarRolAdministrador($_SESSION['id_usuario'])){
				echo "Error al registrar usuario: El usuario no es administrador.";
				return false;
			}
			else if (!($usuario->rol=='administrador' or $usuario->rol=='voluntario' or $usuario->rol=='socio')){
				echo "Error al registrar usuario: El rol del usuario no es válido.";
				return false;				
			}
			else{
				$exito=false;
				$consulta=$this->conexion->prepare("INSERT INTO usuario (rol, nombre, apellido1, apellido2, DNI, fecha_nacimiento, localidad, email, telefono, aspiraciones, observaciones, password, imagen)
				 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?);");
				$consulta->bind_param("ssssssssissss",$usuario->rol,$usuario->nombre, $usuario->apellido1, $usuario->apellido2, 
							$usuario->DNI,$usuario->fecha_nacimiento,$usuario->localidad,$usuario->email, $usuario->telefono,
							$usuario->aspiraciones, $usuario->observaciones, $usuario->password, $usuario->imagen);
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

		public function getIdActividad($nombre,$descripcion){
			$consulta=$this->conexion->prepare("SELECT id_actividad FROM actividad WHERE nombre=? AND descripcion=? ORDER BY id_actividad DESC;");
			$consulta->bind_param("ss",$nombre,$descripcion);
			$consulta->execute();
			$fila_resultado = $consulta->get_result()->fetch_assoc();
			return $fila_resultado['id_actividad'];
		}
		public function comprobarRolAdministrador($id){
			$consulta=$this->conexion->prepare("SELECT rol FROM usuario WHERE id=?");
			$consulta->bind_param("i",$id);
			$consulta->execute();
			$fila_resultado = $consulta->get_result()->fetch_assoc();
			
			if($fila_resultado['rol'] === "administrador") {
				return true;
			} else {
				return false;
			}
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

		public function regEtiqueta($actividad, $etiqueta){

			$consulta=$this->conexion->prepare("SELECT * FROM actividad_etiquetas WHERE id_actividad=? AND etiqueta=?");
			$consulta->bind_param("is",$actividad->id_actividad,$etiqueta);
			$consulta->execute();
			if($consulta->get_result()->num_rows){
				echo "Error al registrar: Etiqueta ya registrada para esa actividad";
				return false;
			}
			else if(is_null($etiqueta)){
				echo "Error al registrar etiqueta: Hay campos obligatorios vacíos.";
				return false;
			}
			else{
				$consulta=$this->conexion->prepare("INSERT INTO actividad_etiquetas (id_actividad, etiqueta) VALUES (?,?);");
				$consulta->bind_param("is",$actividad->id_actividad,$etiqueta);
				$consulta->execute();
				return ($consulta->affected_rows!=-1);
			}
		}

		//Modifica el usuario '$usuario', buscándolo por su id y cambiando el resto a los valores del objeto
		//Si cambia a administrador, comprobar que el usuario de la sesion es administrador.
		public function updateUsuario($usuario){
			$consulta=$this->conexion->prepare("SELECT * FROM usuario WHERE id=?;");
			$consulta->bind_param("i",$usuario->id);
			$consulta->execute();
			if($consulta->get_result()->num_rows==0){
				echo "Error al modificar: No tienen permiso para modificar al usuario.";
				return false;
			}
			else if(is_null($usuario->rol) or is_null($usuario->nombre) or is_null($usuario->apellido1) or is_null($usuario->apellido2) or 
					is_null($usuario->fecha_nacimiento) or is_null($usuario->localidad) or is_null($usuario->email) or 
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
					observaciones=?, password=?, imagen=? WHERE id=?;");
				$consulta->bind_param("ssssssssissssi",$usuario->rol,$usuario->nombre, $usuario->apellido1, $usuario->apellido2, 
					$usuario->DNI,$usuario->fecha_nacimiento,$usuario->localidad,$usuario->email, $usuario->telefono,
					$usuario->aspiraciones, $usuario->observaciones, $usuario->password,$usuario->imagen,$usuario->id);

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
			$consulta=$this->conexion->prepare("SELECT rol FROM usuario WHERE id=?");
			$consulta->bind_param("i",$_SESSION['id_usuario']);
			$consulta->execute();
			$fila_resultado = $consulta->get_result()->fetch_assoc();
			if ($fila_resultado['rol']=='socio'){
				$actividad->id_socio=$_SESSION['id_usuario'];
				$consulta=$this->conexion->prepare("UPDATE actividad SET fecha=?, localizacion=?, id_usuario_propone=? WHERE id_actividad=? AND id_socio=?;");
				$consulta->bind_param("ssiii",$actividad->fecha,$actividad->localizacion,$_SESSION['id_usuario'],$actividad->id_actividad,$actividad->id_socio);
				$consulta->execute();
				return ($consulta->affected_rows==1);
			}
			else{
				$actividad->id_voluntario=$_SESSION['id_usuario'];
				$consulta=$this->conexion->prepare("UPDATE actividad SET fecha=?, localizacion=?,id_usuario_propone=? WHERE id_actividad=? AND id_voluntario=?;");
				$consulta->bind_param("ssiii",$actividad->fecha,$actividad->localizacion,$_SESSION['id_usuario'],$actividad->id_actividad,$actividad->id_voluntario);
				$consulta->execute();
				return ($consulta->affected_rows==1);
			}	
		}
		//Confirmar fecha y localizacion
		public function confirmarFechaLocalizacion($actividad){

			$consultaComprobacion = $this->conexion->prepare("SELECT id_usuario_propone FROM actividad WHERE id_actividad=?;");
			$consultaComprobacion->bind_param("i",$actividad->id_actividad);
			$consultaComprobacion->execute(); 
			$fila_resultadoComprobacion = $consultaComprobacion->get_result()->fetch_assoc();
			$id_usuario_propone = $fila_resultadoComprobacion['id_usuario_propone'];
			if ($id_usuario_propone==$_SESSION['id_usuario']) return false;

			$consulta=$this->conexion->prepare("SELECT rol FROM usuario WHERE id=?");
			$consulta->bind_param("i",$_SESSION['id_usuario']);
			$consulta->execute();
			$fila_resultado = $consulta->get_result()->fetch_assoc();
			if ($fila_resultado['rol']=='socio'){
				$actividad->id_socio=$_SESSION['id_usuario'];
				if ($actividad->cerrada){
					$consulta=$this->conexion->prepare("UPDATE actividad SET cerrada=? WHERE id_actividad=? AND id_socio=?;");
					$consulta->bind_param("iii",$actividad->cerrada,$actividad->id_actividad,$actividad->id_socio);
					$consulta->execute();
				}
				else{
					$consulta=$this->conexion->prepare("UPDATE actividad SET fecha=NULL, localizacion=NULL, id_usuario_propone=NULL WHERE id_actividad=? AND id_socio=?;");
					$consulta->bind_param("ii",$actividad->id_actividad,$actividad->id_socio);
					$consulta->execute();
				}
				return ($consulta->affected_rows==1);
			}
			else{
				$actividad->id_voluntario=$_SESSION['id_usuario'];
				if ($actividad->cerrada){
					$consulta=$this->conexion->prepare("UPDATE actividad SET cerrada=? WHERE id_actividad=? AND id_voluntario=?;");
					$consulta->bind_param("iii",$actividad->cerrada,$actividad->id_actividad,$actividad->id_voluntario);
					$consulta->execute();
				}
				else{
					$consulta=$this->conexion->prepare("UPDATE actividad SET fecha=NULL,localizacion=NULL WHERE id_actividad=? AND id_voluntario=?;");
					$consulta->bind_param("ii",$actividad->id_actividad,$actividad->id_voluntario);
					$consulta->execute();
				}
				return ($consulta->affected_rows==1);
			}	
		}

		public function valorar($actividad){
			$consulta=$this->conexion->prepare("SELECT rol FROM usuario WHERE id=?");
			$consulta->bind_param("i",$_SESSION['id_usuario']);
			$consulta->execute();
			$fila_resultado = $consulta->get_result()->fetch_assoc();
			if ($fila_resultado['rol']=='socio'){
				$actividad->id_socio=$_SESSION['id_usuario'];
				$consulta=$this->conexion->prepare("UPDATE actividad SET puntuacion=? WHERE id_actividad=? AND id_socio=? AND cerrada=1");
				$consulta->bind_param("iii",$actividad->puntuacion,$actividad->id_actividad,$actividad->id_socio);
				$consulta->execute();
				return ($consulta->affected_rows==1);
			}
			else{
				$actividad->id_voluntario=$_SESSION['id_usuario'];
				$consulta=$this->conexion->prepare("UPDATE actividad SET puntuacion=? WHERE id_actividad=? AND id_voluntario=? AND cerrada=1;");
				$consulta->bind_param("iii",$actividad->puntuacion,$actividad->id_actividad,$actividad->id_voluntario);
				$consulta->execute();
				return ($consulta->affected_rows==1);
			}	
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

		/*
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
		*/

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
			fecha,localizacion,descripcion,puntuacion,cerrada,imagen FROM actividad WHERE id_actividad=?;");
			$consulta->bind_param("i",$actividad->id_actividad);
			$consulta->execute();
			$fila=$consulta->get_result()->fetch_assoc();

			$actividad->id_voluntario=$fila['id_voluntario'];
			$actividad->id_socio=$fila['id_socio'];
			$actividad->nombre=$fila['nombre'];
			$actividad->fecha=$fila['fecha'];
			$actividad->localizacion=$fila['localizacion'];
			$actividad->descripcion=$fila['descripcion'];
			$actividad->puntuacion=$fila['puntuacion'];
			$actividad->cerrada=$fila['cerrada'];
			$actividad->imagen=$file['imagen'];

			$etiquetas;
			$consulta2=$this->conexion->prepare("SELECT etiqueta FROM actividad_etiquetas WHERE id_actividad=?;");
			$consulta2->bind_param("i",$actividad->id_actividad);
			$consulta2->execute();
			$resultado2=$consulta2->get_result();
			while($fila_resultado2 = $resultado2->fetch_assoc()){
				$etiquetas[]=$fila_resultado2['etiqueta'];
			}

			$actividad->etiquetas = $etiquetas;
			return $actividad;
		}

		//Falta que tras las actividades donde participa el usuario aparezcan las que coinciden con sus gustos.
		public function getActividades(){
			$actividades=array();
			$consulta=$this->conexion->prepare("SELECT rol FROM usuario WHERE id=?");
			$consulta->bind_param("i",$_SESSION['id_usuario']);
			$consulta->execute();
			$fila_resultado = $consulta->get_result()->fetch_assoc();
			
				if ($fila_resultado['rol']=='socio'){
					$id_socio=$_SESSION['id_usuario'];
					//Las que están en proceso de cierre o cerradas cuya fecha aún no ha llegado	
					$consulta=$this->conexion->prepare("SELECT id_actividad,id_socio,id_voluntario,fecha,localizacion,cerrada,nombre,descripcion,id_usuario_propone,imagen FROM actividad WHERE id_socio=? AND id_voluntario IS NOT NULL AND (cerrada=0 OR (cerrada=1 AND fecha > NOW())) ORDER BY fecha ASC;");
					$consulta->bind_param("i",$id_socio);
					$consulta->execute();
					$resultado=$consulta->get_result();
					while($fila_resultado = $resultado->fetch_assoc()){
						$act=new Actividad;
						$act->constructFromAssociativeArray($fila_resultado);
						$etiquetas;
						$consulta2=$this->conexion->prepare("SELECT etiqueta FROM actividad_etiquetas WHERE id_actividad=?;");
						$consulta2->bind_param("i",$act->id_actividad);
						$consulta2->execute();
						$resultado2=$consulta2->get_result();
						while($fila_resultado2 = $resultado2->fetch_assoc()){
							$etiquetas[]=$fila_resultado2['etiqueta'];
						}
						$actividad->etiquetas = $etiquetas;

						$actividades[]=$act;
					}
					// LAS QUE COINCIDEN CON ALGÚN GUSTO SUYO
					$consulta=$this->conexion->prepare("SELECT DISTINCT actividad.id_actividad,id_socio,id_voluntario,fecha,localizacion,cerrada,nombre,descripcion,id_usuario_propone,imagen FROM actividad,actividad_etiquetas,gustos 
									WHERE actividad.id_socio IS NULL AND actividad.id_actividad=actividad_etiquetas.id_actividad AND gustos.id_usuario=? AND gustos.gusto=actividad_etiquetas.etiqueta;");
					$consulta->bind_param("i",$id_socio);
					$consulta->execute();
					$resultado=$consulta->get_result();
					while($fila_resultado = $resultado->fetch_assoc()){
						$act=new Actividad;
						$act->constructFromAssociativeArray($fila_resultado);
						$etiquetas;
						$consulta2=$this->conexion->prepare("SELECT etiqueta FROM actividad_etiquetas WHERE id_actividad=?;");
						$consulta2->bind_param("i",$act->id_actividad);
						$consulta2->execute();
						$resultado2=$consulta2->get_result();
						while($fila_resultado2 = $resultado2->fetch_assoc()){
							$etiquetas[]=$fila_resultado2['etiqueta'];
						}
						$actividad->etiquetas = $etiquetas;

						$actividades[]=$act;
					}

					//LAS QUE NO COINCIDEN CON NINGÚN GUSTO SUYO.
					$consulta=$this->conexion->prepare("SELECT actividad.id_actividad,id_socio,id_voluntario,fecha,localizacion,cerrada,nombre,descripcion,id_usuario_propone,imagen FROM actividad 
									WHERE id_socio IS NULL AND NOT EXISTS
									(SELECT DISTINCT actividad.id_actividad,id_socio,id_voluntario,fecha,localizacion,cerrada,nombre,descripcion,id_usuario_propone,imagen FROM actividad,actividad_etiquetas,gustos 
									WHERE actividad.id_socio IS NULL AND actividad.id_actividad=actividad_etiquetas.id_actividad AND gustos.id_usuario=? AND gustos.gusto=actividad_etiquetas.etiqueta);");
					$consulta->bind_param("i",$id_socio);
					$consulta->execute();
					$resultado=$consulta->get_result();
					while($fila_resultado = $resultado->fetch_assoc()){
						$act=new Actividad;
						$act->constructFromAssociativeArray($fila_resultado);
						$etiquetas;
						$consulta2=$this->conexion->prepare("SELECT etiqueta FROM actividad_etiquetas WHERE id_actividad=?;");
						$consulta2->bind_param("i",$act->id_actividad);
						$consulta2->execute();
						$resultado2=$consulta2->get_result();
						while($fila_resultado2 = $resultado2->fetch_assoc()){
							$etiquetas[]=$fila_resultado2['etiqueta'];
						}
						$actividad->etiquetas = $etiquetas;

						$actividades[]=$act;
					}
				}
				else{
					$id_voluntario=$_SESSION['id_usuario'];
					$consulta=$this->conexion->prepare("SELECT id_actividad,id_socio,id_voluntario,fecha,localizacion,cerrada,nombre,id_usuario_propone,descripcion FROM actividad WHERE id_voluntario=? AND id_voluntario IS NOT NULL AND (cerrada=0 OR (cerrada=1 AND fecha > NOW())) ORDER BY fecha ASC;");
					$consulta->bind_param("i",$id_voluntario);
					$consulta->execute();
					$resultado=$consulta->get_result();
					while($fila_resultado = $resultado->fetch_assoc()){
						$act=new Actividad;
						$act->constructFromAssociativeArray($fila_resultado);
						$etiquetas;
						$consulta2=$this->conexion->prepare("SELECT etiqueta FROM actividad_etiquetas WHERE id_actividad=?;");
						$consulta2->bind_param("i",$act->id_actividad);
						$consulta2->execute();
						$resultado2=$consulta2->get_result();
						while($fila_resultado2 = $resultado2->fetch_assoc()){
							$etiquetas[]=$fila_resultado2['etiqueta'];
						}
						$actividad->etiquetas = $etiquetas;

						$actividades[]=$act;
					}
					//LAS QUE COINCIDEN CON ALGÚN GUSTO SUYO
					$consulta=$this->conexion->prepare("SELECT actividad.id_actividad,id_socio,id_voluntario,fecha,localizacion,cerrada,nombre,id_usuario_propone,descripcion FROM actividad,actividad_etiquetas,gustos 
					WHERE actividad.id_voluntario IS NULL AND actividad.id_actividad=actividad_etiquetas.id_actividad AND gustos.id_usuario=? AND gustos.gusto=actividad_etiquetas.etiqueta;");
					$consulta->bind_param("i",$id_voluntario);
					$consulta->execute();
					$resultado=$consulta->get_result();
					while($fila_resultado = $resultado->fetch_assoc()){
						$act=new Actividad;
						$act->constructFromAssociativeArray($fila_resultado);
						$etiquetas;
						$consulta2=$this->conexion->prepare("SELECT etiqueta FROM actividad_etiquetas WHERE id_actividad=?;");
						$consulta2->bind_param("i",$act->id_actividad);
						$consulta2->execute();
						$resultado2=$consulta2->get_result();
						while($fila_resultado2 = $resultado2->fetch_assoc()){
							$etiquetas[]=$fila_resultado2['etiqueta'];
						}
						$actividad->etiquetas = $etiquetas;

						$actividades[]=$act;
					}

					//LAS QUE NO COINCIDEN CON ALGÚN GUSTO SUYO
					$consulta=$this->conexion->prepare("SELECT actividad.id_actividad,id_socio,id_voluntario,fecha,localizacion,cerrada,nombre,id_usuario_propone,descripcion FROM actividad 
					WHERE id_voluntario IS NULL AND NOT EXISTS (SELECT actividad.id_actividad,id_socio,id_voluntario,fecha,localizacion,cerrada,nombre,id_usuario_propone,descripcion FROM actividad,actividad_etiquetas,gustos 
					WHERE actividad.id_voluntario IS NULL AND actividad.id_actividad=actividad_etiquetas.id_actividad AND gustos.id_usuario=? AND gustos.gusto=actividad_etiquetas.etiqueta);");
					$consulta->bind_param("i",$id_voluntario);
					$consulta->execute();
					$resultado=$consulta->get_result();
					while($fila_resultado = $resultado->fetch_assoc()){
						$act=new Actividad;
						$act->constructFromAssociativeArray($fila_resultado);
						$etiquetas;
						$consulta2=$this->conexion->prepare("SELECT etiqueta FROM actividad_etiquetas WHERE id_actividad=?;");
						$consulta2->bind_param("i",$act->id_actividad);
						$consulta2->execute();
						$resultado2=$consulta2->get_result();
						while($fila_resultado2 = $resultado2->fetch_assoc()){
							$etiquetas[]=$fila_resultado2['etiqueta'];
						}
						$actividad->etiquetas = $etiquetas;

						$actividades[]=$act;
					}
				}
			return $actividades;
		}

		public function getActividadesTerminadas(){
			$actividades=array();
			$consulta=$this->conexion->prepare("SELECT rol FROM usuario WHERE id=?");
			$consulta->bind_param("i",$_SESSION['id_usuario']);
			$consulta->execute();
			$fila_resultado = $consulta->get_result()->fetch_assoc();
			
				if ($fila_resultado['rol']=='socio'){
					$id_socio=$_SESSION['id_usuario'];
					//Las que están en proceso de cierre o cerradas cuya fecha aún no ha llegado	
					$consulta=$this->conexion->prepare("SELECT id_actividad,id_socio,id_voluntario,fecha,localizacion,cerrada,nombre,descripcion,id_usuario_propone,imagen FROM actividad WHERE id_socio=? AND id_voluntario IS NOT NULL AND cerrada=1 AND fecha < NOW() AND puntuacion IS NULL ORDER BY fecha ASC;");
					$consulta->bind_param("i",$id_socio);
					$consulta->execute();
					$resultado=$consulta->get_result();
					while($fila_resultado = $resultado->fetch_assoc()){
						$act=new Actividad;
						$act->constructFromAssociativeArray($fila_resultado);
						$etiquetas;
						$consulta2=$this->conexion->prepare("SELECT etiqueta FROM actividad_etiquetas WHERE id_actividad=?;");
						$consulta2->bind_param("i",$act->id_actividad);
						$consulta2->execute();
						$resultado2=$consulta2->get_result();
						while($fila_resultado2 = $resultado2->fetch_assoc()){
							$etiquetas[]=$fila_resultado2['etiqueta'];
						}
						$actividad->etiquetas = $etiquetas;

						$actividades[]=$act;
					}
				}
			return $actividades;
		}

		//Falta que devuelva valoración media de cada usuario.
		public function getUsuarios(){
			
			if ($this->comprobarRolAdministrador($_SESSION['id_usuario'])){
				$usuarios=array();
				$consulta=$this->conexion->prepare("SELECT id,nombre,apellido1,apellido2,imagen FROM usuario;");
				$consulta->execute();
				$resultado=$consulta->get_result();
				while($fila_resultado=$resultado->fetch_assoc()){
					$usuario = new Usuario;
					$usuario->id=$fila_resultado['id'];
					$usuario->nombre=$fila_resultado['nombre'];
					$usuario->imagen=$fila_resultado['imagen'];
					$usuario->apellido1=$fila_resultado['apellido1'];
					$usuario->apellido2=$fila_resultado['apellido2'];
					$usuarios[]=$usuario;
				}
				return $usuarios;
			}
			else
				return null;
		}

		public function buscarUsuarios($keywords){
			
			if ($this->comprobarRolAdministrador($_SESSION['id_usuario'])){
				$usuarios=array();
				$keywords = '%'.$keywords.'%';
				$consulta=$this->conexion->prepare("SELECT id,nombre,imagen,apellido1,apellido2 FROM usuario WHERE nombre LIKE ? OR apellido1 LIKE ? OR apellido2 LIKE ?;");
				$consulta->bind_param("sss",$keywords,$keywords,$keywords);
				$consulta->execute();
				$resultado=$consulta->get_result();
				while($fila_resultado=$resultado->fetch_assoc()){
					$usuario = new Usuario;
					$usuario->id=$fila_resultado['id'];
					$usuario->nombre=$fila_resultado['nombre'];
					$usuario->imagen=$fila_resultado['imagen'];
					$usuario->apellido1=$fila_resultado['apellido1'];
					$usuario->apellido2=$fila_resultado['apellido2'];
					$usuarios[]=$usuario;
				}
				return $usuarios;
			}
			else
				return null;
		}

		public function getUsuario($id_usuario){
			$usuario=new Usuario;
			$consulta=$this->conexion->prepare("SELECT id,rol,nombre,apellido1,apellido2,DNI,fecha_nacimiento,localidad,email,telefono,aspiraciones,observaciones,password,imagen 
												FROM usuario WHERE id=?;");
			$consulta->bind_param("i",$id_usuario);
			$consulta->execute();
			$resultado=$consulta->get_result();
			$fila_resultado=$resultado->fetch_assoc();
			$gustos = [];
			$consulta2=$this->conexion->prepare("SELECT gusto FROM gustos WHERE id_usuario=?;");
			$consulta2->bind_param("i",$id_usuario);
			$consulta2->execute();
			$resultado2=$consulta2->get_result();
			while($fila_resultado2 = $resultado2->fetch_assoc()){
				$gustos[]=$fila_resultado2['gusto'];
			}
			$usuario->construct2($fila_resultado['rol'],$fila_resultado['nombre'],$fila_resultado['apellido1'],$fila_resultado['apellido2'],$fila_resultado['DNI'],
								$fila_resultado['fecha_nacimiento'],$fila_resultado['localidad'],$fila_resultado['email'],$fila_resultado['telefono'],$fila_resultado['aspiraciones'],$fila_resultado['observaciones'],$fila_resultado['password'],$fila_resultado['imagen'],$gustos);
			$usuario->id=$id_usuario;
			return $usuario;
		}

		public function getUsuarioNombre($id_usuario){
			$usuario=new Usuario;
			$consulta=$this->conexion->prepare("SELECT nombre,apellido1,apellido2 FROM usuario WHERE id=?;");
			$consulta->bind_param("i",$id_usuario);
			$consulta->execute();
			$resultado=$consulta->get_result();
			$fila_resultado=$resultado->fetch_assoc();
			$usuario->nombre=$fila_resultado['nombre'];
			$usuario->apellido1=$fila_resultado['apellido1'];
			$usuario->apellido2=$fila_resultado['apellido2'];
			return $usuario;
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
