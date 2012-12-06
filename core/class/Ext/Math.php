<?php

/**
 * 数学处理扩展
 * 
 * @author 小鱼哥哥
 *         @time 2011-12-27 17:18
 * @version 1.0
 */
class Ext_Math {

    /**
     * 获取安全锁值
     *
     * @return integer 安全锁值
     */
    public static function getLockKey(){
        return mt_rand(0,65535);
    }

    /**
     * 根据数组值作为概率进行随机选择
     *
     * @param array $array
     *            概率数组, 格式为：
     *            array (
     *            键名1 => 概率值1,
     *            键名2 => 概率值2,
     *            ......
     *            )
     * @param mixed $except
     *            忽略的键名
     * @param integer $degree
     *            精度倍数
     * @return mixed 本次概率到的值
     */
    public static function getProbabilityKey($array, $except = null, $degree = 100){
        if (!is_null($except)){
            unset($array[$except]);
        }
        $total = array_sum($array) * $degree;
        if (!$total){
            return false;
        }
        $intRand = mt_rand(0,$total);
        $offset = 0;
        foreach($array as $key=>$item){
            $value = $item * $degree;
            if ($intRand <= $value + $offset){
                $result = $key;
                break;
            }
            $offset += $value;
        }
        return $result;
    }

    /**
     * 根据概率值返回是否成功
     *
     * @param integer $value
     *            概率值 (百分比值，如：30 表示 30%)
     * @param integer $base
     *            概率分母
     * @param integer $degree
     *            精度倍数
     * @return boolean
     */
    public static function probability($value, $base = 100, $degree = 100){
        if ($value <= 0) return false;
        $value = $value * $degree;
        $rand = mt_rand(1,$degree * $base);
        return ($rand <= $value);
    }

    /**
     * 计算公式值
     *
     * @param string $str
     *            公式的字符串
     * @param array $arg
     *            变量参数数组
     * @return mixed 计算过的公式值
     */
    public static function evalString($str, $args){
        if (is_array($args) && count($args) > 0){
            extract($args);
        }
        eval("\$result = ($str);");
        return $result;
    }

    /**
     * 转换字节数为常用格式
     * 
     * @param
     *            mixed
     * @return void
     */
    public static function lifeByte($byte){
        if ($byte > 1024 * 1024 * 1024){
            $lifeByte = sprintf('%0.2f',$byte / 1024 / 1024 / 1024) . 'G';
        }elseif ($byte > 1024 * 1204){
            $lifeByte = sprintf('%0.2f',$byte / 1024 / 1024) . 'M';
        }else{
            $lifeByte = sprintf('%0.2f',$byte / 1024) . 'K';
        }
        return $lifeByte;
    }
}
