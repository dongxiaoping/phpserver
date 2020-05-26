/*
 Navicat Premium Data Transfer

 Source Server         : Rool阿里云服务器
 Source Server Type    : MySQL
 Source Server Version : 50564
 Source Host           : 120.26.52.88:3306
 Source Schema         : roll_race

 Target Server Type    : MySQL
 Target Server Version : 50564
 File Encoding         : 65001

 Date: 26/05/2020 11:14:08
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for bet_record
-- ----------------------------
DROP TABLE IF EXISTS `bet_record`;
CREATE TABLE `bet_record`  (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `roomId` int(12) NULL DEFAULT NULL,
  `raceNum` int(2) NULL DEFAULT NULL,
  `landCorner` int(5) NOT NULL DEFAULT 0,
  `bridg` int(5) NOT NULL DEFAULT 0,
  `skyCorner` int(5) NOT NULL DEFAULT 0,
  `middle` int(5) NOT NULL DEFAULT 0,
  `land` int(5) NOT NULL DEFAULT 0,
  `sky` int(5) NOT NULL DEFAULT 0,
  `userId` int(12) NULL DEFAULT NULL,
  `creatTime` datetime NULL DEFAULT NULL,
  `modTime` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2277 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for cost_record
-- ----------------------------
DROP TABLE IF EXISTS `cost_record`;
CREATE TABLE `cost_record`  (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `userId` int(12) NOT NULL,
  `roomId` int(12) NOT NULL,
  `cost` int(5) NOT NULL,
  `modTime` datetime NULL,
  `creatTime` datetime NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 612 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Fixed;

-- ----------------------------
-- Table structure for race
-- ----------------------------
DROP TABLE IF EXISTS `race`;
CREATE TABLE `race`  (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `roomId` int(12) NULL DEFAULT NULL,
  `raceNum` int(2) NULL DEFAULT NULL,
  `playState` int(2) NULL DEFAULT NULL,
  `landlordScore` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `skyScore` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `middleScore` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `landScore` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `points` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `landlordId` int(12) NULL DEFAULT NULL,
  `creatTime` datetime NULL DEFAULT NULL,
  `modTime` datetime NULL DEFAULT NULL,
  `landResult` varchar(8) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `middleResult` varchar(8) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `bridgResult` varchar(8) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `landCornerResult` varchar(8) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `skyCornerResult` varchar(8) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `skyResult` varchar(8) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 18375 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for recharge_record
-- ----------------------------
DROP TABLE IF EXISTS `recharge_record`;
CREATE TABLE `recharge_record`  (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `userId` int(12) NOT NULL,
  `cost` int(5) NOT NULL,
  `platform` int(2) NOT NULL,
  `modTime` datetime NULL,
  `creatTime` datetime NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1562 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Fixed;

-- ----------------------------
-- Table structure for room
-- ----------------------------
DROP TABLE IF EXISTS `room`;
CREATE TABLE `room`  (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `creatUserId` int(12) NULL DEFAULT NULL,
  `memberLimit` int(3) NULL DEFAULT NULL,
  `playCount` int(3) NULL DEFAULT NULL,
  `roomState` int(2) NULL DEFAULT NULL,
  `roomFee` float(4, 2) NULL DEFAULT NULL,
  `roomPay` int(1) NULL DEFAULT NULL,
  `playMode` int(2) NULL DEFAULT NULL,
  `costLimit` int(3) NULL DEFAULT NULL,
  `oningRaceNum` int(2) NULL DEFAULT NULL,
  `creatTime` datetime NULL DEFAULT NULL,
  `modTime` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1499 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for room_player
-- ----------------------------
DROP TABLE IF EXISTS `room_player`;
CREATE TABLE `room_player`  (
  `id` bigint(12) NOT NULL AUTO_INCREMENT,
  `nick` varchar(80) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `userId` bigint(12) NULL DEFAULT NULL,
  `icon` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `roomId` bigint(12) NULL DEFAULT NULL,
  `modTime` datetime NULL DEFAULT NULL,
  `creatTime` datetime NULL DEFAULT NULL,
  `roleType` tinyint(2) NULL DEFAULT NULL,
  `score` int(8) NULL DEFAULT NULL,
  `state` tinyint(2) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1102 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user`  (
  `id` bigint(12) NOT NULL AUTO_INCREMENT,
  `icon` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `nick` varchar(80) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `score` int(8) NULL DEFAULT NULL COMMENT '暂时不用',
  `diamond` int(8) NULL DEFAULT NULL,
  `password` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `phone` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `type` tinyint(2) NULL DEFAULT NULL COMMENT '1作弊用户， 0普通用户',
  `creatTime` datetime NULL DEFAULT NULL,
  `modTime` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3440 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

SET FOREIGN_KEY_CHECKS = 1;
