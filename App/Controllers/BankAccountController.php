<?php

namespace App\Controllers;

use App\Helpers\CSRFSecurityHelper;
use App\Models\BankAccount;
use App\Helpers\NotificationHelper;
use App\Models\User;
use \Core\View;

class BankAccountController extends \Core\Controller
{
    public function indexAction()
    {
        $this->logger->info(
            'Begining handle `' . $_SERVER['REQUEST_METHOD'] . '` request on `' . __FUNCTION__ . '`', 
            ['idUser' => $_SESSION['user']['idUser'] ?? null]
        );
        
        $this->view['bankAccounts'] = !empty($_GET['idOwner'])
            ? BankAccount::findByIdOwner($_GET['idOwner'])
            : BankAccount::getAll();

        $this->logger->debug(
            'Got ' . count($this->view['bankAccounts']) . ' results :', 
            ['idUser' => $_SESSION['user']['idUser'] ?? null, 'bankAccounts' => $this->view['bankAccounts']]
        );
            
        CSRFSecurityHelper::clear();
        $this->view['debug']['session'] = $_SESSION;
        $this->view += NotificationHelper::flush();

        View::renderTemplate('BankAccount/index.html.twig', $this->view);
    }

    public function detailsAction()
    {
        $this->logger->info(
            'Begining handle `' . $_SERVER['REQUEST_METHOD'] . '` request on `' . __FUNCTION__ . '`', 
            ['idUser' => $_SESSION['user']['idUser'] ?? null]
        );
        
        $this->view['bankAccount'] = BankAccount::find($_GET['idBankAccount']);

        CSRFSecurityHelper::clear();
        $this->view['debug']['session'] = $_SESSION;
        $this->view += NotificationHelper::flush();

        View::renderTemplate('BankAccount/details.html.twig', $this->view);
    }

    public function addAction()
    {
        $this->logger->info(
            'Begining handle `' . $_SERVER['REQUEST_METHOD'] . '` request on `' . __FUNCTION__ . '`', 
            ['idUser' => $_SESSION['user']['idUser'] ?? null]
        );
        
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                if(!empty($_SESSION['bankAccount'])) {
                    $this->view['bankAccount'] = $_SESSION['bankAccount'];
                    $this->logger->debug(
                        'Restoring model from saved state', 
                        ['idUser' => $_SESSION['user']['idUser'] ?? null, 'bankAccount' => $this->view['bankAccount']]
                    );
                    unset($_SESSION['bankAccount']);
                } else {
                    $this->view['bankAccount'] = [];
                }

                $this->view['availableOwners'] = User::getAll();

                $this->view += CSRFSecurityHelper::createAndFlush(__METHOD__);
                $this->view['debug']['session'] = $_SESSION;
                $this->view += NotificationHelper::flush();

                View::renderTemplate('BankAccount/add.html.twig', $this->view);
                break;

            case 'POST':
                $bankAccountForm = $_POST;

                if(!CSRFSecurityHelper::verify(__METHOD__, $bankAccountForm)) {
                    NotificationHelper::set('bankAccount.add', 'danger', 'Le jeton CSRF n\'est pas valide');
                    header('Location: /bankAccount', true, 302);
                    exit;
                }

                if(!BankAccount::add($bankAccountForm)) {
                    $_SESSION['bankAccount'] = $bankAccountForm;

                    $this->logger->notice(
                        'Unable to save bank account', 
                        ['idUser' => $_SESSION['user']['idUser'] ?? null, 'bankAccount' => $bankAccountForm]
                    );

                    NotificationHelper::set('bankAccount.add', 'warning', 'Erreur lors de l\'ajoute du compte bancaire');
                    header('Location: /bankAccount');
                    exit;
                }

                $this->logger->info('Bank account saved', ['idUser' => $_SESSION['user']['idUser'] ?? null]);
                NotificationHelper::set('bankAccount.add', 'success', 'Compte bancaire ajouté');
                header('Location: /bankAccount');
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
                if(!empty($_SESSION['bankAccount'])) {
                    $this->view['bankAccount'] = $_SESSION['bankAccount'];
                    $this->logger->debug(
                        'Restoring model from saved state', 
                        ['idUser' => $_SESSION['user']['idUser'] ?? null, 'bankAcocunt' => $this->view['bankAccount']]
                    );
                    unset($_SESSION['bankAccount']);
                } else {
                    $this->logger->info(
                        'Filtering on bank acocunt.', 
                        ['idUser' => $_SESSION['user']['idUser'] ?? null, 'idBankAccount' => $_GET['idBankAccount']]
                    );
                    $bankAccountId = $_GET['idBankAccount'];
                    $this->view['bankAccount'] = BankAccount::find($bankAccountId);
                }

                $this->view['availableOwners'] = User::getAll();

                $this->view += CSRFSecurityHelper::createAndFlush(__METHOD__);
                $this->view['debug']['session'] = $_SESSION;
                $this->view += NotificationHelper::flush();

                View::renderTemplate('BankAccount/edit.html.twig', $this->view);
                break;

            case 'POST':
                $bankAccountForm = $_POST;

                if(!CSRFSecurityHelper::verify(__METHOD__, $bankAccountForm)) {
                    NotificationHelper::set('bankAccount.edit', 'danger', 'Le jeton CSRF n\'est pas valide');
                    header('Location: /bankAccount', true, 302);
                    exit;
                }

                if(!BankAccount::update($bankAccountForm)) {
                    $_SESSION['bankAccount'] = $bankAccountForm;
                    
                    $this->logger->notice(
                        'Unable to update bank account', 
                        ['idUser' => $_SESSION['user']['idUser'] ?? null, 'bankAccount' => $bankAccountForm]
                    );

                    NotificationHelper::set('bankAccount.edit', 'warning', 'Erreur lors de la sauvegarde du compte bancaire');
                    header('Location: /bankAccount/edit?idBankcAccount=' . $bankAccountForm['idBankAccount']);
                    exit;
                }

                $this->logger->info('Bank account updated', ['idUser' => $_SESSION['user']['idUser'] ?? null]);
                NotificationHelper::set('bankAccount.edit', 'success', 'Compte bancaire sauvegardé');
                header('Location: /bankAccount');
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

    public function removeAction()
    {
        $this->logger->info(
            'Begining handle `' . $_SERVER['REQUEST_METHOD'] . '` request on `' . __FUNCTION__ . '`', 
            ['idUser' => $_SESSION['user']['idUser'] ?? null]
        );
        
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $this->logger->info(
                    'Filtering on bank account.', 
                    ['idUser' => $_SESSION['user']['idUser'] ?? null, 'idBankAccount' => $_GET['idBankAccount']]
                );
                $bankAccountId = $_GET['idBankAccount'];
                $this->view['bankAccount'] = BankAccount::find($bankAccountId);

                $this->view += CSRFSecurityHelper::createAndFlush(__METHOD__);
                $this->view['debug']['session'] = $_SESSION;
                $this->view += NotificationHelper::flush();

                View::renderTemplate('BankAccount/remove.html.twig', $this->view);
                break;

            case 'POST':
                $bankAccountForm = $_POST;

                if(!CSRFSecurityHelper::verify(__METHOD__, $bankAccountForm)) {
                    NotificationHelper::set('bankAccount.remove', 'danger', 'Le jeton CSRF n\'est pas valide');
                    header('Location: /bankAccount', true, 302);
                    exit;
                }

                if(!BankAccount::remove($bankAccountForm)) {
                    $_SESSION['bankAccount'] = $bankAccountForm;

                    $this->logger->notice(
                        'Unable to remove bank account', 
                        ['idUser' => $_SESSION['user']['idUser'] ?? null, 'bankAccount' => $bankAccountForm]
                    );

                    NotificationHelper::set('bankAccount.remove', 'warning', 'Erreur lors de la suppression du compte bancaire');
                    header('Location: /bankAccount');
                    exit;
                }
                
                $this->logger->info('Bank account removed', ['idUser' => $_SESSION['user']['idUser'] ?? null]);
                NotificationHelper::set('bankAccount.remove', 'success', 'Le compte bancaire a été supprimé');
                header('Location: /bankAccount');
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