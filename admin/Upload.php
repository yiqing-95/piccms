<?php

/**
 * 上传管理
 * 
 * @author YHS
 *         @time 2011-8-30 15:53
 * @version 1.0
 */
class Upload_Controller extends Base_Controller {

    public function __construct(){
        parent::__construct();
    }

    public function index(){
        $uid = $this->input->getIntval('uid');
        $articleId = $this->input->getIntval('id');
        $hash = $this->input->get('hash');
        if ($hash != md5(Wee::$config['encrypt_key'] . $uid . $articleId)){
            exit("Auth 验证失败");
        }
        $uInfo = load_model('Admin')->getByUid($uid);
        if (!$uInfo){
            exit('用户不存在');
        }
        $modAttach = load_model('Attach');
        $file = $modAttach->makeAttachName();
        $path = $modAttach->getAttachPath($file);
        try{
            $rs = Ext_Upload::save('Filedata',$path);
            if (!$rs['error']){
                $attachFile = $file . '.' . $rs['ext'];
                $miniFile = $modAttach->getThumbAttach($attachFile);
                $attachPath = $modAttach->getAttachPath($attachFile);
                $miniPath = $modAttach->getAttachPath($miniFile);
                $size = $modAttach->dealImage($attachPath,$miniPath,$attachFile,
                        $miniFile);
                $data = array(
                        'uid'=>$uInfo['uid'],
                        'article_id'=>$articleId,
                        'name'=>$rs['name'],
                        'remark'=>$rs['name'],
                        'file'=>$attachFile,
                        'ext'=>$rs['ext'],
                        'size'=>$size,
                        'upload_time'=>Ext_Date::now()
                );
                $id = $modAttach->add($data);
                echo 1;
            }else{
                echo $rs['errorMsg'];
            }
        }catch(Error $e){
            echo $e->getMessage();
        }
    }
}