/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `admin_menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_menu` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int NOT NULL DEFAULT '0',
  `order` int NOT NULL DEFAULT '0',
  `title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uri` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `permission` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `admin_operation_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_operation_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `path` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `method` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `input` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `admin_operation_log_user_id_index` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `admin_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_permissions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `http_method` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `http_path` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `admin_permissions_name_unique` (`name`) USING BTREE,
  UNIQUE KEY `admin_permissions_slug_unique` (`slug`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `admin_role_menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_role_menu` (
  `role_id` int NOT NULL,
  `menu_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `admin_role_menu_role_id_menu_id_index` (`role_id`,`menu_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `admin_role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_role_permissions` (
  `role_id` int NOT NULL,
  `permission_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `admin_role_permissions_role_id_permission_id_index` (`role_id`,`permission_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `admin_role_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_role_users` (
  `role_id` int NOT NULL,
  `user_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `admin_role_users_role_id_user_id_index` (`role_id`,`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `admin_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_roles` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `admin_roles_name_unique` (`name`) USING BTREE,
  UNIQUE KEY `admin_roles_slug_unique` (`slug`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `admin_user_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_user_permissions` (
  `user_id` int NOT NULL,
  `permission_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `admin_user_permissions_user_id_permission_id_index` (`user_id`,`permission_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `admin_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_last_name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Фамилия',
  `user_patronymic_name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Отчество',
  `trust_number` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Номер доверенности',
  `trust_date` date DEFAULT NULL COMMENT 'Дата доверенности',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `admin_users_username_unique` (`username`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `api_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `available_sizes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `available_sizes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned DEFAULT NULL,
  `one_c_product_id` int unsigned DEFAULT NULL,
  `brand_id` bigint unsigned DEFAULT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  `stock_id` bigint unsigned DEFAULT NULL,
  `sku` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buy_price` double(8,2) NOT NULL,
  `sell_price` double(8,2) NOT NULL,
  `size_none` int unsigned NOT NULL DEFAULT '0',
  `size_31` int unsigned NOT NULL DEFAULT '0',
  `size_32` int unsigned NOT NULL DEFAULT '0',
  `size_33` int unsigned NOT NULL DEFAULT '0',
  `size_34` int unsigned NOT NULL DEFAULT '0',
  `size_35` int unsigned NOT NULL DEFAULT '0',
  `size_36` int unsigned NOT NULL DEFAULT '0',
  `size_37` int unsigned NOT NULL DEFAULT '0',
  `size_38` int unsigned NOT NULL DEFAULT '0',
  `size_39` int unsigned NOT NULL DEFAULT '0',
  `size_40` int unsigned NOT NULL DEFAULT '0',
  `size_41` int unsigned NOT NULL DEFAULT '0',
  `size_42` int unsigned NOT NULL DEFAULT '0',
  `size_43` int unsigned NOT NULL DEFAULT '0',
  `size_44` int unsigned NOT NULL DEFAULT '0',
  `size_45` int unsigned NOT NULL DEFAULT '0',
  `size_46` int unsigned NOT NULL DEFAULT '0',
  `size_47` int unsigned NOT NULL DEFAULT '0',
  `size_48` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `sizes_availabilities_product_id_index` (`product_id`) USING BTREE,
  KEY `available_sizes_sku_index` (`sku`) USING BTREE,
  KEY `available_sizes_category_id_index` (`category_id`) USING BTREE,
  KEY `available_sizes_brand_id_index` (`brand_id`) USING BTREE,
  KEY `available_sizes_stock_id_index` (`stock_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `available_sizes_full`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `available_sizes_full` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned DEFAULT NULL,
  `one_c_product_id` int unsigned DEFAULT NULL,
  `brand_id` bigint unsigned DEFAULT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  `stock_id` bigint unsigned DEFAULT NULL,
  `sku` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buy_price` double(8,2) NOT NULL,
  `sell_price` double(8,2) NOT NULL,
  `size_none` int unsigned NOT NULL DEFAULT '0',
  `size_31` int unsigned NOT NULL DEFAULT '0',
  `size_32` int unsigned NOT NULL DEFAULT '0',
  `size_33` int unsigned NOT NULL DEFAULT '0',
  `size_34` int unsigned NOT NULL DEFAULT '0',
  `size_35` int unsigned NOT NULL DEFAULT '0',
  `size_36` int unsigned NOT NULL DEFAULT '0',
  `size_37` int unsigned NOT NULL DEFAULT '0',
  `size_38` int unsigned NOT NULL DEFAULT '0',
  `size_39` int unsigned NOT NULL DEFAULT '0',
  `size_40` int unsigned NOT NULL DEFAULT '0',
  `size_41` int unsigned NOT NULL DEFAULT '0',
  `size_42` int unsigned NOT NULL DEFAULT '0',
  `size_43` int unsigned NOT NULL DEFAULT '0',
  `size_44` int unsigned NOT NULL DEFAULT '0',
  `size_45` int unsigned NOT NULL DEFAULT '0',
  `size_46` int unsigned NOT NULL DEFAULT '0',
  `size_47` int unsigned NOT NULL DEFAULT '0',
  `size_48` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `sizes_availabilities_product_id_index` (`product_id`) USING BTREE,
  KEY `available_sizes_sku_index` (`sku`) USING BTREE,
  KEY `available_sizes_category_id_index` (`category_id`) USING BTREE,
  KEY `available_sizes_brand_id_index` (`brand_id`) USING BTREE,
  KEY `available_sizes_stock_id_index` (`stock_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `banners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `banners` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `position` enum('catalog_top','index_main','index_top','index_bottom','main_menu_catalog','catalog_mob','feedback','feedback_mob') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `priority` int unsigned NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `start_datetime` datetime DEFAULT NULL,
  `end_datetime` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `show_timer` tinyint(1) DEFAULT NULL,
  `spoiler` json DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `banners_position_index` (`position`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `batches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `dispatch_date` datetime DEFAULT NULL COMMENT 'Дата отправки',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `brands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `brands` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `one_c_id` int unsigned DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `seo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `brands_name_unique` (`name`) USING BTREE,
  UNIQUE KEY `brands_slug_unique` (`slug`) USING BTREE,
  UNIQUE KEY `brands_one_c_id_unique` (`one_c_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cart_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cart_data` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cart_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `count` int NOT NULL,
  `size_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `cart_data_cart_id_index` (`cart_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `carts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `carts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `promocode_id` bigint unsigned DEFAULT NULL,
  `cancel_promocode` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `one_c_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `_lft` int unsigned NOT NULL DEFAULT '0',
  `_rgt` int unsigned NOT NULL DEFAULT '0',
  `parent_id` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `categories_slug_unique` (`slug`) USING BTREE,
  UNIQUE KEY `categories_path_unique` (`path`) USING BTREE,
  UNIQUE KEY `categories_title_unique` (`title`) USING BTREE,
  UNIQUE KEY `categories_one_c_name_unique` (`one_c_name`) USING BTREE,
  KEY `categories__lft__rgt_parent_id_index` (`_lft`,`_rgt`,`parent_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `country_id` bigint unsigned NOT NULL COMMENT 'ID страны',
  `name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Название города',
  `slug` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Slug города',
  `catalog_title` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Загаловок в каталоге',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `cities_slug_unique` (`slug`) USING BTREE,
  KEY `cities_country_id_foreign` (`country_id`) USING BTREE,
  KEY `cities_slug_index` (`slug`) USING BTREE,
  CONSTRAINT `cities_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `collections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collections` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `seo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `colors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `colors` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `seo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `configs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `configs` (
  `key` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `config` json NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`key`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `countries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `countries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mask` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `img` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `prefix` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `countries_code_index` (`code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `currencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `currencies` (
  `code` char(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `rate` double(8,2) NOT NULL,
  `decimals` smallint NOT NULL DEFAULT '0',
  `symbol` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`code`) USING BTREE,
  KEY `currencies_country_index` (`country`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `delivery_methods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `delivery_methods` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `instance` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `sorting` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `delivery_methods_class_unique` (`instance`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `devices` (
  `id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `cart_id` bigint unsigned DEFAULT NULL,
  `yandex_id` bigint unsigned DEFAULT NULL,
  `google_id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('mobile','desktop') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `agent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `devices_user_id_index` (`user_id`) USING BTREE,
  KEY `devices_cart_id_index` (`cart_id`) USING BTREE,
  KEY `devices_yandex_id_index` (`yandex_id`) USING BTREE,
  KEY `devices_google_id_index` (`google_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `docs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `docs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `html` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `docs_slug_unique` (`slug`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fabrics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fabrics` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `seo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `favorites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `favorites` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `device_id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `favorites_user_id_index` (`user_id`) USING BTREE,
  KEY `favorites_device_id_index` (`device_id`) USING BTREE,
  KEY `favorites_product_id_index` (`product_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `feedback_answers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `feedback_answers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `feedback_id` bigint unsigned NOT NULL,
  `admin_id` bigint unsigned DEFAULT NULL,
  `text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `publish` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `feedback_answers_feedback_id_index` (`feedback_id`) USING BTREE,
  KEY `feedback_answers_admin_id_index` (`admin_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `feedbacks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `feedbacks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `yandex_id` bigint unsigned DEFAULT NULL,
  `user_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_phone` bigint unsigned DEFAULT NULL,
  `user_city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `rating` tinyint NOT NULL DEFAULT '5',
  `product_id` bigint unsigned NOT NULL DEFAULT '0',
  `type_id` tinyint NOT NULL DEFAULT '1',
  `captcha_score` tinyint NOT NULL DEFAULT '0',
  `view_only_posted` tinyint(1) NOT NULL DEFAULT '1',
  `publish` tinyint(1) NOT NULL DEFAULT '0',
  `ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `feedbacks_user_id_index` (`user_id`) USING BTREE,
  KEY `feedbacks_yandex_id_index` (`yandex_id`) USING BTREE,
  KEY `feedbacks_product_id_index` (`product_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `heels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `heels` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `seo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `index_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `index_links` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `links` json NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `info_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `info_pages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `html` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `info_pages_slug_unique` (`slug`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `installments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `installments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_item_id` bigint unsigned NOT NULL,
  `contract_number` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `monthly_fee` double(8,2) NOT NULL,
  `send_notifications` tinyint(1) NOT NULL DEFAULT '0',
  `notice_sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `installment_form_file` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Файл бланка рассрочки',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `installments_order_item_id_unique` (`order_item_id`) USING BTREE,
  KEY `installments_contract_number_index` (`contract_number`) USING BTREE,
  CONSTRAINT `installments_order_item_id_foreign` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `jobs_queue_index` (`queue`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `log_inventories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_inventories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `action` enum('RESTORE','UPDATE','DELETE') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'UPDATE',
  `added_sizes` json DEFAULT NULL,
  `removed_sizes` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `log_order_actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_order_actions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `admin_id` int unsigned DEFAULT NULL,
  `action` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `log_order_actions_order_id_foreign` (`order_id`) USING BTREE,
  KEY `log_order_actions_admin_id_foreign` (`admin_id`) USING BTREE,
  CONSTRAINT `log_order_actions_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `log_order_actions_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `log_order_item_inventory_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_order_item_inventory_notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_item_id` bigint unsigned NOT NULL,
  `stock_id` bigint unsigned NOT NULL,
  `created_at` timestamp NOT NULL,
  `reserved_at` timestamp NULL DEFAULT NULL,
  `canceled_at` timestamp NULL DEFAULT NULL,
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `sended_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `returned_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `log_order_item_inventory_notifications_order_item_id_unique` (`order_item_id`) USING BTREE,
  KEY `log_order_item_inventory_notifications_stock_id_index` (`stock_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `log_sms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_sms` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `order_id` bigint unsigned DEFAULT NULL,
  `mailing_id` bigint unsigned DEFAULT NULL,
  `route` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `text` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `log_sms_admin_id_index` (`admin_id`) USING BTREE,
  KEY `log_sms_order_id_index` (`order_id`) USING BTREE,
  KEY `log_sms_user_id_index` (`user_id`) USING BTREE,
  KEY `log_sms_mailing_id_index` (`mailing_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mailings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mailings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `manufacturers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `manufacturers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `media` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `model_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `collection_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disk` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `conversions_disk` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` bigint unsigned NOT NULL,
  `manipulations` json NOT NULL,
  `custom_properties` json NOT NULL,
  `generated_conversions` json NOT NULL,
  `responsive_images` json NOT NULL,
  `order_column` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `media_uuid_unique` (`uuid`) USING BTREE,
  KEY `media_model_type_model_id_index` (`model_type`,`model_id`) USING BTREE,
  KEY `media_order_column_index` (`order_column`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `online_payment_statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `online_payment_statuses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `online_payment_id` bigint unsigned NOT NULL COMMENT 'ID платежа',
  `admin_user_id` int unsigned DEFAULT NULL COMMENT 'ID admin пользователя',
  `payment_status_enum_id` tinyint unsigned DEFAULT NULL COMMENT 'enum ID статуса плажета',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `online_payment_statuses_admin_user_id_foreign` (`admin_user_id`) USING BTREE,
  KEY `online_payment_statuses_online_payment_id_foreign` (`online_payment_id`) USING BTREE,
  CONSTRAINT `online_payment_statuses_admin_user_id_foreign` FOREIGN KEY (`admin_user_id`) REFERENCES `admin_users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `online_payment_statuses_online_payment_id_foreign` FOREIGN KEY (`online_payment_id`) REFERENCES `online_payments` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `online_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `online_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL COMMENT 'Номер заказа',
  `currency_code` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Код валюты в ISO 4217',
  `currency_value` double(10,2) NOT NULL DEFAULT '1.00' COMMENT 'Валютный курс',
  `method_enum_id` tinyint unsigned DEFAULT NULL COMMENT 'enum ID платежной системы',
  `admin_user_id` int unsigned DEFAULT NULL COMMENT 'ID admin пользователя',
  `amount` double(12,2) DEFAULT NULL COMMENT 'Сумма оплаты',
  `expires_at` datetime DEFAULT NULL COMMENT 'Время жизни платежа',
  `payment_id` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ID платежа в платежной системе',
  `payment_num` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Номер платежа',
  `payment_url` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ссылка на платеж',
  `card_last4` tinyint unsigned DEFAULT NULL COMMENT 'Последние 4 цифры карты',
  `card_type` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Тип карты',
  `email` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Email плательщика',
  `phone` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Телефон плательщика',
  `fio` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ФИО плательщика',
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Комментарий',
  `is_test` tinyint(1) DEFAULT NULL COMMENT 'Тестовый платеж',
  `payed_at` datetime DEFAULT NULL COMMENT 'Дата оплаты платежа',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `qr_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'QR code платежа',
  `link_code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Уникальный код ссылки на платеж',
  `link_expires_at` datetime DEFAULT NULL COMMENT 'Времся жизни ссылки на платеж',
  `request_data` json DEFAULT NULL,
  `last_status_enum_id` tinyint unsigned DEFAULT '1' COMMENT 'enum ID последнего статуса платежа',
  `paid_amount` double(12,2) DEFAULT NULL COMMENT 'Оплаченная клиентом сумма',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `online_payments_order_id_foreign` (`order_id`) USING BTREE,
  KEY `online_payments_admin_user_id_foreign` (`admin_user_id`) USING BTREE,
  KEY `online_payments_payment_url_index` (`payment_url`) USING BTREE,
  KEY `online_payments_link_code_index` (`link_code`) USING BTREE,
  CONSTRAINT `online_payments_admin_user_id_foreign` FOREIGN KEY (`admin_user_id`) REFERENCES `admin_users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `online_payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `order_admin_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_admin_comments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `comment` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `order_admin_comments_order_id_index` (`order_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `order_item_statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_item_statuses` (
  `key` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_for_admin` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_for_user` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sorting` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`key`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `size_id` bigint unsigned NOT NULL,
  `count` int NOT NULL,
  `buy_price` double(8,2) NOT NULL,
  `price` double(8,2) NOT NULL,
  `old_price` double(8,2) NOT NULL,
  `current_price` double(8,2) NOT NULL,
  `discount` double(8,2) NOT NULL DEFAULT '0.00',
  `promocode_applied` tinyint(1) NOT NULL DEFAULT '0',
  `status_key` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `status_updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `release_date` date DEFAULT NULL,
  `pred_period` tinyint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `order_items_order_id_index` (`order_id`) USING BTREE,
  KEY `order_items_status_key_index` (`status_key`) USING BTREE,
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `order_statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_statuses` (
  `key` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_for_admin` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_for_user` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sorting` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`key`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `order_tracks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_tracks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `track_number` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Трек номер заказа',
  `track_link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ссылка для отслеживания трек номера',
  `order_id` bigint unsigned DEFAULT NULL COMMENT 'Номер заказа',
  `delivery_type_enum` smallint unsigned NOT NULL DEFAULT '1' COMMENT 'Тип доставки',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `order_tracks_track_number_delivery_type_enum_unique` (`track_number`,`delivery_type_enum`) USING BTREE,
  KEY `order_tracks_order_id_foreign` (`order_id`) USING BTREE,
  CONSTRAINT `order_tracks_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `device_id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `patronymic_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `promocode_id` bigint unsigned DEFAULT NULL,
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `total_price` double(8,2) unsigned NOT NULL DEFAULT '0.00',
  `currency` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `rate` double(8,2) NOT NULL,
  `country_id` bigint unsigned DEFAULT NULL,
  `region` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zip` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_addr` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_id` bigint unsigned DEFAULT NULL,
  `payment_cost` double(8,2) DEFAULT NULL,
  `delivery_id` bigint unsigned DEFAULT NULL,
  `delivery_cost` double(8,2) DEFAULT NULL,
  `delivery_price` double(8,2) DEFAULT NULL,
  `delivery_point_id` bigint unsigned DEFAULT NULL,
  `order_method` enum('default','oneclick','chat','phone','email','viber','telegram','whatsapp','insta','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default',
  `utm_medium` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `utm_source` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `utm_campaign` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `utm_content` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `utm_term` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_key` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `status_updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `admin_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `batch_id` bigint unsigned DEFAULT NULL COMMENT 'Номер партии',
  `weight` double(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `orders_user_id_index` (`user_id`) USING BTREE,
  KEY `orders_device_id_index` (`device_id`) USING BTREE,
  KEY `orders_promocode_id_index` (`promocode_id`) USING BTREE,
  KEY `orders_utm_medium_index` (`utm_medium`) USING BTREE,
  KEY `orders_utm_source_index` (`utm_source`) USING BTREE,
  KEY `orders_utm_campaign_index` (`utm_campaign`) USING BTREE,
  KEY `orders_utm_content_index` (`utm_content`) USING BTREE,
  KEY `orders_utm_term_index` (`utm_term`) USING BTREE,
  KEY `orders_status_key_index` (`status_key`) USING BTREE,
  KEY `orders_admin_id_index` (`admin_id`) USING BTREE,
  KEY `orders_batch_id_foreign` (`batch_id`) USING BTREE,
  CONSTRAINT `orders_batch_id_foreign` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `payment_methods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_methods` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `instance` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `sorting` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `payment_methods_class_unique` (`instance`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`) USING BTREE,
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `product_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_attributes` (
  `product_id` bigint unsigned NOT NULL,
  `attribute_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attribute_id` bigint unsigned NOT NULL,
  KEY `product_attributes_attribute_type_attribute_id_index` (`attribute_type`,`attribute_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `product_carousels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_carousels` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `categories` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_imidj` tinyint(1) NOT NULL DEFAULT '0',
  `only_sale` tinyint(1) NOT NULL DEFAULT '0',
  `only_new` tinyint(1) NOT NULL DEFAULT '0',
  `speed` int unsigned NOT NULL DEFAULT '3000',
  `count` smallint unsigned NOT NULL DEFAULT '15',
  `sorting` tinyint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `enum_type_id` tinyint unsigned DEFAULT NULL,
  `additional_settings` json DEFAULT NULL COMMENT 'Дополнительные настройки',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `product_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_groups` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sku` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `label_id` int unsigned NOT NULL DEFAULT '0',
  `buy_price` decimal(8,2) NOT NULL DEFAULT '0.00',
  `price` decimal(8,2) NOT NULL DEFAULT '0.00',
  `old_price` decimal(8,2) NOT NULL DEFAULT '0.00',
  `category_id` bigint unsigned NOT NULL DEFAULT '0',
  `season_id` bigint unsigned NOT NULL DEFAULT '0',
  `brand_id` bigint unsigned NOT NULL DEFAULT '0',
  `manufacturer_id` bigint unsigned NOT NULL DEFAULT '0',
  `collection_id` bigint unsigned NOT NULL DEFAULT '0',
  `color_txt` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fabric_top_txt` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fabric_inner_txt` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fabric_insole_txt` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fabric_outsole_txt` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `heel_txt` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bootleg_height_txt` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `action` tinyint(1) NOT NULL DEFAULT '0',
  `rating` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `product_group_id` bigint unsigned DEFAULT NULL COMMENT 'ID группы товаров',
  `key_features` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ключевая особенность товара',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `products_slug_unique` (`slug`) USING BTREE,
  KEY `products_price_id_index` (`price`,`id`) USING BTREE,
  KEY `products_created_at_id_index` (`created_at`,`id`) USING BTREE,
  KEY `products_rating_id_index` (`rating`,`id`) USING BTREE,
  KEY `products_sku_index` (`sku`) USING BTREE,
  KEY `products_category_id_index` (`category_id`) USING BTREE,
  KEY `products_season_id_index` (`season_id`) USING BTREE,
  KEY `products_brand_id_index` (`brand_id`) USING BTREE,
  KEY `products_manufacturer_id_index` (`manufacturer_id`) USING BTREE,
  KEY `products_collection_id_index` (`collection_id`) USING BTREE,
  KEY `products_product_group_id_foreign` (`product_group_id`) USING BTREE,
  CONSTRAINT `products_product_group_id_foreign` FOREIGN KEY (`product_group_id`) REFERENCES `product_groups` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `promotions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `promotions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sales` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `label_text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `algorithm` enum('fake','simple','count','ascending') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'simple',
  `sale` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `categories` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `collections` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `styles` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `seasons` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `only_new` tinyint(1) NOT NULL DEFAULT '0',
  `only_discount` tinyint(1) NOT NULL DEFAULT '0',
  `add_client_sale` tinyint(1) NOT NULL DEFAULT '0',
  `add_review_sale` tinyint(1) NOT NULL DEFAULT '1',
  `has_installment` tinyint(1) NOT NULL DEFAULT '1',
  `has_fitting` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `seasons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `seasons` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `seo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `seo_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `seo_links` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `folder_enum_id` tinyint unsigned DEFAULT NULL COMMENT 'Enum id каталога',
  `seo_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Seo ссылка',
  `destination` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Куда приведет seo ссылка',
  `tag` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Тег',
  `frequency` int DEFAULT NULL COMMENT 'Частота',
  `frequency_updated_at` datetime DEFAULT NULL COMMENT 'Дата/время обновления поля частота',
  `h1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'h1 заголовок',
  `main_text` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Основной текст',
  `meta_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Meta title',
  `meta_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Meta description',
  `meta_keywords` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Meta keywords',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `seo_links_destination_index` (`destination`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `short_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `short_links` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `short_link` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_link` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `short_links_short_link_unique` (`short_link`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sizes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sizes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `statuses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `stocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stocks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `one_c_id` int unsigned DEFAULT NULL,
  `city_id` bigint unsigned NOT NULL,
  `chat_id` bigint unsigned DEFAULT NULL,
  `type` enum('shop','stock') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'stock',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `internal_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `worktime` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Рабочее время',
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `has_pickup` tinyint(1) NOT NULL DEFAULT '0',
  `geo_latitude` double(10,7) DEFAULT NULL,
  `geo_longitude` double(10,7) DEFAULT NULL,
  `check_availability` tinyint(1) NOT NULL DEFAULT '0',
  `sorting` smallint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `stocks_one_c_id_unique` (`one_c_id`) USING BTREE,
  KEY `stocks_chat_id_index` (`chat_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `styles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `styles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `seo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tag_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tag_groups` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Название группы тегов',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tags` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `seo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `tag_group_id` bigint unsigned DEFAULT '1' COMMENT 'Номер группы',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `tags_name_unique` (`name`) USING BTREE,
  UNIQUE KEY `tags_slug_unique` (`slug`) USING BTREE,
  KEY `tags_tag_group_id_foreign` (`tag_group_id`) USING BTREE,
  CONSTRAINT `tags_tag_group_id_foreign` FOREIGN KEY (`tag_group_id`) REFERENCES `tag_groups` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `telegram_bots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `telegram_bots` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `telegram_bots_token_unique` (`token`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `telegram_chats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `telegram_chats` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `chat_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telegram_bot_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `telegram_chats_chat_id_telegram_bot_id_unique` (`chat_id`,`telegram_bot_id`) USING BTREE,
  KEY `telegram_chats_telegram_bot_id_foreign` (`telegram_bot_id`) USING BTREE,
  CONSTRAINT `telegram_chats_telegram_bot_id_foreign` FOREIGN KEY (`telegram_bot_id`) REFERENCES `telegram_bots` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expire_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `urls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `urls` (
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  `redirect` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  UNIQUE KEY `urls_slug_unique` (`slug`) USING BTREE,
  KEY `urls_model_type_model_id_index` (`model_type`,`model_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_addresses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `country_id` bigint unsigned DEFAULT NULL,
  `region` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zip` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_addresses_user_id_index` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_groups` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `discount` double(8,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_passports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_passports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `passport_number` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Номер паспорта',
  `series` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Серия паспорта',
  `issued_by` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Кем выдан',
  `issued_date` date DEFAULT NULL COMMENT 'Когда выдан',
  `personal_number` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Личный номер',
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `registration_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Адрес прописки',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_passports_user_id_foreign` (`user_id`) USING BTREE,
  CONSTRAINT `user_passports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cart_token` bigint unsigned DEFAULT NULL,
  `group_id` bigint unsigned NOT NULL DEFAULT '1',
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `patronymic_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `phone_verified_at` timestamp NULL DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `users_email_unique` (`email`) USING BTREE,
  UNIQUE KEY `users_cart_token_unique` (`cart_token`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` VALUES (1,'2014_10_12_000000_create_users_table',1);
INSERT INTO `migrations` VALUES (2,'2014_10_12_100000_create_password_resets_table',1);
INSERT INTO `migrations` VALUES (3,'2016_01_04_173148_create_admin_tables',1);
INSERT INTO `migrations` VALUES (4,'2019_08_19_000000_create_failed_jobs_table',1);
INSERT INTO `migrations` VALUES (5,'2019_12_14_000001_create_personal_access_tokens_table',1);
INSERT INTO `migrations` VALUES (6,'2020_07_21_191003_create_categories_table',1);
INSERT INTO `migrations` VALUES (7,'2020_08_28_174916_create_jobs_table',1);
INSERT INTO `migrations` VALUES (8,'2020_09_02_150216_create_urls_table',1);
INSERT INTO `migrations` VALUES (9,'2020_09_04_122722_create_product_attributes_table',1);
INSERT INTO `migrations` VALUES (10,'2020_09_08_090900_create_fabrics_table',1);
INSERT INTO `migrations` VALUES (11,'2020_09_08_091019_create_sizes_table',1);
INSERT INTO `migrations` VALUES (12,'2020_09_08_091030_create_colors_table',1);
INSERT INTO `migrations` VALUES (13,'2020_09_08_091646_create_seasons_table',1);
INSERT INTO `migrations` VALUES (14,'2020_09_08_091813_create_tags_table',1);
INSERT INTO `migrations` VALUES (15,'2020_09_08_091832_create_brands_table',1);
INSERT INTO `migrations` VALUES (16,'2020_09_24_110810_create_heels_table',1);
INSERT INTO `migrations` VALUES (17,'2020_09_24_110820_create_styles_table',1);
INSERT INTO `migrations` VALUES (18,'2020_10_16_085850_create_products_table',1);
INSERT INTO `migrations` VALUES (19,'2020_10_19_084729_create_media_table',1);
INSERT INTO `migrations` VALUES (20,'2020_10_28_104316_create_countries_table',1);
INSERT INTO `migrations` VALUES (21,'2020_10_28_115506_create_feedback_table',1);
INSERT INTO `migrations` VALUES (22,'2020_10_28_120115_create_feedback_answers_table',1);
INSERT INTO `migrations` VALUES (23,'2020_12_03_080136_create_carts_table',1);
INSERT INTO `migrations` VALUES (24,'2020_12_03_080200_create_cart_data_table',1);
INSERT INTO `migrations` VALUES (25,'2020_12_15_102136_create_orders_table',1);
INSERT INTO `migrations` VALUES (26,'2020_12_15_102242_create_order_items_table',1);
INSERT INTO `migrations` VALUES (27,'2021_02_02_130357_create_info_pages_table',1);
INSERT INTO `migrations` VALUES (28,'2021_02_03_110356_create_banners_table',1);
INSERT INTO `migrations` VALUES (29,'2021_02_25_102813_create_delivery_methods_table',1);
INSERT INTO `migrations` VALUES (30,'2021_02_25_103145_create_payment_methods_table',1);
INSERT INTO `migrations` VALUES (31,'2021_02_28_220402_create_collections_table',1);
INSERT INTO `migrations` VALUES (32,'2021_06_06_103038_create_sales_table',1);
INSERT INTO `migrations` VALUES (33,'2021_06_25_131418_create_currencies_table',1);
INSERT INTO `migrations` VALUES (34,'2021_08_25_224659_create_product_carousels_table',1);
INSERT INTO `migrations` VALUES (35,'2021_09_09_135535_create_manufacturers_table',1);
INSERT INTO `migrations` VALUES (36,'2021_10_21_111912_create_index_links_table',1);
INSERT INTO `migrations` VALUES (37,'2021_11_14_163634_create_api_users_table',1);
INSERT INTO `migrations` VALUES (38,'2021_11_19_020637_create_user_addresses_table',1);
INSERT INTO `migrations` VALUES (39,'2021_12_08_101737_create_order_statuses_table',1);
INSERT INTO `migrations` VALUES (40,'2021_12_08_101830_create_order_item_statuses_table',1);
INSERT INTO `migrations` VALUES (41,'2021_12_26_180140_create_order_admin_comments_table',1);
INSERT INTO `migrations` VALUES (42,'2022_01_26_232337_create_devices_table',1);
INSERT INTO `migrations` VALUES (43,'2022_01_28_144022_create_favorites_table',1);
INSERT INTO `migrations` VALUES (44,'2022_03_07_120403_create_statuses_table',1);
INSERT INTO `migrations` VALUES (45,'2022_05_16_155735_add_generated_conversions_to_media_table',1);
INSERT INTO `migrations` VALUES (46,'2022_06_01_230930_create_docs_table',1);
INSERT INTO `migrations` VALUES (47,'2022_06_16_001820_create_short_links_table',1);
INSERT INTO `migrations` VALUES (48,'2022_06_18_222313_create_installments_table',1);
INSERT INTO `migrations` VALUES (49,'2022_09_01_092326_add_spoiler_and_timer_to_banners_table',1);
INSERT INTO `migrations` VALUES (50,'2022_09_08_214841_remove_timer_field_to_banners_table',1);
INSERT INTO `migrations` VALUES (51,'2022_09_12_222038_create_promotions_table',1);
INSERT INTO `migrations` VALUES (52,'2022_09_13_001514_add_type_field_to_product_carousels_table',1);
INSERT INTO `migrations` VALUES (53,'2022_09_21_233541_add_enum_posituions_to_banners_table',1);
INSERT INTO `migrations` VALUES (54,'2022_09_29_013241_update_db_rows_2909',1);
INSERT INTO `migrations` VALUES (55,'2022_10_09_140114_create_sms_logs_table',1);
INSERT INTO `migrations` VALUES (56,'2022_10_03_214755_create_payments_table',2);
INSERT INTO `migrations` VALUES (57,'2022_10_03_220446_create_payment_statuses_table',2);
INSERT INTO `migrations` VALUES (58,'2022_10_26_125039_create_tokens_table',3);
INSERT INTO `migrations` VALUES (59,'2022_10_26_160033_create_configs_table',4);
INSERT INTO `migrations` VALUES (60,'2022_10_28_123835_add_payment_num_field_to_online_payments_table',5);
INSERT INTO `migrations` VALUES (61,'2022_10_31_031140_add_qr_code_field_to_online_payments_table',6);
INSERT INTO `migrations` VALUES (62,'2022_10_31_200153_change_fields_in_installments_table',7);
INSERT INTO `migrations` VALUES (63,'2022_11_15_003955_create_seo_links_table',8);
INSERT INTO `migrations` VALUES (64,'2022_11_25_231505_create_cities_table',8);
INSERT INTO `migrations` VALUES (65,'2022_11_25_123211_add_phone_auth_to_users_table',9);
INSERT INTO `migrations` VALUES (66,'2022_12_11_172146_create_mailings_table',10);
INSERT INTO `migrations` VALUES (67,'2022_12_11_210859_create_user_groups_table',10);
INSERT INTO `migrations` VALUES (68,'2022_12_12_230048_change_default_value_in_installments_table',11);
INSERT INTO `migrations` VALUES (69,'2022_12_13_215311_create_product_groups_table',12);
INSERT INTO `migrations` VALUES (70,'2022_12_13_223622_add_group_id_field_to_products_table',12);
INSERT INTO `migrations` VALUES (71,'2022_12_23_231210_add_add_review_sale_field_to_sales_table',13);
INSERT INTO `migrations` VALUES (72,'2022_12_26_223227_add_link_code_field_to_online_payments_table',14);
INSERT INTO `migrations` VALUES (73,'2022_12_27_224129_add_last_status_enum_id_field_to_online_payments_table',15);
INSERT INTO `migrations` VALUES (74,'2023_01_13_224819_rename_fields_in_payments_and_deliveries_tables',16);
INSERT INTO `migrations` VALUES (75,'2023_01_20_174414_add_city_field_to_feedbacks_table',17);
INSERT INTO `migrations` VALUES (76,'2023_02_04_121539_add_status_updated_at_field_to_order_items_table',18);
INSERT INTO `migrations` VALUES (77,'2023_02_25_193804_add_status_updated_at_field_to_orders_table',19);
INSERT INTO `migrations` VALUES (78,'2014_10_12_100000_create_password_reset_tokens_table',20);
INSERT INTO `migrations` VALUES (79,'2023_03_14_121346_create_stocks_table',21);
INSERT INTO `migrations` VALUES (80,'2023_02_13_221211_create_user_passports_table',22);
INSERT INTO `migrations` VALUES (81,'2023_03_19_132916_change_worktime_size_to_stocks_table',23);
INSERT INTO `migrations` VALUES (82,'2023_03_20_233548_add_configs',24);
INSERT INTO `migrations` VALUES (83,'2023_02_28_231103_add_installment_form_file_field_to_installments_table',25);
INSERT INTO `migrations` VALUES (84,'2023_03_22_212121_add_newsletter_to_configs_table',25);
INSERT INTO `migrations` VALUES (85,'2023_03_29_115735_add_delivery_track_fields_to_orders_table',25);
INSERT INTO `migrations` VALUES (86,'2023_03_31_170034_update_stocks_table',26);
INSERT INTO `migrations` VALUES (88,'2023_04_01_200657_create_sizes_availabilities_table',27);
INSERT INTO `migrations` VALUES (89,'2023_04_04_212652_rename_sizes_availabilities_table',28);
INSERT INTO `migrations` VALUES (90,'2023_04_14_010428_add_sizes_table_data',29);
INSERT INTO `migrations` VALUES (91,'2023_04_20_103906_create_inventory_logs_table',30);
INSERT INTO `migrations` VALUES (92,'2023_04_10_233924_create_batches_table',31);
INSERT INTO `migrations` VALUES (93,'2023_04_11_224700_add_batch_id_to_orders_table',31);
INSERT INTO `migrations` VALUES (94,'2023_04_18_233508_create_order_tracks_table',31);
INSERT INTO `migrations` VALUES (95,'2023_04_18_234320_move_and_remove_order_track_fields_to_orders_table',31);
INSERT INTO `migrations` VALUES (96,'2023_05_04_122933_add_indexes_to_available_sizes_table',32);
INSERT INTO `migrations` VALUES (97,'2023_05_06_142549_create_tag_groups_table',33);
INSERT INTO `migrations` VALUES (98,'2023_05_07_223517_add_tag_group_id_field_to_tag_table',33);
INSERT INTO `migrations` VALUES (99,'2023_05_10_015502_add_key_features_fields_to_products_table',33);
INSERT INTO `migrations` VALUES (100,'2023_05_10_020242_add_weight_field_to_orders_table',33);
INSERT INTO `migrations` VALUES (101,'2023_06_03_145345_add_foreign_key_to_order_items_table',34);
INSERT INTO `migrations` VALUES (102,'2023_06_05_100546_add_category_name_field_to_available_sizes_table',35);
INSERT INTO `migrations` VALUES (103,'2023_06_11_112411_create_log_order_actions_table',36);
INSERT INTO `migrations` VALUES (104,'2023_06_22_000913_create_available_sizes_full_table',37);
INSERT INTO `migrations` VALUES (105,'2023_06_29_224317_add_registration_address_field_to_user_passports_table',38);
INSERT INTO `migrations` VALUES (106,'2023_06_30_084304_add_fields_to_admin_users_table',38);
INSERT INTO `migrations` VALUES (107,'2023_05_24_230317_add_additional_settings_field_to_product_carousels_table',39);
INSERT INTO `migrations` VALUES (114,'2023_07_30_141645_create_log_order_item_inventory_notifications_table',41);
INSERT INTO `migrations` VALUES (115,'2023_08_01_231141_add_chat_id_to_stocks',41);
INSERT INTO `migrations` VALUES (116,'2023_07_29_133700_create_telegram_bots_table',42);
INSERT INTO `migrations` VALUES (117,'2023_07_29_133701_create_telegram_chats_table',42);
INSERT INTO `migrations` VALUES (118,'2023_08_06_165923_add_only_discount_to_sales_table',43);
