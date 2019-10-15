<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();
$app->addRoutingMiddleware();
session_start();

function hasBodyJson(Request $request) {
    $contentType = $request -> getHeaderLine('Content-Type');
    $comparison = strcmp($contentType, 'application/json');

    return $comparison;
}

function setResponse(Response $response, String $description, int $status) {
    $response = $response -> withHeader('Content-type', 'application/json') -> withStatus($status);
    $body = json_encode(array('description' => $description));

    $response -> getBody() -> write($body);

    return $response;
}

$app->get('/[health]', function (Request $request, Response $response, $args) {
    $response -> getBody() -> write("El servidor está corriendo");

    return $response;
});

$app -> get('/usuario', function (Request $request, Response $response, $args) {
    $conexion_bd= new gestorBD();
    $user = new Usuario;
    $user->email = $args['email'];
    $user->password = $args['password'];

    $exito = $conexion_bd->identificarUsuario($user);
    if ($exito){
        $response = setResponse($response, 'OK', 200);
        $_SESSION['id_usuario']=$conexion_bd->getIdUsuario($user->email);
    }
    else
        $response = setResponse($response, 'No pudo identificarse al usuario', 400);
    
    $conexion_bd->close();
    return $response;
});

$app -> post('/usuario', function (Request $request, Response $response, $args) {
    $comparison = hasBodyJson($request);

    if ($comparison) {
        $response = setReponse($response, 'El cuerpo no contiene json', 400);
    } else {
        $conexion_bd= new gestorBD();

        $new_gustos = array();
        for ($i=0;$i<count($args['gustos']);$i++)
            $new_gustos []=$args['gustos'][$i]['gusto'];

        $new_user= new Usuario;
        $new_user->construct2($args['rol'],$args['nombre'], $args['apellido1'], $args['apellido2'], $args['DNI'], $args['fecha_nacimiento'], $args['localidad'],
                             $args['email'], $args['telefono'], $args['aspiraciones'], $args['observaciones'],$args['password'],$new_gustos);
        $exito = $conexion_bd->regUsuario($new_user);
        if ($exito)
            $response = setResponse($response, 'OK', 200);
        else
            $response =setResponse($response, 'Ya existe el usuario o hay campos obligatorios vacíos', 400);
    }
    $conexion_bd->close();
    return $response;
});

$app -> put('/usuario', function (Request $request, Response $response, $args) {
    $comparison = hasBodyJson($request);

    if ($comparison) {
        $response = setReponse($response, 'El cuerpo no contiene json', 400);
    } else {
        $conexion_bd= new gestorBD();

        $new_gustos = array();
        for ($i=0;$i<count($args['gustos']);$i++)
            $new_gustos []=$args['gustos'][$i]['gusto'];

        $new_user= new Usuario;
        $new_user->construct2($args['rol'],$args['nombre'], $args['apellido1'], $args['apellido2'], $args['DNI'], $args['fecha_nacimiento'], $args['localidad'],
                             $args['email'], $args['telefono'], $args['aspiraciones'], $args['observaciones'],$args['password']);
        $new_user->id=$_SESSION['id'];

        $exito = $conexion_bd->updateUsuario($new_user);

        if ($exito)
            $response = setResponse($response, 'OK', 200);
        else
            $response =setResponse($response, 'Ya existe el usuario o hay campos obligatorios vacíos', 400);
    }
    $conexion_bd->close();
    return $response;
});

$app -> delete('/usuario/{id}', function (Request $request, Response $response, $args) {
    $conexion_bd= new gestorBD();
    $usuario = new Usuario;
    $usuario->id=$args['id'];
    $exito = $conexion_bd->deleteUsuario($usuario);
    if ($exito)
        $response = setResponse($response, 'Operación para la eliminación de los datos del usuario', 200);
    else
        $response =setResponse($response, 'El usuario no se puede eliminar porque su id no está registrado', 400);
    $conexion_bd->close();
    return $response;
});

$app -> get('/actividades', function (Request $request, Response $response, $args) {
    $response = setResponse($response, 'Operación donde el usuario obtendrá las actividades
        cerradas por él y que no han sido aceptadas.', 200);

    return $response;
});

$app-> get('/actividades/{id}', function (Request $request, Response $response, $args) {
    $response = setResponse($response, 'Obteniendo los datos relacionados con la actividad: ' . $args['id'], 200);

    return $response;
});

$app -> put('/actividades/apuntarse/{id}', function (Request $request, Response $response, $args) {
    $comparison = hasBodyJson($request);

    if ($comparison) {
        $response = setReponse($response, 'El cuerpo no contiene json', 400);
    } else {
        $response = setResponse($response, 'Apuntándose a la actividad ' . $args['id'], 200);
    }

    return $response;
});

$app -> post('/actividades', function (Request $request, Response $response, $args) {
    $comparison = hasBodyJson($request);

    if ($comparison) {
        $response = setReponse($response, 'El cuerpo no contiene json', 400);
    } else {
        $response = setResponse($response, 'Modificando los datos relacionados de la actividad introducida', 200);
    }
    
    return $response;
});

$app -> put('/actividades/proponerFechaLocalizacion', function (Request $request, Response $response, $args) {
    $comparison = hasBodyJson($request);

    if ($comparison) {
        $response = setReponse($response, 'El cuerpo no contiene json', 400);
    } else {
        $response = setResponse($response, 'Proponiendo fecha y localización para la actividad', 200);
    }

    return $response;
});

$app -> put('/actividades/confirmarFechaLocalizacion', function (Request $request, Response $response, $args) {
    $comparison = hasBodyJson($request);

    if ($comparison) {
        $response = setReponse($response, 'El cuerpo no contiene json', 400);
    } else {
        $response = setResponse($response, 'Confirmando la asistencia de la fecha de localización', 200);
    }

    return $response;
});

$app -> put('/actividades/valorar/', function (Request $request, Response $response, $args) {
    $comparison = hasBodyJson($request);

    if ($comparison) {
        $response = setReponse($response, 'El cuerpo no contiene json', 400);
    } else {
        $response = setResponse($response, 'Valorando actividad', 200);
    }

    return $response;
});

$app -> run();

?>