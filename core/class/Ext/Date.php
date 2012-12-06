<?php

/**
 * 日期时间扩展
 * 
 * @author 小鱼哥哥
 *         @time 2011-12-27 17:17
 * @version 1.0
 */
class Ext_Date {

    /**
     *
     * @var integer 当前时间戳
     */
    public static $currentTime = 0;

    /**
     * 获取微秒时间
     *
     * @return mixed 微秒时间
     */
    public static function getMicrotime(){
        return microtime(true);
    }

    /**
     * 获取当前时间戳
     *
     * @return mixed 当前时间戳
     */
    public static function getTime(){
        if (0 == self::$currentTime){
            self::$currentTime = time();
        }
        return self::$currentTime;
    }

    /**
     * getTime 的别名
     *
     * @return mixed 当前时间戳
     */
    public static function now(){
        return self::getTime();
    }

    /**
     * 获取今天凌晨的时间戳
     * 
     * @return void
     */
    public static function today(){
        return strtotime(date('Y-m-d 00:00:00'));
    }

    /**
     * 获取日期/时间信息
     *
     * @param mixed $sec
     *            时间戳, 默认为当前时间
     * @return array 日期/时间信息
     */
    public static function getInfo($sec = null){
        if (!$sec) $sec = self::getTime();
        $rs = getdate($sec);
        $info = array(
                'year'=>$rs['year'],
                'month'=>$rs['mon'],
                'day'=>$rs['mday'],
                'week'=>$rs['wday'],
                'hour'=>$rs['hours'],
                'minute'=>$rs['minutes'],
                'second'=>$rs['seconds'],
                'time'=>$sec
        );
        foreach($info as & $value){
            if ($value < 10){
                $value = '0' . $value;
            }
        }
        unset($value);
        return $info;
    }

    /**
     * 格式化时间
     *
     * @param mixed $sec
     *            时间戳, 默认为当前时间
     * @param string $type
     *            转换格式
     * @return string 转换后的时间
     */
    public static function format($sec = null, $type = 'Y-m-d H:i:s'){
        if (!$sec) $sec = self::getTime();
        return date($type,$sec);
    }

    /**
     * 获取生活化的时间表达
     * 
     * @param integer $sec
     *            需要转换的时间戳
     * @return string
     */
    public static function life($sec){
        if (!$sec){
            return '';
        }
        $now = self::getTime();
        $today = self::today();
        $limit = $now - $sec;
        if ($sec > $today){
            if ($limit < 0){
                $re = '';
            }elseif ($limit < 60){
                $re = '刚刚';
            }elseif ($limit < 3600){
                $re = floor($limit / 60) . ' 分钟前';
            }elseif ($limit < 3600 * 24){
                $re = floor($limit / 3600) . ' 小时前';
            }
        }else{
            if ($sec > ($today - 3600 * 24)){
                $re = '昨天 ' . date('H:i',$sec);
            }elseif ($sec > ($today - 3600 * 24 * 2)){
                $re = '前天 ' . date('H:i',$sec);
            }else{
                $re = date('Y-m-d',$sec);
            }
        }
        return $re;
    }

    /**
     * 加减时间,返回运算后的时间字符串表示
     *
     * @param mixed $time
     *            时间
     * @param integer $addTime
     *            添加或者减少的时间
     * @return string 运算后的时间字符串表示
     */
    public static function add($time, $addTime = 0){
        if (!is_numeric($time)){
            $time = strtotime($time);
        }
        $time += $addTime;
        return self::format($time);
    }

    /**
     * 根据秒数返回类似电子钟的格式 00:00:00
     *
     * @param integer $sec
     *            时间戳
     * @return string 电子钟字符串
     */
    public static function getClock($sec){
        $h = 0;
        $m = 0;
        if ($sec >= 3600){
            $h = floor($sec / 3600);
            $sec = $sec % 3600;
        }
        if ($sec >= 60){
            $m = floor($sec / 60);
            $sec = $sec % 60;
        }
        $reArr = array();
        if ($h < 10){
            $h = '0' . $h;
        }
        if ($m < 10){
            $m = '0' . $m;
        }
        if ($sec < 10){
            $sec = '0' . $sec;
        }
        $restr = implode(':',array(
                $h,
                $m,
                $sec
        ));
        return $restr;
    }
}