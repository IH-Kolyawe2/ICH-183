<?php

namespace App\Models;

use PDO;

class User extends \Core\Model
{
    public static function getAll()
    {
        $db = static::getDB();

        $models = $db
            ->query(<<< SQL
                SELECT `idUser`, `firstname`, `lastname`, `mailAddress`, `password`, `createdAt`, `updatedAt`, `deletedAt`
                FROM `users`
                WHERE `deletedAt` IS NULL;
                SQL)
            ->fetchAll();

        return $models;

        return $models;
    }

    public static function find(int $id)
    {
        $db = static::getDB();

        $stmt = $db
            ->prepare(<<< SQL
                SELECT `idUser`, `firstname`, `lastname`, `mailAddress`, `password`, `createdAt`, `updatedAt`, `deletedAt`
                FROM `users`
                WHERE `idUser` = :idUser
                LIMIT 1;
                SQL);

        $stmt->bindParam(':idUser', $id, PDO::PARAM_INT);
        $stmt->execute();
        $model = $stmt->fetch();

        return $model;
    }

    public static function findByMailAddress(string $mailAddress)
    {
        $db = static::getDB();
        $stmt = $db
            ->prepare(<<< SQL
                SELECT `idUser`, `firstname`, `lastname`, `mailAddress`, `password`, `createdAt`, `updatedAt`, `deletedAt`
                FROM `users`
                WHERE `mailAddress`= :mailAddress
                LIMIT 1;
                SQL);

        $stmt->bindParam(':mailAddress', $mailAddress, PDO::PARAM_STR);
        $stmt->execute();
        $model = $stmt->fetch();

        return $model;
    }

    public static function findByMailAddressAndPassword(string $mailAddress, string $password)
    {
        $db = static::getDB();
        $stmt = $db
            ->prepare(<<< SQL
                SELECT `idUser`, `firstname`, `lastname`, `mailAddress`, `password`, `createdAt`, `updatedAt`, `deletedAt`
                FROM `users`
                WHERE `mailAddress`= :mailAddress
                AND `password`= :password
                LIMIT 1;
                SQL);

        $stmt->bindParam(':mailAddress', $mailAddress, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);
        $stmt->execute();
        $model = $stmt->fetch();

        return $model;
    }

    public static function add($model): bool
    {
        $db = static::getDB();
        $stmt = $db
            ->prepare(<<< SQL
                INSERT INTO `users`
                    (`firstname`, `lastname`, `mailAddress`, `password`)
                VALUES
                    (:firstname, :lastname, :mailAddress, :password);
                SQL);

        $stmt->bindParam(':firstname', $model['firstname'], PDO::PARAM_STR);
        $stmt->bindParam(':lastname', $model['lastname'], PDO::PARAM_STR);
        $stmt->bindParam(':mailAddress', $model['mailAddress'], PDO::PARAM_STR);
        $stmt->bindParam(':password', $model['password'], PDO::PARAM_STR);
        $success = $stmt->execute();

        return $success;
    }

    public static function update($model)
    {
        $db = static::getDB();

        $stmt = $db
            ->prepare(<<< SQL
                UPDATE `users` SET
                    `firstname` = :firstname,
                    `lastname` = :lastname,
                    `mailAddress` = :mailAddress,
                    `updatedAt` = CURRENT_TIMESTAMP
                WHERE `idUser` = :idUser
                LIMIT 1;
                SQL);

        $stmt->bindParam(':idUser', $model['idUser'], PDO::PARAM_INT);
        $stmt->bindParam(':firstname', $model['firstname'], PDO::PARAM_STR);
        $stmt->bindParam(':lastname', $model['lastname'], PDO::PARAM_STR);
        $stmt->bindParam(':mailAddress', $model['mailAddress'], PDO::PARAM_STR);
        $success = $stmt->execute();

        return $success;
    }

    public static function updatePassword($model)
    {
        $db = static::getDB();
        $stmt = $db
            ->prepare(<<< SQL
                UPDATE `Users` SET
                    `password` = :password,
                    `updatedAt` = CURRENT_TIMESTAMP
                WHERE `deletedAt` IS NULL
                AND `idUser` = :idUser
                LIMIT 1;
            SQL);

        $stmt->bindParam(':idUser', $model['idUser'], PDO::PARAM_INT);
        $stmt->bindParam(':password', $model['password'], PDO::PARAM_STR);
        $success = $stmt->execute();

        return $success;
    }

    public static function remove($model)
    {
        $db = static::getDB();
        $stmt = $db
            ->prepare(<<< SQL
                UPDATE `Users` SET
                    `deletedAt` = CURRENT_TIMESTAMP
                WHERE `idUser` = :idUser
                LIMIT 1;
                SQL);

        $stmt->bindParam(':idUser', $model['idUser'], PDO::PARAM_INT);
        $success = $stmt->execute();

        return $success;
    }
}
