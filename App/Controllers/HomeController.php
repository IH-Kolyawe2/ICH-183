<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\User;
use \App\Helpers\NotificationHelper;

class HomeController extends \Core\Controller
{
    public function indexAction()
    {
        $this->view['hello'] = 'Bienvenue sur la page d\'accueil';

        $this->view['debug']['session'] = $_SESSION;
        $this->view += NotificationHelper::flush();

        View::renderTemplate('Home/index.html.twig', $this->view);
    }
}
