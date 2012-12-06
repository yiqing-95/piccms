--
-- WeePHP 会话管理模块SQL
--
--
-- 表的结构 `session`
--

CREATE TABLE IF NOT EXISTS `session` (
  `id` char(32) NOT NULL COMMENT 'session_id',
  `value` text NOT NULL,
  `exp` int(11) NOT NULL COMMENT '过期时间',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
