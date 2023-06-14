<?php

namespace App\Controllers;

use \Core\View;
use \App\Helpers\NotificationHelper;
use \App\Models\User;
use App\Libs\SessionSecurityHandler;

class AuthFormController extends \Core\Controller
{
    private $sessionSecurityHandler;

    public function __construct($route_params)
    {
        parent::__construct($route_params);

        $this->sessionSecurityHandler = new SessionSecurityHandler();
    }

    public function loginAction()
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                if(isset($_SESSION['user'])) {
                    NotificationHelper::set('authForm.login', 'warning', 'Vous êtes déjà connecté');
                    header('Location: /auth');
                    exit;
                }

                $this->view += NotificationHelper::flush();
                View::renderTemplate('AuthForm/login.html.twig', $this->view);
                break;

            case 'POST':
                $userForm = $_POST;

                $user = User::findByMailAddressAndPassword($userForm['mailAddress'], $userForm['password']);

                if($user == null) {
                    NotificationHelper::set('authForm.login', 'warning', 'Le nom d\'utilisateur ou mot de passe est invalide');
                    header('Location: /auth');
                    exit;
                }

                $this->sessionSecurityHandler->regenerateSession();

                $_SESSION['user'] = $user;

                NotificationHelper::set('authForm.login', 'success', 'Le processus de connexion a réussi');
                header('Location: /auth');
                exit;

            default:
                http_response_code(422);
                exit;
        }
    }

    public function logoutAction()
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                if(empty($_SESSION['user'])) {
                    NotificationHelper::set('authForm.logout', 'warning', 'Vous n\'êtes pas connecté');
                    header('Location: /auth');
                    exit;
                }

                View::renderTemplate('AuthForm/logout.html.twig');
                break;

            case 'POST':
                if(empty($_SESSION['user'])) {
                    NotificationHelper::set('authForm.logout', 'warning', 'Vous n\'êtes pas connecté');
                    header('Location: /auth');
                    exit;
                }

                $this->sessionSecurityHandler->destroySession();

                NotificationHelper::set('authForm.logout', 'success', 'Le processus de déconnexion a réussi');
                header('Location: /auth');
                exit;

            default:
                http_response_code(422);
                exit;
        }
    }
}