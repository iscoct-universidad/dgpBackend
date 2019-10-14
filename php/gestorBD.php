<?php
	include('actividad.php');
	include('usuario.php');
	class gestorBD{
		//Clase para modificar la base de datos
		private $conexion;

		//Fucnión que realiza la conexión con la BD
		public function __construct($host, $user, $pass, $DB){
			$conexion = mysqli_connect ($host, $user, $pass, $DB);
			$conexion->set_charset("utf8");
			if (mysqli_connect_errno()){
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
			$resultado=mysqli_query($conexion, $comprobar);
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
			$comprobar="SELECT * FROM usuario WHERE email=" . $usuario->email;
			$resultado=mysqli_query($conexion, $comprobar);
			if(mysqli_num_rows($resultado)>0){
				echo "Error al registrar: El usuario ya existe.";
				exit();
			}
			else if(is_null($usuario->rol) or is_null($usuario->nombre) or is_null($usuario->apellido1) or is_null($usuario->apellido2) or 
					is_null($usuario->DNI) or is_null($usuario->fecha_nacimiento) or is_null($usuario->localidad) or is_null($usuario->email) or 
					is_null($usuario->telefono) or is_null($usuario->password)){
				echo "Error al registrar usuario: Hay campos obligatorios vacíos.";
				exit();
			}
			else{
				$envio = "INSERT INTO usuarios (rol, nombre, apellido1, apellido2, DNI, fecha_nacimiento, localidad, email, telefono, aspiraciones, observaciones, password)
					VALUES ($usuario->rol, $usuario->nombre, $usuario->apellido1, $usuario->apellido2, $usuario->DNI, $usuario->fecha_nacimiento,
					$usuario->localidad, $usuario->email, $usuario->telefono, $usuario->aspiraciones, $usuario->observaciones, $usuario->password)";
				mysqli_query($this->conexion, $envio);
			}
		}

		//Añade el gusto '$gusto' al usuario '$usuario'
		public function regGusto($usuario, $gusto){
			$comprobar="SELECT * FROM gustos WHERE id_usuario=" . $usuario->id . " AND gusto=" . $gusto;
			$resultado=mysqli_query($conexion, $comprobar);
			if(mysqli_num_rows($resultado)>0){
				echo "Error al registrar: Gusto ya registrado para ese usuario.";
				exit();
			}
			else if(is_null($gusto)){
				echo "Error al registrar gusto: Hay campos obligatorios vacíos.";
				exit();
			}
			else{
				$envio = "INSERT INTO gustos (id_usuario, gusto)
					VALUES ($usuario->id, $gusto)";
				mysqli_query($this->conexion, $envio);
			}
		}

		//Modifica el usuario '$usuario', buscándolo por su id y cambiando el resto a los valores del objeto
		public function updateUsuario($usuario){
			$comprobar="SELECT * FROM usuario WHERE id=" . $usuario->id;
			$resultado=mysqli_query($conexion, $comprobar);
			if(mysqli_num_rows($resultado)<=0){
				echo "Error al modificar: El usuario no existe.";
				exit();
			}
			else if(is_null($usuario->rol) or is_null($usuario->nombre) or is_null($usuario->apellido1) or is_null($usuario->apellido2) or 
					is_null($usuario->DNI) or is_null($usuario->fecha_nacimiento) or is_null($usuario->localidad) or is_null($usuario->email) or 
					is_null($usuario->telefono) or is_null($usuario->password)){
				echo "Error al modificar usuario: Hay campos obligatorios vacíos.";
				exit();
			}
			else{
				$cambio="UPDATE usuario SET rol=$usuario->rol, nombre=$usuario->nombre, apellido1=$usuario->apellido1,
					apellido2=$usuario->apellido2, DNI=$usuario->DNI, fecha_nacimiento=$usuario->fecha_nacimiento,
					localidad=$usuario->localidad, email=$usuario->email, telefono=$usuario->telefono, aspiraciones=$usuario->aspiraciones,
					observaciones=$usuario->observaciones, password=$usuario->password WHERE id=$usuario->id";
				
				mysqli_query($this->conexion, $cambio);
			}
		}

		//Modifica la actividad '$actividad', buscándola por su id y cambiando el resto a los valores del objeto
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
		}

		//Borra el usuario "$usuario", buscándolo por su id
		public function deleteUsuario($usuario){
			$comprobar="SELECT * FROM usuario WHERE id=" . $usuario->id;
			$resultado=mysqli_query($conexion, $comprobar);
			if(mysqli_num_rows($resultado)<=0){
				echo "Error al borrar: El usuario no existe.";
				exit();
			}
			else{
				$envio = "DELETE FROM usuario WHERE id=" . $usuario->id;
				mysqli_query($this->conexion, $envio);
			}
		}

		//Borra la actividad "$actividad", buscándola por su id
		public function deleteActividad($actividad){
			$comprobar="SELECT * FROM actividad WHERE id_actividad=" . $actividad->id_actividad;
			$resultado=mysqli_query($conexion, $comprobar);
			if(mysqli_num_rows($resultado)<=0){
				echo "Error al borrar: La actividad no existe.";
				exit();
			}
			else{
				$envio = "DELETE FROM actividad WHERE id_actividad=" . $actividad->id_actividad;
				mysqli_query($this->conexion, $envio);
			}
		}

		//Borra el gusto "$gusto", buscándolo por la combinación de usuario y gusto
		public function deleteGusto($gusto){
			$comprobar="SELECT * FROM gustos WHERE id_usuario=" . $gusto->id_usuario . " AND gusto=" . $gusto->gusto;
			$resultado=mysqli_query($conexion, $comprobar);
			if(mysqli_num_rows($resultado)<=0){
				echo "Error al borrar: No existe la combinación de gusto y usuario.";
				exit();
			}
			else{
				$envio = "DELETE FROM gustos WHERE id_usuario=" . $gusto->id_usuario . " AND gusto=" . $gusto->gusto;
				mysqli_query($this->conexion, $envio);
			}
		}
	}
?>