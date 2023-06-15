<?php

namespace App\Helpers;

class CSRFSecurityHelper
{
    private const KEY_BACKEND_KEY = '_csrf_key';
    private const TOKEN_BACKEND_KEY = '_csrf_token';

    public static function create(string $key): string
    {
        $csrfToken = self::generateToken();
        $_SESSION[self::KEY_BACKEND_KEY] = $key;
        $_SESSION[self::TOKEN_BACKEND_KEY] = $csrfToken;

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

    public static function verify(string $key, array $values): bool
    {
        if(isset($_SESSION[self::KEY_BACKEND_KEY], $_SESSION[self::TOKEN_BACKEND_KEY], $values[self::TOKEN_BACKEND_KEY])) {
            $expectedKey = $_SESSION[self::KEY_BACKEND_KEY];
            $expectedToken = $_SESSION[self::TOKEN_BACKEND_KEY];
            self::clear();

            return $expectedKey === $key && $expectedToken === $values[self::TOKEN_BACKEND_KEY];
        }

        return false;
    }

    private static function generateToken()
    {
        $csrfBin = random_bytes(32);
        $csrfToken = bin2hex($csrfBin);

        return $csrfToken;
    }
}