/*
SQLyog Ultimate v12.09 (64 bit)
MySQL - 8.0.11 
*********************************************************************
*/
/*!40101 SET NAMES utf8 */;

create table `pay_member_auth_rule` (
	`id` mediumint (8),
	`icon` varchar (300),
	`menu_name` varchar (300),
	`title` varchar (300),
	`pid` tinyint (5),
	`is_menu` tinyint (1),
	`is_race_menu` tinyint (1),
	`type` tinyint (1),
	`status` tinyint (1),
	`condition` char (300)
); 
insert into `pay_member_auth_rule` (`id`, `icon`, `menu_name`, `title`, `pid`, `is_menu`, `is_race_menu`, `type`, `status`, `condition`) values('1','fa fa-home','Index/index','管理首页','0','1','0','1','1','');
insert into `pay_member_auth_rule` (`id`, `icon`, `menu_name`, `title`, `pid`, `is_menu`, `is_race_menu`, `type`, `status`, `condition`) values('2','fa fa-volume-up','System/#','公告','0','1','0','1','1','');
insert into `pay_member_auth_rule` (`id`, `icon`, `menu_name`, `title`, `pid`, `is_menu`, `is_race_menu`, `type`, `status`, `condition`) values('3','fa fa-user-circle','Admin/#','子账户管理','0','1','0','1','1','');
insert into `pay_member_auth_rule` (`id`, `icon`, `menu_name`, `title`, `pid`, `is_menu`, `is_race_menu`, `type`, `status`, `condition`) values('4','fa fa-users','User/#','账户管理','0','1','0','1','1','');
insert into `pay_member_auth_rule` (`id`, `icon`, `menu_name`, `title`, `pid`, `is_menu`, `is_race_menu`, `type`, `status`, `condition`) values('5','fa fa-money','Agent/#','财务管理','0','1','0','1','1','');
insert into `pay_member_auth_rule` (`id`, `icon`, `menu_name`, `title`, `pid`, `is_menu`, `is_race_menu`, `type`, `status`, `condition`) values('6','fa fa-check','User/#','结算管理','0','1','0','1','1','');
insert into `pay_member_auth_rule` (`id`, `icon`, `menu_name`, `title`, `pid`, `is_menu`, `is_race_menu`, `type`, `status`, `condition`) values('7','fa fa-gears','Withdrawal','订单管理','0','1','0','1','1','');
insert into `pay_member_auth_rule` (`id`, `icon`, `menu_name`, `title`, `pid`, `is_menu`, `is_race_menu`, `type`, `status`, `condition`) values('8','fa fa-gears','Channel/#','代理管理','0','1','0','1','1','');
insert into `pay_member_auth_rule` (`id`, `icon`, `menu_name`, `title`, `pid`, `is_menu`, `is_race_menu`, `type`, `status`, `condition`) values('9','fa fa-bank','Content/#','API管理','0','1','0','1','1','');
-- insert into `pay_member_auth_rule` (`id`, `icon`, `menu_name`, `title`, `pid`, `is_menu`, `is_race_menu`, `type`, `status`, `condition`) values('10','fa fa-line-chart','Statistics/#','财务分析','0','1','0','1','1','');
insert into `pay_member_auth_rule` (`id`, `icon`, `menu_name`, `title`, `pid`, `is_menu`, `is_race_menu`, `type`, `status`, `condition`) values('10','','Index/main','控制面板','1','1','0','1','1','');
insert into `pay_member_auth_rule` (`id`, `icon`, `menu_name`, `title`, `pid`, `is_menu`, `is_race_menu`, `type`, `status`, `condition`) values('11','','Account/qrcode','台卡管理','1','1','0','1','1','');
insert into `pay_member_auth_rule` (`id`, `icon`, `menu_name`, `title`, `pid`, `is_menu`, `is_race_menu`, `type`, `status`, `condition`) values('12','','Index/gonggao','站内公告','2','1','0','1','1','');
insert into `pay_member_auth_rule` (`id`, `icon`, `menu_name`, `title`, `pid`, `is_menu`, `is_race_menu`, `type`, `status`, `condition`) values('13','','Auth/index','子账户列表','3','1','0','1','1','');
insert into `pay_member_auth_rule` (`id`, `icon`, `menu_name`, `title`, `pid`, `is_menu`, `is_race_menu`, `type`, `status`, `condition`) values('14','','Account/profile','基本信息','4','1','0','1','1','');
insert into `pay_member_auth_rule` (`id`, `icon`, `menu_name`, `title`, `pid`, `is_menu`, `is_race_menu`, `type`, `status`, `condition`) values('15','','Account/bankcard','银行卡管理','4','1','0','1','1','');
insert into `pay_member_auth_rule` (`id`, `icon`, `menu_name`, `title`, `pid`, `is_menu`, `is_race_menu`, `type`, `status`, `condition`) values('16','','Account/authorized','认证信息','4','1','0','1','1','');
insert into `pay_member_auth_rule` (`id`, `icon`, `menu_name`, `title`, `pid`, `is_menu`, `is_race_menu`, `type`, `status`, `condition`) values('17','','Account/editPassword','登录密码','4','1','0','1','1','');
insert into `pay_member_auth_rule` (`id`, `icon`, `menu_name`, `title`, `pid`, `is_menu`, `is_race_menu`, `type`, `status`, `condition`) values('18','','Account/editPaypassword','支付密码','4','1','0','1','1','');
insert into `pay_member_auth_rule` (`id`, `icon`, `menu_name`, `title`, `pid`, `is_menu`, `is_race_menu`, `type`, `status`, `condition`) values('19','','Account/loginrecord','登录记录','4','1','0','1','1','');
insert into `pay_member_auth_rule` (`id`, `icon`, `menu_name`, `title`, `pid`, `is_menu`, `is_race_menu`, `type`, `status`, `condition`) values('20','','Account/google','Google身份验证','4','1','0','1','1','');
insert into `pay_member_auth_rule` (`id`, `icon`, `menu_name`, `title`, `pid`, `is_menu`, `is_race_menu`, `type`, `status`, `condition`) values('21','','Account/changeRecord','资金记录','5','1','0','1','1','');
insert into `pay_member_auth_rule` (`id`, `icon`, `menu_name`, `title`, `pid`, `is_menu`, `is_race_menu`, `type`, `status`, `condition`) values('22','','Account/channelFinance','通道分析','5','1','0','1','1','');
insert into `pay_member_auth_rule` (`id`, `icon`, `menu_name`, `title`, `pid`, `is_menu`, `is_race_menu`, `type`, `status`, `condition`) values('23','','Account/complaintsDeposit','保证金明细','5','1','0','1','1','');
insert into `pay_member_auth_rule` (`id`, `icon`, `menu_name`, `title`, `pid`, `is_menu`, `is_race_menu`, `type`, `status`, `condition`) values('24','','Account/frozenMoney','冻结资金明细','5','1','0','1','1','');
insert into `pay_member_auth_rule` (`id`, `icon`, `menu_name`, `title`, `pid`, `is_menu`, `is_race_menu`, `type`, `status`, `condition`) values('25','','Withdrawal/clearing','结算申请','6','1','0','1','1','');
insert into `pay_member_auth_rule` (`id`, `icon`, `menu_name`, `title`, `pid`, `is_menu`, `is_race_menu`, `type`, `status`, `condition`) values('26','','Withdrawal/dfapply','代付申请','6','1','0','1','1','');
insert into `pay_member_auth_rule` (`id`, `icon`, `menu_name`, `title`, `pid`, `is_menu`, `is_race_menu`, `type`, `status`, `condition`) values('27','','Withdrawal/index','结算记录','6','1','0','1','1','');
insert into `pay_member_auth_rule` (`id`, `icon`, `menu_name`, `title`, `pid`, `is_menu`, `is_race_menu`, `type`, `status`, `condition`) values('28','','Withdrawal/payment','代付记录','6','1','0','1','1','');
insert into `pay_member_auth_rule` (`id`, `icon`, `menu_name`, `title`, `pid`, `is_menu`, `is_race_menu`, `type`, `status`, `condition`) values('29','','Agent/order','所有订单','7','1','0','1','1','');
insert into `pay_member_auth_rule` (`id`, `icon`, `menu_name`, `title`, `pid`, `is_menu`, `is_race_menu`, `type`, `status`, `condition`) values('30','','Agent/invitecode','注册邀请码','8','1','0','1','1','');
insert into `pay_member_auth_rule` (`id`, `icon`, `menu_name`, `title`, `pid`, `is_menu`, `is_race_menu`, `type`, `status`, `condition`) values('31','','Agent/member','下级商户管理','8','1','0','1','1','');
insert into `pay_member_auth_rule` (`id`, `icon`, `menu_name`, `title`, `pid`, `is_menu`, `is_race_menu`, `type`, `status`, `condition`) values('32','','Agent/invitecode','注册邀请码','8','1','0','1','1','');
insert into `pay_member_auth_rule` (`id`, `icon`, `menu_name`, `title`, `pid`, `is_menu`, `is_race_menu`, `type`, `status`, `condition`) values('33','','Channel/index','查看通道费率','9','1','0','1','1','');
