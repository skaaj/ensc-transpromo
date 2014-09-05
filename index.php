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
    ''            // pwd
);

// Retrieve user informations
if(isset($_SESSION['id_user']))
	$app['user'] = $app['database']->get_user($_SESSION['id_user']);
else
	$app['user'] = null;

// Routing rules

/* ROOT */

$app->get('/', function(Silex\Application $app) use($twig) {
	// retrieve all data the view will need
	push($array, 'deadlines',    $app['database']->get_deadlines());
	push($array, 'informations', $app['database']->get_informations());
	
	set_active($array, 'home');
	
	check_notif($array);
	check_user($array, $app);

	return $twig->render('index.html.twig', $array);
});

/* REGISTER */

// TODO
$app->get('/register', function () use($twig) {
	check_notif($array);

	return $twig->render('register.html.twig', $array);
});

$app->post('/register', function(Silex\Application $app) use($twig) {

	$result = $app['database']->insert_user(
		$_POST['prenom'],
		$_POST['nom'],
		$_POST['mail'],
		$_POST['pwd'],
		$_POST['year'],
		$_POST['school'],
		$_POST['skill'],
		$_POST['public']
		);

	var_dump($result);

	if($result === 'mail'){
		push_notif(new_notification(
			'Échec de l\'inscription !',
			'L\'adresse email indiquée existe déjà.',
			'danger'
		));

    	$sub_request = Request::create('/register', 'GET');
    	return $app->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
	}elseif($result === false){
		push_notif(new_notification(
			'Échec de l\'inscription !',
			'Une erreur est survenue lors de l\'inscription. Veuillez recommencer.',
			'danger'
		));

    	$sub_request = Request::create('/register', 'GET');
    	return $app->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
	}else{
		push_notif(new_notification(
			'Inscription réussie !',
			'Vous pouvez maintenant vous connecter avec vos identifiants.',
			'success'
		));

    	$sub_request = Request::create('/login', 'GET');
    	return $app->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
	}
});

/* LOGIN */

$app->get('/login', function () use($twig) {
	check_notif($array);

	return $twig->render('login.html.twig', $array);
});

// TODO (0.9)
//   -> error precision
$app->post('/login', function(Silex\Application $app) use($twig) {
	$user = $app['database']->get_user_mail($_POST['mail']);

	$local_pwd  = $_POST['pwd'];
	$remote_pwd = $user['mdp'];

	if($remote_pwd == $local_pwd)
	{
		$_SESSION['id_user'] = $user['id_user'];

		push_notif(new_notification(
			'Connexion réussi !',
			'Vous pouvez maintenant utiliser le site.',
			'success'
		));

    	$sub_request = Request::create('/', 'GET');
    	return $app->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
	}
	else
	{
		push_notif(new_notification(
			'Erreur !',
			'Le compte n\'existe pas ou le mot de passe est incorrect.',
			'danger'
		));

    	$sub_request = Request::create('/login', 'GET');
    	return $app->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
	}
});

/* LOGOUT */

$app->get('/logout', function(Silex\Application $app) use($twig) {
	unset($_SESSION['id_user']);

	push_notif(new_notification(
		'Succés !',
		'Vous avez bien été déconnecté.',
		'success'
	));

    $sub_request = Request::create('/', 'GET');
    return $app->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
});

/* PROJECTS */

// list - design
$app->get('/project/', function(Silex\Application $app) use($twig) {
	$projects = $app['database']->get_projects();

	return $twig->render('projects.html.twig', array('projects' => $projects, 'active' => 'project'));
});

// details - design
$app->get('/project/{id}', function (Silex\Application $app, $id) use($twig) {
	$project = $app['database']->get_project($id);
	var_dump($project);

	return 'Project details page';
});

// add (2A) - design 
$app->get('/project/add', function(Silex\Application $app) use($twig) {
	return 'Add project';
});

// apply (1A) - design
$app->get('/project/apply/{id}', function (Silex\Application $app, $id) use($twig) {
	return 'Do you really want to apply to '.$id.' project ?';
});

/* IDEAS */

// list - design
$app->get('/idea/', function(Silex\Application $app) use($twig) {
	$ideas = $app['database']->get_ideas();
	return $twig->render('ideas.html.twig', array('ideas' => $ideas, 'active' => 'idea'));
});

// details - design
$app->get('/idea/{id}', function (Silex\Application $app, $id) use($twig) {
	$idea = $app['database']->get_idea($id);
	
	var_dump($idea);
	
	if(empty($idea))
		return 'id '.$id.' can\'t be found';
	else
		return $twig->render('idea.html.twig', array('idea' => $idea));
});

$app->get('/idea/add/', function (Silex\Application $app) use($twig) {
	return 'Add idea';
});

/* PROFILE */

$app->get('/members', function (Silex\Application $app) use($twig) {
	$members = $app['database']->get_members();
	return $twig->render('members.html.twig', array('members' => $members));
});

// 2A
$app->get('/candidates', function (Silex\Application $app) use($twig) {
	return 'Candidates list page'; // TODO
});

// 1A
$app->get('/applications', function (Silex\Application $app) use($twig) {
	return 'Applications list page'; // TODO
});

/* MISC */

$app->get('/teacher/add/info', function (Silex\Application $app) use($twig) {
	return 'Add a information'; // TODO
});

$app->get('/teacher/add/deadline', function (Silex\Application $app) use($twig) {
	return 'Add a deadline'; // TODO
});

$app->get('/cgu', function (Silex\Application $app) use($twig) {
	return 'CGU page'; // TODO
});

$app->get('/about', function (Silex\Application $app) use($twig) {
	return 'About page'; // TODO
});

$app->get('/report', function (Silex\Application $app) use($twig) {
	return 'Bug report page'; // TODO
});

$app->run();