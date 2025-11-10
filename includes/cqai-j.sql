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
-- Table structure for admins
-- ----------------------------
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET latin1 NOT NULL,
  `branch` varchar(100) CHARACTER SET latin1 NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET latin1 NULL DEFAULT NULL,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  `last_login` timestamp(0) NULL DEFAULT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `username`(`username`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of admins
-- ----------------------------
INSERT INTO `admins` VALUES (1, 'admin', 'all', '$2y$10$2knpl.JqcMC0S0CdK4fkx.ESgqJiGEZcyXqjDHuyWdgFJ1sFvpE4e', '2025-09-09 10:14:05', NULL, 1);

-- ----------------------------
-- Table structure for branches
-- ----------------------------
DROP TABLE IF EXISTS `branches`;
CREATE TABLE `branches`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `name`(`name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 9 CHARACTER SET = utf8mb4 ROW_FORMAT = Dynamic;

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
-- Table structure for latitude_extension
-- ----------------------------
DROP TABLE IF EXISTS `latitude_extension`;
CREATE TABLE `latitude_extension`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `description` text CHARACTER SET utf8mb4 NULL,
  `latitude_description` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `related_aircraft` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
  `latitude_period` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
  `uploaded_by` int NULL DEFAULT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `uploaded_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  `is_active` tinyint(1) NULL DEFAULT 1,
  `branch_id` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `fk_le_uploaded_by`(`uploaded_by`) USING BTREE,
  INDEX `fk_le_branch`(`branch_id`) USING BTREE,
  CONSTRAINT `fk_le_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `fk_le_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of latitude_extension
-- ----------------------------

-- ----------------------------
-- Table structure for maintenance_categories
-- ----------------------------
DROP TABLE IF EXISTS `maintenance_categories`;
CREATE TABLE `maintenance_categories`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `name`(`name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 ROW_FORMAT = Dynamic;

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
  `title` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `description` text CHARACTER SET utf8mb4 NULL,
  `related_aircraft` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
  `subscription_period` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `uploaded_by` int NULL DEFAULT NULL,
  `uploaded_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  `is_active` tinyint(1) NULL DEFAULT 1,
  `branch_id` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `fk_os_uploaded_by`(`uploaded_by`) USING BTREE,
  INDEX `fk_os_branch`(`branch_id`) USING BTREE,
  CONSTRAINT `fk_os_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `fk_os_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of online_subscription
-- ----------------------------

-- ----------------------------
-- Table structure for osh_categories
-- ----------------------------
DROP TABLE IF EXISTS `osh_categories`;
CREATE TABLE `osh_categories`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `name`(`name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 ROW_FORMAT = Dynamic;

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
  `name` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `name`(`name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of productivity_categories
-- ----------------------------
INSERT INTO `productivity_categories` VALUES (1, 'Productivity', '2025-09-09 10:14:05');
INSERT INTO `productivity_categories` VALUES (2, 'Occupational Safety & Health', '2025-09-09 10:14:05');
INSERT INTO `productivity_categories` VALUES (3, 'Environmental Mgt', '2025-09-09 10:14:05');
INSERT INTO `productivity_categories` VALUES (4, 'Awards', '2025-09-09 10:14:05');

-- ----------------------------
-- Table structure for publication_categories
-- ----------------------------
DROP TABLE IF EXISTS `publication_categories`;
CREATE TABLE `publication_categories`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `name`(`name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 ROW_FORMAT = Dynamic;

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
  `title` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `description` text CHARACTER SET utf8mb4 NULL,
  `publication_category_id` int NULL DEFAULT NULL,
  `maintenance_category_id` int NULL DEFAULT NULL,
  `uploaded_by` int NULL DEFAULT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
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
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of publication_documents
-- ----------------------------

-- ----------------------------
-- Table structure for qa_categories
-- ----------------------------
DROP TABLE IF EXISTS `qa_categories`;
CREATE TABLE `qa_categories`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `name`(`name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of qa_categories
-- ----------------------------
INSERT INTO `qa_categories` VALUES (1, 'Audit Checklists', '2025-09-09 10:14:05');
INSERT INTO `qa_categories` VALUES (2, 'Audit Reports', '2025-09-09 10:14:05');
INSERT INTO `qa_categories` VALUES (3, 'Audit Plans', '2025-09-09 10:14:05');

-- ----------------------------
-- Table structure for ac_categories
-- ----------------------------
DROP TABLE IF EXISTS `ac_categories`;
CREATE TABLE `ac_categories`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `name`(`name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ac_categories
-- ----------------------------
INSERT INTO `ac_categories` VALUES (1, 'AE', '2025-09-09 10:14:05');
INSERT INTO `ac_categories` VALUES (2, 'GE', '2025-09-09 10:14:05');
INSERT INTO `ac_categories` VALUES (3, 'EE', '2025-09-09 10:14:05');

-- ----------------------------
-- Table structure for sections
-- ----------------------------
DROP TABLE IF EXISTS `sections`;
CREATE TABLE `sections`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `name`(`name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 ROW_FORMAT = Dynamic;

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
  `name` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `name`(`name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 ROW_FORMAT = Dynamic;

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
  `title` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `description` text CHARACTER SET utf8mb4 NULL,
  `service_category_id` int NULL DEFAULT NULL,
  `qa_category_id` int NULL DEFAULT NULL,
  `uploaded_by` int NULL DEFAULT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
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
) ENGINE = InnoDB AUTO_INCREMENT = 14 CHARACTER SET = utf8mb4 ROW_FORMAT = Dynamic;

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
  `name` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `code` varchar(50) CHARACTER SET utf8mb4 NULL DEFAULT NULL,
  `is_active` tinyint(1) NULL DEFAULT 1,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 23 CHARACTER SET = utf8mb4 ROW_FORMAT = Dynamic;

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
  `name` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `name`(`name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 ROW_FORMAT = Dynamic;

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
  `title` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `description` text CHARACTER SET utf8mb4 NULL,
  `training_category_id` int NULL DEFAULT NULL,
  `uploaded_by` int NULL DEFAULT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
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
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of training_documents
-- ----------------------------


SET FOREIGN_KEY_CHECKS = 1;



-- Create Formation table
DROP TABLE IF EXISTS formation;
CREATE TABLE formation (
    formation_id INT AUTO_INCREMENT PRIMARY KEY,
    formation_name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Type table
DROP TABLE IF EXISTS type;
CREATE TABLE type (
    type_id INT AUTO_INCREMENT PRIMARY KEY,
    type_name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Aircraft Competency table
DROP TABLE IF EXISTS aircraft_competency;
CREATE TABLE aircraft_competency (
    record_id INT AUTO_INCREMENT PRIMARY KEY,
    svc_no VARCHAR(50),
    rank VARCHAR(50),
    name VARCHAR(100),
    branch VARCHAR(50),
    trade VARCHAR(100),
    formation_id INT,
    posted_in_date DATE,
    posted_out_date DATE,
    type_id INT,
    competency_level VARCHAR(100),
    training_start_date DATE,
    training_end_date DATE,
    formation_ref VARCHAR(100),
    for_ref_date DATE,
    qai_ref VARCHAR(100),
    qai_ref_date DATE,
    dt_ref VARCHAR(100),
    dt_ref_date DATE,
    qao_ref VARCHAR(100),
    qao_ref_date DATE,
    theory_marks DECIMAL(5,2),
    practical_marks DECIMAL(5,2),
    competency_issue_ref VARCHAR(100),
    com_issue_date DATE,
    competency_renew_ref VARCHAR(100),
    renew_date DATE,
    certificate_no VARCHAR(100),
    cer_issued_date DATE,
    retired_date DATE,
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (formation_id) REFERENCES formation(formation_id),
    FOREIGN KEY (type_id) REFERENCES type(type_id)
);

-- Insert formations
INSERT INTO formation (formation_name) VALUES
 ('No 1 FTW'), ('02 SQN'), ('03 SQN'),
('04 SQN'), ('05 SQN'), ('06 SQN'), ('07 SQN'), ('08 SQN'), ('09 SQN'),
('10 SQN'), ('11 SQN'), ('61 FLT'), ('Helitours'), ('ASW'),('AEW'),
('AOW'), ('AFM'), ('AR & DW'), ('RMW'), ('RADAR SQN'),
('E &TE Rma'), ('E &TE Kat');

INSERT INTO formation (formation_name) VALUES
 ('12 SQN'), ('14 SQN'), ('112 SQN'),
('KDU'), ('MINIUSCA');

-- Insert aircraft types
INSERT INTO type (type_name) VALUES
('K-08'), ('PT-6'), ('C-150'), ('C-130'), ('AN32B'),
('B-200'), ('B-300'), ('BELL-412/412EP'), ('BELL-212'),
('MI-17'), ('F-7GS'), ('BELL-206'), ('Y-12II/IV'),
('MI-24/35'), ('Kfir'), ('Lihiniya MK-I/II'), ('Bay Servicing'),
('Communication System'), ('Navigation System'), ('Radar System'),
('AGSE'), ('OTHER'), ('All Aircraft'), ('RADAR');

INSERT INTO type (type_name) VALUES
('Cesna 421'), ('AVRO'), ('Y-8'), ('BH-2'), ('CHIPMUNK'),
('Tiger Month'), ('Siaimar Chetti'), ('Lihiniya MK-1& MK-1E');


-- If branch contains text values like 'AE', 'GE', etc., update them to match IDs
UPDATE aircraft_competency SET branch = 1 WHERE branch = 'AE';
UPDATE aircraft_competency SET branch = 2 WHERE branch = 'GE';
UPDATE aircraft_competency SET branch = 3 WHERE branch = 'EE';
UPDATE aircraft_competency SET branch = 4 WHERE branch = 'CPD';

-- Now alter the table
ALTER TABLE aircraft_competency MODIFY COLUMN branch INT;
ALTER TABLE aircraft_competency ADD FOREIGN KEY (branch) REFERENCES ac_categories(id);

-- Create latitude table
DROP TABLE IF EXISTS latitude;
CREATE TABLE latitude (
    id INT AUTO_INCREMENT PRIMARY KEY,
    active ENUM('YES','NO') DEFAULT 'YES',
    type VARCHAR(100) DEFAULT 'Latitude',
    formation_id INT NOT NULL,
    aircraft_type_id INT NOT NULL,
    tail_no VARCHAR(50),
    part_no VARCHAR(100),
    description TEXT,
    serial_no VARCHAR(100),
    reason TEXT,
    hrs DECIMAL(10,2),
    ldgs INT,
    date DATE,
    present_latitude VARCHAR(100),
    dgae_auth_ref VARCHAR(100),
    auth_date DATE,
    latitude_expiry DATE,
    total_prev_latitude VARCHAR(100),
    demand_ref VARCHAR(100),
    status VARCHAR(50),
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (formation_id) REFERENCES formation(formation_id),
    FOREIGN KEY (aircraft_type_id) REFERENCES type(type_id),
    FOREIGN KEY (created_by) REFERENCES admins(id)
);

-- Create modification table
DROP TABLE IF EXISTS modification;
CREATE TABLE modification (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mod_no VARCHAR(100),
    directorate VARCHAR(100),
    formation_id INT,
    type_id INT,
    description TEXT,
    recommended_date DATE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (formation_id) REFERENCES formation(formation_id),
    FOREIGN KEY (type_id) REFERENCES type(type_id),
    FOREIGN KEY (created_by) REFERENCES admins(id)
);

-- Create rnd table
DROP TABLE IF EXISTS rnd;
CREATE TABLE rnd (
    id INT AUTO_INCREMENT PRIMARY KEY,
    directorate VARCHAR(100),
    formation_id INT,
    type_id INT,
    description TEXT,
    rnd_no VARCHAR(100),
    issue_date DATE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (formation_id) REFERENCES formation(formation_id),
    FOREIGN KEY (type_id) REFERENCES type(type_id),
    FOREIGN KEY (created_by) REFERENCES admins(id)
);

-- Create indexes for better performance
CREATE INDEX idx_modification_formation ON modification(formation_id);
CREATE INDEX idx_modification_type ON modification(type_id);
CREATE INDEX idx_modification_directorate ON modification(directorate);

CREATE INDEX idx_rnd_formation ON rnd(formation_id);
CREATE INDEX idx_rnd_type ON rnd(type_id);
CREATE INDEX idx_rnd_directorate ON rnd(directorate);

-- Drop existing table and recreate with both diesel and petrol support
DROP TABLE IF EXISTS `vehicle_emission_test`;

CREATE TABLE `vehicle_emission_test` (
  `id` int NOT NULL AUTO_INCREMENT,
  `serial_no` int DEFAULT NULL,
  `camp_id` int NOT NULL,
  `vehicle_no` varchar(100) NOT NULL,
  `vehicle_type` varchar(100) NOT NULL,
  `fuel_type` enum('Diesel','Petrol') NOT NULL DEFAULT 'Diesel',
  `model` varchar(100) DEFAULT NULL,
  `test_date` date NOT NULL,
  
  -- Diesel Test Parameters
  `first_test` decimal(10,2) DEFAULT NULL,
  `second_test` decimal(10,2) DEFAULT NULL,
  `third_test` decimal(10,2) DEFAULT NULL,
  `average` decimal(10,2) DEFAULT NULL,
  
  -- Petrol Test Parameters
  `rpm_2500_hc` decimal(10,2) DEFAULT NULL,
  `rpm_2500_co` decimal(10,2) DEFAULT NULL,
  `idle_hc` decimal(10,2) DEFAULT NULL,
  `idle_co` decimal(10,2) DEFAULT NULL,
  
  `status` varchar(50) NOT NULL,
  `next_due_date` date DEFAULT NULL,
  `remarks` text,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_vet_camp` (`camp_id`),
  KEY `fk_vet_created_by` (`created_by`),
  KEY `idx_fuel_type` (`fuel_type`),
  CONSTRAINT `fk_vet_camp` FOREIGN KEY (`camp_id`) REFERENCES `slaf_establishments` (`id`),
  CONSTRAINT `fk_vet_created_by` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;

-- ----------------------------
-- Table structure for ad_bulletins
-- ----------------------------
DROP TABLE IF EXISTS `ad_bulletins`;
CREATE TABLE `ad_bulletins`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `reference_no` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `bulletin_description` text CHARACTER SET utf8mb4 NOT NULL,
  `related_aircraft_id` int NOT NULL,
  `formation_id` int NOT NULL,
  `date_of_issue` date NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `reference_no`(`reference_no`) USING BTREE,
  INDEX `related_aircraft_id`(`related_aircraft_id`) USING BTREE,
  INDEX `formation_id`(`formation_id`) USING BTREE,
  CONSTRAINT `ad_bulletins_ibfk_1` FOREIGN KEY (`related_aircraft_id`) REFERENCES `type` (`type_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `ad_bulletins_ibfk_2` FOREIGN KEY (`formation_id`) REFERENCES `formation` (`formation_id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for qai_newsletters
-- ----------------------------
DROP TABLE IF EXISTS `qai_newsletters`;
CREATE TABLE `qai_newsletters` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sno` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `description` text NOT NULL,
  `issue_date` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;

-- ----------------------------
-- Table structure for tech_librarys
-- ----------------------------
DROP TABLE IF EXISTS `tech_library`;
CREATE TABLE `tech_library`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `sno` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `publication_index` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for maintenance_documents
-- ----------------------------
DROP TABLE IF EXISTS `maintenance_documents`;
CREATE TABLE `maintenance_documents`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `document_type` enum('worksheet','schedule') NOT NULL,
  `document_number` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `description` text CHARACTER SET utf8mb4 NOT NULL,
  `formation_id` int NOT NULL,
  `trade` varchar(255) CHARACTER SET utf8mb4 NULL DEFAULT NULL,
  `type_id` int NOT NULL,
  `issue` varchar(50) CHARACTER SET utf8mb4 NOT NULL,
  `revision` varchar(50) CHARACTER SET utf8mb4 NULL DEFAULT NULL,
  `revision_date` date NULL DEFAULT NULL,
  `branch_id` int NOT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `fk_md_formation`(`formation_id`) USING BTREE,
  INDEX `fk_md_type`(`type_id`) USING BTREE,
  INDEX `fk_md_branch`(`branch_id`) USING BTREE,
  INDEX `idx_document_type`(`document_type`) USING BTREE,
  INDEX `idx_document_number`(`document_number`) USING BTREE,
  CONSTRAINT `fk_md_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_md_formation` FOREIGN KEY (`formation_id`) REFERENCES `formation` (`formation_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_md_type` FOREIGN KEY (`type_id`) REFERENCES `type` (`type_id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for training_syllabus
-- ----------------------------
DROP TABLE IF EXISTS `training_syllabus`;
CREATE TABLE `training_syllabus`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `syllabus_no` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `formation_id` int NOT NULL,
  `type_id` int NOT NULL,
  `trade` varchar(255) CHARACTER SET utf8mb4 NULL DEFAULT NULL,
  `syllabus_type`varchar(255) CHARACTER SET utf8mb4 NULL DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 NOT NULL,
  `issue` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `revision` varchar(50) CHARACTER SET utf8mb4 NULL DEFAULT NULL,
  `revision_date` date NULL DEFAULT NULL,
  `ac_categories_id` int NOT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `idx_syllabus_no`(`syllabus_no`) USING BTREE,
  INDEX `fk_ts_formation`(`formation_id`) USING BTREE,
  INDEX `fk_ts_type`(`type_id`) USING BTREE,
  INDEX `ac_categories`(`ac_categories_id`) USING BTREE,
  INDEX `idx_syllabus_type`(`syllabus_type`) USING BTREE,
  INDEX `idx_trade`(`trade`) USING BTREE,
  CONSTRAINT `fk_ts_ac_categories` FOREIGN KEY (`ac_categories_id`) REFERENCES `ac_categories` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_ts_formation` FOREIGN KEY (`formation_id`) REFERENCES `formation` (`formation_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_ts_type` FOREIGN KEY (`type_id`) REFERENCES `type` (`type_id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for training_record_Cpd
-- ----------------------------
DROP TABLE IF EXISTS `training_record_cpd`;
CREATE TABLE `training_record_cpd`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `sno` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `trade` varchar(255) CHARACTER SET utf8mb4 NULL DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 NOT NULL,
  `duration` varchar(255) CHARACTER SET utf8mb4 NULL DEFAULT NULL,
  `ac_categories_id` int NOT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  FOREIGN KEY (`ac_categories_id`) REFERENCES `ac_categories` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 ROW_FORMAT = Dynamic;
ALTER TABLE `training_record_cpd` 
ADD COLUMN `created_by` int NULL AFTER `file_path`,
ADD COLUMN `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
ADD FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`);
-- ----------------------------
-- Table structure for ranks
-- ----------------------------
DROP TABLE IF EXISTS `ranks`;
CREATE TABLE `ranks`  (
  `id` int NOT NULL,
  `rank_name` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `rank_name`(`rank_name`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 ROW_FORMAT = Dynamic;

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
-- Table structure for active_qcc
-- ----------------------------
DROP Table IF EXISTS active_qcc;
CREATE TABLE `active_qcc` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sno` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `qcc_name` varchar(255) NOT NULL,
  `slaf_establishment_id` int NOT NULL,
  `location` varchar(255) NOT NULL,
  `team_members` text NOT NULL,
  `category_id` int NULL DEFAULT 1,
  `section_id` int NULL DEFAULT 5,
  `created_by` int NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`slaf_establishment_id`) REFERENCES `slaf_establishments` (`id`),
  FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`),
  FOREIGN KEY (`category_id`) REFERENCES `productivity_categories` (`id`)
);

-- ----------------------------
-- Table structure for audit_report
-- ----------------------------
DROP TABLE IF EXISTS audit_report;
CREATE TABLE `audit_report` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sno` varchar(100) NOT NULL,
  `slaf_establishment_id` int NOT NULL,
  `conducted_date` date NOT NULL,
  `productivity_category_id` int NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `section_id` int NULL DEFAULT 5,
  `uploaded_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`slaf_establishment_id`) REFERENCES `slaf_establishments` (`id`),
  FOREIGN KEY (`productivity_category_id`) REFERENCES `productivity_categories` (`id`),
  FOREIGN KEY (`uploaded_by`) REFERENCES `admins` (`id`),
  FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for osh_manual
-- ----------------------------
DROP Table IF EXISTS osh_manual;
CREATE TABLE `osh_manual` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sno` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `description` text CHARACTER SET utf8mb4 NOT NULL,
  `manual_no` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  'rev_status' varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `category_id` int NULL DEFAULT 3,
  `section_id` int NULL DEFAULT 5,
  `created_by` int NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`),
  FOREIGN KEY (`category_id`) REFERENCES `productivity_categories` (`id`)
);

-- ----------------------------
-- Table structure for awards
-- ----------------------------
DROP TABLE IF EXISTS `awards`;
CREATE TABLE `awards` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sno` varchar(100) NOT NULL,
  `year` year NOT NULL,
  `slaf_establishment_id` int NOT NULL,
  `qcc_name` varchar(500) NULL,
  `placement` enum('1st' , '2nd','3rd') NOT NULL,
  `team_members` text NOT NULL,
  `award_type` enum('qcc','environment') NOT NULL COMMENT 'qcc=Best Quality Control Circle, environment=Best Environment Management Project',
  `category_id` int NULL DEFAULT 4,
  `section_id` int NULL DEFAULT 5,
  `created_by` int NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`slaf_establishment_id`) REFERENCES `slaf_establishments` (`id`),
  FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`),
  FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`),
  FOREIGN KEY (`category_id`) REFERENCES `productivity_categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ----------------------------
-- Table structure for training_syllabus_cpd
-- ----------------------------
DROP TABLE IF EXISTS `training_syllabus_cpd`;
CREATE TABLE `training_syllabus_cpd`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `syllabus_no` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `trade` varchar(255) CHARACTER SET utf8mb4 NULL DEFAULT NULL,
  `course_description` varchar(255) CHARACTER SET utf8mb4 NULL DEFAULT NULL,
  `issue` varchar(255) CHARACTER SET utf8mb4 NULL DEFAULT NULL,
  `revision` varchar(255) CHARACTER SET utf8mb4 NULL DEFAULT NULL,
  `revised_date` date NULL DEFAULT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `created_by` int NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for training_syllabus_cpd
-- ----------------------------
DROP TABLE IF EXISTS `training_syllabus_cpd`;
CREATE TABLE `training_syllabus_cpd`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `syllabus_no` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `trade` varchar(255) CHARACTER SET utf8mb4 NULL DEFAULT NULL,
  `course_description` varchar(255) CHARACTER SET utf8mb4 NULL DEFAULT NULL,
  `issue` varchar(255) CHARACTER SET utf8mb4 NULL DEFAULT NULL,
  `revision` varchar(255) CHARACTER SET utf8mb4 NULL DEFAULT NULL,
  `revised_date` date NULL DEFAULT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `ac_categories_id` int NOT NULL,
  `created_by` int NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  FOREIGN KEY (`ac_categories_id`) REFERENCES `ac_categories` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 ROW_FORMAT = Dynamic;