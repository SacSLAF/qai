/* Table structure for table `admins` */
DROP TABLE IF EXISTS `admins`;

CREATE TABLE `admins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET latin1 NOT NULL,
  `branch` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  `password` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `username` (`username`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

/* Data for the table `admins` */
INSERT INTO `admins` (`id`, `username`, `branch`, `password`, `last_login`, `is_admin`) VALUES 
(1, 'admin', 'all', '$2y$10$2knpl.JqcMC0S0CdK4fkx.ESgqJiGEZcyXqjDHuyWdgFJ1sFvpE4e', NULL, 1);

/* Table structure for table `sections` */
DROP TABLE IF EXISTS `sections`;

CREATE TABLE `sections` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

/* Data for the table `sections` */
INSERT INTO `sections` (`id`, `name`) VALUES 
(1, 'qai'),
(2, 'services'),
(3, 'publication'),
(4, 'training'),
(5, 'productivity');

/* Table structure for table `branches` */
DROP TABLE IF EXISTS `branches`;

CREATE TABLE `branches` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4;

/* Data for the table `branches` */
INSERT INTO `branches` (`id`, `name`) VALUES 
(1, 'Aeronautical Engineering'),
(2, 'Air Operations'),
(3, 'Construction Engineering'),
(4, 'Electronic Engineering'),
(5, 'General Engineering'),
(6, 'Ground Operations'),
(7, 'Productivity Management'),
(8, 'Training');

/* Table structure for table `service_categories` */
DROP TABLE IF EXISTS `service_categories`;

CREATE TABLE `service_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

/* Data for the table `service_categories` */
INSERT INTO `service_categories` (`id`, `name`) VALUES 
(1, 'Quality Assurance Audits'),
(2, 'Aircraft Competency'),
(3, 'Latitudes & Extensions'),
(4, 'Modifications R&D Projects'),
(5, 'Vehicle Emission Test');

/* Table structure for table `qa_categories` */
DROP TABLE IF EXISTS `qa_categories`;

CREATE TABLE `qa_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

/* Data for the table `qa_categories` */
INSERT INTO `qa_categories` (`id`, `name`) VALUES 
(1, 'Audit Checklists'),
(2, 'Audit Reports'),
(3, 'Audit Plans');

/* Table structure for table `service_documents` */
DROP TABLE IF EXISTS `service_documents`;

CREATE TABLE `service_documents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `service_category_id` int DEFAULT NULL,
  `qa_category_id` int DEFAULT NULL,
  `uploaded_by` int DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT '1',
  `branch_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uploaded_by` (`uploaded_by`),
  KEY `fk_service_category` (`service_category_id`),
  KEY `fk_qa_category` (`qa_category_id`),
  KEY `fk_service_branch` (`branch_id`),
  CONSTRAINT `fk_qa_category` FOREIGN KEY (`qa_category_id`) REFERENCES `qa_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_service_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_service_category` FOREIGN KEY (`service_category_id`) REFERENCES `service_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `service_documents_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* Table structure for table `aircraft_competency` */
DROP TABLE IF EXISTS `aircraft_competency`;

CREATE TABLE `aircraft_competency` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `svc_no` varchar(100) NOT NULL,
  `rank` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `aircraft_type` varchar(100) NOT NULL,
  `last_level_of_competency` varchar(100) NOT NULL,
  `renewal_date` date NOT NULL,
  `currency` varchar(100) NOT NULL,
  `squadron` varchar(100) NOT NULL,
  `uploaded_by` int DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT '1',
  `branch_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_ac_uploaded_by` (`uploaded_by`),
  KEY `fk_ac_branch` (`branch_id`),
  CONSTRAINT `fk_ac_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ac_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* Table structure for table `latitude_extension` */
DROP TABLE IF EXISTS `latitude_extension`;

CREATE TABLE `latitude_extension` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `latitude_description` varchar(255) NOT NULL,
  `related_aircraft` varchar(100) NOT NULL,
  `latitude_period` varchar(100) NOT NULL,
  `uploaded_by` int DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT '1',
  `branch_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_le_uploaded_by` (`uploaded_by`),
  KEY `fk_le_branch` (`branch_id`),
  CONSTRAINT `fk_le_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_le_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* Table structure for table `vehicle_emission_test` */
DROP TABLE IF EXISTS `vehicle_emission_test`;

CREATE TABLE `vehicle_emission_test` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `vehicle_no` varchar(100) NOT NULL,
  `test_performed_date` date NOT NULL,
  `state` enum('Pass','Fail') NOT NULL,
  `remarks` text,
  `uploaded_by` int DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT '1',
  `branch_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_vet_uploaded_by` (`uploaded_by`),
  KEY `fk_vet_branch` (`branch_id`),
  CONSTRAINT `fk_vet_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_vet_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* Table structure for table `publication_categories` */
DROP TABLE IF EXISTS `publication_categories`;

CREATE TABLE `publication_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

/* Data for the table `publication_categories` */
INSERT INTO `publication_categories` (`id`, `name`) VALUES 
(1, 'Online Subscription'),
(2, 'Airworthiness Directives & Bulletins'),
(3, 'QAI Safety Newsletters'),
(4, 'Maintenance Programme'),
(5, 'Technical Library');

/* Table structure for table `online_subscription` */
DROP TABLE IF EXISTS `online_subscription`;

CREATE TABLE `online_subscription` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `related_aircraft` varchar(100) NOT NULL,
  `subscription_period` varchar(100) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_by` int DEFAULT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT '1',
  `branch_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_os_uploaded_by` (`uploaded_by`),
  KEY `fk_os_branch` (`branch_id`),
  CONSTRAINT `fk_os_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_os_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* Table structure for table `ad_bulletins` */
DROP TABLE IF EXISTS `ad_bulletins`;

CREATE TABLE `ad_bulletins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `aircraft_type` varchar(100) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_by` int DEFAULT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT '1',
  `branch_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_ad_uploaded_by` (`uploaded_by`),
  KEY `fk_ad_branch` (`branch_id`),
  CONSTRAINT `fk_ad_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ad_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* Table structure for table `maintenance_categories` */
DROP TABLE IF EXISTS `maintenance_categories`;

CREATE TABLE `maintenance_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

/* Data for the table `maintenance_categories` */
INSERT INTO `maintenance_categories` (`id`, `name`) VALUES 
(1, 'Servicing Schedule'),
(2, 'Worksheets');

/* Table structure for table `publication_documents` */
DROP TABLE IF EXISTS `publication_documents`;

CREATE TABLE `publication_documents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `publication_category_id` int DEFAULT NULL,
  `maintenance_category_id` int DEFAULT NULL,
  `uploaded_by` int DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT '1',
  `branch_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uploaded_by` (`uploaded_by`),
  KEY `fk_publication_category` (`publication_category_id`),
  KEY `fk_maintenance_category` (`maintenance_category_id`),
  KEY `fk_publication_branch` (`branch_id`),
  CONSTRAINT `fk_maintenance_category` FOREIGN KEY (`maintenance_category_id`) REFERENCES `maintenance_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_publication_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_publication_category` FOREIGN KEY (`publication_category_id`) REFERENCES `publication_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `publication_documents_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* Table structure for table `training_categories` */
DROP TABLE IF EXISTS `training_categories`;

CREATE TABLE `training_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

/* Data for the table `training_categories` */
INSERT INTO `training_categories` (`id`, `name`) VALUES 
(1, 'Approved Training Syllabus'),
(2, 'Continues Professional Development'),
(3, 'Productivity Improvement Professional Development'),
(4, 'Outside Training');

/* Table structure for table `training_documents` */
DROP TABLE IF EXISTS `training_documents`;

CREATE TABLE `training_documents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `training_category_id` int DEFAULT NULL,
  `uploaded_by` int DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT '1',
  `branch_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uploaded_by` (`uploaded_by`),
  KEY `fk_training_category` (`training_category_id`),
  KEY `fk_training_branch` (`branch_id`),
  CONSTRAINT `fk_training_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_training_category` FOREIGN KEY (`training_category_id`) REFERENCES `training_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `training_documents_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* Table structure for table `productivity_categories` */
DROP TABLE IF EXISTS `productivity_categories`;

CREATE TABLE `productivity_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

/* Data for the table `productivity_categories` */
INSERT INTO `productivity_categories` (`id`, `name`) VALUES 
(1, 'Occupational Health & Safety'),
(2, 'Environment'),
(3, 'Quality Control Circle'),
(4, 'Awards');

/* Table structure for table `osh_categories` */
DROP TABLE IF EXISTS `osh_categories`;

CREATE TABLE `osh_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

/* Data for the table `osh_categories` */
INSERT INTO `osh_categories` (`id`, `name`) VALUES 
(1, 'Audit Checklists'),
(2, 'Audit Reports'),
(3, 'Audit Plans'),
(4, 'OSH Manual');

/* Table structure for table `environment_categories` */
DROP TABLE IF EXISTS `environment_categories`;

CREATE TABLE `environment_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

/* Data for the table `environment_categories` */
INSERT INTO `environment_categories` (`id`, `name`) VALUES 
(1, 'Audit Checklists'),
(2, 'Audit Reports'),
(3, 'Audit Plans');

/* Table structure for table `productivity_documents` */
DROP TABLE IF EXISTS `productivity_documents`;

CREATE TABLE `productivity_documents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `productivity_category_id` int DEFAULT NULL,
  `osh_category_id` int DEFAULT NULL,
  `environment_category_id` int DEFAULT NULL,
  `uploaded_by` int DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT '1',
  `branch_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uploaded_by` (`uploaded_by`),
  KEY `fk_productivity_category` (`productivity_category_id`),
  KEY `fk_osh_category` (`osh_category_id`),
  KEY `fk_environment_category` (`environment_category_id`),
  KEY `fk_productivity_branch` (`branch_id`),
  CONSTRAINT `fk_environment_category` FOREIGN KEY (`environment_category_id`) REFERENCES `environment_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_osh_category` FOREIGN KEY (`osh_category_id`) REFERENCES `osh_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_productivity_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_productivity_category` FOREIGN KEY (`productivity_category_id`) REFERENCES `productivity_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `productivity_documents_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;