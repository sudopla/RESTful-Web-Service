/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50553
Source Host           : localhost:3306
Source Database       : eg1stfpw

Target Server Type    : MYSQL
Target Server Version : 50553
File Encoding         : 65001

Date: 2016-11-05 00:26:14
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `Counter`
-- ----------------------------
DROP TABLE IF EXISTS `Counter`;
CREATE TABLE `Counter` (
  `id_play` int(11) NOT NULL DEFAULT '0',
  `intents` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id_play`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of Counter
-- ----------------------------

-- ----------------------------
-- Table structure for `Request_Play`
-- ----------------------------
DROP TABLE IF EXISTS `Request_Play`;
CREATE TABLE `Request_Play` (
  `id_request` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `request_user_id` int(11) DEFAULT NULL,
  `type_game` tinyint(4) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `id_play` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_request`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of Request_Play
-- ----------------------------

-- ----------------------------
-- Table structure for `Score_2_minut`
-- ----------------------------
DROP TABLE IF EXISTS `Score_2_minut`;
CREATE TABLE `Score_2_minut` (
  `id_play` int(11) NOT NULL AUTO_INCREMENT,
  `id_user_1` int(11) DEFAULT NULL,
  `id_user_2` int(11) DEFAULT NULL,
  `score` int(11) DEFAULT '0',
  `date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id_play`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of Score_2_minut
-- ----------------------------

-- ----------------------------
-- Table structure for `Score_no_time`
-- ----------------------------
DROP TABLE IF EXISTS `Score_no_time`;
CREATE TABLE `Score_no_time` (
  `id_play` int(11) NOT NULL AUTO_INCREMENT,
  `id_user_1` int(11) DEFAULT NULL,
  `id_user_2` int(11) DEFAULT NULL,
  `score` int(11) DEFAULT '0',
  `date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id_play`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of Score_no_time
-- ----------------------------

-- ----------------------------
-- Table structure for `Sessions`
-- ----------------------------
DROP TABLE IF EXISTS `Sessions`;
CREATE TABLE `Sessions` (
  `id_session` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `imei` varchar(40) NOT NULL,
  `time_stamp` date NOT NULL,
  PRIMARY KEY (`id_session`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of Sessions
-- ----------------------------

-- ----------------------------
-- Table structure for `Users`
-- ----------------------------
DROP TABLE IF EXISTS `Users`;
CREATE TABLE `Users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `nickname` varchar(32) DEFAULT NULL,
  `password` varchar(60) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `state` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of Users
-- ----------------------------

-- ----------------------------
-- Table structure for `Vector_Temp`
-- ----------------------------
DROP TABLE IF EXISTS `Vector_Temp`;
CREATE TABLE `Vector_Temp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_play` int(11) DEFAULT NULL,
  `num_vector` int(11) DEFAULT NULL,
  `vector` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of Vector_Temp
-- ----------------------------
