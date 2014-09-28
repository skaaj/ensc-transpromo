<?php

require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/app/utils/misc.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

session_start();

$app = new Silex\Application();

// Silex
$app['debug'] = true;

// Twig
$app->register(new Silex\Provider\SerializerServiceProvider());

$loader = new Twig_Loader_Filesystem(__DIR__.'/web/');
$twig   = new Twig_Environment($loader);

// Instantiations
$app['database'] = new Model\Database();

// Retrieve user informations
if(isset($_SESSION['id_user']))
	$app['user'] = $app['database']->get_user($_SESSION['id_user']);
else
	$app['user'] = null;

// Creating modules

$home     = new Module\Home($app, $twig);
$register = new Module\Register($app, $twig);
$login    = new Module\Login($app, $twig);
$project  = new Module\Project($app, $twig);
$idea     = new Module\Idea($app, $twig);
$profile  = new Module\Profile($app, $twig);

/*
  ___ ___  ___  ___ ___ _    ___ 
 | _ \ _ \/ _ \| __|_ _| |  | __|
 |  _/   / (_) | _| | || |__| _| 
 |_| |_|_\\___/|_| |___|____|___|
                                 
*/

$app->get('/members/', function (Silex\Application $app) use($twig) {
	get_context($array, $app);
	set_active($array, 'members');

	push($array, 'members', $app['database']->get_members());

	return $twig->render('members.html.twig', $array);
});

$app->get('/emailme/', function (Silex\Application $app) use($twig) {
	send_mail("too late", "to apologize", "ben.denom@gmail.com");
	return 'okbro';
});

// 1A
$app->get('/applications', function (Silex\Application $app) use($twig) {
	return 'Applications list page'; // TODO
});

/*
  __  __ ___ ___  ___ 
 |  \/  |_ _/ __|/ __|
 | |\/| || |\__ \ (__ 
 |_|  |_|___|___/\___|
                      
*/

$app->get('/teacher/add/info', function (Silex\Application $app) use($twig) {
	return 'Add a information'; // TODO
});

$app->get('/teacher/add/deadline', function (Silex\Application $app) use($twig) {
	return 'Add a deadline'; // TODO
});

$app->get('/cgu', function (Silex\Application $app) use($twig) {
	return $twig->render('cgu.html.twig');
});

$app->get('/about', function (Silex\Application $app) use($twig) {
	return 'About page'; // TODO
});

$app->get('/contact', function (Silex\Application $app) use($twig) {
	return $twig->render('contact.html.twig');
});

function notif_n_redirect($notif, $path, $app){
	push_notif($notif);
	return redirect($path, $app);
}


function redirect($path, $app)
{
	$sub_request = Request::create($path, 'GET');
	return $app->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
}


$app->run();