<?php

/**
 * 文章
 * 
 * @author 小鱼哥哥
 *         @time 2011-9-6 15:33
 * @version 1.0
 */
class Article_Model extends Model {

    public function __construct(){
        parent::__construct();
    }

    /**
     * 根据条件获取数据
     * 
     * @param
     *            mixed
     * @return void
     */
    public function search($where, $limit = '0, 10', $order = 'id', $by = 'DESC'){
        if(is_array($where)){
            if (!isset($where['status'])){
                $where['status'] = 1;
            }
            if ($where['status'] < 0){
                unset($where['status']);
            }

        }
        $res = $this->db->table('#@_article')
            ->field(
                'id, cid, title, tag, color, cover, author, comeurl, remark, hits, star, status, up, down, jumpurl, addtime')
            ->where($where)
            ->limit($limit)
            ->order($order . ' ' . $by)
            ->getAll();
        foreach($res as &$value){
            $value = $this->getVo($value);
        }
        unset($value);
        return $res;
    }

    /**
     * 获取文章记录总数
     * 
     * @param
     *            mixed
     * @return void
     */
    public function getTotal($where = array()){
        if (!isset($where['status'])){
            $where['status'] = 1;
        }
        if ($where['status'] < 0){
            unset($where['status']);
        }
        $res = $this->db->table('#@_article')
            ->field("COUNT(*) AS num")
            ->where($where)
            ->getOne();
        return $res['num'];
    }

    /**
     * 处理文章数据
     * 
     * @param
     *            mixed
     * @return void
     */
    public function getVo($value){
        $modAttach = load_model('Attach');
        $cMod = load_model('Cate');
        // 分类
        $value['cate'] = load_model('Cate')->get($value['cid']);
        // 标签
        if ($value['tag']){
            $value['tagArr'] = explode(',',$value['tag']);
        }else{
            $value['tagArr'] = array();
        }
        if ($value['jumpurl']){
            $value['url'] = url('Jump','',array(
                    'url'=>$value['jumpurl']
            ),0);
        }else{
            $value['url'] = $this->getUrl($value['id']);
        }
        $value['pubdate'] = Ext_Date::format($value['addtime']);
        // 封面缩略图
        $value['cover_url'] = '';
        if ($value['cover']){
            $value['cover_url'] = $modAttach->getAttachUrl($value['cover']);
        }
        return $value;
    }

    public function getUrl($id, $page = 0){
        if (Wee::$config['url_html_content']){
            $url = Wee::$config['web_url'] . $this->_getName($id,$page);
        }else{
            if ($page > 1){
                $url = url('Article','',array(
                        'id'=>$id,
                        'p'=>$page
                ));
            }else{
                $url = url('Article','',array(
                        'id'=>$id
                ));
            }
        }
        return $url;
    }

    public function getPath($id, $page = 0){
        return APP_PATH . $this->_getName($id,$page);
    }

    private function _getName($id, $page = 0){
        $mod = floor($id / 100);
        if ($page > 1){
            $name = "{$mod}/{$id}_{$page}" . Wee::$config['url_suffix'];
        }else{
            $name = "{$mod}/{$id}" . Wee::$config['url_suffix'];
        }
        if (Wee::$config['url_dir_content']){
            $name = Wee::$config['url_dir_content'] . '/' . $name;
        }
        return $name;
    }

    /**
     * 获取文章信息
     * 
     * @param
     *            mixed
     * @return void
     */
    public function get($articleId){
        $cacheKey = __METHOD__ . "{$articleId}";
        $cacheData = $this->cache->getFromBox($cacheKey);
        if ($cacheData){
            return $cacheData;
        }
        $rs = $this->db->table('#@_article')
            ->where("id = $articleId")
            ->getOne();
        if ($rs){
            $rs = $this->getVo($rs);
            /*
             * if ($getAttach) { $modAttach = load_model('Attach'); $attachList
             * = $modAttach->getAttachList($articleId); $rs['attach'] =
             * $attachList; $rs['attach_num'] = count($rs['attach']); }
             */
        }
        $this->cache->setToBox($cacheKey,$rs);
        return $rs;
    }

    /**
     * 上一篇下一篇
     * 
     * @param
     *            mixed
     * @return void
     */
    public function getPre($id){
        $res = $this->search("id < $id AND status = 1",1,'id','DESC');
        if ($res){
            $res = $res[0];
        }
        return $res;
    }

    public function getNext($id){
        $res = $this->search("id > $id AND status = 1",1,'id','ASC');
        if ($res){
            $res = $res[0];
        }
        return $res;
    }

    /**
     * 删除文章
     * 
     * @param
     *            mixed
     * @return void
     */
    public function del($articleId){
        $modAttach = load_model('Attach');
        $attachList = $modAttach->getAttachList($articleId);
        // 删除附件
        if (!empty($attachList)){
            foreach($attachList as $value){
                $modAttach->delByInfo($value);
            }
        }
        // 删除标签
        $this->setTags($articleId,null);
        // 删除评论
        load_model('Comment')->delByArticleId($articleId);
        $rs = $this->db->table('#@_article')
            ->where("id = $articleId")
            ->delete();
        return $rs;
    }

    public function set($id, $data){
        $rs = $this->db->table('#@_article')
            ->where(array(
                'id'=>$id
        ))
            ->update($data);
        return $rs;
    }

    public function add($data){
        $this->db->table('#@_article')->insert($data);
        return $this->db->insertId();
    }

    /**
     * 分析标签
     * 
     * @param
     *            mixed
     * @return void
     */
    public function parseTags($title){
        $tagList = $this->getTags();
        $tag = array();
        if ($tagList){
            foreach($tagList as $value){
                if (false !== strpos($title,$value['tag'])){
                    $tag[] = $value['tag'];
                    $title = str_replace($value['tag'],'',$title);
                }
            }
        }
        return implode(',',$tag);
    }

    /**
     * 获取TAGS
     * 
     * @param
     *            mixed
     * @return void
     */
    public function getTags($limit = 0){
        if ($limit){
            $this->db->limit($limit);
        }
        $rs = $this->db->table('#@_tags')
            ->field("tag, COUNT(tag) AS num")
            ->group('tag')
            ->getAll();
        foreach($rs as & $value){
            $value = $this->getTagVo($value);
        }
        return $rs;
    }

    /**
     * 获取标签内容
     * 
     * @param
     *            mixed
     * @return void
     */
    public function getTagVo($value){
        $value['url'] = url('Tags','',array(
                'tag'=>$value['tag']
        ));
        $value['star'] = mt_rand(0,4);
        return $value;
    }

    /**
     * 更新标签
     * 
     * @param
     *            mixed
     * @return void
     */
    public function setTags($articleId, $tag, $title = ''){
        $this->db->table('#@_tags')
            ->where("article_id = $articleId")
            ->delete();
        if ($tag){
            $tag = explode(',',$tag);
            $data = array();
            foreach($tag as $key=>$value){
                $data[$key] = array(
                        'tag'=>trim($value),
                        'article_id'=>$articleId,
                        'title'=>$title
                );
            }
            $this->db->table('#@_tags')->insert($data);
        }else{
            $this->db->table('#@_tags')
                ->where(array(
                    'article_id'=>$articleId
            ))
                ->delete();
        }
    }

    /**
     * 获取标签总数
     * 
     * @param
     *            mixed
     * @return void
     */
    public function getTagsTotal($tag){
        $res = $this->db->table('#@_tags')
            ->field("COUNT(tag) AS num")
            ->where("tag = '$tag'")
            ->getOne();
        return $res['num'];
    }

    /**
     * 获取标签文章列表
     * 
     * @param
     *            mixed
     * @return void
     */
    public function getTagsArticle($tag, $limit = '0, 10'){
        $res = $this->db->table('#@_tags')
            ->where(array(
                'tag'=>$tag
        ))
            ->limit($limit)
            ->getAll();
        if ($res){
            $ids = Ext_Array::cols($res,'article_id');
            if (false !== strpos($limit,',')){
                list (,$limit) = explode(',',$limit);
            }
            $res = $this->search(array(
                    'id'=>$ids
            ),$limit);
        }
        return $res;
    }
}



