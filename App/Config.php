<?php

namespace App;

use App\Libs\Logger;

/**
 * Application configuration
 */
class Config
{
    const DB_HOST = 'localhost';
    const DB_PORT = '3306';
    const DB_NAME = 'twict';
    const DB_CHARSET = 'utf8';
    const DB_USER = 'root';
    const DB_PASS = '123456';

    const BCRYPT_COST = 7;

    const SHOW_ERRORS = true;

    const LOG_FILE = __DIR__ . '/../logs/twict.log';

    // https://github.com/Seldaek/monolog/blob/main/doc/01-usage.md#log-levels
    const LOG_LEVEL = Logger::INFO;
}
