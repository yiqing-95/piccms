DROP TABLE IF EXISTS `#@_admin`;
CREATE TABLE `#@_admin` (
  `uid` int(10) unsigned NOT NULL auto_increment COMMENT 'UID',
  `name` varchar(30) NOT NULL COMMENT '用户名',
  `password` char(32) NOT NULL COMMENT '密码',
  `email` varchar(50) NOT NULL COMMENT '管理组',
  `pre` smallint(5) NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  UNIQUE KEY `uname` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#@_adsense`;
CREATE TABLE `#@_adsense` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `title` varchar(50) NOT NULL,
  `des` varchar(50) NOT NULL default '' COMMENT '说明',
  `content` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#@_article`;
CREATE TABLE `#@_article` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `cid` smallint(5) NOT NULL,
  `title` varchar(250) NOT NULL,
  `tag` varchar(100) NOT NULL default '' COMMENT '标签',
  `color` char(8) NOT NULL default '',
  `cover` varchar(250) NOT NULL default '' COMMENT '封面',
  `author` varchar(50) NOT NULL default '' COMMENT '作者',
  `comeurl` varchar(250) NOT NULL default '' COMMENT '来源',
  `remark` text NOT NULL,
  `content` text NOT NULL,
  `hits` mediumint(8) NOT NULL default '0',
  `star` tinyint(1) NOT NULL default '1',
  `status` tinyint(1) NOT NULL default '1',
  `up` mediumint(8) NOT NULL default '0',
  `down` mediumint(8) NOT NULL default '0',
  `jumpurl` varchar(255) NOT NULL default '',
  `addtime` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `cid` (`cid`),
  KEY `hits` (`hits`),
  KEY `star` (`star`),
  KEY `status` (`status`),
  KEY `up` (`up`),
  KEY `down` (`down`),
  KEY `addtime` (`addtime`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#@_attach`;
CREATE TABLE `#@_attach` (
  `id` int(10) unsigned NOT NULL auto_increment COMMENT '附件ID',
  `article_id` int(10) unsigned NOT NULL default '0' COMMENT '主题ID',
  `uid` int(10) unsigned NOT NULL default '0' COMMENT '用户ID',
  `name` varchar(100) NOT NULL default '' COMMENT '文件名',
  `remark` text NOT NULL COMMENT '文件描述',
  `size` int(11) NOT NULL default '0' COMMENT '文件大小',
  `file` varchar(250) NOT NULL COMMENT '文件路径',
  `ext` varchar(10) NOT NULL COMMENT '文件类型',
  `status` tinyint(1) NOT NULL default '1' COMMENT '状态, 1:正常 0:隐藏',
  `type` tinyint(1) NOT NULL default '0' COMMENT '附件类型, 0:本地文件, 1:网络文件',
  `try_count` tinyint(2) NOT NULL default '0' COMMENT '重试次数',
  `upload_time` int(10) unsigned NOT NULL default '0' COMMENT '上传时间',
  PRIMARY KEY  (`id`),
  KEY `article_id` (`article_id`),
  KEY `type` (`type`,`try_count`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#@_cache`;
CREATE TABLE `#@_cache` (
  `name` varchar(40) NOT NULL default '' COMMENT '字符串',
  `value` text NOT NULL COMMENT '显示文字',
  `package` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT '时间',
  PRIMARY KEY  (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='公用数据存贮';

DROP TABLE IF EXISTS `#@_cate`;
CREATE TABLE `#@_cate` (
  `cid` smallint(5) unsigned NOT NULL auto_increment,
  `pid` smallint(5) NOT NULL,
  `oid` smallint(5) NOT NULL default '0',
  `view_type` tinyint(1) NOT NULL default '0' COMMENT '内容显示模式:0: 普通模式, 1:幻灯模式',
  `status` tinyint(1) NOT NULL default '1',
  `name` varchar(30) NOT NULL default '',
  `eng_name` varchar(30) NOT NULL default '',
  `ctpl` varchar(30) NOT NULL default '' COMMENT '分类模板',
  `ctitle` varchar(50) NOT NULL,
  `ckeywords` varchar(255) NOT NULL,
  `cdescription` varchar(255) NOT NULL,
  PRIMARY KEY  (`cid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#@_comment`;
CREATE TABLE `#@_comment` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `article_id` int(10) NOT NULL,
  `title` varchar(250) NOT NULL,
  `user_name` char(30) NOT NULL default '' COMMENT '用户名',
  `up` int(11) NOT NULL default '0',
  `ip` char(20) NOT NULL COMMENT 'IP',
  `content` text NOT NULL,
  `status` tinyint(1) NOT NULL default '0' COMMENT '默认状态',
  `dateline` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `article_id` (`article_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#@_config`;
CREATE TABLE `#@_config` (
  `name` varchar(40) NOT NULL default '' COMMENT '字符串',
  `value` text NOT NULL COMMENT '显示文字',
  `des` varchar(50) NOT NULL default '' COMMENT '说明',
  PRIMARY KEY  (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='语言包(简体中文)';

DROP TABLE IF EXISTS `#@_lang_chs`;
CREATE TABLE `#@_lang_chs` (
  `name` varchar(40) NOT NULL default '' COMMENT '字符串',
  `value` text NOT NULL COMMENT '显示文字',
  PRIMARY KEY  (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='语言包(简体中文)';

DROP TABLE IF EXISTS `#@_link`;
CREATE TABLE `#@_link` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `title` varchar(50) NOT NULL,
  `logo` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `oid` tinyint(3) NOT NULL,
  `type` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#@_pick_list`;
CREATE TABLE `#@_pick_list` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `rule_id` int(11) NOT NULL,
  `title` varchar(250) NOT NULL,
  `url` varchar(250) NOT NULL,
  `article_id` int(11) NOT NULL default '0',
  `is_picked` tinyint(1) NOT NULL default '0' COMMENT '是否采集过',
  `p_content_urls` text NOT NULL COMMENT '分页地址',
  `p_content_page` mediumint(5) NOT NULL default '0' COMMENT '当前页数',
  `do_time` int(11) NOT NULL default '0' COMMENT '采集时间',
  PRIMARY KEY  (`id`),
  KEY `rule_id` (`rule_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#@_pick_rule`;
CREATE TABLE `#@_pick_rule` (
  `id` mediumint(7) NOT NULL auto_increment,
  `webname` varchar(100) NOT NULL default '',
  `cid` int(11) NOT NULL default '0',
  `status` tinyint(1) NOT NULL default '0' COMMENT '文章状态',
  `charset_type` char(10) NOT NULL default '' COMMENT '目标网站编码',
  `listurl` text NOT NULL,
  `page_begin` int(10) NOT NULL default '0',
  `page_end` int(10) NOT NULL,
  `page_step` int(10) NOT NULL,
  `listmoreurl` text NOT NULL,
  `list_content_rule` text NOT NULL,
  `list_url_rule` text NOT NULL,
  `list_url_join` varchar(250) NOT NULL default '',
  `title_rule` text NOT NULL,
  `link_include_word` varchar(250) NOT NULL,
  `link_noinclude_word` varchar(250) NOT NULL,
  `link_replace_word` varchar(250) default NULL,
  `title_replace_word` varchar(250) default NULL,
  `content_rule` text NOT NULL,
  `file_rule` text NOT NULL,
  `file_include_word` varchar(250) NOT NULL,
  `file_noinclude_word` varchar(250) NOT NULL,
  `file_replace_word` varchar(250) default NULL,
  `page_content_rule` text NOT NULL,
  `page_rule` text NOT NULL,
  `page_url_join` varchar(250) NOT NULL default '',
  `page_include_word` varchar(250) NOT NULL default '',
  `page_noinclude_word` varchar(250) NOT NULL default '',
  `page_first` tinyint(1) NOT NULL default '0' COMMENT '分页规则里是否包含有第1页',
  `last_pick_time` int(11) NOT NULL default '0',
  `add_time` int(11) NOT NULL COMMENT '添加时间',
  `p_list_page` mediumint(5) NOT NULL default '0' COMMENT '列表处理到多少页',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='采集规则';

DROP TABLE IF EXISTS `#@_tags`;
CREATE TABLE `#@_tags` (
  `tag` varchar(30) NOT NULL,
  `title` varchar(250) NOT NULL COMMENT '文章标题',
  `article_id` int(11) NOT NULL COMMENT '文章ID',
  KEY `tag` (`tag`),
  KEY `article_id` (`article_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `#@_admin` (`uid`, `name`, `password`, `email`, `pre`) VALUES
(1, 'admin', '10b6b3d0887c48556c37914f110ffeca', '', 15);

INSERT INTO `#@_adsense` (`id`, `title`, `des`, `content`) VALUES
(1, 'banner', '头部banner广告', '<a href="#" target="_blank"><img src="{$web_url}images/ad/adv_700_90.jpg" border="0" /></a>'),
(2, 'index-top', '首页上部广告', '<a href="#" target="_blank"> <img border="0" src="{$web_url}images/ad/adv_980_90.jpg" width="980" height="90"> </a>'),
(3, 'index-bottom', '首页底部广告', '<a href="#" target="_blank"> <img border="0" src="{$web_url}images/ad/adv_980_90.jpg" width="980" height="90"> </a>'),
(4, 'article-right', '内容页右边广告', '<a href="javascript:void(0)"><img src="{$web_path}images/ad/adv_300_200.jpg" alt="" /></a>'),
(6, 'cate-right', '列表页右边广告', '<a href="javascript:void(0)"><img src="{$web_path}images/ad/adv_220_90.jpg" alt="" /></a>');

INSERT INTO `#@_cate` (`cid`, `pid`, `oid`, `view_type`, `status`, `name`, `eng_name`, `ctpl`, `ctitle`, `ckeywords`, `cdescription`) VALUES
(11, 0, 0, 0, 1, '测试测试', '', '', '', '', ''),
(7, 0, 4, 1, 1, '惊艳模特', '', '', '', '', ''),
(8, 0, 3, 1, 1, '明星美女', '', '', '', '', ''),
(9, 0, 2, 0, 1, '性感美女', '', '', '', '', ''),
(6, 0, 1, 0, 1, '清纯美女', '', '', '', '', '');

INSERT INTO `#@_config` (`name`, `value`, `des`) VALUES
('web_name', '漫窝 - 美图网', ''),
('web_url', 'http://localhost/wbsifan/piccms/', ''),
('web_path', '/wbsifan/piccms/', ''),
('web_email', '29500196@qq.com', ''),
('web_qq', '29500196', ''),
('web_icp', '沪ICP10001号', ''),
('web_hotkey', '风光|国家地理', ''),
('web_keywords', '图片,风景,摄影', ''),
('web_description', '图片,风景,摄影', ''),
('web_copyright', 'Copyright © 2012 <strong style="color:#FF6600">Manwo.Com</strong>, All Rights Reserved 漫窝 版权所有', ''),
('web_tongji', '', ''),
('upload_http_thumb', '0', ''),
('template_skin', 'default', ''),
('web_admin_cover', '1', ''),
('web_admin_pagenum', '10', ''),
('web_list_pagenum', '20', ''),
('web_maps_num', '100', ''),
('web_rss_num', '100', ''),
('web_comment', '1', ''),
('web_comment_status', '0', ''),
('web_comment_vcode', '1', ''),
('web_comment_pagenum', '10', ''),
('web_pick_hits', '100', ''),
('web_pick_up', '100', ''),
('web_adsensepath', '', ''),
('upload_path', 'attach', ''),
('upload_style', 'Y-m-d', ''),
('upload_thumb_type', '1', ''),
('upload_thumb_w', '100', ''),
('upload_thumb_h', '100', ''),
('upload_cut_pct', '90', ''),
('upload_resize', '1', ''),
('upload_max_w', '960', ''),
('upload_max_h', '960', ''),
('upload_water', '1', ''),
('upload_water_img', 'images/water.png', ''),
('upload_water_pct', '80', ''),
('upload_water_pos', '9', ''),
('upload_max_num', '30', ''),
('upload_dispatch', '1', ''),
('upload_safe_link', '0', ''),
('upload_safe_domain', 'test.com|pic.com', ''),
('upload_ftp', '0', ''),
('upload_ftp_host', '', ''),
('upload_ftp_user', '', ''),
('upload_ftp_pass', '', ''),
('upload_ftp_port', '', ''),
('upload_ftp_dir', '', ''),
('upload_ftp_url', '', ''),
('url_mode', '1', ''),
('url_suffix', '.html', ''),
('url_html_index', '0', ''),
('url_html_cate', '0', ''),
('url_html_content', '0', ''),
('url_html_maps', '0', ''),
('url_dir_cate', 'html', ''),
('url_dir_content', 'html/article', ''),
('url_dir_maps', 'html/maps', ''),
('url_create_time', '1', ''),
('url_create_num', '10', ''),
('html_cache_on', '0', ''),
('html_cache_index', '60', ''),
('html_cache_cate', '60', ''),
('html_cache_content', '60', ''),
('web_article_pagenum', '1', ''),
('url_route_on', '1', ''),
('url_route_rule_cate', 'list', ''),
('url_route_rule_article', 'article', ''),
('url_route_rule_tags', 'tags', ''),
('url_route_rule_search', 'search', ''),
('url_route', '0', ''),
('upload_water_text', '漫窝网版权所有', ''),
('upload_water_textcolor', 'ffffff', ''),
('upload_water_bgcolor', '000000', '');


INSERT INTO `#@_lang_chs` (`name`, `value`) VALUES
('cms_name', 'Mypic图片管理系统'),
('cms_ver', '3.0Beta');

INSERT INTO `#@_link` (`id`, `title`, `logo`, `url`, `oid`, `type`) VALUES
(1, 'PicCMS', 'http://www.piccms.com/org/link-logo.gif', 'http://www.piccms.com', 0, 2),
(2, '漫窝-美图', '', 'http://www.manwo.com', 2, 1),
(3, '哇图网壁纸', '', 'http://www.walltu.com', 3, 1),
(4, '小志博客', '', 'http://www.linxz.de/', 4, 1);
