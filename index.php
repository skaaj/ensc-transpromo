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
$app['database'] = new Model\Database(
    'localhost',  // host
    'transpromo', // db
    'root',       // user
    'rockmyroot'  // pwd
);

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
		$mail = new \PHPMailer(); // create a new object
			$mail->IsSMTP(); // enable SMTP
			$mail->CharSet="UTF-8";
			$mail->SMTPDebug = 2; // debugging: 1 = errors and messages, 2 = messages only
			$mail->SMTPAuth = true; // authentication enabled
			$mail->SMTPSecure = 'tls'; // secure transfer enabled REQUIRED for GMail
			$mail->Host = "smtp.gmail.com";
			$mail->Port = 587; // or 587
			$mail->IsHTML(true);
			$mail->Username = "assistance.askit@gmail.com";
			$mail->Password = "askit2014";
			$mail->SetFrom("noreply@transpromo","benjo le pro");
			$mail->Subject = "serveur qui marche";
			$mail->Body = "ok super important";
			$mail->AddAddress("ben.denom@gmail.com");

			if(!$mail->Send())
			{
			  echo "Mailer Error: " . $mail->ErrorInfo;
			}
			else
			{
			  echo "Message sent!";
			}
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