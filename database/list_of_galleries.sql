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

 Date: 19/02/2024 18:28:05
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for list_of_galleries
-- ----------------------------
DROP TABLE IF EXISTS `list_of_galleries`;
CREATE TABLE `list_of_galleries` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `album_id` int unsigned DEFAULT NULL,
  `folder_id` int unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_vi_0900_ai_ci NOT NULL,
  `path` text COLLATE utf8mb4_vi_0900_ai_ci,
  `title_slug` varchar(255) COLLATE utf8mb4_vi_0900_ai_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_vi_0900_ai_ci,
  `author` varchar(255) COLLATE utf8mb4_vi_0900_ai_ci DEFAULT NULL,
  `status` int unsigned DEFAULT '2',
  `post_date` datetime DEFAULT NULL,
  `post_update_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `album_id_idx` (`album_id`) USING BTREE,
  KEY `status_idx` (`status`) USING BTREE,
  CONSTRAINT `album_id_albums_ibfk` FOREIGN KEY (`album_id`) REFERENCES `albums` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `status_status_ibfk` FOREIGN KEY (`status`) REFERENCES `status` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vi_0900_ai_ci;

SET FOREIGN_KEY_CHECKS = 1;
