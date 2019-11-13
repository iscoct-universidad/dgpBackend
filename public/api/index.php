<?php
use DI\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../php/actividad.php';
require_once __DIR__ . '/../../php/gestorBD.php';
require_once __DIR__ . '/../../php/usuario.php';

$container = new Container();
$container->set('upload_directory',__DIR__ . '/../images');

AppFactory::setContainer($container);
$app = AppFactory::create();
$app->addRoutingMiddleware();
session_start();

function hasBodyJson(Request $request) {
    $contentType = $request -> getHeaderLine('Content-Type');
    $comparison = strcmp($contentType, 'application/json');
   
    return $comparison;
}

function setResponse(Response $response, $array_body, int $status) {
    $response = $response -> withHeader('Content-type', 'application/json') -> withStatus($status);
    $body = json_encode($array_body);

    $response -> getBody() -> write($body);

    return $response;
}

function setHeader(Request $request, Response $response) {
	$origin = $request -> getHeader('Origin');

	return $response -> withHeader('Access-Control-Allow-Origin', $origin)
		-> withHeader('Access-Control-Allow-Methods', 'GET, OPTIONS, PUT, POST, DELETE')
		-> withHeader('Access-Control-Max-Age', '9999999')
		-> withHeader('Access-Control-Allow-Headers', 'access-control-allow-origin, content-type')
		-> withHeader('Access-Control-Allow-Credentials', 'true');
}

$app -> options('/[{path:.*}]', function (Request $request, Response $response, $args) {
	
	return setHeader($request, $response);
});

$app->get('/api/[health]', function (Request $request, Response $response, $args) {
    $response -> getBody() -> write("El servidor está corriendo");

    return setHeader($request, $response);
});

$app->get('/api/usuario',function (Request $request,Response $response, $args) {
    $conexion_bd= new gestorBD();
    $post = $request->getBody();
    $post=json_decode($post,true);
    if (!is_null($post['id_usuario']) && $conexion_bd->comprobarRolAdministrador($_SESSION['id_usuario']))
        $usuario=$conexion_bd->getUsuario($post['id_usuario']);
    else if($_SESSION['id_usuario'] !== NULL)
        $usuario=$conexion_bd->getUsuario($_SESSION['id_usuario']);
    else
    	$usuario = 'No esta seteada la sesión';
    $response = setResponse($response,array("usuario"=>$usuario), 200);

    return setHeader($request, $response);
});

$app->get('/api/usuarios',function (Request $request,Response $response, $args) {
    $usuarios=array();
    $conexion_bd= new gestorBD();
    $usuarios=$conexion_bd->getUsuarios();
    $response = setResponse($response,array("usuarios"=>$usuarios), 200);
    return setHeader($request, $response);
});

$app -> get('/api/usuario/{id}', function (Request $request, Response $response, $args) {
	$conexion_bd = new gestorBD();
	$isIdDefined = ! is_null($args['id']);
	$isSuperuser = $conexion_bd -> comprobarRolAdministrador($_SESSION['id_usuario']);
	$code = 200;

	if ($isIdDefined && $isSuperuser) {
		$usuario = $conexion_bd -> getUsuario($args['id']);
	} else if (! $isSuperuser) {
		$usuario = 'You are not superuser in this system';
		$code = 403;
	} else {
		$usuario = 'Id is not set';
		$code = 400;
	}
	
	$response = setResponse($response, array("usuario" => $usuario), $code);
	
	return setHeader($request, $response);
});

$app -> post('/api/usuario', function (Request $request,Response $response, $args) {
    $comparison = hasBodyJson($request);
    if ($comparison) {
    	var_dump($comparison);
        $response = setResponse($response, array('description' =>'El cuerpo no contiene json'), 400);
    } 
    else {
        $conexion_bd= new gestorBD();
        $user = new Usuario;
        $post = $request->getBody();
        $post=json_decode($post,true);
        $user->email = $post['email'];
        $user->password = $post['password'];
        $exito = $conexion_bd->identificarUsuario($user);
        if ($exito){
            $response = setResponse($response, array('email' => $_SESSION['id_usuario'],'description' => 'OK'), 200);
        }
        else
            $response = setResponse($response, array('description' => 'No pudo identificarse al usuario'), 400);
        
        $conexion_bd->close();
    }

    return setHeader($request, $response);
});

$app -> post('/api/usuario/nuevo', function (Request $request, Response $response, $args) {
    $conexion_bd= new gestorBD();
    $uploadFiles = $request->getUploadedFiles();

	if (count($uploadFiles) > 0) {
		$imageFile = $uploadFiles['imagen'];

		if ($imageFile->getError() === UPLOAD_ERR_OK){
		    $imagePath = moveUploadFile($this->get('upload_directory',$imageFile));
		}
		else{
		    $imagePath = '';
		}
    } else {
    	$imagePath = '';
    }

    $post=$request->getBody();
    $post=json_decode($post,true);
    $new_gustos = array();

	if (array_key_exists('gustos', $post)) {
		for ($i=0;$i<count($post['gustos']);$i++) {
		    $new_gustos[]=$post['gustos'][$i];
		}
    }
    $new_user= new Usuario;
    $new_user->construct2($post['rol'],$post['nombre'], $post['apellido1'], $post['apellido2'], $post['DNI'], $post['fecha_nacimiento'], $post['localidad'],
                             $post['email'], $post['telefono'], $post['aspiraciones'], $post['observaciones'],$post['password'],$imagePath,$new_gustos);
    $exito = $conexion_bd->regUsuario($new_user);
    if ($exito)
        $response = setResponse($response,array('description'=> 'OK'), 200);
    else
        $response =setResponse($response,array('description'=> 'Ya existe el usuario, hay campos obligatorios vacíos o se incluyeron gustos repetidos.'), 400);
    $conexion_bd->close();

    return setHeader($request, $response);
});

$app -> put('/api/usuario', function (Request $request, Response $response, $args) {
        $conexion_bd= new gestorBD();

        $uploadFiles = $request->getUploadedFiles();
        
        if (! empty($uploadFiles)) {
		    $imageFile = $uploadFiles['imagen'];
		
		    if ($imageFile->getError() === UPLOAD_ERR_OK){
		        $imagePath = moveUploadFile($this->get('upload_directory',$imageFile));
		    }
		    else{
		        $imagePath = '';
		    }
        } else {
        	$imagePath = '';
        }

        $put=$request->getBody();
        $put=json_decode($put,true);
        $new_gustos = array();
        for ($i=0;$i<count($put['gustos']);$i++)
            $new_gustos []=$put['gustos'][$i];

        $new_user= new Usuario;
        $new_user->construct2($put['rol'],$put['nombre'], $put['apellido1'], $put['apellido2'], $put['DNI'], $put['fecha_nacimiento'], $put['localidad'],
                             $put['email'], $put['telefono'], $put['aspiraciones'], $put['observaciones'],$put['password'],$imagePath,$new_gustos);
        $new_user->id=$_SESSION['id_usuario'];

        $exito = $conexion_bd->updateUsuario($new_user);

        if ($exito)
            $response = setResponse($response,array('description'=> 'OK'), 200);
        else
            $response =setResponse($response,array('description'=> 'No tiene permiso para modificar el usuario, hay campos obligatorios vacíos o se incluyeron gustos repetidos.'), 400);
        $conexion_bd->close();

    return setHeader($request, $response);
});

$app -> delete('/api/usuario/{id}', function (Request $request, Response $response, $args) {
    $conexion_bd= new gestorBD();
    $usuario = new Usuario;
    $usuario->id=$args['id'];
    $exito = $conexion_bd->deleteUsuario($usuario);
    if ($exito)
        $response = setResponse($response, array('description'=>'Usuario eliminado correctamente'), 200);
    else
        $response =setResponse($response, array('description'=>'El usuario no se puede eliminar porque su id no está registrado'), 400);
    $conexion_bd->close();

    return setHeader($request, $response);
});

//Falta devolver primero las que coinciden con los gustos del usuario.
$app -> get('/api/actividades', function (Request $request, Response $response, $args) {
    
    $actividades=array();
    $conexion_bd= new gestorBD();
    $actividades=$conexion_bd->getActividades();
    $response = setResponse($response,array("actividades"=>$actividades), 200);

    return setHeader($request, $response);
});

$app-> get('/api/actividades/{id}', function (Request $request, Response $response, $args) {
    $actividad = new Actividad;
    $conexion_bd= new gestorBD();
    $actividad->id_actividad=$args[id];
    $actividad=$conexion_bd->getActividad($actividad);
    $response = setResponse($response,array('actividad'=>$actividad), 200);
    $conexion_bd->close();
    return setHeader($request, $response);
});

$app -> put('/api/actividades/apuntarse/{id}', function (Request $request, Response $response, $args) {
    $actividad = new Actividad;
    $conexion_bd= new gestorBD();
    $actividad->id_actividad=$args['id'];
    $exito=$conexion_bd->apuntarseActividad($actividad);
    if ($exito)
        $response = setResponse($response, array('description'=>'OK'), 200);
    else
        $response = setResponse($response, array('description'=>'No pudo apuntarse a la actividad'), 400);
    $conexion_bd->close();

    return setHeader($request, $response);
});

$app -> post('/api/actividades', function (Request $request, Response $response, $args) {
    $uploadFiles = $request->getUploadedFiles();

    if (! empty($uploadFiles)) {
		$imageFile = $uploadFiles['imagen'];

		if ($imageFile->getError() === UPLOAD_ERR_OK){
		    $imagePath = moveUploadFile($this->get('upload_directory',$imageFile));
		}
		else{
		    $imagePath=null;
		}

		$post=$request->getBody();
		$post=json_decode($post,true);
		$actividad=new Actividad;
		$conexion_bd= new gestorBD();
		$actividad->nombre=$post['nombre'];
		$actividad->descripcion=$post['descripcion'];
		$actividad->imagen=$imagePath;
		$new_etiquetas = array();
		for ($i=0;$i<count($post['etiquetas']);$i++){
		    $new_etiquetas[]=$post['etiquetas'][$i];
		}
		$actividad->etiquetas=$new_etiquetas;
		$exito=$conexion_bd->regActividad($actividad);
		if ($exito) $response = setResponse($response, array('description'=>'OK'), 200);
		else $response = setResponse($response, array('description'=>'No ha sido posible crear la actividad'), 400);
		$conexion_bd->close();
    } else {
    	$response = setResponse($response, array('description' => 'No ha sido posible crear la actividad'), 400);
    }

    return setHeader($request, $response);
});

$app -> put('/api/actividades/proponerFechaLocalizacion/{id}', function (Request $request, Response $response, $args) {
    $comparison = hasBodyJson($request);

    if ($comparison) {
        $response = setReponse($response, array('description' =>'El cuerpo no contiene json'), 400);
    } else {
        $put=$request->getBody();
        $put=json_decode($put,true);
        $actividad=new Actividad;
        $conexion_bd= new gestorBD();
        $actividad->id_actividad=$args['id'];
        $actividad->fecha=$put['fecha'];
        $actividad->localizacion=$put['localizacion'];
        $exito=$conexion_bd->proponerFechaLocalizacion($actividad);
        if ($exito)
            $response = setResponse($response,array('description' =>'OK'), 200);
        else
            $response = setResponse($response,array('description' =>'No se pudo proponer esa fecha y hora'), 400);
    }

    return setHeader($request, $response);
});

$app -> put('/api/actividades/confirmarFechaLocalizacion/{id}', function (Request $request, Response $response, $args) {
    $comparison = hasBodyJson($request);

    if ($comparison) {
        $response = setReponse($response, array('description' =>'El cuerpo no contiene json'), 400);
    } else {
        $put=$request->getBody();
        $put=json_decode($put,true);
        $actividad=new Actividad;
        $conexion_bd= new gestorBD();
        $actividad->id_actividad=$args['id'];
        $actividad->cerrada=$put['cerrada'];
        $exito = $conexion_bd->confirmarFechaLocalizacion($actividad);
        if ($exito){
            $response = setResponse($response,array( 'description'=>'OK'), 200);
        }
        else{
            $response = setResponse($response,array( 'description'=>'No se pudo confirmar o rechazar la fecha y localizacion'), 400);
        }
    }

    return setHeader($request, $response);
});

$app -> put('/api/actividades/valorar/{id}', function (Request $request, Response $response, $args) {
    $comparison = hasBodyJson($request);

    if ($comparison) {
        $response = setReponse($response, array('description' =>'El cuerpo no contiene json'), 400);
    } else {
        $put=$request->getBody();
        $put=json_decode($put,true);
        $actividad=new Actividad;
        $conexion_bd= new gestorBD();
        $actividad->id_actividad=$args['id'];
        $actividad->puntuacion=$put['puntuacion'];
        $exito = $conexion_bd->valorar($actividad);
        if ($exito){
            $response = setResponse($response,array( 'description'=>'OK'), 200);
        }
        else{
            $response = setResponse($response,array( 'description'=>'No se pudo valorar la actividad'), 400);
        }
    }

    return setHeader($request, $response);
});

function moveUploadFile($directory, UploadedFileInterface $uploadedFile){
    $extendion = pathinfo($uploadedFile->getClientFilename(),PATHINFO_EXTENSION);
    $basename = bin2hex(random_bytes(8));
    $filename = sprintf('%s.%0,8s', $basename, $extension);

    $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

    return $filename;
}

$app -> run();

?>
