<?php

/**
 * 标签管理模型
 * 
 * @author 小鱼哥哥
 *         @time 2011-9-6 15:33
 * @version 1.0
 */
class Tag_Model extends Model {

    public function __construct(){
        parent::__construct();
    }

    /**
     * 生成图片地址
     * 调用: @image($src, $width = 0, $height = 0, $type = 1)
     * 如果$width或者$height为0, 则显示原图
     * 
     * @param string $src
     *            图片附件地址
     * @param int $width
     *            生成图片宽度, 默认为0
     * @param int $height
     *            生成图片高度, 默认为0
     * @param int $type
     *            缩略图生成方式, 0:等比例, 1:居中裁剪, 2:左上裁剪, 默认为1
     * @return string 生成后的图片地址
     */
    public function image($src, $width = 0, $height = 0, $type = 1){
        if (Wee::$config['upload_dispatch']){ // 使用调度器
            $args = "$src,$width,$height,$type";
            $crc = substr(md5(Wee::$config['encrypt_key'] . $args),10,6);
            $url = Wee::$config['web_url'] . 'attach.php?r=' .
                     Ext_String::base64UrlEncode("$args,$crc");
        }else{
            $url = load_model('Attach')->makeImage($src,$width,$height,$type);
        }
        return $url;
    }

    /**
     * 获取文章列表
     * 调用: @article($cid, $star, $num = 10, $order = 'id')
     * 返回一个数组, 在 foreach 循环中调用
     * 
     * @param int $cid
     *            分类ID, 多个分类用","分隔, 如"1,2"
     * @param int $star
     *            显级, 多个星级用","分隔, 如 "2,3,4"
     * @param int $num
     *            要显示的条数
     * @param string $order
     *            排序字段, hits:点击, up:顶, down:踩, addtime:添加时间, 默认为最新添加
     * @return array 文章列表
     */
    public function article($cid, $star, $num = 10, $order = 'id'){
        $cacheKey = "article_{$cid}_{$star}_{$num}_{$order}";
        $cacheData = $this->cache->getFromBox($cacheKey);
        if ($cacheData){
            return $cacheData;
        }
        if ($cid){
            if (false !== strpos($cid,',')){
                $where['cid'] = explode(',',$cid);
            }else{
                $modCate = load_model('Cate');
                $cate = $modCate->getPlace($cid);
                if ($cate['sonId']){
                    $where['cid'] = $cate['sonId'];
                    array_unshift($where['cid'],$cid);
                }else{
                    $where['cid'] = $cid;
                }
            }
        }
        if ($star){
            if (false !== strpos($star,',')){
                $where['star'] = explode(',',$star);
            }else{
                $where['star'] = $star;
            }
        }
        if ('week' == $order){
            $where[] = "addtime > " . time() - 7 * 24 * 3600;
            $order = 'hits';
        }
        $articleMod = load_model('Article');
        $articleList = $articleMod->search($where,$num,$order,'DESC');
        $this->cache->setToBox($cacheKey,$articleList);
        return $articleList;
    }

    /**
     * 获取某篇文章的相关文章
     * 调用: @relevant($id, $limit = 5)
     * 
     * @param int $id
     *            文章ID
     * @param int $limit
     *            调用条数
     * @return array 相关文章列表
     */
    public function relevant($id, $limit = 5){
        $modArticle = load_model('Article');
        $articleInfo = $modArticle->get($id);
        $list = array();
        if ($articleInfo['tagArr']){
            $res = $this->db->table('#@_tags')
                ->where(array(
                    'tag'=>$articleInfo['tagArr']
            ))
                ->limit($limit)
                ->getAll();
            $ids = Ext_Array::cols($res,'article_id');
            $where = array(
                    'id'=>$ids
            );
            $list = $modArticle->search($where,$limit);
        }
        return $list;
    }

    /**
     * 获取标签列表
     * 调用: @tags($num = 20)
     * 
     * @param int $num
     *            标签数量, 默认为20
     * @return array
     */
    public function tags($num = 20){
        $cacheKey = "tags_$num";
        $cacheData = $this->cache->getFromFile($cacheKey);
        if ($cacheData){
            return $cacheData;
        }
        $modArticle = load_model('Article');
        $tags = $modArticle->getTags($num);
        $this->cache->setToFile($cacheKey,$tags);
        return $tags;
    }

    /**
     * 获取关键词搜索地址
     * 调用: @searchurl($keyword)
     * 
     * @param string $keyword
     *            关键词
     * @return string
     */
    public function searchurl($keyword){
        return url('Search','',array(
                'keyword'=>$keyword
        ));
    }

    /**
     * 获取标签地址
     * 调用: @searchurl($keyword)
     * 
     * @param string $keyword
     *            关键词
     * @return string
     */
    public function tagsurl($tag){
        return url('Tags','',array(
                'tag'=>$tag
        ));
    }

    /**
     * 获取RSS订阅地址
     * 调用: @rssurl()
     * 
     * @return string
     */
    public function rssurl(){
        if (Wee::$config['url_html_maps']){
            return Wee::$config['web_url'] . Wee::$config['url_dir_maps'] .
                     '/rss.xml';
        }else{
            return url('Maps','rss');
        }
    }

    /**
     * 获取SiteMap地址
     * 调用: @sitemapurl()
     * 
     * @return string
     */
    public function sitemapurl(){
        if (Wee::$config['url_html_maps']){
            return Wee::$config['web_url'] . Wee::$config['url_dir_maps'] .
                     '/sitemap.xml';
        }else{
            return url('Maps');
        }
    }

    /**
     * 获取友情连接列表
     * 调用: @links($type = 0, $num = 100)
     * 
     * @param int $type
     *            友情链接类型, 0:不限, 1:文字, 2:图片, 默认为0
     * @return array
     */
    public function links($type = 0, $num = 100){
        $cacheKey = "links_{$type}_{$num}";
        $cacheData = $this->cache->getFromFile($cacheKey);
        if ($cacheData){
            return $cacheData;
        }
        $where = array();
        if ($type){
            $where['type'] = $type;
        }
        $linkList = load_model('Link')->getList($where,$num,'oid ASC');
        $this->cache->setToFile($cacheKey,$linkList);
        return $linkList;
    }

    /**
     * 获取广告
     * 调用: @adsense($title)
     * 
     * @param string $title
     *            广告标识
     * @return string 广告内容
     */
    public function adsense($title){
        $cKey = "Adsense_$title";
        $cData = $this->cache->getFromFile($cKey);
        if ($cData){
            return $cData;
        }
        $info = load_model('Adsense')->getByTitle($title);
        if ($info){
            $this->cache->setToFile($cKey,$info['content']);
            return $info['content'];
        }else{
            return "<!-- $title:广告标识不存在 -->";
        }
    }
}



