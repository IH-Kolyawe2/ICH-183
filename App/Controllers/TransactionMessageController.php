<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\Financialtransaction;
use App\Models\TransactionMessage;
use App\Helpers\NotificationHelper;
use App\Libs\HtmlSanitizer;
use Core\View;

class TransactionMessageController extends \Core\Controller
{
    public function indexAction()
    {
        if(!empty($_GET['idTransaction']))
            $this->view['transactionMessages'] = TransactionMessage::findByIdTransaction($_GET['idTransaction']);
        else
            $this->view['transactionMessages'] = TransactionMessage::getAll();

        $this->view['debug']['session'] = $_SESSION;
        $this->view += NotificationHelper::flush();

        View::renderTemplate('TransactionMessage/index.html.twig', $this->view);
    }

    public function detailsAction()
    {
        $this->view['transactionMessage'] = TransactionMessage::find($_GET['idTransactionMessage']);

         // Manual implementation of sanitization
        /*
        this prevent img tag to work.
        Disable it and use blacktaglist filter in html.twig view file.
        */
        //$this->view['transactionMessage']['content'] = HtmlSanitizer::sanitizeValue($this->view['transactionMessage']['content']);

        $this->view['debug']['session'] = $_SESSION;
        $this->view += NotificationHelper::flush();

        View::renderTemplate('TransactionMessage/details.html.twig', $this->view);
    }

    public function addAction()
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                if(!empty($_SESSION['transactionMessage'])) {
                    $this->view['transactionMessage'] = $_SESSION['transactionMessage'];
                    unset($_SESSION['transactionMessage']);
                } else 
                    $this->view['transactionMessage'] = [];

                $this->view['availableAuthors'] = User::getAll();
                $this->view['availableTransactions'] = Financialtransaction::getAll();

                $this->view['view']['debug'] = $_SESSION;
                $this->view += NotificationHelper::flush();

                View::renderTemplate('TransactionMessage/add.html.twig', $this->view);
                break;

            case 'POST':
                $transactionMessageForm = $_POST;

                if(!TransactionMessage::add($transactionMessageForm)) {
                    $_SESSION['transactionMessage'] = $transactionMessageForm;

                    NotificationHelper::set('transactionMessage.add', 'warning', 'Erreur lors de l\'ajout du message de transatcion');
                    header('Location: /transactionMessage/add');
                    exit;
                }

                NotificationHelper::set('transactionMessage.add', 'success', 'Message de transatcion ajouté');
                header('Location: /transactionMessage');
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
                if(!empty($_SESSION['transactionMessage'])) {
                    $this->view['transactionMessage'] = $_SESSION['transactionMessage'];
                    unset($_SESSION['transactionMessage']);
                } else {
                    $transactionMessageId = $_GET['idTransactionMessage'];
                    $this->view['transactionMessage'] = TransactionMessage::find($transactionMessageId);
                }

                $this->view['availableAuthors'] = User::getAll();
                $this->view['availableTransactions'] = Financialtransaction::getAll();

                $this->view['view']['debug'] = $_SESSION;
                $this->view += NotificationHelper::flush();

                View::renderTemplate('TransactionMessage/edit.html.twig', $this->view);
                break;

            case 'POST':
                $transactionMessageForm = $_POST;

                if(!TransactionMessage::update($transactionMessageForm)) {
                    $_SESSION['transactionMessage'] = $transactionMessageForm;

                    NotificationHelper::set('transactionMessage.edit', 'warning', 'Erreur lors de l\'ajout du message de transatcion');
                    header('Location: /transactionMessage/edit?idTransactionMessage=' . $transactionMessageForm['idTransactionMessage']);
                    exit;
                }

                NotificationHelper::set('transactionMessage.add', 'success', 'Message de transatcion ajouté');
                header('Location: /transactionMessage');
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
                if(!empty($_SESSION['transactionMessage'])) {
                    $this->view['transactionMessage'] = $_SESSION['transactionMessage'];
                    unset($_SESSION['transactionMessage']);
                } else {
                    $this->view['transactionMessage'] = TransactionMessage::find($_GET['idTransactionMessage']);
                }

                $this->view['debug']['session'] = $_SESSION;
                $this->view += NotificationHelper::flush();

                View::renderTemplate('TransactionMessage/remove.html.twig', $this->view);
                break;

            case 'POST':
                $transactionMessageForm = $_POST;

                if(!TransactionMessage::remove($transactionMessageForm)) {
                    $_SESSION['transactionMessage'] = $transactionMessageForm;

                    NotificationHelper::set('transactionMessage.remove', 'warning', 'Erreur lors de la suppression du message de transaction');
                    header('Location: /transactionMessage');
                    exit;
                }

                NotificationHelper::set('transactionMessage.remove', 'success', 'Message de transaction supprimé');
                header('Location: /transactionMessage');
                exit;
                
            default:
                http_response_code(422);
                exit;
        }
    }
}