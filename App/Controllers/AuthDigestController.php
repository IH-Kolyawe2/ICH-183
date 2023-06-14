<?php

namespace App\Controllers;

use \Core\View;
use \App\Helpers\NotificationHelper;
use App\Libs\HttpDigestAuthParser;
use App\Models\User;

class AuthDigestController extends \Core\Controller
{
    private const REALM = 'XiOXIvsHBuRMDBvMTF';

    public function loginAction()
    {
        if(empty($_SERVER['PHP_AUTH_DIGEST'])) {
            header('HTTP/1.1 401 Unauthorized');
            header('WWW-Authenticate: Digest realm="' . self::REALM . '",qop="auth",nonce="' . uniqid() . '",opaque="' . md5(self::REALM) . '"');

            View::renderTemplate('AuthDigest/login.html.twig');
            return;
        }

        $data = HttpDigestAuthParser::parse($_SERVER['PHP_AUTH_DIGEST']);

        if($data === false) {
            NotificationHelper::set('authDigest.login', 'danger', 'La méthode d\'authentification n\'est pas supportée.');
            header('Location: /auth');
            exit;
        }

        $user = User::findByMailAddress($data['username']);

        if($user == null) {
            NotificationHelper::set('authDigest.login', 'danger', 'Le nom d\'utilisateur est invalide');
            header('Location: /auth');
            exit;
        }

        $a1 = md5(implode(':', [$user['mailAddress'], self::REALM, $user['password']]));
        $a2 = md5(implode(':', [$_SERVER['REQUEST_METHOD'], $data['uri']]));
        $response = md5(implode(':', [$a1, $data['nonce'], $data['nc'], $data['cnonce'], $data['qop'], $a2]));

        if($data['response'] !== $response) {
            NotificationHelper::set('authDigest.login', 'danger', 'Le mot de passe est invalide');
            header('Location: /auth');
            exit;
        }

        session_regenerate_id();

        NotificationHelper::set('authDigest.login', 'success', 'Le processus de connexion a réussi');
        $_SESSION['user'] = $user;
        header('Location: /auth');
    }

    public function loginCancelAction()
    {
        NotificationHelper::set('authDigest.login', 'warning', 'Le processus de connexion a été annulé');
        header('Location: /auth');
    }


    public function logoutAction()
    {
        session_destroy();
        session_start();

        header('HTTP/1.1 401 Unauthorized');
        header('WWW-Authenticate: Digest realm="' . self::REALM . '",qop="auth",nonce="' . uniqid() . '",opaque="' . md5(self::REALM) . '"');

        View::renderTemplate('AuthDigest/logout.html.twig');
    }

    public function logoutCancelAction()
    {
        NotificationHelper::set('authDigest.logout', 'success', 'Le processus de déconnexion a réussi');
        header('Location: /auth');
    }
}