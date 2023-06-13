<?php

namespace App\Helpers;

class NotificationHelper
{
    private const BACKEND_KEY = 'notifications';

    public static function set(string $key, string $type, string $content)
    {
        self::ensureBackendCreated();

        $_SESSION[self::BACKEND_KEY][$key] = ['type' => $type, 'content' => $content];
    }

    public static function remove(string $key)
    {
        self::ensureBackendCreated();

        unset($_SESSION[self::BACKEND_KEY][$key]);
    }

    public static function get(): array
    {
        self::ensureBackendCreated();

        return $_SESSION[self::BACKEND_KEY];
    }

    public static function clear()
    {
        self::ensureBackendCreated();

        unset($_SESSION[self::BACKEND_KEY]);
    }

    public static function flush()
    {
        $notifications = self::get();

        self::clear();

        return [self::BACKEND_KEY => $notifications];
    }

    protected static function ensureBackendCreated()
    {
        if (empty($_SESSION[self::BACKEND_KEY]))
            $_SESSION[self::BACKEND_KEY] = [];
    }
}
