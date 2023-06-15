<?php

namespace App\Models;

use PDO;




class BankAccount extends \Core\Model
{
    protected const QUERY_SELECT = <<< SQL
        SELECT 
            `b`.`idBankAccount`,
            `b`.`description`,
            `b`.`idOwner`,
            IFNULL((SELECT SUM(`_f`.`amount`)
               FROM (
                   SELECT SUM(`_f`.`amount`) * -1 AS `amount` FROM `financialtransactions` AS `_f` WHERE `_f`.`deletedAt` IS NULL AND `_f`.`idSender` = `b`.`idBankAccount`
                   UNION
                   SELECT SUM(`_f`.`amount`) AS `amount` FROM `financialtransactions` AS `_f` WHERE `_f`.`deletedAt` IS NULL AND `_f`.`idRecipient` = `b`.`idBankAccount`
               ) AS `_f`
              ), 0) AS `balance`,
            `o`.`firstname` AS `owner.firstname`,
            `o`.`lastname` AS `owner.lastname`,
            `o`.`mailAddress` AS `owner.mailAddress`,
            `b`.`createdAt`, `b`.`updatedAt`,
            `b`.`deletedAt`
        FROM `BankAccounts` AS `b`
            LEFT JOIN `users` AS `o` ON `b`.`idOwner` = `o`.`idUser`
        SQL;

    /**
     * Get all the bank accounts as an associative array
     * 
     * @return array
     */
    public static function getAll()
    {
        $db = static::getDB();
        $stmt = $db
            ->prepare(self::QUERY_SELECT . <<< SQL
                WHERE `b`.`deletedAt` IS NULL
            SQL);

        $stmt->execute();
        $models = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $models;
    }

    public static function find(int $id)
    {
        $db = static::getDB();
        $stmt = $db
            ->prepare(self::QUERY_SELECT . <<< SQL
                WHERE `b`.`deletedAt` IS NULL
                AND `b`.`idBankAccount` = :idBankAccount
                LIMIT 1;
            SQL);
        
        $stmt->bindParam(':idBankAccount', $id, PDO::PARAM_INT);
        $stmt->execute();
        $model = $stmt->fetch(PDO::FETCH_ASSOC);

        return $model;
    }

    public static function findByIdOwner(int $idOwner)
    {
        $db = static::getDB();
        $stmt = $db
            ->prepare(self::QUERY_SELECT . <<< SQL
                WHERE `b`.`deletedAt` IS NULL
                AND `b`.`idOwner` = :idOwner
            SQL);

        $stmt->bindParam(':idOwner', $idOwner, PDO::PARAM_INT);
        $stmt->execute();
        $models = $stmt->fetch(PDO::FETCH_ASSOC);

        return $models;
    }

    public static function add($model): bool
    {
        $db = static::getDB();
        $stmt = $db
            ->prepare(<<< SQL
                INSERT INTO `BankAccounts`
                    (`description`, `idOwner`)
                VALUES
                    (:description, :idOwner);
            SQL);

        $stmt->bindParam(':description', $model['description'], PDO::PARAM_STR);
        $stmt->bindParam(':idOwner', $model['idOwner'], PDO::PARAM_INT);
        $success = $stmt->execute();

        return $success;
    }

    public static function update($model)
    {
        $db = static::getDB();
        $stmt = $db
            ->prepare(<<< SQL
                UPDATE `BankAccounts` SET
                    `description` = :description, 
                    `idOwner` = :idOwner,
                    `updatedAt` = CURRENT_TIMESTAMP
                WHERE `deletedAt` IS NULL
                AND `idBankAccount` = :idBankAccount
                LIMIT 1;
            SQL);

        $stmt->bindParam(':description', $model['description'], PDO::PARAM_STR);
        $stmt->bindParam(':idOwner', $model['idOwner'], PDO::PARAM_INT);
        $stmt->bindParam(':idBankAccount', $model['idBankAccount'], PDO::PARAM_INT);
        $success = $stmt->execute();

        return $success;
    }

    public static function remove($model)
    {
        $db = static::getDB();
        $stmt = $db
            ->prepare(<<< SQL
                UPDATE `BankAccounts` SET
                    `deletedAt` = CURRENT_TIMESTAMP
                WHERE `deletedAt` IS NULL
                AND `idBankAccount` = :idBankAccount
                LIMIT 1;
            SQL);

        $stmt->bindParam(':idBankAccount', $model['idBankAccount'], PDO::PARAM_INT);
        $success = $stmt->execute();

        return $success;
    }
}