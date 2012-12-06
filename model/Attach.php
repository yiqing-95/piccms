<?php

/**
 * Attach附件模型
 * 
 * @author 小鱼哥哥
 *         @time 2011-9-6 15:33
 * @version 1.0
 */
class Attach_Model extends Model {

    /**
     * initModel
     * 
     * @param
     *            mixed
     * @return void
     */
    public function __construct(){
        parent::__construct();
    }

    public function getTotalNum($where = array()){
        if ($where){
            $this->db->where($where);
        }
        $res = $this->db->field('COUNT(*) AS num')
            ->table('#@_attach as a')
            ->getOne();
        return $res['num'];
    }

    public function search($where = array(), $limit = "0, 10", $order = 'a.id', $by = 'DESC'){
        if ($where){
            $this->db->where($where);
        }
        $rs = $this->db->field('a.*, b.title, b.cid, b.cover')
            ->table(
                '#@_attach as a LEFT JOIN #@_article as b ON b.id = a.article_id')
            ->order("$order $by")
            ->limit($limit)
            ->getAll();
        $arr = array();
        foreach($rs as $value){
            $value = $this->getVo($value);
            $arr[] = $value;
        }
        return $arr;
    }

    public function getAll($articleId, $uid = 0, $type = null){
        if ($uid){
            $where = array(
                    'article_id'=>$articleId,
                    'uid'=>$uid
            );
        }else{
            $where = array(
                    'article_id'=>$articleId
            );
        }
        if (!is_null($type)){
            $where['type'] = $type;
        }
        $rs = $this->db->table('#@_attach')
            ->where($where)
            ->getAll();
        $arr = array();
        foreach($rs as $value){
            $value = $this->getVo($value);
            $arr[] = $value;
        }
        return $arr;
    }

    /**
     * 获取附件数量
     * 
     * @param
     *            mixed
     * @return void
     */
    public function getAttachNum($articleId){
        $res = $this->db->field('COUNT(*) AS num')
            ->table('#@_attach')
            ->where(array(
                'article_id'=>$articleId,
                'status'=>1
        ))
            ->getOne();
        return $res['num'];
    }

    /**
     * 获取附件列表
     * 
     * @param
     *            mixed
     * @return void
     */
    public function getAttachList($articleId, $limit = null){
        if ($limit){
            $this->db->limit($limit);
        }
        $rs = $this->db->table('#@_attach')
            ->where(array(
                'article_id'=>$articleId,
                'status'=>1
        ))
            ->getAll();
        $arr = array();
        foreach($rs as $value){
            $value = $this->getVo($value);
            $arr[] = $value;
        }
        return $arr;
    }

    /**
     * 获取缩略图
     * 
     * @param
     *            mixed
     * @return void
     */
    public function getThumbList($articleId){
        $rs = $this->db->table('#@_attach')
            ->field('file, type')
            ->where(array(
                'article_id'=>$articleId,
                'status'=>1
        ))
            ->getAll();
        $arr = array();
        $modArticle = load_model('Article');
        foreach($rs as $key=>$value){
            $value = $this->getVo($value);
            $value['article_url'] = $modArticle->getUrl($articleId,$key + 1);
            $arr[] = $value;
        }
        return $arr;
    }

    public function getOne($attachId){
        $rs = $this->db->table('#@_attach')
            ->where(array(
                'id'=>$attachId
        ))
            ->getOne();
        $rs = $this->getVo($rs);
        return $rs;
    }

    public function getVo($data){
        if (0 == $data['type']){
            $data['url'] = $this->getAttachUrl($data['file']);
            $data['thumb_file'] = $this->getThumbAttach($data['file']);
            $data['thumb_url'] = $this->getAttachUrl($data['thumb_file']);
        }else{
            $data['url'] = $data['file'];
            $data['thumb_file'] = $data['file'];
            $data['thumb_url'] = $data['file'];
        }
        if (isset($data['upload_time'])){
            $data['upload_time'] = Ext_Date::format($data['upload_time']);
        }
        if (isset($data['size'])){
            $data['size'] = Ext_Math::lifeByte($data['size']);
        }
        return $data;
    }

    /**
     * 处理图片
     * 
     * @param
     *            mixed
     * @return integer 处理后的附件大小
     */
    public function dealImage($attachPath, $miniPath, $attachFile, $miniFile){
        // 自动裁剪大图片
        if (Wee::$config['upload_resize']){
            Ext_Image::cut($attachPath,$attachPath,Wee::$config['upload_max_w'],
                    Wee::$config['upload_max_h'],0,
                    Wee::$config['upload_cut_pct']);
        }
        // 生成缩略图
        Ext_Image::cut($attachPath,$miniPath,Wee::$config['upload_thumb_w'],
                Wee::$config['upload_thumb_h'],Wee::$config['upload_thumb_type'],
                Wee::$config['upload_cut_pct']);
        // 添加图片水印
        if (1 == Wee::$config['upload_water']){
            Ext_Image::water($attachPath,$attachPath,
                    Wee::$config['upload_water_img'],
                    Wee::$config['upload_water_pos'],
                    Wee::$config['upload_water_pct'],
                    Wee::$config['upload_cut_pct']);
        }
        // 添加文字水印
        if (2 == Wee::$config['upload_water']){
            if (!is_file(CORE_PATH . 'misc/font/simsun.ttc')){
                show_error('找不到字体文件 core/misc/font/simsun.ttc');
            }
            Ext_Image::text($attachPath,$attachPath,
                    Wee::$config['upload_water_text'],
                    Wee::$config['upload_water_bgcolor'],
                    Wee::$config['upload_water_textcolor'],
                    Wee::$config['upload_cut_pct']);
        }
        $size = @filesize($attachPath);
        // 转移到FTP
        if (Wee::$config['upload_ftp']){
            $ftp = $this->getFtpObj();
            $ftp->put($miniPath,$miniFile);
            @unlink($miniPath);
            $ftp->put($attachPath,$attachFile);
            @unlink($attachPath);
        }
        return $size;
    }

    public function add($data){
        $this->db->table('#@_attach')->insert($data);
        return $this->db->insertId();
    }

    public function set($id, $data){
        $rs = $this->db->table('#@_attach')
            ->where("id = $id")
            ->update($data);
        return $rs;
    }

    public function setTid($uid, $tid){
        $rs = $this->db->table('#@_attach')
            ->where(array(
                'uid'=>$uid,
                'tid'=>0
        ))
            ->update(array(
                'tid'=>$tid
        ));
        return $rs;
    }

    /**
     * 根据附件信息删除附件
     * 
     * @param
     *            mixed
     * @return void
     */
    public function delByInfo($info){
        $attachId = $info['id'];
        if (0 == $info['type']){
            if (Wee::$config['upload_ftp']){
                $ftp = $this->getFtpObj();
                $ftp->unlink($info['file']);
                $ftp->unlink($this->getThumbAttach($info['file']));
            }else{
                $path = $this->getAttachPath($info['file']);
                @unlink($path);
                $sPath = $this->getAttachPath(
                        $this->getThumbAttach($info['file']));
                @unlink($sPath);
            }
        }
        $this->db->table('#@_attach')
            ->where("id = $attachId")
            ->delete();
    }

    public function del($attachId){
        $info = $this->getOne($attachId);
        if ($info){
            $this->delByInfo($info);
        }
        return true;
    }

    public function makeAttachName(){
        if (!Wee::$config['upload_style']){
            Wee::$config['upload_style'] = 'Y-m';
        }
        return date(Wee::$config['upload_style']) . '/' . time() .
                 Ext_String::getSalt();
    }

    public function getAttachPath($attach){
        return APP_PATH . Wee::$config['upload_path'] . '/' . $attach;
    }

    public function getThumbAttach($attach, $suffix = '_s'){
        if (!$attach){
            return false;
        }
        $pInfo = pathinfo($attach);
        if (empty($pInfo['extension'])){
            return false;
        }
        if (empty($pInfo['filename'])){
            $pInfo['filename'] = substr($pInfo['basename'],0,
                    -(strlen($pInfo['extension']) + 1));
        }
        $sFile = $pInfo['dirname'] . '/' . $pInfo['filename'] . $suffix . '.' .
                 $pInfo['extension'];
        return $sFile;
    }

    public function getExt($attach){
        $ext = Ext_File::getExt($attach);
        if (!in_array($ext,array(
                'jpg',
                'gif',
                'png',
                'jpeg'
        ))){
            $ext = 'jpg';
        }
        return $ext;
    }

    /**
     * 将远程文件下载到本地
     * 
     * @param
     *            mixed
     * @return void
     */
    public function saveHttp($remoteFile, $localFile){
        $content = Ext_Network::openUrl($remoteFile);
        if (strlen($content) < 100){
            return false;
        }
        return Ext_File::write($localFile,$content);
    }

    /**
     * 生成指定大小的图片
     * 
     * @param
     *            mixed
     * @return void
     */
    public function makeImage($src, $width = 0, $height = 0, $type = 0){
        if ($width && $height){
            $ext = $this->getExt($src);
            $hash = substr(md5($src),8,16);
            $hash = substr($hash,0,2) . '/' . substr($hash,2,2) . '/' . $hash;
            $sFile = 'thumb/' . $hash . "_{$width}_{$height}_{$type}.{$ext}";
            $sPath = $this->getAttachPath($sFile);
            $sUrl = $this->getLocalUrl($sFile);
            if (is_file($sPath)){
                return $sUrl;
            }
            // 处理FTP图片
            if (Wee::$config['upload_ftp']){
                $src = Wee::$config['upload_ftp_url'] . $src;
            }
            // 处理网络图片
            if ($this->isHttp($src)){
                if (!Wee::$config['upload_http_thumb']){
                    return $src;
                }
                $tFile = 'temp/' . $hash . ".{$ext}";
                $tPath = $this->getAttachPath($tFile);
                if (!is_file($tPath)){
                    $this->saveHttp($src,$tPath);
                }
            }else{
                $tPath = $this->getAttachPath($src);
            }
            // 生成缩略图
            if (is_file($tPath)){
                $rs = Ext_Image::cut($tPath,$sPath,$width,$height,$type,
                        Wee::$config['upload_cut_pct']);
            }
            // 输出缩略图
            if (is_file($sPath)){
                return $sUrl;
            }else{
                return false;
            }
        }else{
            if (Wee::$config['upload_ftp']){
                $src = $this->getFtpUrl($src);
            }
            if ($this->isHttp($src)){
                return $src;
            }else{
                return $this->getLocalUrl($src);
            }
        }
    }

    /**
     * 获取本地附件地址
     * 
     * @param
     *            mixed
     * @return void
     */
    public function getLocalUrl($attach){
        return Wee::$config['web_url'] . Wee::$config['upload_path'] . '/' .
                 $attach;
    }

    /**
     * 获取FTP附件地址
     * 
     * @param
     *            mixed
     * @return void
     */
    public function getFtpUrl($attach){
        return Wee::$config['upload_ftp_url'] . $attach;
    }

    /**
     * 获取附件的URL地址
     * 
     * @param
     *            mixed
     * @return void
     */
    public function getAttachUrl($attach, $isLocalFile = false){
        if ($this->isHttp($attach)){
            return $attach;
        }
        if (!$isLocalFile && Wee::$config['upload_ftp']){
            $url = $this->getFtpUrl($attach);
        }else{
            $url = $this->getLocalUrl($attach);
        }
        return $url;
    }

    /**
     * 获取FTP连接对象
     * 
     * @param
     *            mixed
     * @return void
     */
    public function getFtpObj(){
        $ftp = new Ext_Ftp(Wee::$config['upload_ftp_host'],
                Wee::$config['upload_ftp_port'],Wee::$config['upload_ftp_user'],
                Wee::$config['upload_ftp_pass']);
        $ftp->baseDir = Wee::$config['upload_ftp_dir'];
        $ftp->connect();
        return $ftp;
    }

    /**
     * 判断是否为网络文件
     * 
     * @param
     *            mixed
     * @return void
     */
    public function isHttp($attach){
        return (0 === strpos($attach,'http://'));
    }
}