<?php

namespace App\Helpers;

use App\Libs\Logger;

class CSRFSecurityHelper
{
    private const KEY_BACKEND_KEY = '_csrf_key';
    private const TOKEN_BACKEND_KEY = '_csrf_token';

    private static \Monolog\Logger $logger;

    public static function getLogger(): \Monolog\Logger
    {
        return self::$logger ?? Logger::getInstance('CSRFSecurityHelper');
    }

    public static function create(string $key): string
    {
        self::getLogger()->info(
            'Creating CSRF token', 
            ['idUser' => $_SESSION['user']['idUser'] ?? null, 'key' => $key]
        );

        $csrfToken = self::generateToken();
        $_SESSION[self::KEY_BACKEND_KEY] = $key;
        $_SESSION[self::TOKEN_BACKEND_KEY] = $csrfToken;

        self::getLogger()->info(
            'CSRF token created', 
            ['idUser' => $_SESSION['user']['idUser'] ?? null, 'csrf' => $csrfToken]
        );

        return $csrfToken;
    }

    public static function get()
    {
        if(isset($_SESSION[self::TOKEN_BACKEND_KEY]))
            return $_SESSION[self::TOKEN_BACKEND_KEY];

        return false;
    }

    public static function clear()
    {
        self::getLogger()->info('Clearing CSRF token', ['idUser' => $_SESSION['user']['idUser'] ?? null]);

        unset($_SESSION[self::KEY_BACKEND_KEY]);
        unset($_SESSION[self::TOKEN_BACKEND_KEY]);
    }

    public static function createAndFlush(string $key): array
    {
        $csrfToken = self::create($key);

        return ['csrf' => [
            'key'=>self::TOKEN_BACKEND_KEY,
            'value'=> $csrfToken
        ]];
    }

    public static function verify(string $key, array $value): bool
    {
        self::getLogger()->info('Verifying CSRF token', ['idUser' => $_SESSION['user']['idUser'] ?? null, 'key' => $key, 'value' => $value]);

        $success = false;

        if(isset($_SESSION[self::KEY_BACKEND_KEY], $_SESSION[self::TOKEN_BACKEND_KEY], $value[self::TOKEN_BACKEND_KEY])) {
            $expectedKey = $_SESSION[self::KEY_BACKEND_KEY];
            $expectedToken = $_SESSION[self::TOKEN_BACKEND_KEY];
            self::clear();

            $success = $expectedKey === $key && $expectedToken === $value[self::TOKEN_BACKEND_KEY];
        }

        if ($success)
            self::getLogger()->info('CSRF token validated', ['idUser' => $_SESSION['user']['idUser'] ?? null, 'key' => $key, 'value' => $value]);
        else
            self::getLogger()->warning('Invalid CSRF token', ['idUser' => $_SESSION['user']['idUser'] ?? null, 'key' => $key, 'value' => $value]);

        return $success;
    }

    private static function generateToken()
    {
        $csrfBin = random_bytes(32);
        $csrfToken = bin2hex($csrfBin);

        return $csrfToken;
    }
}