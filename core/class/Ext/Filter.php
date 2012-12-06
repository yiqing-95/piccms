<?php

/**
 * 字符过滤扩展
 * 
 * @author 小鱼哥哥
 *         @time 2011-12-27 17:17
 * @version 1.0
 */
class Ext_Filter {

    /**
     * 过滤字符
     * 
     * @param
     *            mixed
     * @return void
     */
    public static function sqlChars($str){
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
                '',
                ' '
        );
        return str_replace($arr,'',$str);
    }

    /**
     * 检查非法字符
     *
     * @param string $str
     *            待检查的字符串
     * @return string/false 返回所包含的非法字符, 不包含则返回false
     */
    public static function checkInvalidChars($str){
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
                '',
                ' '
        );
        foreach($arr as $ch){
            if (false !== strstr($str,$ch)){
                if ('　' == $ch || '' == $ch)
                    return '不能显示的空字符';
                else
                    return $ch;
            }
        }
        return false;
    }

    /**
     * 检查敏感词
     *
     * @param string $str
     *            待检查的字符串
     * @param array $match
     *            包含的敏感词的引用
     * @return Boolean 包含返回true, 否则返回false
     */
    public static function checkBadWord($str, & $match = array ()){
        $badWord = require (WEE_PATH . 'misc/lang/badword_' .
                 Wee::$config['lang']['name'] . '.php');
        $filterChar = '/|\s|\*|＊|\&|＆|\$|@|＠|\!|！|#|＃|\%|％|\^|\;|\=|\.|_|\-|\(|（|）|\)|。|「|」|『|』|〖|〗|【|】|《|》｛| ｝|¨|　|,|｜|；|‘|\"|\'|’|~|～|`|“|”|、|·|ˉ|‖|－|\/|\\\/';
        $newStr = preg_replace($filterChar,'',$str);
        $re = preg_match($badWord,$newStr,$match);
        if (false == $re){
            if (preg_match("/\.\w/",$str)){
                $urlStr = "/((https?:\/\/)?([\w%\-]+\.)+\w+\/?[&\/?=%\-\w:.]*)/iu";
                $re = preg_match($urlStr,$str,$match);
            }
        }
        return $re;
    }
}
