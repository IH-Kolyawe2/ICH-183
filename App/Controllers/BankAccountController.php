<?php

namespace App\Controllers;

use App\Models\BankAccount;
use App\Helpers\NotificationHelper;
use App\Models\User;
use \Core\View;

class BankAccountController extends \Core\Controller
{
    public function indexAction()
    {
        $this->view['bankAccounts'] = !empty($_GET['idOwner'])
            ? BankAccount::findByIdOwner($_GET['idOwner'])
            : BankAccount::getAll();

        $this->view['debug']['session'] = $_SESSION;
        $this->view += NotificationHelper::flush();

        View::renderTemplate('BankAccount/index.html.twig', $this->view);
    }

    public function detailsAction()
    {
        $this->view['bankAccount'] = BankAccount::find($_GET['idBankAccount']);

        $this->view['debug']['session'] = $_SESSION;
        $this->view += NotificationHelper::flush();

        View::renderTemplate('BankAccount/details.html.twig', $this->view);
    }

    public function addAction()
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                if(!empty($_SESSION['bankAccount'])) {
                    $this->view['bankAccount'] = $_SESSION['bankAccount'];
                    unset($_SESSION['bankAccount']);
                } else {
                    $this->view['bankAccount'] = [];
                }

                $this->view['availableOwners'] = User::getAll();

                $this->view['debug']['session'] = $_SESSION;
                $this->view += NotificationHelper::flush();

                View::renderTemplate('BankAccount/add.html.twig', $this->view);
                break;

            case 'POST':
                $bankAccountForm = $_POST;

                if(!BankAccount::add($bankAccountForm)) {
                    $_SESSION['bankAccount'] = $bankAccountForm;

                    NotificationHelper::set('bankAccount.add', 'warning', 'Erreur lors de l\'ajoute du compte bancaire');
                    header('Location: /bankAccount');
                    exit;
                }

                NotificationHelper::set('bankAccount.add', 'success', 'Compte bancaire ajouté');
                header('Location: /bankAccount');
                exit;

            default:
                http_response_code(422);
                exit;
        }
    }

    public function editAction()
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                if(!empty($_SESSION['bankAccount'])) {
                    $this->view['bankAccount'] = $_SESSION['bankAccount'];
                } else {
                    $bankAccountId = $_GET['idBankAccount'];
                    $this->view['bankAccount'] = BankAccount::find($bankAccountId);
                }

                $this->view['availableOwners'] = User::getAll();

                $this->view['debug']['session'] = $_SESSION;
                $this->view += NotificationHelper::flush();

                View::renderTemplate('BankAccount/edit.html.twig', $this->view);
                break;

            case 'POST':
                $bankAccountForm = $_POST;

                if(!BankAccount::update($bankAccountForm)) {
                    $_SESSION['bankAccount'] = $bankAccountForm;

                    NotificationHelper::set('bankAccount.edit', 'warning', 'Erreur lors de la sauvegarde du compte bancaire');
                    header('Location: /bankAccount/edit?idBankcAccount=' . $bankAccountForm['idBankAccount']);
                    exit;
                }

                NotificationHelper::set('bankAccount.edit', 'success', 'Compte bancaire sauvegardé');
                header('Location: /bankAccount');
                exit;

            default:
                http_response_code(422);
                exit;
        }

    }

    public function removeAction()
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $bankAccountId = $_GET['idBankAccount'];
                $this->view['bankAccount'] = BankAccount::find($bankAccountId);

                $this->view['debug']['session'] = $_SESSION;
                $this->view += NotificationHelper::flush();

                View::renderTemplate('BankAccount/remove.html.twig', $this->view);
                break;

            case 'POST':
                $bankAccountForm = $_POST;

                if(!BankAccount::remove($bankAccountForm)) {
                    $_SESSION['bankAccount'] = $bankAccountForm;

                    NotificationHelper::set('bankAccount.remove', 'warning', 'Erreur lors de la suppression du compte bancaire');
                    header('Location: /bankAccount');
                    exit;
                }

                NotificationHelper::set('bankAccount.remove', 'success', 'Le compte bancaire a été supprimé');
                header('Location: /bankAccount');
                exit;

            default:
                http_response_code(422);
                exit;
        }
    }
}