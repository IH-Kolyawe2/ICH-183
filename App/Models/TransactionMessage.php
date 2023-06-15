<?php

namespace App\Models;

use PDO;

class TransactionMessage extends \Core\Model
{
    protected const QUERY_SELECT = <<< SQL
        SELECT
            `t`.`idTransactionMessage`,
            `t`.`content`,
            `t`.`idTransaction`,
            `f`.`amount` AS `transaction.amount`,
            `s`.`description` AS `transaction.sender.description`,
            `so`.`firstname` AS `transaction.sender.owner.firstname`,
            `so`.`lastname` AS `transaction.sender.owner.lastname`,
            `so`.`mailAddress` AS `transaction.sender.owner.mailAddress`,
            `r`.`description` AS `transaction.recipient.description`,
            `ro`.`firstname` AS `transaction.recipient.owner.firstname`,
            `ro`.`lastname` AS `transaction.recipient.owner.lastname`,
            `ro`.`mailAddress` AS `transaction.recipient.owner.mailAddress`,
            `t`.`idAuthor`,
            `a`.`firstname` AS `author.firstname`,
            `a`.`lastname` AS `author.lastname`,
            `a`.`mailAddress` AS `author.mailAddress`,
            `t`.`createdAt`,
            `t`.`updatedAt`,
            `t`.`deletedAt`
        FROM `TransactionMessages` AS `t`
            LEFT JOIN `financialTransactions` AS `f` ON `t`.`idTransaction` = `f`.`idFinancialTransaction`
            LEFT JOIN `users` AS `a` ON `t`.`idAuthor` = `a`.`idUser`
            LEFT JOIN `bankAccounts` AS `s` ON `f`.`idSender` = `s`.`idBankAccount`
            LEFT JOIN `users` AS `so` ON `s`.`idOwner` = `so`.`idUser`
            LEFT JOIN `bankAccounts` AS `r` ON `f`.`idRecipient` = `r`.`idBankAccount`
            LEFT JOIN `users` AS `ro` ON `r`.`idOwner` = `ro`.`idUser`
        SQL;

    /**
     * Get all transaction messages as an associative array
     * 
     * @return array
     */
    public static function getAll()
    {
        $db = static::getDB();
        $stmt = $db
            ->prepare(SELF::QUERY_SELECT . <<< SQL
                WHERE `t`.`deletedAt` IS NULL
            SQL);
        
        $stmt->execute();
        $models = $stmt->fetchall(PDO::FETCH_ASSOC);

        return $models;
    }

    public static function find(int $id)
    {
        $db = static::getDB();
        $stmt = $db
            ->prepare(SELF::QUERY_SELECT . <<< SQL
                WHERE `t`.`deletedAt` IS NULL
                AND `t`.`idTransactionMessage` = :idTransactionMessage
                LIMIT 1;
            SQL);
        
        $stmt->bindParam(':idTransactionMessage', $id, PDO::PARAM_INT);
        $stmt->execute();
        $model = $stmt->fetch(PDO::FETCH_ASSOC);

        return $model;
    }

    public static function findByIdTransaction(int $idTransaction)
    {
        $db = static::getDB();
        $stmt = $db
            ->prepare(self::QUERY_SELECT . <<< SQL
                WHERE `t`.`deletedAt` IS NULL
                AND `t`.`idTransaction`= :idTransaction;
            SQL);

        $stmt->bindParam(':idTransaction', $idTransaction, PDO::PARAM_INT);
        $stmt->execute();
        $models = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $models;
    }

    public static function add($model): bool
    {
        $db = static::getDB();
        $stmt = $db
            ->prepare(<<< SQL
                INSERT INTO `TransactionMessages` 
                    (`content`, `idTransaction`, `idAuthor`) 
                VALUES
                    (:content, :idTransaction, :idAuthor);
            SQL);

        $stmt->bindParam(':content', $model['content'], PDO::PARAM_STR);
        $stmt->bindParam(':idTransaction', $model['idTransaction'], PDO::PARAM_INT);
        $stmt->bindParam(':idAuthor', $model['idAuthor'], PDO::PARAM_INT);
        $success = $stmt->execute();

        return $success;
    }

    public static function update($model)
    {
        $db = static::getDB();
        $stmt = $db
            ->prepare(<<< SQL
                UPDATE `TransactionMessages` SET
                    `content` = :content
                    , `idTransaction` = :idTransaction
                    , `idAuthor` =:idAuthor
                    , `updatedAt` = CURRENT_TIMESTAMP
                WHERE `deletedAt` IS NULL
                AND `idTransactionMessage` = :idTransactionMessage
                LIMIT 1;
            SQL);

        $stmt->bindParam(':content', $model['content'], PDO::PARAM_STR);
        $stmt->bindParam(':idTransaction', $model['idTransaction'], PDO::PARAM_INT);
        $stmt->bindParam(':idAuthor', $model['idAuthor'], PDO::PARAM_INT);
        $stmt->bindParam(':idTransactionMessage', $model['idTransactionMessage'], PDO::PARAM_INT);
        $success = $stmt->execute();

        return $success;
    }

    public static function remove($model)
    {
        $db = static::getDB();
        $stmt = $db
            ->prepare(<<< SQL
                UPDATE `TransactionMessages` SET
                    `deletedAt` = CURRENT_TIMESTAMP
                WHERE `deletedAt` IS NULL
                AND `idTransactionMessage` = :idTransactionMessage
                LIMIT 1;
            SQL);

        $stmt->bindParam(':idTransactionMessage', $model['idTransactionMessage'], PDO::PARAM_INT);
        $success = $stmt->execute();

        return $success;
    }
}