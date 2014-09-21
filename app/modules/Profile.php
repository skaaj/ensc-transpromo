<?php

namespace Module;

class Register extends Module {

    protected function init_routes()
    {
        $this->app->get('/register', function () {
            check_notif($array);

            return $this->twig->render('register.html.twig', $array);
        });

        $this->app->post('/register', function() {

            $result = $this->app['database']->insert_user(
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
                push_notif(new_notification(
                    'Inscription réussie !',
                    'Vous pouvez maintenant vous connecter avec vos identifiants.',
                    'success'
                ));

                $sub_request = Request::create('/login', 'GET');
                return $this->app->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
            }
        });
    }
}