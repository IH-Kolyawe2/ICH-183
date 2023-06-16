<?php

namespace App\Libs;

use App\Config;
use Monolog\ErrorHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;

class Logger extends \Monolog\Logger
{
    private static $loggers = [];

    public function __construct($key = 'app', $config = null)
    {
        parent::__construct($key);

        if(empty($config)) {
            $config = [
                'logFile' => Config::LOG_FILE,
                'logLevel' => Config::LOG_LEVEL
            ];
        }

        $this->pushHandler(new RotatingFileHandler($config['logFile'], $config['logLevel']));
    }

    public static function getInstance($key = 'app', $config = null)
    {
        if(empty(self::$loggers[$key])) {
            self::$loggers[$key] = new Logger($key, $config);
        }

        return self::$loggers[$key];
    }
}