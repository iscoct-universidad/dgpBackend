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
$container['db'] = new gestorBD();

AppFactory::setContainer($container);
$app = AppFactory::create();
$app -> addRoutingMiddleware();

$conexion_bd = new gestorBD();

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

$app -> get('/api/[health]', function (Request $request, Response $response, $args) {
    $response -> getBody() -> write("El servidor está corriendo");

    return $response;
});

$app->get('/api/usuarios', function (Request $request,Response $response, $args) {
    $conexion_bd = $app -> getContainer()['db'];
    $isSuperUser = $conexion_bd -> comprobarRolAdministrador($sessionUser);

    if ($isSuperUser) {
        $usuarios = $conexion_bd -> getUsuarios();

        $response = setResponse($response, array("usuarios" => $usuarios), 200);
    } else {
        $response = setResponse($response, array("description" => "No es administrador"), 400);
    }

    $response = setHeader($request, $response);

    return $response;
});

$app -> get('/api/usuario/{id}', function (Request $request, Response $response, $args) {
    $isIdDefined = !is_null($args['id']);
    $sessionUser = $_SESSION['id_usuario'];
    $conexion_bd = $app -> getContainer()['db'];
	$isSuperuser = $conexion_bd -> comprobarRolAdministrador($sessionUser);

	if ($isIdDefined && $isSuperuser) {
        $usuario = $conexion_bd -> getUsuario($args['id']);
        $code = 200;
	} else if (!$isIdDefined && !is_null($sessionUser)) {
		$usuario = $conexion_bd -> getUsuario($sessionUser);
		$code = 200;
	} else {
        $description = "Ha definido un argumento y no es administrador";
        $code = 400;
    }
	
	$response = setResponse($response, array("usuario" => $usuario), $code);
    $response = setHeader($request, $response);

	return $response;
});

$app -> get('/api/usuarioNombre/{id}', function (Request $request, Response $response, $args) {
    $conexion_bd = $app -> getContainer()['db'];
    $isSuperuser = $conexion_bd -> comprobarRolAdministrador($sessionUser);

    if ($isSuperUser) {
        $usuario=$conexion_bd->getUsuarioNombre($args['id']);
	
        $response = setResponse($response, array("usuario" => $usuario), 200);
    } else {
        $response = setResponse($response, array("description" => "No es administrador"), 400);
    }

    $response = setHeader($request, $response);

	return $response;
});

$app -> post('/api/login', function (Request $request, Response $response, $args) {
    $comparison = hasBodyJson($request);

    if ($comparison) {
        $response = setResponse($response, array('description' =>'El cuerpo no contiene json'), 400);
    } else {
        $user = new Usuario();
    
        $post = $request -> getBody();
        $post = json_decode($post,true);

        $user -> email = $post['email'];
        $user -> password = $post['password'];

        $conexion_bd = $app -> getContainer()['db'];
        $existeUsuarioEnBD = $conexion_bd -> identificarUsuario($user);
    
        if ($existeUsuarioEnBD){
            $response = setResponse($response, array('id_usuario' => $_SESSION['id_usuario'],'description' => 'OK'), 200);
        } else {
            $response = setResponse($response, array('description' => 'No pudo identificarse al usuario'), 400);
        }
    }

    return setHeader($request, $response);
});

$app -> post('/api/usuario/nuevo', function (Request $request, Response $response, $args) {
    $uploadFiles = $request->getUploadedFiles();

	if (count($uploadFiles) > 0) {
		$imageFile = $uploadFiles['imagen'];

		if ($imageFile->getError() === UPLOAD_ERR_OK){
		    $imagePath = moveUploadFile($this->get('upload_directory'),$imageFile);
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
        $gustos_post = json_decode($post['gustos'], true);

		for ($i=0; $i < count($gustos_post['gustos']); $i++) {
		    $new_gustos[$i]=$gustos_post['gustos'][$i];
		}
    }

    $new_user = new Usuario();
    $new_user -> constructFromArguments($post['rol'],$post['nombre'], $post['apellido1'], $post['apellido2'], $post['DNI'], $post['fecha_nacimiento'], $post['localidad'],
                             $post['email'], $post['telefono'], $post['aspiraciones'], $post['observaciones'],$post['password'],$imagePath,$new_gustos);
    $conexion_bd = $app -> getContainer()['db'];
    $exito = $conexion_bd->regUsuario($new_user);

    if ($exito) {
        $response = setResponse($response, array('description'=> 'OK'), 200);
    } else {
        $response = setResponse($response, array('description'=> 'Ya existe el usuario, hay campos obligatorios vacíos o se incluyeron gustos repetidos.'), 400);
    }

    return setHeader($request, $response);
});

$app -> post('/api/usuario/modificar', function (Request $request, Response $response, $args) {
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

        $new_user = new Usuario;
        $new_user -> constructFromArguments($post['rol'],$post['nombre'], $post['apellido1'], $post['apellido2'], $post['DNI'], $post['fecha_nacimiento'], $post['localidad'],
                             $post['email'], $post['telefono'], $post['aspiraciones'], $post['observaciones'],$post['password'],$imagePath,$new_gustos);
        $new_user -> id=$_SESSION['id_usuario'];

        $conexion_bd = $app -> getContainer()['db'];
        $exito = $conexion_bd->updateUsuario($new_user);

        if ($exito) {
            $response = setResponse($response,array('description'=> 'OK'), 200);
        } else {
            $response = setResponse($response,array('description'=> 'No tiene permiso para modificar el usuario, hay campos obligatorios vacíos o se incluyeron gustos repetidos.'), 400);
        }

    return setHeader($request, $response);
});

$app -> delete('/api/usuario/{id}', function (Request $request, Response $response, $args) {
    $usuario = new Usuario;
    $usuario->id = $args['id'];
    $conexion_bd = $app -> getContainer()['db'];
    $exito = $conexion_bd -> deleteUsuario($usuario);

    if ($exito) {
        $response = setResponse($response, array('description'=>'Usuario eliminado correctamente'), 200);
    } else {
        $response = setResponse($response, array('description'=>'El usuario no se puede eliminar porque su id no está registrado'), 400);
    }

    return setHeader($request, $response);
});

$app -> get('/api/actividades', function (Request $request, Response $response, $args) {
    $conexion_bd = $app -> getContainer()['db'];
    $actividades = $conexion_bd->getActividades();

    $response = setResponse($response,array("actividades"=>$actividades), 200);
    $response = setHeader($request, $response);

    return $reponse;
});

$app -> get('/api/actividades/terminadas', function (Request $request, Response $response, $args) {
    $conexion_bd = $app -> getContainer()['db'];
    $actividades=$conexion_bd->getActividadesTerminadas();

    $response = setResponse($response,array("actividades"=>$actividades), 200);
    $response = setHeader($request, $response);

    return $response;
});

$app -> get('/api/actividades/propias', function (Request $request, Response $response, $args) {
    $conexion_bd = $app -> getContainer()['db'];
    $actividades = $conexion_bd->getActividadesPropias();

    $response = setResponse($response,array("actividades"=>$actividades), 200);
    $response = setHeader($request, $response);
    
    return $response;
});

$app -> get('/api/actividades/usuario/{id}', function (Request $request, Response $response, $args) {
    $conexion_bd = $app -> getContainer()['db'];
    $actividades=$conexion_bd->getActividadesUsuario($args['id']);

    $response = setResponse($response,array("actividades"=>$actividades), 200);
    $response = setHeader($request, $response);

    return $response;
});

$app-> get('/api/actividades/{id}', function (Request $request, Response $response, $args) {
    $actividad = new Actividad;
    $actividad->id_actividad=$args['id'];
    $conexion_bd = $app -> getContainer()['db'];
    $actividad=$conexion_bd->getActividad($actividad);
    $response = setResponse($response,array('actividad'=>$actividad), 200);
    $response = setHeader($request, $response);

    return $response;
});

$app -> put('/api/actividades/apuntarse/{id}', function (Request $request, Response $response, $args) {
    $actividad = new Actividad;
    $actividad->id_actividad=$args['id'];
    $conexion_bd = $app -> getContainer()['db'];
    $exito=$conexion_bd->apuntarseActividad($actividad);

    if ($exito)
        $response = setResponse($response, array('description'=>'OK'), 200);
    else
        $response = setResponse($response, array('description'=>'No pudo apuntarse a la actividad'), 400);

    return setHeader($request, $response);
});

$app -> post('/api/actividades', function (Request $request, Response $response, $args) {
    $uploadFiles = $request->getUploadedFiles();

    if (! empty($uploadFiles)) {
		$imageFile = $uploadFiles['imagen'];

		if ($imageFile->getError() === UPLOAD_ERR_OK){
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
        $conexion_bd = $app -> getContainer()['db'];
		$exito=$conexion_bd->regActividad($actividad);
		if ($exito) $response = setResponse($response, array('description'=>'OK'), 200);
		else $response = setResponse($response, array('description'=>'No ha sido posible crear la actividad'), 400);
    return setHeader($request, $response);
});

$app->get('/api/actividades/chat/{id}', function (Request $request, Response $response, $args) {
    $actividad = new Actividad;
    $actividad->id_actividad=$args['id'];
    $conexion_bd = $app -> getContainer()['db'];
    $actividad = $conexion_bd->getChat($actividad);

    $response = setResponse($response,array('chat'=>$actividad->mensajes_chat),200);
    $response = setHeader($request, $response);

    return $response;
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
        $mensajeChat->id_actividad=$args['id'];
        $mensajeChat->contenido=$post['contenido'];

        $conexion_bd = $app -> getContainer()['db'];
        $exito=$conexion_bd->publicarMensaje($mensajeChat);
    
        if ($exito) {
            $response = setResponse($response,array('description' =>'OK'), 200);
        } else {
            $response = setResponse($response,array('description' =>'No se pudo enviar el mensaje'), 400);
        }
    }
    return setHeader($request, $response);
});

$app -> put('/api/actividades/cerrar/{id}', function (Request $request, Response $response, $args) {
    $comparison = hasBodyJson($request);

    if ($comparison) {
        $response = setReponse($response, array('description' =>'El cuerpo no contiene json'), 400);
    } else {
        $put=$request->getBody();
        $put=json_decode($put,true);

        $actividad = new Actividad;
        $actividad->id_actividad=$args['id'];

        if (empty($put['fecha']) || $put['fecha']=='') $put['fecha']=null;
        $actividad->fecha=$put['fecha'];
        if (empty($put['localizacion']) || $put['localizacion']=='') $put['localizacion']=null;
        $actividad->localizacion=$put['localizacion'];
        $conexion_bd = $app -> getContainer()['db'];
        $exito=$conexion_bd->cerrarActividad($actividad);

        if ($exito) {
            $response = setResponse($response, array('description'=>'OK'), 200);
        } else {
            $response = setResponse($response, array('description'=>'No pudo cerrarse la actividad'), 400);
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
        $actividad->id_actividad=$args['id'];
        $puntuacion=$put['puntuacion'];
        $texto_valoracion=$put['texto_valoracion'];
        $conexion_bd = $app -> getContainer()['db'];
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
    $conexion_bd = $app -> getContainer()['db'];
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
