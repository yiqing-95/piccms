<?php

/**
 * XML编码扩展
 * 
 * @author 小鱼哥哥
 *         @time 2011-12-20 11:57
 * @version 1.0
 */
class Ext_Xml {

    /**
     * XML编码
     * 
     * @param
     *            mixed
     * @return void
     */
    public static function encode($data, $root = 'root', $item = 'item', $charset = 'UTF-8'){
        list ($endroot,) = explode(' ',$root,2);
        $xml = "<?xml version=\"1.0\" encoding=\"$charset\"?>\n<$root>\n" .
                 self::dataToXml($data,$item,$charset) . "</$endroot>";
        return $xml;
    }

    public static function dataToXml($data, $item = 'item', $charset = 'UTF-8'){
        if (is_object($data)){
            $data = get_object_vars($data);
        }
        $xml = '';
        foreach($data as $key=>$value){
            if (is_numeric($key)){
                $xml .= "<$item id=\"{$key}\">";
            }else{
                $xml .= "<{$key}>";
            }
            if (is_array($value) || is_object($value)){
                $xml .= self::dataToXml($value,$item,$charset);
            }elseif (preg_match('/&|\'|\"|>|<|\n/',$value,$tmp)){
                $xml .= "<![CDATA[{$value}]]>";
            }else{
                $xml .= $value;
            }
            if (is_numeric($key)){
                $xml .= "</$item>\n";
            }else{
                $xml .= "</{$key}>\n";
            }
        }
        return $xml;
    }

    /**
     * XML解码
     * 
     * @param
     *            mixed
     * @return void
     */
    public static function decode($xmlString){
        $xmlObj = simplexml_load_string($xmlString);
        $arr = json_decode(json_encode($xmlObj),true);
        return $arr;
    }
}
