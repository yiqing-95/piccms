<?php

/**
 * 附件
 *
 * @author YHS
 *         @time 2011-8-30 15:53
 * @version 1.0
 */
class Attach_Controller extends Base_Controller {

    public function __construct(){
        parent::__construct();
    }

    public function index(){
        $width = $height = $type = 0;
        
        // 出错时的图片
        $errImg = Wee::$config['web_url'] . 'images/nophoto.jpg';
        
        // 盗链时的图片
        $safeImg = Wee::$config['web_url'] . 'images/safephoto.jpg';
        
        $r = explode(',',Ext_String::base64UrlDecode(Wee::$input->get('r')));
        
        if (!is_array($r) || 5 != count($r)){
            exit('error');
        }
        list ($src,$width,$height,$type,$crc) = $r;
        
        // 参数效验
        if ($crc != substr(
                md5(
                        Wee::$config['encrypt_key'] . "$src,$width,$height,$type"),
                10,6)){
            header("Location:$errImg");
            exit();
        }
        
        // 处理防盗链
        if (Wee::$config['upload_safe_link']){
            if (isset($_SERVER['HTTP_REFERER'])){
                $reffer = parse_url($_SERVER['HTTP_REFERER']);
                $safeDomain = array();
                if (Wee::$config['upload_safe_domain']){
                    $safeDomain = explode("|",
                            Wee::$config['upload_safe_domain']);
                }
                $safeDomain[] = $_SERVER["HTTP_HOST"];
                if (!in_array($reffer['host'],$safeDomain)){
                    header("Location:$safeImg");
                    return;
                }
            }
        }
        
        $mod = load_model('Attach');
        $url = $mod->makeImage($src,$width,$height,$type);
        
        if (!$url){
            header("Location:$errImg");
            return;
        }
        
        // 输出图片
        header("Location:$url");
        /*
         * // 占用过多内存 if (Wee::$config['upload_safe_link'] && !empty($url[1])) {
         * $ctype = array( 'gif' => 'image/gif', 'png' => 'image/x-png', 'jpg'
         * => 'image/jpeg', 'jpeg' => 'image/jpeg', ); $ext =
         * $mod->getExt($src); ob_get_clean(); header("Content-Type:
         * {$ctype[$ext]}"); //header("Content-Disposition: attachment;
         * filename=\"".basename($url)."\";"); readfile($url[1]); } else {
         * header("Location:{$url[0]}"); }
         */
    }
}