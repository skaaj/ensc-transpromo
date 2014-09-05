<?php
// build an array containing a notification
function new_notification($title, $message, $type, $action_1 =  null, $target_1 = null, $action_2 = null, $target_2 = null)
{
	$notif = array();

	// Default notification
	$notif['title']   = $title;
	$notif['message'] = $message;
	$notif['type']    = $type;

	// Main action
	if($action_1 != null){
		$notif['action']['label']  = $action_1;
		$notif['action']['target'] = $target_1;
	}

	// Alternative action
	if($action_2 != null){
		$notif['alt']['label']  = $action_2;
		$notif['alt']['target'] = $target_2;
	}

	return $notif;
}

// push a new data into an array
function instanciate(&$array){
	if(!isset($array))
		$array = array();
}

function push(&$array, $label, $data)
{
	instanciate($array);

	$array[$label] = $data;
}

// TODO (v1.0)
//   Allow multiple notifications
function push_notif($notif)
{
	// erase the last one (< v1.0)
	$_SESSION['notif'] = $notif;
}

function pop_notif()
{
	$notif = null;

	if(isset($_SESSION['notif']))
	{
		$notif = $_SESSION['notif'];
		unset($_SESSION['notif']);
	}

	return $notif;
}

function check_user(&$array, $app)
{
	// index did not load the user
	if(isset($_SESSION['id_user']) && empty($app['user']))
		$app['user'] = $app['database']->get_user($_SESSION['id_user']);
	// index did not delete the user
	else if(empty($_SESSION['id_user']) && isset($app['user']))
		unset($app['user']);

	if(isset($app['user'])){
		push($array, 'user', $app['user']);
	}
}

function set_active(&$array, $label)
{
	push($array, 'active', $label);
}

function check_notif(&$array)
{
	$notif = pop_notif();

	if($notif != null)
		push($array, 'notif', $notif);
	else
		instanciate($array);
}