<?php

namespace Core;

use PDO;
use App\Config;
use Exception;

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

    public static function validate(array &$model, array $fields = null): array
    {
        $fields ??= array_keys($model);

        $validate = function (string $name, array &$model, $key, $default, int $filter, array $additionalOptions = []) {
            $result = filter_var(
                trim($model[$key]), 
                $filter, 
                ['options' => [
                    'default' => new Exception('La valeur du champ `' . $name . '` est invalide.')]
                    + $additionalOptions
                ]
            );

            if ($result instanceof Exception) {
                $model[$key] = $default;
                throw $result;
            }

            $model[$key] = $result;
        };

        $definitions = [
            'idUser' => fn ($k) => $validate('ID', $model, $k, $model[$k], FILTER_VALIDATE_INT, ['min_range' => 1]),
            'firstname' => fn ($k) => $validate('prénom', $model, $k, $model[$k], FILTER_VALIDATE_REGEXP, ['regexp' => '/.+/i']),
            'lastname' => fn ($k) => $validate('nom', $model, $k, $model[$k], FILTER_VALIDATE_REGEXP, ['regexp' => '/.+/i']),
            'mailAddress' => fn ($k) => $validate('adresse email', $model, $k, $model[$k], FILTER_VALIDATE_EMAIL),
            'password' => fn ($k) => $validate('mot de passe encrypté', $model, $k, $model[$k], FILTER_VALIDATE_REGEXP, ['regexp' => '/^\$2[ayb]\$.{56}$/i']),
            'passwordPlaintext' => fn ($k) => $validate('mot de passe', $model, $k, $model[$k], FILTER_VALIDATE_REGEXP, ['regexp' => '/^\S*(?=\S{8,})(?=\S*[a-z]{1,})(?=\S*[A-Z]{1,})(?=\S*[\d]{1,})(?=\S*[\W]{1,})\S*$/']),
            'passwordPlaintextConfirm' => fn ($k) => $validate('confirmation de mot de passe', $model, $k, $model[$k], FILTER_VALIDATE_REGEXP, ['regexp' => '/^\S*(?=\S{8,})(?=\S*[a-z]{1,})(?=\S*[A-Z]{1,})(?=\S*[\d]{1,})(?=\S*[\W]{1,})\S*$/']),
        ];

        $messages = [];

        foreach (array_keys($model) as $key) {
            if (!(in_array($key, $fields))) {
                unset($model[$key]);
                continue;
            }

            if (array_key_exists($key, $definitions)) {
                try {
                    $definitions[$key]($key);
                } catch (Exception $e) {
                    $messages[] = $e->getMessage();
                }
            }
        }

        return $messages;
    }
}
