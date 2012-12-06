<?php

/**
 * Config配置管理
 * 
 * @author 小鱼哥哥
 *         @time 2011-9-6 15:33
 * @version 1.0
 */
class Config_Model extends Model {

    public function __construct(){
        parent::__construct();
    }

    /**
     * 初始化核心配置
     * 
     * @param
     *            mixed
     * @return void
     */
    public function initConfig(){
        $config = $this->db->table('#@_config')->getAll();
        $config = Ext_Array::format($config,'name','value');
        if ($config['url_route']){
            $config['url_route_rule'] = array(
                    $config['url_route_rule_cate']=>array(
                            'Cate',
                            'index',
                            'cid,p'
                    ),
                    $config['url_route_rule_article']=>array(
                            'Article',
                            'index',
                            'id,p'
                    ),
                    $config['url_route_rule_tags']=>array(
                            'Tags',
                            'index',
                            'tag,p'
                    ),
                    $config['url_route_rule_search']=>array(
                            'Search',
                            'index',
                            'keyword,p'
                    )
            );
        }
        $rs = write_config(Wee::$config['data_path'] . 'web-config.php',$config);
        return $rs;
    }

    /**
     * 更新配置参数
     * 
     * @param
     *            mixed
     * @return void
     */
    public function setConfig($name, $value = null){
        if (is_array($name)){
            foreach($name as $key=>$val){
                $this->db->table('#@_config')->replace(
                        array(
                                'name'=>$key,
                                'value'=>trim($val)
                        ));
            }
        }else{
            $this->db->table('#@_config')->replace(
                    array(
                            'name'=>$name,
                            'value'=>trim($value)
                    ));
        }
        $this->initConfig();
    }

    /**
     * 更新文件缓存
     * 
     * @param
     *            mixed
     * @return void
     */
    public function clearFileCache(){
        return Ext_Dir::del(Wee::$config['data_path'] . 'cache/');
    }

    public function getSkinList(){
        $source = Wee::$config['view_path'];
        $dirs = Ext_Dir::getDirList($source,Ext_Dir::TYPE_DIR,
                array(
                        'admin',
                        '.svn',
                        'install'
                ));
        return $dirs;
    }
}



