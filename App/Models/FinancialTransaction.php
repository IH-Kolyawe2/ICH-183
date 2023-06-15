<?php

namespace App\Models;

use PDO;

class Financialtransaction extends \Core\Model
{
    protected const QUERY_SELECT = <<< SQL
        SELECT
            `f`.`idFinancialTransaction`,
            `f`.`amount`,
            `f`.`idSender`,
            `s`.`description` AS `sender.description`,
            `so`.`firstname` AS `sender.owner.firstname`,
            `so`.`lastname` AS `sender.owner.lastname`,
            `so`.`mailAddress` AS `sender.owner.mailAddress`,
            `f`.`idRecipient`,
            `r`.`description` AS `recipient.description`,
            `ro`.`firstname` AS `recipient.owner.firstname`,
            `ro`.`lastname` AS `recipient.owner.lastname`,
            `ro`.`mailAddress` AS `recipient.owner.mailAddress`,
            `f`.`createdAt`,
            `f`.`updatedAt`,
            `f`.`deletedAt`
        FROM `FinancialTransactions` AS `f`
            LEFT JOIN `bankAccounts` AS `s` ON `f`.`idSender` = `s`.`idBankAccount`
            LEFT JOIN `users` AS `so` ON `s`.`idOwner` = `so`.`idUser`
            LEFT JOIN `bankAccounts` AS `r` ON `f`.`idRecipient` = `r`.`idBankAccount`
            LEFT JOIN `users` AS `ro` ON `r`.`idOwner` = `ro`.`idUser`
        SQL;

    /**
     * Get all the financial transactions as an associative array
     * 
     * @return array
     */
    public static function getAll()
    {
        $db = static::getDB();
        $stmt = $db
            ->prepare(self::QUERY_SELECT . <<< SQL
                WHERE `f`.`deletedAt` IS NULL
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
                WHERE `f`.`deletedAt` IS NULL
                AND `f`.`idFinancialTransaction` = :idFinancialTransaction
                LIMIT 1;
            SQL);

        $stmt->bindParam(':idFinancialTransaction', $id, PDO::PARAM_INT);
        $stmt->execute();
        $model = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $model;
    }

    public static function findByIdSender(int $idSender)
    {
        $db = static::getDB();
        $stmt = $db
            ->prepare(self::QUERY_SELECT . <<< SQL
                WHERE `f`.`deletedAt` IS NULL
                AND `f`.`idSender` = :idSender
            SQL);

        $stmt->bindParam(':idSender', $idSender, PDO::PARAM_INT);
        $stmt->execute();
        $models = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $models;
    }

    public static function findByIdRecipient(int $idRecipient)
    {
        $db = static::getDB();
        $stmt = $db
            ->prepare(self::QUERY_SELECT . <<< SQL
                WHERE `f`.`deletedAt` IS NULL
                AND `f`.`idRecipient` = :idRecipient
            SQL);

        $stmt->bindParam(':idRecipient', $idRecipient, PDO::PARAM_INT);
        $stmt->execute();
        $models = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $models;
    }

    public static function add($model): bool
    {
        $db = static::getDB();
        $stmt = $db
            ->prepare(<<< SQL
                INSERT INTO `FinancialTransactions`
                    (`amount`, `idSender`, `idRecipient`)
                VALUES
                    (:amount, :idSender, :idRecipient);
            SQL);

        $stmt->bindParam(':amount', $model['amount'], PDO::PARAM_STR);
        $stmt->bindParam(':idSender', $model['idSender'], PDO::PARAM_INT);
        $stmt->bindParam(':idRecipient', $model['idRecipient'], PDO::PARAM_INT);
        $success = $stmt->execute();

        return $success;
    }

    public static function update($model)
    {
        $db = static::getDB();
        $stmt = $db
            ->prepare(<<< SQL
                UPDATE `FinancialTransactions` SET
                    `amount` = :amount,
                    `idSender` = :idSender,
                    `idRecipient` = :idRecipient,
                    `updatedAt` = CURRENT_TIMESTAMP
                WHERE `deletedAt` IS NULL
                AND `idFinancialTransaction` = :idFinancialTransaction
                LIMIT 1
            SQL);

        $stmt->bindParam(':amount', $model['amount'], PDO::PARAM_STR);
        $stmt->bindParam(':idSender', $model['idSender'], PDO::PARAM_INT);
        $stmt->bindParam(':idRecipient', $model['idRecipient'], PDO::PARAM_INT);
        $stmt->bindParam(':idFinancialTransaction', $model['idFinancialTransaction'], PDO::PARAM_INT);
        $success = $stmt->execute();

        return $success;
    }

    public static function remove($model)
    {
        $db = static::getDB();
        $stmt = $db
            ->prepare(<<< SQL
                UPDATE `FinancialTransactions` SET
                    `deletedAt` = CURRENT_TIMESTAMP
                WHERE `deletedAt` IS NULL
                AND `idFinancialTransaction` = :idFinancialTransaction
                LIMIT 1
            SQL);

        $stmt->bindParam(':idFinancialTransaction', $model['idFinancialTransaction'], PDO::PARAM_INT);
        $success = $stmt->execute();

        return $success;
    }
}