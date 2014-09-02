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

	// TODO
	//   informations
	//   deadline


	return $twig->render('base.html.twig', $data);
});

/* REGISTER */

// todo
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

// list - design
$app->get('/project/', function () use($app, $twig) {
	$sql = 'SELECT id_proj, titre FROM projet';
	$query = $app['database']->prepare($sql);
	$query->execute();

	$projects = $app['database']->fetch_all($query);

	return $twig->render('projects.html.twig', array('projects' => $projects));
});

// details - design
$app->get('/project/{id}', function (Silex\Application $app, $id) use($twig) {
	return 'Project details page'; // TODO
});

// add - todo - 2A
$app->get('/project/add', function () use($app, $twig) {
	return 'Add project';
});

// apply - 1A
$app->get('/project/apply/{id}', function (Silex\Application $app, $id) use($app, $twig) {
	return 'Do you really want to apply to '.$id.' project ?'; // TODO
});

/* IDEAS */

// list - design
$app->get('/idea/', function () use($app, $twig) {
	$sql = 'SELECT * FROM idee';
	$query = $app['database']->prepare($sql);
	$query->execute();

	$ideas = $app['database']->fetch_all($query);

	return $twig->render('ideas.html.twig', array('ideas' => $ideas));
});

// details - design
$app->get('/idea/{id}', function (Silex\Application $app, $id) use($twig) {
	$sql = 'SELECT * FROM idee WHERE id_idee = ?';
	$query = $app['database']->prepare($sql);
	$query->execute(array($id));

	$idea = $app['database']->fetch_one($query);

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
	return 'Members list page'; // TODO
});

// 2A
$app->get('/candidates', function (Silex\Application $app) use($twig) {
	return 'Candidates list page'; // TODO
});

// 1A
$app->get('/applies', function (Silex\Application $app) use($twig) {
	return 'Applies list page'; // TODO
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