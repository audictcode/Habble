-- Habble consolidated schema (up to latest migration in repository)
-- Generated from migration files on 2026-02-27
-- SQL dialect: MySQL 8+

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `campaign_info_comments`;
DROP TABLE IF EXISTS `user_daily_mission_rewards`;
DROP TABLE IF EXISTS `user_web_game_rewards`;
DROP TABLE IF EXISTS `furni_values`;
DROP TABLE IF EXISTS `user_notifications`;
DROP TABLE IF EXISTS `user_badges`;
DROP TABLE IF EXISTS `sub_navigations`;
DROP TABLE IF EXISTS `users_logs`;
DROP TABLE IF EXISTS `users_warnings`;
DROP TABLE IF EXISTS `users_bans`;
DROP TABLE IF EXISTS `articles_comments`;
DROP TABLE IF EXISTS `slides`;
DROP TABLE IF EXISTS `articles`;
DROP TABLE IF EXISTS `topics_comments`;
DROP TABLE IF EXISTS `topics`;
DROP TABLE IF EXISTS `password_resets`;
DROP TABLE IF EXISTS `failed_jobs`;
DROP TABLE IF EXISTS `campaign_infos`;
DROP TABLE IF EXISTS `daily_missions`;
DROP TABLE IF EXISTS `web_games`;
DROP TABLE IF EXISTS `furni_categories`;
DROP TABLE IF EXISTS `badges`;
DROP TABLE IF EXISTS `navigations`;
DROP TABLE IF EXISTS `articles_categories`;
DROP TABLE IF EXISTS `topics_categories`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `habbo_name` varchar(50) DEFAULT NULL,
  `habbo_hotel` varchar(15) DEFAULT NULL,
  `habbo_verification_code` varchar(20) DEFAULT NULL,
  `habbo_verified_at` timestamp NULL DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `ip_register` varchar(50) NOT NULL,
  `ip_last_login` varchar(50) NOT NULL,
  `last_login` timestamp NOT NULL,
  `birth_date` date DEFAULT NULL,
  `astros` int unsigned NOT NULL DEFAULT 0,
  `stelas` int unsigned NOT NULL DEFAULT 0,
  `lunaris` int unsigned NOT NULL DEFAULT 0,
  `cosmos` int unsigned NOT NULL DEFAULT 0,
  `web_experience` int unsigned NOT NULL DEFAULT 0,
  `profile_image_path` varchar(255) NOT NULL DEFAULT 'profiles/default.png',
  `topics_comment_count` int NOT NULL DEFAULT 0,
  `disabled` tinyint(1) NOT NULL DEFAULT 0,
  `rank` tinyint unsigned NOT NULL DEFAULT 1,
  `forum_signature` text,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_habbo_name_hotel_unique` (`habbo_name`,`habbo_hotel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `topics_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `icon` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `topics_categories_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `articles_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `icon` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `navigations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(255) NOT NULL,
  `small_icon` varchar(255) DEFAULT NULL,
  `hover_icon` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `order` smallint NOT NULL DEFAULT 0,
  `visible` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `badges` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `code` varchar(10) NOT NULL,
  `habboassets_badge_id` bigint unsigned DEFAULT NULL,
  `habboassets_hotel` varchar(8) DEFAULT NULL,
  `habboassets_source_created_at` timestamp NULL DEFAULT NULL,
  `habboassets_source_updated_at` timestamp NULL DEFAULT NULL,
  `imported_from_habboassets_at` timestamp NULL DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `rarity` enum('normal','event','promo','very','staff') NOT NULL DEFAULT 'normal',
  `content_slug` varchar(255) DEFAULT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `habbo_published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `badges_code_unique` (`code`),
  KEY `badges_code_index` (`code`),
  UNIQUE KEY `badges_habboassets_badge_id_unique` (`habboassets_badge_id`),
  KEY `badges_habboassets_hotel_index` (`habboassets_hotel`),
  KEY `badges_published_at_index` (`published_at`),
  KEY `badges_habbo_published_at_index` (`habbo_published_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `furni_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `icon` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `web_games` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text,
  `thumbnail_url` varchar(255) DEFAULT NULL,
  `game_url` varchar(255) DEFAULT NULL,
  `category` varchar(255) NOT NULL DEFAULT 'arcade',
  `game_type` varchar(255) NOT NULL DEFAULT 'external',
  `intro_text` text,
  `info_text` text,
  `option_title` varchar(255) DEFAULT NULL,
  `option_description` text,
  `option_reward_text` varchar(255) DEFAULT NULL,
  `quiz_questions` longtext,
  `published_at` timestamp NULL DEFAULT NULL,
  `participation_ends_at` timestamp NULL DEFAULT NULL,
  `xp_reward` int unsigned NOT NULL DEFAULT 0,
  `astros_reward` int unsigned NOT NULL DEFAULT 0,
  `stelas_reward` int unsigned NOT NULL DEFAULT 0,
  `lunaris_reward` int unsigned NOT NULL DEFAULT 0,
  `cosmos_reward` int unsigned NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `web_games_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `daily_missions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `intro_text` text,
  `published_at` timestamp NULL DEFAULT NULL,
  `xp_reward` int unsigned NOT NULL DEFAULT 0,
  `astros_reward` int unsigned NOT NULL DEFAULT 0,
  `stelas_reward` int unsigned NOT NULL DEFAULT 0,
  `lunaris_reward` int unsigned NOT NULL DEFAULT 0,
  `cosmos_reward` int unsigned NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `campaign_infos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT 'Información campaña',
  `slug` varchar(255) NOT NULL DEFAULT 'informacion-campana',
  `target_page` varchar(255) NOT NULL DEFAULT 'informacion-campana',
  `month_label` varchar(255) DEFAULT NULL,
  `excerpt` text,
  `banner_image_path` varchar(255) DEFAULT NULL,
  `body_html` longtext,
  `content_html` longtext,
  `info_cells` json DEFAULT NULL,
  `primary_button_text` varchar(255) DEFAULT NULL,
  `primary_button_url` varchar(255) DEFAULT NULL,
  `secondary_button_text` varchar(255) DEFAULT NULL,
  `secondary_button_url` varchar(255) DEFAULT NULL,
  `primary_button_color` varchar(255) NOT NULL DEFAULT '#0095ff',
  `secondary_button_color` varchar(255) NOT NULL DEFAULT '#1f2937',
  `use_custom_html` tinyint(1) NOT NULL DEFAULT 0,
  `created_by_user_id` bigint unsigned DEFAULT NULL,
  `author_name` varchar(255) DEFAULT NULL,
  `author_avatar_url` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `campaign_infos_slug_index` (`slug`),
  KEY `campaign_infos_target_page_index` (`target_page`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `topics` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `category_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `content` text NOT NULL,
  `moderated` enum('moderated','pending','closed') NOT NULL DEFAULT 'pending',
  `moderator` varchar(255) DEFAULT NULL,
  `slug` varchar(255) NOT NULL,
  `fixed` tinyint(1) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `comments_count` int NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `topics_category_id_foreign` (`category_id`),
  KEY `topics_user_id_foreign` (`user_id`),
  CONSTRAINT `topics_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `topics_categories` (`id`),
  CONSTRAINT `topics_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `topics_comments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `topic_id` bigint unsigned NOT NULL,
  `content` text NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `moderated` enum('moderated','pending') NOT NULL DEFAULT 'pending',
  `moderator` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `topics_comments_user_id_foreign` (`user_id`),
  KEY `topics_comments_topic_id_foreign` (`topic_id`),
  CONSTRAINT `topics_comments_topic_id_foreign` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`),
  CONSTRAINT `topics_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `articles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `category_id` bigint unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `reviewed` tinyint(1) NOT NULL DEFAULT 0,
  `reviewer` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `fixed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `articles_user_id_foreign` (`user_id`),
  KEY `articles_category_id_foreign` (`category_id`),
  CONSTRAINT `articles_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `articles_categories` (`id`),
  CONSTRAINT `articles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `slides` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 0,
  `fixed` tinyint(1) NOT NULL DEFAULT 0,
  `new_tab` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `articles_comments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `article_id` bigint unsigned NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `articles_comments_user_id_foreign` (`user_id`),
  KEY `articles_comments_article_id_foreign` (`article_id`),
  CONSTRAINT `articles_comments_article_id_foreign` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`),
  CONSTRAINT `articles_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users_bans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `admin_id` bigint unsigned NOT NULL,
  `type` enum('ip','account') NOT NULL DEFAULT 'account',
  `reason` varchar(255) DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `users_bans_user_id_foreign` (`user_id`),
  KEY `users_bans_admin_id_foreign` (`admin_id`),
  CONSTRAINT `users_bans_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`),
  CONSTRAINT `users_bans_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users_warnings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `admin_id` bigint unsigned NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `users_warnings_user_id_foreign` (`user_id`),
  KEY `users_warnings_admin_id_foreign` (`admin_id`),
  CONSTRAINT `users_warnings_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`),
  CONSTRAINT `users_warnings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `ip` varchar(255) NOT NULL,
  `content` varchar(255) NOT NULL,
  `browser` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `users_logs_user_id_foreign` (`user_id`),
  CONSTRAINT `users_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sub_navigations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `navigation_id` bigint unsigned NOT NULL,
  `label` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `new_tab` tinyint(1) NOT NULL DEFAULT 0,
  `min_rank` tinyint unsigned DEFAULT NULL,
  `order` smallint NOT NULL DEFAULT 0,
  `visible` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `sub_navigations_navigation_id_foreign` (`navigation_id`),
  CONSTRAINT `sub_navigations_navigation_id_foreign` FOREIGN KEY (`navigation_id`) REFERENCES `navigations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_badges` (
  `user_id` bigint unsigned NOT NULL,
  `badge_id` bigint unsigned NOT NULL,
  KEY `user_badges_user_id_index` (`user_id`),
  KEY `user_badges_badge_id_index` (`badge_id`),
  CONSTRAINT `user_badges_badge_id_foreign` FOREIGN KEY (`badge_id`) REFERENCES `badges` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_badges_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `to_user_id` bigint unsigned NOT NULL,
  `from_user_id` bigint unsigned NOT NULL,
  `type` enum('mention','comment','author','staff','warning','info') NOT NULL DEFAULT 'info',
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `user_saw` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_notifications_to_user_id_foreign` (`to_user_id`),
  KEY `user_notifications_from_user_id_foreign` (`from_user_id`),
  CONSTRAINT `user_notifications_from_user_id_foreign` FOREIGN KEY (`from_user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `user_notifications_to_user_id_foreign` FOREIGN KEY (`to_user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `furni_values` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `habboassets_furni_id` bigint unsigned DEFAULT NULL,
  `habboassets_hotel` varchar(16) DEFAULT NULL,
  `habboassets_source_url` text,
  `source_provider` varchar(32) DEFAULT NULL,
  `habbofurni_item_id` varchar(255) DEFAULT NULL,
  `habbofurni_imported_at` timestamp NULL DEFAULT NULL,
  `external_metadata` json DEFAULT NULL,
  `imported_from_habboassets_at` timestamp NULL DEFAULT NULL,
  `category_id` bigint unsigned NOT NULL,
  `admin_id` bigint unsigned NOT NULL,
  `price` int DEFAULT NULL,
  `price_type` enum('coins','diamonds','duckets') NOT NULL DEFAULT 'coins',
  `state` enum('up','down','regular') NOT NULL DEFAULT 'regular',
  `icon_path` varchar(255) DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `furni_values_category_id_foreign` (`category_id`),
  KEY `furni_values_admin_id_foreign` (`admin_id`),
  KEY `furni_values_habboassets_hotel_index` (`habboassets_hotel`),
  KEY `furni_values_habboassets_furni_id_index` (`habboassets_furni_id`),
  KEY `furni_values_category_id_index` (`category_id`),
  KEY `furni_values_updated_at_index` (`updated_at`),
  KEY `furni_values_category_id_updated_at_index` (`category_id`,`updated_at`),
  CONSTRAINT `furni_values_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`),
  CONSTRAINT `furni_values_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `furni_categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_web_game_rewards` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `web_game_id` bigint unsigned NOT NULL,
  `rewarded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_web_game_rewards_user_id_web_game_id_unique` (`user_id`,`web_game_id`),
  KEY `user_web_game_rewards_web_game_id_foreign` (`web_game_id`),
  CONSTRAINT `user_web_game_rewards_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_web_game_rewards_web_game_id_foreign` FOREIGN KEY (`web_game_id`) REFERENCES `web_games` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_daily_mission_rewards` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `daily_mission_id` bigint unsigned NOT NULL,
  `mission_date` date NOT NULL,
  `rewarded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_daily_mission_unique` (`user_id`,`daily_mission_id`,`mission_date`),
  KEY `user_daily_mission_rewards_daily_mission_id_foreign` (`daily_mission_id`),
  CONSTRAINT `user_daily_mission_rewards_daily_mission_id_foreign` FOREIGN KEY (`daily_mission_id`) REFERENCES `daily_missions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_daily_mission_rewards_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `campaign_info_comments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `campaign_info_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `campaign_info_comments_campaign_info_id_foreign` (`campaign_info_id`),
  KEY `campaign_info_comments_user_id_foreign` (`user_id`),
  CONSTRAINT `campaign_info_comments_campaign_info_id_foreign` FOREIGN KEY (`campaign_info_id`) REFERENCES `campaign_infos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `campaign_info_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Data changes from migration-based data seeds (idempotent)
-- ---------------------------------------------------------------------------

-- 2026_02_26_200100_seed_catalog_furni_categories.php
UPDATE `furni_categories` SET `icon` = 'fa-gem' WHERE LOWER(`name`) = LOWER('Rares');
INSERT INTO `furni_categories` (`name`, `icon`)
SELECT 'Rares', 'fa-gem'
WHERE NOT EXISTS (
  SELECT 1 FROM `furni_categories` WHERE LOWER(`name`) = LOWER('Rares')
);

UPDATE `furni_categories` SET `icon` = 'fa-couch' WHERE LOWER(`name`) = LOWER('Furnis normales');
INSERT INTO `furni_categories` (`name`, `icon`)
SELECT 'Furnis normales', 'fa-couch'
WHERE NOT EXISTS (
  SELECT 1 FROM `furni_categories` WHERE LOWER(`name`) = LOWER('Furnis normales')
);

UPDATE `furni_categories` SET `icon` = 'fa-shirt' WHERE LOWER(`name`) = LOWER('Ropa');
INSERT INTO `furni_categories` (`name`, `icon`)
SELECT 'Ropa', 'fa-shirt'
WHERE NOT EXISTS (
  SELECT 1 FROM `furni_categories` WHERE LOWER(`name`) = LOWER('Ropa')
);

UPDATE `furni_categories` SET `icon` = 'fa-paw' WHERE LOWER(`name`) = LOWER('Animales');
INSERT INTO `furni_categories` (`name`, `icon`)
SELECT 'Animales', 'fa-paw'
WHERE NOT EXISTS (
  SELECT 1 FROM `furni_categories` WHERE LOWER(`name`) = LOWER('Animales')
);

UPDATE `furni_categories` SET `icon` = 'fa-wand-magic-sparkles' WHERE LOWER(`name`) = LOWER('Efectos');
INSERT INTO `furni_categories` (`name`, `icon`)
SELECT 'Efectos', 'fa-wand-magic-sparkles'
WHERE NOT EXISTS (
  SELECT 1 FROM `furni_categories` WHERE LOWER(`name`) = LOWER('Efectos')
);

UPDATE `furni_categories` SET `icon` = 'fa-music' WHERE LOWER(`name`) = LOWER('Sonidos');
INSERT INTO `furni_categories` (`name`, `icon`)
SELECT 'Sonidos', 'fa-music'
WHERE NOT EXISTS (
  SELECT 1 FROM `furni_categories` WHERE LOWER(`name`) = LOWER('Sonidos')
);

-- 2026_02_26_140100_seed_campaign_submenus_and_info.php
INSERT INTO `campaign_infos` (
  `title`, `slug`, `month_label`, `content_html`, `active`, `published_at`, `created_at`, `updated_at`
)
SELECT
  'Información campaña',
  'informacion-campana',
  'Febrero 2026',
  '<div class="wrapper content-border"><main id="main" class="content"><article><section><header class="post-header"><h1 class="post-title">Febrero 2026 en Habbo</h1></header><div class="post-inner"><div class="post-content"><p>Contenido mensual de campaña.</p><p>Nota: Puedes pegar aquí el bloque HTML completo desde HK en "Información campaña mensual".</p></div></div></section></article></main></div>',
  1,
  CURRENT_TIMESTAMP,
  CURRENT_TIMESTAMP,
  CURRENT_TIMESTAMP
WHERE NOT EXISTS (
  SELECT 1 FROM `campaign_infos` WHERE `slug` = 'informacion-campana'
);

-- 2026_02_26_150000_ensure_campaign_submenus_under_contenidos.php
SET @contenidos_nav_id := (
  SELECT `id` FROM `navigations` WHERE LOWER(`label`) = 'contenidos' ORDER BY `id` ASC LIMIT 1
);

UPDATE `sub_navigations`
SET `navigation_id` = @contenidos_nav_id, `slug` = '/pages/noticias-campana', `visible` = 1
WHERE @contenidos_nav_id IS NOT NULL
  AND LOWER(`label`) = LOWER('Noticias campaña');

INSERT INTO `sub_navigations` (`navigation_id`, `label`, `slug`, `new_tab`, `order`, `visible`)
SELECT @contenidos_nav_id, 'Noticias campaña', '/pages/noticias-campana', 0, 10, 1
WHERE @contenidos_nav_id IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM `sub_navigations` WHERE LOWER(`label`) = LOWER('Noticias campaña')
  );

UPDATE `sub_navigations`
SET `navigation_id` = @contenidos_nav_id, `slug` = '/pages/informacion-campana', `visible` = 1
WHERE @contenidos_nav_id IS NOT NULL
  AND LOWER(`label`) = LOWER('Informacion campaña');

INSERT INTO `sub_navigations` (`navigation_id`, `label`, `slug`, `new_tab`, `order`, `visible`)
SELECT @contenidos_nav_id, 'Informacion campaña', '/pages/informacion-campana', 0, 10, 1
WHERE @contenidos_nav_id IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM `sub_navigations` WHERE LOWER(`label`) = LOWER('Informacion campaña')
  );

-- 2026_02_26_220100_add_all_news_submenu_under_contenidos.php
SET @contenidos_last_order := (
  SELECT COALESCE(MAX(`order`), 0) FROM `sub_navigations` WHERE `navigation_id` = @contenidos_nav_id
);

INSERT INTO `sub_navigations` (`navigation_id`, `label`, `slug`, `new_tab`, `min_rank`, `order`, `visible`)
SELECT @contenidos_nav_id, 'Todas las noticias', '/pages/noticias', 0, NULL, @contenidos_last_order + 1, 1
WHERE @contenidos_nav_id IS NOT NULL
  AND NOT EXISTS (
    SELECT 1
    FROM `sub_navigations`
    WHERE `navigation_id` = @contenidos_nav_id
      AND LOWER(`label`) = 'todas las noticias'
  );

-- 2026_02_26_190000_add_habbo_catalog_submenus.php
SET @habbo_nav_id := (
  SELECT `id` FROM `navigations` WHERE LOWER(`label`) = 'habbo' ORDER BY `id` ASC LIMIT 1
);

UPDATE `sub_navigations`
SET `slug` = '/pages/todos-los-furnis', `order` = 2, `visible` = 1
WHERE @habbo_nav_id IS NOT NULL
  AND `navigation_id` = @habbo_nav_id
  AND LOWER(`label`) = LOWER('Todos los Furnis');
INSERT INTO `sub_navigations` (`navigation_id`, `label`, `slug`, `new_tab`, `order`, `visible`)
SELECT @habbo_nav_id, 'Todos los Furnis', '/pages/todos-los-furnis', 0, 2, 1
WHERE @habbo_nav_id IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM `sub_navigations`
    WHERE `navigation_id` = @habbo_nav_id
      AND LOWER(`label`) = LOWER('Todos los Furnis')
  );

UPDATE `sub_navigations`
SET `slug` = '/pages/toda-la-ropa', `order` = 3, `visible` = 1
WHERE @habbo_nav_id IS NOT NULL
  AND `navigation_id` = @habbo_nav_id
  AND LOWER(`label`) = LOWER('Toda la Ropa');
INSERT INTO `sub_navigations` (`navigation_id`, `label`, `slug`, `new_tab`, `order`, `visible`)
SELECT @habbo_nav_id, 'Toda la Ropa', '/pages/toda-la-ropa', 0, 3, 1
WHERE @habbo_nav_id IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM `sub_navigations`
    WHERE `navigation_id` = @habbo_nav_id
      AND LOWER(`label`) = LOWER('Toda la Ropa')
  );

UPDATE `sub_navigations`
SET `slug` = '/pages/todos-los-rares', `order` = 4, `visible` = 1
WHERE @habbo_nav_id IS NOT NULL
  AND `navigation_id` = @habbo_nav_id
  AND LOWER(`label`) = LOWER('Todos los Rares');
INSERT INTO `sub_navigations` (`navigation_id`, `label`, `slug`, `new_tab`, `order`, `visible`)
SELECT @habbo_nav_id, 'Todos los Rares', '/pages/todos-los-rares', 0, 4, 1
WHERE @habbo_nav_id IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM `sub_navigations`
    WHERE `navigation_id` = @habbo_nav_id
      AND LOWER(`label`) = LOWER('Todos los Rares')
  );

UPDATE `sub_navigations`
SET `slug` = '/pages/todos-los-sonidos', `order` = 5, `visible` = 1
WHERE @habbo_nav_id IS NOT NULL
  AND `navigation_id` = @habbo_nav_id
  AND LOWER(`label`) = LOWER('Todos los Sonidos');
INSERT INTO `sub_navigations` (`navigation_id`, `label`, `slug`, `new_tab`, `order`, `visible`)
SELECT @habbo_nav_id, 'Todos los Sonidos', '/pages/todos-los-sonidos', 0, 5, 1
WHERE @habbo_nav_id IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM `sub_navigations`
    WHERE `navigation_id` = @habbo_nav_id
      AND LOWER(`label`) = LOWER('Todos los Sonidos')
  );

UPDATE `sub_navigations`
SET `slug` = '/pages/todos-los-animales', `order` = 6, `visible` = 1
WHERE @habbo_nav_id IS NOT NULL
  AND `navigation_id` = @habbo_nav_id
  AND LOWER(`label`) = LOWER('Todos los Animales');
INSERT INTO `sub_navigations` (`navigation_id`, `label`, `slug`, `new_tab`, `order`, `visible`)
SELECT @habbo_nav_id, 'Todos los Animales', '/pages/todos-los-animales', 0, 6, 1
WHERE @habbo_nav_id IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM `sub_navigations`
    WHERE `navigation_id` = @habbo_nav_id
      AND LOWER(`label`) = LOWER('Todos los Animales')
  );

UPDATE `sub_navigations`
SET `slug` = '/pages/todos-los-efectos', `order` = 7, `visible` = 1
WHERE @habbo_nav_id IS NOT NULL
  AND `navigation_id` = @habbo_nav_id
  AND LOWER(`label`) = LOWER('Todos los Efectos');
INSERT INTO `sub_navigations` (`navigation_id`, `label`, `slug`, `new_tab`, `order`, `visible`)
SELECT @habbo_nav_id, 'Todos los Efectos', '/pages/todos-los-efectos', 0, 7, 1
WHERE @habbo_nav_id IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM `sub_navigations`
    WHERE `navigation_id` = @habbo_nav_id
      AND LOWER(`label`) = LOWER('Todos los Efectos')
  );

-- 2026_02_26_210000_add_min_rank_to_sub_navigations_table.php
SET @radio_nav_id := (
  SELECT `id` FROM `navigations` WHERE LOWER(`label`) = 'radio' ORDER BY `id` ASC LIMIT 1
);
UPDATE `sub_navigations`
SET `min_rank` = 2
WHERE @radio_nav_id IS NOT NULL
  AND `navigation_id` = @radio_nav_id
  AND `min_rank` IS NULL;

-- 2026_02_26_230000_add_globo_suerte_to_radio_submenu.php
UPDATE `sub_navigations`
SET `slug` = 'https://www.habbo.es/room/125772597', `new_tab` = 1, `order` = 2, `visible` = 1
WHERE @radio_nav_id IS NOT NULL
  AND `navigation_id` = @radio_nav_id
  AND LOWER(`label`) = LOWER('Globo de la Suerte');

INSERT INTO `sub_navigations` (`navigation_id`, `label`, `slug`, `new_tab`, `order`, `visible`)
SELECT @radio_nav_id, 'Globo de la Suerte', 'https://www.habbo.es/room/125772597', 1, 2, 1
WHERE @radio_nav_id IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM `sub_navigations`
    WHERE `navigation_id` = @radio_nav_id
      AND LOWER(`label`) = LOWER('Globo de la Suerte')
  );

SET FOREIGN_KEY_CHECKS = 1;
