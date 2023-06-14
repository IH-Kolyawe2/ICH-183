<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\User;
use \App\Helpers\NotificationHelper;

class AuthController extends \Core\Controller
{
    public function indexAction()
    {
        $this->view['debug']['session'] = $_SESSION;
        $this->view += NotificationHelper::flush();

        View::renderTemplate('Auth/index.html.twig', $this->view);
    }
}