--
-- 进程控制模块
--
-- 表的结构 `task_control`
--

CREATE TABLE IF NOT EXISTS `task_control` (
  `id` varchar(30) collate utf8_unicode_ci NOT NULL COMMENT '任务标识',
  `des` varchar(50) collate utf8_unicode_ci default NULL COMMENT '任务描述',
  `max_count` tinyint(3) NOT NULL default '0' COMMENT '子进程数量',
  `script` varchar(255) collate utf8_unicode_ci NOT NULL COMMENT '执行脚本',
  `type` enum('keep','minute','hour','day','week','month') collate utf8_unicode_ci NOT NULL default 'keep' COMMENT '执行类型',
  `exec_time` varchar(255) collate utf8_unicode_ci NOT NULL COMMENT '执行时间可使用的公式变量: $year, $month, $day, $weekday, $hour, $minute, $second;可使用的比较符: +-*/% ==<>;',
  `last_exec_time` timestamp NOT NULL default '0000-00-00 00:00:00',
  `status` tinyint(1) NOT NULL default '0' COMMENT '执行状态 (-1: 暂停, 0: 等待执行, 1:正在执行 )',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='任务进程列表';

--
-- 转存表中的数据 `task_control`
--

INSERT INTO `task_control` (`id`, `des`, `max_count`, `script`, `type`, `exec_time`, `last_exec_time`, `status`) VALUES
('test1', '测试进程1', 1, 'server/server.php c=main a=test1', 'minute', '$minute % 1 == 0', '2010-09-09 15:04:46', 1),
('test2', '测试进程2', 0, 'server/server.php c=main a=test3', 'keep', '1', '2010-09-09 15:04:29', -1);
