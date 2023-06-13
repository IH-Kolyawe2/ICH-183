<?php

namespace Core;

use PDO;
use App\Config;

/**
 * Base model
 */
abstract class Model
{
    /**
     * Get the PDO database connection
     *
     * @return mixed
     */
    protected static function getDB()
    {
        static $db = null;

        if ($db === null) {
            $dsn = 'mysql:host=' . Config::DB_HOST . ';port=' . Config::DB_PORT . ';dbname=' . Config::DB_NAME . ';charset=' . Config::DB_CHARSET;
            $db = new PDO($dsn, Config::DB_USER, Config::DB_PASS);

            $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        }

        return $db;
    }
}
