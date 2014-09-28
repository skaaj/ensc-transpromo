<?php

namespace Module;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Project extends Module {

    protected function init_routes()
    {
        // GET LIST
        $this->app->get('/project/', function() {
            get_context($array, $this->app);

            push($array, 'projects', $this->app['database']->get_projects());
            set_active($array, 'project');

            return $this->twig->render('projects.html.twig', $array);
        });

        // GET ADD
        $this->app->get('/project/add', function() {
            get_context($array, $this->app);
            set_active($array, 'project');

            if($this->app['user']['qualite'] < 2){
                push_notif(new_notification(
                    'Action refusée !',
                    'Vous devez être en deuxième année pour créer un projet.',
                    'warning'
                ));

                $sub_request = Request::create('/project/', 'GET');
                return $this->app->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
            }
            
            return $this->twig->render('project_add.html.twig', $array);
        });

        // POST ADD
        $this->app->post('/project/add', function() {
            get_context($array, $this->app);
            set_active($array, 'project');

            if($this->app['user']['qualite'] < 2){
                push_notif(new_notification(
                    'Action refusée !',
                    'Vous devez être en deuxième année pour créer un projet.',
                    'warning'
                ));

                $sub_request = Request::create('/project/', 'GET');
                return $this->app->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
            }

            $result = $this->app['database']->insert_project($_POST['title'], $_POST['descr'], $_POST['seek'], $this->app['user']['id_user']);
            
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
            return $this->app->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
        });

        // GET EDIT
        $this->app->get('/project/edit/{id}', function($id) {
            get_context($array, $this->app);
            set_active($array, 'project');

            push($array, 'project', $this->app['database']->get_project($id));

            if($array['project']['id_user_cre'] != $this->app['user']['id_user']){
                push_notif(new_notification(
                    'Action refusée !',
                    'Vous devez être propriétaire du projet que vous souhaitez éditer.',
                    'warning'
                ));

                $sub_request = Request::create('/project/'.$id, 'GET');
                return $this->app->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
            }

            return $this->twig->render('project_edit.html.twig', $array);
        });

        // POST EDIT
        $this->app->post('/project/edit/{id}', function($id) {
            get_context($array, $this->app);
            set_active($array, 'project');

            push($array, 'project', $this->app['database']->get_project($id));

            if($array['project']['id_user_cre'] != $this->app['user']['id_user']){
                push_notif(new_notification(
                    'Action refusée !',
                    'Vous devez être propriétaire du projet que vous souhaitez éditer.',
                    'warning'
                ));

                $sub_request = Request::create('/project/'.$id, 'GET');
                return $this->app->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
            }

            $this->app['database']->edit_project($_POST['title'], $_POST['descr'], $_POST['seek'], $id);

            push_notif(new_notification(
                'Projet édité !',
                'N\'éditez pas trop souvent votre projet pour ne pas perdre les autres utilisateurs.',
                'success'
            ));

            $sub_request = Request::create('/project/', 'GET');
            return $this->app->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
        });

        $this->app->get('/project/delete/{id}', function($id) {
            get_context($array, $this->app);
            set_active($array, 'project');

            push($array, 'project', $this->app['database']->get_project($id));

            if($array['project']['id_user_cre'] != $this->app['user']['id_user']){
                push_notif(new_notification(
                    'Action refusée !',
                    'Vous devez être propriétaire du projet que vous souhaitez supprimer.',
                    'warning'
                ));

                $sub_request = Request::create('/project/'.$id, 'GET');
                return $this->app->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
            }

            $this->app['database']->delete_project($id);

            push_notif(new_notification(
                'Projet supprimé !',
                'Votre projet a été supprimé et ne peut pas être récupéré. Cependant cette fonctionnalité pourrait faire l\'objet d\'une mise à jour.',
                'success'
            ));

            $sub_request = Request::create('/', 'GET');
            return $this->app->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
        });

        $this->app->get('/project/accept/{id}/{cand}', function($id, $cand) {
            get_context($array, $this->app);
            set_active($array, 'project');

            push($array, 'project', $this->app['database']->get_project($id));

            if($array['project']['id_user_cre'] != $this->app['user']['id_user']){
                push_notif(new_notification(
                    'Action refusée !',
                    'Vous devez être propriétaire du projet pour accepter une candidature.',
                    'warning'
                ));

                $sub_request = Request::create('/project/'.$id, 'GET');
                return $this->app->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
            }

            if($this->app['database']->accept_application($cand)){
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
            return $this->app->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
        });

        // GET PROJECT DETAILS
        $this->app->get('/project/{id}', function($id) {
            get_context($array, $this->app);
            set_active($array, 'project');
            
            if(!empty($this->app['user'])){
                push($array, 'owner', $this->app['database']->get_owned_project($this->app['user']['id_user']));
                push($array, 'has_application', $this->app['database']->has_application($this->app['user']['id_user'], $id));
                push($array, 'applications', $this->app['database']->get_applications($id));
                push($array, 'team', $this->app['database']->get_team($id));
            }

            push($array, 'project', $this->app['database']->get_project($id));
            push($array, 'count', $this->app['database']->get_places($id));

            return $this->twig->render('project.html.twig', $array);
        });

        // GET APPLY
        $this->app->post('/project/apply/{id}', function($id) {
            get_context($array, $this->app);

            $ids = $this->app['database']->has_already_project($this->app['user']['id_user']);

            if(!empty($ids))
            {
                return notif_n_redirect(new_notification('Candidature refusée !', 'Vous êtes déjà dans un projet.', 'danger'), '/', $this->app);
            }else{
                if($this->app['database']->add_application($_POST['motiv'], $this->app['user']['id_user'], $id))
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

            return redirect('/', $this->app);
        });
    }
}