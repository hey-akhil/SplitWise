-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Feb 10, 2025 at 11:36 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;

SET time_zone =  "+00:00";

--
-- Database: `splitWise`
--
CREATE DATABASE IF NOT EXISTS `splitWise` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `splitWise`;

-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `encrypted_password` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `encrypted_password`, `created_at`) VALUES
(1, 'admin', 'admin@gmail.com', 'admin', '$2y$10$U/1ZDjDhUG3Ar0t3juSyE.m/QWmXKpg0HHJzfwMusl0XiY6R2IThy', '2025-02-03 06:14:54');
