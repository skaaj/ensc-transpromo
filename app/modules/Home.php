<?php

namespace Module;

class Home extends Module {

    protected function init_routes()
    {
        $this->app->get('/', function() {
            push($array, 'deadlines',    $this->app['database']->get_deadlines());
            push($array, 'informations', $this->app['database']->get_informations());
                
            set_active($array, 'home');
                
            get_context($array, $this->app);

            return $this->twig->render('index.html.twig', $array);
        });
    }
}