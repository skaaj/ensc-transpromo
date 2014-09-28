<?php

namespace Module;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Login extends Module {

    protected function init_routes()
    {
        $this->app->get('/login', function () {
            check_notif($array);

            return $this->twig->render('login.html.twig', $array);
        });

        $this->app->post('/login', function() {
            $user = $this->app['database']->get_user_mail($_POST['mail']);

            $local_pwd  = $_POST['pwd'];
            $remote_pwd = $user['mdp'];

            if($remote_pwd == $local_pwd)
            {
                $_SESSION['id_user'] = $user['id_user'];

                push_notif(new_notification(
                    'Connexion réussie !',
                    'Vous pouvez maintenant utiliser le site.',
                    'success'
                ));

                $sub_request = Request::create('/', 'GET');
                return $this->app->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
            }
            else
            {
                push_notif(new_notification(
                    'Erreur !',
                    'Le compte n\'existe pas ou le mot de passe est incorrect.',
                    'danger'
                ));

                $sub_request = Request::create('/login', 'GET');
                return $this->app->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
            }
        });

        /* LOGOUT */

        $this->app->get('/logout', function() {
            unset($_SESSION['id_user']);

            push_notif(new_notification(
                'Succés !',
                'Vous avez bien été déconnecté.',
                'success'
            ));

            $sub_request = Request::create('/', 'GET');
            return $this->app->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
        });
    }
}