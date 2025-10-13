/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 80041
 Source Host           : localhost:3306
 Source Schema         : cqai-j

 Target Server Type    : MySQL
 Target Server Version : 80041
 File Encoding         : 65001

 Date: 13/10/2025 11:30:27
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

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
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

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
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

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
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of admins
-- ----------------------------
INSERT INTO `admins` VALUES (1, 'admin', 'all', '$2y$10$2knpl.JqcMC0S0CdK4fkx.ESgqJiGEZcyXqjDHuyWdgFJ1sFvpE4e', '2025-09-09 10:14:05', NULL, 1);

-- ----------------------------
-- Table structure for aircraft_competency
-- ----------------------------
DROP TABLE IF EXISTS `aircraft_competency`;
CREATE TABLE `aircraft_competency`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `svc_no` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `rank` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `aircraft_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `last_level_of_competency` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `renewal_date` date NOT NULL,
  `currency` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `squadron` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `uploaded_by` int NULL DEFAULT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `uploaded_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  `is_active` tinyint(1) NULL DEFAULT 1,
  `branch_id` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `fk_ac_uploaded_by`(`uploaded_by`) USING BTREE,
  INDEX `fk_ac_branch`(`branch_id`) USING BTREE,
  CONSTRAINT `fk_ac_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `fk_ac_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of aircraft_competency
-- ----------------------------
INSERT INTO `aircraft_competency` VALUES (1, 'Test', 'Test d', '99999', 'Sqn Ldr', 'Test', 'PT 6', '1', '2026-08-08', '1', '2', 1, 'uploads/services/doc_9.pdf', '2025-09-28 10:33:05', 0, 1);

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
) ENGINE = InnoDB AUTO_INCREMENT = 9 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

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
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of environment_categories
-- ----------------------------
INSERT INTO `environment_categories` VALUES (1, 'Audit Checklists', '2025-09-09 10:14:05');
INSERT INTO `environment_categories` VALUES (2, 'Audit Reports', '2025-09-09 10:14:05');
INSERT INTO `environment_categories` VALUES (3, 'Audit Plans', '2025-09-09 10:14:05');

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
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

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
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of maintenance_categories
-- ----------------------------
INSERT INTO `maintenance_categories` VALUES (1, 'Servicing Schedule', '2025-09-09 10:14:05');
INSERT INTO `maintenance_categories` VALUES (2, 'Worksheets', '2025-09-09 10:14:05');

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
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

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
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

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
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

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
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

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
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

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
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

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
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of qa_categories
-- ----------------------------
INSERT INTO `qa_categories` VALUES (1, 'Audit Checklists', '2025-09-09 10:14:05');
INSERT INTO `qa_categories` VALUES (2, 'Audit Reports', '2025-09-09 10:14:05');
INSERT INTO `qa_categories` VALUES (3, 'Audit Plans', '2025-09-09 10:14:05');

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
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

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
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

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
) ENGINE = InnoDB AUTO_INCREMENT = 14 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

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
) ENGINE = InnoDB AUTO_INCREMENT = 23 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

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
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

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
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of training_documents
-- ----------------------------

-- ----------------------------
-- Table structure for vehicle_emission_test
-- ----------------------------
DROP TABLE IF EXISTS `vehicle_emission_test`;
CREATE TABLE `vehicle_emission_test`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `vehicle_no` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `test_performed_date` date NOT NULL,
  `state` enum('Pass','Fail') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `uploaded_by` int NULL DEFAULT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `uploaded_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  `is_active` tinyint(1) NULL DEFAULT 1,
  `branch_id` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `fk_vet_uploaded_by`(`uploaded_by`) USING BTREE,
  INDEX `fk_vet_branch`(`branch_id`) USING BTREE,
  CONSTRAINT `fk_vet_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `fk_vet_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of vehicle_emission_test
-- ----------------------------

SET FOREIGN_KEY_CHECKS = 1;
