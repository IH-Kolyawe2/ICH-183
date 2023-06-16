<?php

namespace App\Controllers;

use App\Helpers\CSRFSecurityHelper;
use App\Models\User;
use App\Helpers\NotificationHelper;
use \Core\View;

class UserController extends \Core\Controller
{
    public function indexAction()
    {
        $this->logger->info(
            'Begining handle `' . $_SERVER['REQUEST_METHOD'] . '` request on `' . __FUNCTION__ . '`', 
            ['idUser' => $_SESSION['user']['idUser'] ?? null]
        );

        $users = User::getAll();
        $this->view['users'] = $users;

        $this->logger->debug(
            'Got ' . count($this->view['users']) . ' results :', 
            ['idUser' => $_SESSION['user']['idUser'] ?? null, 'users' => $this->view['users']]
        );

        CSRFSecurityHelper::clear();
        $this->view['debug']['session'] = $_SESSION;
        $this->view += NotificationHelper::flush();

        View::renderTemplate(
            '/User/index.html.twig',
            $this->view
        );
    }

    public function detailsAction()
    {
        $this->logger->info(
            'Begining handle `' . $_SERVER['REQUEST_METHOD'] . '` request on `' . __FUNCTION__ . '`', 
            ['idUser' => $_SESSION['user']['idUser'] ?? null]
        );

        $user = $_GET;

        $validationMessages = User::validate($user);

        if (!empty($validationMessages)) {
            NotificationHelper::set('user.detail', 'warning', 'Erreur lors de l\'affichage de l\'utilisateur : <br>' . implode('<br>', $validationMessages));
            header('Location: /user/index');
            exit;
        }

        $this->view['user'] = User::find($user['idUser']);

        CSRFSecurityHelper::clear();
        $this->view['debug']['session'] = $_SESSION;
        $this->view += NotificationHelper::flush();

        View::renderTemplate('User/details.html.twig', $this->view);
    }

    public function addAction()
    {
        $this->logger->info(
            'Begining handle `' . $_SERVER['REQUEST_METHOD'] . '` request on `' . __FUNCTION__ . '`', 
            ['idUser' => $_SESSION['user']['idUser'] ?? null]
        );

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                if(!empty($_SESSION['savedstate'])) {
                    $user = $_SESSION['savedstate'];
                    $this->logger->debug(
                        'Restoring model from saved state', 
                        ['idUser' => $_SESSION['user']['idUser'] ?? null, 'user' => $this->view['savedstate']]
                    );
                    unset($_SESSION['savedstate']);
                }else {
                    $user = [];
                }
                
                $this->view['user'] = $user;
                $this->view += CSRFSecurityHelper::createAndFlush(__METHOD__);
                $this->view['debug']['session'] = $_SESSION;
                $this->view += NotificationHelper::flush();

                // Affichage du formulaire
                View::renderTemplate('/User/add.html.twig', $this->view);
                break;

            case 'POST':
                // Insertion du nouvel utilisateur
                $user = $_POST;

                if(!CSRFSecurityHelper::verify(__METHOD__, $user)) {
                    NotificationHelper::set('user.add', 'danger', 'Le jeton CSRF n\'est pas valide');
                    header('Location: /user', true, 302);
                    exit;
                }

                $validationMessages = User::validate($user);

                if(!empty($validationMessages)) {
                    unset(
                        $user['passwordPlaintext'],
                        $user['passwordPlaintextConfirm']
                    );

                    $_SESSION['savedState'] = $user;

                    NotificationHelper::set('user.add', 'warning', 'Erreur lors de l\'ajout de l\'utilisateur : <br>' . implode('<br>', $validationMessages));
                    header('Location: /user/add');
                    exit;
                };

                if($user['passwordPlaintext'] !== $user['passwordPlaintextConfirm']) {
                    unset(
                        $uset['passwordPlaintext'],
                        $uset['passwordPlaintextConfirm']
                    );

                    $this->logger->notice(
                        'Passwords are different', 
                        ['idUser' => $_SESSION['user']['idUser'] ?? null]
                    );

                    $_SESSION['savedstate'] = $user;
                    NotificationHelper::set('user.add', 'warning', 'Les mots passes correspondent pas');
                    header('Location: /user/add');
                    exit;
                }

                if(!User::add($user)) {
                    unset(
                        $user['passwordPlaintext'],
                        $user['passwordPlaintextConfirm']
                    );                    

                    $this->logger->notice(
                        'Unable to add user', 
                        ['idUser' => $_SESSION['user']['idUser'] ?? null, 'user' => $user]
                    );

                    $_SESSION['savedstate'] = $user;

                    NotificationHelper::set('user.add', 'warning', 'Erreur lors de l\'ajout de l\'utilisateur');
                    header('Location: /user/add');
                    exit;
                }

                $this->logger->info('User added', ['idUser' => $_SESSION['user']['idUser'] ?? null]);
                NotificationHelper::set('user.add', 'success', 'Utilisateur ajouté');
                header('Location: /User');
                exit;

            default:
                $this->logger->warning(
                    'Request method not supported', 
                    ['idUser' => $_SESSION['user']['idUser'] ?? null, 'requestMethod' => $_SERVER['REQUEST_METHOD']]
                );
                http_response_code(422);
                exit;
        }
    }

    public function editAction()
    {
        $this->logger->info(
            'Begining handle `' . $_SERVER['REQUEST_METHOD'] . '` request on `' . __FUNCTION__ . '`', 
            ['idUser' => $_SESSION['user']['idUser'] ?? null]
        );

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                if(!empty($_SESSION['user'])) {
                    $user = $_SESSION['savedstate'];
                    $this->logger->debug(
                        'Restoring model from saved state', 
                        ['idUser' => $_SESSION['user']['idUser'] ?? null, 'user' => $this->view['savedstate']]
                    );
                    unset($_SESSION['savedstate']);
                } else {
                    $this->logger->info(
                        'Filtering on user.', 
                        ['idUser' => $_SESSION['user']['idUser'] ?? null, 'idUser' => $_GET['idUser']]
                    );
                    $user = $_GET;
                    $validationMessages = User::validate($user);

                    if (!empty($validationMessages)) {
                        $_SESSION['savedState'] = $user;
                        NotificationHelper::set('user.edit', 'warning', 'Erreur lors de l\'édition de l\'utilisateur : <br>' . implode('<br>', $validationMessages));
                        header('Location: /user/index');
                        exit;
                    }

                    $user = User::find($user['idUser']);
                }

                $this->view['user'] = $user;
                $this->view += CSRFSecurityHelper::createAndFlush(__METHOD__);
                $this->view['debug']['session'] = $_SESSION;
                $this->view += NotificationHelper::flush();

                View::renderTemplate('/User/edit.html.twig', $this->view);
                break;

            case 'POST':
                // Mise à jour de utilisateur
                $user = $_POST;

                if(!CSRFSecurityHelper::verify(__METHOD__, $user)) {
                    NotificationHelper::set('user.edit', 'danger', 'Le jeton CSRF n\'est pas valide');
                    header('Location: /user', true, 302);
                    exit;
                }

                $validationMessages = User::validate($user);

                if(!empty($validationMessages)) {
                    $_SESSIO['savedState'] = $user;
                    
                    NotificationHelper::set('user.edit', 'warning', 'Erreur lors de la modification de l\'utilisateur : : <br>' . implode('<br>', $validationMessages));
                    header('Location: /user/index');
                    exit;
                }

                $dbModel = User::find($user['idUser']);
                $user = array_merge($dbModel, $user);

                if(!User::update($user)) {
                    $_SESSION['savedState'] = $user;

                    $this->logger->notice(
                        'Unable to update user', 
                        ['idUser' => $_SESSION['user']['idUser'] ?? null, 'user' => $user]
                    );

                    NotificationHelper::set('user.edit', 'warning', 'Erreur lors de la sauvegarde de l\'utilisateur');
                    header('Location: /user/edit?idUser=' . $user['idUser']);
                    exit;
                }

                $this->logger->info('User updated', ['idUser' => $_SESSION['user']['idUser'] ?? null]);
                NotificationHelper::set('user.edit', 'success', 'Utilisateur mise à jour');
                header('Location: /User');
                exit;
            
            default:
                $this->logger->warning(
                    'Request method not supported', 
                    ['idUser' => $_SESSION['user']['idUser'] ?? null, 'requestMethod' => $_SERVER['REQUEST_METHOD']]
                );
                http_response_code(422);
                exit;
        }
    }

    public function editPasswordAction()
    {
        $this->logger->info(
            'Begining handle `' . $_SERVER['REQUEST_METHOD'] . '` request on `' . __FUNCTION__ . '`', 
            ['idUser' => $_SESSION['user']['idUser'] ?? null]
        );

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $this->logger->info(
                    'Filtering on user.', 
                    ['idUser' => $_SESSION['user']['idUser'] ?? null, 'idUser' => $_GET['idUser']]
                );
                
                $user = $_GET;
                $validationMessages = User::validate($user);

                if (!empty($validationMessages)) {
                    NotificationHelper::set('user.editPassword', 'warning', 'Erreur lors de l\'édition de l\'utilisateur : <br>' . implode('<br>', $validationMessages));
                    header('Location: /user/index');
                    exit;
                }

                $this->view['user'] = User::find($user['idUser']);
                $this->view += CSRFSecurityHelper::createAndFlush(__METHOD__);
                $this->view['debug']['session'] = $_SESSION;
                $this->view += NotificationHelper::flush();

                View::renderTemplate('user/editPassword.html.twig', $this->view);
                break;

            case 'POST':
                $userForm = $_POST;

                if(!CSRFSecurityHelper::verify(__METHOD__, $userForm)) {
                    NotificationHelper::set('user.add', 'danger', 'Le jeton CSRF n\'est pas valide');
                    header('Location: /user', true, 302);
                    exit;
                }

                $validationMessages = User::validate($userForm);

                if (!empty($validationMessages)) {
                    NotificationHelper::set('user.editPassword', 'warning', 'Erreur lors de l\'édition de l\'utilisateur : <br>' . implode('<br>', $validationMessages));
                    header('Location: /user/editPassword?idUser=' . $userForm['idUser']);
                    exit;
                }

                if($userForm['passwordPlaintext'] !== $userForm['passwordPlaintextConfirm']) {
                    unset(
                        $userForm['passwordPlaintext'],
                        $userForm['passwordPlaintextConfirm']
                    );
                    
                    $this->logger->notice(
                        'Passwords are different', 
                        ['idUser' => $_SESSION['user']['idUser'] ?? null]
                    );

                    $_SESSION['savedState'] = $userForm;
                    
                    NotificationHelper::set('user.editPassword', 'warning', 'Les mots de passes ne correspondent pas');
                    header('Location: /user/editPassword?idUser=' . $userForm['idUser']);
                    exit;
                }

                $dbModel = User::find($userForm['idUser']);
                $user = array_merge($dbModel, $userForm);

                if(!User::updatePassword($user)) {
                    unset(
                        $userForm['passwordPlaintext'],
                        $userForm['passwordPlaintextConfirm']
                    );

                    $this->logger->notice(
                        'Unable to update user password', 
                        ['idUser' => $_SESSION['user']['idUser'] ?? null, 'user' => $userForm]
                    );

                    $_SESSION['user'] = $userForm;
                    
                    NotificationHelper::set('user.editPassword', 'warning', 'Erreur lors de la sauvegarde du mot de passe de l\'utilisateur');
                    header('Location: /user/editPassword?idUser=' . $userForm['idUser']);
                    exit;
                }

                $this->logger->info('User password updated', ['idUser' => $_SESSION['user']['idUser'] ?? null]);
                NotificationHelper::set('user.editPassword', 'success', 'Mot de passe de l\'utilisateur sauvegardé');
                header('Location: /user');
                exit;
                break;

            default:
                $this->logger->warning(
                    'Request method not supported', 
                    ['idUser' => $_SESSION['user']['idUser'] ?? null, 'requestMethod' => $_SERVER['REQUEST_METHOD']]
                );
                http_response_code(422);
                exit;
        }
    }

    public function removeAction()
    {
        $this->logger->info(
            'Begining handle `' . $_SERVER['REQUEST_METHOD'] . '` request on `' . __FUNCTION__ . '`', 
            ['idUser' => $_SESSION['user']['idUser'] ?? null]
        );

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                if (!empty($_SESSION['savedState'])) {
                    $user = $_SESSION['savedState'];
                } else {
                    $this->logger->info(
                        'Filtering on user.', 
                        ['idUser' => $_SESSION['user']['idUser'] ?? null, 'idUser' => $_GET['idUser']]
                    );
                    $user = $_GET;

                    $validationMessages = User::validate($user);

                    if (!empty($validationMessages)) {
                        $_SESSION['savedState'] = $user;
                        NotificationHelper::set('user.remove', 'warning', 'Erreur lors de la suppression de l\'utilisateur : <br>' . implode('<br>', $validationMessages));
                        header('Location: /user/index');
                        exit;
                    }

                    $user = User::find($user['idUser']);
                }
                
                $this->view['user'] = $user;
                $this->view += CSRFSecurityHelper::createAndFlush(__METHOD__);
                $this->view['debug']['sessuib'] = $_SESSION;
                $this->view += NotificationHelper::flush();

                View::renderTemplate('/User/remove.html.twig', $this->view);
                break;

            case 'POST':
                $user = $_POST;

                if(!CSRFSecurityHelper::verify(__METHOD__, $user)) {
                    NotificationHelper::set('user.remove', 'danger', 'Le jeton CSRF n\'est pas valide');
                    header('Location: /user', true, 302);
                    exit;
                }

                if (!User::remove($user)) {
                    $_SESSION['savedState'] = $user;

                    $this->logger->notice(
                        'Unable to remove user', 
                        ['idUser' => $_SESSION['user']['idUser'] ?? null, 'user' => $user]
                    );

                    NotificationHelper::set('user.remove', 'warning', 'Erreur lors de la suppression de l\'utilisateur');
                    header('Location: /user/remove?idUser=' . $user['idUser']);
                    exit;
                }

                $this->logger->info('User removed', ['idUser' => $_SESSION['user']['idUser'] ?? null]);
                NotificationHelper::set('user.remove', 'success', 'Utilisateur supprimé');
                header('Location: /user');
                exit;

            default:
                $this->logger->warning(
                    'Request method not supported', 
                    ['idUser' => $_SESSION['user']['idUser'] ?? null, 'requestMethod' => $_SERVER['REQUEST_METHOD']]
                );
                http_response_code(422);
                exit;
        }
    }
}
