<?php

namespace App\Libs;

class SessionSecurityHandler
{
    protected string $sessionKey;
    protected string $secureKey;

    public function __construct()
    {
        $this->sessionKey = session_name();
        $this->secureKey = 'SECURE' . session_name();
    }

    public function startSession()
    {
        if(session_status() == PHP_SESSION_NONE){
            session_start([
                'cookie_httponly' => true,
                'cookie_samesite' => 'strict',
                'use_cookies' => true,
                'use_only_cookies' => true,
                'use_strict_mode' => true,
                'use_trans_sid' => false
            ]);
        }

        if(!isset($_COOKIE[$this->sessionKey], $_COOKIE[$this->secureKey]))
            $this->setSecureCookie();
    }

    public function regenerateSession()
    {
        session_regenerate_id(true);
        $this->setSecureCookie();
    }

    public function destroySession()
    {
        if(session_status() == PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        if(isset($_COOKIE[$this->secureKey])) {
            unset($_COOKIE[$this->secureKey]);
            $this->unsetSecureCookie();
        }
    }

    public function verifySession() : bool
    {
        if(isset($_COOKIE[$this->sessionKey])) {
            if(empty($_COOKIE[$this->secureKey])) {
                $this->destroySession();
                $this->startSession();
                return false;
            }

            $expectedSecureValue = $this->computeSecureValue();

            if($_COOKIE[$this->secureKey] !== $expectedSecureValue) {
                $this->destroySession();
                $this->startSession();
                return false;
            }
        }

        return true;
    }

    private function setSecureCookie()
    {
        $secureValue = $this->computeSecureValue();

        $sessionCookieParams = session_get_cookie_params();
        setcookie(
            $this->secureKey,
            $secureValue,
            $sessionCookieParams['lifetime'],
            '/',
            $sessionCookieParams['domain'],
            $sessionCookieParams['secure'],
            $sessionCookieParams['httponly']
        );
    }

    private function unsetSecureCookie()
    {
        $sessionCookieParams = session_get_cookie_params();
        setcookie(
            $this->secureKey,
            '',
            -1,
            '/',
            $sessionCookieParams['domain'],
            $sessionCookieParams['secure'],
            $sessionCookieParams['httponly']
        );
    }

    private function computeSecureValue()
    {
        $secureValue = md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);

        return $secureValue;
    }
}