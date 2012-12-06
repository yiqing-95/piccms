<?php
/**
 * 文章统计插件
 *
 * @author 小鱼哥哥
 *         @time 2012-2-7 14:18
 * @version 1.0
 */

// 注册文章总数调用标签 @articleNum()
Wee::$output->registerTag('articleNum','tag_article_num');

function tag_article_num($cid = 0){
    $where = array();
    if ($cid){
        $where['cid'] = $cid;
    }
    $totalNum = load_model('Article')->getTotal($where);
    return $totalNum;
}

// 注册今日更新文章数调用标签 @todayNum()
Wee::$output->registerTag('todayNum','tag_today_num');

function tag_today_num($cid = 0){
    $today = Ext_Date::today();
    $where = array(
            "addtime > $today"
    );
    if ($cid){
        $where['cid'] = $cid;
    }
    $todayNum = load_model('Article')->getTotal($where);
    return $todayNum;
}