<?php
use DI\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../php/actividad.php';
require_once __DIR__ . '/../../php/valoracion.php';
require_once __DIR__ . '/../../php/gestorBD.php';
require_once __DIR__ . '/../../php/usuario.php';
require_once __DIR__ . '/../../php/mensajeChat.php';

$container = new Container();
$container->set('upload_directory',__DIR__ . '/../images');

AppFactory::setContainer($container);
$app = AppFactory::create();
$app->addRoutingMiddleware();
session_start();

function hasBodyJson(Request $request) {
    $contentType = $request -> getHeaderLine('Content-Type');
    $comparison = strcmp($contentType, 'application/json');
   
    if ($comparison) $comparison = strcmp($contentType,'application/json; charset=utf-8');

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

$app -> get('/api/usuarioNombre/{id}', function (Request $request, Response $response, $args) {
	$conexion_bd = new gestorBD();

    $usuario=$conexion_bd->getUsuarioNombre($args['id']);
	
	$response = setResponse($response, array("usuario" => $usuario), 200);
	
	return setHeader($request, $response);
});

$app -> post('/api/usuario', function (Request $request,Response $response, $args) {
    $comparison = hasBodyJson($request);
    if ($comparison) {
        $response = setResponse($response, array('description' =>'El cuerpo no contiene json'), 400);
    } 
    else {
        $conexion_bd= new gestorBD();
        $user = new Usuario;
        $post = $request->getBody();
        $post=json_decode($post,true);
        $user->email = $post['email'];
        $user->password = $post['password'];
        $usuario = $conexion_bd->identificarUsuario($user);
        if ($usuario!=false){
            $response = setResponse($response, array('id_usuario' => $_SESSION['id_usuario'],'description' => 'OK'), 200);
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
		    $imagePath =moveUploadFile($this->get('upload_directory'),$imageFile);
		}
		else{
		    $imagePath = '';
		}
    } else {
    	$imagePath = '';
    }

    $post=$request->getParsedBody();
    $new_gustos = array();
	if (array_key_exists('gustos', $post)) {
        $gustos_post = json_decode($post['gustos'],true);
		for ($i=0;$i<count($gustos_post['gustos']);$i++) {
		    $new_gustos[]=$gustos_post['gustos'][$i];
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

$app -> post('/api/usuario/modificar', function (Request $request, Response $response, $args) {
        $conexion_bd= new gestorBD();

        $post=$request->getParsedBody();
        $isSuperuser = $conexion_bd -> comprobarRolAdministrador($_SESSION['id_usuario']);
        if ($isSuperuser OR $_SESSION['id_usuario']==$post['id_usuario']){
            $uploadFiles = $request->getUploadedFiles();
            if (count($uploadFiles) > 0) {
                $imageFile = $uploadFiles['imagen'];
        
                if ($imageFile->getError() === UPLOAD_ERR_OK){
                    $imagePath =moveUploadFile($this->get('upload_directory'),$imageFile);
                }
                else{
                    $imagePath = '';
                }
            } else {
                $imagePath = '';
            }


            $new_gustos = array();
            if (array_key_exists('gustos', $post)) {
                $gustos_post = json_decode($post['gustos'],true);
                for ($i=0;$i<count($gustos_post['gustos']);$i++) {
                    $new_gustos[]=$gustos_post['gustos'][$i];
                }
            }

            $new_user= new Usuario;
            $new_user->construct2($post['rol'],$post['nombre'], $post['apellido1'], $post['apellido2'], $post['DNI'], $post['fecha_nacimiento'], $post['localidad'],
                                $post['email'], $post['telefono'], $post['aspiraciones'], $post['observaciones'],$post['password'],$imagePath,$new_gustos);
            $new_user->id=$post['id_usuario'];

            $exito = $conexion_bd->updateUsuario($new_user);

            if ($exito)
                $response = setResponse($response,array('description'=> 'OK'), 200);
            else
                $response =setResponse($response,array('description'=> 'No tiene permiso para modificar el usuario, hay campos obligatorios vacíos o se incluyeron gustos repetidos.'), 400);
        }
        else{
            $response =setResponse($response,array('description'=> 'No tiene permiso para modificar el usuario, hay campos obligatorios vacíos o se incluyeron gustos repetidos.'), 400);
        }
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

$app -> get('/api/actividades', function (Request $request, Response $response, $args) {
    
    $actividades=array();
    $conexion_bd= new gestorBD();
    $actividades=$conexion_bd->getActividades();
    $response = setResponse($response,array("actividades"=>$actividades), 200);
    $conexion_bd->close();
    return setHeader($request, $response);
});

$app -> get('/api/actividades/terminadas', function (Request $request, Response $response, $args) {
    
    $actividades=array();
    $conexion_bd= new gestorBD();
    $actividades=$conexion_bd->getActividadesTerminadas();
    $response = setResponse($response,array("actividades"=>$actividades), 200);
    $conexion_bd->close();
    return setHeader($request, $response);
});

$app -> get('/api/actividades/propias', function (Request $request, Response $response, $args) {
    
    $actividades=array();
    $conexion_bd= new gestorBD();
    $actividades=$conexion_bd->getActividadesPropias();
    $response = setResponse($response,array("actividades"=>$actividades), 200);
    $conexion_bd->close();
    return setHeader($request, $response);
});

$app -> get('/api/actividades/usuario/{id}', function (Request $request, Response $response, $args) {
    $actividades=array();
    $conexion_bd= new gestorBD();
    $actividades=$conexion_bd->getActividadesUsuario($args['id']);
    $response = setResponse($response,array("actividades"=>$actividades), 200);
    $conexion_bd->close();
    return setHeader($request, $response);
});

$app-> get('/api/actividades/{id}', function (Request $request, Response $response, $args) {
    $actividad = new Actividad;
    $conexion_bd= new gestorBD();
    $actividad->id_actividad=$args['id'];
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
        echo 'entra al if';
		$imageFile = $uploadFiles['imagen'];

		if ($imageFile->getError() === UPLOAD_ERR_OK){
            echo 'segundo if';
		    $imagePath = moveUploadFile($this->get('upload_directory'),$imageFile);
		}
		else{
            echo $imageFile->getError();
		    $imagePath=null;
		}
    }
    else{
        $imagePath=null;
    }
		$post=$request->getParsedBody();
		$actividad=new Actividad;
		$conexion_bd= new gestorBD();
		$actividad->nombre=$post['nombre'];
        $actividad->descripcion=$post['descripcion'];
        $actividad->tipo=$post['tipo'];

        if (empty($post['fecha']) || $post['fecha']=='') $post['fecha']=null;
        $actividad->fecha=$post['fecha'];
        if (empty($post['localizacion']) || $post['localizacion']=='') $post['localizacion']=null;
        $actividad->localizacion=$post['localizacion'];
        $actividad->imagen=$imagePath;
        print_r($actividad);
        $new_etiquetas = array();
        if (array_key_exists('etiquetas', $post)) {
            $etiquetas_post = json_decode($post['etiquetas'],true);
            for ($i=0;$i<count($etiquetas_post['etiquetas']);$i++){
                $new_etiquetas[]=$etiquetas_post['etiquetas'][$i];
            }
        }
		$actividad->etiquetas=$new_etiquetas;
		$exito=$conexion_bd->regActividad($actividad);
		if ($exito) $response = setResponse($response, array('description'=>'OK'), 200);
		else $response = setResponse($response, array('description'=>'No ha sido posible crear la actividad'), 400);
		$conexion_bd->close();
    return setHeader($request, $response);
});

$app->get('/api/actividades/chat/{id}', function (Request $request, Response $response, $args) {
    $actividad = new Actividad;
    $conexion_bd= new gestorBD();
    $actividad->id_actividad=$args['id'];
    $actividad = $conexion_bd->getChat($actividad);
    $response = setResponse($response,array('chat'=>$actividad->mensajes_chat),200);
    return setHeader($request, $response);
});

$app->post('/api/actividades/chat/{id}', function (Request $request, Response $response, $args) {
    $comparison = hasBodyJson($request);
    if ($comparison) {
        $response = setResponse($response, array('description' =>'El cuerpo no contiene json'), 400);
    } 
    else {
        $post=$request->getBody();
        $post=json_decode($post,true);
        $mensajeChat=new MensajeChat;
        $conexion_bd= new gestorBD();
        $mensajeChat->id_actividad=$args['id'];
        $mensajeChat->contenido=$post['contenido'];
        $exito=$conexion_bd->publicarMensaje($mensajeChat);
        if ($exito)
            $response = setResponse($response,array('description' =>'OK'), 200);
        else
            $response = setResponse($response,array('description' =>'No se pudo enviar el mensaje'), 400);
    }
    return setHeader($request, $response);
});

/*
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
*/

$app -> put('/api/actividades/cerrar/{id}', function (Request $request, Response $response, $args) {
    $comparison = hasBodyJson($request);

    if ($comparison) {
        $response = setReponse($response, array('description' =>'El cuerpo no contiene json'), 400);
    } else {
        $put=$request->getBody();
        $put=json_decode($put,true);

        $actividad = new Actividad;
        $conexion_bd= new gestorBD();
        $actividad->id_actividad=$args['id'];

        if (empty($put['fecha']) || $put['fecha']=='') $put['fecha']=null;
        $actividad->fecha=$put['fecha'];
        if (empty($put['localizacion']) || $put['localizacion']=='') $put['localizacion']=null;
        $actividad->localizacion=$put['localizacion'];
        $exito=$conexion_bd->cerrarActividad($actividad);
        if ($exito)
            $response = setResponse($response, array('description'=>'OK'), 200);
        else
            $response = setResponse($response, array('description'=>'No pudo cerrarse la actividad'), 400);
        $conexion_bd->close();
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
        $puntuacion=$put['puntuacion'];
        $texto_valoracion=$put['texto_valoracion'];
        $exito = $conexion_bd->valorar($actividad,$puntuacion,$texto_valoracion);
        if ($exito){
            $response = setResponse($response,array( 'description'=>'OK'), 200);
        }
        else{
            $response = setResponse($response,array( 'description'=>'No se pudo valorar la actividad'), 400);
        }
    }

    return setHeader($request, $response);
});

$app -> get('/api/buscarUsuario/{keywords}', function (Request $request, Response $response, $args) {
    $keywords = $args['keywords'];
    $conexion_bd= new gestorBD();
    $listaUsuarios = $conexion_bd->buscarUsuarios($keywords);
    $response = setResponse($response,array('usuarios'=>$listaUsuarios),200);
    return setHeader($request, $response);
});

//puede ser necesario aumentar upload_max_filesize y post_max_size en php.ini
function moveUploadFile($directory, $uploadedFile){
    $extension = pathinfo($uploadedFile->getClientFilename(),PATHINFO_EXTENSION);
    $basename = bin2hex(random_bytes(8));
    $filename = sprintf('%s.%s', $basename, $extension);

    echo 'llega a servidor '.$filename;
    $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

    return $filename;
}

$app -> run();

?>
