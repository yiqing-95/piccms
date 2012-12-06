<?php

/**
 * 附件管理
 * 
 * @author YHS
 *         @time 2011-8-30 15:53
 * @version 1.0
 */
class Attach_Controller extends Base_Controller {

    public function __construct(){
        parent::__construct();
        $this->checkLogin(Ext_Auth::CONTENT_EDIT);
        $this->waitTime = 1;
    }

    public function localList(){
        $articleId = $this->input->getIntval('id');
        $adminInfo = load_model('Admin')->getAdminInfo();
        if ($articleId){
            $localList = load_model('Attach')->getAll($articleId,0);
        }else{
            $localList = load_model('Attach')->getAll(0,$adminInfo['uid']);
        }
        $this->output->set('localList',$localList);
        $this->output->display('attach_locallist.html');
    }

    public function delAttach(){
        $attachId = $this->input->get('id');
        if (is_array($attachId)){
            foreach($attachId as $value){
                load_model('Attach')->del($value);
            }
        }else{
            load_model('Attach')->del($attachId);
        }
    }

    public function downHttp(){
        $this->output->setDataMode(true);
        $articleId = $this->input->getIntval('id');
        $content = strip_tags(stripcslashes($this->input->get('content')),
                '<img>');
        $patten = '/<img.*?src=[\'\"]?([^\s>\"\']+)[\'\"][^>]*>([^<]*)/is';
        if (preg_match_all($patten,$content,$arr)){
            if (!empty($arr[1])){
                $mod = load_model('Attach');
                foreach($arr[1] as $key=>$value){
                    $ext = $mod->getExt($value);
                    $data[] = array(
                            'uid'=>$this->adminInfo['uid'],
                            'article_id'=>$articleId,
                            'name'=>basename($value),
                            'remark'=>trim($arr[2][$key]),
                            'file'=>$value,
                            'ext'=>$ext,
                            'size'=>0,
                            'upload_time'=>Ext_Date::now(),
                            'type'=>1
                    );
                }
                $mod->add($data);
            }
        }else{
            show_msg('没有匹配到任何图片');
        }
    }

    /**
     * 保存网络图片到本地
     * 
     * @param
     *            mixed
     * @return void
     */
    public function saveByArticleId(){
        $articleId = $this->input->getIntval('id');
        $inframe = $this->input->get('inframe');
        $adminInfo = load_model('Admin')->getAdminInfo();
        $modAttach = load_model('Attach');
        $where = array(
                'a.type'=>1
        );
        $where[] = "a.try_count < 3";
        if ($articleId){
            $where['a.article_id'] = $articleId;
        }else{
            $where['a.article_id'] = 0;
            $where['a.uid'] = $adminInfo['uid'];
        }
        $actionUrl = "?c=Attach&a=saveByArticleId&id=$articleId&inframe=$inframe";
        $this->_doSaveHttp($where,$actionUrl);
        if ($inframe){
            js_run('parent.getAttachList()');
            show_msg('全部图片成功保存','?c=Frame',$this->waitTime,'?c=Frame');
        }else{
            show_msg('全部图片成功保存','?c=Article&a=show',$this->waitTime);
        }
    }

    /**
     * 保存所有远程图片到本地
     * 
     * @param
     *            mixed
     * @return void
     */
    public function saveHttp(){
        $id = $this->input->get('id');
        $where = array(
                'a.type'=>1
        );
        $where[] = "a.try_count < 3";
        if ($id){
            if (!is_array($id)){
                $id = explode(':',$id);
            }
            $where['a.id'] = $id;
        }
        $actionUrl = "?c=Attach&a=saveHttp";
        if ($id){
            $actionUrl .= "&id=" . implode(':',$id);
        }
        $this->_doSaveHttp($where,$actionUrl);
        show_msg('全部图片成功保存','?c=Attach&a=show',$this->waitTime);
    }

    private function _doSaveHttp($where, $actionUrl){
        $saveSize = 1;
        $modAttach = load_model('Attach');
        $modArticle = load_model('Article');
        $total = $modAttach->getTotalNum($where);
        $attachList = load_model('Attach')->search($where,$saveSize);
        if ($attachList){
            foreach($attachList as $value){
                $attachFile = $modAttach->makeAttachName() . '.' . $value['ext'];
                $miniFile = $modAttach->getThumbAttach($attachFile);
                $attachPath = $modAttach->getAttachPath($attachFile);
                $miniPath = $modAttach->getAttachPath($miniFile);
                
                // 保存到本地
                if (!$modAttach->saveHttp($value['file'],$attachPath)){
                    $modAttach->set($value['id'],"try_count = try_count + 1");
                    if ($this->input->get('inframe')){
                        $msg = $value['file'] . ": 保存失败";
                    }else{
                        $msg = $value['file'] . "<br>保存失败, 请检查源文件是否存在, 或者是否防盗链";
                    }
                    show_msg($msg);
                }
                
                $size = $modAttach->dealImage($attachPath,$miniPath,$attachFile,
                        $miniFile);
                $data = array(
                        'file'=>$attachFile,
                        'size'=>$size,
                        'type'=>0
                );
                $modAttach->set($value['id'],$data);
                
                if ($value['cover'] == $value['file']){
                    $modArticle->set($value['article_id'],
                            array(
                                    'cover'=>$attachFile
                            ));
                }
                
                if ($this->input->get('inframe')){
                    $msg = "正在保存网络图片, 还有 $total 张, 稍后继续...";
                }else{
                    $msg = '<b>[From:]</b> ' . $value['file'] .
                             '<br><b>[SaveTo:]</b> ' . $attachFile .
                             "<br><b>[State:]</b> 正在保存网络图片, 还有 $total 张, 稍后继续...";
                }
                show_msg($msg,$actionUrl,$this->waitTime);
            }
        }
    }

    /**
     * 设置图片状态
     * 
     * @param
     *            mixed
     * @return void
     */
    public function setStatus(){
        $attachId = $this->input->get('id');
        $status = $this->input->getIntval('status');
        if (is_array($attachId)){
            foreach($attachId as $value){
                load_model('Attach')->set($value,array(
                        'status'=>$status
                ));
            }
        }else{
            load_model('Attach')->set($attachId,array(
                    'status'=>$status
            ));
        }
    }

    /**
     * 图片管理
     * 
     * @param
     *            mixed
     * @return void
     */
    public function show(){
        $p = $this->input->getIntval('p');
        $type = $this->input->get('type');
        $status = $this->input->get('status');
        $order = $this->input->get('order');
        $by = $this->input->get('by');
        $modCate = load_model('Cate');
        $modAttach = load_model('Attach');
        $cateList = $modCate->getList();
        $where = array();
        $where = array();
        if (Ext_Valid::check($type,'number')){
            $where['a.type'] = $type;
        }
        if (Ext_Valid::check($status,'number')){
            $where['a.status'] = $status;
        }
        if (!$order){
            $order = 'a.id';
        }
        if (!$by){
            $by = 'DESC';
        }
        $totalNum = $modAttach->getTotalNum($where);
        $url = "javascript:showpage('@')";
        $pageInfo = new Ext_Page($url,$totalNum,$p,
                Wee::$config['web_admin_pagenum']);
        $attachList = load_model('Attach')->search($where,$pageInfo->limit(),
                $order,$by);
        $this->output->set(
                array(
                        'type'=>$type,
                        'status'=>$status,
                        'order'=>$order,
                        'by'=>$by,
                        'p'=>$p,
                        'attachList'=>$attachList,
                        'cateList'=>$cateList,
                        'upload_thumb_w'=>Wee::$config['upload_thumb_w'],
                        'upload_thumb_h'=>Wee::$config['upload_thumb_h'],
                        'pageHtml'=>$pageInfo->html()
                ));
        $this->output->display('attach_show.html');
    }
}