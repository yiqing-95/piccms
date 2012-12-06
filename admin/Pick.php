<?php

/**
 * 图片采集器
 * 
 * @author YHS
 *         @time 2011-8-30 15:53
 * @version 1.0
 */
class Pick_Controller extends Base_Controller {

    public function __construct(){
        parent::__construct();
        $this->checkLogin(Ext_Auth::WEB_EDIT);
        $this->mod = load_model('Pick');
        $this->waitTime = 1;
    }

    public function show(){
        $pickList = $this->mod->getList();
        $l = $this->cache->getFromDb('abc');
        $this->output->set('pickList',$pickList);
        $this->output->display('pick_show.html');
    }

    /**
     * 采集进程
     * 
     * @param
     *            mixed
     * @return void
     */
    public function progress(){
        $id = $this->input->getIntval('id');
        $pickInfo = $this->mod->get($id);
        if (!$pickInfo){
            show_msg("$id: 采集节点不存在");
        }
        $this->mod->set($id,array(
                'last_pick_time'=>time()
        ));
        $this->output->set('id',$id);
        $this->output->display('pick_progress.html');
    }

    /**
     * 重新采集
     * 
     * @param
     *            mixed
     * @return void
     */
    public function replay(){
        $id = $this->input->getIntval('id');
        $pickInfo = $this->mod->get($id);
        if (!$pickInfo){
            show_msg("$id: 采集节点不存在");
        }
        $this->mod->replay($id);
        show_msg("重新开始采集","?c=Pick&a=progress&id=$id",0);
    }

    /**
     * 采集
     * 
     * @param
     *            mixed
     * @return void
     */
    public function doList(){
        // $this->output->setDataMode(true);
        $id = $this->input->getIntval('id');
        $inframe = $this->input->get('inframe');
        $pickInfo = $this->mod->get($id);
        if (!$pickInfo){
            show_msg("$id: 采集节点不存在");
        }
        $node = new Pick_Node_Model($pickInfo);
        if ($node->isOver()){
            $msg = "列表页分析完成, 开始采集内容页...";
            $this->output->set('msg',$msg);
            $this->output->set('next',true);
            js_run("parent.showProgress('$msg')");
            show_msg($msg,"?c=Pick&a=doContent&inframe=$inframe&id=$id",
                    $this->waitTime);
        }
        if ($node->doPickList()){
            $node->setNextPage();
        }
        $msg = "<队列:{$node->nextPage}/{$node->totalListUrlsNum}>[{$node->thisUrl()}] 分析完成, 共 {$node->totalTitleUrlsNum} 条记录";
        js_run("parent.showProgress('$msg')");
        show_msg($msg,"?c=Pick&a=doList&inframe=$inframe&id=$id",
                $this->waitTime);
    }

    /**
     * 采集内容
     * 
     * @param
     *            mixed
     * @return void
     */
    public function doContent(){
        // $this->output->setDataMode(true);
        $id = $this->input->getIntval('id');
        $inframe = $this->input->get('inframe');
        $pickInfo = $this->mod->get($id);
        if (!$pickInfo){
            show_msg("$id: 采集节点不存在");
        }
        $pro = new Pick_Pro_Model($pickInfo);
        // dump($pro);
        if ($pro->isMutiPage()){
            if (!$pro->pageUrls){
                $pro->setIsPicked();
                $msg = "<队列:{$pro->overProNum}/{$pro->totalProNum}>[$pro->title] 处理分页地址失败...";
                js_run("parent.showProgress('$msg')");
                show_msg($msg,"?c=Pick",-1);
            }
            if ($pro->isMutiOver()){
                $pro->setIsPicked();
                $msg = "<队列:{$pro->overProNum}/{$pro->totalProNum}>[{$pro->title}] 采集完成";
                js_run("parent.showProgress('$msg')");
                show_msg($msg,"?c=Pick&a=doContent&inframe=$inframe&id=$id",
                        $this->waitTime);
            }
            if ($pro->doMutiContent()){
                $pro->setNextPage();
                $msg = "<队列:{$pro->overProNum}/{$pro->totalProNum}>[{$pro->title}] 第 {$pro->nextPage}/{$pro->totalPageUrlsNum} 页 [{$pro->thisUrl}] 采集完成, 共 {$pro->fileNum} 张图片";
                js_run("parent.showProgress('$msg')");
                show_msg($msg,"?c=Pick&a=doContent&inframe=$inframe&id=$id",
                        $this->waitTime);
            }
        }else{
            if ($pro->doContent()){
                $pro->setIsPicked();
                $msg = "[队列:{$pro->overProNum}/{$pro->totalProNum}][{$pro->title}] 采集完成, 共 {$pro->fileNum} 张图片";
                js_run("parent.showProgress('$msg')");
                show_msg($msg,"?c=Pick&a=doContent&inframe=$inframe&id=$id",
                        $this->waitTime);
            }
        }
    }

    /**
     * 复制
     * 
     * @param
     *            mixed
     * @return void
     */
    public function copy(){
        $id = $this->input->getIntval('id');
        $pickInfo = $this->mod->get($id);
        if (!$pickInfo){
            show_msg("$id: 采集节点不存在");
        }
        $node = new Pick_Node_Model($pickInfo);
        $data = $pickInfo;
        unset($data['id']);
        $data['webname'] = $pickInfo['webname'] . '_副本';
        $data['add_time'] = time();
        foreach($data as & $value){
            $value = addslashes($value);
        }
        unset($value);
        $this->mod->add($data);
        show_msg('复制成功','?c=Pick&a=show');
    }

    /**
     * 删除
     * 
     * @param
     *            mixed
     * @return void
     */
    public function del(){
        $id = $this->input->getIntval('id');
        $this->mod->del($id);
        show_msg('删除成功','?c=Pick&a=show',0);
    }

    /**
     * 导出
     * 
     * @param
     *            mixed
     * @return void
     */
    public function export(){
        $id = $this->input->getIntval('id');
        $pickInfo = $this->mod->get($id);
        if (!$pickInfo){
            show_msg("$id: 采集节点不存在");
        }
        unset($pickInfo['id']);
        $text = $this->mod->export($pickInfo);
        if (check_submit()){
            Ext_Network::outContent($pickInfo['webname'] . '_pr.txt',$text);
            return;
        }
        $this->output->set('id',$id);
        $this->output->set('text',$text);
        $this->output->display('pick_export.html');
    }

    /**
     * 导入
     * 
     * @param
     *            mixed
     * @return void
     */
    public function import(){
        $importmode = $this->input->get('importmode');
        if ('file' == $importmode){
            $upfile = $_FILES['upfile'];
            if ($upfile['error']){
                show_msg('上传文件失败');
            }
            $txt = Ext_File::read($upfile['tmp_name']);
        }else{
            $txt = $this->input->getTrim('txt');
            if (!$txt){
                show_msg('请输入规则内容');
            }
        }
        $data = $this->mod->import($txt);
        if (!$data){
            show_msg("无效的规则编码");
        }
        if (isset($data['id'])){
            unset($data['id']);
        }
        $data['add_time'] = time();
        $data = array_map('addslashes',$data);
        $this->mod->add($data);
        show_msg("导入规则成功",'?c=Pick&a=show');
    }

    public function add(){
        $id = $this->input->getIntval('id');
        if ($id){
            $pickInfo = $this->mod->get($id);
            if (!$pickInfo){
                show_msg("$id: 采集节点不存在");
            }
            $this->output->set($pickInfo);
            $cTreeStr = load_model('Cate')->printTree('cid',$pickInfo['cid'],
                    false,false);
        }else{
            $cTreeStr = load_model('Cate')->printTree('cid',0,false,false);
        }
        if (check_submit()){
            $webname = $this->input->getTrim('webname');
            $cid = $this->input->getIntval('cid');
            $titleRule = $this->input->getTrim('title_rule');
            $listUrlRule = $this->input->getTrim('list_url_rule');
            $charset = $this->input->get('charset_type');
            $pageContentRule = $this->input->getTrim('page_content_rule');
            $pageRule = $this->input->getTrim('page_rule');
            $contentRule = $this->input->getTrim('content_rule');
            $fileRule = $this->input->getTrim('file_rule');
            $listurl = $this->input->get('listurl');
            $listmoreurl = $this->input->getTrim('listmoreurl');
            $pageBegin = $this->input->getIntval('page_begin');
            $pageEnd = $this->input->getIntval('page_end');
            $pageStep = $this->input->getIntval('page_step');
            $listContentRule = $this->input->getTrim('list_content_rule');
            if (!$webname){
                show_msg('规则名称不能为空');
            }
            if (!$titleRule){
                show_msg('请输入内容页地址和标题规则');
            }
            if (!$fileRule){
                show_msg('请输入图片地址规则');
            }
            $data = array(
                    "webname"=>$webname,
                    "cid"=>$cid,
                    'status'=>$this->input->getIntval('status'),
                    "charset_type"=>$charset,
                    "listurl"=>$listurl,
                    "page_begin"=>$pageBegin,
                    "page_end"=>$pageEnd,
                    "page_step"=>$pageStep,
                    "listmoreurl"=>$listmoreurl,
                    "list_content_rule"=>$listContentRule,
                    "list_url_rule"=>$listUrlRule,
                    "list_url_join"=>$this->input->getTrim('list_url_join'),
                    "title_rule"=>$titleRule,
                    "link_include_word"=>$this->input->getTrim(
                            'link_include_word'),
                    "link_noinclude_word"=>$this->input->getTrim(
                            'link_noinclude_word'),
                    "content_rule"=>$contentRule,
                    "file_rule"=>$fileRule,
                    "file_url_join"=>$this->input->getTrim('file_url_join'),
                    "file_include_word"=>$this->input->getTrim(
                            'file_include_word'),
                    "file_noinclude_word"=>$this->input->getTrim(
                            'file_noinclude_word'),
                    "page_content_rule"=>$pageContentRule,
                    "page_rule"=>$pageRule,
                    "page_url_join"=>$this->input->getTrim('page_url_join'),
                    "page_include_word"=>$this->input->getTrim(
                            'page_include_word'),
                    "page_noinclude_word"=>$this->input->getTrim(
                            'page_noinclude_word'),
                    "page_first"=>$this->input->getIntval('page_first')
            );
            if ($id){
                $this->mod->set($id,$data);
                show_msg('编辑成功','?c=Pick&a=show');
            }else{
                $data['add_time'] = time();
                $this->mod->add($data);
                show_msg('添加成功','?c=Pick&a=show');
            }
        }
        $this->output->set('cTreeStr',$cTreeStr);
        $this->output->display('pick_add.html');
    }

    public function test(){
        fopen();
    }

    /**
     * 测试列表页规则
     * 
     * @param
     *            mixed
     * @return void
     */
    public function testListRule($return = false){
        $urlArr = $this->testListUrl(true);
        if (!$urlArr){
            return false;
        }
        $listUrlRule = $this->input->getTrim('list_url_rule');
        $linkInWord = $this->input->getTrim('link_include_word');
        $linkNoinWord = $this->input->getTrim('link_noinclude_word');
        $charset = $this->input->get('charset_type');
        $listContentRule = $this->input->get('list_content_rule');
        $listContentRule = stripslashes($listContentRule);
        $listUrlRule = stripslashes($listUrlRule);
        $listUrlJoin = stripslashes($this->input->getTrim('list_url_join'));
        if (!$listUrlRule){
            return $this->_echoError('请输入文章地址规则');
        }
        if (false === strpos($listUrlRule,'[url]') ||
                 false === strpos($listUrlRule,'[title]')){
            return $this->_echoError('内容页地址和标题规则必需包含 [url] 和 [title] ');
        }
        // 访问列表页地址
        $url = reset($urlArr);
        $html = $this->mod->getHtml($url,$charset);
        if (!$html){
            return $this->_echoError("访问 $url 地址失败");
        }
        // file_put_contents('1.txt', $html);
        // 获取列表页区域
        $html = $this->mod->getContent($listContentRule,$html);
        if (!$html){
            return $this->_echoError("分析列表页内容区域失败, 请检查规则");
        }
        
        // 匹配链接地址和标题
        $listUrls = $this->mod->getUrls($listUrlRule,$url,$html,$linkInWord,
                $linkNoinWord,$listUrlJoin);
        if (!$listUrls){
            return $this->_echoError("没有匹配到内容页地址和标题");
        }
        if ($return){
            return $listUrls;
        }
        echo '<div class="fopen_resault"><ul>';
        foreach($listUrls as $value){
            echo "<li>[文章]<a target='_blank' href='{$value['url']}'>{$value['url']}</a> {$value['title']}</li>";
        }
        echo "</ul></div>";
    }

    /**
     * 测试分页规则
     * 
     * @param
     *            mixed
     * @return void
     */
    public function testShowPage($return = false){
        $listUrls = $this->testListRule(true);
        if (!$listUrls){
            return false;
        }
        $charset = $this->input->get('charset_type');
        $pageContentRule = stripslashes(
                $this->input->getTrim('page_content_rule'));
        $pageRule = stripslashes($this->input->getTrim('page_rule'));
        $pageUrlJoin = stripslashes($this->input->getTrim('page_url_join'));
        $pageIncludeWord = $this->input->getTrim('page_include_word');
        $pageNoincludeWord = $this->input->getTrim('page_noinclude_word');
        if (!$pageRule){
            return $this->_echoError('请输入分页地址规则');
        }
        if (false === strpos($pageRule,'[url]')){
            return $this->_echoError('分页地址规则必需包含 [url]');
        }
        // 访问内容页地址
        $url = $listUrls[0]['url'];
        $html = $this->mod->getHtml($url,$charset);
        if (!$html){
            return $this->_echoError("访问 $url 地址失败");
        }
        // 获取内容区域
        $html = $this->mod->getContent($pageContentRule,$html);
        if (!$html){
            return $this->_echoError("没有匹配到分页内容区域");
        }
        // 获取分页Urls
        $pageUrls = $this->mod->getUrls($pageRule,$url,$html,$pageIncludeWord,
                $pageNoincludeWord,$pageUrlJoin);
        if (!$pageUrls){
            return $this->_echoError('没有匹配到分页地址');
        }
        if ($return){
            return $pageUrls;
        }
        
        echo '<div class="fopen_resault"><ul>';
        foreach($pageUrls as $value){
            echo "<li>[分页]<a target='_blank' href='{$value['url']}'>{$value['url']}</a> {$value['title']}</li>";
        }
        echo "</ul></div>";
    }

    /**
     * 测试内容页
     * 
     * @param
     *            mixed
     * @return void
     */
    public function testContentRule($return = false){
        $listUrls = $this->testListRule(true);
        if (!$listUrls){
            return false;
        }
        $charset = $this->input->get('charset_type');
        $titleRule = stripslashes($this->input->getTrim('title_rule'));
        $contentRule = stripslashes($this->input->getTrim('content_rule'));
        $fileRule = stripslashes($this->input->getTrim('file_rule'));
        $fileUrlJoin = stripslashes($this->input->getTrim('file_url_join'));
        $fileIncludeWord = $this->input->getTrim('file_include_word');
        $fileNoincludeWord = $this->input->getTrim('file_noinclude_word');
        if (!$fileRule){
            return $this->_echoError('请输入图片地址规则');
        }
        if (false === strpos($fileRule,'[url]')){
            return $this->_echoError('图片地址规则必需包含 [url]');
        }
        // 访问内容页地址
        $url = $listUrls[0]['url'];
        $html = $this->mod->getHtml($url,$charset);
        if (!$html){
            return $this->_echoError("访问 $url 地址失败");
        }
        // 获取标题
        if ($titleRule){
            $title = $this->mod->getContent($titleRule,$html);
        }else{
            $title = '';
        }
        // 获取内容区域
        $html = $this->mod->getContent($contentRule,$html);
        if (!$html){
            return $this->_echoError("没有匹配到图片内容区域");
        }
        // 获取图片Urls
        $fileUrls = $this->mod->getUrls($fileRule,$url,$html,$fileIncludeWord,
                $fileNoincludeWord,$fileUrlJoin);
        if (!$fileUrls){
            return $this->_echoError('没有匹配到图片地址和标题');
        }
        if ($return){
            return $fileUrls;
        }
        echo '<div class="fopen_resault">';
        echo '<h3>标题: ' . $title . '</h3>';
        echo '<ul>';
        foreach($fileUrls as $value){
            echo "<li>[图]<a target='_blank' href='{$value['url']}'>{$value['url']}</a> {$value['title']}</li>";
        }
        echo "</ul></div>";
    }

    /**
     * 测试列表序列地址
     * 
     * @param
     *            mixed
     * @return void
     */
    public function testListUrl($return = false){
        $listurl = $this->input->get('listurl');
        $listmoreurl = $this->input->getTrim('listmoreurl');
        $pageBegin = $this->input->getIntval('page_begin');
        $pageEnd = $this->input->getIntval('page_end');
        $pageStep = $this->input->getIntval('page_step');
        $urlArr = $this->mod->getListUrls($listmoreurl,$listurl,$pageBegin,
                $pageEnd,$pageStep);
        if (!$urlArr){
            return $this->_echoError("没有找到列表页地址");
        }
        if ($return){
            return $urlArr;
        }
        echo '<div class="fopen_resault"><ul>';
        foreach($urlArr as $value){
            echo "<li>[列表]<a target='_blank' href='{$value}'>{$value}</a></li>";
        }
        echo '</ul></div>';
    }

    private function _echoError($msg){
        echo "<div class='fopen_resault'>$msg</div>";
        return false;
    }
}