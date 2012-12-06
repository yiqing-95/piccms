<?php

/**
 * 插件管理器
 * 
 * @author 小鱼哥哥
 *         @time 2011-12-27 17:17
 * @version 1.0
 */
class Ext_Hack {

    /**
     * 注册插件
     * 
     * @param
     *            mixed
     * @return void
     */
    public static function register($hackArr){
        foreach($hackArr as $value){
            $hClass = $value . '_Index_Hack';
            $hFile = Wee::$config['hack_path'] . "$value/index.php";
            import_file($hFile);
            if (!class_exists($hClass,false)){
                show_error("$value: 插件已经停用或者没有安装");
            }
            Wee::$box['hackInstance'][$value] = new $hClass();
        }
    }

    /**
     * 获取已经注册过的插件实例
     * 
     * @param
     *            mixed
     * @return void
     */
    public function getInstance($hack){
        if (!isset(Wee::$box['hackInstance'][$hack])){
            show_error("$hack: 插件未注册");
        }
        return Wee::$box['hackInstance'][$hack];
    }

    /**
     * 运行插件服务
     * 
     * @param
     *            mixed
     * @return void
     */
    public static function handle($m, $do){
        $mClass = $m . '_Admin_Hack';
        $mFile = Wee::$config['hack_path'] . "$m/admin.php";
        import_file($mFile);
        if (!class_exists($mClass,false)){
            show_msg("$m: 插件不存在或者没有安装");
        }
        $mObj = new $mClass();
        $mObj->$do();
    }

    /**
     * 获取插件列表
     * 
     * @param
     *            mixed
     * @return void
     */
    public static function gethackList(){
        $fList = Ext_Dir::getDirList(Wee::$config['hack_path'],
                Ext_Dir::TYPE_DIR,array(
                        '.svn'
                ));
        $hackList = array();
        if ($fList){
            foreach($fList as $value){
                $doc = Wee::$config['hack_path'] . $value . '/doc.xml';
                if (is_file($doc)){
                    $hackList[$value] = Ext_Xml::xmlFileToArray($doc);
                }
            }
        }
        return $hackList;
    }
}