<?php

namespace App\Controllers;

use App\Helpers\CSRFSecurityHelper;
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

                $this->view += CSRFSecurityHelper::createAndFlush(__METHOD__);
                $this->view += NotificationHelper::flush();
                View::renderTemplate('AuthForm/login.html.twig', $this->view);
                break;

            case 'POST':
                $userForm = $_POST;

                if(!CSRFSecurityHelper::verify(__METHOD__, $userForm)) {
                    NotificationHelper::set('authForm.login', 'warning', 'Le jeton CSRF n\'est pas valide');
                    header('Location: /authForm/login', true, 302);
                    exit;
                }

                $user = User::findByMailAddressAndPassword($userForm['mailAddress'], $userForm['passwordPlaintext']);

                if($user == null) {
                    NotificationHelper::set('authForm.login', 'warning', 'Le nom d\'utilisateur ou mot de passe est invalide');
                    header('Location: /auth');
                    exit;
                }

                $this->sessionSecurityHandler->regenerateSession();

                $_SESSION['user'] = $user;
                unset($user['passwordPlainText']);

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

                $this->view += CSRFSecurityHelper::createAndFlush(__METHOD__);
                $this->view += NotificationHelper::flush();
                View::renderTemplate('AuthForm/logout.html.twig', $this->view);
                break;

            case 'POST':
                $userForm = $_POST;

                if(!CSRFSecurityHelper::verify(__METHOD__, $userForm)) {
                    NotificationHelper::set('authForm.logout', 'danger', 'Le jeton CSRF n\'est pas valide');
                    header('Location: /authFrom/login', true, 302);
                    exit;
                }

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