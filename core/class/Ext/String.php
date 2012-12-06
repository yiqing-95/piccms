<?php

/**
 * 字符串处理扩展
 * 
 * @author 小鱼哥哥
 *         @time 2011-12-27 17:18
 * @version 1.0
 */
class Ext_String {

    /**
     * 获取字符串长度
     *
     * @param string $str
     *            字符串
     * @param string $charset
     *            编码, 默认使用配置里的编码
     * @return integer 字符串长度
     */
    public static function strlen($str, $charset = ''){
        if (!$charset){
            $charset = 'utf-8';
        }
        if (function_exists('iconv_strlen')){
            return iconv_strlen($str,$charset);
        }elseif (function_exists('mb_strlen')){
            return mb_strlen($str,$charset);
        }
    }

    /**
     * 清除HTML标记
     * 
     * @param string $str
     *            字符串
     * @return string
     */
    public static function stripTags($str){
        return preg_replace('/<[^>]*>/','',$str);
    }

    /**
     * 截取字符
     *
     * @param string $str
     *            字符串
     * @param integer $length
     *            要截取的长度
     * @param string $suffix
     *            后缀
     * @param string $charset
     *            编码, 默认使用配置里的编码
     * @return mixed
     */
    public static function cut($str, $length, $suffix = '', $charset = 'utf-8'){
        if (Ext_String::strlen($str) > $length){
            if (function_exists('iconv_substr')){
                $str = iconv_substr($str,0,$length,$charset);
            }elseif (function_exists('mb_substr')){
                $str = mb_substr($str,0,$length,$charset);
            }
            $suffix && $str .= $suffix;
        }
        return $str;
    }

    /**
     * 生成哈希分布规则
     *
     * @param string $str
     *            字符串
     * @param integer $level
     *            目录层数
     * @param integer $length
     *            每层目录长度
     * @return string
     */
    public static function hash($str, $level = 1, $length = 2){
        $hash = hash('md5',strtolower($str));
        for($i = 0; $i < $level; $i++){
            $hashParts[] = substr($hash,$i * $length,$length);
        }
        $hash = implode('/',$hashParts);
        return $hash;
    }

    /**
     * 计算公式结果
     *
     * @param string $formula
     *            公式表达式
     * @param array $assignVars
     *            包含公式要使用的变量的数组
     * @return mixed 计算结果
     */
    public static function formula($formula, $assignVars){
        if (!trim($formula)) return null;
        if (is_array($assignVars) && count($assignVars) > 0) extract(
                $assignVars);
        eval("\$formulaResult = ($formula);");
        return $formulaResult;
    }

    /**
     * 获取随机字符
     *
     * @param integer $num
     *            字符串位数
     * @return string
     */
    public static function getSalt($num = 4){
        $str = "23456789abcdefghijkmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVW";
        $rs = '';
        $len = strlen($str) - 1;
        for($i = 0; $i < $num; $i++){
            $rs .= $str[mt_rand(0,$len)];
        }
        return $rs;
    }

    /**
     * 字符串加密
     *
     * @param string $string
     *            字符串
     * @param string $key
     *            密钥
     * @return string
     */
    public static function encrypt($string, $key){
        srand((double)microtime() * 1000000);
        $encryptKey = md5(mt_rand(0,32000));
        $ctr = 0;
        $tmp = "";
        for($i = 0; $i < strlen($string); $i++){
            if ($ctr == strlen($encryptKey)){
                $ctr = 0;
            }
            $tmp .= substr($encryptKey,$ctr,1) .
                     (substr($string,$i,1) ^ substr($encryptKey,$ctr,1));
            $ctr++;
        }
        return base64_encode(self::_keyed($tmp,$key));
    }

    /**
     * 字符串解密
     *
     * @param string $string
     *            字符串
     * @param string $key
     *            密钥
     * @return string
     */
    public static function decrypt($string, $key){
        $string = self::_keyed(base64_decode($string),$key);
        $tmp = "";
        for($i = 0; $i < strlen($string); $i++){
            $md5 = substr($string,$i,1);
            $i++;
            $tmp .= (substr($string,$i,1) ^ $md5);
        }
        return $tmp;
    }

    private static function _keyed($string, $encryptKey){
        $encryptKey = md5($encryptKey);
        $ctr = 0;
        $tmp = "";
        for($i = 0; $i < strlen($string); $i++){
            if ($ctr == strlen($encryptKey)){
                $ctr = 0;
            }
            $tmp .= substr($string,$i,1) ^ substr($encryptKey,$ctr,1);
            $ctr++;
        }
        return $tmp;
    }

    /**
     * 生成不可逆的加密码
     * 
     * @param string $uname
     *            用户名
     * @param string $password
     *            密码
     * @return void
     */
    public static function passHash($password){
        $hash = md5($password);
        return md5(substr($hash,16,16) . substr($hash,0,16));
    }

    /**
     * 用正则表达式从取出一段文字
     * 
     * @param
     *            mixed
     * @return void
     */
    public static function cutPreg($startFlag, $endFlag, $str, $mode = 's'){
        if (preg_match(
                '/' . preg_quote($startFlag) . '(.*?)' . preg_quote($endFlag) .
                         "/{$mode}",$str,$result)){
            $str = $result[0];
        }
        return $str;
    }

    /**
     * 匹配链接地址
     * 
     * @param
     *            mixed
     * @return void
     */
    public static function fetchLinks($str){
        $flag = "/<\s*a\s.*?href\s*=\s*([\"\'])?(?(1)(.*?)\\1|([^\s\>]+))(?:.*?)>(.*?)<\/a>/is";
        $match = array();
        if (preg_match_all($flag,$str,$links)){
            foreach($links[0] as $key=>$val){
                if (!empty($links[2][$key])){
                    $value['url'] = $links[2][$key];
                    $value['title'] = $links[4][$key];
                    $match[] = $value;
                }elseif (!empty($links[3][$key])){
                    $value['url'] = $links[3][$key];
                    $value['title'] = $links[4][$key];
                    $match[] = $value;
                }
            }
        }
        return $match;
    }

    /**
     * 匹配图片地址
     * 
     * @param
     *            mixed
     * @return void
     */
    public static function fetchImages($str){
        $patten = '/<img.*?src=[\'\"]?([^\s>\"\']+)[\'\"][^>]*>/is';
        $match = array();
        if (preg_match_all($patten,$content,$arr)){
            foreach($arr[1] as $value){
                $match[] = array(
                        'src'=>$value,
                        'title'=>''
                );
            }
        }
        return $match;
    }

    /**
     *
     * @param mixed $args            
     * @return mixed
     */
    public static function base64UrlEncode($input){
        return strtr(base64_encode($input),'+/=','-_!');
    }

    public static function base64UrlDecode($input){
        return base64_decode(strtr($input,'-_!','+/='));
    }
}
