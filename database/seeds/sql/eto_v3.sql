-- phpMyAdmin SQL Dump
-- version 4.7.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 20, 2018 at 11:56 PM
-- Server version: 5.7.17
-- PHP Version: 5.6.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `[DB_PREFIX]v3_migrate`
--

-- --------------------------------------------------------

--
-- Table structure for table `[DB_PREFIX]bases`
--

CREATE TABLE IF NOT EXISTS `[DB_PREFIX]bases` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ref_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ref_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lat` double(10,7) DEFAULT NULL,
  `lng` double(10,7) DEFAULT NULL,
  `radius` smallint(6) NOT NULL DEFAULT '0',
  `calculate_route` tinyint(4) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `selected` tinyint(4) NOT NULL DEFAULT '0',
  `status` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ref_id` (`ref_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `[DB_PREFIX]bases`
--

INSERT INTO `[DB_PREFIX]bases` (`id`, `ref_type`, `ref_id`, `name`, `description`, `address`, `lat`, `lng`, `radius`, `calculate_route`, `ordering`, `selected`, `status`, `created_at`, `updated_at`) VALUES
(1, NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL, '2018-02-20 21:53:21', '2018-02-20 21:53:21');

-- --------------------------------------------------------

--
-- Table structure for table `[DB_PREFIX]booking`
--

CREATE TABLE IF NOT EXISTS `[DB_PREFIX]booking` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `profile_id` smallint(5) NOT NULL DEFAULT '0',
  `user_id` int(10) NOT NULL DEFAULT '0',
  `unique_key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ref_number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `profile_id` (`profile_id`),
  KEY `user_id` (`user_id`),
  KEY `unique_key` (`unique_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `[DB_PREFIX]booking_route`
--

CREATE TABLE IF NOT EXISTS `[DB_PREFIX]booking_route` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` int(10) NOT NULL DEFAULT '0',
  `service_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `service_duration` smallint(6) NOT NULL DEFAULT '0',
  `driver_id` int(10) NOT NULL DEFAULT '0',
  `driver_data` text COLLATE utf8_unicode_ci,
  `vehicle_id` int(10) NOT NULL DEFAULT '0',
  `vehicle_data` text COLLATE utf8_unicode_ci,
  `commission` double(8,2) NOT NULL DEFAULT '0.00',
  `cash` double(8,2) NOT NULL DEFAULT '0.00',
  `status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ref_number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `route` tinyint(3) NOT NULL DEFAULT '0',
  `category_start` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `category_type_start` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `location_start` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `address_start` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `address_start_complete` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `coordinate_start_lat` float(10,6) NOT NULL DEFAULT '0.000000',
  `coordinate_start_lon` float(10,6) NOT NULL DEFAULT '0.000000',
  `waypoints` text COLLATE utf8_unicode_ci NOT NULL,
  `waypoints_complete` text COLLATE utf8_unicode_ci NOT NULL,
  `category_end` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `category_type_end` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `location_end` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `address_end` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `address_end_complete` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `coordinate_end_lat` float(10,6) NOT NULL DEFAULT '0.000000',
  `coordinate_end_lon` float(10,6) NOT NULL DEFAULT '0.000000',
  `distance` float(8,2) NOT NULL DEFAULT '0.00',
  `duration` int(11) NOT NULL DEFAULT '0',
  `distance_base_start` float(8,2) NOT NULL DEFAULT '0.00',
  `duration_base_start` int(10) NOT NULL DEFAULT '0',
  `distance_base_end` float(8,2) NOT NULL DEFAULT '0.00',
  `duration_base_end` int(10) NOT NULL DEFAULT '0',
  `date` datetime DEFAULT NULL,
  `flight_number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `departure_city` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `meet_and_greet` smallint(5) NOT NULL DEFAULT '0',
  `meeting_point` text COLLATE utf8_unicode_ci NOT NULL,
  `waiting_time` smallint(5) NOT NULL DEFAULT '0',
  `vehicle` text COLLATE utf8_unicode_ci NOT NULL,
  `vehicle_list` text COLLATE utf8_unicode_ci NOT NULL,
  `passengers` smallint(5) NOT NULL DEFAULT '0',
  `luggage` smallint(5) NOT NULL DEFAULT '0',
  `hand_luggage` smallint(5) NOT NULL DEFAULT '0',
  `child_seats` smallint(5) NOT NULL DEFAULT '0',
  `baby_seats` smallint(5) NOT NULL DEFAULT '0',
  `infant_seats` smallint(6) NOT NULL DEFAULT '0',
  `wheelchair` smallint(6) NOT NULL DEFAULT '0',
  `items` text COLLATE utf8_unicode_ci,
  `extra_charges_list` text COLLATE utf8_unicode_ci NOT NULL,
  `extra_charges_price` double(5,2) NOT NULL DEFAULT '0.00',
  `total_price` double(10,2) NOT NULL DEFAULT '0.00',
  `discount` double(8,2) NOT NULL DEFAULT '0.00',
  `discount_code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `contact_title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `contact_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `contact_email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `contact_mobile` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `lead_passenger_title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `lead_passenger_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `lead_passenger_mobile` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `requirements` text COLLATE utf8_unicode_ci NOT NULL,
  `notified` smallint(5) NOT NULL DEFAULT '0',
  `source` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `source_details` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `job_reminder` smallint(5) NOT NULL DEFAULT '0',
  `notes` text COLLATE utf8_unicode_ci,
  `modified_date` datetime DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `profile_id` (`booking_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `[DB_PREFIX]cache`
--

CREATE TABLE IF NOT EXISTS `[DB_PREFIX]cache` (
  `key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  `expiration` int(11) NOT NULL,
  UNIQUE KEY `easytaxioffice_cache_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `[DB_PREFIX]category`
--

CREATE TABLE IF NOT EXISTS `[DB_PREFIX]category` (
  `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `profile_id` smallint(5) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `icon` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `color` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '#777777',
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  `ordering` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `profile_id` (`profile_id`)
) ENGINE=MyISAM AUTO_INCREMENT=177 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `[DB_PREFIX]category`
--

INSERT INTO `[DB_PREFIX]category` (`id`, `profile_id`, `name`, `type`, `icon`, `color`, `featured`, `ordering`, `published`) VALUES
(1, 1, 'Airports', 'airport', 'fa-plane', '#337AB7', 1, 1, 1),
(2, 1, 'Cruise Ports', 'seaport', 'fa-ship', '#5CB85C', 1, 2, 1),
(3, 1, 'Hotels', 'hotel', 'fa-h-square', '#F0AD4E', 0, 5, 1),
(4, 1, 'Stations', 'station', 'fa-subway', '#5BC0DE', 0, 6, 1),
(5, 1, 'Address', 'address', 'fa-map-marker', '#959595', 0, 3, 1),
(6, 1, 'Post code', 'postcode', 'fa-map-marker', '#563D7C', 0, 4, 0);

-- --------------------------------------------------------

--
-- Table structure for table `[DB_PREFIX]charge`
--

CREATE TABLE IF NOT EXISTS `[DB_PREFIX]charge` (
  `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `profile_id` smallint(5) NOT NULL DEFAULT '0',
  `note` text COLLATE utf8_unicode_ci NOT NULL,
  `note_published` tinyint(1) NOT NULL DEFAULT '1',
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `params` text COLLATE utf8_unicode_ci NOT NULL,
  `value` double(5,2) NOT NULL DEFAULT '0.00',
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `profile_id` (`profile_id`)
) ENGINE=MyISAM AUTO_INCREMENT=703 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `[DB_PREFIX]charge`
--

INSERT INTO `[DB_PREFIX]charge` (`id`, `profile_id`, `note`, `note_published`, `type`, `params`, `value`, `start_date`, `end_date`, `published`) VALUES
(1, 1, 'Child seat', 1, 'child_seat', '', 0.00, NULL, NULL, 1),
(2, 1, '', 1, 'distance_min', '', 0.00, NULL, NULL, 1),
(8, 1, 'Pick up airport parking charge', 1, 'geocode_start', '[\"RH6 0PJ\",\"RH6 0NP\",\"RH6 0RN\",\"RH6 0LA\",\"RH6 0LL\",\"RH6 0NY\",\"RH6 0LG\",\"RH6 0PA\",\"RH6 0LX\",\"RH6 0NN\",\"RH6 0JJ\",\"RH6 0PH\",\"RH6 0GQ\",\"RH6 0JE\",\"RH6 0NX\",\"RH6 0JA\",\"RH6 0JD\",\"RH6 0DY\",\"RH6 0JG\",\"RH6 0JW\",\"RH6 0NS\",\"RH6 0RW\",\"RH6 0QP\",\"RH6 0QN\",\"RH6 0QL\",\"RH6 0QG\",\"RH6 0LP\",\"RH6 0LF\",\"RH6 0JH\",\"RH6 0EY\",\"RH6 0EZ\",\"RH6 0EU\",\"RH6 0FP\",\"RH6 0RJ\",\"RH6 0NG\",\"RH6 0SQ\",\"RH6 0WG\",\"RH6 0SG\",\"RH6 0LD\",\"RH6 0PQ\",\"RH6 0PE\",\"RH6 0LB\",\"RH6 0ND\",\"RH6 0NH\",\"RH6 0QS\",\"RH6 0BU\",\"TW6 1BJ\",\"TW6 1RR\",\"TW6 1AP\",\"TW6 1EW\",\"TW6 1QG\",\"TW6 3XA\",\"TW6 2GA\",\"TW6 2GT\",\"TW6 2QE\",\"TW6 1DU\",\"TW6 1PA\",\"TW6 1JH\",\"TW6 2RL\",\"TW6 1\",\"TW6 2\",\"TW6 3\",\"TW6\",\"LU2 9QT\",\"LU2 9QE\",\"LU2 9DH\",\"LU2 9LS\",\"LU2 9NE\",\"LU2 9LY\",\"LU2 9LA\",\"LU2 9GP\",\"LU2 9XJ\",\"LU2 9XD\",\"LU2 9PA\",\"LU2 9LX\",\"LU2 9NG\",\"LU2 9NQ\",\"LU2 9NR\",\"LU2 9QG\",\"LU2 9LZ\",\"LU2 9LU\",\"LU2 9NW\",\"CM24 1QW\",\"CM24 1RW\",\"CM24 1PP\",\"CM24 1PZ\",\"CM24 1PY\",\"CM24 1RL\",\"CM24 1\",\"B26 3QJ\",\"SO18 2NL\",\"BS48 3DY\",\"SS2 6YF\",\"GU14 6XA\",\"TN16 3BH\",\"TN16 3BP\",\"HA4 6NG\",\"UB9 5DF\",\"UB9 5DN\",\"CT17 9EQ\",\"CT17\",\"CO12 4SR\",\"SO15 1BS\",\"E16 2PX\",\"E16 2PB\",\"E16 2\"]', 0.00, NULL, NULL, 1),
(9, 1, 'Pick up airport parking charge', 1, 'geocode_start2', '[\"E16 2PX\",\"E16 2PB\",\"E16 2HN\",\"E16\"]', 0.00, NULL, NULL, 1),
(14, 1, 'Drop off airport parking charge', 1, 'geocode_end', '[\"LU2 9QT\",\"LU2 9QE\",\"LU2 9DH\",\"LU2 9LS\",\"LU2 9NE\",\"LU2 9LY\",\"LU2 9LA\",\"LU2 9GP\",\"LU2 9XJ\",\"LU2 9XD\",\"LU2 9PA\",\"LU2 9LX\",\"LU2 9NG\",\"LU2 9NQ\",\"LU2 9NR\",\"LU2 9QG\",\"LU2 9LZ\",\"LU2 9LU\",\"LU2 9NW\",\"CM24 1QW\",\"CM24 1RW\",\"CM24 1PP\",\"CM24 1PZ\",\"CM24 1PY\",\"CM24 1RL\",\"CM24 1\"]', 0.00, NULL, NULL, 1),
(15, 1, 'Southend airport charge', 0, 'geocode_both', '[\"SS2 6YF\"]', 0.00, NULL, NULL, 0),
(98, 1, 'All airports', 1, 'airport_postcodes', '[\"RH6 0PJ\",\"RH6 0NP\",\"RH6 0RN\",\"RH6 0LA\",\"RH6 0LL\",\"RH6 0NY\",\"RH6 0LG\",\"RH6 0PA\",\"RH6 0LX\",\"RH6 0NN\",\"RH6 0JJ\",\"RH6 0PH\",\"RH6 0GQ\",\"RH6 0JE\",\"RH6 0NX\",\"RH6 0JA\",\"RH6 0JD\",\"RH6 0DY\",\"RH6 0JG\",\"RH6 0JW\",\"RH6 0NS\",\"RH6 0RW\",\"RH6 0QP\",\"RH6 0QN\",\"RH6 0QL\",\"RH6 0QG\",\"RH6 0LP\",\"RH6 0LF\",\"RH6 0JH\",\"RH6 0EY\",\"RH6 0EZ\",\"RH6 0EU\",\"RH6 0FP\",\"RH6 0RJ\",\"RH6 0NG\",\"RH6 0SQ\",\"RH6 0WG\",\"RH6 0SG\",\"RH6 0LD\",\"RH6 0PQ\",\"RH6 0PE\",\"RH6 0LB\",\"RH6 0ND\",\"RH6 0NH\",\"RH6 0QS\",\"RH6 0BU\",\"TW6 1BJ\",\"TW6 1RR\",\"TW6 1AP\",\"TW6 1EW\",\"TW6 1QG\",\"TW6 3XA\",\"TW6 2GA\",\"TW6 2GT\",\"TW6 2QE\",\"TW6 1DU\",\"TW6 1PA\",\"TW6 1JH\",\"TW6 2RL\",\"TW6 1\",\"TW6 2\",\"TW6 3\",\"TW6\",\"LU2 9QT\",\"LU2 9QE\",\"LU2 9DH\",\"LU2 9LS\",\"LU2 9NE\",\"LU2 9LY\",\"LU2 9LA\",\"LU2 9GP\",\"LU2 9XJ\",\"LU2 9XD\",\"LU2 9PA\",\"LU2 9LX\",\"LU2 9NG\",\"LU2 9NQ\",\"LU2 9NR\",\"LU2 9QG\",\"LU2 9LZ\",\"LU2 9LU\",\"LU2 9NW\",\"CM24 1QW\",\"CM24 1RW\",\"CM24 1PP\",\"CM24 1PZ\",\"CM24 1PY\",\"CM24 1RL\",\"CM24 1\",\"B26 3QJ\",\"SO18 2NL\",\"BS48 3DY\",\"SS2 6YF\",\"GU14 6XA\",\"TN16 3BH\",\"TN16 3BP\",\"HA4 6NG\",\"UB9 5DF\",\"UB9 5DN\",\"CT17 9EQ\",\"CT17\",\"CO12 4SR\",\"SO15 1BS\",\"E16 2PX\",\"E16 2PB\",\"E16 2\"]', 0.00, NULL, NULL, 1),
(61, 1, 'Booster seat', 1, 'baby_seat', '', 0.00, NULL, NULL, 1),
(62, 1, 'Meet and greet', 1, 'meet_and_greet', '', 0.00, NULL, NULL, 1),
(63, 1, 'Extra pick up / drop off', 1, 'waypoint', '', 0.00, NULL, NULL, 1),
(251, 1, 'Infant seat', 1, 'infant_seats', '', 0.00, NULL, NULL, 1),
(255, 1, 'Wheelchair', 1, 'wheelchair', '', 0.00, NULL, NULL, 1),
(293, 1, 'Waiting time after landing', 1, 'waiting_time', '', 0.00, NULL, NULL, 1),
(547, 1, 'Pick up airport parking charge', 1, 'parking', '{\"location\":{\"enabled\":1,\"type\":\"from\",\"list\":[{\"address\":\"RH6 0PJ\",\"postcode\":\"RH6 0PJ\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0NP\",\"postcode\":\"RH6 0NP\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0RN\",\"postcode\":\"RH6 0RN\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0LA\",\"postcode\":\"RH6 0LA\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0LL\",\"postcode\":\"RH6 0LL\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0NY\",\"postcode\":\"RH6 0NY\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0LG\",\"postcode\":\"RH6 0LG\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0PA\",\"postcode\":\"RH6 0PA\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0LX\",\"postcode\":\"RH6 0LX\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0NN\",\"postcode\":\"RH6 0NN\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0JJ\",\"postcode\":\"RH6 0JJ\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0PH\",\"postcode\":\"RH6 0PH\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0GQ\",\"postcode\":\"RH6 0GQ\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0JE\",\"postcode\":\"RH6 0JE\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0NX\",\"postcode\":\"RH6 0NX\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0JA\",\"postcode\":\"RH6 0JA\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0JD\",\"postcode\":\"RH6 0JD\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0DY\",\"postcode\":\"RH6 0DY\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0JG\",\"postcode\":\"RH6 0JG\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0JW\",\"postcode\":\"RH6 0JW\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0NS\",\"postcode\":\"RH6 0NS\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0RW\",\"postcode\":\"RH6 0RW\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0QP\",\"postcode\":\"RH6 0QP\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0QN\",\"postcode\":\"RH6 0QN\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0QL\",\"postcode\":\"RH6 0QL\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0QG\",\"postcode\":\"RH6 0QG\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0LP\",\"postcode\":\"RH6 0LP\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0LF\",\"postcode\":\"RH6 0LF\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0JH\",\"postcode\":\"RH6 0JH\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0EY\",\"postcode\":\"RH6 0EY\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0EZ\",\"postcode\":\"RH6 0EZ\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0EU\",\"postcode\":\"RH6 0EU\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0FP\",\"postcode\":\"RH6 0FP\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0RJ\",\"postcode\":\"RH6 0RJ\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0NG\",\"postcode\":\"RH6 0NG\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0SQ\",\"postcode\":\"RH6 0SQ\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0WG\",\"postcode\":\"RH6 0WG\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0SG\",\"postcode\":\"RH6 0SG\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0LD\",\"postcode\":\"RH6 0LD\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0PQ\",\"postcode\":\"RH6 0PQ\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0PE\",\"postcode\":\"RH6 0PE\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0LB\",\"postcode\":\"RH6 0LB\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0ND\",\"postcode\":\"RH6 0ND\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0NH\",\"postcode\":\"RH6 0NH\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0QS\",\"postcode\":\"RH6 0QS\",\"lat\":0,\"lng\":0},{\"address\":\"RH6 0BU\",\"postcode\":\"RH6 0BU\",\"lat\":0,\"lng\":0},{\"address\":\"TW6 1BJ\",\"postcode\":\"TW6 1BJ\",\"lat\":0,\"lng\":0},{\"address\":\"TW6 1RR\",\"postcode\":\"TW6 1RR\",\"lat\":0,\"lng\":0},{\"address\":\"TW6 1AP\",\"postcode\":\"TW6 1AP\",\"lat\":0,\"lng\":0},{\"address\":\"TW6 1EW\",\"postcode\":\"TW6 1EW\",\"lat\":0,\"lng\":0},{\"address\":\"TW6 1QG\",\"postcode\":\"TW6 1QG\",\"lat\":0,\"lng\":0},{\"address\":\"TW6 3XA\",\"postcode\":\"TW6 3XA\",\"lat\":0,\"lng\":0},{\"address\":\"TW6 2GA\",\"postcode\":\"TW6 2GA\",\"lat\":0,\"lng\":0},{\"address\":\"TW6 2GT\",\"postcode\":\"TW6 2GT\",\"lat\":0,\"lng\":0},{\"address\":\"TW6 2QE\",\"postcode\":\"TW6 2QE\",\"lat\":0,\"lng\":0},{\"address\":\"TW6 1DU\",\"postcode\":\"TW6 1DU\",\"lat\":0,\"lng\":0},{\"address\":\"TW6 1PA\",\"postcode\":\"TW6 1PA\",\"lat\":0,\"lng\":0},{\"address\":\"TW6 1JH\",\"postcode\":\"TW6 1JH\",\"lat\":0,\"lng\":0},{\"address\":\"TW6 2RL\",\"postcode\":\"TW6 2RL\",\"lat\":0,\"lng\":0},{\"address\":\"TW6 1\",\"postcode\":\"TW6 1\",\"lat\":0,\"lng\":0},{\"address\":\"TW6 2\",\"postcode\":\"TW6 2\",\"lat\":0,\"lng\":0},{\"address\":\"TW6 3\",\"postcode\":\"TW6 3\",\"lat\":0,\"lng\":0},{\"address\":\"TW6\",\"postcode\":\"TW6\",\"lat\":0,\"lng\":0},{\"address\":\"LU2 9QT\",\"postcode\":\"LU2 9QT\",\"lat\":0,\"lng\":0},{\"address\":\"LU2 9QE\",\"postcode\":\"LU2 9QE\",\"lat\":0,\"lng\":0},{\"address\":\"LU2 9DH\",\"postcode\":\"LU2 9DH\",\"lat\":0,\"lng\":0},{\"address\":\"LU2 9LS\",\"postcode\":\"LU2 9LS\",\"lat\":0,\"lng\":0},{\"address\":\"LU2 9NE\",\"postcode\":\"LU2 9NE\",\"lat\":0,\"lng\":0},{\"address\":\"LU2 9LY\",\"postcode\":\"LU2 9LY\",\"lat\":0,\"lng\":0},{\"address\":\"LU2 9LA\",\"postcode\":\"LU2 9LA\",\"lat\":0,\"lng\":0},{\"address\":\"LU2 9GP\",\"postcode\":\"LU2 9GP\",\"lat\":0,\"lng\":0},{\"address\":\"LU2 9XJ\",\"postcode\":\"LU2 9XJ\",\"lat\":0,\"lng\":0},{\"address\":\"LU2 9XD\",\"postcode\":\"LU2 9XD\",\"lat\":0,\"lng\":0},{\"address\":\"LU2 9PA\",\"postcode\":\"LU2 9PA\",\"lat\":0,\"lng\":0},{\"address\":\"LU2 9LX\",\"postcode\":\"LU2 9LX\",\"lat\":0,\"lng\":0},{\"address\":\"LU2 9NG\",\"postcode\":\"LU2 9NG\",\"lat\":0,\"lng\":0},{\"address\":\"LU2 9NQ\",\"postcode\":\"LU2 9NQ\",\"lat\":0,\"lng\":0},{\"address\":\"LU2 9NR\",\"postcode\":\"LU2 9NR\",\"lat\":0,\"lng\":0},{\"address\":\"LU2 9QG\",\"postcode\":\"LU2 9QG\",\"lat\":0,\"lng\":0},{\"address\":\"LU2 9LZ\",\"postcode\":\"LU2 9LZ\",\"lat\":0,\"lng\":0},{\"address\":\"LU2 9LU\",\"postcode\":\"LU2 9LU\",\"lat\":0,\"lng\":0},{\"address\":\"LU2 9NW\",\"postcode\":\"LU2 9NW\",\"lat\":0,\"lng\":0},{\"address\":\"CM24 1QW\",\"postcode\":\"CM24 1QW\",\"lat\":0,\"lng\":0},{\"address\":\"CM24 1RW\",\"postcode\":\"CM24 1RW\",\"lat\":0,\"lng\":0},{\"address\":\"CM24 1PP\",\"postcode\":\"CM24 1PP\",\"lat\":0,\"lng\":0},{\"address\":\"CM24 1PZ\",\"postcode\":\"CM24 1PZ\",\"lat\":0,\"lng\":0},{\"address\":\"CM24 1PY\",\"postcode\":\"CM24 1PY\",\"lat\":0,\"lng\":0},{\"address\":\"CM24 1RL\",\"postcode\":\"CM24 1RL\",\"lat\":0,\"lng\":0},{\"address\":\"CM24 1\",\"postcode\":\"CM24 1\",\"lat\":0,\"lng\":0},{\"address\":\"B26 3QJ\",\"postcode\":\"B26 3QJ\",\"lat\":0,\"lng\":0},{\"address\":\"SO18 2NL\",\"postcode\":\"SO18 2NL\",\"lat\":0,\"lng\":0},{\"address\":\"BS48 3DY\",\"postcode\":\"BS48 3DY\",\"lat\":0,\"lng\":0},{\"address\":\"SS2 6YF\",\"postcode\":\"SS2 6YF\",\"lat\":0,\"lng\":0},{\"address\":\"GU14 6XA\",\"postcode\":\"GU14 6XA\",\"lat\":0,\"lng\":0},{\"address\":\"TN16 3BH\",\"postcode\":\"TN16 3BH\",\"lat\":0,\"lng\":0},{\"address\":\"TN16 3BP\",\"postcode\":\"TN16 3BP\",\"lat\":0,\"lng\":0},{\"address\":\"HA4 6NG\",\"postcode\":\"HA4 6NG\",\"lat\":0,\"lng\":0},{\"address\":\"UB9 5DF\",\"postcode\":\"UB9 5DF\",\"lat\":0,\"lng\":0},{\"address\":\"UB9 5DN\",\"postcode\":\"UB9 5DN\",\"lat\":0,\"lng\":0},{\"address\":\"CT17 9EQ\",\"postcode\":\"CT17 9EQ\",\"lat\":0,\"lng\":0},{\"address\":\"CT17\",\"postcode\":\"CT17\",\"lat\":0,\"lng\":0},{\"address\":\"CO12 4SR\",\"postcode\":\"CO12 4SR\",\"lat\":0,\"lng\":0},{\"address\":\"SO15 1BS\",\"postcode\":\"SO15 1BS\",\"lat\":0,\"lng\":0},{\"address\":\"E16 2PX\",\"postcode\":\"E16 2PX\",\"lat\":0,\"lng\":0},{\"address\":\"E16 2PB\",\"postcode\":\"E16 2PB\",\"lat\":0,\"lng\":0},{\"address\":\"E16 2\",\"postcode\":\"E16 2\",\"lat\":0,\"lng\":0}]}}', 0.00, NULL, NULL, 1),
(549, 1, 'Drop off airport parking charge', 1, 'parking', '{\"location\":{\"enabled\":1,\"type\":\"to\",\"list\":[{\"address\":\"LU2 9QT\",\"postcode\":\"LU2 9QT\",\"lat\":0,\"lng\":0},{\"address\":\"LU2 9QE\",\"postcode\":\"LU2 9QE\",\"lat\":0,\"lng\":0},{\"address\":\"LU2 9DH\",\"postcode\":\"LU2 9DH\",\"lat\":0,\"lng\":0},{\"address\":\"LU2 9LS\",\"postcode\":\"LU2 9LS\",\"lat\":0,\"lng\":0},{\"address\":\"LU2 9NE\",\"postcode\":\"LU2 9NE\",\"lat\":0,\"lng\":0},{\"address\":\"LU2 9LY\",\"postcode\":\"LU2 9LY\",\"lat\":0,\"lng\":0},{\"address\":\"LU2 9LA\",\"postcode\":\"LU2 9LA\",\"lat\":0,\"lng\":0},{\"address\":\"LU2 9GP\",\"postcode\":\"LU2 9GP\",\"lat\":0,\"lng\":0},{\"address\":\"LU2 9XJ\",\"postcode\":\"LU2 9XJ\",\"lat\":0,\"lng\":0},{\"address\":\"LU2 9XD\",\"postcode\":\"LU2 9XD\",\"lat\":0,\"lng\":0},{\"address\":\"LU2 9PA\",\"postcode\":\"LU2 9PA\",\"lat\":0,\"lng\":0},{\"address\":\"LU2 9LX\",\"postcode\":\"LU2 9LX\",\"lat\":0,\"lng\":0},{\"address\":\"LU2 9NG\",\"postcode\":\"LU2 9NG\",\"lat\":0,\"lng\":0},{\"address\":\"LU2 9NQ\",\"postcode\":\"LU2 9NQ\",\"lat\":0,\"lng\":0},{\"address\":\"LU2 9NR\",\"postcode\":\"LU2 9NR\",\"lat\":0,\"lng\":0},{\"address\":\"LU2 9QG\",\"postcode\":\"LU2 9QG\",\"lat\":0,\"lng\":0},{\"address\":\"LU2 9LZ\",\"postcode\":\"LU2 9LZ\",\"lat\":0,\"lng\":0},{\"address\":\"LU2 9LU\",\"postcode\":\"LU2 9LU\",\"lat\":0,\"lng\":0},{\"address\":\"LU2 9NW\",\"postcode\":\"LU2 9NW\",\"lat\":0,\"lng\":0},{\"address\":\"CM24 1QW\",\"postcode\":\"CM24 1QW\",\"lat\":0,\"lng\":0},{\"address\":\"CM24 1RW\",\"postcode\":\"CM24 1RW\",\"lat\":0,\"lng\":0},{\"address\":\"CM24 1PP\",\"postcode\":\"CM24 1PP\",\"lat\":0,\"lng\":0},{\"address\":\"CM24 1PZ\",\"postcode\":\"CM24 1PZ\",\"lat\":0,\"lng\":0},{\"address\":\"CM24 1PY\",\"postcode\":\"CM24 1PY\",\"lat\":0,\"lng\":0},{\"address\":\"CM24 1RL\",\"postcode\":\"CM24 1RL\",\"lat\":0,\"lng\":0},{\"address\":\"CM24 1\",\"postcode\":\"CM24 1\",\"lat\":0,\"lng\":0}]}}', 0.00, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `[DB_PREFIX]config`
--

CREATE TABLE IF NOT EXISTS `[DB_PREFIX]config` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `profile_id` smallint(5) NOT NULL DEFAULT '0',
  `key` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  `type` char(8) COLLATE utf8_unicode_ci NOT NULL,
  `browser` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `profile_id` (`profile_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3835 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `[DB_PREFIX]config`
--

INSERT INTO `[DB_PREFIX]config` (`id`, `profile_id`, `key`, `value`, `type`, `browser`) VALUES
(2, 1, 'min_booking_time_limit', '8', 'int', 1),
(3, 1, 'booking_map_enable', '1', 'int', 1),
(4, 1, 'auto_payment_redirection', '1', 'int', 1),
(5, 1, 'ref_format', '{rand6}', 'string', 0),
(6, 1, 'display_field_help', '1', 'int', 1),
(7, 1, 'language', 'en-GB', 'string', 1),
(2216, 1, 'mail_host', '[MAIL_HOST]', 'string', 0),
(2214, 1, 'code_body', '', 'string', 0),
(543, 1, 'booking_required_contact_mobile', '0', 'int', 1),
(544, 1, 'booking_required_waiting_time', '0', 'int', 1),
(545, 1, 'booking_required_address_complete', '0', 'int', 1),
(546, 1, 'booking_required_flight_number', '0', 'int', 1),
(547, 1, 'booking_required_departure_city', '0', 'int', 1),
(548, 1, 'booking_required_passengers', '0', 'int', 1),
(549, 1, 'booking_required_child_seats', '0', 'int', 1),
(550, 1, 'booking_required_baby_seats', '0', 'int', 1),
(551, 1, 'booking_required_luggage', '0', 'int', 1),
(2198, 1, 'driver_show_total', '0', 'string', 0),
(2197, 1, 'google_analytics_tracking_id', '', 'string', 0),
(21, 1, 'company_name', '[APP_NAME]', 'string', 1),
(22, 1, 'company_email', '[APP_EMAIL]', 'string', 1),
(23, 1, 'thank_you_message', '', 'string', 0),
(24, 1, 'payment_thank_you_message', '', 'string', 0),
(25, 1, 'source_list', '[\"Website\",\"By phone\",\"Direct\",\"Admin\",\"Other\"]', 'string', 0),
(26, 1, 'status_list', '[]', 'string', 0),
(27, 1, 'payment_status_list', '[]', 'string', 0),
(97, 1, 'enable_passengers', '1', 'int', 1),
(98, 1, 'enable_luggage', '1', 'int', 1),
(99, 1, 'enable_hand_luggage', '1', 'int', 1),
(100, 1, 'enable_child_seats', '0', 'int', 1),
(101, 1, 'enable_baby_seats', '0', 'int', 1),
(102, 1, 'night_charge_enable', '0', 'int', 0),
(103, 1, 'night_charge_start', '00:00', 'string', 0),
(104, 1, 'night_charge_end', '00:00', 'string', 0),
(105, 1, 'night_charge_factor', '0', 'float', 0),
(106, 1, 'quote_avoid_highways', '1', 'int', 0),
(107, 1, 'quote_avoid_tolls', '1', 'int', 0),
(108, 1, 'quote_avoid_ferries', '1', 'int', 0),
(109, 1, 'quote_enable_shortest_route', '0', 'int', 0),
(110, 1, 'delete_incomplete_bookings_after', '0', 'int', 0),
(111, 1, 'quote_distance_range', '[{\"distance\":10,\"value\":2.5,\"factor_type\":2,\"vehicle\":[{\"id\":4,\"value\":2.5},{\"id\":13,\"value\":1.2},{\"id\":5,\"value\":1.5},{\"id\":2,\"value\":2},{\"id\":1,\"value\":1}]},{\"distance\":25,\"value\":2.15,\"factor_type\":2,\"vehicle\":[{\"id\":4,\"value\":2.5},{\"id\":13,\"value\":1.2},{\"id\":5,\"value\":1.5},{\"id\":2,\"value\":2},{\"id\":1,\"value\":1}]},{\"distance\":40,\"value\":2.2,\"factor_type\":2,\"vehicle\":[{\"id\":4,\"value\":2.5},{\"id\":13,\"value\":1.2},{\"id\":5,\"value\":1.5},{\"id\":2,\"value\":2},{\"id\":1,\"value\":1}]},{\"distance\":50,\"value\":1.6,\"factor_type\":2,\"vehicle\":[{\"id\":4,\"value\":2.5},{\"id\":13,\"value\":1.2},{\"id\":5,\"value\":1.5},{\"id\":2,\"value\":2},{\"id\":1,\"value\":1}]},{\"distance\":1000,\"value\":1.5,\"factor_type\":2,\"vehicle\":[{\"id\":4,\"value\":2.5},{\"id\":13,\"value\":1.2},{\"id\":5,\"value\":1.5},{\"id\":2,\"value\":2},{\"id\":1,\"value\":1}]}]', 'object', 0),
(2184, 1, 'incomplete_bookings_display', '1', 'int', 0),
(2185, 1, 'incomplete_bookings_delete_enable', '0', 'int', 0),
(2186, 1, 'incomplete_bookings_delete_after', '72', 'int', 0),
(112, 1, 'password_length_min', '6', 'int', 1),
(113, 1, 'password_length_max', '30', 'int', 1),
(114, 1, 'url_terms', 'https://example.com/terms-and-conditions/', 'string', 1),
(2262, 1, 'locale_switcher_enabled', '0', 'int', 0),
(2263, 1, 'locale_switcher_style', 'dropdown', 'string', 0),
(2194, 1, 'booking_base_action', 'disallow', 'string', 0),
(119, 1, 'register_enable', '1', 'int', 1),
(120, 1, 'register_activation_enable', '0', 'int', 0),
(121, 1, 'login_enable', '1', 'int', 1),
(2190, 1, 'textlocal_api_key', '', 'string', 0),
(2191, 1, 'textlocal_test_mode', '0', 'int', 0),
(124, 1, 'company_telephone', '', 'string', 1),
(125, 1, 'url_home', 'https://example.com', 'string', 1),
(126, 1, 'url_feedback', 'https://example.com/contact/', 'string', 1),
(127, 1, 'url_contact', 'https://example.com/contact/', 'string', 1),
(128, 1, 'url_booking', 'https://example.com/book-now/', 'string', 1),
(129, 1, 'booking_cancel_time', '48', 'int', 0),
(2189, 1, 'textlocal_enabled', '0', 'int', 0),
(2277, 1, 'logo', '', 'string', 1),
(526, 1, 'eto_branding', '1', 'int', 1),
(527, 1, 'quote_enable_straight_line', '0', 'int', 0),
(2215, 1, 'mail_driver', '[MAIL_DRIVER]', 'string', 0),
(2201, 1, 'invoice_info', '', 'string', 0),
(530, 1, 'company_address', '', 'string', 1),
(531, 1, 'manual_thank_you_message', '', 'string', 0),
(533, 1, 'booking_waiting_time_enable', '0', 'int', 1),
(534, 1, 'booking_round_total_price', '0', 'int', 0),
(535, 1, 'booking_directions_enable', '1', 'int', 1),
(536, 1, 'booking_cancel_enable', '1', 'int', 0),
(537, 1, 'booking_request_time', '02:00', 'string', 0),
(538, 1, 'booking_request_enable', '0', 'int', 0),
(2193, 1, 'pcapredict_api_key', '', 'string', 0),
(552, 1, 'booking_required_hand_luggage', '0', 'int', 1),
(541, 1, 'url_customer', 'https://example.com/my-account/', 'string', 1),
(553, 1, 'debug', '0', 'int', 1),
(554, 1, 'secret_key', '', 'string', 0),
(555, 1, 'google_places_api_key', '', 'string', 0),
(556, 1, 'google_maps_geocoding_api_key', '', 'string', 0),
(557, 1, 'google_maps_directions_api_key', '', 'string', 0),
(558, 1, 'google_maps_embed_api_key', '', 'string', 1),
(559, 1, 'locations_skip_place_id', '', 'string', 0),
(562, 1, 'quote_address_suffix', '', 'string', 0),
(563, 1, 'embedded', '0', 'int', 1),
(2200, 1, 'invoice_display_details', '1', 'int', 0),
(1215, 1, 'booking_terms_disable_button', '0', 'int', 1),
(1219, 1, 'booking_summary_enable', '1', 'int', 1),
(1222, 1, 'currency_symbol', '£', 'string', 1),
(1223, 1, 'currency_code', '', 'string', 1),
(1225, 1, 'autocomplete_google_places', '1', 'int', 0),
(1226, 1, 'autocomplete_force_selection', '0', 'int', 1),
(1227, 1, 'booking_map_zoom', '10', 'int', 1),
(1228, 1, 'booking_map_draggable', '1', 'int', 1),
(1229, 1, 'booking_map_zoomcontrol', '1', 'int', 1),
(1230, 1, 'booking_map_scrollwheel', '0', 'int', 1),
(1231, 1, 'google_country_code', '', 'string', 1),
(1232, 1, 'booking_distance_unit', '0', 'int', 0),
(1233, 1, 'google_adwords_conversions', '', 'object', 1),
(2192, 1, 'pcapredict_enabled', '0', 'int', 0),
(1348, 1, 'booking_time_picker_style', '0', 'int', 1),
(1347, 1, 'booking_date_picker_style', '0', 'int', 1),
(1346, 1, 'booking_meet_and_greet_enable', '1', 'int', 1),
(1345, 1, 'booking_map_open', '0', 'int', 1),
(1344, 1, 'google_region_code', '', 'string', 1),
(2163, 1, 'booking_include_aiport_charges', '0', 'int', 0),
(2164, 1, 'booking_required_infant_seats', '0', 'int', 1),
(2165, 1, 'booking_min_price_type', '0', 'int', 0),
(2166, 1, 'booking_account_discount', '0', 'float', 1),
(2167, 1, 'enable_infant_seats', '0', 'int', 1),
(2188, 1, 'google_maps_javascript_api_key', '', 'string', 1),
(2170, 1, 'booking_meet_and_greet_compulsory', '0', 'int', 1),
(2171, 1, 'booking_required_wheelchair', '0', 'int', 1),
(2172, 1, 'booking_account_autocompletion', '1', 'int', 1),
(2173, 1, 'enable_wheelchair', '0', 'int', 1),
(2199, 1, 'invoice_enabled', '1', 'int', 0),
(2175, 1, 'booking_duration_rate', '0', 'float', 0),
(2176, 1, 'booking_items', '[]', 'object', 1),
(2187, 1, 'google_language', '', 'string', 1),
(2204, 1, 'invoice_display_logo', '1', 'int', 0),
(2205, 1, 'invoice_bill_from', '', 'string', 0),
(2206, 1, 'company_number', '', 'string', 1),
(2177, 1, 'booking_return_discount', '0', 'float', 1),
(2178, 1, 'booking_allow_one_type_of_child_seat', '0', 'int', 1),
(2179, 1, 'quote_duration_in_traffic', '1', 'int', 0),
(2180, 1, 'quote_traffic_model', 'pessimistic', 'string', 0),
(2181, 1, 'booking_deposit', '[]', 'object', 0),
(2182, 1, 'booking_deposit_balance', 'card', 'string', 0),
(2183, 1, 'booking_deposit_selected', 'deposit', 'string', 0),
(2207, 1, 'company_tax_number', '', 'string', 1),
(2208, 1, 'invoice_display_payments', '1', 'int', 0),
(2213, 1, 'code_head', '', 'string', 0),
(2211, 1, 'tax_name', '', 'string', 0),
(2212, 1, 'tax_percent', '0', 'float', 0),
(2217, 1, 'mail_port', '[MAIL_PORT]', 'string', 0),
(2218, 1, 'mail_username', '[MAIL_USERNAME]', 'string', 0),
(2219, 1, 'mail_password', '[MAIL_PASS]', 'string', 0),
(2220, 1, 'mail_encryption', '[MAIL_ENCRYPT]', 'string', 0),
(2221, 1, 'timezone', 'Europe/London', 'string', 0),
(2222, 1, 'date_format', 'd/m/Y', 'string', 0),
(2223, 1, 'time_format', 'H:i', 'string', 0),
(2224, 1, 'styles_default_bg_color', '#1c70b1', 'string', 0),
(2225, 1, 'styles_active_bg_color', '#185f96', 'string', 0),
(2226, 1, 'styles_default_border_color', '#1c70b1', 'string', 0),
(2227, 1, 'styles_default_text_color', '#ffffff', 'string', 0),
(2228, 1, 'styles_active_border_color', '#185f96', 'string', 0),
(2229, 1, 'styles_active_text_color', '#ffffff', 'string', 0),
(2230, 1, 'custom_css', '#etoMinimalContainer .etoMinimalContainer {\r\n    background: rgba(245, 245, 245, 0.8);\r\n    padding: 20px;\r\n    width: 450px;\r\n    max-width: 100%;\r\n    margin: 40px auto 0 auto;\r\n    border-radius: 2px;\r\n    box-shadow: 0px 0px 5px #cbcbcb;\r\n    border: 1px #ffffff solid;\r\n}', 'string', 0),
(2235, 1, 'invoice_styles_default_bg_color', '#3b8cc1', 'string', 0),
(2236, 1, 'invoice_styles_default_text_color', '#ffffff', 'string', 0),
(2237, 1, 'invoice_styles_active_bg_color', '#2f75a8', 'string', 0),
(2238, 1, 'invoice_styles_active_text_color', '#ffffff', 'string', 0),
(2239, 1, 'driver_show_unique_id', '1', 'string', 0),
(2240, 1, 'driver_show_reject_button', '1', 'string', 0),
(2241, 1, 'driver_show_restart_button', '0', 'string', 0),
(2242, 1, 'driver_show_passenger_phone_number', '1', 'string', 0),
(2243, 1, 'driver_show_passenger_email', '0', 'string', 0),
(2244, 1, 'start_of_week', '1', 'string', 0),
(2245, 1, 'booking_listing_refresh_type', '1', 'int', 0),
(2246, 1, 'booking_listing_refresh_interval', '60', 'int', 0),
(2247, 1, 'booking_listing_refresh_counter', '0', 'int', 0),
(2248, 1, 'night_charge_factor_type', '0', 'int', 0),
(2249, 1, 'fixed_prices_deposit_enable', '0', 'int', 0),
(2250, 1, 'fixed_prices_deposit_type', '0', 'int', 0),
(2264, 1, 'locale_switcher_display', 'names_flags', 'string', 0),
(2261, 1, 'locale_active', '[\"en-GB\",\"es-ES\",\"pt-PT\",\"pt-BR\",\"it-IT\",\"ru-RU\",\"hu-HU\",\"fr-FR\",\"de-DE\",\"pl-PL\"]', 'object', 0),
(3827, 1, 'booking_required_flight_landing_time', '0', 'int', 1),
(3828, 1, 'booking_flight_landing_time_enable', '0', 'int', 1),
(3829, 1, 'mail_sendmail', '[MAIL_SENDMAIL]', 'string', 0),
(3830, 1, 'customer_allow_company_number', '0', 'string', 0),
(3831, 1, 'customer_require_company_number', '0', 'string', 0),
(3832, 1, 'customer_allow_company_tax_number', '0', 'string', 0),
(3833, 1, 'customer_require_company_tax_number', '0', 'string', 0),
(3834, 1, 'admin_default_page', 'getting-started', 'string', 0);

-- --------------------------------------------------------

--
-- Table structure for table `[DB_PREFIX]discount`
--

CREATE TABLE IF NOT EXISTS `[DB_PREFIX]discount` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `profile_id` tinyint(5) NOT NULL DEFAULT '0',
  `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 - Flat | 1 - Percent',
  `value` double(5,2) NOT NULL DEFAULT '0.00',
  `allowed_times` smallint(5) NOT NULL DEFAULT '0',
  `used_times` smallint(5) NOT NULL DEFAULT '0',
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `minimum_bookings` smallint(5) NOT NULL DEFAULT '0',
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `created_date` datetime DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `[DB_PREFIX]events`
--

CREATE TABLE IF NOT EXISTS `[DB_PREFIX]events` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ref_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ref_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `start_at` datetime DEFAULT NULL,
  `end_at` datetime DEFAULT NULL,
  `repeat_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `repeat_interval` smallint(5) UNSIGNED DEFAULT '0',
  `repeat_days` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `repeat_end` datetime DEFAULT NULL,
  `repeat_limit` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `status` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ref_id` (`ref_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Table structure for table `[DB_PREFIX]excluded_routes`
--

CREATE TABLE IF NOT EXISTS `[DB_PREFIX]excluded_routes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `profile_id` int(11) NOT NULL DEFAULT '0',
  `direction` tinyint(1) NOT NULL DEFAULT '0',
  `start_postcode` text COLLATE utf8_unicode_ci,
  `end_postcode` text COLLATE utf8_unicode_ci,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `allowed` tinyint(1) NOT NULL DEFAULT '0',
  `vehicles` text COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `modified_date` datetime DEFAULT NULL,
  `ordering` int(10) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `[DB_PREFIX]file`
--

CREATE TABLE IF NOT EXISTS `[DB_PREFIX]file` (
  `file_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `file_profile_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `file_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `file_description` text COLLATE utf8_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `file_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'category',
  `file_ref_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `file_free_download` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `file_ordering` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `file_limit` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`file_id`),
  KEY `file_type` (`file_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `[DB_PREFIX]fixed_prices`
--

CREATE TABLE IF NOT EXISTS `[DB_PREFIX]fixed_prices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `profile_id` int(10) NOT NULL DEFAULT '0',
  `service_ids` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` tinyint(3) NOT NULL DEFAULT '0',
  `start_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 - Include | 1 - Exclude',
  `start_postcode` text COLLATE utf8_unicode_ci,
  `start_date` datetime DEFAULT NULL,
  `direction` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 - Any | 1 - Exact',
  `end_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 - Include | 1 - Exclude',
  `end_postcode` text COLLATE utf8_unicode_ci,
  `end_date` datetime DEFAULT NULL,
  `value` double(5,2) NOT NULL DEFAULT '0.00',
  `params` text COLLATE utf8_unicode_ci NOT NULL,
  `modified_date` datetime DEFAULT NULL,
  `ordering` int(10) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `[DB_PREFIX]location`
--

CREATE TABLE IF NOT EXISTS `[DB_PREFIX]location` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `profile_id` int(10) NOT NULL DEFAULT '0',
  `category_id` smallint(5) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `full_address` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `search_keywords` text COLLATE utf8_unicode_ci NOT NULL,
  `lat` float(10,6) NOT NULL DEFAULT '0.000000',
  `lon` float(10,6) NOT NULL DEFAULT '0.000000',
  `ordering` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `profile_id` (`profile_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7078 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `[DB_PREFIX]location`
--

INSERT INTO `[DB_PREFIX]location` (`id`, `profile_id`, `category_id`, `name`, `address`, `full_address`, `search_keywords`, `lat`, `lon`, `ordering`, `published`) VALUES
(1974, 1, 1, 'City Airport', 'E16 2PX', 'City Airport, E16 2PX', '', 0.000000, 0.000000, 0, 1),
(1975, 1, 1, 'Gatwick North Terminal', 'RH6 0PJ', 'Gatwick North, RH6 0PJ', 'LGW, London Gatwick', 51.161118, -0.177591, 0, 1),
(1976, 1, 1, 'Gatwick South Terminal', 'RH6 0NP', 'Gatwick South, RH6 0NP', 'LGW, London Gatwick', 51.155991, -0.163049, 0, 1),
(1977, 1, 1, 'Heathrow Terminal 1', 'TW6 1AP', 'Terminal 1, Hounslow, TW6 1AP', '', 51.472385, -0.450959, 0, 1),
(1978, 1, 1, 'Heathrow Terminal 2', 'TW6 1EW', 'Terminal 2, Hounslow, TW6 1EW', '', 51.469707, -0.451842, 0, 1),
(1979, 1, 1, 'Heathrow Terminal 3', 'TW6 1QG', 'Terminal 3, Hounslow, TW6 1QG', '', 51.470360, -0.458471, 0, 1),
(1980, 1, 1, 'Heathrow Terminal 4', 'TW6 3XA', 'Heathrow Terminal 4, TW6 3XA', '', 0.000000, 0.000000, 0, 1),
(1981, 1, 1, 'Heathrow Terminal 5', 'TW6 2GA', 'Heathrow Terminal 5, TW6 2GA', '', 0.000000, 0.000000, 0, 1),
(1982, 1, 1, 'Luton Ariport', 'LU2 9QT', 'Luton Ariport, LU2 9QT', '', 0.000000, 0.000000, 0, 1),
(1983, 1, 1, 'Stansted Airport', 'CM24 1QW', 'Stansted Airport, CM24 1QW', '', 0.000000, 0.000000, 0, 1),
(1984, 1, 6, 'CR0', 'CR0', 'CR0', '', 0.000000, 0.000000, 0, 1),
(1985, 1, 6, 'CR1', 'CR1', 'CR1', '', 0.000000, 0.000000, 0, 1),
(1986, 1, 6, 'CR2', 'CR2', 'CR2', '', 0.000000, 0.000000, 0, 1),
(1987, 1, 6, 'CR3', 'CR3', 'CR3', '', 0.000000, 0.000000, 0, 1),
(1988, 1, 6, 'CR4', 'CR4', 'CR4', '', 0.000000, 0.000000, 0, 1),
(1989, 1, 6, 'CR5', 'CR5', 'CR5', '', 0.000000, 0.000000, 0, 1),
(1990, 1, 6, 'CR6', 'CR6', 'CR6', '', 0.000000, 0.000000, 0, 1),
(1991, 1, 6, 'CR7', 'CR7', 'CR7', '', 0.000000, 0.000000, 0, 1),
(1992, 1, 6, 'CR8', 'CR8', 'CR8', '', 0.000000, 0.000000, 0, 1),
(1993, 1, 6, 'E1', 'E1', 'E1', '', 0.000000, 0.000000, 0, 1),
(1994, 1, 6, 'E2', 'E2', 'E2', '', 0.000000, 0.000000, 0, 1),
(1995, 1, 6, 'E3', 'E3', 'E3', '', 0.000000, 0.000000, 0, 1),
(1996, 1, 6, 'E4', 'E4', 'E4', '', 0.000000, 0.000000, 0, 1),
(1997, 1, 6, 'E5', 'E5', 'E5', '', 0.000000, 0.000000, 0, 1),
(1998, 1, 6, 'E6', 'E6', 'E6', '', 0.000000, 0.000000, 0, 1),
(1999, 1, 6, 'E7', 'E7', 'E7', '', 0.000000, 0.000000, 0, 1),
(2000, 1, 6, 'E8', 'E8', 'E8', '', 0.000000, 0.000000, 0, 1),
(2001, 1, 6, 'E9', 'E9', 'E9', '', 0.000000, 0.000000, 0, 1),
(2002, 1, 6, 'E10', 'E10', 'E10', '', 0.000000, 0.000000, 0, 1),
(2003, 1, 6, 'E11', 'E11', 'E11', '', 0.000000, 0.000000, 0, 1),
(2004, 1, 6, 'E12', 'E12', 'E12', '', 0.000000, 0.000000, 0, 1),
(2005, 1, 6, 'E13', 'E13', 'E13', '', 0.000000, 0.000000, 0, 1),
(2006, 1, 6, 'E14', 'E14', 'E14', '', 0.000000, 0.000000, 0, 1),
(2007, 1, 6, 'E15', 'E15', 'E15', '', 0.000000, 0.000000, 0, 1),
(2008, 1, 6, 'E16', 'E16', 'E16', '', 0.000000, 0.000000, 0, 1),
(2009, 1, 6, 'E17', 'E17', 'E17', '', 0.000000, 0.000000, 0, 1),
(2010, 1, 6, 'E18', 'E18', 'E18', '', 0.000000, 0.000000, 0, 1),
(2011, 1, 6, 'EC1', 'EC1', 'EC1', '', 0.000000, 0.000000, 0, 1),
(2012, 1, 6, 'EC2', 'EC2', 'EC2', '', 0.000000, 0.000000, 0, 1),
(2013, 1, 6, 'EC3', 'EC3', 'EC3', '', 0.000000, 0.000000, 0, 1),
(2014, 1, 6, 'EC4', 'EC4', 'EC4', '', 0.000000, 0.000000, 0, 1),
(2015, 1, 6, 'HA0', 'HA0', 'HA0', '', 0.000000, 0.000000, 0, 1),
(2016, 1, 6, 'HA1', 'HA1', 'HA1', '', 0.000000, 0.000000, 0, 1),
(2017, 1, 6, 'HA2', 'HA2', 'HA2', '', 0.000000, 0.000000, 0, 1),
(2018, 1, 6, 'HA3', 'HA3', 'HA3', '', 0.000000, 0.000000, 0, 1),
(2019, 1, 6, 'HA4', 'HA4', 'HA4', '', 0.000000, 0.000000, 0, 1),
(2020, 1, 6, 'HA5', 'HA5', 'HA5', '', 0.000000, 0.000000, 0, 1),
(2021, 1, 6, 'HA6', 'HA6', 'HA6', '', 0.000000, 0.000000, 0, 1),
(2022, 1, 6, 'HA7', 'HA7', 'HA7', '', 0.000000, 0.000000, 0, 1),
(2023, 1, 6, 'HA8', 'HA8', 'HA8', '', 0.000000, 0.000000, 0, 1),
(2024, 1, 6, 'HP1', 'HP1', 'HP1', '', 0.000000, 0.000000, 0, 1),
(2025, 1, 6, 'HP2', 'HP2', 'HP2', '', 0.000000, 0.000000, 0, 1),
(2026, 1, 6, 'HP3', 'HP3', 'HP3', '', 0.000000, 0.000000, 0, 1),
(2027, 1, 6, 'HP4', 'HP4', 'HP4', '', 0.000000, 0.000000, 0, 1),
(2028, 1, 6, 'HP5', 'HP5', 'HP5', '', 0.000000, 0.000000, 0, 1),
(2029, 1, 6, 'HP6', 'HP6', 'HP6', '', 0.000000, 0.000000, 0, 1),
(2030, 1, 6, 'HP7', 'HP7', 'HP7', '', 0.000000, 0.000000, 0, 1),
(2031, 1, 6, 'HP8', 'HP8', 'HP8', '', 0.000000, 0.000000, 0, 1),
(2032, 1, 6, 'HP9', 'HP9', 'HP9', '', 0.000000, 0.000000, 0, 1),
(2033, 1, 6, 'KT1', 'KT1', 'KT1', '', 0.000000, 0.000000, 0, 1),
(2034, 1, 6, 'KT2', 'KT2', 'KT2', '', 0.000000, 0.000000, 0, 1),
(2035, 1, 6, 'KT3', 'KT3', 'KT3', '', 0.000000, 0.000000, 0, 1),
(2036, 1, 6, 'KT4', 'KT4', 'KT4', '', 0.000000, 0.000000, 0, 1),
(2037, 1, 6, 'KT5', 'KT5', 'KT5', '', 0.000000, 0.000000, 0, 1),
(2038, 1, 6, 'KT6', 'KT6', 'KT6', '', 0.000000, 0.000000, 0, 1),
(2039, 1, 6, 'KT7', 'KT7', 'KT7', '', 0.000000, 0.000000, 0, 1),
(2040, 1, 6, 'KT8', 'KT8', 'KT8', '', 0.000000, 0.000000, 0, 1),
(2041, 1, 6, 'KT9', 'KT9', 'KT9', '', 0.000000, 0.000000, 0, 1),
(2042, 1, 6, 'KT10', 'KT10', 'KT10', '', 0.000000, 0.000000, 0, 1),
(2043, 1, 6, 'KT11', 'KT11', 'KT11', '', 0.000000, 0.000000, 0, 1),
(2044, 1, 6, 'KT12', 'KT12', 'KT12', '', 0.000000, 0.000000, 0, 1),
(2045, 1, 6, 'KT13', 'KT13', 'KT13', '', 0.000000, 0.000000, 0, 1),
(2046, 1, 6, 'KT14', 'KT14', 'KT14', '', 0.000000, 0.000000, 0, 1),
(2047, 1, 6, 'KT15', 'KT15', 'KT15', '', 0.000000, 0.000000, 0, 1),
(2048, 1, 6, 'KT16', 'KT16', 'KT16', '', 0.000000, 0.000000, 0, 1),
(2049, 1, 6, 'KT17', 'KT17', 'KT17', '', 0.000000, 0.000000, 0, 1),
(2050, 1, 6, 'KT18', 'KT18', 'KT18', '', 0.000000, 0.000000, 0, 1),
(2051, 1, 6, 'KT19', 'KT19', 'KT19', '', 0.000000, 0.000000, 0, 1),
(2052, 1, 6, 'KT20', 'KT20', 'KT20', '', 0.000000, 0.000000, 0, 1),
(2053, 1, 6, 'KT21', 'KT21', 'KT21', '', 0.000000, 0.000000, 0, 1),
(2054, 1, 6, 'KT22', 'KT22', 'KT22', '', 0.000000, 0.000000, 0, 1),
(2055, 1, 6, 'KT23', 'KT23', 'KT23', '', 0.000000, 0.000000, 0, 1),
(2056, 1, 6, 'KT24', 'KT24', 'KT24', '', 0.000000, 0.000000, 0, 1),
(2057, 1, 6, 'N1', 'N1', 'N1', '', 0.000000, 0.000000, 0, 1),
(2058, 1, 6, 'N2', 'N2', 'N2', '', 0.000000, 0.000000, 0, 1),
(2059, 1, 6, 'N3', 'N3', 'N3', '', 0.000000, 0.000000, 0, 1),
(2060, 1, 6, 'N4', 'N4', 'N4', '', 0.000000, 0.000000, 0, 1),
(2061, 1, 6, 'N5', 'N5', 'N5', '', 0.000000, 0.000000, 0, 1),
(2062, 1, 6, 'N6', 'N6', 'N6', '', 0.000000, 0.000000, 0, 1),
(2063, 1, 6, 'N7', 'N7', 'N7', '', 0.000000, 0.000000, 0, 1),
(2064, 1, 6, 'N8', 'N8', 'N8', '', 0.000000, 0.000000, 0, 1),
(2065, 1, 6, 'N9', 'N9', 'N9', '', 0.000000, 0.000000, 0, 1),
(2066, 1, 6, 'N10', 'N10', 'N10', '', 0.000000, 0.000000, 0, 1),
(2067, 1, 6, 'N11', 'N11', 'N11', '', 0.000000, 0.000000, 0, 1),
(2068, 1, 6, 'N12', 'N12', 'N12', '', 0.000000, 0.000000, 0, 1),
(2069, 1, 6, 'N13', 'N13', 'N13', '', 0.000000, 0.000000, 0, 1),
(2070, 1, 6, 'N14', 'N14', 'N14', '', 0.000000, 0.000000, 0, 1),
(2071, 1, 6, 'N15', 'N15', 'N15', '', 0.000000, 0.000000, 0, 1),
(2072, 1, 6, 'N16', 'N16', 'N16', '', 0.000000, 0.000000, 0, 1),
(2073, 1, 6, 'N17', 'N17', 'N17', '', 0.000000, 0.000000, 0, 1),
(2074, 1, 6, 'N18', 'N18', 'N18', '', 0.000000, 0.000000, 0, 1),
(2075, 1, 6, 'N19', 'N19', 'N19', '', 0.000000, 0.000000, 0, 1),
(2076, 1, 6, 'N20', 'N20', 'N20', '', 0.000000, 0.000000, 0, 1),
(2077, 1, 6, 'N21', 'N21', 'N21', '', 0.000000, 0.000000, 0, 1),
(2078, 1, 6, 'N22', 'N22', 'N22', '', 0.000000, 0.000000, 0, 1),
(2079, 1, 6, 'NW1', 'NW1', 'NW1', '', 0.000000, 0.000000, 0, 1),
(2080, 1, 6, 'NW2', 'NW2', 'NW2', '', 0.000000, 0.000000, 0, 1),
(2081, 1, 6, 'NW3', 'NW3', 'NW3', '', 0.000000, 0.000000, 0, 1),
(2082, 1, 6, 'NW4', 'NW4', 'NW4', '', 0.000000, 0.000000, 0, 1),
(2083, 1, 6, 'NW5', 'NW5', 'NW5', '', 0.000000, 0.000000, 0, 1),
(2084, 1, 6, 'NW6', 'NW6', 'NW6', '', 0.000000, 0.000000, 0, 1),
(2085, 1, 6, 'NW7', 'NW7', 'NW7', '', 0.000000, 0.000000, 0, 1),
(2086, 1, 6, 'NW8', 'NW8', 'NW8', '', 0.000000, 0.000000, 0, 1),
(2087, 1, 6, 'SE1', 'SE1', 'SE1', '', 0.000000, 0.000000, 0, 1),
(2088, 1, 6, 'SE2', 'SE2', 'SE2', '', 0.000000, 0.000000, 0, 1),
(2089, 1, 6, 'SE3', 'SE3', 'SE3', '', 0.000000, 0.000000, 0, 1),
(2090, 1, 6, 'SE4', 'SE4', 'SE4', '', 0.000000, 0.000000, 0, 1),
(2091, 1, 6, 'SE5', 'SE5', 'SE5', '', 0.000000, 0.000000, 0, 1),
(2092, 1, 6, 'SE6', 'SE6', 'SE6', '', 0.000000, 0.000000, 0, 1),
(2093, 1, 6, 'SE7', 'SE7', 'SE7', '', 0.000000, 0.000000, 0, 1),
(2094, 1, 6, 'SE8', 'SE8', 'SE8', '', 0.000000, 0.000000, 0, 1),
(2095, 1, 6, 'SE9', 'SE9', 'SE9', '', 0.000000, 0.000000, 0, 1),
(2096, 1, 6, 'SE10', 'SE10', 'SE10', '', 0.000000, 0.000000, 0, 1),
(2097, 1, 6, 'SE11', 'SE11', 'SE11', '', 0.000000, 0.000000, 0, 1),
(2098, 1, 6, 'SE12', 'SE12', 'SE12', '', 0.000000, 0.000000, 0, 1),
(2099, 1, 6, 'SE13', 'SE13', 'SE13', '', 0.000000, 0.000000, 0, 1),
(2100, 1, 6, 'SE14', 'SE14', 'SE14', '', 0.000000, 0.000000, 0, 1),
(2101, 1, 6, 'SE15', 'SE15', 'SE15', '', 0.000000, 0.000000, 0, 1),
(2102, 1, 6, 'SE16', 'SE16', 'SE16', '', 0.000000, 0.000000, 0, 1),
(2103, 1, 6, 'SE17', 'SE17', 'SE17', '', 0.000000, 0.000000, 0, 1),
(2104, 1, 6, 'SE18', 'SE18', 'SE18', '', 0.000000, 0.000000, 0, 1),
(2105, 1, 6, 'SE19', 'SE19', 'SE19', '', 0.000000, 0.000000, 0, 1),
(2106, 1, 6, 'SE20', 'SE20', 'SE20', '', 0.000000, 0.000000, 0, 1),
(2107, 1, 6, 'SE21', 'SE21', 'SE21', '', 0.000000, 0.000000, 0, 1),
(2108, 1, 6, 'SE22', 'SE22', 'SE22', '', 0.000000, 0.000000, 0, 1),
(2109, 1, 6, 'SE23', 'SE23', 'SE23', '', 0.000000, 0.000000, 0, 1),
(2110, 1, 6, 'SE24', 'SE24', 'SE24', '', 0.000000, 0.000000, 0, 1),
(2111, 1, 6, 'SE25', 'SE25', 'SE25', '', 0.000000, 0.000000, 0, 1),
(2112, 1, 6, 'SE26', 'SE26', 'SE26', '', 0.000000, 0.000000, 0, 1),
(2113, 1, 6, 'SE27', 'SE27', 'SE27', '', 0.000000, 0.000000, 0, 1),
(2114, 1, 6, 'SE28', 'SE28', 'SE28', '', 0.000000, 0.000000, 0, 1),
(2115, 1, 6, 'SL1', 'SL1', 'SL1', '', 0.000000, 0.000000, 0, 1),
(2116, 1, 6, 'SL2', 'SL2', 'SL2', '', 0.000000, 0.000000, 0, 1),
(2117, 1, 6, 'SL3', 'SL3', 'SL3', '', 0.000000, 0.000000, 0, 1),
(2118, 1, 6, 'SL4', 'SL4', 'SL4', '', 0.000000, 0.000000, 0, 1),
(2119, 1, 6, 'SL5', 'SL5', 'SL5', '', 0.000000, 0.000000, 0, 1),
(2120, 1, 6, 'SW1', 'SW1', 'SW1', '', 0.000000, 0.000000, 0, 1),
(2121, 1, 6, 'SW2', 'SW2', 'SW2', '', 0.000000, 0.000000, 0, 1),
(2122, 1, 6, 'SW3', 'SW3', 'SW3', '', 0.000000, 0.000000, 0, 1),
(2123, 1, 6, 'SW4', 'SW4', 'SW4', '', 0.000000, 0.000000, 0, 1),
(2124, 1, 6, 'SW5', 'SW5', 'SW5', '', 0.000000, 0.000000, 0, 1),
(2125, 1, 6, 'SW6', 'SW6', 'SW6', '', 0.000000, 0.000000, 0, 1),
(2126, 1, 6, 'SW7', 'SW7', 'SW7', '', 0.000000, 0.000000, 0, 1),
(2127, 1, 6, 'SW8', 'SW8', 'SW8', '', 0.000000, 0.000000, 0, 1),
(2128, 1, 6, 'SW9', 'SW9', 'SW9', '', 0.000000, 0.000000, 0, 1),
(2129, 1, 6, 'SW10', 'SW10', 'SW10', '', 0.000000, 0.000000, 0, 1),
(2130, 1, 6, 'SW11', 'SW11', 'SW11', '', 0.000000, 0.000000, 0, 1),
(2131, 1, 6, 'SW12', 'SW12', 'SW12', '', 0.000000, 0.000000, 0, 1),
(2132, 1, 6, 'SW13', 'SW13', 'SW13', '', 0.000000, 0.000000, 0, 1),
(2133, 1, 6, 'SW14', 'SW14', 'SW14', '', 0.000000, 0.000000, 0, 1),
(2134, 1, 6, 'SW15', 'SW15', 'SW15', '', 0.000000, 0.000000, 0, 1),
(2135, 1, 6, 'SW16', 'SW16', 'SW16', '', 0.000000, 0.000000, 0, 1),
(2136, 1, 6, 'SW17', 'SW17', 'SW17', '', 0.000000, 0.000000, 0, 1),
(2137, 1, 6, 'SW18', 'SW18', 'SW18', '', 0.000000, 0.000000, 0, 1),
(2138, 1, 6, 'SW19', 'SW19', 'SW19', '', 0.000000, 0.000000, 0, 1),
(2139, 1, 6, 'SW20', 'SW20', 'SW20', '', 0.000000, 0.000000, 0, 1),
(2140, 1, 6, 'TW1', 'TW1', 'TW1', '', 0.000000, 0.000000, 0, 1),
(2141, 1, 6, 'TW2', 'TW2', 'TW2', '', 0.000000, 0.000000, 0, 1),
(2142, 1, 6, 'TW3', 'TW3', 'TW3', '', 0.000000, 0.000000, 0, 1),
(2143, 1, 6, 'TW4', 'TW4', 'TW4', '', 0.000000, 0.000000, 0, 1),
(2144, 1, 6, 'TW5', 'TW5', 'TW5', '', 0.000000, 0.000000, 0, 1),
(2145, 1, 6, 'TW6', 'TW6', 'TW6', '', 0.000000, 0.000000, 0, 1),
(2146, 1, 6, 'TW7', 'TW7', 'TW7', '', 0.000000, 0.000000, 0, 1),
(2147, 1, 6, 'TW8', 'TW8', 'TW8', '', 0.000000, 0.000000, 0, 1),
(2148, 1, 6, 'TW9', 'TW9', 'TW9', '', 0.000000, 0.000000, 0, 1),
(2149, 1, 6, 'TW10', 'TW10', 'TW10', '', 0.000000, 0.000000, 0, 1),
(2150, 1, 6, 'TW11', 'TW11', 'TW11', '', 0.000000, 0.000000, 0, 1),
(2151, 1, 6, 'TW12', 'TW12', 'TW12', '', 0.000000, 0.000000, 0, 1),
(2152, 1, 6, 'TW13', 'TW13', 'TW13', '', 0.000000, 0.000000, 0, 1),
(2153, 1, 6, 'TW14', 'TW14', 'TW14', '', 0.000000, 0.000000, 0, 1),
(2154, 1, 6, 'TW15', 'TW15', 'TW15', '', 0.000000, 0.000000, 0, 1),
(2155, 1, 6, 'TW16', 'TW16', 'TW16', '', 0.000000, 0.000000, 0, 1),
(2156, 1, 6, 'TW17', 'TW17', 'TW17', '', 0.000000, 0.000000, 0, 1),
(2157, 1, 6, 'TW18', 'TW18', 'TW18', '', 0.000000, 0.000000, 0, 1),
(2158, 1, 6, 'TW19', 'TW19', 'TW19', '', 0.000000, 0.000000, 0, 1),
(2159, 1, 6, 'TW20', 'TW20', 'TW20', '', 0.000000, 0.000000, 0, 1),
(2160, 1, 6, 'UB1', 'UB1', 'UB1', '', 0.000000, 0.000000, 0, 1),
(2161, 1, 6, 'UB2', 'UB2', 'UB2', '', 0.000000, 0.000000, 0, 1),
(2162, 1, 6, 'UB3', 'UB3', 'UB3', '', 0.000000, 0.000000, 0, 1),
(2163, 1, 6, 'UB4', 'UB4', 'UB4', '', 0.000000, 0.000000, 0, 1),
(2164, 1, 6, 'UB5', 'UB5', 'UB5', '', 0.000000, 0.000000, 0, 1),
(2165, 1, 6, 'UB6', 'UB6', 'UB6', '', 0.000000, 0.000000, 0, 1),
(2166, 1, 6, 'UB7', 'UB7', 'UB7', '', 0.000000, 0.000000, 0, 1),
(2167, 1, 6, 'UB8', 'UB8', 'UB8', '', 0.000000, 0.000000, 0, 1),
(2168, 1, 6, 'UB9', 'UB9', 'UB9', '', 0.000000, 0.000000, 0, 1),
(2169, 1, 6, 'UB10', 'UB10', 'UB10', '', 0.000000, 0.000000, 0, 1),
(2170, 1, 6, 'W1', 'W1', 'W1', '', 0.000000, 0.000000, 0, 1),
(2171, 1, 6, 'W2', 'W2', 'W2', '', 0.000000, 0.000000, 0, 1),
(2172, 1, 6, 'W3', 'W3', 'W3', '', 0.000000, 0.000000, 0, 1),
(2173, 1, 6, 'W4', 'W4', 'W4', '', 0.000000, 0.000000, 0, 1),
(2174, 1, 6, 'W5', 'W5', 'W5', '', 0.000000, 0.000000, 0, 1),
(2175, 1, 6, 'W6', 'W6', 'W6', '', 0.000000, 0.000000, 0, 1),
(2176, 1, 6, 'W7', 'W7', 'W7', '', 0.000000, 0.000000, 0, 1),
(2177, 1, 6, 'W8', 'W8', 'W8', '', 0.000000, 0.000000, 0, 1),
(2178, 1, 6, 'W9', 'W9', 'W9', '', 0.000000, 0.000000, 0, 1),
(2179, 1, 6, 'W10', 'W10', 'W10', '', 0.000000, 0.000000, 0, 1),
(2180, 1, 6, 'W11', 'W11', 'W11', '', 0.000000, 0.000000, 0, 1),
(2181, 1, 6, 'W12', 'W12', 'W12', '', 0.000000, 0.000000, 0, 1),
(2182, 1, 6, 'W13', 'W13', 'W13', '', 0.000000, 0.000000, 0, 1),
(2183, 1, 6, 'W14', 'W14', 'W14', '', 0.000000, 0.000000, 0, 1),
(2184, 1, 6, 'WC1', 'WC1', 'WC1', '', 0.000000, 0.000000, 0, 1),
(2185, 1, 6, 'WC2', 'WC2', 'WC2', '', 0.000000, 0.000000, 0, 1),
(2186, 1, 2, 'Dover Cruise Port', 'CT17 9EQ', 'Dover Cruise Port, CT17 9EQ', '', 0.000000, 0.000000, 0, 1),
(2187, 1, 2, 'Portsmouth Cruise Port', 'PO2 8SP', 'Portsmouth Cruise Port, PO2 8SP', '', 0.000000, 0.000000, 0, 1),
(2188, 1, 2, 'Southampton Cruise Port', 'SO15 1BS', 'Southampton Cruise Port, SO15 1BS', '', 0.000000, 0.000000, 0, 1),
(2189, 1, 2, 'Harwich International Cruise Port', 'CO12 4SR', 'Harwich International Cruise Port, CO12 4SR', '', 0.000000, 0.000000, 0, 1),
(2190, 1, 1, 'Birmingham International  Airport', 'B26 3QJ', 'Birmingham International  Airport, B26 3QJ', '', 0.000000, 0.000000, 0, 1),
(2191, 1, 1, 'Southampton Airport', 'SO18 2NL', 'Southampton Airport, SO18 2NL', '', 0.000000, 0.000000, 0, 1),
(2192, 1, 1, 'Bristol Airport', 'BS48 3DY', 'Bristol Airport, BS48 3DY', '', 0.000000, 0.000000, 0, 1),
(2193, 1, 1, 'Southend Airport', 'SS2 6YF', 'Southend Airport, SS2 6YF', '', 0.000000, 0.000000, 0, 1),
(2194, 1, 1, 'Farnborough Airport', 'GU14 6XA', 'Farnborough Airport, GU14 6XA', '', 0.000000, 0.000000, 0, 1),
(2195, 1, 1, 'Biggin Hill Airport', 'TN16 3BH', 'Biggin Hill Airport, TN16 3BH', '', 0.000000, 0.000000, 0, 1),
(2196, 1, 1, 'Ruislip Military Airport', 'HA4 6NG', 'Ruislip Military Airport, HA4 6NG', '', 0.000000, 0.000000, 0, 1),
(2197, 1, 1, 'Denham Aerodrome', 'UB9 5DF', 'Denham Aerodrome, UB9 5DF', '', 0.000000, 0.000000, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `[DB_PREFIX]meeting_point`
--

CREATE TABLE IF NOT EXISTS `[DB_PREFIX]meeting_point` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `profile_id` int(11) NOT NULL DEFAULT '0',
  `postcode` text COLLATE utf8_unicode_ci NOT NULL,
  `meet_and_greet` tinyint(1) NOT NULL DEFAULT '0',
  `airport` tinyint(1) NOT NULL DEFAULT '0',
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `note` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `modified_date` datetime DEFAULT NULL,
  `ordering` int(10) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=105 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `[DB_PREFIX]meeting_point`
--

INSERT INTO `[DB_PREFIX]meeting_point` (`id`, `profile_id`, `postcode`, `meet_and_greet`, `airport`, `description`, `note`, `modified_date`, `ordering`, `published`) VALUES
(2, 1, 'RH6 0NP,RH6 0PJ,RH6 0RN', 1, 1, 'When you enter Arrivals your driver will be standing in the centre with your name. If you can not locate him please call your driver on the number.', 'Gatwick North & South', '2016-09-03 13:22:46', 1, 0),
(3, 1, 'RH6 0PJ,RH6 0RN', 0, 1, 'Please call your driver on the supplied telephone number when you have landed. On arrival at the North Terminal remain on the same level. Go outside of the airport and make towards the Sofitel Hotel or Premier Inn. Your driver will meet you here.', 'Gatwick North', '2016-09-02 15:39:29', 2, 0),
(4, 1, 'RH6 0NP', 0, 1, 'Please call your driver on the supplied telephone number when you have landed. On arrival at the South Terminal go to the SHORT STAY ORANGE CAR PARK, LEVEL 0. Driver will wait there.', 'Gatwick South', '2016-09-02 15:39:36', 3, 0),
(6, 1, 'ALL', 0, 1, 'Arrivals hall', 'All other airports', '2016-09-03 16:09:26', 4, 0);

-- --------------------------------------------------------

--
-- Table structure for table `[DB_PREFIX]migrations`
--

CREATE TABLE IF NOT EXISTS `[DB_PREFIX]migrations` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `[DB_PREFIX]migrations`
--

INSERT INTO `[DB_PREFIX]migrations` (`id`, `migration`, `batch`) VALUES
(16, '2017_03_09_000000_create_users_table', 6),
(17, '2017_03_09_100000_create_password_resets_table', 6),
(18, '2017_03_09_123013_create_sessions_table', 6),
(19, '2017_03_09_124415_create_cache_table', 6),
(20, '2017_03_12_080000_v3_0', 6),
(21, '2017_03_20_090000_v3_2', 6),
(22, '2017_04_21_194415_create_bases_table', 6),
(23, '2017_04_21_194515_create_services_table', 6),
(24, '2017_05_01_114515_create_events_table', 6),
(25, '2017_05_02_114515_create_transactions_table', 6),
(26, '2017_07_12_080000_v3_3', 6),
(27, '2017_03_19_000000_create_profiles_table', 7),
(28, '2017_03_19_100000_create_vehicles_table', 7),
(29, '2017_03_08_000000_create_migrations_table', 8),
(33, '2017_08_30_140000_v3_3_12', 9),
(34, '2017_09_07_140000_v3_3_13', 10),
(35, '2017_09_09_173957_create_notifications_table', 10),
(52, '2017_10_15_140000_v3_3_17', 11),
(54, '2017_11_21_140000_v3_3_24', 12),
(55, '2017_11_27_140000_v3_3_27', 13),
(64, '2017_12_13_140000_v3_3_30', 14),
(65, '2017_12_13_140000_v3_3_33', 15),
(67, '2018_01_12_140000_v3_3_36', 16),
(68, '2018_02_01_140000_v3_3_39', 17),
(75, '2018_02_18_140000_v3_3_44', 18);

-- --------------------------------------------------------

--
-- Table structure for table `[DB_PREFIX]notifications`
--

CREATE TABLE IF NOT EXISTS `[DB_PREFIX]notifications` (
  `id` char(36) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `notifiable_id` int(10) UNSIGNED NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `data` text COLLATE utf8_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_id_notifiable_type_index` (`notifiable_id`,`notifiable_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `[DB_PREFIX]password_resets`
--

CREATE TABLE IF NOT EXISTS `[DB_PREFIX]password_resets` (
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `easytaxioffice_password_resets_email_index` (`email`),
  KEY `easytaxioffice_password_resets_token_index` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Table structure for table `[DB_PREFIX]payment`
--

CREATE TABLE IF NOT EXISTS `[DB_PREFIX]payment` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `profile_id` smallint(5) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `payment_page` text COLLATE utf8_unicode_ci NOT NULL,
  `image` text COLLATE utf8_unicode_ci NOT NULL,
  `params` text COLLATE utf8_unicode_ci NOT NULL,
  `method` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `factor_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 - Flat | 1 - Percent',
  `price` double(5,2) NOT NULL DEFAULT '0.00',
  `default` tinyint(1) NOT NULL DEFAULT '0',
  `ordering` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `is_backend` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `profile_id` (`profile_id`)
) ENGINE=MyISAM AUTO_INCREMENT=340 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `[DB_PREFIX]payment`
--

INSERT INTO `[DB_PREFIX]payment` (`id`, `profile_id`, `name`, `description`, `payment_page`, `image`, `params`, `method`, `factor_type`, `price`, `default`, `ordering`, `published`, `is_backend`) VALUES
(1, 1, 'Cash', 'Cash', '', 'cash.png', '{}', 'cash', 1, 0.00, 1, 1, 1, 0),
(3, 1, 'Barclaycard', 'Credit or Debit Card', 'Pay by Credit or Debit Card via Barclaycard\r\nYou will be passed over to secure Barclaycard payment pages to complete your booking.\r\nClick the button below to proceed with your booking.', 'creditcards.png', '{\"pspid\":\"\",\"pass_phrase\":\"\",\"paramvar\":\"\",\"operation_mode\":\"SAL\",\"currency_code\":\"GBP\",\"language_code\":\"auto\",\"test_mode\":\"0\",\"test_amount\":\"0.00\",\"deposit\":\"1\"}', 'epdq', 1, 0.00, 0, 4, 0, 0),
(154, 1, 'Cardsave', 'Credit or Debit Card', 'Pay by Credit or Debit Card via Cardsave\r\nYou will be passed over to secure Worldpay payment pages to complete your booking.\r\nClick the button below to proceed with your booking.', 'cardsave.png', '{\"pre_shared_key\":\"\",\"merchant_id\":\"\",\"password\":\"\",\"operation_mode\":\"SALE\",\"country_code\":\"826\",\"currency_code\":\"826\",\"language_code\":\"auto\",\"test_mode\":\"0\",\"test_amount\":\"0.00\",\"deposit\":\"1\"}', 'cardsave', 1, 0.00, 0, 8, 0, 0),
(153, 1, 'PayPal', 'PayPal', 'Pay with PayPal\r\nYou will be passed over to secure PayPal payment pages to complete your booking.\r\nClick the button below to proceed with your booking.', 'paypal.png', '{\"paypal_email\":\"[APP_EMAIL]\",\"currency_code\":\"GBP\",\"language_code\":\"auto\",\"test_mode\":\"0\",\"test_amount\":\"0.00\",\"deposit\":\"1\"}', 'paypal', 1, 0.00, 0, 2, 1, 0),
(185, 1, 'Redsys', 'Credit or Debit Card', 'Pay by Credit or Debit Card via RedSys\r\nYou will be passed over to secure RedSys payment pages to complete your booking.\r\nClick the button below to proceed with your booking.', 'redsys.png', '{\"merchant_id\":\"\",\"terminal_id\":\"001\",\"encryption_key\":\"\",\"signature_version\":\"HMAC_SHA256_V1\",\"operation_mode\":\"0\",\"currency_code\":\"978\",\"language_code\":\"auto\",\"test_mode\":\"0\",\"test_amount\":\"1.00\",\"deposit\":\"1\"}', 'redsys', 1, 0.00, 0, 5, 0, 0),
(192, 1, 'Stripe', 'Credit or Debit Card', 'Pay by Credit or Debit Card via Stripe\r\nYou will be passed over to secure Stripe payment pages to complete your booking.\r\nClick the button below to proceed with your booking.', 'stripe.png', '{\"pk_live\":\"\",\"sk_live\":\"\",\"pk_test\":\"\",\"sk_test\":\"\",\"sca_mode\":\"1\",\"zip_code\":\"true\",\"three_d_secure\":\"false\",\"currency_code\":\"GBP\",\"language_code\":\"auto\",\"test_mode\":\"0\",\"test_amount\":\"0.00\",\"deposit\":\"1\"}', 'stripe', 1, 0.00, 0, 3, 0, 0),
(191, 1, 'Worldpay', 'Credit or Debit Card', 'Pay by Credit or Debit Card via Worldpay\r\nYou will be passed over to secure Worldpay payment pages to complete your booking.\r\nClick the button below to proceed with your booking.', 'worldpay.png', '{\"inst_id\":\"\",\"md5_secret\":\"\",\"signature_fields\":\"instId:amount:currency:cartId\",\"currency_code\":\"GBP\",\"language_code\":\"auto\",\"test_mode\":\"0\",\"test_amount\":\"0.00\",\"deposit\":\"1\"}', 'worldpay', 1, 0.00, 0, 6, 0, 0),
(242, 1, 'Account', 'Reserve', '', 'account.png', '{}', 'account', 1, 0.00, 0, 9, 0, 0),
(244, 1, 'Payzone', 'Credit or Debit Card', 'Pay by Credit or Debit Card via Payzone\r\nYou will be passed over to secure Payzone payment page to complete your booking.\r\nClick the button below to proceed with your booking.', 'payzone.png', '{\"pre_shared_key\":\"\",\"merchant_id\":\"\",\"password\":\"\",\"operation_mode\":\"SALE\",\"country_code\":\"826\",\"currency_code\":\"826\",\"language_code\":\"auto\",\"test_mode\":\"0\",\"test_amount\":\"0.00\",\"deposit\":\"1\"}', 'payzone', 1, 0.00, 0, 7, 0, 0),
(245, 1, 'BACS', 'BACS - Bank transfer', '', 'bacs.png', '{}', 'bacs', 1, 0.00, 0, 10, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `[DB_PREFIX]profile`
--

CREATE TABLE IF NOT EXISTS `[DB_PREFIX]profile` (
  `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `domain` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `license_key` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `default` tinyint(1) NOT NULL DEFAULT '0',
  `ordering` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=62 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `[DB_PREFIX]profile`
--

INSERT INTO `[DB_PREFIX]profile` (`id`, `name`, `description`, `domain`, `key`, `license_key`, `default`, `ordering`, `published`) VALUES
(1, '[APP_NAME]', '', '', '[APP_KEY]', '', 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `[DB_PREFIX]profiles`
--

CREATE TABLE IF NOT EXISTS `[DB_PREFIX]profiles` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL DEFAULT '0',
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `first_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `mobile_no` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `telephone_no` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `emergency_no` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `postcode` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `profile_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `company_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `company_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `company_tax_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `national_insurance_no` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bank_account` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `unique_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `commission` double(8,2) NOT NULL DEFAULT '0.00',
  `availability` text COLLATE utf8_unicode_ci,
  `insurance` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `insurance_expiry_date` datetime DEFAULT NULL,
  `driving_licence` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `driving_licence_expiry_date` date DEFAULT NULL,
  `pco_licence` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pco_licence_expiry_date` date DEFAULT NULL,
  `phv_licence` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phv_licence_expiry_date` date DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=68 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `[DB_PREFIX]profiles`
--

INSERT INTO `[DB_PREFIX]profiles` (`id`, `user_id`, `title`, `first_name`, `last_name`, `date_of_birth`, `mobile_no`, `telephone_no`, `emergency_no`, `address`, `city`, `postcode`, `state`, `country`, `profile_type`, `company_name`, `company_number`, `company_tax_number`, `national_insurance_no`, `bank_account`, `unique_id`, `commission`, `availability`, `insurance`, `insurance_expiry_date`, `driving_licence`, `driving_licence_expiry_date`, `pco_licence`, `pco_licence_expiry_date`, `phv_licence`, `phv_licence_expiry_date`, `description`, `created_at`, `updated_at`) VALUES
(17, 1, '', '', '', NULL, '', '', '', '', '', '', '', '', 'private', NULL, NULL, NULL, '', '', '', 0.00, '[]', '', NULL, '', NULL, '', NULL, '', NULL, '', '2017-01-17 23:23:41', '2018-02-20 21:53:21');

-- --------------------------------------------------------

--
-- Table structure for table `[DB_PREFIX]services`
--

CREATE TABLE IF NOT EXISTS `[DB_PREFIX]services` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `site_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `factor_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'addition',
  `factor_value` double(8,2) NOT NULL DEFAULT '0.00',
  `duration` tinyint(4) NOT NULL DEFAULT '0',
  `duration_min` smallint(6) DEFAULT '0',
  `duration_max` smallint(6) NOT NULL DEFAULT '0',
  `hide_location` tinyint(4) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `selected` tinyint(4) NOT NULL DEFAULT '0',
  `status` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `site_id` (`site_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `[DB_PREFIX]sessions`
--

CREATE TABLE IF NOT EXISTS `[DB_PREFIX]sessions` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8_unicode_ci,
  `payload` text COLLATE utf8_unicode_ci NOT NULL,
  `last_activity` int(11) NOT NULL,
  UNIQUE KEY `easytaxioffice_sessions_id_unique` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `[DB_PREFIX]transactions`
--

CREATE TABLE IF NOT EXISTS `[DB_PREFIX]transactions` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ref_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ref_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `unique_key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `payment_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `payment_method` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `payment_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `payment_charge` double(8,2) NOT NULL DEFAULT '0.00',
  `currency_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `amount` double(8,2) NOT NULL DEFAULT '0.00',
  `ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `response` text COLLATE utf8_unicode_ci,
  `status` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `requested_at` datetime DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ref_id` (`ref_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `[DB_PREFIX]user`
--

CREATE TABLE IF NOT EXISTS `[DB_PREFIX]user` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `profile_id` tinyint(5) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '1 - Customer | 2 - Driver',
  `ip` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `token_password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `token_activation` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `is_company` tinyint(4) NOT NULL DEFAULT '0',
  `last_visit_date` datetime DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `activated` tinyint(1) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `[DB_PREFIX]users`
--

CREATE TABLE IF NOT EXISTS `[DB_PREFIX]users` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `role` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'customer',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `avatar` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `push_token` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lat` double(10,6) DEFAULT NULL,
  `lng` double(10,6) DEFAULT NULL,
  `accuracy` double(8,2) DEFAULT NULL,
  `heading` double(8,2) DEFAULT NULL,
  `last_seen_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `easytaxioffice_users_email_unique` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=93 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `[DB_PREFIX]users`
--

INSERT INTO `[DB_PREFIX]users` (`id`, `role`, `name`, `username`, `email`, `avatar`, `password`, `remember_token`, `push_token`, `status`, `lat`, `lng`, `accuracy`, `heading`, `last_seen_at`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'Admin', 'admin', '[APP_EMAIL]', NULL, '[APP_PASSWORD]', '', NULL, 'approved', NULL, NULL, NULL, NULL, '2018-02-20 21:54:15', '2016-10-09 13:13:21', '2018-02-20 21:54:15');

-- --------------------------------------------------------

--
-- Table structure for table `[DB_PREFIX]user_customer`
--

CREATE TABLE IF NOT EXISTS `[DB_PREFIX]user_customer` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL DEFAULT '0',
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `first_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `mobile_number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `telephone_number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `emergency_number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `company_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `company_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `company_tax_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `city` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `postcode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `state` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `country` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `[DB_PREFIX]vehicle`
--

CREATE TABLE IF NOT EXISTS `[DB_PREFIX]vehicle` (
  `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `profile_id` smallint(5) NOT NULL DEFAULT '0',
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `service_ids` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `hourly_rate` double(8,2) NOT NULL DEFAULT '0.00',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `disable_info` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `max_amount` smallint(5) NOT NULL DEFAULT '0',
  `passengers` smallint(5) NOT NULL DEFAULT '0',
  `luggage` smallint(5) NOT NULL DEFAULT '0',
  `hand_luggage` smallint(5) NOT NULL DEFAULT '0',
  `child_seats` smallint(5) NOT NULL DEFAULT '0',
  `baby_seats` smallint(5) NOT NULL DEFAULT '0',
  `infant_seats` smallint(6) NOT NULL DEFAULT '0',
  `wheelchair` smallint(6) NOT NULL DEFAULT '0',
  `factor_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 - Flat | 1 - Multiply',
  `price` double(5,2) NOT NULL DEFAULT '0.00',
  `default` tinyint(1) NOT NULL DEFAULT '0',
  `ordering` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `is_backend` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `profile_id` (`profile_id`)
) ENGINE=MyISAM AUTO_INCREMENT=205 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `[DB_PREFIX]vehicle`
--

INSERT INTO `[DB_PREFIX]vehicle` (`id`, `profile_id`, `user_id`, `service_ids`, `hourly_rate`, `name`, `description`, `disable_info`, `image`, `max_amount`, `passengers`, `luggage`, `hand_luggage`, `child_seats`, `baby_seats`, `infant_seats`, `wheelchair`, `factor_type`, `price`, `default`, `ordering`, `published`, `is_backend`) VALUES
(1, 1, 0, NULL, 0.00, 'Saloon', '', '', 'vehicle_type1515618352.png', 1, 4, 2, 2, 1, 1, 1, 0, 0, 0.00, 1, 1, 1, 0),
(2, 1, 0, NULL, 0.00, 'MPV', '', '', 'vehicle_type1515618388.png', 1, 5, 4, 4, 1, 1, 1, 0, 0, 0.00, 0, 4, 1, 0),
(4, 1, 0, NULL, 0.00, '8 Seater', '', '', 'vehicle_type1515618396.png', 1, 8, 8, 10, 1, 1, 1, 0, 0, 0.00, 0, 5, 1, 0),
(5, 1, 0, NULL, 0.00, 'Executive', '', '', 'vehicle_type1515618380.png', 1, 3, 2, 2, 1, 1, 1, 0, 0, 0.00, 0, 3, 1, 0),
(13, 1, 0, NULL, 0.00, 'Estate', '', '', 'vehicle_type1515618369.png', 1, 4, 4, 4, 1, 1, 1, 0, 0, 0.00, 0, 2, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `[DB_PREFIX]vehicles`
--

CREATE TABLE IF NOT EXISTS `[DB_PREFIX]vehicles` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `registration_mark` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mot` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mot_expiry_date` datetime DEFAULT NULL,
  `make` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `model` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `colour` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `body_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `no_of_passengers` int(10) NOT NULL DEFAULT '0',
  `registered_keeper_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `registered_keeper_address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `status` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `selected` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
