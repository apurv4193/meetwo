-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Nov 08, 2016 at 07:37 AM
-- Server version: 5.6.17
-- PHP Version: 5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `meetwo`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(320) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `name`, `email`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'admin@admin.com', '$2y$10$tNxqWWEbEfHksxeJDFkRgeZN09naxQTvRAhRbdEdrfnuxFuc2rlwK', NULL, '0000-00-00 00:00:00', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE IF NOT EXISTS `migrations` (
  `migration` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`migration`, `batch`) VALUES
('2016_06_23_045411_create_admin_users_table', 1),
('2016_11_03_090940_create_mt_u_users_table', 1),
('2016_11_03_091225_create_mt_udt_user_device_token_table', 1),
('2016_11_03_092256_create_mt_up_user_photos_table', 1),
('2016_11_03_093324_create_mt_q_questions_table', 1),
('2016_11_03_093816_create_mt_qo_question_options_table', 1),
('2016_11_03_094017_create_mt_qa_question_answers_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `mt_qa_question_answers`
--

CREATE TABLE IF NOT EXISTS `mt_qa_question_answers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `qa_user_id` bigint(20) unsigned NOT NULL,
  `qa_question_id` bigint(20) unsigned NOT NULL,
  `qa_option_id` int(11) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'timestamp',
  `deleted` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 - Active , 2 - Inactive, 3 - Deleted',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `mt_qa_question_answers`
--

INSERT INTO `mt_qa_question_answers` (`id`, `qa_user_id`, `qa_question_id`, `qa_option_id`, `created_at`, `updated_at`, `deleted`) VALUES
(1, 1, 1, 1, '2016-11-07 12:32:26', NULL, 1),
(2, 1, 2, 1, '2016-11-07 12:32:29', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `mt_qo_question_options`
--

CREATE TABLE IF NOT EXISTS `mt_qo_question_options` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `qo_question_id` bigint(20) NOT NULL,
  `qo_option` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'timestamp',
  `deleted` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 - Active , 2 - Inactive, 3 - Deleted',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `mt_qo_question_options`
--

INSERT INTO `mt_qo_question_options` (`id`, `qo_question_id`, `qo_option`, `created_at`, `updated_at`, `deleted`) VALUES
(1, 5, 'YES', '2016-11-07 12:05:40', NULL, 1),
(2, 2, 'NO', '2016-11-07 13:08:35', NULL, 1),
(3, 4, 'YES', '2016-11-08 04:42:43', NULL, 1),
(4, 5, 'NO', '2016-11-08 04:44:20', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `mt_q_questions`
--

CREATE TABLE IF NOT EXISTS `mt_q_questions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `q_question_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp',
  `updated_at` timestamp NOT NULL COMMENT 'timestamp',
  `deleted` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 - Active , 2 - Inactive, 3 - Deleted',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `mt_q_questions`
--

INSERT INTO `mt_q_questions` (`id`, `q_question_text`, `created_at`, `updated_at`, `deleted`) VALUES
(1, 'Do you want to travel?', '2016-11-07 12:05:40', '0000-00-00 00:00:00', 1),
(2, 'Do you want Singning ?', '2016-11-07 13:08:35', '0000-00-00 00:00:00', 1),
(4, 'Do you want Dancing ?', '2016-11-08 04:42:43', '0000-00-00 00:00:00', 1),
(5, 'Do you want child ?', '2016-11-08 04:44:20', '0000-00-00 00:00:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `mt_sq_skipped_question`
--

CREATE TABLE IF NOT EXISTS `mt_sq_skipped_question` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `sq_user_id` bigint(20) unsigned NOT NULL,
  `sq_question_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `mt_sq_skipped_question`
--

INSERT INTO `mt_sq_skipped_question` (`id`, `sq_user_id`, `sq_question_id`) VALUES
(1, 1, 3),
(2, 1, 4);

-- --------------------------------------------------------

--
-- Table structure for table `mt_udt_user_device_token`
--

CREATE TABLE IF NOT EXISTS `mt_udt_user_device_token` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `udt_user_id` bigint(20) unsigned NOT NULL,
  `udt_device_token` varchar(255) DEFAULT NULL,
  `udt_device_type` tinyint(1) NOT NULL COMMENT '1=>iPhone, 2=>Android',
  `udt_device_id` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp',
  `updated_at` timestamp NOT NULL COMMENT 'timestamp',
  `deleted` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 - Active , 2 - Inactive, 3 - Deleted',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `mt_udt_user_device_token`
--

INSERT INTO `mt_udt_user_device_token` (`id`, `udt_user_id`, `udt_device_token`, `udt_device_type`, `udt_device_id`, `created_at`, `updated_at`, `deleted`) VALUES
(1, 1, 'gtfdhhty', 1, '', '2016-11-07 11:42:22', '0000-00-00 00:00:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `mt_up_user_photos`
--

CREATE TABLE IF NOT EXISTS `mt_up_user_photos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `up_user_id` bigint(20) unsigned NOT NULL,
  `up_photo_name` varchar(255) NOT NULL,
  `up_is_profile_photo` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1=>Profile photo, 0=>Normal photo',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'timestamp',
  `deleted` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 - Active , 2 - Inactive, 3 - Deleted',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `mt_up_user_photos`
--

INSERT INTO `mt_up_user_photos` (`id`, `up_user_id`, `up_photo_name`, `up_is_profile_photo`, `created_at`, `updated_at`, `deleted`) VALUES
(1, 1, 'New', 1, '2016-11-07 09:30:54', NULL, 1),
(2, 7, '1', 1, '2016-11-07 10:55:49', NULL, 1),
(3, 8, 'newdemo.png', 1, '2016-11-07 11:01:55', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `mt_u_users`
--

CREATE TABLE IF NOT EXISTS `mt_u_users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `u_firstname` varchar(100) NOT NULL,
  `u_lastname` varchar(100) DEFAULT NULL,
  `u_email` varchar(100) DEFAULT NULL,
  `u_gender` tinyint(1) unsigned NOT NULL COMMENT '1 - Male, 2 - Female',
  `u_social_provider` varchar(100) NOT NULL COMMENT 'Sociad Media provider name',
  `u_fb_identifier` varchar(100) NOT NULL COMMENT 'Unique Identifier',
  `u_fb_accesstoken` varchar(255) DEFAULT NULL COMMENT 'access token',
  `u_phone` varchar(15) NOT NULL,
  `u_birthdate` date DEFAULT NULL,
  `u_description` text NOT NULL,
  `u_school` varchar(255) NOT NULL,
  `u_current_work` varchar(255) NOT NULL,
  `u_looking_for` tinyint(1) unsigned NOT NULL COMMENT '1->Male, 2->Female, 3->Both',
  `u_looking_distance` int(11) unsigned NOT NULL,
  `u_looking_age` int(11) unsigned NOT NULL,
  `u_compatibility_notification` tinyint(1) unsigned NOT NULL COMMENT '1->Receive, 0->Not receive',
  `u_newchat_notification` tinyint(1) unsigned NOT NULL COMMENT '1->Receive, 0->Not receive',
  `u_acceptance_notification` tinyint(1) unsigned NOT NULL COMMENT '1->Receive, 0->Not receive',
  `u_country` varchar(255) NOT NULL DEFAULT '0',
  `u_pincode` varchar(6) DEFAULT NULL,
  `u_location` varchar(255) DEFAULT NULL,
  `u_latitude` decimal(11,8) NOT NULL,
  `u_longitude` decimal(11,8) NOT NULL,
  `u_profile_active` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1-Yes, 0-No',
  `is_question_attempted` tinyint(1) NOT NULL DEFAULT '0',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'timestamp',
  `deleted` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 - Active , 2 - Inactive, 3 - Deleted',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

--
-- Dumping data for table `mt_u_users`
--

INSERT INTO `mt_u_users` (`id`, `u_firstname`, `u_lastname`, `u_email`, `u_gender`, `u_social_provider`, `u_fb_identifier`, `u_fb_accesstoken`, `u_phone`, `u_birthdate`, `u_description`, `u_school`, `u_current_work`, `u_looking_for`, `u_looking_distance`, `u_looking_age`, `u_compatibility_notification`, `u_newchat_notification`, `u_acceptance_notification`, `u_country`, `u_pincode`, `u_location`, `u_latitude`, `u_longitude`, `u_profile_active`, `is_question_attempted`, `remember_token`, `created_at`, `updated_at`, `deleted`) VALUES
(1, 'Dhara', 'Patel', 'dhara.gadhiya@inexture.in', 2, 'Facebook', '123456', NULL, '', '2016-11-08', 'Demo', 'GTU', 'PHP Developer', 0, 0, 0, 0, 0, 0, '0', NULL, NULL, '0.00000000', '0.00000000', 0, 0, NULL, '2016-11-07 09:01:03', NULL, 1),
(2, '', '', 'abc@abc.com', 0, '', '9877', NULL, '', NULL, '', '', '', 0, 0, 0, 0, 0, 0, '0', NULL, NULL, '0.00000000', '0.00000000', 0, 0, NULL, '2016-11-07 10:24:52', NULL, 1),
(3, '', '', 'abc@abc.com', 0, '', '9877899', NULL, '', NULL, '', '', '', 0, 0, 0, 0, 0, 0, '0', NULL, NULL, '0.00000000', '0.00000000', 0, 0, NULL, '2016-11-07 10:29:41', NULL, 1),
(4, '', '', 'abc@abc.com', 0, '', '98787', NULL, '', NULL, '', '', '', 0, 0, 0, 0, 0, 0, '0', NULL, NULL, '0.00000000', '0.00000000', 0, 0, NULL, '2016-11-07 10:52:13', NULL, 1),
(5, '', '', 'abc@abc.com', 0, '', '098999', NULL, '', NULL, '', '', '', 0, 0, 0, 0, 0, 0, '0', NULL, NULL, '0.00000000', '0.00000000', 0, 0, NULL, '2016-11-07 10:54:05', NULL, 1),
(6, '', '', 'abc@abc.com', 0, '', '54353', NULL, '', NULL, '', '', '', 0, 0, 0, 0, 0, 0, '0', NULL, NULL, '0.00000000', '0.00000000', 0, 0, NULL, '2016-11-07 10:55:03', NULL, 1),
(7, 'New', 'Patel', 'new@gmail.com', 1, '', '8767', NULL, '', '2015-10-12', 'Demo', 'Silver', 'Design', 0, 0, 0, 0, 0, 0, '0', NULL, NULL, '0.00000000', '0.00000000', 0, 0, NULL, '2016-11-07 10:55:49', NULL, 1),
(8, 'New', 'Patel', 'new@gmail.com', 2, '', '98989', NULL, '', NULL, 'Demo', 'Silver', 'Design', 0, 0, 0, 0, 0, 0, '0', NULL, NULL, '0.00000000', '0.00000000', 0, 0, NULL, '2016-11-07 11:01:55', NULL, 1);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
