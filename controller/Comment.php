<?php

/**
 * 评论
 *
 * @author 小鱼哥哥
 *         @time 2011-12-7 15:56
 * @version 1.0
 */
class Comment_Controller extends Base_Controller {

    public function __construct(){
        parent::__construct();
    }

    public function index(){
        $this->assignData();
        $id = $this->input->getIntval('id');
        $p = max($this->input->getIntval('p'),1);
        $article = load_model('Article')->get($id,false);
        $modComment = load_model('Comment');
        $where = array(
                'article_id'=>$id
        );
        $total = $modComment->getTotal($where);
        $url = "javascript:loadComment($id, '@')";
        $pageInfo = new Ext_Page($url,$total,$p,
                Wee::$config['web_comment_pagenum']);
        $list = $modComment->getAll($where,$pageInfo->limit());
        foreach($list as $key=>& $value){
            $value['floor'] = $total -
                     ($p - 1) * Wee::$config['web_comment_pagenum'] - $key;
        }
        unset($value);
        $userName = Cookie::get('p_c_username');
        $clientIp = Ext_Network::getClientIp();
        $this->output->set('userName',$userName);
        $this->output->set('clientIp',$clientIp);
        $this->output->set('article',$article);
        $this->output->set('total',$total);
        $this->output->set('list',$list);
        $this->output->set('pageHtml',$pageInfo->html());
        $this->output->set('web_comment_vcode',
                Wee::$config['web_comment_vcode']);
        $this->output->display('comment.html');
    }

    public function post(){
        if (!Wee::$config['web_comment']){
            $this->_showjs("alert('评论已关闭')");
            return;
        }
        $data = array(
                'article_id'=>$this->input->getIntval('id'),
                'title'=>$this->input->getTrim('title'),
                'user_name'=>$this->input->getTrim('user_name'),
                'content'=>strip_tags($this->input->getTrim('content')),
                'ip'=>Ext_Network::getClientIp(),
                'status'=>1,
                'dateline'=>time()
        );
        if (Wee::$config['web_comment_status']){
            $data['status'] = 0;
        }
        if (!$data['user_name']){
            $this->_showjs("alert('请输入昵称')");
            return;
        }
        if (!$data['content']){
            $this->_showjs("alert('评论内容不能为空')");
            return;
        }
        if (Ext_String::strlen($data['content']) > 250){
            $this->_showjs("alert('评论内容不能超过250个字符')");
            return;
        }
        if (Wee::$config['web_comment_vcode']){
            $vcode = $this->input->getTrim('vcode');
            if (!$vcode ||
                     (strtolower($vcode) != strtolower(Cookie::get('p_c_vcode')))){
                $this->_showjs("alert('验证码不正确')");
                return;
            }
        }
        $modComment = load_model('Comment');
        // 跟贴
        $replyId = $this->input->get('reply_id');
        $replyInfo = $modComment->get($replyId);
        if ($replyInfo){
            $data['content'] = addslashes(
                    "<fieldset class='follow_reply'>
				<legend class='follow_title'>引用 {$replyInfo['user_name']} 的评论:</legend>
				<div class='follow_content'>{$replyInfo['content']}</div>
			    </fieldset>") . $data['content'];
        }
        // dump($data);
        // exit;
        $modComment->add($data);
        Cookie::set('p_c_username',$data['user_name']);
        $this->_showjs(
                "alert('发表评论成功');parent.loadComment({$data['article_id']})");
    }

    public function up(){
        $id = $this->input->getIntval('id');
        load_model('Comment')->upRelpy($id);
    }

    private function _showjs($js){
        echo "<script>$js</script>";
    }

    public function vcode(){
        $vcode = Ext_String::getSalt();
        Cookie::set('p_c_vcode',$vcode);
        Ext_Image::vcode($vcode,90,22);
    }
}