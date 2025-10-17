/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 80041
 Source Host           : localhost:3306
 Source Schema         : cqai

 Target Server Type    : MySQL
 Target Server Version : 80041
 File Encoding         : 65001

 Date: 17/10/2025 21:44:15
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for ac_categories
-- ----------------------------
DROP TABLE IF EXISTS `ac_categories`;
CREATE TABLE `ac_categories`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `name`(`name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of ac_categories
-- ----------------------------
INSERT INTO `ac_categories` VALUES (1, 'AE', '2025-09-09 10:14:05');
INSERT INTO `ac_categories` VALUES (2, 'GE', '2025-09-09 10:14:05');
INSERT INTO `ac_categories` VALUES (3, 'EE', '2025-09-09 10:14:05');
INSERT INTO `ac_categories` VALUES (4, 'CPD', '2025-09-09 10:14:05');

-- ----------------------------
-- Table structure for active_qcc_registrations
-- ----------------------------
DROP TABLE IF EXISTS `active_qcc_registrations`;
CREATE TABLE `active_qcc_registrations`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `qcc_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `slaf_establishment_id` int NOT NULL,
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `team_members` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `category_id` int NOT NULL,
  `qcc_category_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `main_category` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT 'productivity',
  `is_active` tinyint(1) NULL DEFAULT 1,
  `created_by` int NULL DEFAULT NULL,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  `updated_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0) ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `slaf_establishment_id`(`slaf_establishment_id`) USING BTREE,
  INDEX `category_id`(`category_id`) USING BTREE,
  CONSTRAINT `active_qcc_registrations_ibfk_1` FOREIGN KEY (`slaf_establishment_id`) REFERENCES `slaf_establishments` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `active_qcc_registrations_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `productivity_categories` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of active_qcc_registrations
-- ----------------------------
INSERT INTO `active_qcc_registrations` VALUES (1, 'Test', 'Test', 'Test QCC', 8, 'Test', 'Test one, Test Two', 3, '0', 'productivity', 1, 1, '2025-10-09 17:40:09', '2025-10-09 17:40:09');

-- ----------------------------
-- Table structure for ad_bulletins
-- ----------------------------
DROP TABLE IF EXISTS `ad_bulletins`;
CREATE TABLE `ad_bulletins`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `aircraft_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `uploaded_by` int NULL DEFAULT NULL,
  `uploaded_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  `is_active` tinyint(1) NULL DEFAULT 1,
  `branch_id` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `fk_ad_uploaded_by`(`uploaded_by`) USING BTREE,
  INDEX `fk_ad_branch`(`branch_id`) USING BTREE,
  CONSTRAINT `fk_ad_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `fk_ad_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of ad_bulletins
-- ----------------------------

-- ----------------------------
-- Table structure for admins
-- ----------------------------
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `branch` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  `last_login` timestamp(0) NULL DEFAULT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `username`(`username`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of admins
-- ----------------------------
INSERT INTO `admins` VALUES (1, 'admin', 'all', '$2y$10$2knpl.JqcMC0S0CdK4fkx.ESgqJiGEZcyXqjDHuyWdgFJ1sFvpE4e', '2025-09-09 10:14:05', NULL, 1);

-- ----------------------------
-- Table structure for aircraft_competency
-- ----------------------------
DROP TABLE IF EXISTS `aircraft_competency`;
CREATE TABLE `aircraft_competency`  (
  `record_id` int NOT NULL AUTO_INCREMENT,
  `svc_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `rank` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `branch` int NULL DEFAULT NULL,
  `trade` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `formation_id` int NULL DEFAULT NULL,
  `posted_in_date` date NULL DEFAULT NULL,
  `posted_out_date` date NULL DEFAULT NULL,
  `type_id` int NULL DEFAULT NULL,
  `competency_level` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `training_start_date` date NULL DEFAULT NULL,
  `training_end_date` date NULL DEFAULT NULL,
  `formation_ref` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `for_ref_date` date NULL DEFAULT NULL,
  `qai_ref` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `qai_ref_date` date NULL DEFAULT NULL,
  `dt_ref` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `dt_ref_date` date NULL DEFAULT NULL,
  `qao_ref` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `qao_ref_date` date NULL DEFAULT NULL,
  `theory_marks` decimal(5, 2) NULL DEFAULT NULL,
  `practical_marks` decimal(5, 2) NULL DEFAULT NULL,
  `competency_issue_ref` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `com_issue_date` date NULL DEFAULT NULL,
  `competency_renew_ref` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `renew_date` date NULL DEFAULT NULL,
  `certificate_no` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `cer_issued_date` date NULL DEFAULT NULL,
  `retired_date` date NULL DEFAULT NULL,
  `remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`record_id`) USING BTREE,
  INDEX `formation_id`(`formation_id`) USING BTREE,
  INDEX `type_id`(`type_id`) USING BTREE,
  INDEX `branch`(`branch`) USING BTREE,
  CONSTRAINT `aircraft_competency_ibfk_1` FOREIGN KEY (`formation_id`) REFERENCES `formation` (`formation_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `aircraft_competency_ibfk_2` FOREIGN KEY (`type_id`) REFERENCES `type` (`type_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `aircraft_competency_ibfk_3` FOREIGN KEY (`branch`) REFERENCES `ac_categories` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of aircraft_competency
-- ----------------------------

-- ----------------------------
-- Table structure for branches
-- ----------------------------
DROP TABLE IF EXISTS `branches`;
CREATE TABLE `branches`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `name`(`name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 9 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of branches
-- ----------------------------
INSERT INTO `branches` VALUES (1, 'Aeronautical Engineering', '2025-09-09 10:14:05');
INSERT INTO `branches` VALUES (2, 'Air Operations', '2025-09-09 10:14:05');
INSERT INTO `branches` VALUES (3, 'Construction Engineering', '2025-09-09 10:14:05');
INSERT INTO `branches` VALUES (4, 'Electronic Engineering', '2025-09-09 10:14:05');
INSERT INTO `branches` VALUES (5, 'General Engineering', '2025-09-09 10:14:05');
INSERT INTO `branches` VALUES (6, 'Ground Operations', '2025-09-09 10:14:05');
INSERT INTO `branches` VALUES (7, 'Productivity Management', '2025-09-09 10:14:05');
INSERT INTO `branches` VALUES (8, 'Training', '2025-09-09 10:14:05');

-- ----------------------------
-- Table structure for environment_categories
-- ----------------------------
DROP TABLE IF EXISTS `environment_categories`;
CREATE TABLE `environment_categories`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `name`(`name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of environment_categories
-- ----------------------------
INSERT INTO `environment_categories` VALUES (1, 'Audit Checklists', '2025-09-09 10:14:05');
INSERT INTO `environment_categories` VALUES (2, 'Audit Reports', '2025-09-09 10:14:05');
INSERT INTO `environment_categories` VALUES (3, 'Audit Plans', '2025-09-09 10:14:05');

-- ----------------------------
-- Table structure for formation
-- ----------------------------
DROP TABLE IF EXISTS `formation`;
CREATE TABLE `formation`  (
  `formation_id` int NOT NULL AUTO_INCREMENT,
  `formation_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`formation_id`) USING BTREE,
  UNIQUE INDEX `formation_name`(`formation_name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of formation
-- ----------------------------
INSERT INTO `formation` VALUES (1, 'No 1 FTW', '2025-10-17 21:41:10');
INSERT INTO `formation` VALUES (2, '02 SQN', '2025-10-17 21:41:10');
INSERT INTO `formation` VALUES (3, '03 SQN', '2025-10-17 21:41:10');
INSERT INTO `formation` VALUES (4, '04 SQN', '2025-10-17 21:41:10');
INSERT INTO `formation` VALUES (5, '05 SQN', '2025-10-17 21:41:10');
INSERT INTO `formation` VALUES (6, '06 SQN', '2025-10-17 21:41:10');
INSERT INTO `formation` VALUES (7, '07 SQN', '2025-10-17 21:41:10');
INSERT INTO `formation` VALUES (8, '08 SQN', '2025-10-17 21:41:10');
INSERT INTO `formation` VALUES (9, '09 SQN', '2025-10-17 21:41:10');
INSERT INTO `formation` VALUES (10, '10 SQN', '2025-10-17 21:41:10');
INSERT INTO `formation` VALUES (11, '11 SQN', '2025-10-17 21:41:10');
INSERT INTO `formation` VALUES (12, '61 FLT', '2025-10-17 21:41:10');
INSERT INTO `formation` VALUES (13, 'Helitours', '2025-10-17 21:41:10');
INSERT INTO `formation` VALUES (14, 'ASW', '2025-10-17 21:41:10');
INSERT INTO `formation` VALUES (15, 'AEW', '2025-10-17 21:41:10');
INSERT INTO `formation` VALUES (16, 'AOW', '2025-10-17 21:41:10');
INSERT INTO `formation` VALUES (17, 'AFM', '2025-10-17 21:41:10');
INSERT INTO `formation` VALUES (18, 'AR & DW', '2025-10-17 21:41:10');
INSERT INTO `formation` VALUES (19, 'RMW', '2025-10-17 21:41:10');
INSERT INTO `formation` VALUES (20, 'RADAR SQN', '2025-10-17 21:41:10');
INSERT INTO `formation` VALUES (21, 'E &TE Rma', '2025-10-17 21:41:10');
INSERT INTO `formation` VALUES (22, 'E &TE Kat', '2025-10-17 21:41:10');
INSERT INTO `formation` VALUES (23, '12 SQN', '2025-10-17 21:41:10');
INSERT INTO `formation` VALUES (24, '14 SQN', '2025-10-17 21:41:10');
INSERT INTO `formation` VALUES (25, '112 SQN', '2025-10-17 21:41:10');
INSERT INTO `formation` VALUES (26, 'KDU', '2025-10-17 21:41:10');
INSERT INTO `formation` VALUES (27, 'MINIUSCA', '2025-10-17 21:41:10');

-- ----------------------------
-- Table structure for latitude
-- ----------------------------
DROP TABLE IF EXISTS `latitude`;
CREATE TABLE `latitude`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `active` enum('YES','NO') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT 'YES',
  `type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT 'Latitude',
  `formation_id` int NOT NULL,
  `aircraft_type_id` int NOT NULL,
  `tail_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `part_no` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `serial_no` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `hrs` decimal(10, 2) NULL DEFAULT NULL,
  `ldgs` int NULL DEFAULT NULL,
  `date` date NULL DEFAULT NULL,
  `present_latitude` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `dgae_auth_ref` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `auth_date` date NULL DEFAULT NULL,
  `latitude_expiry` date NULL DEFAULT NULL,
  `total_prev_latitude` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `demand_ref` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `created_by` int NULL DEFAULT NULL,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  `updated_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0) ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `formation_id`(`formation_id`) USING BTREE,
  INDEX `aircraft_type_id`(`aircraft_type_id`) USING BTREE,
  INDEX `created_by`(`created_by`) USING BTREE,
  CONSTRAINT `latitude_ibfk_1` FOREIGN KEY (`formation_id`) REFERENCES `formation` (`formation_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `latitude_ibfk_2` FOREIGN KEY (`aircraft_type_id`) REFERENCES `type` (`type_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `latitude_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of latitude
-- ----------------------------

-- ----------------------------
-- Table structure for latitude_extension
-- ----------------------------
DROP TABLE IF EXISTS `latitude_extension`;
CREATE TABLE `latitude_extension`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `latitude_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `related_aircraft` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `latitude_period` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `uploaded_by` int NULL DEFAULT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `uploaded_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  `is_active` tinyint(1) NULL DEFAULT 1,
  `branch_id` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `fk_le_uploaded_by`(`uploaded_by`) USING BTREE,
  INDEX `fk_le_branch`(`branch_id`) USING BTREE,
  CONSTRAINT `fk_le_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `fk_le_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of latitude_extension
-- ----------------------------

-- ----------------------------
-- Table structure for maintenance_categories
-- ----------------------------
DROP TABLE IF EXISTS `maintenance_categories`;
CREATE TABLE `maintenance_categories`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `name`(`name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of maintenance_categories
-- ----------------------------
INSERT INTO `maintenance_categories` VALUES (1, 'Servicing Schedule', '2025-09-09 10:14:05');
INSERT INTO `maintenance_categories` VALUES (2, 'Worksheets', '2025-09-09 10:14:05');

-- ----------------------------
-- Table structure for modification
-- ----------------------------
DROP TABLE IF EXISTS `modification`;
CREATE TABLE `modification`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `mod_no` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `directorate` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `formation_id` int NULL DEFAULT NULL,
  `type_id` int NULL DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `recommended_date` date NULL DEFAULT NULL,
  `created_by` int NULL DEFAULT NULL,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  `updated_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0) ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `created_by`(`created_by`) USING BTREE,
  INDEX `idx_modification_formation`(`formation_id`) USING BTREE,
  INDEX `idx_modification_type`(`type_id`) USING BTREE,
  INDEX `idx_modification_directorate`(`directorate`) USING BTREE,
  CONSTRAINT `modification_ibfk_1` FOREIGN KEY (`formation_id`) REFERENCES `formation` (`formation_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `modification_ibfk_2` FOREIGN KEY (`type_id`) REFERENCES `type` (`type_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `modification_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of modification
-- ----------------------------

-- ----------------------------
-- Table structure for online_subscription
-- ----------------------------
DROP TABLE IF EXISTS `online_subscription`;
CREATE TABLE `online_subscription`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `related_aircraft` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `subscription_period` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `uploaded_by` int NULL DEFAULT NULL,
  `uploaded_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  `is_active` tinyint(1) NULL DEFAULT 1,
  `branch_id` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `fk_os_uploaded_by`(`uploaded_by`) USING BTREE,
  INDEX `fk_os_branch`(`branch_id`) USING BTREE,
  CONSTRAINT `fk_os_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `fk_os_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of online_subscription
-- ----------------------------

-- ----------------------------
-- Table structure for osh_categories
-- ----------------------------
DROP TABLE IF EXISTS `osh_categories`;
CREATE TABLE `osh_categories`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `name`(`name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of osh_categories
-- ----------------------------
INSERT INTO `osh_categories` VALUES (1, 'Audit Checklists', '2025-09-09 10:14:05');
INSERT INTO `osh_categories` VALUES (2, 'Audit Reports', '2025-09-09 10:14:05');
INSERT INTO `osh_categories` VALUES (3, 'Audit Plans', '2025-09-09 10:14:05');
INSERT INTO `osh_categories` VALUES (4, 'OSH Manual', '2025-09-09 10:14:05');

-- ----------------------------
-- Table structure for productivity_categories
-- ----------------------------
DROP TABLE IF EXISTS `productivity_categories`;
CREATE TABLE `productivity_categories`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `name`(`name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of productivity_categories
-- ----------------------------
INSERT INTO `productivity_categories` VALUES (1, 'Occupational Health & Safety', '2025-09-09 10:14:05');
INSERT INTO `productivity_categories` VALUES (2, 'Environment', '2025-09-09 10:14:05');
INSERT INTO `productivity_categories` VALUES (3, 'Quality Control Circle', '2025-09-09 10:14:05');
INSERT INTO `productivity_categories` VALUES (4, 'Awards', '2025-09-09 10:14:05');

-- ----------------------------
-- Table structure for productivity_documents
-- ----------------------------
DROP TABLE IF EXISTS `productivity_documents`;
CREATE TABLE `productivity_documents`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `productivity_category_id` int NULL DEFAULT NULL,
  `osh_category_id` int NULL DEFAULT NULL,
  `environment_category_id` int NULL DEFAULT NULL,
  `uploaded_by` int NULL DEFAULT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `uploaded_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  `is_active` tinyint(1) NULL DEFAULT 1,
  `branch_id` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `uploaded_by`(`uploaded_by`) USING BTREE,
  INDEX `fk_productivity_category`(`productivity_category_id`) USING BTREE,
  INDEX `fk_osh_category`(`osh_category_id`) USING BTREE,
  INDEX `fk_environment_category`(`environment_category_id`) USING BTREE,
  INDEX `fk_productivity_branch`(`branch_id`) USING BTREE,
  CONSTRAINT `fk_environment_category` FOREIGN KEY (`environment_category_id`) REFERENCES `environment_categories` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `fk_osh_category` FOREIGN KEY (`osh_category_id`) REFERENCES `osh_categories` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `fk_productivity_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `fk_productivity_category` FOREIGN KEY (`productivity_category_id`) REFERENCES `productivity_categories` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `productivity_documents_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of productivity_documents
-- ----------------------------
INSERT INTO `productivity_documents` VALUES (1, 'QCC registration form', 'QCC', 3, NULL, NULL, 1, 'uploads/productivity/doc_1.pdf', '2025-10-09 16:49:06', 1, NULL);

-- ----------------------------
-- Table structure for publication_categories
-- ----------------------------
DROP TABLE IF EXISTS `publication_categories`;
CREATE TABLE `publication_categories`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `name`(`name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of publication_categories
-- ----------------------------
INSERT INTO `publication_categories` VALUES (1, 'Online Subscription', '2025-09-09 10:14:05');
INSERT INTO `publication_categories` VALUES (2, 'Airworthiness Directives & Bulletins', '2025-09-09 10:14:05');
INSERT INTO `publication_categories` VALUES (3, 'QAI Safety Newsletters', '2025-09-09 10:14:05');
INSERT INTO `publication_categories` VALUES (4, 'Maintenance Programme', '2025-09-09 10:14:05');
INSERT INTO `publication_categories` VALUES (5, 'Technical Library', '2025-09-09 10:14:05');

-- ----------------------------
-- Table structure for publication_documents
-- ----------------------------
DROP TABLE IF EXISTS `publication_documents`;
CREATE TABLE `publication_documents`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `publication_category_id` int NULL DEFAULT NULL,
  `maintenance_category_id` int NULL DEFAULT NULL,
  `uploaded_by` int NULL DEFAULT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `uploaded_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  `is_active` tinyint(1) NULL DEFAULT 1,
  `branch_id` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `uploaded_by`(`uploaded_by`) USING BTREE,
  INDEX `fk_publication_category`(`publication_category_id`) USING BTREE,
  INDEX `fk_maintenance_category`(`maintenance_category_id`) USING BTREE,
  INDEX `fk_publication_branch`(`branch_id`) USING BTREE,
  CONSTRAINT `fk_maintenance_category` FOREIGN KEY (`maintenance_category_id`) REFERENCES `maintenance_categories` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `fk_publication_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `fk_publication_category` FOREIGN KEY (`publication_category_id`) REFERENCES `publication_categories` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `publication_documents_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of publication_documents
-- ----------------------------

-- ----------------------------
-- Table structure for qa_categories
-- ----------------------------
DROP TABLE IF EXISTS `qa_categories`;
CREATE TABLE `qa_categories`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `name`(`name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of qa_categories
-- ----------------------------
INSERT INTO `qa_categories` VALUES (1, 'Audit Checklists', '2025-09-09 10:14:05');
INSERT INTO `qa_categories` VALUES (2, 'Audit Reports', '2025-09-09 10:14:05');
INSERT INTO `qa_categories` VALUES (3, 'Audit Plans', '2025-09-09 10:14:05');

-- ----------------------------
-- Table structure for ranks
-- ----------------------------
DROP TABLE IF EXISTS `ranks`;
CREATE TABLE `ranks`  (
  `id` int NOT NULL,
  `rank_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `rank_name`(`rank_name`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ranks
-- ----------------------------
INSERT INTO `ranks` VALUES (2, 'AC');
INSERT INTO `ranks` VALUES (29, 'ACM');
INSERT INTO `ranks` VALUES (26, 'Air Cdre');
INSERT INTO `ranks` VALUES (28, 'Air Marshal');
INSERT INTO `ranks` VALUES (3, 'Aircraftman 2');
INSERT INTO `ranks` VALUES (27, 'AVM');
INSERT INTO `ranks` VALUES (6, 'Cpl');
INSERT INTO `ranks` VALUES (10, 'F Sgt');
INSERT INTO `ranks` VALUES (21, 'Fg Off');
INSERT INTO `ranks` VALUES (22, 'Flt Lt');
INSERT INTO `ranks` VALUES (25, 'Gp Capt');
INSERT INTO `ranks` VALUES (4, 'LAC');
INSERT INTO `ranks` VALUES (41, 'Miss');
INSERT INTO `ranks` VALUES (42, 'Mr');
INSERT INTO `ranks` VALUES (43, 'Mrs');
INSERT INTO `ranks` VALUES (13, 'MWO');
INSERT INTO `ranks` VALUES (20, 'Plt Off');
INSERT INTO `ranks` VALUES (8, 'Sgt');
INSERT INTO `ranks` VALUES (23, 'Sqn Ldr');
INSERT INTO `ranks` VALUES (24, 'Wg Cdr');
INSERT INTO `ranks` VALUES (12, 'WO');

-- ----------------------------
-- Table structure for rnd
-- ----------------------------
DROP TABLE IF EXISTS `rnd`;
CREATE TABLE `rnd`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `directorate` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `formation_id` int NULL DEFAULT NULL,
  `type_id` int NULL DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `rnd_no` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `issue_date` date NULL DEFAULT NULL,
  `created_by` int NULL DEFAULT NULL,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  `updated_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0) ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `created_by`(`created_by`) USING BTREE,
  INDEX `idx_rnd_formation`(`formation_id`) USING BTREE,
  INDEX `idx_rnd_type`(`type_id`) USING BTREE,
  INDEX `idx_rnd_directorate`(`directorate`) USING BTREE,
  CONSTRAINT `rnd_ibfk_1` FOREIGN KEY (`formation_id`) REFERENCES `formation` (`formation_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `rnd_ibfk_2` FOREIGN KEY (`type_id`) REFERENCES `type` (`type_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `rnd_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of rnd
-- ----------------------------

-- ----------------------------
-- Table structure for sections
-- ----------------------------
DROP TABLE IF EXISTS `sections`;
CREATE TABLE `sections`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `name`(`name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of sections
-- ----------------------------
INSERT INTO `sections` VALUES (1, 'qai', '2025-09-09 10:14:05');
INSERT INTO `sections` VALUES (2, 'services', '2025-09-09 10:14:05');
INSERT INTO `sections` VALUES (3, 'publication', '2025-09-09 10:14:05');
INSERT INTO `sections` VALUES (4, 'training', '2025-09-09 10:14:05');
INSERT INTO `sections` VALUES (5, 'productivity', '2025-09-09 10:14:05');

-- ----------------------------
-- Table structure for service_categories
-- ----------------------------
DROP TABLE IF EXISTS `service_categories`;
CREATE TABLE `service_categories`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `name`(`name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of service_categories
-- ----------------------------
INSERT INTO `service_categories` VALUES (1, 'Quality Assurance Audits', '2025-09-09 10:14:05');
INSERT INTO `service_categories` VALUES (2, 'Aircraft Competency', '2025-09-09 10:14:05');
INSERT INTO `service_categories` VALUES (3, 'Latitudes & Extensions', '2025-09-09 10:14:05');
INSERT INTO `service_categories` VALUES (4, 'Modifications R&D Projects', '2025-09-09 10:14:05');
INSERT INTO `service_categories` VALUES (5, 'Vehicle Emission Test', '2025-09-09 10:14:05');

-- ----------------------------
-- Table structure for service_documents
-- ----------------------------
DROP TABLE IF EXISTS `service_documents`;
CREATE TABLE `service_documents`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `service_category_id` int NULL DEFAULT NULL,
  `qa_category_id` int NULL DEFAULT NULL,
  `uploaded_by` int NULL DEFAULT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `uploaded_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  `is_active` tinyint(1) NULL DEFAULT 1,
  `branch_id` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `uploaded_by`(`uploaded_by`) USING BTREE,
  INDEX `fk_service_category`(`service_category_id`) USING BTREE,
  INDEX `fk_qa_category`(`qa_category_id`) USING BTREE,
  INDEX `fk_service_branch`(`branch_id`) USING BTREE,
  CONSTRAINT `fk_qa_category` FOREIGN KEY (`qa_category_id`) REFERENCES `qa_categories` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `fk_service_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `fk_service_category` FOREIGN KEY (`service_category_id`) REFERENCES `service_categories` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `service_documents_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 14 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of service_documents
-- ----------------------------
INSERT INTO `service_documents` VALUES (13, 'Test report one', 'Test report one description', 1, 2, 1, 'uploads/services/doc_13.pdf', '2025-09-28 17:02:01', 1, 4);

-- ----------------------------
-- Table structure for slaf_establishments
-- ----------------------------
DROP TABLE IF EXISTS `slaf_establishments`;
CREATE TABLE `slaf_establishments`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `is_active` tinyint(1) NULL DEFAULT 1,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 23 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of slaf_establishments
-- ----------------------------
INSERT INTO `slaf_establishments` VALUES (1, 'SLAF Academy CBY', 'CBY', 1, '2025-10-09 17:06:10');
INSERT INTO `slaf_establishments` VALUES (2, 'SLAF Base AHP', 'AHP', 1, '2025-10-09 17:06:10');
INSERT INTO `slaf_establishments` VALUES (3, 'SLAF Base HIN', 'HIN', 1, '2025-10-09 17:06:10');
INSERT INTO `slaf_establishments` VALUES (4, 'SLAF Base KAT', 'KAT', 1, '2025-10-09 17:06:10');
INSERT INTO `slaf_establishments` VALUES (5, 'SLAF Base RMA', 'RMA', 1, '2025-10-09 17:06:10');
INSERT INTO `slaf_establishments` VALUES (6, 'SLAF Base VNA', 'VNA', 1, '2025-10-09 17:06:10');
INSERT INTO `slaf_establishments` VALUES (7, 'SLAF CTS Dla', 'DLA', 1, '2025-10-09 17:06:10');
INSERT INTO `slaf_establishments` VALUES (8, 'SLAF RTC AMP', 'AMP', 1, '2025-10-09 17:06:10');
INSERT INTO `slaf_establishments` VALUES (9, 'SLAF Stn BCL', 'BCL', 1, '2025-10-09 17:06:10');
INSERT INTO `slaf_establishments` VALUES (10, 'SLAF STN BIA', 'BIA', 1, '2025-10-09 17:06:10');
INSERT INTO `slaf_establishments` VALUES (11, 'SLAF STN IRM', 'IRM', 1, '2025-10-09 17:06:10');
INSERT INTO `slaf_establishments` VALUES (12, 'SLAF Stn KGl', 'KGL', 1, '2025-10-09 17:06:10');
INSERT INTO `slaf_establishments` VALUES (13, 'SLAF Stn KTK', 'KTK', 1, '2025-10-09 17:06:10');
INSERT INTO `slaf_establishments` VALUES (14, 'SLAF STN MIR', 'MIR', 1, '2025-10-09 17:06:10');
INSERT INTO `slaf_establishments` VALUES (15, 'SLAF Stn MOW', 'MOW', 1, '2025-10-09 17:06:10');
INSERT INTO `slaf_establishments` VALUES (16, 'SLAF STN PGL', 'PGL', 1, '2025-10-09 17:06:10');
INSERT INTO `slaf_establishments` VALUES (17, 'SLAF STN PLV', 'PLV', 1, '2025-10-09 17:06:10');
INSERT INTO `slaf_establishments` VALUES (18, 'SLAF Stn PLY', 'PLY', 1, '2025-10-09 17:06:10');
INSERT INTO `slaf_establishments` VALUES (19, 'SLAF Stn SGR', 'SGR', 1, '2025-10-09 17:06:10');
INSERT INTO `slaf_establishments` VALUES (20, 'SLAF Stn SJP', 'SJP', 1, '2025-10-09 17:06:10');
INSERT INTO `slaf_establishments` VALUES (21, 'SLAF Stn WLA', 'WLA', 1, '2025-10-09 17:06:10');
INSERT INTO `slaf_establishments` VALUES (22, 'SLAF TTS Eka', 'EKA', 1, '2025-10-09 17:06:10');

-- ----------------------------
-- Table structure for training_categories
-- ----------------------------
DROP TABLE IF EXISTS `training_categories`;
CREATE TABLE `training_categories`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `name`(`name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of training_categories
-- ----------------------------
INSERT INTO `training_categories` VALUES (1, 'Approved Training Syllabus', '2025-09-09 10:14:05');
INSERT INTO `training_categories` VALUES (2, 'Continues Professional Development', '2025-09-09 10:14:05');
INSERT INTO `training_categories` VALUES (3, 'Productivity Improvement Professional Development', '2025-09-09 10:14:05');
INSERT INTO `training_categories` VALUES (4, 'Outside Training', '2025-09-09 10:14:05');

-- ----------------------------
-- Table structure for training_documents
-- ----------------------------
DROP TABLE IF EXISTS `training_documents`;
CREATE TABLE `training_documents`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `training_category_id` int NULL DEFAULT NULL,
  `uploaded_by` int NULL DEFAULT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `uploaded_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  `is_active` tinyint(1) NULL DEFAULT 1,
  `branch_id` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `uploaded_by`(`uploaded_by`) USING BTREE,
  INDEX `fk_training_category`(`training_category_id`) USING BTREE,
  INDEX `fk_training_branch`(`branch_id`) USING BTREE,
  CONSTRAINT `fk_training_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `fk_training_category` FOREIGN KEY (`training_category_id`) REFERENCES `training_categories` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `training_documents_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of training_documents
-- ----------------------------

-- ----------------------------
-- Table structure for type
-- ----------------------------
DROP TABLE IF EXISTS `type`;
CREATE TABLE `type`  (
  `type_id` int NOT NULL AUTO_INCREMENT,
  `type_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`type_id`) USING BTREE,
  UNIQUE INDEX `type_name`(`type_name`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of type
-- ----------------------------
INSERT INTO `type` VALUES (1, 'K-08', '2025-10-17 21:41:10');
INSERT INTO `type` VALUES (2, 'PT-6', '2025-10-17 21:41:10');
INSERT INTO `type` VALUES (3, 'C-150', '2025-10-17 21:41:10');
INSERT INTO `type` VALUES (4, 'C-130', '2025-10-17 21:41:10');
INSERT INTO `type` VALUES (5, 'AN32B', '2025-10-17 21:41:10');
INSERT INTO `type` VALUES (6, 'B-200', '2025-10-17 21:41:10');
INSERT INTO `type` VALUES (7, 'B-300', '2025-10-17 21:41:10');
INSERT INTO `type` VALUES (8, 'BELL-412/412EP', '2025-10-17 21:41:10');
INSERT INTO `type` VALUES (9, 'BELL-212', '2025-10-17 21:41:10');
INSERT INTO `type` VALUES (10, 'MI-17', '2025-10-17 21:41:10');
INSERT INTO `type` VALUES (11, 'F-7GS', '2025-10-17 21:41:10');
INSERT INTO `type` VALUES (12, 'BELL-206', '2025-10-17 21:41:10');
INSERT INTO `type` VALUES (13, 'Y-12II/IV', '2025-10-17 21:41:10');
INSERT INTO `type` VALUES (14, 'MI-24/35', '2025-10-17 21:41:10');
INSERT INTO `type` VALUES (15, 'Kfir', '2025-10-17 21:41:10');
INSERT INTO `type` VALUES (16, 'Lihiniya MK-I/II', '2025-10-17 21:41:10');
INSERT INTO `type` VALUES (17, 'Bay Servicing', '2025-10-17 21:41:10');
INSERT INTO `type` VALUES (18, 'Communication System', '2025-10-17 21:41:10');
INSERT INTO `type` VALUES (19, 'Navigation System', '2025-10-17 21:41:10');
INSERT INTO `type` VALUES (20, 'Radar System', '2025-10-17 21:41:10');
INSERT INTO `type` VALUES (21, 'AGSE', '2025-10-17 21:41:10');
INSERT INTO `type` VALUES (22, 'OTHER', '2025-10-17 21:41:10');
INSERT INTO `type` VALUES (23, 'All Aircraft', '2025-10-17 21:41:10');
INSERT INTO `type` VALUES (24, 'RADAR', '2025-10-17 21:41:10');
INSERT INTO `type` VALUES (25, 'Cesna 421', '2025-10-17 21:41:10');
INSERT INTO `type` VALUES (26, 'AVRO', '2025-10-17 21:41:10');
INSERT INTO `type` VALUES (27, 'Y-8', '2025-10-17 21:41:10');
INSERT INTO `type` VALUES (28, 'BH-2', '2025-10-17 21:41:10');
INSERT INTO `type` VALUES (29, 'CHIPMUNK', '2025-10-17 21:41:10');
INSERT INTO `type` VALUES (30, 'Tiger Month', '2025-10-17 21:41:10');
INSERT INTO `type` VALUES (31, 'Siaimar Chetti', '2025-10-17 21:41:10');
INSERT INTO `type` VALUES (32, 'Lihiniya MK-1& MK-1E', '2025-10-17 21:41:10');

-- ----------------------------
-- Table structure for vehicle_emission_test
-- ----------------------------
DROP TABLE IF EXISTS `vehicle_emission_test`;
CREATE TABLE `vehicle_emission_test`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `serial_no` int NULL DEFAULT NULL,
  `camp_id` int NOT NULL,
  `vehicle_no` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `vehicle_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `fuel_type` enum('Diesel','Petrol') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'Diesel',
  `model` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `test_date` date NOT NULL,
  `first_test` decimal(10, 2) NULL DEFAULT NULL,
  `second_test` decimal(10, 2) NULL DEFAULT NULL,
  `third_test` decimal(10, 2) NULL DEFAULT NULL,
  `average` decimal(10, 2) NULL DEFAULT NULL,
  `rpm_2500_hc` decimal(10, 2) NULL DEFAULT NULL,
  `rpm_2500_co` decimal(10, 2) NULL DEFAULT NULL,
  `idle_hc` decimal(10, 2) NULL DEFAULT NULL,
  `idle_co` decimal(10, 2) NULL DEFAULT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `next_due_date` date NULL DEFAULT NULL,
  `remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `created_by` int NULL DEFAULT NULL,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  `updated_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0) ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `fk_vet_camp`(`camp_id`) USING BTREE,
  INDEX `fk_vet_created_by`(`created_by`) USING BTREE,
  INDEX `idx_fuel_type`(`fuel_type`) USING BTREE,
  CONSTRAINT `fk_vet_camp` FOREIGN KEY (`camp_id`) REFERENCES `slaf_establishments` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_vet_created_by` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of vehicle_emission_test
-- ----------------------------

SET FOREIGN_KEY_CHECKS = 1;
