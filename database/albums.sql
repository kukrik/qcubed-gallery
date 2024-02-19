/*
 Navicat MySQL Data Transfer

 Source Server         : KOHALIK
 Source Server Type    : MySQL
 Source Server Version : 80030
 Source Host           : localhost:3306
 Source Schema         : qcubed-5

 Target Server Type    : MySQL
 Target Server Version : 80030
 File Encoding         : 65001

 Date: 19/02/2024 18:28:34
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for albums
-- ----------------------------
DROP TABLE IF EXISTS `albums`;
CREATE TABLE `albums` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `folder_id` int unsigned DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_vi_0900_ai_ci NOT NULL,
  `path` text COLLATE utf8mb4_vi_0900_ai_ci,
  `title_slug` varchar(255) COLLATE utf8mb4_vi_0900_ai_ci DEFAULT NULL,
  `post_date` datetime DEFAULT NULL,
  `post_update_date` datetime DEFAULT NULL,
  `is_enabled` int unsigned DEFAULT '2',
  PRIMARY KEY (`id`),
  KEY `is_enabled_idx` (`is_enabled`) USING BTREE,
  CONSTRAINT `is_enabled_activity` FOREIGN KEY (`is_enabled`) REFERENCES `activity` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vi_0900_ai_ci;

SET FOREIGN_KEY_CHECKS = 1;
