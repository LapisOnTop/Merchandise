-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 18, 2026 at 04:03 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `system`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `session_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'FK → cashier_sessions.id (nullable for system events)',
  `cashier_name` varchar(60) DEFAULT NULL,
  `action` varchar(100) NOT NULL COMMENT 'e.g. CASHIER_LOGIN, SALE_COMPLETED',
  `description` text DEFAULT NULL COMMENT 'Human-readable detail about the action',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IPv4 or IPv6 of the client',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='POS audit trail — all cashier and sale events';

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `session_id`, `cashier_name`, `action`, `description`, `ip_address`, `created_at`) VALUES
(1, 1, 'CASHIER01', 'CASHIER_LOGIN', 'Session started with ₱2,000 starting cash', '::1', '2026-03-18 01:09:50');

-- --------------------------------------------------------

--
-- Table structure for table `cashier_sessions`
--

CREATE TABLE `cashier_sessions` (
  `id` int(10) UNSIGNED NOT NULL,
  `cashier_name` varchar(60) NOT NULL COMMENT 'Cashier code entered at login e.g. CASHIER01',
  `login_time` datetime NOT NULL DEFAULT current_timestamp(),
  `logout_time` datetime DEFAULT NULL COMMENT 'NULL means session is still active',
  `starting_cash` decimal(12,2) NOT NULL DEFAULT 2000.00 COMMENT 'Fixed ₱2,000 cash drawer at start of shift',
  `total_sales` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Running total updated on every sale',
  `ending_cash` decimal(12,2) DEFAULT NULL COMMENT 'starting_cash + total_sales, set on logout',
  `status` enum('active','closed') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='POS cashier shift sessions — one row per login';

--
-- Dumping data for table `cashier_sessions`
--

INSERT INTO `cashier_sessions` (`id`, `cashier_name`, `login_time`, `logout_time`, `starting_cash`, `total_sales`, `ending_cash`, `status`) VALUES
(1, 'CASHIER01', '2026-03-18 01:09:50', NULL, 2000.00, 0.00, NULL, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `competencies`
--

CREATE TABLE `competencies` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `required_level` tinyint(4) DEFAULT 3,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `competency_assessments`
--

CREATE TABLE `competency_assessments` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `competency_id` int(11) NOT NULL,
  `self_rating` tinyint(4) DEFAULT NULL,
  `manager_rating` tinyint(4) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `assessed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contracts`
--

CREATE TABLE `contracts` (
  `id` int(11) NOT NULL,
  `contract_id` varchar(50) NOT NULL,
  `parties` varchar(255) NOT NULL,
  `expiry_date` date NOT NULL,
  `status` enum('active','archived') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dispatch`
--

CREATE TABLE `dispatch` (
  `id` int(11) NOT NULL,
  `request_id` varchar(50) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `needed_at` datetime DEFAULT NULL,
  `destination` text DEFAULT NULL,
  `pickup_location` text DEFAULT NULL,
  `items_transport` text DEFAULT NULL,
  `goods_type` varchar(100) DEFAULT NULL,
  `special_instructions` text DEFAULT NULL,
  `estimated_weight` varchar(50) DEFAULT NULL,
  `box_count` int(11) DEFAULT NULL,
  `vehicle_size` varchar(50) DEFAULT NULL,
  `special_features` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `add_vehicle` varchar(255) DEFAULT NULL,
  `add_driver` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `uploaded_by` varchar(100) NOT NULL DEFAULT 'Admin',
  `status` enum('pending','approved','archived') NOT NULL DEFAULT 'pending',
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `title`, `category`, `file_path`, `file_name`, `uploaded_by`, `status`, `uploaded_at`) VALUES
(2, 'Bold ni jake', 'Report', 'uploads/documents/1773774901_PhilID-specimen-Front_highres1-1024x576.png', 'PhilID-specimen-Front_highres1-1024x576.png', 'Admin', 'approved', '2026-03-17 19:15:01');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `home_address` text NOT NULL,
  `date_of_birth` date NOT NULL,
  `gender` enum('male','female','other','prefer_not_to_say') NOT NULL,
  `civil_status` enum('single','married','divorced','widowed','separated') NOT NULL,
  `nationality` varchar(50) NOT NULL,
  `resume_path` varchar(500) DEFAULT NULL,
  `valid_id_path` varchar(500) DEFAULT NULL,
  `application_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','reviewed','contacted','accepted','rejected') NOT NULL DEFAULT 'pending',
  `sss_number` varchar(20) DEFAULT NULL,
  `philhealth_number` varchar(20) DEFAULT NULL,
  `pagibig_number` varchar(20) DEFAULT NULL,
  `bir_tax_form` varchar(500) DEFAULT NULL,
  `nbi_clearance_path` varchar(500) DEFAULT NULL,
  `psa_birth_certificate_path` varchar(500) DEFAULT NULL,
  `onboarding_status` enum('pending','in_progress','completed') DEFAULT 'pending',
  `department` varchar(100) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `work_status` enum('available','unavailable','assigned','delivering') NOT NULL DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `job_id`, `full_name`, `email`, `phone`, `home_address`, `date_of_birth`, `gender`, `civil_status`, `nationality`, `resume_path`, `valid_id_path`, `application_date`, `status`, `sss_number`, `philhealth_number`, `pagibig_number`, `bir_tax_form`, `nbi_clearance_path`, `psa_birth_certificate_path`, `onboarding_status`, `department`, `position`, `work_status`) VALUES
(2, 9, 'Angelito Bruzon', 'angelitobruzon222@gmail.com', '09150051473', 'asddddddddddddddddddddddd', '1991-05-15', 'male', 'single', 'Filipino', 'uploads/applications/1773521910_resume_GLINDRO.pdf', 'uploads/applications/1773521910_id_Introduction-2.jpg', '2026-03-14 20:58:30', 'accepted', '123', '123123', '123123', 'uploads/ap', 'uploads/applications/nbi_7_1773595802.png', 'uploads/applications/psa_7_1773595802.png', 'completed', 'Human Resources', 'HR Assistant', 'available'),
(3, 14, 'Jake Gosling', 'angelitobruzon222@gmail.com', '0917 482 6395', 'Test', '2026-03-05', 'male', 'single', 'Filipino', 'uploads/applications/1773735978_resume_CPE-ELEC-2-FINALS-PROJECT.pdf', 'uploads/applications/1773735978_id_PhilID-specimen-Front_highres1-1024x576.png', '2026-03-17 08:26:18', 'accepted', '123', '123', '123', 'uploads/ap', 'uploads/applications/nbi_15_1773736009.png', 'uploads/applications/psa_15_1773736009.png', 'completed', 'log2', NULL, 'available'),
(4, 14, 'Frank Talipan', 'angelitobruzon222@gmail.com', '0917 482 6395', 'test', '2026-03-05', 'male', 'single', 'Filipino', 'uploads/applications/1773736135_resume_CPE-ELEC-2-FINALS-PROJECT.pdf', 'uploads/applications/1773736135_id_PhilID-specimen-Front_highres1-1024x576.png', '2026-03-17 08:28:55', 'accepted', '123', '123', '123', 'uploads/ap', 'uploads/applications/nbi_16_1773736190.png', 'uploads/applications/psa_16_1773736190.png', 'completed', 'log2', 'Driver', 'available'),
(5, 9, 'Eunice', 'angelitobruzon222@gmail.com', '09669827634', 'Test', '2026-03-16', 'female', 'single', 'Filipino', 'uploads/applications/1773759368_resume_CPE-ELEC-2-FINALS-PROJECT.pdf', 'uploads/applications/1773759368_id_PhilID-specimen-Front_highres1-1024x576.png', '2026-03-17 14:56:08', 'accepted', '12', '123', '123', 'uploads/ap', 'uploads/applications/nbi_18_1773759506.png', 'uploads/applications/psa_18_1773759506.png', 'completed', 'Human Resources', 'HR Assistant', 'available'),
(6, 14, 'Taduran', 'kennethtaduran00@gmail.com', '0917 482 6395', 'test', '2026-03-16', 'male', 'single', 'Filipino', 'uploads/applications/1773759331_resume_CPE-ELEC-2-FINALS-PROJECT.pdf', 'uploads/applications/1773759331_id_PhilID-specimen-Front_highres1-1024x576.png', '2026-03-17 14:55:31', 'accepted', '123', '123', '123', 'uploads/ap', 'uploads/applications/nbi_17_1773759523.png', 'uploads/applications/psa_17_1773759523.png', 'completed', 'log2', 'Driver', 'available'),
(7, 14, 'Angelito Bruzon', 'angelitobruzon222@gmail.com', '0917 482 6395', 'Phase 3 Block 7 Lupang Pangako Payatas Quezon City', '2005-07-06', 'male', 'single', 'Filipino', 'uploads/applications/1773795893_resume_CPE-ELEC-2-FINALS-PROJECT.pdf', 'uploads/applications/1773795893_id_PhilID-specimen-Front_highres1-1024x576.png', '2026-03-18 01:04:53', 'accepted', '123', '123', '123', 'uploads/ap', 'uploads/applications/nbi_19_1773795916.png', 'uploads/applications/psa_19_1773795916.png', 'completed', 'log2', 'Driver', '');

-- --------------------------------------------------------

--
-- Table structure for table `employee_trainings`
--

CREATE TABLE `employee_trainings` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `training_name` varchar(255) NOT NULL,
  `training_type` enum('mandatory','optional','certification') NOT NULL DEFAULT 'optional',
  `status` enum('assigned','in_progress','completed','cancelled') NOT NULL DEFAULT 'assigned',
  `assigned_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `completion_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_trainings`
--

INSERT INTO `employee_trainings` (`id`, `employee_id`, `training_name`, `training_type`, `status`, `assigned_date`, `completion_date`, `notes`) VALUES
(1, 2, 'burat training', 'mandatory', 'in_progress', '2026-03-16 10:50:10', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `facility_reservations`
--

CREATE TABLE `facility_reservations` (
  `id` int(11) NOT NULL,
  `room` varchar(100) NOT NULL,
  `type` varchar(100) NOT NULL,
  `date` date NOT NULL,
  `duration` varchar(50) NOT NULL,
  `requester` varchar(100) NOT NULL,
  `rank` varchar(100) NOT NULL,
  `status` enum('pending','approved') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inspection_reports`
--

CREATE TABLE `inspection_reports` (
  `id` int(11) NOT NULL,
  `po_id` int(11) NOT NULL,
  `po_number` varchar(50) NOT NULL,
  `supplier_name` varchar(255) DEFAULT NULL,
  `inspection_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_items` int(11) DEFAULT 0,
  `total_received` int(11) DEFAULT 0,
  `total_rejected` int(11) DEFAULT 0,
  `overall_status` enum('Passed','Failed','Partial') DEFAULT 'Passed',
  `report_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`report_data`)),
  `created_by` varchar(100) DEFAULT 'Admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inspection_reports`
--

INSERT INTO `inspection_reports` (`id`, `po_id`, `po_number`, `supplier_name`, `inspection_date`, `total_items`, `total_received`, `total_rejected`, `overall_status`, `report_data`, `created_by`, `created_at`) VALUES
(1, 1, 'PO-20260317-0001', 'Hatdog.corp', '2026-03-17 06:39:40', 1, 30, 0, 'Passed', '{\"po_id\":1,\"po_number\":\"PO-20260317-0001\",\"supplier_name\":\"Hatdog.corp\",\"inspection_date\":\"2026-03-17 07:39:40\",\"items\":[{\"po_item_id\":\"1\",\"product_id\":\"1\",\"batch_number\":\"Batch-001\",\"expiry_date\":\"2026-04-11\",\"received_qty\":30,\"rejected_qty\":0,\"inspection_status\":\"Passed\",\"inspection_notes\":\"Goods\"}],\"summary\":{\"total_items\":1,\"total_received\":30,\"total_rejected\":0,\"overall_status\":\"Passed\"}}', 'Admin', '2026-03-17 06:39:40'),
(2, 2, 'PO-20260317-0002', 'Hatdog.corp', '2026-03-17 06:52:22', 2, 82, 0, 'Passed', '{\"po_id\":2,\"po_number\":\"PO-20260317-0002\",\"supplier_name\":\"Hatdog.corp\",\"inspection_date\":\"2026-03-17 07:52:22\",\"items\":[{\"po_item_id\":\"2\",\"product_id\":\"2\",\"batch_number\":\"BATCH - 32020\",\"expiry_date\":\"2026-04-11\",\"received_qty\":50,\"rejected_qty\":0,\"inspection_status\":\"Passed\",\"inspection_notes\":\"Goods\"},{\"po_item_id\":\"3\",\"product_id\":\"1\",\"batch_number\":\"BATCH - 32020\",\"expiry_date\":\"2026-04-11\",\"received_qty\":32,\"rejected_qty\":0,\"inspection_status\":\"Passed\",\"inspection_notes\":\"Goods\"}],\"summary\":{\"total_items\":2,\"total_received\":82,\"total_rejected\":0,\"overall_status\":\"Passed\"}}', 'Admin', '2026-03-17 06:52:22'),
(3, 3, 'PO-20260317-0003', 'Tiktok shop', '2026-03-17 08:09:07', 2, 50, 50, 'Partial', '{\"po_id\":3,\"po_number\":\"PO-20260317-0003\",\"supplier_name\":\"Tiktok shop\",\"inspection_date\":\"2026-03-17 09:09:07\",\"items\":[{\"po_item_id\":\"4\",\"product_id\":\"2\",\"batch_number\":\"005\",\"expiry_date\":\"2026-03-31\",\"received_qty\":50,\"rejected_qty\":0,\"inspection_status\":\"Passed\",\"inspection_notes\":\"Very Good\"},{\"po_item_id\":\"5\",\"product_id\":\"1\",\"batch_number\":\"006\",\"expiry_date\":\"2026-03-31\",\"received_qty\":0,\"rejected_qty\":50,\"inspection_status\":\"Failed\",\"inspection_notes\":\"Bad\"}],\"summary\":{\"total_items\":2,\"total_received\":50,\"total_rejected\":50,\"overall_status\":\"Partial\"}}', 'Admin', '2026-03-17 08:09:07');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `batch_number` varchar(50) DEFAULT NULL,
  `inventory_quantity` int(11) DEFAULT 0,
  `store_quantity` int(11) DEFAULT 0,
  `expiry_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `product_id`, `batch_number`, `inventory_quantity`, `store_quantity`, `expiry_date`, `created_at`, `updated_at`) VALUES
(1, 1, 'Batch-2025', 30, 0, '2026-04-11', '2026-03-17 06:35:58', '2026-03-17 06:35:58'),
(2, 1, 'Batch-001', 30, 0, '2026-04-11', '2026-03-17 06:39:40', '2026-03-17 06:39:40'),
(3, 2, 'BATCH - 32020', 30, 20, '2026-04-11', '2026-03-17 06:52:22', '2026-03-17 08:02:37'),
(5, 2, '005', 50, 0, '2026-03-31', '2026-03-17 08:09:07', '2026-03-17 08:09:07'),
(6, 1, '006', 0, 0, '2026-03-31', '2026-03-17 08:09:07', '2026-03-17 08:09:07');

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `threshold` int(11) DEFAULT 0,
  `expiry_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `name`, `category`, `stock`, `threshold`, `expiry_date`) VALUES
(1, 'Kahoy', 'Supply', 15, 10, '2026-03-12');

-- --------------------------------------------------------

--
-- Table structure for table `job_applications`
--

CREATE TABLE `job_applications` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `home_address` text NOT NULL,
  `date_of_birth` date NOT NULL,
  `gender` enum('male','female','other','prefer_not_to_say') NOT NULL,
  `civil_status` enum('single','married','divorced','widowed','separated') NOT NULL,
  `nationality` varchar(50) NOT NULL,
  `resume_path` varchar(500) DEFAULT NULL,
  `valid_id_path` varchar(500) DEFAULT NULL,
  `application_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','reviewed','contacted','accepted','rejected') NOT NULL DEFAULT 'pending',
  `sss_number` varchar(20) DEFAULT NULL,
  `philhealth_number` varchar(20) DEFAULT NULL,
  `pagibig_number` varchar(20) DEFAULT NULL,
  `bir_tax_form` varchar(10) DEFAULT NULL,
  `nbi_clearance_path` varchar(500) DEFAULT NULL,
  `psa_birth_certificate_path` varchar(500) DEFAULT NULL,
  `onboarding_status` enum('pending','in_progress','completed') DEFAULT 'pending',
  `department` varchar(100) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_applications`
--

INSERT INTO `job_applications` (`id`, `job_id`, `full_name`, `email`, `phone`, `home_address`, `date_of_birth`, `gender`, `civil_status`, `nationality`, `resume_path`, `valid_id_path`, `application_date`, `status`, `sss_number`, `philhealth_number`, `pagibig_number`, `bir_tax_form`, `nbi_clearance_path`, `psa_birth_certificate_path`, `onboarding_status`, `department`, `position`) VALUES
(2, 9, 'Angelito Bruzon', 'bruzon@gmail.com', '09150051473', 'dawdaaaaaaaaaaaaaaaaasdwadasd', '2015-01-15', 'male', 'single', 'Filipino', 'uploads/applications/1773512396_resume_GLINDRO.pdf', 'uploads/applications/1773512396_id_Introduction-2.jpg', '2026-03-14 18:19:56', 'accepted', '123', '123', '123', 'uploads/ap', 'uploads/applications/nbi_2_1773597004.png', 'uploads/applications/psa_2_1773597010.png', 'completed', 'Human Resources', 'HR Assistant'),
(15, 14, 'Jake Gosling', 'angelitobruzon222@gmail.com', '0917 482 6395', 'Test', '2026-03-05', 'male', 'single', 'Filipino', 'uploads/applications/1773735978_resume_CPE-ELEC-2-FINALS-PROJECT.pdf', 'uploads/applications/1773735978_id_PhilID-specimen-Front_highres1-1024x576.png', '2026-03-17 08:26:18', 'accepted', '123', '123', '123', 'uploads/ap', 'uploads/applications/nbi_15_1773736009.png', 'uploads/applications/psa_15_1773736009.png', 'completed', 'log2', 'Driver'),
(16, 14, 'Frank Talipan', 'angelitobruzon222@gmail.com', '0917 482 6395', 'test', '2026-03-05', 'male', 'single', 'Filipino', 'uploads/applications/1773736135_resume_CPE-ELEC-2-FINALS-PROJECT.pdf', 'uploads/applications/1773736135_id_PhilID-specimen-Front_highres1-1024x576.png', '2026-03-17 08:28:55', 'accepted', '123', '123', '123', 'uploads/ap', 'uploads/applications/nbi_16_1773736190.png', 'uploads/applications/psa_16_1773736190.png', 'completed', 'log2', 'Driver'),
(17, 14, 'Taduran', 'kennethtaduran00@gmail.com', '0917 482 6395', 'test', '2026-03-16', 'male', 'single', 'Filipino', 'uploads/applications/1773759331_resume_CPE-ELEC-2-FINALS-PROJECT.pdf', 'uploads/applications/1773759331_id_PhilID-specimen-Front_highres1-1024x576.png', '2026-03-17 14:55:31', 'accepted', '123', '123', '123', 'uploads/ap', 'uploads/applications/nbi_17_1773759523.png', 'uploads/applications/psa_17_1773759523.png', 'completed', 'log2', 'Driver'),
(18, 9, 'Eunice', 'angelitobruzon222@gmail.com', '09669827634', 'Test', '2026-03-16', 'female', 'single', 'Filipino', 'uploads/applications/1773759368_resume_CPE-ELEC-2-FINALS-PROJECT.pdf', 'uploads/applications/1773759368_id_PhilID-specimen-Front_highres1-1024x576.png', '2026-03-17 14:56:08', 'accepted', '12', '123', '123', 'uploads/ap', 'uploads/applications/nbi_18_1773759506.png', 'uploads/applications/psa_18_1773759506.png', 'completed', 'Human Resources', 'HR Assistant'),
(19, 14, 'Angelito Bruzon', 'angelitobruzon222@gmail.com', '0917 482 6395', 'Phase 3 Block 7 Lupang Pangako Payatas Quezon City', '2005-07-06', 'male', 'single', 'Filipino', 'uploads/applications/1773795893_resume_CPE-ELEC-2-FINALS-PROJECT.pdf', 'uploads/applications/1773795893_id_PhilID-specimen-Front_highres1-1024x576.png', '2026-03-18 01:04:53', 'accepted', '123', '123', '123', 'uploads/ap', 'uploads/applications/nbi_19_1773795916.png', 'uploads/applications/psa_19_1773795916.png', 'completed', 'log2', 'Driver');

-- --------------------------------------------------------

--
-- Table structure for table `job_postings`
--

CREATE TABLE `job_postings` (
  `id` int(11) NOT NULL,
  `department` varchar(50) NOT NULL,
  `branch` varchar(100) DEFAULT NULL,
  `position` varchar(100) NOT NULL,
  `num_applicants` int(11) NOT NULL,
  `requirements` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_postings`
--

INSERT INTO `job_postings` (`id`, `department`, `branch`, `position`, `num_applicants`, `requirements`, `created_at`) VALUES
(9, 'Human Resources', 'Manila', 'HR Assistant', 12, 'Bachelor degree in Psychology or HRM', '2026-03-14 18:02:35'),
(10, 'Information Technology', 'Quezon City', 'Web Developer', 8, 'Knowledge in PHP, MySQL, HTML, CSS, JavaScript', '2026-03-14 18:02:35'),
(11, 'Finance', 'Makati', 'Accountant', 5, 'CPA preferred, experience in financial reporting', '2026-03-14 18:02:35'),
(12, 'Marketing', 'Taguig', 'Marketing Specialist', 10, 'Experience in digital marketing and social media management', '2026-03-14 18:02:35'),
(13, 'Customer Service', 'Pasig', 'Customer Service Representative', 15, 'Good communication skills and customer handling experience', '2026-03-14 18:02:35'),
(14, 'log2', 'jordan', 'Driver', 1, '2 Years of Driving Experience', '2026-03-17 08:25:37');

-- --------------------------------------------------------

--
-- Table structure for table `learning_modules`
--

CREATE TABLE `learning_modules` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `date_uploaded` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `leave_type` varchar(100) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `matters`
--

CREATE TABLE `matters` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `type` varchar(100) NOT NULL,
  `status` enum('open','in_progress','resolved') NOT NULL DEFAULT 'open',
  `assigned_to` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pricing`
--

CREATE TABLE `pricing` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `cost_price` decimal(12,2) DEFAULT 0.00,
  `selling_price` decimal(12,2) DEFAULT 0.00,
  `markup_percentage` decimal(5,2) DEFAULT 0.00,
  `margin_percentage` decimal(5,2) DEFAULT 0.00,
  `min_price` decimal(12,2) DEFAULT NULL,
  `max_price` decimal(12,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pricing_history`
--

CREATE TABLE `pricing_history` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `cost_price` decimal(12,2) DEFAULT NULL,
  `selling_price` decimal(12,2) DEFAULT NULL,
  `markup_percentage` decimal(5,2) DEFAULT NULL,
  `margin_percentage` decimal(5,2) DEFAULT NULL,
  `changed_by` varchar(100) DEFAULT NULL,
  `change_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `sku` varchar(50) NOT NULL,
  `barcode` varchar(100) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `cost_price` decimal(10,2) NOT NULL,
  `store_price` decimal(10,2) NOT NULL,
  `reorder_level` int(11) DEFAULT 0,
  `stock_quantity` int(11) DEFAULT 0,
  `status` enum('active','inactive','low_stock') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `sku`, `barcode`, `product_name`, `description`, `category_id`, `cost_price`, `store_price`, `reorder_level`, `stock_quantity`, `status`, `created_at`, `updated_at`) VALUES
(1, '100000', 'asda2424', 'coke', 'Soda Pop Girl', 1, 12.00, 20.00, 50, 0, 'active', '2026-03-17 05:07:59', '2026-03-17 05:07:59'),
(2, '1231245', '21312353', 'Royal', 'Oranji', 1, 12.00, 20.00, 50, 0, 'active', '2026-03-17 05:37:01', '2026-03-17 05:37:01');

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

CREATE TABLE `product_categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_categories`
--

INSERT INTO `product_categories` (`id`, `category_name`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Beverage', 'Soda pop\r\n', 'active', '2026-03-17 05:07:19', '2026-03-17 05:07:19');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `id` int(11) NOT NULL,
  `po_number` varchar(64) NOT NULL,
  `supplier_name` varchar(255) DEFAULT NULL,
  `expected_delivery_date` date DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `total_cost` decimal(14,2) DEFAULT 0.00,
  `total_profit` decimal(14,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_orders`
--

INSERT INTO `purchase_orders` (`id`, `po_number`, `supplier_name`, `expected_delivery_date`, `status`, `total_cost`, `total_profit`, `created_at`, `updated_at`) VALUES
(1, 'PO-20260317-0001', 'Hatdog.corp', '2026-03-24', 'Approved', 360.00, 240.00, '2026-03-17 05:48:46', '2026-03-17 05:49:03'),
(2, 'PO-20260317-0002', 'Hatdog.corp', '2026-03-25', '', 984.00, 656.00, '2026-03-17 06:06:49', '2026-03-17 06:52:22'),
(3, 'PO-20260317-0003', 'Tiktok shop', '2026-03-25', '', 1200.00, 800.00, '2026-03-17 08:06:37', '2026-03-17 08:09:07'),
(4, 'PO-20260317-0004', 'Tiktok shop', '2026-03-18', 'Approved', 240.00, 160.00, '2026-03-17 21:05:22', '2026-03-17 21:05:26');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_items`
--

CREATE TABLE `purchase_order_items` (
  `id` int(11) NOT NULL,
  `po_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_cost` decimal(12,2) DEFAULT 0.00,
  `unit_price` decimal(12,2) DEFAULT 0.00,
  `total_cost` decimal(14,2) DEFAULT 0.00,
  `total_price` decimal(14,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_order_items`
--

INSERT INTO `purchase_order_items` (`id`, `po_id`, `product_id`, `quantity`, `unit_cost`, `unit_price`, `total_cost`, `total_price`, `created_at`) VALUES
(1, 1, 1, 30, 12.00, 20.00, 360.00, 600.00, '2026-03-17 05:48:46'),
(2, 2, 2, 50, 12.00, 20.00, 600.00, 1000.00, '2026-03-17 06:06:49'),
(3, 2, 1, 32, 12.00, 20.00, 384.00, 640.00, '2026-03-17 06:06:49'),
(4, 3, 2, 50, 12.00, 20.00, 600.00, 1000.00, '2026-03-17 08:06:37'),
(5, 3, 1, 50, 12.00, 20.00, 600.00, 1000.00, '2026-03-17 08:06:37'),
(6, 4, 2, 20, 12.00, 20.00, 240.00, 400.00, '2026-03-17 21:05:22');

-- --------------------------------------------------------

--
-- Table structure for table `receiving_inspections`
--

CREATE TABLE `receiving_inspections` (
  `id` int(11) NOT NULL,
  `po_id` int(11) NOT NULL,
  `po_item_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `batch_number` varchar(100) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `received_qty` int(11) DEFAULT 0,
  `rejected_qty` int(11) DEFAULT 0,
  `inspection_status` enum('Passed','Failed') DEFAULT 'Passed',
  `inspection_notes` text DEFAULT NULL,
  `inspected_by` varchar(100) DEFAULT 'Admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `receiving_inspections`
--

INSERT INTO `receiving_inspections` (`id`, `po_id`, `po_item_id`, `product_id`, `batch_number`, `expiry_date`, `received_qty`, `rejected_qty`, `inspection_status`, `inspection_notes`, `inspected_by`, `created_at`) VALUES
(1, 1, 1, 1, 'Batch-2025', '2026-04-11', 30, 0, 'Passed', 'Good', 'Admin', '2026-03-17 06:35:58'),
(2, 1, 1, 1, 'Batch-001', '2026-04-11', 30, 0, 'Passed', 'Goods', 'Admin', '2026-03-17 06:39:40'),
(3, 2, 2, 2, 'BATCH - 32020', '2026-04-11', 50, 0, 'Passed', 'Goods', 'Admin', '2026-03-17 06:52:22'),
(4, 2, 3, 1, 'BATCH - 32020', '2026-04-11', 32, 0, 'Passed', 'Goods', 'Admin', '2026-03-17 06:52:22'),
(5, 3, 4, 2, '005', '2026-03-31', 50, 0, 'Passed', 'Very Good', 'Admin', '2026-03-17 08:09:07'),
(6, 3, 5, 1, '006', '2026-03-31', 0, 50, 'Failed', 'Bad', 'Admin', '2026-03-17 08:09:07');

-- --------------------------------------------------------

--
-- Table structure for table `request_reservation`
--

CREATE TABLE `request_reservation` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `threshold` int(11) NOT NULL DEFAULT 0,
  `expiry_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reservation`
--

CREATE TABLE `reservation` (
  `id` int(11) NOT NULL,
  `request_id` varchar(50) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `requester` varchar(255) DEFAULT NULL,
  `purpose` varchar(255) DEFAULT NULL,
  `needed_at` datetime DEFAULT NULL,
  `destination` varchar(255) DEFAULT NULL,
  `pickup_location` varchar(255) DEFAULT NULL,
  `items_transport` text DEFAULT NULL,
  `goods_type` varchar(100) DEFAULT NULL,
  `special_instructions` text DEFAULT NULL,
  `estimated_weight` decimal(8,2) DEFAULT NULL,
  `box_count` int(11) DEFAULT NULL,
  `vehicle_size` varchar(100) DEFAULT NULL,
  `special_features` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `requested_by_id` int(11) DEFAULT NULL,
  `requested_by` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `add_vehicle` int(11) DEFAULT NULL,
  `add_driver` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(10) UNSIGNED NOT NULL,
  `transaction_ref` varchar(30) NOT NULL COMMENT 'e.g. TXN-20260318-A1B2C3D4',
  `session_id` int(10) UNSIGNED NOT NULL COMMENT 'FK → cashier_sessions.id',
  `cashier_name` varchar(60) NOT NULL COMMENT 'Snapshot of cashier name at time of sale',
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Reserved for VAT; currently 0',
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `cash_received` decimal(12,2) NOT NULL DEFAULT 0.00,
  `change_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('completed','voided') NOT NULL DEFAULT 'completed',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='POS sales transactions — one row per checkout';

-- --------------------------------------------------------

--
-- Table structure for table `sales_items`
--

CREATE TABLE `sales_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `sale_id` int(10) UNSIGNED NOT NULL COMMENT 'FK → sales.id',
  `product_id` int(10) UNSIGNED NOT NULL COMMENT 'FK → products.id (system database)',
  `product_name` varchar(200) NOT NULL COMMENT 'Snapshot of name at time of sale',
  `barcode` varchar(100) NOT NULL COMMENT 'Snapshot of barcode at time of sale',
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL COMMENT 'Snapshot of store_price at time of sale',
  `line_total` decimal(12,2) NOT NULL COMMENT 'unit_price × quantity',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='POS sale line items — one row per product per transaction';

-- --------------------------------------------------------

--
-- Table structure for table `stock_adjustments`
--

CREATE TABLE `stock_adjustments` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `batch_number` varchar(50) DEFAULT NULL,
  `adjustment_type` enum('transfer','adjustment') NOT NULL,
  `quantity` int(11) NOT NULL,
  `from_location` varchar(50) DEFAULT NULL,
  `to_location` varchar(50) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `adjustment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_adjustments`
--

INSERT INTO `stock_adjustments` (`id`, `product_id`, `batch_number`, `adjustment_type`, `quantity`, `from_location`, `to_location`, `reason`, `adjustment_date`, `created_by`) VALUES
(1, 1, 'BATCH - 32020', 'transfer', 10, 'inventory', 'store', 'asd', '2026-03-17 07:35:11', NULL),
(15, 2, 'BATCH - 32020', 'transfer', 20, 'inventory', 'store', 'test', '2026-03-17 07:58:54', NULL),
(16, 2, 'BATCH - 32020', 'transfer', 20, 'store', 'inventory', 'transfer', '2026-03-17 08:02:37', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `stock_movements`
--

CREATE TABLE `stock_movements` (
  `id` int(11) NOT NULL,
  `inventory_id` int(11) DEFAULT NULL,
  `movement_type` enum('in','out') DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `performed_by` int(11) DEFAULT NULL,
  `performed_by_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_transfers`
--

CREATE TABLE `stock_transfers` (
  `id` int(11) NOT NULL,
  `transfer_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `product_id` int(11) NOT NULL,
  `transfer_type` enum('warehouse_to_store','store_adjustment','inventory_adjustment') NOT NULL,
  `quantity` int(11) NOT NULL,
  `from_location` varchar(100) DEFAULT NULL,
  `to_location` varchar(100) DEFAULT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` varchar(100) DEFAULT 'Admin',
  `status` enum('Pending','Completed') DEFAULT 'Completed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `succession_plans`
--

CREATE TABLE `succession_plans` (
  `id` int(11) NOT NULL,
  `position` varchar(100) NOT NULL,
  `department` varchar(100) NOT NULL,
  `current_employee_id` int(11) DEFAULT NULL,
  `successor_employee_id` int(11) DEFAULT NULL,
  `readiness_level` enum('low','medium','high') NOT NULL DEFAULT 'low',
  `development_plan` text DEFAULT NULL,
  `target_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `supplier_name` varchar(255) NOT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `categories` varchar(255) DEFAULT NULL,
  `business_registration` varchar(255) DEFAULT NULL,
  `tax_id` varchar(255) DEFAULT NULL,
  `permits` text DEFAULT NULL,
  `certifications` text DEFAULT NULL,
  `contract_status` enum('active','inactive','pending') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `role`) VALUES
(1, 'admin@hr1.com', 'adminpass', 'hr1Admin'),
(2, 'user@hr1.com', 'userpass', 'hr1Main'),
(3, 'admin@administrative.com', 'adminpass', 'administrativeAdmin'),
(4, 'user@administrative.com', 'userpass', 'administrativeMain'),
(5, 'admin@core1.com', 'adminpass', 'core1Admin'),
(6, 'user@core1.com', 'userpass', 'core1Main'),
(7, 'admin@core2.com', 'adminpass', 'core2Admin'),
(8, 'user@core2.com', 'userpass', 'core2Main'),
(9, 'admin@finance.com', 'adminpass', 'financeAdmin'),
(10, 'user@finance.com', 'userpass', 'financeMain'),
(11, 'admin@hr2.com', 'adminpass', 'hr2Admin'),
(12, 'user@hr2.com', 'userpass', 'hr2Main'),
(13, 'admin@hr3.com', 'adminpass', 'hr3Admin'),
(14, 'user@hr3.com', 'userpass', 'hr3Main'),
(15, 'admin@hr4.com', 'adminpass', 'hr4Admin'),
(16, 'user@hr4.com', 'userpass', 'hr4Main'),
(17, 'admin@log1.com', 'adminpass', 'log1Admin'),
(18, 'user@log1.com', 'userpass', 'log1Main'),
(19, 'admin@log2.com', 'adminpass', 'log2Admin'),
(20, 'user@log2.com', 'userpass', 'log2Main');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL,
  `brand` varchar(100) NOT NULL,
  `vehicle_brand` varchar(100) DEFAULT NULL,
  `model` varchar(100) NOT NULL,
  `plate_number` varchar(50) NOT NULL,
  `status` enum('Available','Unavailable','In Delivery','Delivering','Assigned') NOT NULL DEFAULT 'Available',
  `conduction_sticker_number` varchar(100) DEFAULT NULL,
  `vehicle_type` varchar(100) DEFAULT NULL,
  `year_model` varchar(10) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `chassis_number` varchar(100) DEFAULT NULL,
  `engine_number` varchar(100) DEFAULT NULL,
  `fuel_type` varchar(50) DEFAULT NULL,
  `transmission_type` varchar(50) DEFAULT NULL,
  `seating_capacity` int(11) DEFAULT NULL,
  `cargo_capacity` decimal(10,2) DEFAULT NULL,
  `or_number` varchar(100) DEFAULT NULL,
  `cr_number` varchar(100) DEFAULT NULL,
  `registration_date` date DEFAULT NULL,
  `registration_expiry_date` date DEFAULT NULL,
  `insurance_provider` varchar(150) DEFAULT NULL,
  `insurance_policy_number` varchar(100) DEFAULT NULL,
  `insurance_expiry_date` date DEFAULT NULL,
  `roadworthiness_status` varchar(50) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `purchase_cost` decimal(12,2) DEFAULT NULL,
  `supplier_dealer_name` varchar(150) DEFAULT NULL,
  `or_cr_file` varchar(500) DEFAULT NULL,
  `insurance_certificate_file` varchar(500) DEFAULT NULL,
  `vehicle_photo_file` varchar(500) DEFAULT NULL,
  `maintenance_records_file` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`id`, `brand`, `vehicle_brand`, `model`, `plate_number`, `status`, `conduction_sticker_number`, `vehicle_type`, `year_model`, `color`, `chassis_number`, `engine_number`, `fuel_type`, `transmission_type`, `seating_capacity`, `cargo_capacity`, `or_number`, `cr_number`, `registration_date`, `registration_expiry_date`, `insurance_provider`, `insurance_policy_number`, `insurance_expiry_date`, `roadworthiness_status`, `purchase_date`, `purchase_cost`, `supplier_dealer_name`, `or_cr_file`, `insurance_certificate_file`, `vehicle_photo_file`, `maintenance_records_file`, `created_at`, `updated_at`) VALUES
(1, 'Toyota', 'Toyota', 'HiAce Commuter Deluxe', 'ABC-123', 'Unavailable', 'CS123456', 'Van', '2001', 'White', 'JH4KA8270MC012547', 'K24A-4589721', 'Diesel', 'Manual', 2, 1000.00, '8745632190', 'CR-09213456', '2026-03-01', '2026-03-01', 'ABC Insurance', 'POL123456', '2026-03-31', 'Roadworthy', '2026-03-01', 10000000.00, 'Toyota North EDSA Dealership', 'uploads/vehicles/1773786346_3d2978b198e0.png', 'uploads/vehicles/1773786346_9fc3ee1c96a2.png', 'uploads/vehicles/1773786346_b194f6e1c8bd.png', 'uploads/vehicles/1773786346_82c7594e63c9.png', '2026-03-17 22:25:46', '2026-03-18 00:45:39'),
(2, 'Toyota', 'Toyota', 'Toyota Hilux Champ / IMV-0', 'XYZ 1234', '', 'CS123456', 'Box_Truck', '2001', 'White', 'JH4KA8270MC012547', 'EN123456789', 'Diesel', 'Automatic', 2, 1000.00, '8745632190', 'CR-09213456', '2026-03-01', '2026-03-31', 'ABC Insurance', 'POL123456', '2026-03-31', 'Roadworthy', '2026-03-01', 10000000.00, 'Toyota North EDSA Dealership', 'uploads/vehicles/1773799173_fa714063535a.png', 'uploads/vehicles/1773799173_c7da4f928daf.png', 'uploads/vehicles/1773799173_31b1f16efca1.png', 'uploads/vehicles/1773799173_2cc27954944d.png', '2026-03-18 01:59:33', '2026-03-18 02:52:19');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_requests`
--

CREATE TABLE `vehicle_requests` (
  `id` int(11) NOT NULL,
  `request_id` varchar(50) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `requester` varchar(255) DEFAULT NULL,
  `purpose` varchar(255) DEFAULT NULL,
  `needed_at` datetime DEFAULT NULL,
  `destination` varchar(255) DEFAULT NULL,
  `pickup_location` varchar(255) DEFAULT NULL,
  `items_transport` text DEFAULT NULL,
  `goods_type` varchar(100) DEFAULT NULL,
  `special_instructions` text DEFAULT NULL,
  `estimated_weight` decimal(8,2) DEFAULT NULL,
  `box_count` int(11) DEFAULT NULL,
  `vehicle_size` varchar(100) DEFAULT NULL,
  `special_features` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `requested_by_id` int(11) DEFAULT NULL,
  `requested_by` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `add_vehicle` varchar(255) DEFAULT NULL,
  `add_driver` varchar(255) DEFAULT NULL,
  `dispatched` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicle_requests`
--

INSERT INTO `vehicle_requests` (`id`, `request_id`, `department`, `requester`, `purpose`, `needed_at`, `destination`, `pickup_location`, `items_transport`, `goods_type`, `special_instructions`, `estimated_weight`, `box_count`, `vehicle_size`, `special_features`, `notes`, `requested_by_id`, `requested_by`, `created_at`) VALUES
(4, 'REQ-0001', 'Core 1', NULL, 'Delivery', '2026-03-19 10:31:00', 'Jordan Branch', 'Warehouse', 'Coke Supply', 'General', 'Keep from Sunlight', 300.00, 6, 'Medium (Box Truck)', 'Closed Van', 'None so far', NULL, NULL, '2026-03-18 00:30:02');

-- --------------------------------------------------------

--
-- Table structure for table `visitor_audit_trail`
--

CREATE TABLE `visitor_audit_trail` (
  `id` int(11) NOT NULL,
  `visitor_id` int(11) NOT NULL,
  `visitor_name` varchar(100) NOT NULL,
  `action` varchar(100) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `visitor_audit_trail`
--

INSERT INTO `visitor_audit_trail` (`id`, `visitor_id`, `visitor_name`, `action`, `timestamp`) VALUES
(1, 1, 'Daniel Gemotra', 'Visitor Checked In', '2026-03-17 17:45:31'),
(2, 1, 'Daniel Gemotra', 'Visitor Left', '2026-03-17 17:47:06'),
(3, 2, 'Angelito Bruzon', 'Visitor Checked In', '2026-03-17 19:14:12');

-- --------------------------------------------------------

--
-- Table structure for table `visitor_log`
--

CREATE TABLE `visitor_log` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `contact` varchar(20) NOT NULL,
  `reason` varchar(100) NOT NULL,
  `time_in` timestamp NOT NULL DEFAULT current_timestamp(),
  `time_out` timestamp NULL DEFAULT NULL,
  `status` enum('Inside','Left') NOT NULL DEFAULT 'Inside'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `visitor_log`
--

INSERT INTO `visitor_log` (`id`, `name`, `contact`, `reason`, `time_in`, `time_out`, `status`) VALUES
(1, 'Daniel Gemotra', '0912345678', 'Restocking', '2026-03-17 17:45:31', '2026-03-17 17:47:06', 'Left'),
(2, 'Angelito Bruzon', '0946966698', 'Maintenance', '2026-03-17 19:14:12', NULL, 'Inside');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_cashier_name` (`cashier_name`),
  ADD KEY `idx_session_id` (`session_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `cashier_sessions`
--
ALTER TABLE `cashier_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cashier_name` (`cashier_name`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_login_time` (`login_time`);

--
-- Indexes for table `competencies`
--
ALTER TABLE `competencies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `competency_assessments`
--
ALTER TABLE `competency_assessments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contracts`
--
ALTER TABLE `contracts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dispatch`
--
ALTER TABLE `dispatch`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employee_trainings`
--
ALTER TABLE `employee_trainings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `facility_reservations`
--
ALTER TABLE `facility_reservations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inspection_reports`
--
ALTER TABLE `inspection_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `po_id` (`po_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product` (`product_id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `job_applications`
--
ALTER TABLE `job_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_job_id` (`job_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `job_postings`
--
ALTER TABLE `job_postings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `learning_modules`
--
ALTER TABLE `learning_modules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `matters`
--
ALTER TABLE `matters`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pricing`
--
ALTER TABLE `pricing`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_id` (`product_id`),
  ADD KEY `idx_product` (`product_id`);

--
-- Indexes for table `pricing_history`
--
ALTER TABLE `pricing_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `idx_category` (`category_id`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `po_number` (`po_number`);

--
-- Indexes for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `po_id` (`po_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `receiving_inspections`
--
ALTER TABLE `receiving_inspections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `po_id` (`po_id`),
  ADD KEY `po_item_id` (`po_item_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `reservation`
--
ALTER TABLE `reservation`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_ref` (`transaction_ref`),
  ADD UNIQUE KEY `uq_txn_ref` (`transaction_ref`),
  ADD KEY `idx_session_id` (`session_id`),
  ADD KEY `idx_cashier_name` (`cashier_name`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `sales_items`
--
ALTER TABLE `sales_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sale_id` (`sale_id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Indexes for table `stock_adjustments`
--
ALTER TABLE `stock_adjustments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product` (`product_id`);

--
-- Indexes for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stock_transfers`
--
ALTER TABLE `stock_transfers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_transfer_date` (`transfer_date`);

--
-- Indexes for table `succession_plans`
--
ALTER TABLE `succession_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `current_employee_id` (`current_employee_id`),
  ADD KEY `successor_employee_id` (`successor_employee_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vehicle_requests`
--
ALTER TABLE `vehicle_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `visitor_audit_trail`
--
ALTER TABLE `visitor_audit_trail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `visitor_id` (`visitor_id`);

--
-- Indexes for table `visitor_log`
--
ALTER TABLE `visitor_log`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cashier_sessions`
--
ALTER TABLE `cashier_sessions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `competencies`
--
ALTER TABLE `competencies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `competency_assessments`
--
ALTER TABLE `competency_assessments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contracts`
--
ALTER TABLE `contracts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `dispatch`
--
ALTER TABLE `dispatch`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `employee_trainings`
--
ALTER TABLE `employee_trainings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `facility_reservations`
--
ALTER TABLE `facility_reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `inspection_reports`
--
ALTER TABLE `inspection_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `job_applications`
--
ALTER TABLE `job_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `job_postings`
--
ALTER TABLE `job_postings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `learning_modules`
--
ALTER TABLE `learning_modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `matters`
--
ALTER TABLE `matters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pricing`
--
ALTER TABLE `pricing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pricing_history`
--
ALTER TABLE `pricing_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `product_categories`
--
ALTER TABLE `product_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `receiving_inspections`
--
ALTER TABLE `receiving_inspections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `reservation`
--
ALTER TABLE `reservation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_items`
--
ALTER TABLE `sales_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_adjustments`
--
ALTER TABLE `stock_adjustments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_transfers`
--
ALTER TABLE `stock_transfers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `succession_plans`
--
ALTER TABLE `succession_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `vehicle_requests`
--
ALTER TABLE `vehicle_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `visitor_audit_trail`
--
ALTER TABLE `visitor_audit_trail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `visitor_log`
--
ALTER TABLE `visitor_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `employee_trainings`
--
ALTER TABLE `employee_trainings`
  ADD CONSTRAINT `employee_trainings_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inspection_reports`
--
ALTER TABLE `inspection_reports`
  ADD CONSTRAINT `inspection_reports_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `job_applications`
--
ALTER TABLE `job_applications`
  ADD CONSTRAINT `job_applications_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `job_postings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pricing`
--
ALTER TABLE `pricing`
  ADD CONSTRAINT `pricing_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pricing_history`
--
ALTER TABLE `pricing_history`
  ADD CONSTRAINT `pricing_history_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD CONSTRAINT `purchase_order_items_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `receiving_inspections`
--
ALTER TABLE `receiving_inspections`
  ADD CONSTRAINT `receiving_inspections_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `receiving_inspections_ibfk_2` FOREIGN KEY (`po_item_id`) REFERENCES `purchase_order_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `receiving_inspections_ibfk_3` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_adjustments`
--
ALTER TABLE `stock_adjustments`
  ADD CONSTRAINT `stock_adjustments_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_transfers`
--
ALTER TABLE `stock_transfers`
  ADD CONSTRAINT `stock_transfers_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `succession_plans`
--
ALTER TABLE `succession_plans`
  ADD CONSTRAINT `succession_plans_ibfk_1` FOREIGN KEY (`current_employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `succession_plans_ibfk_2` FOREIGN KEY (`successor_employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `visitor_audit_trail`
--
ALTER TABLE `visitor_audit_trail`
  ADD CONSTRAINT `vat_ibfk_1` FOREIGN KEY (`visitor_id`) REFERENCES `visitor_log` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
