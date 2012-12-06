<?php

/**
 * 图片处理扩展
 * 
 * @author 小鱼哥哥
 *         @time 2011-12-27 17:17
 * @version 1.0
 */
class Ext_Image {

    /**
     *
     * @var boolean 是否处理GIF
     */
    public static $doGif = true;

    /**
     *
     * @var boolean 是否放大图片
     */
    public static $cutZoom = false;

    /**
     *
     * @var array 支持的图片类型
     */
    private static $_imageExts = array(
            1=>'gif',
            2=>'jpg',
            3=>'png'
    );

    /**
     * 生成验证码
     *
     * @param string $text
     *            要生成的字符
     * @param integer $width
     *            生成的图片宽度
     * @param integer $height
     *            生成的图片高度
     * @return void 输出验证码图片
     */
    public static function vcode($text = 'TEST', $width = 60, $height = 20){
        $num = strlen($text);
        $im = imagecreatetruecolor($width,$height);
        $back_color = imagecolorallocate($im,250,250,250);
        $boder_color = imagecolorallocate($im,200,200,200);
        imagefilledrectangle($im,0,0,$width,$height,$back_color);
        imagerectangle($im,0,0,$width - 1,$height - 1,$boder_color);
        for($i = 0; $i < strlen($text); $i++){
            $x = floor(($width - 5) / $num) * $i + 5;
            $y = mt_rand(0,$height - 15);
            $text_color = imagecolorallocate($im,mt_rand(0,255),mt_rand(0,128),
                    mt_rand(0,255));
            imagechar($im,5,$x,$y,$text{$i},$text_color);
        }
        for($i = 0; $i < $width; $i++){
            $dis_color = imagecolorallocate($im,mt_rand(0,255),mt_rand(0,255),
                    mt_rand(0,255));
            imagesetpixel($im,mt_rand(0,$width),mt_rand(0,$height),$dis_color);
        }
        @ob_end_clean();
        header(
                "Cache-Control: max-age=1, s-maxage=1, no-cache, must-revalidate");
        header("Content-type: image/png");
        imagepng($im);
        imagedestroy($im);
    }

    /**
     * 裁剪图片
     *
     * @param string $oldFile
     *            原图路径
     * @param string $newFile
     *            新图路径
     * @param integer $newW
     *            新图宽度
     * @param integer $newH
     *            新图高度
     * @param integer $cutType
     *            裁剪类型 0: 等比列, 1: 居中, 2: 左上
     * @param integer $pct
     *            清晰度
     * @return mixed
     */
    public static function cut($oldFile, $newFile, $newW, $newH, $cutType = 0, 
            $pct = 80){
        $info = self::getInfo($oldFile);
        $oldW = $info['width'];
        $oldH = $info['height'];
        $type = $info['type'];
        $ext = $info['ext'];
        unset($info);
        $justCopy = false;
        if ('gif' == $ext && false == self::$doGif){
            $justCopy = true;
        }
        if ($oldW < $newW && $oldH < $newH && false == self::$cutZoom){
            $justCopy = true;
        }
        if (true == $justCopy){
            if (!is_dir(dirname($newFile))){
                Ext_Dir::mkDirs(dirname($newFile),0777);
            }
            $flag = @copy($oldFile,$newFile);
            return $flag;
        }
        // 等比列
        if (0 == $cutType){
            $scale = min($newW / $oldW,$newH / $oldH); // 计算缩放比例
            $width = (int)($oldW * $scale); // 缩略图尺寸
            $height = (int)($oldH * $scale);
            $startW = $startH = 0;
            $endW = $oldW;
            $endH = $oldH;
        }        // center center 裁剪
        elseif (1 == $cutType){
            $scale1 = round($newW / $newH,2);
            $scale2 = round($oldW / $oldH,2);
            if ($scale1 > $scale2){
                $endH = round($oldW / $scale1,2);
                $startH = ($oldH - $endH) / 2;
                $startW = 0;
                $endW = $oldW;
            }else{
                $endW = round($oldH * $scale1,2);
                $startW = ($oldW - $endW) / 2;
                $startH = 0;
                $endH = $oldH;
            }
            $width = $newW;
            $height = $newH;
        }elseif (2 == $cutType){ // left top 裁剪
            $scale1 = round($newW / $newH,2);
            $scale2 = round($oldW / $oldH,2);
            if ($scale1 > $scale2){
                $endH = round($oldW / $scale1,2);
                $endW = $oldW;
            }else{
                $endW = round($oldH * $scale1,2);
                $endH = $oldH;
            }
            $startW = 0;
            $startH = 0;
            $width = $newW;
            $height = $newH;
        }else{
            self::showError($cutType . ' :裁剪类型错误');
        }
        $oldIm = self::createImFrom($oldFile,$type);
        $newIm = self::createIm($width,$height,$type);
        if ($type == 'jpeg') imageinterlace($newIm,1);
        self::copyIm($newIm,$oldIm,$startW,$startH,$endW,$endH,$width,$height);
        if ('' == pathinfo($newFile,PATHINFO_EXTENSION)){
            $newFile .= '.' . $ext;
        }
        $flag = self::saveIm($newIm,$newFile,$type,$pct);
        imagedestroy($oldIm);
        imagedestroy($newIm);
        return $flag;
    }

    public static function text($oldFile, $newFile, $text, $bgColor = '000000', 
            $textColor = 'ffffff', $pct = 80, $bgHeight = 20, $textSize = 10){
        $oldInfo = self::getInfo($oldFile);
        $oldImg = self::createImFrom($oldFile,$oldInfo['type']);
        $width = $oldInfo['width'];
        $height = $oldInfo['height'];
        $ext = $oldInfo['ext'];
        if ('gif' == $oldInfo['ext'] && false == self::$doGif){
            return true;
        }
        $newImg = imagecreatetruecolor($width,$height + $bgHeight);
        $bgColor = self::rgbColor($bgColor);
        $bgColor = imagecolorallocate($newImg,$bgColor['r'],$bgColor['g'],
                $bgColor['b']);
        $textColor = self::rgbColor($textColor);
        $textColor = imagecolorallocate($newImg,$textColor['r'],$textColor['g'],
                $textColor['b']);
        imagecopymerge($newImg,$oldImg,0,0,0,0,$width,$height,100);
        imagefilledrectangle($newImg,0,$height,$width,$height + $bgHeight,
                $bgColor);
        $font = CORE_PATH . 'misc/font/simsun.ttc';
        imagettftext($newImg,$textSize,0,5,$height + $bgHeight - 5,$textColor,
                $font,$text);
        $flag = self::saveIm($newImg,$newFile,$oldInfo['type'],$pct);
        imagedestroy($oldImg);
        imagedestroy($newImg);
        return $flag;
    }

    /**
     * 生成水印
     *
     * @param string $oldFile
     *            原图路径
     * @param string $newFile
     *            新图路径
     * @param integer $waterFile
     *            水印图路径
     * @param integer $waterPos
     *            水印位置 0: 随机, 1-9: 9宫格位置
     * @param integer $waterPct
     *            水印透明度
     * @return mixed
     */
    public static function water($oldFile, $newFile, $waterFile, $waterPos = 1, 
            $waterPct = 80, $pct = 80){
        $oldInfo = self::getInfo($oldFile);
        $oldW = $oldInfo['width'];
        $oldH = $oldInfo['height'];
        $oldImg = self::createImFrom($oldFile,$oldInfo['type']);
        $waterInfo = self::getInfo($waterFile);
        $waterW = $waterInfo['width'];
        $waterH = $waterInfo['height'];
        $waterImg = self::createImFrom($waterFile,$waterInfo['type']);
        $ext = $oldInfo['ext'];
        if ('gif' == $oldInfo['ext'] && false == self::$doGif){
            return true;
        }
        // 剪切水印
        $waterW > $oldW && $waterW = $oldW;
        $waterH > $oldH && $waterH = $oldH;
        
        // 水印位置
        switch($waterPos){
            case 0: // 随机
                $pos_x = rand(0,($oldW - $waterW));
                $pos_y = rand(0,($oldH - $waterH));
                break;
            case 1: // 1为顶端居左
                $pos_x = 0;
                $pos_y = 0;
                break;
            case 2: // 2为顶端居中
                $pos_x = ($oldW - $waterW) / 2;
                $pos_y = 0;
                break;
            case 3: // 3为顶端居右
                $pos_x = $oldW - $waterW;
                $pos_y = 0;
                break;
            case 4: // 4为中部居左
                $pos_x = 0;
                $pos_y = ($oldH - $waterH) / 2;
                break;
            case 5: // 5为中部居中
                $pos_x = ($oldW - $waterW) / 2;
                $pos_y = ($oldH - $waterH) / 2;
                break;
            case 6: // 6为中部居右
                $pos_x = $oldW - $waterW;
                $pos_y = ($oldH - $waterH) / 2;
                break;
            case 7: // 7为底端居左
                $pos_x = 0;
                $pos_y = $oldH - $waterH;
                break;
            case 8: // 8为底端居中
                $pos_x = ($oldW - $waterW) / 2;
                $pos_y = $oldH - $waterH;
                break;
            case 9: // 9为底端居右
                $pos_x = $oldW - $waterW;
                $pos_y = $oldH - $waterH;
                break;
            default: // 随机
                $pos_x = rand(0,($oldW - $waterW));
                $pos_y = rand(0,($oldH - $waterH));
                break;
        }
        // 设定图像的混色模式
        imagealphablending($oldImg,true);
        // 添加水印
        imagecopymerge($oldImg,$waterImg,$pos_x,$pos_y,0,0,$waterW,$waterH,
                $waterPct);
        $flag = self::saveIm($oldImg,$newFile,$oldInfo['type'],$pct);
        imagedestroy($oldImg);
        imagedestroy($waterImg);
        return $flag;
    }

    /**
     * 保存图片
     *
     * @param resource $im
     *            源图片资源
     * @param string $file
     *            要保存的文件名
     * @param string $type
     *            图片类型, 默认为 jpeg
     * @param integer $pct
     *            清晰度
     * @return boolean
     */
    public static function saveIm($im, $file, $type = 'jpeg', $pct = 80){
        if (!is_dir(dirname($file))){
            Ext_Dir::mkDirs(dirname($file),0777);
        }
        $fun = 'image' . $type;
        if ('jpeg' == $type){
            $flag = @$fun($im,$file,$pct);
        }else{
            $flag = @$fun($im,$file);
        }
        return $flag;
    }

    /**
     * 从文件创建图片源
     *
     * @param string $file
     *            图片文件名
     * @param string $type
     *            图片类型
     * @return resource 图片资源
     */
    public static function createImFrom($file, $type = 'jpeg'){
        $fun = 'imagecreatefrom' . $type;
        $im = $fun($file);
        return $im;
    }

    /**
     * 创建新的图片源
     *
     * @param integer $width
     *            图片宽度
     * @param integer $height
     *            图片高度
     * @param string $type
     *            图片类型
     * @return resource 图片源
     */
    public static function createIm($width, $height, $type = 'jpeg'){
        if ('gif' != $type && function_exists('imagecreatetruecolor')){
            $im = imagecreatetruecolor($width,$height);
        }else{
            $im = imagecreate($width,$height);
        }
        return $im;
    }

    /**
     * 合成图片源
     *
     * @param resource $newIm
     *            新图
     * @param resource $oldIm
     *            老图
     * @param integer $startW
     *            开始横坐标
     * @param integer $startH
     *            开始纵坐标
     * @param integer $endW
     *            结束横坐标
     * @param integer $endH
     *            结束纵坐标
     * @param integer $width
     *            宽度
     * @param integer $height
     *            高度
     * @return resource 合成后的图片
     */
    public static function copyIm($newIm, $oldIm, $startW, $startH, $endW, $endH, 
            $width, $height){
        if (function_exists("imagecopyresampled")){
            $fun = "imagecopyresampled";
        }else{
            $fun = "imagecopyresized";
        }
        $flag = $fun($newIm,$oldIm,0,0,$startW,$startH,$width,$height,$endW,
                $endH);
        return $flag;
    }

    /**
     * 获取图片信息
     *
     * @param string $file
     *            图片文件名
     * @return array 图片信息
     */
    public static function getInfo($file){
        $info = @getimagesize($file);
        if (empty($info)) self::showError($file . ' :这不是一张可用的图片');
        $info['type'] = substr($info['mime'],6);
        if (isset(self::$_imageExts[$info[2]])){
            $info['ext'] = self::$_imageExts[$info[2]];
        }else{
            self::showError($file . ' :不支持 ' . $info['mime'] . ' 类型文件');
        }
        $info['width'] = $info[0];
        $info['height'] = $info[1];
        return $info;
    }

    /**
     *
     * @param string $oldFile
     *            原图路径
     * @param string $newFile
     *            新图路径
     * @param integer $x
     *            原图x坐标
     * @param integer $y
     *            原图y坐标
     * @param integer $w
     *            原图选取宽度
     * @param integer $h
     *            原图选取高度
     * @param integer $targW
     *            新图保存宽度
     * @param integer $targH
     *            原图保存高度
     * @param integer $quality
     *            品质
     * @return void
     */
    public static function crop($oldFile, $newFile, $x, $y, $w, $h, $targW = 100, 
            $targH = 100, $quality = 90){
        $oldIm = self::createImFrom($oldFile);
        $newIm = self::createIm($targW,$targH);
        $rs = self::copyIm($newIm,$oldIm,$x,$y,$w,$h,$targW,$targH);
        self::saveIm($newIm,$newFile,'jpeg',$quality);
    }

    /**
     * 显示错误信息
     *
     * @param string $errorMsg
     *            错误信息
     * @return void
     */
    public static function showError($errorMsg){
        show_error($errorMsg);
    }

    /**
     * Web颜色转换成RGB
     * 
     * @param
     *            mixed
     * @return void
     */
    public static function rgbColor($color){
        if ('#' == $color{0}){
            $color = substr($color,1);
        }
        $color = unpack('Cr/Cg/Cb',pack('H*',$color));
        return $color;
    }
}