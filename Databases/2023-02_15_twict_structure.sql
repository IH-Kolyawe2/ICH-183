-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : lun. 20 fév. 2023 à 09:40
-- Version du serveur : 8.0.32
-- Version de PHP : 8.1.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Base de données : `twict`
--
CREATE DATABASE IF NOT EXISTS `twict` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `twict`;

-- --------------------------------------------------------

--
-- Structure de la table `bankaccount`
--

DROP TABLE IF EXISTS `bankaccount`;
CREATE TABLE IF NOT EXISTS `bankaccount` (
  `idBankAccount` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `description` varchar(255) DEFAULT NULL,
  `idOwner` int UNSIGNED NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime DEFAULT NULL,
  `deletedAt` datetime DEFAULT NULL,
  PRIMARY KEY (`idBankAccount`),
  KEY `fk_BankAccount_Users_idx` (`idOwner`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `financialtransaction`
--

DROP TABLE IF EXISTS `financialtransaction`;
CREATE TABLE IF NOT EXISTS `financialtransaction` (
  `idFinancialTransaction` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `amount` double DEFAULT NULL,
  `idSender` int UNSIGNED NOT NULL,
  `idRecipient` int UNSIGNED NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime DEFAULT NULL,
  `deletedAt` datetime DEFAULT NULL,
  PRIMARY KEY (`idFinancialTransaction`),
  KEY `fk_FinancialTransaction_BankAccount1_idx` (`idSender`),
  KEY `fk_FinancialTransaction_BankAccount2_idx` (`idRecipient`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `transactionmessage`
--

DROP TABLE IF EXISTS `transactionmessage`;
CREATE TABLE IF NOT EXISTS `transactionmessage` (
  `idTransactionMessage` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `content` text NOT NULL,
  `idTransaction` int UNSIGNED NOT NULL,
  `idAuthor` int UNSIGNED NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime DEFAULT NULL,
  `deletedAt` datetime DEFAULT NULL,
  PRIMARY KEY (`idTransactionMessage`),
  KEY `fk_TransactionMessage_FinancialTransaction1_idx` (`idTransaction`),
  KEY `fk_TransactionMessage_Users1_idx` (`idAuthor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `idUser` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `firstname` varchar(255) DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `mailAddress` varchar(255) DEFAULT NULL,
  `password` varchar(64) DEFAULT NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime DEFAULT NULL,
  `deletedAt` datetime DEFAULT NULL,
  PRIMARY KEY (`idUser`),
  UNIQUE KEY `idUser_UNIQUE` (`idUser`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`idUser`, `firstname`, `lastname`, `mailAddress`, `password`, `createdAt`, `updatedAt`, `deletedAt`) VALUES
(16, 'Albert', 'Adam', 'albert.adam@twict.dev', '123456', '2023-02-15 00:00:00', '2023-02-15 11:50:22', NULL),
(20, 'Béatrice', 'Blanc', 'beatrice.blanc@twict.dev', '', '2023-02-15 00:00:00', '2023-02-15 11:04:14', NULL),
(21, 'Clément', 'Chevalier', 'clement.chevalier@twict.dev', NULL, '2023-02-15 00:00:00', '2023-02-15 00:00:00', NULL),
(22, 'Michael', 'Vogel', 'michael.vogel@twict.dev', '', '2023-02-15 11:01:02', NULL, '2023-02-15 11:25:47');

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `bankaccount`
--
ALTER TABLE `bankaccount`
  ADD CONSTRAINT `fk_BankAccount_Users` FOREIGN KEY (`idOwner`) REFERENCES `users` (`idUser`);

--
-- Contraintes pour la table `financialtransaction`
--
ALTER TABLE `financialtransaction`
  ADD CONSTRAINT `fk_FinancialTransaction_BankAccount1` FOREIGN KEY (`idSender`) REFERENCES `bankaccount` (`idBankAccount`),
  ADD CONSTRAINT `fk_FinancialTransaction_BankAccount2` FOREIGN KEY (`idRecipient`) REFERENCES `bankaccount` (`idBankAccount`);

--
-- Contraintes pour la table `transactionmessage`
--
ALTER TABLE `transactionmessage`
  ADD CONSTRAINT `fk_TransactionMessage_FinancialTransaction1` FOREIGN KEY (`idTransaction`) REFERENCES `financialtransaction` (`idFinancialTransaction`),
  ADD CONSTRAINT `fk_TransactionMessage_Users1` FOREIGN KEY (`idAuthor`) REFERENCES `users` (`idUser`);
COMMIT;
