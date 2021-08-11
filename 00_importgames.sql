-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : sam. 31 juil. 2021 à 17:37
-- Version du serveur : 5.7.35-0ubuntu0.18.04.1
-- Version de PHP : 7.2.34-23+ubuntu18.04.1+deb.sury.org+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `importgames`
--

-- --------------------------------------------------------

--
-- Structure de la table `achat`
--

CREATE TABLE `achat` (
  `id` int(11) NOT NULL,
  `produit_id` int(11) DEFAULT NULL,
  `titre` text,
  `qte` int(11) DEFAULT NULL,
  `prix` float(10,2) DEFAULT NULL,
  `date_a` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `achat`
--

INSERT INTO `achat` (`id`, `produit_id`, `titre`, `qte`, `prix`, `date_a`) VALUES
(1, 1, 'Astral Chain', 5, 40.50, '2020-09-28'),
(2, 2, 'Rem & Ram Twins Ver.', 10, 105.00, '2020-09-28'),
(3, 3, 'Cahier A4 \"Yuri!!! on Ice\"', 12, 7.20, '2020-09-28'),
(4, 1, 'Astral Chain', 5, 39.00, '2020-09-30'),
(5, 4, 'Psikyo Shooting Library Vol.2 (Multilingue)', 20, 17.00, '2020-10-06'),
(6, 5, 'Yubari \"Collection Kantai -KanColle-\"', 30, 11.00, '2020-10-06'),
(7, 6, 'Stylo à bille \"Kanan Matsuura Idol\"', 24, 2.00, '2020-10-08'),
(8, 7, 'Hell Tower Mary Skelter 2', 5, 22.00, '2020-10-08'),
(9, 8, 'POP UP PARADE Hatsune Miku', 24, 11.50, '2020-10-25'),
(10, 10, 'Attack on Titan 2 Final Battle', 15, 27.30, '2020-10-25'),
(11, 11, 'Lacia 2011 Version', 5, 41.80, '2020-10-25'),
(12, 12, 'Étui de notes adhésives, design \"Vinland Saga\"', 36, 2.25, '2020-10-25'),
(13, 13, 'Hatsune Miku Project Diva Mega39\'s', 12, 31.80, '2020-10-25'),
(14, 14, 'METAL BUILD Evangelion-01', 4, 135.90, '2020-10-25'),
(15, 15, 'Étui à stylos \"Psycho-Pass 2\" (Modèle 2)', 24, 3.14, '2020-10-25'),
(16, 16, 'Coffee Talk (Multilingue)', 20, 19.95, '2020-10-25'),
(17, 17, 'King of Fighters \'98 Ultimate Match Iori Yagami', 10, 69.50, '2020-10-25'),
(18, 18, 'Bloc-notes de terrain style Nerv \"Rebuild of Evangelion\"', 6, 6.70, '2020-10-25'),
(19, 19, 'Test', 1, 10.00, '2020-12-11');

-- --------------------------------------------------------

--
-- Structure de la table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `name` text CHARACTER SET utf8 COLLATE utf8_bin,
  `password` text CHARACTER SET utf8 COLLATE utf8_bin,
  `prv` text,
  `date_reg` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `admin`
--

INSERT INTO `admin` (`id`, `name`, `password`, `prv`, `date_reg`) VALUES
(1, 'Nerofaust', '$argon2i$v=19$m=1024,t=2,p=2$Qk1rbncxUVVzUktKQm5kYQ$ljUJnaKyct49EBfG1U85X+tVo9rWeZ8CT7rzUpif15Q', '1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33', '2020-08-29'),
(2, 'Zack', '$argon2i$v=19$m=1024,t=2,p=2$LlpoODkuV0hhSHp2YjlGVA$VjL4GVHqkQkSl7CbNN786vhn0IOTDqp81zPdFEKRR8Y', '1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33', '2020-08-29'),
(3, 'Admin_3', '$argon2i$v=19$m=1024,t=2,p=2$RHFkYWVpdGNjMy5HOTE3eA$IUlb5c6niGyOyaIGf9wlQPDb+YOdK4v35KTz5Q4og24', '1, 4, 7, 11, 12, 15, 17, 19, 22, 25, 26, 27, 29, 31', '2020-08-30');

-- --------------------------------------------------------

--
-- Structure de la table `categorie`
--

CREATE TABLE `categorie` (
  `id` int(11) NOT NULL,
  `titre` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `categorie`
--

INSERT INTO `categorie` (`id`, `titre`) VALUES
(1, 'Jeux vidéo'),
(2, 'Figurines'),
(3, 'Papeterie'),
(4, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `commande`
--

CREATE TABLE `commande` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `numero` int(11) DEFAULT NULL,
  `adresse` text,
  `total` float(10,2) DEFAULT NULL,
  `date_co` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `commande`
--

INSERT INTO `commande` (`id`, `user_id`, `numero`, `adresse`, `total`, `date_co`) VALUES
(1, 1, 1, 'Lemaître Ludovic\r\n8 Résidence du Parc\r\n94450 LIMEIL-BRÉVANNES\r\nFR', 109.80, '2020-04-30 07:27:35'),
(2, 1, 2, 'Lemaître Ludovic\r\n8 Résidence du Parc\r\n94450 LIMEIL-BRÉVANNES\r\nFR', 55.00, '2020-05-01 05:37:18'),
(3, 2, 1, 'leconte guy françois\r\n5 rue de la gare\r\n58270 BEAUMONT-SARDOLLES\r\nFR', 79.80, '2020-05-01 09:40:00'),
(4, 3, 1, 'Beld Ashram\r\n85 rue du Trocadéro\r\n94000 CRÉTEIL\r\nFR', 180.90, '2020-05-02 15:26:56'),
(5, 1, 3, 'Lemaître Ludovic\r\n8 Résidence du Parc\r\n94450 LIMEIL-BRÉVANNES\r\nFR', 100.00, '2020-05-13 19:07:08'),
(6, 3, 2, 'Beld Ashram\r\n85 rue du Trocadéro\r\n94000 CRÉTEIL\r\nFR', 69.90, '2020-05-13 19:11:53'),
(7, 1, 4, 'Lemaître Ludovic\r\n8 Résidence du Parc\r\n94450 LIMEIL-BRÉVANNES\r\nFR', 19.50, '2020-05-14 08:49:47'),
(8, 1, 5, 'Lemaître Ludovic\r\n8 Résidence du Parc\r\n94450 LIMEIL-BRÉVANNES\r\nFR', 21.80, '2020-05-14 09:00:42'),
(9, 1, 6, 'Lemaître Ludovic\r\n8 Résidence du Parc\r\n94450 LIMEIL-BRÉVANNES\r\nFR', 74.90, '2020-05-14 09:07:00'),
(10, 1, 7, 'Lemaître Ludovic\r\n8 Résidence du Parc\r\n94450 LIMEIL-BRÉVANNES\r\nFR', 338.90, '2020-05-14 09:25:19'),
(11, 1, 8, 'Lemaître Ludovic\r\n8 Résidence du Parc\r\n94450 LIMEIL-BRÉVANNES\r\nFR', 25.00, '2020-05-14 14:20:48'),
(12, 1, 9, 'Lemaître Ludovic\r\n8 Résidence du Parc\r\n94450 LIMEIL-BRÉVANNES\r\nFR', 4.50, '2020-05-14 14:22:03'),
(13, 1, 10, 'Lemaître Ludovic\r\n8 Résidence du Parc\r\n94450 LIMEIL-BRÉVANNES\r\nFR', 279.80, '2020-05-17 16:28:23'),
(14, 3, 3, 'Beld Ashram\r\n85 rue du Trocadéro\r\n94000 CRÉTEIL\r\nFR', 16.30, '2020-05-20 10:11:36'),
(15, 1, 11, 'Lemaître Ludovic\r\n8 Résidence du Parc\r\n94450 LIMEIL-BRÉVANNES\r\nFR', 313.90, '2020-05-22 09:39:18'),
(16, 1, 12, 'Lemaître Ludovic\r\n8 Résidence du Parc\r\n94450 LIMEIL-BRÉVANNES\r\nFR', 238.00, '2020-06-08 16:59:24'),
(17, 3, 4, 'Beld Ashram\r\n85 rue du Trocadéro\r\n94000 CRÉTEIL\r\nFR', 99.90, '2020-06-16 17:46:36'),
(18, 1, 13, 'Lemaître Ludovic\r\n8 Résidence du Parc\r\n94450 LIMEIL-BRÉVANNES\r\nFR', 19.50, '2020-06-18 09:24:20'),
(19, 1, 14, 'Lemaître Ludovic\r\n8 Résidence du Parc\r\n94450 LIMEIL-BRÉVANNES\r\nFR', 64.90, '2020-06-18 09:28:26'),
(20, 3, 5, 'Beld Ashram\r\n85 rue du Trocadéro\r\n94000 CRÉTEIL\r\nFR', 360.00, '2020-06-18 09:36:26'),
(21, 3, 6, 'Beld Ashram\r\n85 rue du Trocadéro\r\n94000 CRÉTEIL\r\nFR', 109.90, '2020-06-18 10:20:54'),
(22, 1, 15, 'Lemaître Ludovic\r\n8 Résidence du Parc\r\n94450 LIMEIL-BRÉVANNES\r\nFR', 92.00, '2020-06-26 19:06:23'),
(23, 3, 7, 'Beld Ashram\r\n85 rue du Trocadéro\r\n94000 CRÉTEIL\r\nFR', 69.90, '2020-08-17 09:43:26'),
(24, 3, 8, 'Beld Ashram\r\n85 rue du Trocadéro\r\n94000 CRÉTEIL\r\nFR', 12.90, '2020-09-13 14:25:27'),
(25, 1, 16, 'Lemaître Ludovic\r\n8 Résidence du Parc\r\n94450 LIMEIL-BRÉVANNES\r\nFR', 116.90, '2020-09-16 11:11:23'),
(26, 1, 17, 'Lemaître Ludovic\r\n8 Résidence du Parc\r\n94450 LIMEIL-BRÉVANNES\r\nFR', 89.00, '2020-10-12 08:53:52'),
(27, 1, 18, 'Lemaître Ludovic\r\n8 Résidence du Parc\r\n94450 LIMEIL-BRÉVANNES\r\nFR', 131.90, '2020-10-25 17:51:47'),
(28, 3, 9, 'Beld Ashram\r\n85 rue du Trocadéro\r\n94000 CRÉTEIL\r\nFR', 351.80, '2020-10-25 18:00:04'),
(29, 1, 19, 'Lemaître Ludovic\r\n8 Résidence du Parc\r\n94450 LIMEIL-BRÉVANNES\r\nFR', 131.90, '2020-11-04 18:43:35'),
(30, 1, 20, 'Lemaître Ludovic\r\n8 Résidence du Parc\r\n94450 LIMEIL-BRÉVANNES\r\nFR', 162.00, '2020-11-19 15:28:56'),
(31, 1, 21, 'Lemaître Ludovic\r\n8 Résidence du Parc\r\n94450 LIMEIL-BRÉVANNES\r\nFR', 20.00, '2020-12-13 11:52:32'),
(32, 6, 1, 'Pro Testeur\n46 Rue René Clair\n75018 Paris\nFR', 162.00, '2020-12-14 17:09:17'),
(33, 1, 22, 'Lemaître Ludovic\r\n8 Résidence du Parc\r\n94450 LIMEIL-BRÉVANNES\r\nFR', 109.90, '2020-12-20 17:28:19');

-- --------------------------------------------------------

--
-- Structure de la table `detail_com`
--

CREATE TABLE `detail_com` (
  `id` int(11) NOT NULL,
  `commande_id` int(11) DEFAULT NULL,
  `produit_id` int(11) DEFAULT NULL,
  `titre` text,
  `qte` int(11) DEFAULT NULL,
  `prix` float(10,2) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `detail_com`
--

INSERT INTO `detail_com` (`id`, `commande_id`, `produit_id`, `titre`, `qte`, `prix`) VALUES
(1, 1, 13, 'Hatsune Miku Project Diva Mega39\'s', 1, 69.90),
(2, 1, 8, 'POP UP PARADE Hatsune Miku', 1, 25.00),
(3, 1, 18, 'Bloc-notes de terrain style Nerv \"Rebuild of Evangelion\"', 1, 14.90),
(4, 2, 7, 'Hell Tower Mary Skelter 2', 1, 55.00),
(5, 3, 16, 'Coffee Talk (Multilingue)', 2, 39.90),
(6, 4, 4, 'Psikyo Shooting Library Vol.2 (Multilingue)', 1, 35.00),
(7, 4, 17, 'King of Fighters \'98 Ultimate Match Iori Yagami', 1, 139.00),
(8, 4, 15, 'Étui à stylos \"Psycho-Pass 2\" (Modèle 2)', 1, 6.90),
(9, 5, 9, 'Porte-Stylo Acrylique \"Shin Kyogoku\"', 1, 8.00),
(10, 5, 11, 'Lacia 2011 Version', 1, 92.00),
(11, 6, 18, 'Bloc-notes de terrain style Nerv \"Rebuild of Evangelion\"', 1, 14.90),
(12, 6, 7, 'Hell Tower Mary Skelter 2', 1, 55.00),
(13, 7, 6, 'Stylo à bille \"Kanan Matsuura Idol\"', 1, 4.50),
(14, 7, 3, 'Cahier A4 \"Yuri!!! on Ice\"', 1, 15.00),
(15, 8, 15, 'Étui à stylos \"Psycho-Pass 2\" (Modèle 2)', 1, 6.90),
(16, 8, 18, 'Bloc-notes de terrain style Nerv \"Rebuild of Evangelion\"', 1, 14.90),
(17, 9, 12, 'Étui de notes adhésives, design \"Vinland Saga\"', 1, 4.90),
(18, 9, 1, 'Astral Chain', 1, 70.00),
(19, 10, 8, 'POP UP PARADE Hatsune Miku', 1, 25.00),
(20, 10, 14, 'METAL BUILD Evangelion-01', 1, 299.00),
(21, 10, 18, 'Bloc-notes de terrain style Nerv \"Rebuild of Evangelion\"', 1, 14.90),
(22, 11, 5, 'Yubari \"Collection Kantai -KanColle-\"', 1, 25.00),
(23, 12, 6, 'Stylo à bille \"Kanan Matsuura Idol\"', 1, 4.50),
(24, 13, 16, 'Coffee Talk (Multilingue)', 1, 39.90),
(25, 13, 7, 'Hell Tower Mary Skelter 2', 1, 55.00),
(26, 13, 12, 'Étui de notes adhésives, design \"Vinland Saga\"', 1, 4.90),
(27, 13, 2, 'Rem & Ram Twins Ver.', 1, 180.00),
(28, 14, 6, 'Stylo à bille \"Kanan Matsuura Idol\"', 1, 4.50),
(29, 14, 15, 'Étui à stylos \"Psycho-Pass 2\" (Modèle 2)', 1, 6.90),
(30, 14, 12, 'Étui de notes adhésives, design \"Vinland Saga\"', 1, 4.90),
(31, 15, 14, 'METAL BUILD Evangelion-01', 1, 299.00),
(32, 15, 18, 'Bloc-notes de terrain style Nerv \"Rebuild of Evangelion\"', 1, 14.90),
(33, 16, 5, 'Yubari \"Collection Kantai -KanColle-\"', 2, 25.00),
(34, 16, 9, 'Porte-Stylo Acrylique \"Shin Kyogoku\"', 1, 8.00),
(35, 16, 2, 'Rem & Ram Twins Ver.', 1, 180.00),
(36, 17, 10, 'Attack on Titan 2 Final Battle', 1, 60.00),
(37, 17, 16, 'Coffee Talk (Multilingue)', 1, 39.90),
(38, 18, 3, 'Cahier A4 \"Yuri!!! on Ice\"', 1, 15.00),
(39, 18, 6, 'Stylo à bille \"Kanan Matsuura Idol\"', 1, 4.50),
(40, 19, 16, 'Coffee Talk (Multilingue)', 1, 39.90),
(41, 19, 5, 'Yubari \"Collection Kantai -KanColle-\"', 1, 25.00),
(42, 20, 2, 'Rem & Ram Twins Ver.', 2, 180.00),
(43, 21, 16, 'Coffee Talk (Multilingue)', 1, 39.90),
(44, 21, 4, 'Psikyo Shooting Library Vol.2 (Multilingue)', 2, 35.00),
(45, 22, 11, 'Lacia 2011 Version', 1, 92.00),
(46, 23, 18, 'Bloc-notes de terrain style Nerv \"Rebuild of Evangelion\"', 1, 14.90),
(47, 23, 7, 'Hell Tower Mary Skelter 2', 1, 55.00),
(48, 24, 9, 'Porte-Stylo Acrylique \"Shin Kyogoku\"', 1, 8.00),
(49, 24, 12, 'Étui de notes adhésives, design \"Vinland Saga\"', 1, 4.90),
(50, 25, 15, 'Étui à stylos \"Psycho-Pass 2\" (Modèle 2)', 1, 6.90),
(51, 25, 7, 'Hell Tower Mary Skelter 2', 2, 55.00),
(52, 26, 7, 'Hell Tower Mary Skelter 2', 1, 55.00),
(53, 26, 6, 'Stylo à bille \"Kanan Matsuura Idol\"', 2, 4.50),
(54, 26, 5, 'Yubari \"Collection Kantai -KanColle-\"', 1, 25.00),
(55, 27, 11, 'Lacia 2011 Version', 1, 92.00),
(56, 27, 18, 'Bloc-notes de terrain style Nerv \"Rebuild of Evangelion\"', 1, 14.90),
(57, 27, 5, 'Yubari \"Collection Kantai -KanColle-\"', 1, 25.00),
(58, 28, 16, 'Coffee Talk (Multilingue)', 2, 39.90),
(59, 28, 2, 'Rem & Ram Twins Ver.', 1, 180.00),
(60, 28, 11, 'Lacia 2011 Version', 1, 92.00),
(61, 29, 11, 'Lacia 2011 Version', 1, 92.00),
(62, 29, 16, 'Coffee Talk (Multilingue)', 1, 39.90),
(63, 30, 11, 'Lacia 2011 Version', 1, 92.00),
(64, 30, 1, 'Astral Chain', 1, 70.00),
(65, 31, 19, 'Test', 1, 20.00),
(66, 32, 1, 'Astral Chain', 1, 70.00),
(67, 32, 11, 'Lacia 2011 Version', 1, 92.00),
(68, 33, 12, 'Étui de notes adhésives, design \"Vinland Saga\"', 1, 4.90),
(69, 33, 4, 'Psikyo Shooting Library Vol.2 (Multilingue)', 3, 35.00);

-- --------------------------------------------------------

--
-- Structure de la table `image`
--

CREATE TABLE `image` (
  `id` int(11) NOT NULL,
  `produit_id` int(11) DEFAULT NULL,
  `url` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `image`
--

INSERT INTO `image` (`id`, `produit_id`, `url`) VALUES
(1, 1, '/importgames/images/image/jeux/6150_24-10-2020.png'),
(2, 1, '/importgames/images/image/jeux/NSwitch_AstralChain_09.jpg'),
(3, 1, '/importgames/images/image/jeux/NSwitch_AstralChain_16.jpg'),
(4, 1, '/importgames/images/image/jeux/astral-chain-.jpeg'),
(5, 2, '/importgames/images/image/figurines/7037_24-10-2020.jpg'),
(6, 2, '/importgames/images/image/figurines/602180469_1.jpg'),
(7, 2, '/importgames/images/image/figurines/602180469_2.jpg'),
(8, 2, '/importgames/images/image/figurines/602180469_3.jpg'),
(9, 3, '/importgames/images/image/papeterie/2829_24-10-2020.jpg'),
(10, 3, '/importgames/images/image/papeterie/994947711_1.jpg'),
(11, 3, '/importgames/images/image/papeterie/VJHCGe1o.jpg'),
(12, 3, '/importgames/images/image/papeterie/Hw5ErBAkgVI.jpg'),
(13, 4, '/importgames/images/image/jeux/7724_24-10-2020.jpg'),
(14, 4, '/importgames/images/image/jeux/psikyo-shooting-library-vol-2-limited-edition-591715.12.jpg'),
(15, 4, '/importgames/images/image/jeux/-591715.2.jpg'),
(16, 4, '/importgames/images/image/jeux/-591715.5.jpg'),
(17, 5, '/importgames/images/image/figurines/4945_24-10-2020.jpg'),
(18, 5, '/importgames/images/image/figurines/920-1.jpg'),
(19, 5, '/importgames/images/image/figurines/8042402.jpg'),
(20, 5, '/importgames/images/image/figurines/b255-768x1024.jpg'),
(21, 6, '/importgames/images/image/papeterie/8886_24-10-2020.jpg'),
(22, 6, '/importgames/images/image/papeterie/994718672_1.jpg'),
(23, 6, '/importgames/images/image/papeterie/c6feaca.jpg'),
(24, 6, '/importgames/images/image/papeterie/love-live-sunshine-clear-file-kanan-matsuura-sukusuta-pre-order.jpg'),
(25, 7, '/importgames/images/image/jeux/2148_24-10-2020.jpg'),
(26, 7, '/importgames/images/image/jeux/-595763.6.jpg'),
(27, 7, '/importgames/images/image/jeux/mary-skelter-2-chinese-subs-595763.9.jpg'),
(28, 7, '/importgames/images/image/jeux/-595763.7.jpg'),
(29, 8, '/importgames/images/image/figurines/5348_24-10-2020.jpg'),
(30, 8, '/importgames/images/image/figurines/602175431_1.jpg'),
(31, 8, '/importgames/images/image/figurines/602175431_2.jpg'),
(32, 8, '/importgames/images/image/figurines/602175431_3.jpg'),
(33, 9, '/importgames/images/image/papeterie/6110_24-10-2020.jpg'),
(34, 9, '/importgames/images/image/papeterie/10630519b.jpg'),
(35, 9, '/importgames/images/image/papeterie/EATqNsOUwAAZ4x0.jpg'),
(36, 9, '/importgames/images/image/papeterie/814226_n.jpg'),
(37, 10, '/importgames/images/image/jeux/3774_24-10-2020.png'),
(38, 10, '/importgames/images/image/jeux/attack-on-titan-2-final-battle-officially-announced-for-switch-Bcjm5Wy6LjY.jpg'),
(39, 10, '/importgames/images/image/jeux/maxresdefault2.jpg'),
(40, 10, '/importgames/images/image/jeux/maxresdefault3.jpg'),
(41, 11, '/importgames/images/image/figurines/9620_24-10-2020.jpg'),
(42, 11, '/importgames/images/image/figurines/602165660_2.jpg'),
(43, 11, '/importgames/images/image/figurines/602165660_5.jpg'),
(44, 11, '/importgames/images/image/figurines/602165660_6.jpg'),
(45, 12, '/importgames/images/image/papeterie/3252_24-10-2020.jpg'),
(46, 12, '/importgames/images/image/papeterie/4589838167032_01.jpg'),
(47, 12, '/importgames/images/image/papeterie/g45854_01.jpg'),
(48, 12, '/importgames/images/image/papeterie/145092156_th.jpg'),
(49, 13, '/importgames/images/image/jeux/4225_24-10-2020.png'),
(50, 13, '/importgames/images/image/jeux/hatsune-miku-project-diva-mega39s-599301.2.jpg'),
(51, 13, '/importgames/images/image/jeux/hatsune-miku-project-diva-mega39s-599301.4.jpg'),
(52, 13, '/importgames/images/image/jeux/hatsune-miku-project-diva-mega39s-599301.8.jpg'),
(53, 14, '/importgames/images/image/figurines/8525_24-10-2020.png'),
(54, 14, '/importgames/images/image/figurines/71R7ezY6vcL._SL1500_.jpg.jpg'),
(55, 14, '/importgames/images/image/figurines/81KhmIAoc9L._SL1500_.jpg'),
(56, 14, '/importgames/images/image/figurines/81pHpvpZbpL._SL1500_.jpg'),
(57, 15, '/importgames/images/image/papeterie/3608_24-10-2020.png'),
(58, 15, '/importgames/images/image/papeterie/IMG_8475__25264.1577160191\'.png'),
(59, 15, '/importgames/images/image/papeterie/IMG_8474__94514.1577160191\'.png'),
(60, 15, '/importgames/images/image/papeterie/IMG_8472__66127.1577160190.png'),
(61, 16, '/importgames/images/image/jeux/3175_24-10-2020.png'),
(62, 16, '/importgames/images/image/jeux/coffee-talk-multilanguage-613955.2.jpg'),
(63, 16, '/importgames/images/image/jeux/coffee-talk-multilanguage-613955.14.jpg'),
(64, 16, '/importgames/images/image/jeux/coffee-talk-multilanguage-613955.16.jpg'),
(65, 17, '/importgames/images/image/figurines/6285_24-10-2020.jpg'),
(66, 17, '/importgames/images/image/figurines/Iori_Yagami_cover__69405.1570575469.1280.1280.jpg'),
(67, 17, '/importgames/images/image/figurines/KoF-98-Iori-Yagami-Figure-012.jpg'),
(68, 17, '/importgames/images/image/figurines/KoF-98-Iori-Yagami-Figure-002.jpg'),
(69, 18, '/importgames/images/image/papeterie/1045_24-10-2020.jpg'),
(70, 18, '/importgames/images/image/papeterie/016.jpg'),
(71, 18, '/importgames/images/image/papeterie/026.jpg'),
(72, 18, '/importgames/images/image/papeterie/045.jpg'),
(73, 1, '/importgames/images/image/jeux/astral-chain-review-1.jpg'),
(74, 2, '/importgames/images/image/figurines/602180469_4.jpg'),
(75, 3, '/importgames/images/image/papeterie/843008d5.jpg'),
(76, 4, '/importgames/images/image/jeux/-591715.10.jpg'),
(77, 5, '/importgames/images/image/figurines/8AAia9d.jpg'),
(78, 6, '/importgames/images/image/papeterie/love-live-sunshine-deka-strap-kanan-matsuura-icon-t-shirt-ver-pre-order.jpg'),
(79, 7, '/importgames/images/image/jeux/-595763.4.jpg'),
(80, 8, '/importgames/images/image/figurines/602175431_4.jpg'),
(81, 9, '/importgames/images/image/papeterie/51AL9fKSAyL._AC_.jpg'),
(82, 10, '/importgames/images/image/jeux/Nile-Dawk.jpg'),
(83, 11, '/importgames/images/image/figurines/602165660_7.jpg'),
(84, 12, '/importgames/images/image/papeterie/116187.jpg'),
(85, 13, '/importgames/images/image/jeux/hatsune-miku-project-diva-mega39s-599301.9.jpg'),
(86, 14, '/importgames/images/image/figurines/719f-InDlTL._SL1500_.jpg'),
(87, 15, '/importgames/images/image/papeterie/IMG_8473__01526.1577160190.png'),
(88, 16, '/importgames/images/image/jeux/coffee-talk-multilanguage-613955.17.jpg'),
(89, 17, '/importgames/images/image/figurines/KoF-98-Iori-Yagami-Figure-017.jpg'),
(90, 18, '/importgames/images/image/papeterie/x1.jpg');

-- --------------------------------------------------------

--
-- Structure de la table `partenaire`
--

CREATE TABLE `partenaire` (
  `id` int(11) NOT NULL,
  `nom` text,
  `image` text,
  `url` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `partenaire`
--

INSERT INTO `partenaire` (`id`, `nom`, `image`, `url`) VALUES
(1, 'Nintendo France', '/importgames/images/partenaire/Nintendo_Logo_2017.png', 'https://www.nintendo.fr'),
(2, 'Sega', '/importgames/images/partenaire/SEGA_logo.png', 'https://www.sega.fr'),
(3, 'Good Smile Company', '/importgames/images/partenaire/gsc_logo.jpg', 'https://www.goodsmile.info/en'),
(4, 'Tamashii Nations', '/importgames/images/partenaire/logo_tamashii.jpg', 'https://www.tamashiinations.com'),
(5, 'Tombow', '/importgames/images/partenaire/tombow-pencil-logo.svg', 'https://www.tomboweurope.com/fr'),
(6, 'Washi Tape (Masking Tape)', '/importgames/images/partenaire/washi_tape_logo.png', 'https://www.masking-tape.jp/en');

-- --------------------------------------------------------

--
-- Structure de la table `produit`
--

CREATE TABLE `produit` (
  `id` int(11) NOT NULL,
  `cat_id` int(11) DEFAULT NULL,
  `titre` text,
  `ean13` bigint(20) DEFAULT NULL,
  `prix` float(10,2) DEFAULT NULL,
  `qte` int(11) DEFAULT '0',
  `date_sortie` date DEFAULT NULL,
  `description` text,
  `apercu_img` text,
  `video` text,
  `date_creation` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `produit`
--

INSERT INTO `produit` (`id`, `cat_id`, `titre`, `ean13`, `prix`, `qte`, `date_sortie`, `description`, `apercu_img`, `video`, `date_creation`) VALUES
(1, 1, 'Astral Chain', 4902370542707, 70.00, 8, '2019-08-30', 'Jeu Nintendo Switch version Japonaise.\r\n\r\nAstral Chain est un jeu d\'action développé par PlatinumGames et édité par Nintendo. Le jeu met en scène une unité de police appelée \"Neuron\", qui a pour rôle de lutter contre de mystérieuses créatures extraterrestres en passe d\'envahir le monde et la ville d\'Ark. En réponse, l\'humanité a créé une arme spéciale baptisée \"Légion\", qui fait office de partenaire et vous accompagne dans vos investigations ainsi que dans les affrontements.', '/importgames/images/produit/5474_24-10-2020.jpg', 'https://www.youtube.com/embed/brmmV3g4qqo', '2020-04-27'),
(2, 2, 'Rem & Ram Twins Ver.', 4589456500082, 180.00, 9, '2020-01-31', 'Figurine en PVC de Rem & Ram Twins Ver. Un magnifique duo des jumelles de l\'anime Re:Zero Starting Life in Another World dans leur uniforme noir et blanc. Complices, elles joignent leurs mains, adoptant une posture dynamique. Un effet de mouvement est donné aux tenues pour plus de réalisme. Une pièce de collection qui saura plaire aux fans.\r\n\r\nTaille : 24 cm\r\n\r\nFabricant : Souyokusha', '/importgames/images/produit/5109_24-10-2020.jpg', 'https://www.youtube.com/embed/fmhhdojwQEc', '2020-04-27'),
(3, 3, 'Cahier A4 \"Yuri!!! on Ice\"', 994947711001, 15.00, 12, '2017-08-26', 'Tiré de l\'événement \"Yuri !!! on ICE x Sagan Tosu\", en collaboration avec le club japonais de football \"Sagan Tosu\".\r\n\r\n[Détails du produit]\r\n\r\nTaille : A4 (21 x 29,7 cm)\r\n\r\nImpression couleur recto verso', '/importgames/images/produit/2773_24-10-2020.jpg', NULL, '2020-03-27'),
(4, 1, 'Psikyo Shooting Library Vol.2 (Multilingue)', 4571442047220, 35.00, 17, '2019-08-29', 'Jeu Nintendo Switch version Japonaise.\r\n\r\nSix jeux de tir du fabricant de jeux «Saikyo», qui a joué un rôle actif principalement dans la scène arcade des années 90, sont publiés sous forme de compilation.\r\n\r\nPrise en charge de la langue japonaise et anglaise pour Sengoku Ace, Sengoku Blade, Gunbird, Gunbird 2 et Gunbarich.\r\nRemarque : Sengoku Cannon est uniquement en japonais.', '/importgames/images/produit/3403_24-10-2020.jpg', 'https://www.youtube.com/embed/-6HdlKbkK2w', '2020-04-27'),
(5, 2, 'Yubari \"Collection Kantai -KanColle-\"', 602182532001, 25.00, 28, '2019-09-30', 'Figurine premium limitée \"Yubari\" Mode Skate.\r\n\r\nIllustration en trois dimensions de \"Yubari\" annoncée lors du festival \"Ice\" du gouvernement gardien \"KanColle\" - Cérémonie sur glace ! Prêtez attention aux poses dynamiques et à la glace !\r\n\r\nTaille : Avec le piédestal environ 21 x 15 cm\r\n\r\nFabricant : Sega', '/importgames/images/produit/1462_24-10-2020.jpg', NULL, '2020-04-27'),
(6, 3, 'Stylo à bille \"Kanan Matsuura Idol\"', 994718672000, 4.50, 22, '2019-10-17', 'Ce stylo à bille rétractable est tiré de l\'anime \"Love Live! Sunshine!!\"\r\n\r\n[Détails du produit]\r\n\r\nTaille : Environ 38 x 95,5 mm\r\n\r\nMatériel : plastique, [contenu] papier impression jet d\'encre', '/importgames/images/produit/5197_24-10-2020.jpg', NULL, '2020-04-27'),
(7, 1, 'Hell Tower Mary Skelter 2', 4995857096190, 55.00, 4, '2019-08-22', 'Jeu Nintendo Switch version Japonaise.\r\n\r\nHell Tower Mary Skelter 2 est un RPG de type dungeon crawler. Le titre explorera un scénario alternatif au premier opus éponyme. Jack, le héros de l\'épisode précédant, se voit ici transformé en un horrible monstre, tandis que l\'on verra l\'introduction de deux nouvelles héroïnes, Tsû et Ningyôhime.', '/importgames/images/produit/2161_24-10-2020.jpg', NULL, '2020-04-28'),
(8, 2, 'POP UP PARADE Hatsune Miku', 4580416941044, 25.00, 24, '2019-06-28', 'POP UP PARADE est une nouvelle série de figurines faciles à collectionner avec des prix abordables et des sorties prévues seulement quatre mois après le début des précommandes ! Chaque figurine mesure environ 17-18 cm de hauteur et la série propose une vaste sélection de personnages de séries animées et de jeux populaires, et bien d\'autres seront bientôt ajoutés !\r\nLa première figurine de la série est Hatsune Miku, vêtue de sa tenue standard bien connue. Assurez-vous de l\'ajouter à votre collection !\r\n\r\nProduit complet sans échelle en ABS et PVC peint avec support inclus. Environ 170 mm de hauteur.\r\n\r\nFabricant : Good Smile Company', '/importgames/images/produit/3911_24-10-2020.jpg', NULL, '2020-04-28'),
(9, 3, 'Porte-Stylo Acrylique \"Shin Kyogoku\"', 994815410000, 8.00, 0, '2019-10-13', 'Un pot à crayons acrylique classique tiré du manga \"Détective Conan\" apparaît !\r\n\r\n[Détails du produit]\r\n\r\nTaille : Environ H125 x L60 x P3 mm\r\n\r\nMatériel : Acrylique', '/importgames/images/produit/7138_24-10-2020.jpg', NULL, '2020-04-28'),
(10, 1, 'Attack on Titan 2 Final Battle', 4988615128134, 60.00, 15, '2019-07-04', 'Jeu Nintendo Switch version Japonaise.\r\n\r\nA.O.T. 2 : Final Battle reprend les bases solides qui ont fait le succès de la série en y ajoutant de nouvelles mécaniques de gameplay exaltantes. Outre la possibilité de découvrir les évènements des saisons 1 et 2 de l’anime l’Attaque des Titans du point de vue de votre éclaireur personnalisé (A.O.T. 2), vous pouvez revivre l’intrigue de la saison 3 à travers les points de vue de nombreux personnages plébiscités par les fans. Armez-vous de votre équipement de manœuvre tridimensionnel et plongez dans le conflit comme jamais auparavant.', '/importgames/images/produit/1674_24-10-2020.jpg', '/importgames/videos/produit/3890_24-10-2020.mp4', '2020-04-28'),
(11, 2, 'Lacia 2011 Version', 4580416940641, 92.00, 0, '2019-08-25', 'Statuette Lacia de la série animée \"Beatless\" en PVC à l´échelle 1/8, sculptée par Iwanaga Sakurako.\r\n\r\nTaille : Environ 28 cm avec socle\r\n\r\nFabricant : Good Smile Company', '/importgames/images/produit/5520_24-10-2020.jpg', 'https://www.youtube.com/embed/5U3vYVh5gz8', '2020-04-28'),
(12, 3, 'Étui de notes adhésives, design \"Vinland Saga\"', 994619514000, 4.90, 35, '2019-10-10', 'Couverture compacte, pratique et facile à transporter dans une poche.\r\n\r\n[Détails du produit]\r\n\r\nTaille : H77 × L57 × P11 mm environ\r\n\r\nMatériel : [Couverture] PU, [Notes] Papier fin', '/importgames/images/produit/4035_24-10-2020.jpg', NULL, '2020-04-28'),
(13, 1, 'Hatsune Miku Project Diva Mega39\'s', 4974365861896, 69.90, 12, '2020-02-13', 'Jeu Nintendo Switch version Japonaise.\r\n\r\nEn plus du \"Mode Arcade\", similaire à celui du \"Hatsune Miku Project DIVA Arcade Future Tone\" sorti sur Playstation 4 en 2017, un nouveau mode de jeu \"Mix Mode\" unique à la Nintendo Switch a été ajouté.\r\nUn total de 101 chansons peuvent être jouées, 100 qui ont marquées l\'histoire du mouvement musical Vocaloid depuis 10 ans, y compris des enregistrements récents ainsi que la chanson thème du jeu \"Catch the Wave\".\r\n\r\nÉditeur : Sega', '/importgames/images/produit/3143_24-10-2020.jpg', 'https://www.youtube.com/embed/qFRWBU47qj8', '2020-04-29'),
(14, 2, 'METAL BUILD Evangelion-01', 4573102550408, 299.00, 4, '2019-09-28', 'L\'unité Eva-01 obtient enfin une sortie spectaculaire dans la gamme METAL BUILD de Bandai ! Elle comprend des pièces moulées sous pression et un traitement de placage pour une apparence brillante inégalée, un système de jonction incroyablement flexible pour des possibilités qui vous surprendront ainsi que des armes supplémentaires ! Son présentoir est également réalisé avec un soin particulier, à l\'image d\'une cage de contention complète avec un câble ombilical souple. Vous ne voudrez pas manquer cet objet Evangelion ultime - procurez-vous le vôtre dès aujourd\'hui !\r\n\r\n[Détails du produit]\r\n\r\nTaille : L17,8 x P10,2 x H22,1 cm\r\n\r\nPoids : 930 g\r\n \r\nMatériel : ABS, PVC, moulé sous pression\r\n\r\nFabricant : Bandai', '/importgames/images/produit/4580_24-10-2020.jpg', 'https://www.youtube.com/embed/U5sMFNf6b4o', '2020-04-29'),
(15, 3, 'Étui à stylos \"Psycho-Pass 2\" (Modèle 2)', 994805680001, 6.90, 24, '2015-03-20', 'Tiré de la saison 2 de l\'anime à succès \"PSYCHO-PASS\", un nouvel étui rigide pour stylos apparaît !\r\n\r\n[Détails du produit]\r\n\r\nTaille : H86 × L192 × P25 mm environ\r\n\r\nMatériel : Fer blanc', '/importgames/images/produit/1292_24-10-2020.jpg', NULL, '2020-04-29'),
(16, 1, 'Coffee Talk (Multilingue)', 4988602172324, 39.90, 17, '2020-01-30', 'Jeu Nintendo Switch version Japonaise.\r\n\r\nCoffee Talk est un jeu de simulation de vie dans un café. Vous y écoutez les problèmes de personnages. Vous les aidez en leur servant des boissons adaptées le tout en restant le plus aimable possible.\r\n\r\nTexte écran : Japonais, Anglais, Français, Allemand', '/importgames/images/produit/6728_24-10-2020.jpg', 'https://www.youtube.com/embed/qP3_61SUmdI', '2020-04-29'),
(17, 2, 'King of Fighters \'98 Ultimate Match Iori Yagami', 4897072871296, 139.00, 10, '2020-02-14', 'Figurine Iori Yagami articulée à l´échelle 1/12 avec accessoires et parties interchangeables. Modèle très détaillé livré en emballage boîte-fenêtre de collection.\r\n\r\n[Détails du contenu]\r\n\r\n・ Iori Yagami (environ 18,1 cm)\r\n\r\n・ Pièces de tête x 4\r\n\r\n・ Pièces à main x 5 paires\r\n\r\n・ \"108 Shiki Yami Barai\" Effet\r\n\r\n・ \"100 Shiki Oni Yaki\" Effet\r\n\r\n・ \"Flame\" Effet\r\n\r\n・ \"1/4 part of Omega Rugal Gravity Smash\" Effet\r\n\r\nFabricant : Storm Collectibles', '/importgames/images/produit/8861_24-10-2020.jpg', NULL, '2020-04-29'),
(18, 3, 'Bloc-notes de terrain style Nerv \"Rebuild of Evangelion\"', 4546098038311, 14.90, 5, '2013-03-31', 'Un mémo avec une excellente résistance à l\'eau au design de la NERV est maintenant disponible !\r\nIl s\'agit d\'un bloc-notes de papier de pierre appelé \"Keeplus\" contenant 80% de carbonate de calcium. Ne pas utiliser de fibres cellulosiques végétales et utiliser du calcaire, riche en minéraux, est également efficace pour protéger l\'environnement. La compatibilité avec les stylos à bille est élevée et vu qu\'il n\'y a pas d\'orientation du papier, l\'écriture est donc très fluide. Il est également très résistant aux éclaboussures et aux déchirures.\r\n\r\n[Détails du produit]\r\n\r\n■ Taille : Dimensions extérieures 130 mm (H) x 85 mm (L)\r\n\r\n■ Matériel : Papier de pierre\r\n\r\n■ Nombre de feuilles : 50\r\n\r\n■ Il est facile d\'écrire sur ce support avec des stylos à bille, des feutres, des pinceaux ou des crayons de couleur.\r\n* Veuillez noter que si vous écrivez avec un stylo à base d\'eau, le contenu peut se décolorer s\'il est mouillé.\r\n\r\nFabricant : TMB Corporation', '/importgames/images/produit/7424_24-10-2020.jpg', NULL, '2020-04-29'),
(19, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `recherche`
--

CREATE TABLE `recherche` (
  `id` int(11) NOT NULL,
  `texte` text,
  `user_id` int(11) DEFAULT NULL,
  `date_rec` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `recherche`
--

INSERT INTO `recherche` (`id`, `texte`, `user_id`, `date_rec`) VALUES
(1, 'test', 4, '2020-07-17 14:59:22'),
(2, 'miku', 0, '2020-07-19 09:39:23'),
(3, 'miku', 1, '2020-07-19 10:19:11'),
(4, 'miku', 1, '2020-07-19 10:32:13'),
(5, 'miku', 1, '2020-07-19 10:35:12'),
(6, 'miku', 1, '2020-07-19 10:35:18'),
(7, 'miku', 1, '2020-07-19 10:47:19'),
(8, '', 1, '2020-07-19 10:47:37'),
(9, '', 1, '2020-07-19 10:50:48'),
(10, '', 1, '2020-07-19 10:50:51'),
(11, '', 1, '2020-07-19 10:52:22'),
(12, 'test', 1, '2020-07-19 10:52:53'),
(13, 'miku', 1, '2020-07-19 10:52:58'),
(14, '', 1, '2020-07-19 10:53:01'),
(15, 'miku', 1, '2020-07-19 10:54:28'),
(16, 'miku', 1, '2020-07-19 10:54:35'),
(17, 'miku', 1, '2020-07-19 10:54:41'),
(18, 'alert(\"Texte XSS\")', 1, '2020-07-19 10:55:03'),
(19, '', 1, '2020-07-19 10:55:52'),
(20, 'miku', 1, '2020-07-19 10:55:57'),
(21, '', 1, '2020-07-19 10:56:21'),
(22, 'test', 1, '2020-07-19 10:56:26'),
(23, 'miku', 1, '2020-07-19 10:56:47'),
(24, '', 1, '2020-07-19 10:57:35'),
(25, 'test', 1, '2020-07-19 10:57:53'),
(26, '', 1, '2020-07-19 10:58:23'),
(27, 'miku', 1, '2020-07-19 11:13:09'),
(28, '', 1, '2020-07-19 11:15:44'),
(29, '', 1, '2020-07-19 11:15:52'),
(30, '', 1, '2020-07-19 11:35:32'),
(31, '', 1, '2020-07-19 11:41:13'),
(32, 'test', 1, '2020-07-21 15:21:30'),
(33, '', 1, '2020-07-21 15:21:33'),
(34, 'test', 1, '2020-07-21 15:22:49'),
(35, 'miku', 1, '2020-07-27 19:06:17'),
(36, 'test', 0, '2020-08-30 09:58:42'),
(37, '', 0, '2020-09-07 10:36:46'),
(38, 'dragon ball', 3, '2020-09-10 16:34:33'),
(39, '', 3, '2020-09-11 10:09:48'),
(40, '*', 3, '2020-09-11 10:09:53'),
(41, 'evangelion', 3, '2020-09-13 15:28:56'),
(42, '', 1, '2020-09-16 11:02:17'),
(43, '', 1, '2020-09-16 11:03:15'),
(44, '', 1, '2020-09-16 11:03:23'),
(45, '', 1, '2020-09-17 17:23:20'),
(46, 'miku', 1, '2020-09-18 10:37:51'),
(47, '', 1, '2020-09-18 11:53:54'),
(48, '', 1, '2020-09-18 15:49:09'),
(49, 'miku', 1, '2020-09-18 15:54:53'),
(50, 'miku', 1, '2020-09-18 16:02:21'),
(51, 'miku', 1, '2020-09-18 16:09:51'),
(52, '', 1, '2020-09-18 16:12:15'),
(53, '', 1, '2020-09-18 16:12:34'),
(54, '', 1, '2020-09-18 16:20:21'),
(55, 'astral', 1, '2020-09-18 16:20:31'),
(56, '', 1, '2020-09-18 16:28:35'),
(57, 'miku', 1, '2020-09-18 16:29:18'),
(58, 'astral', 1, '2020-09-18 16:29:25'),
(59, 'test', 1, '2020-09-18 16:31:16'),
(60, '', 1, '2020-09-18 16:33:21'),
(61, 'test', 1, '2020-09-18 16:33:41'),
(62, 'astral', 1, '2020-09-18 16:33:57'),
(63, 'astral', 1, '2020-09-18 16:34:31'),
(64, 'astral', 1, '2020-09-18 16:34:55'),
(65, '', 1, '2020-09-18 16:37:00'),
(66, 'test', 1, '2020-09-18 16:37:04'),
(67, 'test', 1, '2020-09-18 16:42:41'),
(68, '', 1, '2020-09-18 16:42:46'),
(69, '', 1, '2020-09-18 17:00:23'),
(70, 'astral', 1, '2020-09-18 17:00:31'),
(71, '', 1, '2020-09-18 17:23:35'),
(72, 'eva', 1, '2020-09-18 17:39:47'),
(73, 'ulysse', 1, '2020-09-20 19:59:58'),
(74, '', 1, '2020-09-20 20:00:04'),
(75, '', 1, '2020-09-23 19:09:46'),
(76, 'miku', 1, '2020-09-29 11:00:51'),
(77, '', 1, '2020-09-29 15:28:29'),
(78, '', 1, '2020-09-29 15:28:41'),
(79, '994947711001 ', 1, '2020-09-29 17:53:45'),
(80, '994947711001', 1, '2020-09-29 17:53:56'),
(81, 'rem', 1, '2020-09-29 17:54:00'),
(82, 'yubari', 1, '2020-10-02 11:09:40'),
(83, 'Test', 1, '2020-10-12 08:58:55'),
(84, '&lt;strong&gt;Test&lt;/strong&gt;', 1, '2020-10-12 08:59:22'),
(85, 'Test', 1, '2020-10-12 08:59:36'),
(86, '', 1, '2020-10-12 09:01:12'),
(87, '', 1, '2020-10-12 13:14:59'),
(88, '', 1, '2020-10-12 18:07:07'),
(89, 'Hatsune Miku', 1, '2020-10-19 14:26:36'),
(90, '', 1, '2020-10-24 11:41:24'),
(91, '', 3, '2020-10-25 16:38:07'),
(92, 'zack', 6, '2020-12-14 17:08:30'),
(93, 'stylo', 4, '2020-12-17 17:31:15'),
(94, '324', 1, '2020-12-17 22:57:00'),
(95, 'astral', 1, '2020-12-18 11:02:10'),
(96, 'shoot', 1, '2020-12-20 17:28:05');

-- --------------------------------------------------------

--
-- Structure de la table `slider`
--

CREATE TABLE `slider` (
  `id` int(11) NOT NULL,
  `titre` text,
  `legende` text,
  `image` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `slider`
--

INSERT INTO `slider` (`id`, `titre`, `legende`, `image`) VALUES
(1, 'Jeux vidéo', 'Import Nintendo Switch', '/importgames/images/slider/H2x1_NSwitch_AstralChain_image1600w.jpg'),
(2, 'Figurines', 'Premium Limitées', '/importgames/images/slider/DBS-Broly-Banniere-CHIBIAKI-bis_86889.png'),
(3, 'Papeterie', 'Fantaisie Japonaise', '/importgames/images/slider/g45854_01.png'),
(4, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `email` text NOT NULL,
  `password` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `nom` text,
  `prenom` text,
  `adresse` text,
  `code_postal` int(11) DEFAULT NULL,
  `ville` text,
  `pays` text,
  `tel` text,
  `date_reg` date NOT NULL,
  `token` text,
  `conf_account` datetime DEFAULT NULL,
  `token_stayco` text,
  `new_pass` int(11) DEFAULT '0',
  `date_unsub` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `username`, `email`, `password`, `nom`, `prenom`, `adresse`, `code_postal`, `ville`, `pays`, `tel`, `date_reg`, `token`, `conf_account`, `token_stayco`, `new_pass`, `date_unsub`) VALUES
(1, 'Satô', 'contact@orange.fr', '$argon2i$v=19$m=1024,t=2,p=2$M1lkQlRzTGtld1NyTjF1TQ$eLLHGfrh7Hflpxkg7loOGSzZvj3jJCxrEZZdotvzOGQ', 'Lemaître', 'Ludovic', '8 Résidence du Parc', 94450, 'LIMEIL-BRÉVANNES', 'FR', '06 73 78 24 75', '2020-04-30', NULL, '2020-04-30 16:25:24', '1f83c73baf23a3ef3f5039ac', 0, NULL),
(2, 'gfLME', 'guy-francois.leconte@sfr.fr', '$argon2i$v=19$m=65536,t=4,p=1$aG1JaUFpZlRtdXpHYzF1cA$0JIbKpqutebtk/zR3hGls+yEekpM2ReMgfu+hBk9J0I', 'leconte', 'guy françois', '5 rue de la gare', 58270, 'BEAUMONT-SARDOLLES', 'FR', '0180425424', '2020-05-01', NULL, '2020-05-01 11:10:13', NULL, 0, NULL),
(3, 'Sôsôginka', 'shooting.star@orange.fr', '$argon2i$v=19$m=65536,t=4,p=1$WmFiZFQ5Q2RaQXZSblp2cg$KgMqPfh1ao2nA0c2xvYanOf7X4R/cRob7jxgWVH4gCo', 'Beld', 'Ashram', '85 rue du Trocadéro', 94000, 'CRÉTEIL', 'FR', '07.55.55.55.55', '2020-05-02', NULL, '2020-05-02 12:03:14', '53fed0b1c37de468e6475a58', 0, NULL),
(4, 'Ryoga', 'ryoga.hibiki@orange.fr', '$argon2i$v=19$m=1024,t=2,p=2$dFJzaU8vUE5hdnFVc29oMw$Mfm4nHAhzvfIWXcpire9aZ42OyrW/0Dl9k1aTep6zjU', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-05-08', NULL, '2020-06-29 17:02:33', '6fcbb1c189824b3bf5819d84', 0, NULL),
(5, 'Pascal', 'pascalikari@free.fr', '$argon2i$v=19$m=65536,t=4,p=1$NDVnNkpUZlFIaXNrTmtkUw$kpjfHUt+pNMjTkaLQu5YjLKa8EwvsCurRylLMAWamvI', 'Ikari', 'Pascal', '59 allée du Pacifique', 92007, 'BAGNEUX', 'FR', '0623589788', '2020-05-13', NULL, '2020-05-13 18:53:04', NULL, 0, NULL),
(6, '', '', '', 'Pro', 'Testeur', '46 Rue René Clair', 75018, 'Paris', 'FR', '01 75 43 42 42', '2020-12-14', NULL, NULL, NULL, NULL, '2020-12-14 17:12:59');

-- --------------------------------------------------------

--
-- Structure de la table `visite_prod`
--

CREATE TABLE `visite_prod` (
  `id` int(11) NOT NULL,
  `produit_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date_v` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `visite_prod`
--

INSERT INTO `visite_prod` (`id`, `produit_id`, `user_id`, `date_v`) VALUES
(1, 1, 0, '2020-04-26 10:00:47'),
(2, 1, 0, '2020-07-09 10:08:56'),
(3, 4, 0, '2020-07-09 10:13:24'),
(4, 4, 0, '2020-07-09 10:13:58'),
(5, 8, 1, '2020-07-09 10:24:29'),
(6, 7, 0, '2020-07-09 16:23:15'),
(7, 1, 0, '2020-07-09 16:23:17'),
(8, 1, 0, '2020-07-10 09:58:29'),
(9, 7, 0, '2020-07-10 09:58:31'),
(10, 7, 0, '2020-07-10 09:58:46'),
(11, 7, 0, '2020-07-10 09:59:09'),
(12, 7, 0, '2020-07-10 09:59:11'),
(13, 7, 0, '2020-07-10 09:59:18'),
(14, 7, 0, '2020-07-10 09:59:20'),
(15, 7, 0, '2020-07-10 09:59:30'),
(16, 7, 0, '2020-07-10 09:59:31'),
(17, 7, 0, '2020-07-10 09:59:37'),
(18, 7, 0, '2020-07-10 09:59:41'),
(19, 7, 0, '2020-07-10 09:59:52'),
(20, 7, 0, '2020-07-10 09:59:53'),
(21, 7, 0, '2020-07-10 10:00:24'),
(22, 7, 0, '2020-07-10 10:00:24'),
(23, 7, 0, '2020-07-10 10:00:31'),
(24, 7, 0, '2020-07-10 10:00:32'),
(25, 7, 0, '2020-07-10 10:00:34'),
(26, 1, 0, '2020-07-10 10:01:07'),
(27, 1, 0, '2020-07-10 10:01:09'),
(28, 7, 0, '2020-07-10 10:07:16'),
(29, 7, 0, '2020-07-10 10:07:18'),
(30, 7, 0, '2020-07-10 10:07:21'),
(31, 7, 0, '2020-07-10 10:07:46'),
(32, 7, 0, '2020-07-10 10:07:50'),
(33, 1, 0, '2020-07-10 10:08:41'),
(34, 1, 0, '2020-07-10 10:08:42'),
(35, 4, 0, '2020-07-10 10:08:54'),
(36, 4, 0, '2020-07-10 10:09:03'),
(37, 4, 0, '2020-07-10 10:09:05'),
(38, 4, 0, '2020-07-10 10:09:28'),
(39, 4, 0, '2020-07-10 10:09:32'),
(40, 4, 0, '2020-07-10 10:09:48'),
(41, 4, 0, '2020-07-10 10:09:49'),
(42, 7, 0, '2020-07-10 10:09:55'),
(43, 7, 0, '2020-07-10 10:10:08'),
(44, 7, 0, '2020-07-10 10:10:09'),
(45, 7, 0, '2020-07-10 10:12:04'),
(46, 7, 0, '2020-07-10 10:12:06'),
(47, 1, 0, '2020-07-10 10:12:28'),
(48, 1, 0, '2020-07-10 10:12:29'),
(49, 1, 0, '2020-07-10 10:12:32'),
(50, 7, 0, '2020-07-10 10:13:19'),
(51, 1, 0, '2020-07-10 10:13:21'),
(52, 18, 4, '2020-07-17 13:59:15'),
(53, 14, 4, '2020-07-17 15:15:01'),
(54, 14, 4, '2020-07-17 15:15:12'),
(55, 1, 4, '2020-07-17 15:20:41'),
(56, 6, 0, '2020-07-21 15:19:12'),
(57, 5, 1, '2020-07-21 15:22:39'),
(58, 1, 1, '2020-07-21 15:22:43'),
(59, 2, 1, '2020-07-21 15:22:56'),
(60, 1, 3, '2020-07-23 13:41:28'),
(61, 1, 3, '2020-07-23 13:59:09'),
(62, 1, 3, '2020-07-23 13:59:11'),
(63, 3, 1, '2020-07-24 14:58:06'),
(64, 4, 1, '2020-07-24 14:58:11'),
(65, 5, 1, '2020-07-24 14:58:12'),
(66, 10, 1, '2020-07-24 14:58:23'),
(67, 10, 1, '2020-07-24 14:58:30'),
(68, 10, 1, '2020-07-24 14:58:32'),
(69, 6, 1, '2020-07-24 14:58:48'),
(70, 12, 0, '2020-08-01 10:33:14'),
(71, 16, 0, '2020-08-01 10:34:00'),
(72, 13, 0, '2020-08-02 13:49:39'),
(73, 8, 0, '2020-08-02 13:49:41'),
(74, 1, 0, '2020-08-02 13:49:55'),
(75, 8, 0, '2020-08-02 13:52:41'),
(76, 13, 0, '2020-08-02 13:52:48'),
(77, 1, 0, '2020-08-02 13:52:53'),
(78, 18, 0, '2020-08-02 17:35:36'),
(79, 11, 1, '2020-08-03 14:52:50'),
(80, 18, 1, '2020-08-03 15:03:01'),
(81, 17, 1, '2020-08-03 17:48:10'),
(82, 7, 1, '2020-08-03 17:48:12'),
(83, 8, 1, '2020-08-03 17:48:19'),
(84, 10, 1, '2020-08-03 17:48:34'),
(85, 13, 1, '2020-08-03 18:18:21'),
(86, 14, 1, '2020-08-03 18:18:22'),
(87, 11, 1, '2020-08-03 18:18:40'),
(88, 15, 1, '2020-08-03 18:18:41'),
(89, 9, 1, '2020-08-03 18:23:21'),
(90, 17, 1, '2020-08-03 18:23:22'),
(91, 3, 1, '2020-08-03 18:23:33'),
(92, 18, 1, '2020-08-03 18:23:35'),
(93, 18, 1, '2020-08-03 18:23:48'),
(94, 18, 1, '2020-08-03 18:24:37'),
(95, 9, 1, '2020-08-03 18:24:43'),
(96, 18, 0, '2020-08-03 18:24:58'),
(97, 9, 1, '2020-08-03 18:25:25'),
(98, 18, 1, '2020-08-03 18:25:33'),
(99, 2, 1, '2020-08-03 18:26:18'),
(100, 18, 1, '2020-08-03 18:26:44'),
(101, 18, 1, '2020-08-03 18:27:33'),
(102, 18, 1, '2020-08-03 18:27:46'),
(103, 18, 1, '2020-08-03 18:42:04'),
(104, 4, 1, '2020-08-03 18:42:10'),
(105, 17, 1, '2020-08-03 18:42:16'),
(106, 15, 1, '2020-08-03 18:42:20'),
(107, 15, 1, '2020-08-03 18:42:25'),
(108, 6, 1, '2020-08-03 18:43:10'),
(109, 8, 1, '2020-08-03 18:43:13'),
(110, 7, 1, '2020-08-03 18:44:06'),
(111, 1, 1, '2020-08-03 18:45:57'),
(112, 14, 1, '2020-08-03 18:46:18'),
(113, 1, 1, '2020-08-03 18:50:32'),
(114, 14, 1, '2020-08-03 18:50:37'),
(115, 13, 0, '2020-08-06 10:51:40'),
(116, 16, 1, '2020-08-06 13:45:11'),
(117, 7, 1, '2020-08-06 13:45:15'),
(118, 16, 1, '2020-08-06 13:45:22'),
(119, 16, 1, '2020-08-06 13:53:24'),
(120, 16, 1, '2020-08-06 13:53:27'),
(121, 12, 1, '2020-08-06 13:56:50'),
(122, 12, 1, '2020-08-06 14:01:31'),
(123, 7, 1, '2020-08-06 14:02:03'),
(124, 7, 1, '2020-08-06 14:02:33'),
(125, 7, 1, '2020-08-06 14:02:35'),
(126, 7, 1, '2020-08-06 14:02:49'),
(127, 7, 1, '2020-08-06 14:18:46'),
(128, 7, 1, '2020-08-06 15:03:47'),
(129, 10, 1, '2020-08-06 15:03:50'),
(130, 10, 1, '2020-08-06 15:15:17'),
(131, 3, 1, '2020-08-06 15:23:41'),
(132, 3, 1, '2020-08-06 15:25:35'),
(133, 6, 1, '2020-08-14 16:40:45'),
(134, 4, 1, '2020-08-16 16:22:17'),
(135, 18, 0, '2020-08-19 11:52:23'),
(136, 16, 0, '2020-08-19 12:12:49'),
(137, 18, 0, '2020-08-19 12:30:27'),
(138, 1, 0, '2020-08-19 17:43:29'),
(139, 17, 4, '2020-08-20 10:29:50'),
(140, 17, 4, '2020-08-20 10:30:48'),
(141, 17, 4, '2020-08-20 10:33:40'),
(142, 17, 0, '2020-08-20 11:27:01'),
(143, 1, 1, '2020-08-23 17:32:45'),
(144, 7, 1, '2020-08-26 17:16:03'),
(145, 1, 1, '2020-08-26 17:16:12'),
(146, 5, 0, '2020-08-30 14:01:25'),
(147, 1, 0, '2020-08-30 14:01:31'),
(148, 11, 0, '2020-08-30 20:49:06'),
(149, 2, 0, '2020-08-30 20:49:11'),
(150, 3, 0, '2020-08-30 20:49:31'),
(151, 4, 0, '2020-08-30 20:49:49'),
(152, 16, 0, '2020-08-30 20:49:53'),
(153, 7, 0, '2020-08-30 21:07:17'),
(154, 11, 0, '2020-08-30 21:13:56'),
(155, 5, 0, '2020-08-30 21:14:23'),
(156, 1, 0, '2020-08-30 21:17:00'),
(157, 5, 0, '2020-08-30 21:17:05'),
(158, 6, 0, '2020-08-30 21:17:18'),
(159, 8, 0, '2020-08-30 21:17:21'),
(160, 11, 0, '2020-08-30 21:17:57'),
(161, 9, 0, '2020-08-30 21:59:07'),
(162, 9, 0, '2020-08-30 21:59:17'),
(163, 12, 0, '2020-08-30 21:59:21'),
(164, 12, 0, '2020-08-30 22:09:41'),
(165, 12, 0, '2020-08-30 22:13:44'),
(166, 12, 0, '2020-08-30 22:14:07'),
(167, 10, 0, '2020-08-30 22:20:32'),
(168, 12, 0, '2020-08-30 22:20:36'),
(169, 13, 0, '2020-08-31 09:29:46'),
(170, 13, 0, '2020-08-31 09:29:53'),
(171, 4, 0, '2020-08-31 09:29:59'),
(172, 18, 0, '2020-09-07 10:35:41'),
(173, 5, 1, '2020-09-07 14:44:24'),
(174, 5, 1, '2020-09-07 14:45:07'),
(175, 5, 1, '2020-09-07 14:45:21'),
(176, 4, 1, '2020-09-08 12:04:10'),
(177, 17, 1, '2020-09-08 12:04:14'),
(178, 4, 1, '2020-09-08 12:16:18'),
(179, 15, 1, '2020-09-08 13:51:53'),
(180, 4, 1, '2020-09-08 14:05:52'),
(181, 5, 1, '2020-09-09 10:49:08'),
(182, 5, 1, '2020-09-09 10:49:11'),
(183, 5, 1, '2020-09-09 11:35:41'),
(184, 9, 3, '2020-09-14 16:50:53'),
(185, 1, 3, '2020-09-14 16:50:55'),
(186, 1, 3, '2020-09-14 16:52:55'),
(187, 1, 3, '2020-09-15 12:09:09'),
(188, 1, 3, '2020-09-15 12:26:30'),
(189, 1, 3, '2020-09-15 12:27:13'),
(190, 1, 3, '2020-09-15 12:27:18'),
(191, 1, 3, '2020-09-15 12:27:53'),
(192, 1, 3, '2020-09-15 12:29:27'),
(193, 1, 3, '2020-09-15 12:29:41'),
(194, 1, 3, '2020-09-15 12:30:19'),
(195, 7, 3, '2020-09-16 09:50:03'),
(196, 8, 3, '2020-09-16 09:56:54'),
(197, 9, 3, '2020-09-16 10:00:48'),
(198, 10, 3, '2020-09-16 10:08:21'),
(199, 11, 3, '2020-09-16 10:11:25'),
(200, 12, 3, '2020-09-16 10:14:57'),
(201, 13, 3, '2020-09-16 10:17:46'),
(202, 14, 3, '2020-09-16 10:21:42'),
(203, 15, 3, '2020-09-16 10:24:31'),
(204, 16, 3, '2020-09-16 10:27:21'),
(205, 17, 3, '2020-09-16 10:30:03'),
(206, 18, 3, '2020-09-16 10:34:40'),
(207, 1, 4, '2020-09-16 10:48:57'),
(208, 2, 1, '2020-09-16 10:57:33'),
(209, 14, 1, '2020-09-16 10:59:14'),
(210, 7, 1, '2020-09-16 16:41:44'),
(211, 10, 1, '2020-09-16 16:41:48'),
(212, 13, 1, '2020-09-16 16:42:18'),
(213, 7, 1, '2020-09-16 17:20:13'),
(214, 10, 1, '2020-09-16 18:32:14'),
(215, 4, 1, '2020-09-16 19:12:58'),
(216, 10, 1, '2020-09-16 19:31:51'),
(217, 4, 1, '2020-09-16 19:31:54'),
(218, 10, 1, '2020-09-17 14:30:20'),
(219, 6, 1, '2020-09-17 14:33:03'),
(220, 10, 1, '2020-09-17 15:40:18'),
(221, 13, 1, '2020-09-17 15:40:21'),
(222, 8, 1, '2020-09-17 17:42:07'),
(223, 17, 1, '2020-09-21 15:50:08'),
(224, 17, 1, '2020-09-21 15:51:21'),
(225, 11, 1, '2020-09-21 15:51:49'),
(226, 12, 1, '2020-09-22 10:19:26'),
(227, 12, 3, '2020-09-22 10:23:17'),
(228, 12, 0, '2020-09-22 10:24:08'),
(229, 8, 1, '2020-09-22 10:46:20'),
(230, 17, 1, '2020-09-22 13:00:50'),
(231, 2, 1, '2020-09-23 14:14:10'),
(232, 15, 1, '2020-09-23 15:23:42'),
(233, 12, 1, '2020-09-23 15:23:51'),
(234, 10, 1, '2020-09-23 15:23:55'),
(235, 8, 1, '2020-09-23 15:24:08'),
(236, 18, 1, '2020-09-23 19:07:19'),
(237, 18, 1, '2020-09-23 19:10:24'),
(238, 8, 1, '2020-09-24 20:16:40'),
(239, 10, 1, '2020-09-24 20:16:42'),
(240, 7, 1, '2020-09-24 20:16:57'),
(241, 8, 1, '2020-09-25 16:12:18'),
(242, 17, 1, '2020-09-25 16:27:20'),
(243, 14, 1, '2020-09-29 17:17:10'),
(244, 13, 1, '2020-10-03 16:18:48'),
(245, 1, 1, '2020-10-03 16:19:30'),
(246, 16, 1, '2020-10-03 16:19:39'),
(247, 8, 1, '2020-10-03 16:19:47'),
(248, 2, 1, '2020-10-03 16:19:49'),
(249, 6, 1, '2020-10-03 16:19:56'),
(250, 18, 1, '2020-10-03 16:20:01'),
(251, 3, 1, '2020-10-03 16:20:43'),
(252, 10, 1, '2020-10-06 18:03:23'),
(253, 7, 1, '2020-10-06 18:03:30'),
(254, 13, 1, '2020-10-06 18:05:07'),
(255, 7, 1, '2020-10-06 20:30:35'),
(256, 10, 1, '2020-10-06 20:36:23'),
(257, 13, 1, '2020-10-06 20:36:28'),
(258, 7, 1, '2020-10-06 20:36:29'),
(259, 17, 1, '2020-10-07 11:19:43'),
(260, 1, 1, '2020-10-08 15:14:41'),
(261, 8, 1, '2020-10-08 16:47:18'),
(262, 1, 1, '2020-10-08 16:48:00'),
(263, 7, 1, '2020-10-08 17:57:45'),
(264, 9, 1, '2020-10-08 17:57:57'),
(265, 7, 1, '2020-10-08 17:59:30'),
(266, 1, 1, '2020-10-08 18:10:04'),
(267, 7, 1, '2020-10-08 18:10:06'),
(268, 9, 1, '2020-10-08 18:10:10'),
(269, 1, 1, '2020-10-08 18:12:35'),
(270, 7, 1, '2020-10-08 18:13:03'),
(271, 1, 1, '2020-10-08 18:13:18'),
(272, 9, 1, '2020-10-08 18:13:30'),
(273, 9, 1, '2020-10-08 18:14:33'),
(274, 1, 1, '2020-10-08 18:43:36'),
(275, 7, 1, '2020-10-08 18:43:59'),
(276, 9, 1, '2020-10-08 18:44:05'),
(277, 7, 1, '2020-10-08 18:44:13'),
(278, 12, 1, '2020-10-08 19:07:39'),
(279, 9, 1, '2020-10-08 19:10:37'),
(280, 15, 1, '2020-10-08 19:10:39'),
(281, 1, 1, '2020-10-08 19:11:10'),
(282, 7, 1, '2020-10-08 19:12:59'),
(283, 7, 1, '2020-10-08 19:13:05'),
(284, 13, 1, '2020-10-08 19:28:41'),
(285, 16, 1, '2020-10-08 19:28:55'),
(286, 13, 1, '2020-10-08 19:29:23'),
(287, 13, 1, '2020-10-08 20:29:12'),
(288, 13, 1, '2020-10-08 20:32:03'),
(289, 7, 1, '2020-10-08 21:11:48'),
(290, 15, 1, '2020-10-08 21:12:19'),
(291, 3, 1, '2020-10-09 09:45:36'),
(292, 7, 1, '2020-10-09 09:46:21'),
(293, 10, 1, '2020-10-09 09:46:52'),
(294, 7, 1, '2020-10-09 09:53:20'),
(295, 7, 1, '2020-10-09 10:32:06'),
(296, 7, 1, '2020-10-09 10:32:26'),
(297, 7, 1, '2020-10-09 10:33:30'),
(298, 7, 1, '2020-10-09 10:52:34'),
(299, 7, 1, '2020-10-09 11:50:06'),
(300, 10, 1, '2020-10-09 16:33:03'),
(301, 1, 1, '2020-10-09 16:33:07'),
(302, 7, 1, '2020-10-09 17:33:42'),
(303, 7, 1, '2020-10-09 17:35:41'),
(304, 7, 1, '2020-10-09 17:36:24'),
(305, 1, 1, '2020-10-09 17:37:20'),
(306, 7, 1, '2020-10-09 17:42:10'),
(307, 7, 1, '2020-10-09 17:42:23'),
(308, 7, 1, '2020-10-09 17:47:00'),
(309, 7, 1, '2020-10-09 17:47:16'),
(310, 7, 1, '2020-10-09 17:47:43'),
(311, 1, 1, '2020-10-09 17:53:53'),
(312, 7, 1, '2020-10-09 18:10:38'),
(313, 1, 1, '2020-10-09 18:12:30'),
(314, 7, 1, '2020-10-09 18:12:35'),
(315, 7, 1, '2020-10-09 18:17:12'),
(316, 1, 1, '2020-10-09 18:17:47'),
(317, 7, 1, '2020-10-09 18:19:11'),
(318, 7, 1, '2020-10-09 18:19:39'),
(319, 1, 1, '2020-10-09 18:27:06'),
(320, 1, 1, '2020-10-09 19:06:42'),
(321, 7, 1, '2020-10-09 19:06:45'),
(322, 1, 1, '2020-10-09 19:20:37'),
(323, 1, 0, '2020-10-09 19:25:02'),
(324, 7, 0, '2020-10-09 19:25:04'),
(325, 1, 0, '2020-10-09 19:27:23'),
(326, 7, 0, '2020-10-09 19:27:45'),
(327, 7, 0, '2020-10-09 19:53:58'),
(328, 7, 0, '2020-10-09 19:54:12'),
(329, 1, 1, '2020-10-09 21:20:07'),
(330, 7, 1, '2020-10-09 21:20:15'),
(331, 4, 1, '2020-10-09 21:20:23'),
(332, 3, 0, '2020-10-10 13:19:02'),
(333, 7, 1, '2020-10-10 16:38:56'),
(334, 16, 1, '2020-10-10 16:46:49'),
(335, 16, 1, '2020-10-10 16:53:35'),
(336, 7, 1, '2020-10-10 17:27:05'),
(337, 7, 1, '2020-10-10 17:30:12'),
(338, 7, 1, '2020-10-10 17:43:41'),
(339, 7, 1, '2020-10-10 17:43:55'),
(340, 7, 1, '2020-10-10 17:46:02'),
(341, 7, 1, '2020-10-10 17:46:54'),
(342, 7, 1, '2020-10-10 17:54:27'),
(343, 7, 1, '2020-10-10 17:54:39'),
(344, 7, 1, '2020-10-10 17:57:51'),
(345, 7, 1, '2020-10-10 17:58:30'),
(346, 7, 1, '2020-10-10 18:27:02'),
(347, 7, 1, '2020-10-10 19:02:29'),
(348, 18, 1, '2020-10-10 21:05:30'),
(349, 6, 1, '2020-10-10 21:05:36'),
(350, 5, 1, '2020-10-10 21:18:19'),
(351, 7, 1, '2020-10-11 11:47:52'),
(352, 7, 1, '2020-10-11 11:48:09'),
(353, 7, 1, '2020-10-11 12:15:09'),
(354, 13, 1, '2020-10-11 12:16:05'),
(355, 17, 1, '2020-10-11 12:20:02'),
(356, 13, 1, '2020-10-11 12:23:33'),
(357, 7, 1, '2020-10-11 12:25:47'),
(358, 7, 1, '2020-10-11 12:32:40'),
(359, 7, 1, '2020-10-11 12:32:52'),
(360, 7, 1, '2020-10-11 12:36:54'),
(361, 7, 1, '2020-10-11 12:47:13'),
(362, 7, 1, '2020-10-11 12:49:22'),
(363, 7, 1, '2020-10-11 12:59:55'),
(364, 5, 1, '2020-10-11 13:00:28'),
(365, 5, 1, '2020-10-11 13:01:32'),
(366, 7, 1, '2020-10-11 13:01:46'),
(367, 5, 1, '2020-10-11 13:02:53'),
(368, 5, 1, '2020-10-11 13:04:00'),
(369, 7, 1, '2020-10-11 13:18:45'),
(370, 7, 1, '2020-10-11 13:25:24'),
(371, 7, 1, '2020-10-11 13:37:15'),
(372, 7, 1, '2020-10-11 14:04:20'),
(373, 7, 1, '2020-10-11 14:05:39'),
(374, 5, 1, '2020-10-11 14:09:51'),
(375, 7, 1, '2020-10-11 14:09:56'),
(376, 5, 1, '2020-10-11 14:47:41'),
(377, 7, 1, '2020-10-11 16:04:29'),
(378, 1, 1, '2020-10-11 16:04:44'),
(379, 5, 1, '2020-10-11 16:05:02'),
(380, 5, 1, '2020-10-11 16:07:28'),
(381, 7, 1, '2020-10-11 16:08:29'),
(382, 7, 0, '2020-10-11 16:11:35'),
(383, 7, 0, '2020-10-11 16:17:03'),
(384, 5, 1, '2020-10-11 16:29:08'),
(385, 7, 1, '2020-10-11 16:33:28'),
(386, 5, 1, '2020-10-11 16:33:29'),
(387, 7, 1, '2020-10-11 16:33:41'),
(388, 7, 1, '2020-10-12 08:54:07'),
(389, 10, 1, '2020-10-12 13:28:57'),
(390, 13, 1, '2020-10-12 14:01:28'),
(391, 13, 1, '2020-10-12 14:02:49'),
(392, 13, 1, '2020-10-12 14:07:45'),
(393, 13, 1, '2020-10-12 15:28:09'),
(394, 13, 1, '2020-10-12 15:34:01'),
(395, 13, 1, '2020-10-12 15:40:57'),
(396, 14, 1, '2020-10-12 15:45:52'),
(397, 1, 1, '2020-10-12 17:49:18'),
(398, 7, 1, '2020-10-12 17:55:13'),
(399, 1, 1, '2020-10-12 17:59:22'),
(400, 1, 1, '2020-10-12 18:01:01'),
(401, 7, 1, '2020-10-12 18:06:05'),
(402, 7, 1, '2020-10-13 10:09:04'),
(403, 5, 1, '2020-10-13 14:01:31'),
(404, 6, 1, '2020-10-13 15:43:20'),
(405, 7, 1, '2020-10-13 18:46:05'),
(406, 1, 1, '2020-10-24 11:20:31'),
(407, 3, 1, '2020-10-24 11:22:57'),
(408, 2, 1, '2020-10-24 11:23:17'),
(409, 10, 1, '2020-10-24 12:36:00'),
(410, 13, 1, '2020-10-25 10:37:44'),
(411, 4, 1, '2020-10-25 10:38:04'),
(412, 8, 0, '2020-10-25 15:01:16'),
(413, 8, 3, '2020-10-25 15:01:53'),
(414, 8, 3, '2020-10-25 15:21:40'),
(415, 9, 3, '2020-10-25 15:22:29'),
(416, 1, 3, '2020-10-25 15:22:57'),
(417, 9, 3, '2020-10-25 15:25:12'),
(418, 7, 3, '2020-10-25 15:51:56'),
(419, 11, 3, '2020-10-25 16:13:21'),
(420, 17, 3, '2020-10-25 16:20:49'),
(421, 10, 3, '2020-10-25 16:40:25'),
(422, 15, 3, '2020-10-25 16:40:44'),
(423, 18, 1, '2020-10-25 17:17:55'),
(424, 18, 1, '2020-10-25 17:21:50'),
(425, 18, 1, '2020-10-25 17:52:16'),
(426, 1, 3, '2020-10-27 10:31:12'),
(427, 1, 3, '2020-10-27 10:36:19'),
(428, 13, 3, '2020-10-27 12:04:14'),
(429, 13, 3, '2020-10-27 14:56:13'),
(430, 15, 1, '2020-11-01 10:54:05'),
(431, 15, 1, '2020-11-01 11:10:42'),
(432, 17, 1, '2020-11-01 13:36:02'),
(433, 15, 0, '2020-11-01 13:40:18'),
(434, 15, 1, '2020-11-01 13:47:32'),
(435, 15, 1, '2020-11-01 13:49:14'),
(436, 15, 0, '2020-11-01 13:56:56'),
(437, 1, 0, '2020-11-01 13:57:39'),
(438, 3, 0, '2020-11-01 13:57:45'),
(439, 16, 1, '2020-11-04 18:54:05'),
(440, 18, 1, '2020-11-19 14:55:39'),
(441, 11, 1, '2020-11-19 15:28:19'),
(442, 19, 1, '2020-12-11 08:59:56'),
(443, 14, 1, '2020-12-11 09:19:44'),
(444, 14, 1, '2020-12-11 09:21:56'),
(445, 19, 1, '2020-12-13 11:54:00'),
(446, 1, 6, '2020-12-14 17:08:58'),
(447, 16, 1, '2020-12-17 22:57:08'),
(448, 4, 1, '2020-12-20 17:25:31'),
(449, 17, 1, '2020-12-28 11:46:39'),
(450, 18, 1, '2020-12-29 15:17:22'),
(451, 15, 3, '2020-12-29 15:23:54'),
(452, 8, 4, '2020-12-29 15:26:25'),
(453, 6, 0, '2021-01-08 18:33:24'),
(454, 1, 0, '2021-01-10 18:34:36'),
(455, 11, 1, '2021-02-09 16:51:09'),
(456, 11, 1, '2021-02-09 16:51:29'),
(457, 2, 1, '2021-02-09 16:51:38'),
(458, 11, 1, '2021-02-09 16:53:22'),
(459, 11, 1, '2021-02-09 16:56:51'),
(460, 2, 1, '2021-02-09 17:05:04'),
(461, 11, 1, '2021-02-09 17:05:24'),
(462, 16, 0, '2021-03-12 19:20:18'),
(463, 17, 0, '2021-03-14 15:17:53'),
(464, 17, 0, '2021-04-12 17:17:38'),
(465, 17, 0, '2021-04-12 18:31:01'),
(466, 12, 1, '2021-04-19 16:39:34');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `achat`
--
ALTER TABLE `achat`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `categorie`
--
ALTER TABLE `categorie`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `commande`
--
ALTER TABLE `commande`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `detail_com`
--
ALTER TABLE `detail_com`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `image`
--
ALTER TABLE `image`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `partenaire`
--
ALTER TABLE `partenaire`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `produit`
--
ALTER TABLE `produit`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `recherche`
--
ALTER TABLE `recherche`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `slider`
--
ALTER TABLE `slider`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `visite_prod`
--
ALTER TABLE `visite_prod`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `achat`
--
ALTER TABLE `achat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT pour la table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `categorie`
--
ALTER TABLE `categorie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `commande`
--
ALTER TABLE `commande`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT pour la table `detail_com`
--
ALTER TABLE `detail_com`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT pour la table `image`
--
ALTER TABLE `image`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT pour la table `partenaire`
--
ALTER TABLE `partenaire`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `produit`
--
ALTER TABLE `produit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT pour la table `recherche`
--
ALTER TABLE `recherche`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT pour la table `slider`
--
ALTER TABLE `slider`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `visite_prod`
--
ALTER TABLE `visite_prod`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=467;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
