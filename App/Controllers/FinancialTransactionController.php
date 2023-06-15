<?php

namespace App\Controllers;

use App\Models\FinancialTransaction;
use App\Helpers\NotificationHelper;
use App\Models\BankAccount;
use \Core\View;

class FinancialTransactionController extends \Core\Controller
{
    public function indexAction()
    {
        if(!empty($_GET['idSender']))
            $this->view['financialTransactions'] = FinancialTransaction::findByIdSender($_GET['idSender']);
        
        else if(!empty($_GET['idRecipient']))
            $this->view['financialTransactions'] = FinancialTransaction::findByIdRecipient($_GET['idRecipient']);
        
        else if(!empty($_GET['idTransaction']))
            //The request comes from the details page of a transaction message
            $this->view['financialTransactions'][0] = FinancialTransaction::find($_GET['idTransaction']);

        else
            $this->view['financialTransactions'] = FinancialTransaction::getAll();

        $this->view['debug']['session'] = $_SESSION;
        $this->view += NotificationHelper::flush();

        View::renderTemplate('FinancialTransaction/index.html.twig', $this->view);
    }

    public function detailsAction()
    {
        $this->view['financialTransaction'] = FinancialTransaction::find($_GET['idFinancialTransaction']);

        $this->view['debug']['session'] = $_SESSION;
        $this->view += NotificationHelper::flush();

        View::renderTemplate('FinancialTransaction/details.html.twig', $this->view);
    }

    public function addAction()
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                if(!empty($_SESSION['financialTransaction'])) {
                    $this->view['financialTransaction'] = $_SESSION['financialTransaction'];
                    unset($_SESSION['financialTransaction']);
                } else {
                    $this->view['financialTransaction'] = [];
                }

                $this->view['availableSenders'] = BankAccount::getAll();
                $this->view['availableRecipients'] = BankAccount::getAll();

                $this->view['debug']['session'] = $_SESSION;
                $this->view += NotificationHelper::flush();

                View::renderTemplate('FinancialTransaction/add.html.twig', $this->view);
                break;

            case 'POST':
                $financialTransactionForm = $_POST;

                if(!FinancialTransaction::add($financialTransactionForm)) {
                    $_SESSION['financialTransaction'] = $financialTransactionForm;

                    NotificationHelper::set('financialTransaction.add', 'warning', 'Erreur lors de l\'ajout de la transaction financière');
                    header('Location: /financialTransaction/add');
                    exit;
                }

                NotificationHelper::set('financialTransaction.add', 'success', 'Transaction financière ajoutée');
                header('Location: /financialTransaction');
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
                if(!empty($_SESSION['financialTransaction'])) {
                    $this->view['financialTransaction'] = $_SESSION['financialTransaction'];
                    unset($_SESSION['financialTransaction']);
                } else {
                    $financialTransactionId = $_GET['idFinancialTransaction'];
                    $this->view['financialTransaction'] = FinancialTransaction::find($financialTransactionId);
                }

                $this->view['availableSenders'] = BankAccount::getAll();
                $this->view['availableRecipients'] = BankAccount::getAll();

                $this->view['debug']['session'] = $_SESSION;
                $this->view += NotificationHelper::flush();

                View::renderTemplate('FinancialTransaction/edit.html.twig', $this->view);
                break;

            case 'POST':
                $financialTransactionForm = $_POST;

                if(!FinancialTransaction::update($financialTransactionForm)) {
                    $_SESSION['financialTransaction'] = $financialTransactionForm;
                    
                    NotificationHelper::set('financialTransaction.edit', 'warning', 'Erreur lors de la sauvegarde de la transaction financière');
                    header('Location: /financialTransaction/edit?idFinancialTransaction=' . $financialTransactionForm['idFinancialTransaction']);
                    exit;
                }

                NotificationHelper::set('financialTransaction.edit', 'success', 'Transaction financière sauvegardée');
                header('Location: /financialTransaction');
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
                $financialTransactionId = $_GET['idFinancialTransaction'];
                $this->view['financialTransaction'] = FinancialTransaction::find($financialTransactionId);

                $this->view['debug']['session'] = $_SESSION;
                $this->view += NotificationHelper::flush();

                View::renderTemplate('financialTransaction/remove.html.twig', $this->view);
                break;

            case 'POST':
                $financialTransactionForm = $_POST;

                if(!FinancialTransaction::remove($financialTransactionForm)) {
                    $_SESSION['financialTransaction'] = $financialTransactionForm;

                    NotificationHelper::set('financialTransaction.remove', 'warning', 'Erreur lors de la suppression de la transaction financière');
                    header('Location: /financialTransaction');
                    exit;
                }

                NotificationHelper::set('financialTransaction.remove', 'success', 'Transaction financière supprimée');
                header('Location: /financialTransaction');
                exit;

            default:
                http_response_code(422);
                exit;
        }
    }
}