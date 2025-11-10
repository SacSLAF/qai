DROP TABLE IF EXISTS `productivity_documents`;
CREATE TABLE `productivity_documents`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `description` text CHARACTER SET utf8mb4 NULL,
  `productivity_category_id` int NULL DEFAULT NULL,
  `osh_category_id` int NULL DEFAULT NULL,
  `environment_category_id` int NULL DEFAULT NULL,
  `uploaded_by` int NULL DEFAULT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
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
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 ROW_FORMAT = Dynamic;


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