-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 22, 2023 at 08:57 AM
-- Server version: 8.0.32
-- PHP Version: 8.1.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `twict`
--
DROP DATABASE IF EXISTS `twict`;
CREATE DATABASE IF NOT EXISTS `twict` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `twict`;

-- --------------------------------------------------------

--
-- Table structure for table `bankaccounts`
--

DROP TABLE IF EXISTS `bankaccounts`;
CREATE TABLE IF NOT EXISTS `bankaccounts` (
  `idBankAccount` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `description` varchar(255) DEFAULT NULL,
  `idOwner` int UNSIGNED NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime DEFAULT NULL,
  `deletedAt` datetime DEFAULT NULL,
  PRIMARY KEY (`idBankAccount`),
  KEY `fk_BankAccount_Users_idx` (`idOwner`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `bankaccounts`
--

INSERT INTO `bankaccounts` (`idBankAccount`, `description`, `idOwner`, `createdAt`, `updatedAt`, `deletedAt`) VALUES
(7, 'Compte privé', 22, '2021-03-28 15:29:36', '2021-04-17 10:05:16', NULL),
(9, 'Compte privé', 23, '2021-03-28 15:29:56', NULL, NULL),
(11, 'Compte privé', 25, '2021-04-17 10:05:30', NULL, NULL),
(12, 'Compte privé', 32, '2021-04-17 10:10:06', NULL, NULL),
(13, 'Compte privé', 36, '2021-04-17 10:51:05', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `financialtransactions`
--

DROP TABLE IF EXISTS `financialtransactions`;
CREATE TABLE IF NOT EXISTS `financialtransactions` (
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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `financialtransactions`
--

INSERT INTO `financialtransactions` (`idFinancialTransaction`, `amount`, `idSender`, `idRecipient`, `createdAt`, `updatedAt`, `deletedAt`) VALUES
(7, 30, 7, 9, '2021-03-28 15:31:20', NULL, NULL),
(9, 20, 7, 11, '2021-04-17 10:27:22', NULL, NULL),
(10, 10, 12, 7, '2021-04-17 10:34:29', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `transactionmessages`
--

DROP TABLE IF EXISTS `transactionmessages`;
CREATE TABLE IF NOT EXISTS `transactionmessages` (
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `transactionmessages`
--

INSERT INTO `transactionmessages` (`idTransactionMessage`, `content`, `idTransaction`, `idAuthor`, `createdAt`, `updatedAt`, `deletedAt`) VALUES
(3, 'Salut ! Merci pour ton coup de pouce !', 7, 23, '2021-03-28 15:38:05', '2021-04-17 11:01:50', NULL),
(5, 'Merci pour ton aide !', 7, 22, '2021-03-28 16:00:01', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
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
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`idUser`, `firstname`, `lastname`, `mailAddress`, `password`, `createdAt`, `updatedAt`, `deletedAt`) VALUES
(22, 'Louis', 'Pasteur', 'pasteur@gmail.com', '1234', '2021-02-07 14:03:06', NULL, NULL),
(23, 'Marie', 'Curie', 'curie@gmail.com', '1234', '2021-02-07 14:03:06', NULL, NULL),
(25, 'Victor', 'Hugo', 'hugo@gmail.com', '1234', '2021-02-07 14:03:06', NULL, NULL),
(32, 'Jules', 'Verne', 'verne@gmail.com', '1234', '2021-02-07 14:03:06', NULL, NULL),
(36, 'Edith', 'Piaf', 'piaf@gmail.com', '1234', '2021-02-07 14:03:06', NULL, NULL);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bankaccounts`
--
ALTER TABLE `bankaccounts`
  ADD CONSTRAINT `fk_BankAccount_Users` FOREIGN KEY (`idOwner`) REFERENCES `users` (`idUser`);

--
-- Constraints for table `financialtransactions`
--
ALTER TABLE `financialtransactions`
  ADD CONSTRAINT `fk_FinancialTransaction_BankAccount1` FOREIGN KEY (`idSender`) REFERENCES `bankaccounts` (`idBankAccount`),
  ADD CONSTRAINT `fk_FinancialTransaction_BankAccount2` FOREIGN KEY (`idRecipient`) REFERENCES `bankaccounts` (`idBankAccount`);

--
-- Constraints for table `transactionmessages`
--
ALTER TABLE `transactionmessages`
  ADD CONSTRAINT `fk_TransactionMessage_FinancialTransaction1` FOREIGN KEY (`idTransaction`) REFERENCES `financialtransactions` (`idFinancialTransaction`),
  ADD CONSTRAINT `fk_TransactionMessage_Users1` FOREIGN KEY (`idAuthor`) REFERENCES `users` (`idUser`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;