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

    public function detailsAction()
    {
        $this->view['user'] = User::find($_GET['idUser']);

        $this->view += NotificationHelper::flush();
        $this->view['debug']['session'] = $_SESSION;

        View::renderTemplate('User/details.html.twig', $this->view);
    }

    public function addAction()
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                // Affichage du formulaire
                View::renderTemplate('/User/add.html.twig', $this->view);
                break;

            case 'POST':
                // Insertion du nouvel utilisateur
                $user = $_POST;
                User::add($user);

                NotificationHelper::set('user.add', 'success', 'Utilisateur ajouté');
                header('Location: /User');
                exit;
        }
    }

    public function editAction()
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $idUser = $_GET['idUser'];
                $this->view['user'] = User::find($idUser);

                View::renderTemplate('/User/edit.html.twig', $this->view);
                break;

            case 'POST':
                // Mise à jour de utilisateur
                $user = $_POST;
                User::update($user);

                NotificationHelper::set('user.edit', 'success', 'Utilisateur mise à jour');
                header('Location: /User');
                exit;
        }
    }

    public function editPasswordAction()
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                if (!empty($_SESSION['user'])) {
                    $this->view['user'] = $_SESSION['user'];
                    unset($_SESSION['user']);
                } else {
                    $idUser = $_GET['idUser'];
                    $this->view['user'] = User::find($idUser);
                }

                unset($this->view['user']['password'], $this->view['user']['passwordconfirm']);

                $this->view['debug']['session'] = $_SESSION;
                $this->view += NotificationHelper::flush();

                View::renderTemplate('user/editPassword.html.twig', $this->view);
                break;

            case 'POST':
                $userForm = $_POST;

                if($userForm['password'] !== $userForm['passwordconfirm']) {
                    $_SESSION['user'] = $userForm;
                    
                    NotificationHelper::set('user.editPassword', 'warning', 'Les mots de passes ne correspondent pas');
                    header('Location: /user/editPassword?idUser=' . $userForm['idUser']);
                    exit;
                }

                if(!User::updatePassword($userForm)) {
                    $_SESSION['user'] = $userForm;
                    
                    NotificationHelper::set('user.editPassword', 'warning', 'Erreur lors de la sauvegarde du mot de passe de l\'utilisateur');
                    header('Location: /user/editPassword?idUser=' . $userForm['idUser']);
                    exit;
                }

                NotificationHelper::set('user.editPassword', 'success', 'Mot de passe de l\'utilisateur sauvegardé');
                header('Location: /user');
                exit;
                break;

            default:
                http_response_code(422);
                exit;
                break;
        }
    }

    public function removeAction()
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $idUser = $_GET['idUser'];
                $this->view['user'] = User::find($idUser);

                $this->view += NotificationHelper::flush();
                View::renderTemplate('/User/remove.html.twig', $this->view);
                break;

            case 'POST':
                $user = $_POST;

                if (!User::remove($user)) {
                    $_SESSION['user'] = $user;

                    NotificationHelper::set('user.remove', 'warning', 'Erreur lors de la suppression de l\'utilisateur');
                    header('Location: /user');
                    exit;
                }

                NotificationHelper::set('user.remove', 'success', 'Utilisateur supprimé');
                header('Location: /user');
                exit;

            default:
                http_response_code(422);
                exit;
        }
    }
}
