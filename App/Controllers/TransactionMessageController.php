<?php

namespace App\Controllers;

use App\Helpers\CSRFSecurityHelper;
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
        $this->logger->info(
            'Begining handle `' . $_SERVER['REQUEST_METHOD'] . '` request on `' . __FUNCTION__ . '`', 
            ['idUser' => $_SESSION['user']['idUser'] ?? null]
        );
        
        if(!empty($_GET['idTransaction'])) {
            // $this->logger->info('Filtering on transaction.', ['idUser' => $_SESSION['user']['idUser'] ?? null, 'idTransaction' => $_GET['idTransaction']]);
            $this->view['transactionMessages'] = TransactionMessage::findByIdTransaction($_GET['idTransaction']);
        } else { 
            $this->logger->info('Get all results', ['idUser' => $_SESSION['user']['idUser'] ?? null]);
            $this->view['transactionMessages'] = TransactionMessage::getAll();
        }
        
        $this->logger->debug(
            'Got ' . count($this->view['transactionMessages']) . ' results :', 
            ['idUser' => $_SESSION['user']['idUser'] ?? null, 'transactionMessages' => $this->view['transactionMessages']]
        );
        
        CSRFSecurityHelper::clear();
        $this->view['debug']['session'] = $_SESSION;
        $this->view += NotificationHelper::flush();

        View::renderTemplate('TransactionMessage/index.html.twig', $this->view);
    }

    public function detailsAction()
    {
        $this->logger->info(
            'Begining handle `' . $_SERVER['REQUEST_METHOD'] . '` request on `' . __FUNCTION__ . '`', 
            ['idUser' => $_SESSION['user']['idUser'] ?? null]
        );

        $this->view['transactionMessage'] = TransactionMessage::find($_GET['idTransactionMessage']);

         // Manual implementation of sanitization
        /*
        this prevent img tag to work.
        Disable it and use blacktaglist filter in html.twig view file.
        */
        //$this->view['transactionMessage']['content'] = HtmlSanitizer::sanitizeValue($this->view['transactionMessage']['content']);

        CSRFSecurityHelper::clear();
        $this->view['debug']['session'] = $_SESSION;
        $this->view += NotificationHelper::flush();

        View::renderTemplate('TransactionMessage/details.html.twig', $this->view);
    }

    public function addAction()
    {
        $this->logger->info(
            'Begining handle `' . $_SERVER['REQUEST_METHOD'] . '` request on `' . __FUNCTION__ . '`', 
            ['idUser' => $_SESSION['user']['idUser'] ?? null]
        );
        
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                if(!empty($_SESSION['transactionMessage'])) {
                    $this->view['transactionMessage'] = $_SESSION['transactionMessage'];
                    $this->logger->debug(
                        'Restoring model from saved state', 
                        ['idUser' => $_SESSION['user']['idUser'] ?? null, 'transactionMessage' => $this->view['transactionMessage']]
                    );
                    unset($_SESSION['transactionMessage']);
                } else {
                    $this->view['transactionMessage'] = [];
                }

                $this->view['availableAuthors'] = User::getAll();
                $this->view['availableTransactions'] = Financialtransaction::getAll();
                    
                $this->view += CSRFSecurityHelper::createAndFlush(__METHOD__);
                $this->view['view']['debug'] = $_SESSION;
                $this->view += NotificationHelper::flush();

                View::renderTemplate('TransactionMessage/add.html.twig', $this->view);
                break;

            case 'POST':
                $transactionMessageForm = $_POST;

                if(!CSRFSecurityHelper::verify(__METHOD__, $transactionMessageForm)) {
                    NotificationHelper::set('transactionMessage.add', 'danger', 'Le jeton CSRF n\'est pas valide');
                    header('Location: /transactionMessage', true, 302);
                    exit;
                }

                if(!TransactionMessage::add($transactionMessageForm)) {
                    $_SESSION['transactionMessage'] = $transactionMessageForm;

                    $this->logger->notice(
                        'Unable to save transaction message', 
                        ['idUser' => $_SESSION['user']['idUser'] ?? null, 'transactionMessage' => $transactionMessageForm]
                    );

                    NotificationHelper::set('transactionMessage.add', 'warning', 'Erreur lors de l\'ajout du message de transatcion');
                    header('Location: /transactionMessage/add');
                    exit;
                }

                $this->logger->info('Transaction message saved', ['idUser' => $_SESSION['user']['idUser'] ?? null]);
                NotificationHelper::set('transactionMessage.add', 'success', 'Message de transatcion ajouté');
                header('Location: /transactionMessage');
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
                if(!empty($_SESSION['transactionMessage'])) {
                    $this->view['transactionMessage'] = $_SESSION['transactionMessage'];
                    $this->logger->debug(
                        'Restoring model from saved state', 
                        ['idUser' => $_SESSION['user']['idUser'] ?? null, 'transactionMessage' => $this->view['transactionMessage']]
                    );
                    unset($_SESSION['transactionMessage']);
                } else {
                    $this->logger->info(
                        'Filtering on transaction message.', 
                        ['idUser' => $_SESSION['user']['idUser'] ?? null, 'idTransactionMessage' => $_GET['idTransactionMessage']]
                    );
                    $transactionMessageId = $_GET['idTransactionMessage'];
                    $this->view['transactionMessage'] = TransactionMessage::find($transactionMessageId);
                }

                $this->view['availableAuthors'] = User::getAll();
                $this->view['availableTransactions'] = Financialtransaction::getAll();

                $this->view += CSRFSecurityHelper::createAndFlush(__METHOD__);
                $this->view['view']['debug'] = $_SESSION;
                $this->view += NotificationHelper::flush();

                View::renderTemplate('TransactionMessage/edit.html.twig', $this->view);
                break;

            case 'POST':
                $transactionMessageForm = $_POST;

                if(!CSRFSecurityHelper::verify(__METHOD__, $transactionMessageForm)) {
                    NotificationHelper::set('transactionMessage.edit', 'danger', 'Le jeton CSRF n\'est pas valide');
                    header('Location: /transactionMessage', true, 302);
                    exit;
                }

                if(!TransactionMessage::update($transactionMessageForm)) {
                    $_SESSION['transactionMessage'] = $transactionMessageForm;

                    $this->logger->notice(
                        'Unable to update transaction message', 
                        ['idUser' => $_SESSION['user']['idUser'] ?? null, 'transactionMessage' => $transactionMessageForm]
                    );

                    NotificationHelper::set('transactionMessage.edit', 'warning', 'Erreur lors de la sauvegarde du message de transatcion');
                    header('Location: /transactionMessage/edit?idTransactionMessage=' . $transactionMessageForm['idTransactionMessage']);
                    exit;
                }

                $this->logger->info('Transaction message updated', ['idUser' => $_SESSION['user']['idUser'] ?? null]);
                NotificationHelper::set('transactionMessage.add', 'success', 'Message de transatcion ajouté');
                header('Location: /transactionMessage');
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
                if(!empty($_SESSION['transactionMessage'])) {
                    $this->view['transactionMessage'] = $_SESSION['transactionMessage'];
                    $this->logger->debug(
                        'Restoring model from saved state', 
                        ['idUser' => $_SESSION['user']['idUser'] ?? null, 'transactionMessage' => $this->view['transactionMessage']]
                    );
                    unset($_SESSION['transactionMessage']);
                } else {
                    $this->logger->info(
                        'Filtering on transaction message.', 
                        ['idUser' => $_SESSION['user']['idUser'] ?? null, 'idTransactionMessage' => $_GET['idTransactionMessage']]
                    );
                    $this->view['transactionMessage'] = TransactionMessage::find($_GET['idTransactionMessage']);
                }

                $this->view += CSRFSecurityHelper::createAndFlush(__METHOD__);
                $this->view['debug']['session'] = $_SESSION;
                $this->view += NotificationHelper::flush();

                View::renderTemplate('TransactionMessage/remove.html.twig', $this->view);
                break;

            case 'POST':
                $transactionMessageForm = $_POST;

                if(!CSRFSecurityHelper::verify(__METHOD__, $transactionMessageForm)) {
                    NotificationHelper::set('transactionMessage.remove', 'danger', 'Le jeton CSRF n\'est pas valide');
                    header('Location: /transactionMessage', true, 302);
                    exit;
                }

                if(!TransactionMessage::remove($transactionMessageForm)) {
                    $_SESSION['transactionMessage'] = $transactionMessageForm;

                    $this->logger->notice(
                        'Unable to remove transaction message', 
                        ['idUser' => $_SESSION['user']['idUser'] ?? null, 'transactionMessage' => $transactionMessageForm]
                    );

                    NotificationHelper::set('transactionMessage.remove', 'warning', 'Erreur lors de la suppression du message de transaction');
                    header('Location: /transactionMessage');
                    exit;
                }

                $this->logger->info('Transaction message removed', ['idUser' => $_SESSION['user']['idUser'] ?? null]);
                NotificationHelper::set('transactionMessage.remove', 'success', 'Message de transaction supprimé');
                header('Location: /transactionMessage');
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