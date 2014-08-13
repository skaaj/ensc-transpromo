<?php

require_once __DIR__.'/vendor/autoload.php';

$app = new Silex\Application();

// Silex
$app['debug'] = true;

// Twig
$app->register(new Silex\Provider\SerializerServiceProvider());

$loader = new Twig_Loader_Filesystem(__DIR__.'/web/');
$twig   = new Twig_Environment($loader);

// Instantiations
// * fixme! * where should I keep the db ?
$app['database'] = new Model\Database(
    'localhost',  // host
    'transpromo', // db
    'root',       // user
    ''            // pwd
);

// Routing rules
$app->get('/', function () use($app, $twig) {
	$obj = new Model\Object($app['database']);
	$data = $obj->list_table();

	if(empty($data))
		return 'sorry da db is empty dawg';
	else
		return $twig->render('base.html.twig', array(
			"table" => $data
		));
});

$app->run();