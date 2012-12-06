<?php

/**
 * 语言包管理
 * 
 * @author 小鱼哥哥
 *         @time 2011-12-27 17:15
 * @version 1.0
 */
class Lang {

    /**
     *
     * @var string 语言包表名
     */
    private static $_table = '#@_lang';

    /**
     *
     * @var string 当前使用的语言
     */
    private static $_type = 'chs';

    /**
     *
     * @var object 语言包表所在数据库引用
     */
    private static $_db;

    /**
     * 初始化语言包
     *
     * @return void
     */
    public function __construct($type, $table){
        $this->_type = $type;
        $this->_table = $table;
        $this->_db = load_db();
    }

    private function _getLangPack(){
        $table = Wee::$config['lang_table'] . '_' . Wee::$config['lang_type'];
        $package = load_db()->table($table)->getAll();
        $package = Ext_Array::format($package,'name','value');
        return $package;
    }

    /**
     * 获取语言包值
     *
     * @param string $name
     *            语言包键名
     * @param array/string $args
     *            占位符替换参数
     * @return string 语言包值
     */
    public static function get($name, $args = array()){
        if (!isset(Wee::$box['lang'])){
            if (Wee::$config['lang_cache']){
                $langFile = Wee::$config['data_path'] . 'cache/' . 'lang_' .
                         Wee::$config['lang_type'] . '.php';
                if (is_file($langFile)){
                    Wee::$box['lang'] = require $langFile;
                }else{
                    $langPack = self::_getLangPack();
                    $rs = Ext_File::writeArray($langFile,$langPack);
                    if (!$rs){
                        show_error("Written language pack cache file failed.");
                    }
                    Wee::$box['lang'] = $langPack;
                }
            }else{
                $langPack = self::_getLangPack();
                Wee::$box['lang'] = $langPack;
            }
        }
        if (empty(Wee::$box['lang'][$name])){
            return $name;
        }
        $text = Wee::$box['lang'][$name];
        if ($args){
            if (!is_array($args)){
                $args = func_get_args();
                array_shift($args);
            }
            $reArr = array();
            foreach($args as $key=>$value){
                $reArr["%$key"] = $value;
            }
            $text = strtr($text,$reArr);
        }
        return $text;
    }

    /**
     * 设置语言包值
     *
     * @param string $langName
     *            语言包标识
     * @param string $name
     *            语言包键名
     * @param string $value
     *            语言包值
     * @param string $package
     *            语言包所属模块
     * @return integer 影响行数
     */
    public static function set($langName, $name, $value, $package = ''){
        self::init();
        self::$_db->useDb(self::$_dbname);
        $tableName = self::getRealTableName($langName);
        $info = self::$_db->table($tableName)
            ->where(array(
                'name'=>$name
        ))
            ->getOne();
        $data = array(
                'value'=>$value
        );
        if ($package){
            $data['package'] = $package;
        }
        if ($info){
            self::$_db->table($tableName)
                ->where(array(
                    'name'=>$name
            ))
                ->update($data);
        }else{
            $data['name'] = $name;
            self::$_db->table($tableName)->insert($data);
        }
        if (Wee::$config['ini_use_cache']){
            $cache = Cache_Mem::factory();
            $cache->del($cache->makeName($name,'lang',false));
        }
        return self::$_db->affectRows();
    }

    /**
     * 清空语言包缓存
     *
     * @return void
     */
    public static function clear(){
        self::init();
        if (Wee::$config['ini_use_cache']){
            $cache = Cache_Mem::factory();
            $cache->clear();
        }
    }

    /**
     * 获取真实的语言包表名
     *
     * @param string $langName
     *            语言包标识
     * @return string 真实的语言包表名
     */
    public static function getRealTableName($langName){
        return self::$_table . '_' . $langName;
    }
}