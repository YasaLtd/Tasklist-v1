-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: 30.01.2026 klo 21:13
-- Palvelimen versio: 8.4.5-5
-- PHP Version: 8.2.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dby7ivib3lmpre`
--

-- --------------------------------------------------------

--
-- Rakenne taululle `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `action` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_id` int NOT NULL,
  `details` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Vedos taulusta `activity_log`
--

INSERT INTO `activity_log` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `created_at`) VALUES
(1, 1, 'create', 'project', 1, 'Created project: Trainbot', '2026-01-30 13:42:21'),
(2, 46, 'create', 'project', 2, 'Created project: Purge Family Full Sim', '2026-01-30 19:39:19'),
(3, 46, 'create', 'project', 3, 'Created project: Purge Club', '2026-01-30 19:41:31'),
(4, 46, 'create', 'task', 1, 'Created task: Morning shift trainer', '2026-01-30 19:42:03'),
(5, 46, 'create', 'task', 2, 'Created task: Event board setup. ', '2026-01-30 19:43:44'),
(6, 46, 'create', 'task', 3, 'Created task: Escort Rental board', '2026-01-30 19:44:09'),
(7, 46, 'create', 'task', 4, 'Created task: Update Graveyard to have all the people ', '2026-01-30 19:44:59'),
(8, 46, 'create', 'task', 5, 'Created task: Get all the DJ Gestures', '2026-01-30 19:45:31'),
(9, 46, 'create', 'task', 6, 'Created task: Create Gestures for those who don\'t have one', '2026-01-30 19:45:43'),
(10, 46, 'create', 'task', 7, 'Created task: Aquire Shift Leads', '2026-01-30 19:47:49'),
(11, 46, 'create', 'task', 8, 'Created task: Complete this task', '2026-01-30 19:48:25'),
(12, 46, 'update', 'task', 8, 'Updated task', '2026-01-30 19:48:28'),
(13, 46, 'update', 'task', 8, 'Updated task', '2026-01-30 19:48:34'),
(14, 46, 'delete', 'task', 8, 'Deleted task: Complete this task', '2026-01-30 19:48:40'),
(15, 46, 'create', 'project', 4, 'Created project: Keep Rena Alive', '2026-01-30 19:49:10'),
(16, 46, 'create', 'task', 9, 'Created task: Do your damn laundry', '2026-01-30 19:49:25'),
(17, 46, 'create', 'task', 10, 'Created task: Call the Dietition people', '2026-01-30 19:49:45');

-- --------------------------------------------------------

--
-- Rakenne taululle `invitations`
--

CREATE TABLE `invitations` (
  `id` int NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `invited_by` int DEFAULT NULL,
  `project_id` int DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Rakenne taululle `projects`
--

CREATE TABLE `projects` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('active','completed','on_hold','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `priority` enum('low','medium','high','urgent') COLLATE utf8mb4_unicode_ci DEFAULT 'medium',
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deadline` datetime DEFAULT NULL,
  `color` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#37505d'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Vedos taulusta `projects`
--

INSERT INTO `projects` (`id`, `name`, `description`, `status`, `priority`, `created_by`, `created_at`, `updated_at`, `deadline`, `color`) VALUES
(1, 'Trainbot', '', 'active', 'high', 1, '2026-01-30 13:42:21', NULL, '2026-02-08 00:00:00', '#fbff05'),
(2, 'Purge Family Full Sim', 'Keep track of the stuff needed to be ready for the full sim. ', 'active', 'high', 46, '2026-01-30 19:39:19', NULL, '2026-02-28 00:00:00', '#41973f'),
(3, 'Purge Club', '', 'active', 'medium', 46, '2026-01-30 19:41:31', NULL, '2026-05-30 00:00:00', '#37505d'),
(4, 'Keep Rena Alive', '', 'active', 'medium', 46, '2026-01-30 19:49:10', NULL, NULL, '#37505d');

-- --------------------------------------------------------

--
-- Rakenne taululle `project_users`
--

CREATE TABLE `project_users` (
  `id` int NOT NULL,
  `project_id` int NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `role` enum('viewer','editor') DEFAULT 'viewer',
  `assigned_by` bigint UNSIGNED NOT NULL,
  `assigned_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Rakenne taululle `tasks`
--

CREATE TABLE `tasks` (
  `id` int NOT NULL,
  `project_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','in_progress','completed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `priority` enum('low','medium','high','urgent') COLLATE utf8mb4_unicode_ci DEFAULT 'medium',
  `assigned_to` bigint UNSIGNED DEFAULT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deadline` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `order_index` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Vedos taulusta `tasks`
--

INSERT INTO `tasks` (`id`, `project_id`, `title`, `description`, `status`, `priority`, `assigned_to`, `created_by`, `created_at`, `updated_at`, `deadline`, `completed_at`, `order_index`) VALUES
(1, 3, 'Morning shift trainer', '', 'pending', 'medium', NULL, 46, '2026-01-30 19:42:03', NULL, '2026-12-28 00:00:00', NULL, 1),
(2, 3, 'Event board setup. ', '', 'pending', 'medium', NULL, 46, '2026-01-30 19:43:44', NULL, '2026-02-13 00:00:00', NULL, 2),
(3, 3, 'Escort Rental board', '', 'pending', 'medium', NULL, 46, '2026-01-30 19:44:09', NULL, '2026-03-31 00:00:00', NULL, 3),
(4, 2, 'Update Graveyard to have all the people ', 'Syn, Eve, steamy, star, Dae ect', 'pending', 'medium', NULL, 46, '2026-01-30 19:44:59', NULL, NULL, NULL, 1),
(5, 3, 'Get all the DJ Gestures', '', 'pending', 'medium', NULL, 46, '2026-01-30 19:45:31', NULL, NULL, NULL, 4),
(6, 3, 'Create Gestures for those who don\'t have one', '', 'pending', 'medium', NULL, 46, '2026-01-30 19:45:43', NULL, NULL, NULL, 5),
(7, 3, 'Aquire Shift Leads', 'For when we get big enough that Management can\'t attend all sets. ', 'pending', 'low', NULL, 46, '2026-01-30 19:47:49', NULL, NULL, NULL, 6),
(9, 4, 'Do your damn laundry', '', 'pending', 'medium', NULL, 46, '2026-01-30 19:49:25', NULL, NULL, NULL, 1),
(10, 4, 'Call the Dietition people', '', 'pending', 'medium', NULL, 46, '2026-01-30 19:49:45', NULL, NULL, NULL, 2);

-- --------------------------------------------------------

--
-- Rakenne taululle `task_assignments`
--

CREATE TABLE `task_assignments` (
  `id` int NOT NULL,
  `task_id` int NOT NULL,
  `user_id` int NOT NULL,
  `assigned_by` int DEFAULT NULL,
  `assigned_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Rakenne taululle `task_comments`
--

CREATE TABLE `task_comments` (
  `id` int NOT NULL,
  `task_id` int NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Rakenne taululle `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `status` enum('active','inactive','pending') DEFAULT 'active',
  `avatar_color` varchar(7) DEFAULT '#37505d',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Vedos taulusta `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `display_name`, `role`, `status`, `avatar_color`, `created_at`, `updated_at`, `last_login`) VALUES
(1, 'admin', 'info@yasa.fi', '$2y$10$8K1p/a7vLbK3sM4TS7yCXOQpQYGvGdVKwK8mA1vX6sYqQHV6Z7/Uy', 'Admin', 'admin', 'active', '#37505d', '2026-01-30 20:14:38', NULL, NULL),
(3, 'pulla', 'anteryasa@gmail.com', '$2y$10$GHVuH4rCD7PoKYwUIiKBfeXQkLhoXNclY3jxGi9OEu0OGqtWXEnH6', 'Steamy', 'admin', 'active', '#F59E0B', '2026-01-30 21:05:17', '2026-01-30 21:28:55', '2026-01-30 21:05:22'),
(4, 'pullamies', 'crawlernerot@gmail.com', '$2y$10$mh0hqA1glP.cr9DeHU/so.9yH8u19uirA/Fb1C0rPdGELo0tYEE1q', 'Steamy', 'admin', 'active', '#10B981', '2026-01-30 21:16:46', '2026-01-30 22:02:38', '2026-01-30 22:02:38'),
(5, 'sarahb13', 'sewhite0542@gmail.com', '$2y$10$f2JXYgvRYvQ4sjyHMAkEHetLI3LLycg87wMV0X9SCgBBb7jwDnQLe', 'Sarah', 'user', 'active', '#10B981', '2026-01-30 21:24:42', '2026-01-30 21:24:47', '2026-01-30 21:24:47'),
(6, 'jakobe_marques', 'jeremyyy36@gmail.com', '$2y$10$cuZ2qZ3Y0aZLnRsv9506x.VwCpEmS.LfIAanPDh.hcV60Dqudq0Sq', 'Jakobe', 'user', 'active', '#37505d', '2026-01-30 21:29:41', '2026-01-30 21:29:48', '2026-01-30 21:29:48'),
(7, 'rena', 'cora217@gmail.com', 'wordpress_auth', 'Renalynn Bob', 'admin', 'active', '#EC4899', '2026-01-30 21:32:11', NULL, '2026-01-30 21:32:11');

-- --------------------------------------------------------

--
-- Rakenne taululle `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `session_token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Vedos taulusta `user_sessions`
--

INSERT INTO `user_sessions` (`id`, `user_id`, `session_token`, `ip_address`, `user_agent`, `created_at`, `expires_at`) VALUES
(2, 46, 'b18161242b94deb1909cc8f22b38870b44c9f1d82d48db202b5fa460d05aa1fc', '173.217.219.81', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 19:32:38', '2026-01-31 20:32:38'),
(3, 3, '53e00129149b5ec9919b4d763c7b4f6dcf8235aa1bf28bc4c9bc2764fb38436c', '212.149.217.182', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 21:05:22', '2026-03-01 22:05:22'),
(5, 4, '4ac787213555f6a7502a0fa2d70eb6e5245eac9d4333b2887ba23d0617ae700a', '212.149.217.182', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 21:17:32', '2026-03-01 22:17:32'),
(6, 4, '66c73552c81e680c1a359dedbc72dac30a1cfda00d00176595f2b9c9f1e21f32', '212.149.217.182', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 21:18:09', '2026-03-01 22:18:09'),
(7, 5, 'b1e58d760d6709202418e96e2a8e2211f55a7a50b9a6509777f2fcb5d6f7fc39', '173.217.219.81', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 21:24:47', '2026-03-01 22:24:47'),
(9, 6, 'e24f81deabe37f3206d40d50bf5468eff17941dfce6a94aa6e75f64a227c3485', '98.39.219.126', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', '2026-01-30 21:29:48', '2026-03-01 22:29:48'),
(10, 7, 'de136327a460ed29e66d2e1c5ab80ff3e8ebdbee8b430a60440907ff45593ede', '173.217.219.81', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 21:32:11', '2026-03-01 22:32:11'),
(11, 4, 'a04003800c207b7e22599b6abd674d4826745c904715248cfbc5c2904b4d06d6', '212.149.217.182', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 21:40:31', '2026-03-01 22:40:31'),
(12, 4, 'c8eb3dc50bd8bd148a1cc39debcd83f5ad5e45f8780ea08d7225b2013ae2722d', '212.149.217.182', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 21:46:44', '2026-03-01 22:46:44'),
(13, 4, 'c2cf3cce7b3986447bb8f6a6446e9adf9078d443c79b9c9025410d28f745cb89', '212.149.217.182', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 21:52:58', '2026-03-01 22:52:58'),
(14, 4, 'aba0e2b782d67187bebf161a8cd152d1addda6573129273cccf0f62b0123d3c7', '212.149.217.182', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 21:54:40', '2026-03-01 22:54:40'),
(15, 4, 'd37a9ca5dd502172222277f107364fad986abc2281c04ff877aaa046e767e2ef', '212.149.217.182', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 22:02:38', '2026-03-01 23:02:38');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `invitations`
--
ALTER TABLE `invitations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_token` (`token`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_deadline` (`deadline`);

--
-- Indexes for table `project_users`
--
ALTER TABLE `project_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_project_user` (`project_id`,`user_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_project` (`project_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_project` (`project_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_assigned` (`assigned_to`),
  ADD KEY `idx_deadline` (`deadline`),
  ADD KEY `idx_order` (`order_index`);

--
-- Indexes for table `task_assignments`
--
ALTER TABLE `task_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_task_user` (`task_id`,`user_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `task_comments`
--
ALTER TABLE `task_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_task` (`task_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_username` (`username`),
  ADD UNIQUE KEY `idx_email` (`email`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_token` (`session_token`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `invitations`
--
ALTER TABLE `invitations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `project_users`
--
ALTER TABLE `project_users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `task_assignments`
--
ALTER TABLE `task_assignments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `task_comments`
--
ALTER TABLE `task_comments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Rajoitteet vedostauluille
--

--
-- Rajoitteet taululle `project_users`
--
ALTER TABLE `project_users`
  ADD CONSTRAINT `fk_pu_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Rajoitteet taululle `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `fk_task_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Rajoitteet taululle `task_assignments`
--
ALTER TABLE `task_assignments`
  ADD CONSTRAINT `fk_ta_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ta_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Rajoitteet taululle `task_comments`
--
ALTER TABLE `task_comments`
  ADD CONSTRAINT `fk_comment_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
