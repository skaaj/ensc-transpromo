<?php

namespace Module;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Register extends Module {

    protected function init_routes()
    {
        $this->app->get('/register', function () {
            check_notif($array);

            return $this->twig->render('register.html.twig', $array);
        });

        $this->app->post('/register', function() {

            $key = md5(microtime(TRUE).$_POST['mail']);
            $result = $this->app['database']->insert_user(
                $_POST['prenom'],
                $_POST['nom'],
                $_POST['mail'],
                $_POST['pwd'],
                $_POST['year'],
                $_POST['school'],
                $_POST['skill'],
                $_POST['public'],
                $key
                );

            if($result === 'mail'){
                push_notif(new_notification(
                    'Échec de l\'inscription !',
                    'L\'adresse email indiquée existe déjà.',
                    'danger'
                ));

                $sub_request = Request::create('/register', 'GET');
                return $this->app->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
            }elseif($result === false){
                push_notif(new_notification(
                    'Échec de l\'inscription !',
                    'Une erreur est survenue lors de l\'inscription. Veuillez recommencer.',
                    'danger'
                ));

                $sub_request = Request::create('/register', 'GET');
                return $this->app->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
            }else{
                send_mail("Activation de votre compte", "Cliquez sur ce lien pour terminer votre inscription : http://localhost/ensc-transpromo/confirm/" . $key, $_POST['mail']);
                push_notif(new_notification(
                    'Clé d\'activation envoyée',
                    'Votre compte a été créé mais n\'est pas encore validé. Vous allez recevoir un courriel afin de pouvoir confirmer votre inscription.',
                    'success'
                ));

                $sub_request = Request::create('/', 'GET');
                return $this->app->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
            }
        });

        $this->app->get('/confirm/{key}', function ($key) {
            check_notif($array);

            $affected_rows = $this->app['database']->confirm_user($key);

            if($affected_rows == 0){
                push_notif(new_notification(
                    'Impossible de valider l\'inscription',
                    'Une erreur est survenue lors de la validation. Contactez un administrateur.',
                    'danger'
                ));

                $sub_request = Request::create('/', 'GET');
                return $this->app->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
            }else{
                push_notif(new_notification(
                    'Inscription validée !',
                    'Vous pouvez maintenant vous connecter sur le site.',
                    'success'
                ));

                $sub_request = Request::create('/login', 'GET');
                return $this->app->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
            }
        });
    }
}