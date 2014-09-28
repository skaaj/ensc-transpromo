<?php

namespace Module;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Profile extends Module {

    protected function init_routes()
    {
        $this->app->get('/profile/{id}', function($id) {
            get_context($array, $this->app);

            push($array, 'infos', $this->app['database']->get_profile($id));

            return $this->twig->render('profile.html.twig', $array);
        });
    }
}