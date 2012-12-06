--
-- WeePHP语言包模块SQL
--
--
-- 表的结构 `lang_chs`
--
-- 创建时间: 2010 年 08 月 27 日 17:40
--

CREATE TABLE IF NOT EXISTS `lang_chs` (
  `name` varchar(40) NOT NULL default '' COMMENT '字符串',
  `value` text NOT NULL COMMENT '显示文字',
  `package` varchar(40) NOT NULL default 'common' COMMENT '语言包',
  PRIMARY KEY  (`name`),
  KEY `language` (`package`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='语言包(简体中文)';
