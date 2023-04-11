/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 80030
 Source Host           : localhost:3306
 Source Schema         : lightpic

 Target Server Type    : MySQL
 Target Server Version : 80030
 File Encoding         : 65001

 Date: 11/04/2023 17:25:27
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for osuu_code
-- ----------------------------
DROP TABLE IF EXISTS `osuu_code`;
CREATE TABLE `osuu_code`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  `ip` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  `create_time` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 18 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of osuu_code
-- ----------------------------

-- ----------------------------
-- Table structure for osuu_images
-- ----------------------------
DROP TABLE IF EXISTS `osuu_images`;
CREATE TABLE `osuu_images`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户ID',
  `storage_id` int UNSIGNED NOT NULL COMMENT '存储桶ID',
  `name` char(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '图片名称',
  `size` decimal(12, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '图片大小(字节：b)',
  `hash` char(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '文件哈希',
  `mime` char(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '文件MIME类型',
  `path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '文件路径',
  `url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT 'url路径',
  `ip` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '上传者IP',
  `create_time` int NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1314 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '图片表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of osuu_images
-- ----------------------------

-- ----------------------------
-- Table structure for osuu_log
-- ----------------------------
DROP TABLE IF EXISTS `osuu_log`;
CREATE TABLE `osuu_log`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL COMMENT '用户id',
  `type` int NULL DEFAULT 2,
  `content` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT '操作内容',
  `operate_id` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  `operate_cont` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  `create_time` int NOT NULL COMMENT '时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 524 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of osuu_log
-- ----------------------------

-- ----------------------------
-- Table structure for osuu_role
-- ----------------------------
DROP TABLE IF EXISTS `osuu_role`;
CREATE TABLE `osuu_role`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `storage_id` int NOT NULL COMMENT '存储桶ID',
  `name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '组名称',
  `is_add` int NOT NULL DEFAULT 0 COMMENT '上传权限',
  `is_del_own` int NOT NULL DEFAULT 0 COMMENT '删除自己上传的图片',
  `is_read` int NOT NULL COMMENT '查看所在存储桶其他人上传的图片',
  `is_del_all` int NOT NULL DEFAULT 0 COMMENT '删除所在存储桶其他人上传的图片',
  `is_read_all` int NOT NULL COMMENT '查看系统全部图片',
  `is_admin` int NOT NULL COMMENT '管理员权限',
  `default` tinyint(1) NOT NULL DEFAULT 0 COMMENT '默认',
  `update_time` int NULL DEFAULT NULL COMMENT '更新时间',
  `create_time` int NULL DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 32 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '文件夹表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of osuu_role
-- ----------------------------
INSERT INTO `osuu_role` VALUES (1, 1000, '超级管理员', 0, 0, 0, 0, 0, 1, 1, 1642174227, 0);

-- ----------------------------
-- Table structure for osuu_storage
-- ----------------------------
DROP TABLE IF EXISTS `osuu_storage`;
CREATE TABLE `osuu_storage`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT '类型',
  `name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL COMMENT '名称',
  `space_domain` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL COMMENT '空间域名',
  `AccessKey` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL COMMENT 'AccessKey  secretId',
  `SecretKey` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL COMMENT 'SecretKey',
  `region` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL COMMENT '所属地域',
  `bucket` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL COMMENT '空间名称',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1043 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of osuu_storage
-- ----------------------------
INSERT INTO `osuu_storage` VALUES (1000, 'local', 'Local Storage 1', 'http://storage1.lightpic.com', NULL, NULL, NULL, 'storage1');

-- ----------------------------
-- Table structure for osuu_system
-- ----------------------------
DROP TABLE IF EXISTS `osuu_system`;
CREATE TABLE `osuu_system`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `key` varchar(32) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `attr` varchar(32) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL COMMENT '类型',
  `type` varchar(32) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL COMMENT '分类',
  `title` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL COMMENT '标题',
  `des` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL COMMENT '描述',
  `value` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL COMMENT '值',
  `extend` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL COMMENT '选项',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `key`(`key` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 16 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of osuu_system
-- ----------------------------
INSERT INTO `osuu_system` VALUES (1, 'email_smtp', 'input', 'email', 'SMTP服务器', NULL, '', NULL);
INSERT INTO `osuu_system` VALUES (2, 'email_port', 'input', 'email', 'SMTP端口', NULL, '', NULL);
INSERT INTO `osuu_system` VALUES (3, 'email_secure', 'radio', 'email', 'SMTP协议', NULL, 'ssl', '{\"0\":\"ssl\",\"1\":\"tls\"}');
INSERT INTO `osuu_system` VALUES (4, 'email_usr', 'input', 'email', '邮箱账号', NULL, '', NULL);
INSERT INTO `osuu_system` VALUES (5, 'email_pwd', 'input', 'email', '邮箱密码', NULL, '', NULL);
INSERT INTO `osuu_system` VALUES (6, 'email_template', 'text', 'email_template', '发件模板', NULL, '<html lang=\"zh\"><head><meta http-equiv=\"Content-Type\"content=\"text/html;charset=utf-8\"/><style>.open_email{background:url(http:width:760px;padding:10px;font-family:Tahoma,\"宋体\";margin:0 auto;margin-bottom:20px;text-align:left;margin-left:8px;margin-top:8px;margin-bottom:8px;margin-right:8px}.open_email a:link,.open_email a:visited{color:#295394;text-decoration:none!important}.open_email a:active,.open_email a:hover{color:#000;text-decoration:underline!important}.open_email h5,.open_email h6{font-size:14px;margin:0;padding-top:2px;line-height:21px}.open_email h5{color:#df0202;padding-bottom:10px}.open_email h6{padding-bottom:2px}.open_email h5 span,.open_email p{font-size:12px;color:#808080;font-weight:normal;margin:0;padding:0;line-height:21px}</style><title></title></head><body><div align=\"center\"><div class=\"open_email\"><div style=\"box-sizing:border-box;text-align:center;min-width:320px; max-width:660px; border:1px solid #f6f6f6; background-color:#f7f8fa; margin:auto; padding:20px 0 30px;\"><table style=\"width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse\"><tbody><tr style=\"font-weight:300\"><td style=\"width:3%;max-width:30px;\"></td><td style=\"max-width:600px;\"><p style=\"height:2px;background-color: #00a4ff;border: 0;font-size:0;padding:0;width:100%;margin-top:20px;\"></p><div id=\"cTMail-inner\"style=\"background-color:#fff; padding:23px 0 20px;box-shadow: 0px 1px 1px 0px rgba(122, 55, 55, 0.2);text-align:left;\"><table style=\"width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse;text-align:left;\"><tbody><tr style=\"font-weight:300\"><td style=\"width:3.2%;max-width:30px;\"></td><td style=\"max-width:480px;text-align:left;\"><h1 style=\"font-weight:bold;font-size:20px; line-height:36px; margin:0 0 16px;\">[标题]</h1><p class=\"cTMail-content\"style=\"font-size: 14px; color: rgb(51, 51, 51); line-height: 24px; margin: 0px 0px 36px; word-wrap: break-word; word-break: break-all;\">[内容]</p><dl style=\"font-size:14px;color:#333; line-height:18px;\"></dl><p id=\"cTMail-sender\"style=\"color:#333;font-size:14px; line-height:26px; word-wrap:break-word; word-break:break-all;margin-top:32px;\">此致<br/><strong>[网站名称]团队</strong><a href=\"[网站地址]\">查看更多</a></p></td><td style=\"width:3.2%;max-width:30px;\"></td></tr></tbody></table></div><div id=\"cTMail-copy\"style=\"text-align:center; font-size:12px; line-height:18px; color:#999\"><table style=\"width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse\"><tbody><tr style=\"font-weight:300\"><td style=\"width:3.2%;max-width:30px;\"></td><td style=\"max-width:540px;\"><p style=\"text-align:center; margin:20px auto 14px auto;font-size:12px;color:#999;\">此为系统邮件，请勿回复。</p></td><td style=\"width:3.2%;max-width:30px;\"></td></tr></tbody></table></div></td><td style=\"width:3%;max-width:30px;\"></td></tr></tbody></table></div></div></div></body></html>', NULL);
INSERT INTO `osuu_system` VALUES (7, 'is_reg', 'switch', 'basics', '开放注册', '是否开启网站前台注册', '1', NULL);
INSERT INTO `osuu_system` VALUES (9, 'init_quota', 'number', 'basics', '用户初始配额/GB', NULL, '100', NULL);
INSERT INTO `osuu_system` VALUES (10, 'upload_max', 'number', 'basics', '上传最大尺寸/MB', NULL, '50', NULL);
INSERT INTO `osuu_system` VALUES (12, 'upload_rule', 'input', 'basics', '允许上传后缀', NULL, 'jpg,jpeg,gif,png,ico,svg', NULL);
INSERT INTO `osuu_system` VALUES (13, 'is_show_storage', 'switch', 'basics', '展示存储桶', '向非管理员用户展示存储桶列表', '1', NULL);
INSERT INTO `osuu_system` VALUES (14, 'is_show_role', 'switch', 'basics', '展示角色组', '向非管理员用户展示角色组列表', '1', NULL);
INSERT INTO `osuu_system` VALUES (15, 'is_show_member', 'switch', 'basics', '展示团队成员', '向非管理员用户展示团队成员列表', '1', NULL);

-- ----------------------------
-- Table structure for osuu_user
-- ----------------------------
DROP TABLE IF EXISTS `osuu_user`;
CREATE TABLE `osuu_user`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_id` int NOT NULL DEFAULT 0 COMMENT '角色ID',
  `username` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '用户名',
  `phone` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '联系电话',
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '邮箱',
  `password` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '密码',
  `avatar` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '头像',
  `capacity` decimal(20, 2) NOT NULL DEFAULT 0.00 COMMENT '可用配额容量(字节：b)',
  `state` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0停用 1启用 2待审核',
  `Secret_key` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT 'API秘钥',
  `reg_ip` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '注册IP',
  `delete_time` int NULL DEFAULT NULL COMMENT '删除时间',
  `update_time` int NOT NULL COMMENT '更新时间',
  `create_time` int NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `username`(`username` ASC) USING BTREE,
  UNIQUE INDEX `email`(`email` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 42 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '用户表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of osuu_user
-- ----------------------------
INSERT INTO `osuu_user` VALUES (1, 1, '管理员', '', 'admin', '7c4a8d09ca3762af61e59520943dc26494f8941b', 'https://oss.aliyuncs.com/aliyun_id_photo_bucket/default_trade.jpg', 1073741824.00, 1, '7x63b59c638aa4a9e98144d9d929c18e', '', NULL, 1642174012, 1639712987);

SET FOREIGN_KEY_CHECKS = 1;
