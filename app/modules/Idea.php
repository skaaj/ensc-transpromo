<?php

namespace Module;

class Idea extends Module {

    protected function init_routes()
    {
        // list - design
        $this->app->get('/idea/', function() {
            get_context($array, $this->app);

            push($array, 'ideas', $this->app['database']->get_ideas());
            set_active($array, 'idea');

            return $this->twig->render('ideas.html.twig', $array);
        });

        $this->app->get('/idea/add', function() {
            get_context($array, $this->app);
            set_active($array, 'idea');

            return $this->twig->render('idea_add.html.twig', $array);
        });

        $this->app->post('/idea/add', function() {
            get_context($array, $this->app);
            set_active($array, 'idea');

            $result = $this->app['database']->insert_idea($_POST['title'], $_POST['desc'], $this->app['user']['id_user']);
            
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

            return redirect('/idea/', $this->app);
        });

        $this->app->get('/idea/adopt/{id}', function($id) {
            get_context($array, $this->app);
            set_active($array, 'idea');

            $can_adopt = $this->app['database']->has_already_project($this->app['user']['id_user']);
            
            if($can_adopt){
                $this->app['database']->transform_idea($id, $this->app['user']['id_user']);

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

            return redirect('/idea/', $this->app);
        });
    }
}