<?php

require_once __DIR__.'/vendor/autoload.php';

session_start();

$app = new Silex\Application();

// Silex
$app['debug'] = true;

// Twig
$app->register(new Silex\Provider\SerializerServiceProvider());

$loader = new Twig_Loader_Filesystem(__DIR__.'/web/');
$twig   = new Twig_Environment($loader);

// Instantiations
$app['database'] = new Model\Database(
    'localhost',  // host
    'transpromo', // db
    'root',       // user
    ''            // pwd
);

if(isset($_SESSION['id_user'])){
	$app['user'] = new Model\User($app['database']);
	$app['user']->load('id_user', $_SESSION['id_user']);
}

// Routing rules

/* ROOT */

$app->get('/', function () use($app, $twig) {
	// retrieve all data the view will need
	$data = array();
	if(isset($app['user']))
		$data['user'] = $app['user']->all();

	return $twig->render('base.html.twig', $data);
});

/* REGISTER */
$app->get('/register', function () use($twig) {
	return $twig->render('register.html.twig');
});

/* LOGIN */

$app->get('/login', function () use($twig) {
	return $twig->render('login.html.twig');
});

$app->post('/login', function() use($app, $twig) {
	$user = new Model\User($app['database']);
	$user->load('mail', $_POST['email']);

	$local_pwd  = $_POST['pwd'];
	$remote_pwd = $user->get('mdp');

	if($remote_pwd == $local_pwd)
	{
		$_SESSION['id_user'] = $user->get('id_user');
		return $app->redirect(__DIR__.'/');
	}
	else
	{
		echo 'KO';
		return $app->redirect(__DIR__.'/');
	}
});

/* LOGOUT */

$app->get('/logout', function () use($app, $twig) {
	session_destroy();
	return $app->redirect(__DIR__.'/');
});

/* PROJECTS */

$app->get('/projects', function () use($app, $twig) {
	return $twig->render('projects.html.twig');
});

$app->run();