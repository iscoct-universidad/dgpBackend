<?php
    require_once __DIR__ . '/../vendor/autoload.php';
	$twig_loader = new Twig_Loader_Filesystem(__DIR__ . '/../html/');
    $twig = new Twig_Environment($twig_loader);
    session_start();

    echo $twig->render('prueba.html',['name' => 'andres']);
    if(){}
    else if(){}
    else if(){}
    else{}
?>