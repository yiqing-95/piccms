<?php

/**
 * 数据验证
 * 
 * @author 小鱼哥哥
 *         @time 2011-9-9 17:10
 * @version 1.0
 */
class Ext_Valid {

    /**
     *
     * @var array 预定义验证格式
     */
    public static $regex = array(
            'require'=>'/.+/', // 匹配任意字符，除了空和断行符
            'email'=>'/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',
            'phone'=>'/^((\(\d{2,3}\))|(\d{3}\-))?(\(0\d{2,3}\)|0\d{2,3}-)?[1-9]\d{6,7}(\-\d{1,4})?$/',
            'mobile'=>'/^((\(\d{2,3}\))|(\d{3}\-))?(13|15)\d{9}$/',
            'url'=>'/^http:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"\"])*$/',
            // 图片连接 http://www.example.com/xxx.jpg
            'img'=>'^(http|https|ftp):(\/\/|\\\\)(([\w\/\\\+\-~`@:%])+\.)+([\w\/\\\.\=\?\+\-~`@\':!%#]|(&amp;)|&)+\.(jpg|bmp|gif|png)$',
            'currency'=>'/^\d+(\.\d+)?$/',
            'number'=>'/\d+$/',
            'zip'=>'/^[1-9]\d{5}$/',
            'qq'=>'/^[1-9]\d{4,12}$/',
            'int'=>'/^[-\+]?\d+$/',
            'double'=>'/^[-\+]?\d+(\.\d+)?$/',
            'english'=>'/^[A-Za-z]+$/'
    );

    /**
     * 验证数据
     * 
     * @param string $value
     *            待验证的数据
     * @param string $checkName
     *            验证类型
     * @return bool
     */
    public static function check($value, $checkName){
        $matchRegex = self::getRegex($checkName);
        return preg_match($matchRegex,trim($value));
    }

    /**
     * 取得验证类型的正则表达式
     * 
     * @param string $name
     *            验证类型
     * @return string
     */
    public static function getRegex($name){
        if (isset(self::$regex[strtolower($name)])){
            return self::$regex[strtolower($name)];
        }else{
            return $name;
        }
    }

    /**
     * 检查非法字符
     *
     * @param string $str
     *            待检查的字符串
     * @return string/false 返回所包含的非法字符, 不包含则返回false
     */
    public static function haveInvalidChars($str){
        $arr = array(
                '\\',
                '/',
                ':',
                '*',
                '?',
                '"',
                '\'',
                '<',
                '>',
                ',',
                '|',
                '%',
                '&',
                '&',
                ';',
                '#',
                '　',
                ''
        );
        foreach($arr as $ch){
            if (false !== strstr($str,$ch)){
                if ('　' == $ch || '' == $ch){
                    return '不能显示的空字符';
                }else{
                    return $ch;
                }
            }
        }
        return false;
    }
}