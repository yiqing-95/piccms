<?php

/**
 * 评论管理模型
 * 
 * @author 小鱼哥哥
 *         @time 2011-12-8 17:14
 * @version 1.0
 */
class Comment_Model extends Model {

    private $_face = array(
            201,
            202,
            203,
            204,
            205,
            206,
            207,
            208,
            209,
            210
    );

    public function __construct(){
        parent::__construct();
        $this->setTable('#@_comment','id');
    }

    public function getTotal($where){
        $res = $this->db->table('#@_comment')
            ->field("COUNT(*) AS num")
            ->where($where)
            ->getOne();
        return $res['num'];
    }

    public function getAll($where, $limit = '0, 10', $order = 'id', $by = 'DESC'){
        $res = $this->db->table('#@_comment')
            ->where($where)
            ->limit($limit)
            ->order("$order $by")
            ->getAll();
        foreach($res as & $value){
            $value['content'] = preg_replace('/\[(\d+)\]/e',
                    "\$this->_parseFace('\\1')",$value['content']);
        }
        return $res;
    }

    public function delByArticleId($id){
        $res = $this->db->table('#@_comment')
            ->where(array(
                'article_id'=>$id
        ))
            ->delete();
        return $res;
    }

    private function _parseFace($con){
        if (in_array($con,$this->_face)){
            return "<img src=" . Wee::$config['web_path'] .
                     "images/face/face{$con}.gif>";
        }else{
            return '[' . $con . ']';
        }
    }

    public function add($data){
        $this->db->table('#@_comment')->insert($data);
    }

    public function upRelpy($id){
        $this->db->table('#@_comment')
            ->where(array(
                'id'=>$id
        ))
            ->update("up = up + 1");
    }
}



