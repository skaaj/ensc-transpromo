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
	
	get_context($array, $app);

	return $twig->render('index.html.twig', $array);
});

/*
  ___ ___ ___ ___ ___ _____ ___ ___ 
 | _ \ __/ __|_ _/ __|_   _| __| _ \
 |   / _| (_ || |\__ \ | | | _||   /
 |_|_\___\___|___|___/ |_| |___|_|_\
                                    
*/

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

/*
  _    ___   ___ ___ _  _ 
 | |  / _ \ / __|_ _| \| |
 | |_| (_) | (_ || || .` |
 |____\___/ \___|___|_|\_|
                          
*/

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

/*
  ___ ___  ___     _ ___ ___ _____ ___ 
 | _ \ _ \/ _ \ _ | | __/ __|_   _/ __|
 |  _/   / (_) | || | _| (__  | | \__ \
 |_| |_|_\\___/ \__/|___\___| |_| |___/
                                       
*/

// GET LIST
$app->get('/project/', function(Silex\Application $app) use($twig) {
	get_context($array, $app);

	push($array, 'projects', $app['database']->get_projects());
	set_active($array, 'project');

	return $twig->render('projects.html.twig', $array);
});

// GET ADD
$app->get('/project/add', function(Silex\Application $app) use($twig) {
	get_context($array, $app);
	set_active($array, 'project');

	if($app['user']['qualite'] < 2){
		push_notif(new_notification(
			'Action refusée !',
			'Vous devez être en deuxième année pour créer un projet.',
			'warning'
		));

	    $sub_request = Request::create('/project/', 'GET');
	    return $app->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
	}
	
	return $twig->render('project_add.html.twig', $array);
});

// POST ADD
$app->post('/project/add', function(Silex\Application $app) use($twig) {
	get_context($array, $app);
	set_active($array, 'project');

	if($app['user']['qualite'] < 2){
		push_notif(new_notification(
			'Action refusée !',
			'Vous devez être en deuxième année pour créer un projet.',
			'warning'
		));

	    $sub_request = Request::create('/project/', 'GET');
	    return $app->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
	}

	$result = $app['database']->insert_project($_POST['title'], $_POST['descr'], $_POST['seek'], $app['user']['id_user']);
	
	if($result){
		push_notif(new_notification(
			'Projet ajouté !',
			'Vous pouvez maintenant former votre équipe.',
			'success'
		));
	}else{
		push_notif(new_notification(
			'Impossible d\'ajouter le projet',
			'Une erreur est survenue lors de l\'ajout du projet. Attention, vous ne pouvez avoir qu\'un seul projet à la fois.',
			'danger'
		));
	}

	$sub_request = Request::create('/project/', 'GET');
	return $app->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
});

// GET EDIT
$app->get('/project/edit/{id}', function(Silex\Application $app, $id) use($twig) {
	get_context($array, $app);
	set_active($array, 'project');

	push($array, 'project', $app['database']->get_project($id));

	if($array['project']['id_user_cre'] != $app['user']['id_user']){
		push_notif(new_notification(
			'Action refusée !',
			'Vous devez être propriétaire du projet que vous souhaitez éditer.',
			'warning'
		));

	    $sub_request = Request::create('/project/'.$id, 'GET');
	    return $app->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
	}

	return $twig->render('project_edit.html.twig', $array);
});

// POST EDIT
$app->post('/project/edit/{id}', function(Silex\Application $app, $id) use($twig) {
	get_context($array, $app);
	set_active($array, 'project');

	push($array, 'project', $app['database']->get_project($id));

	if($array['project']['id_user_cre'] != $app['user']['id_user']){
		push_notif(new_notification(
			'Action refusée !',
			'Vous devez être propriétaire du projet que vous souhaitez éditer.',
			'warning'
		));

	    $sub_request = Request::create('/project/'.$id, 'GET');
	    return $app->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
	}

	$app['database']->edit_project($_POST['title'], $_POST['descr'], $_POST['seek'], $id);

	push_notif(new_notification(
		'Projet édité !',
		'N\'éditez pas trop souvent votre projet pour ne pas perdre les autres utilisateurs.',
		'success'
	));

	$sub_request = Request::create('/project/', 'GET');
	return $app->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
});

$app->get('/project/delete/{id}', function(Silex\Application $app, $id) use($twig) {
	get_context($array, $app);
	set_active($array, 'project');

	push($array, 'project', $app['database']->get_project($id));

	if($array['project']['id_user_cre'] != $app['user']['id_user']){
		push_notif(new_notification(
			'Action refusée !',
			'Vous devez être propriétaire du projet que vous souhaitez supprimer.',
			'warning'
		));

	    $sub_request = Request::create('/project/'.$id, 'GET');
	    return $app->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
	}

	$app['database']->delete_project($id);

	push_notif(new_notification(
		'Projet supprimé !',
		'Votre projet a été supprimé et ne peut pas être récupéré. Cependant cette fonctionnalité pourrait faire l\'objet d\'une mise à jour.',
		'success'
	));

	$sub_request = Request::create('/', 'GET');
	return $app->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
});

$app->get('/project/accept/{id}/{cand}', function(Silex\Application $app, $id, $cand) use($twig) {
	get_context($array, $app);
	set_active($array, 'project');

	push($array, 'project', $app['database']->get_project($id));

	if($array['project']['id_user_cre'] != $app['user']['id_user']){
		push_notif(new_notification(
			'Action refusée !',
			'Vous devez être propriétaire du projet pour accepter une candidature.',
			'warning'
		));

	    $sub_request = Request::create('/project/'.$id, 'GET');
	    return $app->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
	}

	if($app['database']->accept_application($cand)){
		push_notif(new_notification(
			'Candidature acceptée !',
			'Vous venez d\'ajouter un membre à votre équipe.',
			'success'
		));
	}else{
		push_notif(new_notification(
			'Candidature refusée !',
			'Impossible d\'ajouter ce membre. Peut-être est il déjà dans une autre équipe ?',
			'danger'
		));
	}

	$sub_request = Request::create('/', 'GET');
	return $app->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
});

// GET PROJECT DETAILS
$app->get('/project/{id}', function (Silex\Application $app, $id) use($twig) {
	get_context($array, $app);
	set_active($array, 'project');
	
	if(!empty($app['user'])){
		push($array, 'owner', $app['database']->get_owned_project($app['user']['id_user']));
		push($array, 'has_application', $app['database']->has_application($app['user']['id_user'], $id));
		push($array, 'applications', $app['database']->get_applications($id));
	}

	push($array, 'project', $app['database']->get_project($id));
	push($array, 'count', $app['database']->get_places($id));

	return $twig->render('project.html.twig', $array);
});

// GET APPLY
$app->post('/project/apply/{id}', function (Silex\Application $app, $id) use($twig) {
	get_context($array, $app);

	$ids = $app['database']->has_already_project($app['user']['id_user']);

	if(!empty($ids))
	{
		return notif_n_redirect(new_notification('Candidature refusée !', 'Vous êtes déjà dans un projet.', 'danger'), '/', $app);
	}else{
		if($app['database']->add_application($_POST['motiv'], $app['user']['id_user'], $id))
		{
			push_notif(new_notification(
				'Candidature ajoutée !',
				'Vous êtes maintenant candidat au projet.',
				'success'
			));
		}
		else
		{
			push_notif(new_notification(
				'Candidature refusée !',
				'Une erreur est survenue. Contactez un administrateur.',
				'danger'
			));
		}
	}

	return redirect('/', $app);
});

/*
  ___ ___  ___   _   ___ 
 |_ _|   \| __| /_\ / __|
  | || |) | _| / _ \\__ \
 |___|___/|___/_/ \_\___/
                         
*/

// list - design
$app->get('/idea/', function(Silex\Application $app) use($twig) {
	get_context($array, $app);

	push($array, 'ideas', $app['database']->get_ideas());
	set_active($array, 'idea');

	return $twig->render('ideas.html.twig', $array);
});

$app->get('/idea/add', function(Silex\Application $app) use($twig) {
	get_context($array, $app);
	set_active($array, 'idea');

	return $twig->render('idea_add.html.twig', $array);
});

$app->post('/idea/add', function (Silex\Application $app) use($twig) {
	get_context($array, $app);
	set_active($array, 'idea');

	$result = $app['database']->insert_idea($_POST['title'], $_POST['desc'], $app['user']['id_user']);
	
	if($result){
		push_notif(new_notification(
			'Idée ajoutée !',
			'Merci d\'avoir contribué.',
			'success'
		));
	}else{
		push_notif(new_notification(
			'Impossible d\'ajouter l\'idée',
			'Une erreur est survenue lors de l\'ajout de l\'idée.',
			'danger'
		));
	}

	return redirect('/idea/', $app);
});

$app->get('/idea/adopt/{id}', function (Silex\Application $app, $id) use($twig) {
	get_context($array, $app);
	set_active($array, 'idea');

	$can_adopt = $app['database']->has_already_project($app['user']['id_user']);
	
	if($can_adopt){
		$app['database']->transform_idea($id, $app['user']['id_user']);

		push_notif(new_notification(
			'Idée ajoutée !',
			'Merci d\'avoir contribué.',
			'success'
		));
	}else{
		push_notif(new_notification(
			'Impossible d\'ajouter l\'idée',
			'Une erreur est survenue lors de l\'ajout de l\'idée.',
			'danger'
		));
	}

	return redirect('/idea/', $app);
});

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