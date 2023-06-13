<?php

namespace App\Controllers;


use App\Models\User;
use App\Helpers\NotificationHelper;
use \Core\View;

class UserController extends \Core\Controller
{
    public function indexAction()
    {
        $users = User::getAll();
        $this->view['users'] = $users;
        $this->view += NotificationHelper::flush();

        View::renderTemplate(
            '/User/index.html.twig',
            $this->view
        );
    }

    public function addAction()
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                // Affichage du formulaire
                View::renderTemplate(
                    '/User/add.html.twig',
                    $this->view
                );
                break;

            case 'POST':
                // Insertion du nouvel utilisateur
                $user = $_POST;
                User::add($user);

                NotificationHelper::set('user.add', 'success', 'Utilisateur ajouté');

                header('Location: /User/index');
                exit;
        }
    }

    public function editAction()
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $idUser = $_GET['idUser'];
                $this->view['user'] = User::find($idUser);

                View::renderTemplate(
                    '/User/edit.html.twig',
                    $this->view
                );
                break;

            case 'POST':
                // Mise à jour de utilisateur
                $user = $_POST;
                User::update($user);

                NotificationHelper::set('user.edit', 'success', 'Utilisateur mise à jour');

                header('Location: /User/index');
                exit;
        }
    }

    public function removeAction()
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $idUser = $_GET['idUser'];
                $this->view['user'] = User::find($idUser);

                $this->view += NotificationHelper::flush();
                View::renderTemplate(
                    '/User/remove.html.twig',
                    $this->view
                );
                break;

            case 'POST':
                $user = $_POST;

                if (!User::remove($user)) {
                    $_SESSION['user'] = $user;

                    NotificationHelper::set('user.remove', 'warning', 'Erreur lors de la suppression de l\'utilisateur');

                    header('Location: /user/index');
                    exit;
                }

                NotificationHelper::set('user.remove', 'success', 'Utilisateur supprimé');
                header('Location: /user/index');
                exit;

            default:
                http_response_code(422);
                exit;
        }
    }
}
