<?php

namespace App\Controllers;

use App\Helpers\CSRFSecurityHelper;
use App\Models\FinancialTransaction;
use App\Helpers\NotificationHelper;
use App\Models\BankAccount;
use \Core\View;

class FinancialTransactionController extends \Core\Controller
{
    public function indexAction()
    {
        $this->logger->info(
            'Begining handle `' . $_SERVER['REQUEST_METHOD'] . '` request on `' . __FUNCTION__ . '`', 
            ['idUser' => $_SESSION['user']['idUser'] ?? null]
        );

        if(!empty($_GET['idSender'])) {
            $this->logger->info('Filtering on sender.', ['idUser' => $_SESSION['user']['idUser'] ?? null, 'idSender' => $_GET['idSender']]);
            $this->view['financialTransactions'] = FinancialTransaction::findByIdSender($_GET['idSender']);
        } else if(!empty($_GET['idRecipient'])) {
            $this->logger->info('Filtering on recipient.', ['idUser' => $_SESSION['user']['idUser'] ?? null, 'idRecipient' => $_GET['idRecipient']]);
            $this->view['financialTransactions'] = FinancialTransaction::findByIdRecipient($_GET['idRecipient']);
        } else if(!empty($_GET['idTransaction'])) {
            $this->logger->info('Filtering on financial transaction.', ['idUser' => $_SESSION['user']['idUser'] ?? null, 'idTransaction' => $_GET['idTransaction']]);
            //The request comes from the details page of a transaction message
            $this->view['financialTransactions'][0] = FinancialTransaction::find($_GET['idTransaction']);
        } else {
            $this->logger->info('Get all results', ['idUser' => $_SESSION['user']['idUser'] ?? null]);
            $this->view['financialTransactions'] = FinancialTransaction::getAll();
        }
        
        $this->logger->debug(
            'Got ' . count($this->view['financialTransactions']) . ' results :', 
            ['idUser' => $_SESSION['user']['idUser'] ?? null, 'financialTransactions' => $this->view['financialTransactions']]
        );

        CSRFSecurityHelper::clear();
        $this->view['debug']['session'] = $_SESSION;
        $this->view += NotificationHelper::flush();

        View::renderTemplate('FinancialTransaction/index.html.twig', $this->view);
    }

    public function detailsAction()
    {
        $this->logger->info(
            'Begining handle `' . $_SERVER['REQUEST_METHOD'] . '` request on `' . __FUNCTION__ . '`', 
            ['idUser' => $_SESSION['user']['idUser'] ?? null]
        );
        
        $this->view['financialTransaction'] = FinancialTransaction::find($_GET['idFinancialTransaction']);

        CSRFSecurityHelper::clear();
        $this->view['debug']['session'] = $_SESSION;
        $this->view += NotificationHelper::flush();

        View::renderTemplate('FinancialTransaction/details.html.twig', $this->view);
    }

    public function addAction()
    {
        $this->logger->info(
            'Begining handle `' . $_SERVER['REQUEST_METHOD'] . '` request on `' . __FUNCTION__ . '`', 
            ['idUser' => $_SESSION['user']['idUser'] ?? null]
        );
        
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                if(!empty($_SESSION['financialTransaction'])) {
                    $this->view['financialTransaction'] = $_SESSION['financialTransaction'];
                    $this->logger->debug(
                        'Restoring model from saved state', 
                        ['idUser' => $_SESSION['user']['idUser'] ?? null, 'financialTransaction' => $this->view['financialTransaction']]
                    );
                    unset($_SESSION['financialTransaction']);
                } else {
                    $this->view['financialTransaction'] = [];
                }

                $this->view['availableSenders'] = BankAccount::getAll();
                $this->view['availableRecipients'] = BankAccount::getAll();

                $this->view += CSRFSecurityHelper::createAndFlush(__METHOD__);
                $this->view['debug']['session'] = $_SESSION;
                $this->view += NotificationHelper::flush();

                View::renderTemplate('FinancialTransaction/add.html.twig', $this->view);
                break;

            case 'POST':
                $financialTransactionForm = $_POST;

                if(!CSRFSecurityHelper::verify(__METHOD__, $financialTransactionForm)) {
                    NotificationHelper::set('financialTransaction.add', 'danger', 'Le jeton CSRF n\'est pas valide');
                    header('Location: /financialTransaction', true, 302);
                    exit;
                }

                if(!FinancialTransaction::add($financialTransactionForm)) {
                    $_SESSION['financialTransaction'] = $financialTransactionForm;

                    $this->logger->notice(
                        'Unable to save financial transaction', 
                        ['idUser' => $_SESSION['user']['idUser'] ?? null, 'financialTransaction' => $financialTransactionForm]
                    );

                    NotificationHelper::set('financialTransaction.add', 'warning', 'Erreur lors de l\'ajout de la transaction financière');
                    header('Location: /financialTransaction/add');
                    exit;
                }

                $this->logger->info('Financial transaction saved', ['idUser' => $_SESSION['user']['idUser'] ?? null]);
                NotificationHelper::set('financialTransaction.add', 'success', 'Transaction financière ajoutée');
                header('Location: /financialTransaction');
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
                if(!empty($_SESSION['financialTransaction'])) {
                    $this->view['financialTransaction'] = $_SESSION['financialTransaction'];
                    $this->logger->debug(
                        'Restoring model from saved state', 
                        ['idUser' => $_SESSION['user']['idUser'] ?? null, 'financialTransaction' => $this->view['financialTransaction']]
                    );
                    unset($_SESSION['financialTransaction']);
                } else {
                    $this->logger->info(
                        'Filtering on financial transaction.', 
                        ['idUser' => $_SESSION['user']['idUser'] ?? null, 'idFinancialTransaction' => $_GET['idFinancialTransaction']]
                    );
                    $financialTransactionId = $_GET['idFinancialTransaction'];
                    $this->view['financialTransaction'] = FinancialTransaction::find($financialTransactionId);
                }

                $this->view['availableSenders'] = BankAccount::getAll();
                $this->view['availableRecipients'] = BankAccount::getAll();

                $this->view += CSRFSecurityHelper::createAndFlush(__METHOD__);
                $this->view['debug']['session'] = $_SESSION;
                $this->view += NotificationHelper::flush();

                View::renderTemplate('FinancialTransaction/edit.html.twig', $this->view);
                break;

            case 'POST':
                $financialTransactionForm = $_POST;

                if(!CSRFSecurityHelper::verify(__METHOD__, $financialTransactionForm)) {
                    NotificationHelper::set('financialTransaction.edit', 'danger', 'Le jeton CSRF n\'est pas valide');
                    header('Location: /financialTransaction', true, 302);
                    exit;
                }

                if(!FinancialTransaction::update($financialTransactionForm)) {
                    $_SESSION['financialTransaction'] = $financialTransactionForm;
                    
                    $this->logger->notice(
                        'Unable to update financial transaction', 
                        ['idUser' => $_SESSION['user']['idUser'] ?? null, 'financialTransaction' => $financialTransactionForm]
                    );

                    NotificationHelper::set('financialTransaction.edit', 'warning', 'Erreur lors de la sauvegarde de la transaction financière');
                    header('Location: /financialTransaction/edit?idFinancialTransaction=' . $financialTransactionForm['idFinancialTransaction']);
                    exit;
                }

                $this->logger->info('Financial transaction updated', ['idUser' => $_SESSION['user']['idUser'] ?? null]);
                NotificationHelper::set('financialTransaction.edit', 'success', 'Transaction financière sauvegardée');
                header('Location: /financialTransaction');
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
                    'Filtering on financial transaction.', 
                    ['idUser' => $_SESSION['user']['idUser'] ?? null, 'idFinancialTransaction' => $_GET['idFinancialTransaction']]
                );
                $financialTransactionId = $_GET['idFinancialTransaction'];
                $this->view['financialTransaction'] = FinancialTransaction::find($financialTransactionId);

                $this->view += CSRFSecurityHelper::createAndFlush(__METHOD__);
                $this->view['debug']['session'] = $_SESSION;
                $this->view += NotificationHelper::flush();

                View::renderTemplate('financialTransaction/remove.html.twig', $this->view);
                break;

            case 'POST':
                $financialTransactionForm = $_POST;

                if(!CSRFSecurityHelper::verify(__METHOD__, $financialTransactionForm)) {
                    NotificationHelper::set('financialTransaction.remove', 'danger', 'Le jeton CSRF n\'est pas valide');
                    header('Location: /financialTransaction', true, 302);
                    exit;
                }

                if(!FinancialTransaction::remove($financialTransactionForm)) {
                    $_SESSION['financialTransaction'] = $financialTransactionForm;

                    $this->logger->notice(
                        'Unable to remove financial transaction', 
                        ['idUser' => $_SESSION['user']['idUser'] ?? null, 'financialTransaction' => $financialTransactionForm]
                    );
                    
                    NotificationHelper::set('financialTransaction.remove', 'warning', 'Erreur lors de la suppression de la transaction financière');
                    header('Location: /financialTransaction');
                    exit;
                }

                $this->logger->info('Financial transaction removed', ['idUser' => $_SESSION['user']['idUser'] ?? null]);
                NotificationHelper::set('financialTransaction.remove', 'success', 'Transaction financière supprimée');
                header('Location: /financialTransaction');
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