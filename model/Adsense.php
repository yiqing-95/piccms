<?php

/**
 * 广告
 * 
 * @author 小鱼哥哥
 *         @time 2011-12-15 15:51
 * @version 1.0
 */
class Adsense_Model extends Model {

    public function __construct(){
        parent::__construct();
        $this->setTable('#@_adsense','id');
    }

    public function getByTitle($title){
        $res = $this->db->table('#@_adsense')
            ->where(array(
                'title'=>$title
        ))
            ->getOne();
        if ($res){
            $res['content'] = str_replace('{$web_url}',Wee::$config['web_url'],
                    $res['content']);
            $res['content'] = str_replace('{$web_path}',
                    Wee::$config['web_path'],$res['content']);
        }
        return $res;
    }
}