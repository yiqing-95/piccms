<?php

/**
 * 生成静态
 * 
 * @author 小鱼哥哥
 *         @time 2011-11-17 16:27
 * @version 1.0
 */
class Html_Controller extends Base_Controller {

    public function __construct(){
        parent::__construct();
        $this->checkLogin(Ext_Auth::CONTENT_EDIT);
    }

    public function index(){
        $this->output->set(
                array(
                        'url_html_index'=>Wee::$config['url_html_index'],
                        'url_html_cate'=>Wee::$config['url_html_cate'],
                        'url_html_content'=>Wee::$config['url_html_content'],
                        'url_html_maps'=>Wee::$config['url_html_maps']
                ));
        $cateStr = load_model('Cate')->printTree('cateCid');
        $conCateStr = load_model('Cate')->printTree('conCid');
        $this->output->set('cateStr',$cateStr);
        $this->output->set('conCateStr',$conCateStr);
        $this->output->display('html_show.html');
    }

    /**
     * 生成首页
     * 
     * @param
     *            mixed
     * @return void
     */
    public function makeIndex(){
        $this->oneKey = $this->input->get('oneKey');
        if (Wee::$config['url_html_index']){
            $url = Wee::$config['web_url'] . 'index.php?c=Main&a=makeHtml';
            $this->makeUrlHtml($url);
            $msg = "网站首页生成完成";
        }else{
            $msg = "网站首页不需要生成";
        }
        if ($this->oneKey){
            $msg .= "<br>开始生成列表页...";
            show_msg($msg,"?c=Html&a=makeCate&oneKey={$this->oneKey}",
                    Wee::$config['url_create_time']);
        }else{
            show_msg($msg,'?c=Html&a=index',Wee::$config['url_create_time']);
        }
    }

    /**
     * 生成列表页
     * 
     * @param
     *            mixed
     * @return void
     */
    public function makeCate(){
        $this->oneKey = $this->input->get('oneKey');
        if (Wee::$config['url_html_cate']){
            $cid = $this->input->getIntval('cateCid');
            $modCate = load_model('Cate');
            $cateList = $modCate->getList();
            if ($cid){
                show_msg('开始生成分类...',"?c=Html&a=makeCateByCid&queue=$cid",0);
            }else{
                $this->queue = array_keys($cateList);
                $cid = reset($this->queue);
                $this->queue = implode(':',$this->queue);
                show_msg('开始生成分类...',
                        "?c=Html&a=makeCateByCid&queue=$this->queue&oneKey=$this->oneKey",
                        0);
            }
        }
        if ($this->oneKey){
            show_msg("列表不需要生成<br>开始生成文章内容页...",
                    "?c=Html&a=makeArticle&oneKey=$this->oneKey",
                    Wee::$config['url_create_time']);
        }else{
            show_msg("列表不需要生成",'?c=Html',Wee::$config['url_create_time']);
        }
    }

    /**
     * 根据分类ID生成分类列表
     * 
     * @param
     *            mixed
     * @return void
     */
    public function makeCateByCid(){
        $this->queue = $this->input->getTrim('queue');
        $this->limitStart = $this->input->getIntval('limitStart');
        $this->pageStart = $this->input->getIntval('pageStart');
        $this->oneKey = $this->input->get('oneKey');
        if ($this->queue){
            $this->queueArr = explode(':',$this->queue);
        }else{
            $this->queueArr = array();
        }
        if (empty($this->queueArr[0])){
            if ($this->oneKey){
                show_msg("列表页生成完成, 开始生成文章内容页...",
                        "?c=Html&a=makeArticle&oneKey={$this->oneKey}",
                        Wee::$config['url_create_time']);
            }else{
                show_msg("列表页生成完成",'?c=Html',Wee::$config['url_create_time']);
            }
        }
        $cid = $this->queueArr[0];
        $modCate = load_model('Cate');
        $cate = $modCate->getPlace($cid);
        if ($cate['sonId']){
            $where['cid'] = $cate['sonId'];
            array_unshift($where['cid'],$cid);
        }else{
            $where['cid'] = $cid;
        }
        $modArticle = load_model('Article');
        $articleNum = $modArticle->getTotal($where);
        $this->totalPage = ceil($articleNum / Wee::$config['web_list_pagenum']);
        if ($this->totalPage < 1){
            $this->totalPage = 1;
        }
        $isOver = true;
        $doCount = 1;
        $this->curPage = $this->pageStart + 1;
        for($i = $this->pageStart; $i < $this->totalPage; $i++){
            if ($doCount > Wee::$config['url_create_num']){
                $isOver = false;
                break;
            }
            $p = $i + 1;
            $url = Wee::$config['web_url'] .
                     "index.php?c=Cate&cid=$cid&p=$p&a=makeHtml";
            $this->makeUrlHtml($url);
            $doCount++;
            $this->pageStart++;
        }
        // 下一个分类
        if ($isOver){
            array_shift($this->queueArr);
            $this->pageStart = 0;
        }
        $cateInfo = load_model('Cate')->get($cid);
        $msg = "分类 [{$cateInfo['name']}] 第 {$this->curPage} / {$this->totalPage} 页生成完成";
        $queue = implode(":",$this->queueArr);
        show_msg($msg,
                "?c=Html&a=makeCateByCid&queue=$queue&oneKey={$this->oneKey}&pageStart={$this->pageStart}",
                Wee::$config['url_create_time']);
    }

    /**
     * 生成文章
     * 
     * @param
     *            mixed
     * @return void
     */
    public function makeArticle(){
        $this->oneKey = $this->input->get('oneKey');
        if (Wee::$config['url_html_content']){
            $cid = $this->input->getIntval('conCid');
            if (!$cid){
                $cateList = load_model('Cate')->getList();
                $cid = implode(':',array_keys($cateList));
            }
            show_msg('开始生成文章页...',
                    "?c=Html&a=makeByCid&queue=$cid&oneKey={$this->oneKey}",0);
        }
        if ($this->oneKey){
            show_msg("内容页不需要生成<br>开始生成地图...",
                    "?c=Html&a=makeMaps&oneKey=$this->oneKey",
                    Wee::$config['url_create_time']);
        }else{
            show_msg("内容页不需要生成",'?c=Html',Wee::$config['url_create_time']);
        }
    }

    /**
     * 根据分类生成文章页
     * 
     * @param
     *            mixed
     * @return void
     */
    public function makeByCid(){
        $this->queue = $this->input->getTrim('queue');
        $this->limitStart = $this->input->getIntval('limitStart');
        $this->pageStart = $this->input->getIntval('pageStart');
        $this->oneKey = $this->input->get('oneKey');
        if ($this->queue){
            $this->queueArr = explode(':',$this->queue);
        }else{
            $this->queueArr = array();
        }
        $modCate = load_model('Cate');
        $modArticle = load_model('Article');
        if (empty($this->queueArr[0])){
            if ($this->oneKey){
                show_msg("内容页生成完成, 开始生成网站地图...",
                        "?c=Html&a=makeMaps&oneKey={$this->oneKey}",
                        Wee::$config['url_create_time']);
            }else{
                show_msg("内容页生成完成",'?c=Html',Wee::$config['url_create_time']);
            }
        }
        $cid = $this->queueArr[0];
        $cate = $modCate->getPlace($cid);
        if ($cate['sonId']){
            $where['cid'] = $cate['sonId'];
            array_unshift($where['cid'],$cid);
        }else{
            $where['cid'] = $cid;
        }
        $total = $modArticle->getTotal($where);
        $articleList = $modArticle->search($where,
                $this->limitStart . ', ' . Wee::$config['url_create_num']);
        if ($articleList){
            $this->_makeArticle($articleList);
            $cateInfo = $modCate->get($cid);
            $this->msg = "分类 [{$cateInfo['name']}] 第 {$this->curArticle} / {$total} 篇 (第 " .
                     ($this->pageStart + 1) . "/{$this->totalPage} 页) 文章生成完成";
        }else{
            array_shift($this->queueArr);
            $cateInfo = $modCate->get($cid);
            $this->msg = "分类 [{$cateInfo['name']}] 内容页生成完成";
            $this->pageStart = 0;
            $this->limitStart = 0;
        }
        $url = '?' . http_build_query(
                array(
                        'c'=>'Html',
                        'a'=>'makeByCid',
                        'oneKey'=>$this->oneKey,
                        'queue'=>implode(":",$this->queueArr),
                        'limitStart'=>$this->limitStart,
                        'pageStart'=>$this->pageStart
                ));
        show_msg($this->msg,$url,Wee::$config['url_create_time']);
    }

    /**
     * 根据更新时间生成文章
     * 
     * @param
     *            mixed
     * @return void
     */
    public function makeByAddtime(){
        $this->limitStart = $this->input->getIntval('limitStart');
        $this->pageStart = $this->input->getIntval('pageStart');
        $mday = $this->input->getIntval('mday');
        $modArticle = load_model('Article');
        $mtime = time() - $mday * 24 * 3600;
        $where = "addtime > $mtime";
        $total = $modArticle->getTotal($where);
        $articleList = $modArticle->search($where,
                $this->limitStart . ', ' . Wee::$config['url_create_num']);
        if ($articleList){
            $this->_makeArticle($articleList);
            $this->msg = "$mday 天内第 {$this->curArticle} / {$total} 篇 (第 " .
                     ($this->pageStart + 1) . "/{$this->totalPage} 页) 文章生成完成";
            $url = '?' . http_build_query(
                    array(
                            'c'=>'Html',
                            'a'=>'makeByAddtime',
                            'limitStart'=>$this->limitStart,
                            'pageStart'=>$this->pageStart,
                            'mday'=>$mday
                    ));
            show_msg($this->msg,$url,Wee::$config['url_create_time']);
        }
        show_msg("$mday 天内所有内容页生成完成",'?c=Html',Wee::$config['url_create_time']);
    }

    /**
     * 生成指定的文章内容
     * 
     * @param
     *            mixed
     * @return void
     */
    public function makeByArticleId(){
        if (!Wee::$config['url_html_content']){
            show_msg("内容页不需要生成");
        }
        $id = $this->input->get('id');
        if (!$id){
            show_msg('至少要选择一篇文章');
        }
        $this->limitStart = $this->input->getIntval('limitStart');
        $this->pageStart = $this->input->getIntval('pageStart');
        if (!is_array($id)){
            $id = explode(':',$id);
        }
        $modArticle = load_model('Article');
        $where = array(
                'id'=>$id
        );
        $total = $modArticle->getTotal($where);
        $articleList = $modArticle->search($where,
                $this->limitStart . ', ' . Wee::$config['url_create_num']);
        if ($articleList){
            $this->_makeArticle($articleList);
            $this->msg = "第 {$this->curArticle} / {$total} 篇 (第 " .
                     ($this->pageStart + 1) . "/{$this->totalPage} 页) 文章生成完成";
            $url = '?' . http_build_query(
                    array(
                            'c'=>'Html',
                            'a'=>'makeByArticleId',
                            'limitStart'=>$this->limitStart,
                            'pageStart'=>$this->pageStart,
                            'id'=>implode(':',$id)
                    ));
            show_msg($this->msg,$url,Wee::$config['url_create_time']);
        }
        show_msg("所选内容页生成完成",'?c=Html',Wee::$config['url_create_time']);
    }

    /**
     * 生成指定文章内容
     * 
     * @param
     *            mixed
     * @return void
     */
    private function _makeArticle($articleList){
        $doCount = 1;
        $isOver = true;
        $modAttach = load_model('Attach');
        $this->curArticle = $this->limitStart + 1;
        foreach($articleList as $value){
            if ($doCount > Wee::$config['url_create_num']){
                $isOver = false;
                break;
            }
            $articleId = $value['id'];
            if (1 == $value['cate']['view_type']){
                Wee::$config['web_article_pagenum'] = 1;
            }
            $attachNum = $modAttach->getAttachNum($articleId);
            $this->totalPage = max(1,
                    ceil($attachNum / Wee::$config['web_article_pagenum']));
            for($i = $this->pageStart; $i < $this->totalPage; $i++){
                if ($doCount > Wee::$config['url_create_num']){
                    $isOver = false;
                    break;
                }
                $page = $i + 1;
                $url = Wee::$config['web_url'] .
                         "index.php?c=Article&id=$articleId&a=makeHtml&p=$page";
                $this->makeUrlHtml($url);
                $doCount++;
                $this->pageStart++;
            }
            if ($isOver){
                $this->pageStart = 0;
                $this->limitStart++;
            }
        }
    }

    /**
     * 生成RSS网站地图
     * 
     * @param
     *            mixed
     * @return void
     */
    public function makeMaps(){
        if (Wee::$config['url_html_maps']){
            $url = Wee::$config['web_url'] .
                     "index.php?c=Maps&a=index&makeHtml=true";
            $this->makeUrlHtml($url);
            $url = Wee::$config['web_url'] .
                     "index.php?c=Maps&a=rss&makeHtml=true";
            $this->makeUrlHtml($url);
            $msg = 'RSS网站地图生成完成';
        }else{
            $msg = 'RSS网站地图不需要生成';
        }
        show_msg($msg,'?c=Html',Wee::$config['url_create_time']);
    }

    /**
     * 生成页面
     * 
     * @param
     *            mixed
     * @return void
     */
    public function makeUrlHtml($url){
        try{
            if (!file_get_contents($url)){
                // echo 1;
            }elseif (!file($url)){
                // echo 2;
            }elseif (!Ext_Network::openUrl($url)){
                // echo 3;
            }else{
                show_msg($url . "<br>生成失败,请检查html保存目录是否存或者是否拥有0777权限",'?c=Html',
                        -1);
            }
        }catch(Error $e){
            show_msg($url . "<br>访问此地址失败请检查服务器DNS设置",'?c=Html',-1);
        }
    }
}