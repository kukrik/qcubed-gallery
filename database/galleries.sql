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

 Date: 27/03/2024 11:23:32
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for galleries
-- ----------------------------
DROP TABLE IF EXISTS `galleries`;
CREATE TABLE `galleries` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `album_id` int unsigned DEFAULT NULL,
  `list_id` int unsigned DEFAULT NULL,
  `folder_id` int unsigned DEFAULT NULL,
  `file_id` int unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_vi_0900_ai_ci DEFAULT NULL,
  `path` text COLLATE utf8mb4_vi_0900_ai_ci,
  `description` text COLLATE utf8mb4_vi_0900_ai_ci,
  `author` varchar(255) COLLATE utf8mb4_vi_0900_ai_ci DEFAULT NULL,
  `status` int unsigned DEFAULT '1',
  `post_date` datetime DEFAULT NULL,
  `post_update_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `status_idx` (`status`) USING BTREE,
  KEY `folder_id_idx` (`folder_id`) USING BTREE,
  KEY `list_id_idx` (`list_id`) USING BTREE,
  KEY `id` (`id`,`status`),
  CONSTRAINT `list_id_galleries_ibfk` FOREIGN KEY (`list_id`) REFERENCES `list_of_galleries` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `status_galleries_ibfk` FOREIGN KEY (`status`) REFERENCES `status` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=240 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vi_0900_ai_ci;

SET FOREIGN_KEY_CHECKS = 1;
