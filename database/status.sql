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

 Date: 29/02/2024 23:38:17
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for status
-- ----------------------------
DROP TABLE IF EXISTS `status`;
CREATE TABLE `status` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `is_enabled` int NOT NULL,
  `written_status` varchar(255) NOT NULL DEFAULT '2',
  `drawn_status` varchar(255) NOT NULL,
  `visibility` tinyint DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `is_enabled` (`is_enabled`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ----------------------------
-- Records of status
-- ----------------------------
BEGIN;
INSERT INTO `status` VALUES (1, 1, 'Published', '<i class=\"fa fa-circle fa-lg\" aria-hidden=\"true\" style=\"color: #449d44; line-height: .1;\"></i>  Published', 1);
INSERT INTO `status` VALUES (2, 2, 'Hidden', '<i class=\"fa fa-circle fa-lg\" aria-hidden=\"true\" style=\"color: #ff0000; line-height: .1;\"></i> Hidden', 1);
INSERT INTO `status` VALUES (3, 3, 'Draft', '<i class=\"fa fa-circle-o fa-lg\" aria-hidden=\"true\" style=\"color: #000000; line-height: .1;\"></i> Draft', 1);
INSERT INTO `status` VALUES (4, 4, 'Waiting...', '<i class=\"fa fa-circle fa-lg\" aria-hidden=\"true\" style=\"color: #ffb00c; line-height: .1;\"></i> Waiting...', 1);
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
