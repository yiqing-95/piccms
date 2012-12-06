<?php

/**
 * 采集节点模型
 * 
 * @author 小鱼哥哥
 *         @time 2011-11-3 16:42
 * @version 1.0
 */
class Pick_Pro_Model extends Model {

    public $mod;

    private $_ruleInfo;

    public function __construct($ruleInfo){
        parent::__construct();
        $this->mod = load_model('Pick');
        $this->_ruleInfo = $ruleInfo;
        $this->init();
    }

    public function init(){
        // 总的任务数
        $this->totalProNum = $this->mod->getProNum($this->id);
        // 已经采集过的任务
        $this->overProNum = $this->mod->getProNum($this->id,1) + 1;
        // 获取1个任务信息
        $this->proInfo = $this->mod->getPro($this->id,0,1);
        // 全部采集任务已完成
        if (!$this->proInfo){
            show_msg("全部采集任务已完成");
        }
        // 当前地址
        $this->thisUrl = $this->proInfo['url'];
        // 当前标题
        $this->title = $this->proInfo['title'];
        // 当前下标
        $this->pContentPage = $this->proInfo['p_content_page'];
        // 当前处理的页数也就是下一个要处理的下标
        $this->nextPage = $this->pContentPage + 1;
        // 处理多页
        $this->pageUrls = null;
        if ($this->isMutiPage()){
            if ($this->proInfo['p_content_urls']){
                $this->pageUrls = $this->proInfo['p_content_urls'];
            }else{
                $this->pageUrls = $this->getPageUrls();
            }
            // 统计总页数
            $this->totalPageUrlsNum = count($this->pageUrls);
        }
    }

    public function isMutiOver(){
        if (isset($this->pageUrls[$this->pContentPage])){
            return false;
        }else{
            return true;
        }
    }

    public function doMutiContent(){
        $this->thisUrl = $this->pageUrls[$this->pContentPage]['url'];
        return $this->doContent();
    }

    public function doContent(){
        $articleMod = load_model('Article');
        $modAttach = load_model('Attach');
        if (!$this->proInfo['article_id']){
            $data = array(
                    'cid'=>$this->cid,
                    'status'=>$this->status,
                    'addtime'=>mt_rand(time() - 3600 * 24 * 30,time()),
                    'hits'=>mt_rand(0,Wee::$config['web_pick_hits']),
                    'up'=>mt_rand(0,Wee::$config['web_pick_up'])
            );
            if ($this->title_rule){
                $data['title'] = $this->getTitle();
            }else{
                $data['title'] = $this->proInfo['title'];
            }
            $data['tag'] = $articleMod->parseTags($data['title']);
            $articleId = $articleMod->add($data);
            // 处理标签
            if ($data['tag']){
                $articleMod->setTags($articleId,$data['tag'],$data['title']);
            }
            $this->mod->setPro($this->proInfo['id'],
                    array(
                            'article_id'=>$articleId
                    ));
        }else{
            $articleId = $this->proInfo['article_id'];
        }
        $this->fileUrls = $this->mod->getUrls($this->file_rule,$this->thisUrl,
                $this->contentHtml(),$this->file_include_word,
                $this->file_noinclude_word,$this->file_url_join);
        $this->fileNum = count($this->fileUrls);
        if ($this->fileNum > 0){
            $articleInfo = $articleMod->get($articleId);
            foreach($this->fileUrls as $value){
                $ext = $modAttach->getExt($value['url']);
                $data = array(
                        'uid'=>0,
                        'article_id'=>$articleId,
                        'name'=>basename($value['url']),
                        'remark'=>$value['title'],
                        'file'=>$value['url'],
                        'ext'=>$ext,
                        'size'=>0,
                        'upload_time'=>time(),
                        'type'=>1
                );
                $modAttach->add($data);
                if (!$articleInfo['cover']){
                    $articleMod->set($articleId,array(
                            'cover'=>$value['url']
                    ));
                    $articleInfo['cover'] = $value['url'];
                }
            }
        }
        return true;
    }

    public function pageHtml(){
        if (isset($this->_pageHtml)){
            return $this->_pageHtml;
        }
        $html = $this->mod->getHtml($this->thisUrl,$this->charset_type);
        if (!$html){
            show_msg("{$this->thisUrl}: 访问内容页地址失败, 请检查地址是否正确");
        }
        $this->_pageHtml = $this->mod->getContent($this->page_content_rule,
                $html);
        return $this->_pageHtml;
    }

    public function contentHtml(){
        if (isset($this->_html)){
            return $this->_html;
        }
        $html = $this->mod->getHtml($this->thisUrl,$this->charset_type);
        if (!$html){
            show_msg("{$this->thisUrl}: 访问内容页地址失败, 请检查地址是否正确");
        }
        $this->_html = $this->mod->getContent($this->content_rule,$html);
        return $this->_html;
    }

    public function setPageUrls(){
        $data = array(
                'p_content_urls'=>addslashes(json_encode($this->pageUrls))
        );
        $this->mod->setPro($this->proInfo['id'],$data);
    }

    public function setNextPage(){
        $this->mod->setPro($this->proInfo['id'],
                array(
                        'p_content_page'=>$this->nextPage
                ));
    }

    public function setIsPicked(){
        $this->mod->setPro($this->proInfo['id'],array(
                'is_picked'=>1
        ));
    }

    public function getTitle(){
        $title = $this->mod->getContent($this->title_rule,$this->contentHtml());
        return $title;
    }

    public function getPageUrls(){
        $this->pageUrls = $this->mod->getUrls($this->page_rule,$this->thisUrl,
                $this->pageHtml(),$this->page_include_word,
                $this->page_noinclude_word,$this->page_url_join);
        // 分析分页地址成功, 保存到数据库
        if ($this->pageUrls){
            // 不包含第一页, 将第一页加入分页数组
            if (!$this->page_first){
                array_unshift($this->pageUrls,
                        array(
                                'url'=>$this->thisUrl,
                                'title'=>'1'
                        ));
            }
            $this->setPageUrls();
        }
        return $this->pageUrls;
    }

    public function isMutiPage(){
        if ($this->page_rule){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 获取节点信息
     * 
     * @param
     *            mixed
     * @return void
     */
    public function __get($name){
        if (isset($this->_ruleInfo[$name])){
            return $this->_ruleInfo[$name];
        }else{
            return null;
        }
    }
}