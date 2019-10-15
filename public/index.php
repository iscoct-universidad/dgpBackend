<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require_once '../Twig/Autoloader.php';
Twig_Autoloader::register();
$loader = new Twig_Loader_Filesystem('../html');
$twig = new Twig_Environment($loader);

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();
$app->addRoutingMiddleware();

echo $twig->render('plantillaPadre.html'); // vale funciona



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
    $response = setResponse($response, 'Operación para tomar los datos del usuario', 200);

    return $response;
});

$app -> post('/usuario', function (Request $request, Response $response, $args) {
    $comparison = hasBodyJson($request);

    if ($comparison) {
        $response = setReponse($response, 'El cuerpo no contiene json', 400);
    } else {
        $response = setResponse($response, 'Operación para la modificación de los datos del usuario', 200);
    }

    return $response;
});

$app -> put('/usuario', function (Request $request, Response $response, $args) {
    $comparison = hasBodyJson($request);

    if ($comparison) {
        $response = setReponse($response, 'El cuerpo no contiene json', 400);
    } else {
        $response = setResponse($response, 'Operación para la creación o reemplazo de los datos del usuario', 200);
    }

    return $response;
});

$app -> delete('/usuario/{id}', function (Request $request, Response $response, $args) {
    $response = setResponse($response, 'Operación para la eliminación de los datos del usuario', 200);

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