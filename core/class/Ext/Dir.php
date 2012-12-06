<?php

/**
 * 目录管理扩展
 * 
 * @author 小鱼哥哥
 *         @time 2011-12-27 17:17
 * @version 1.0
 */
class Ext_Dir extends DirectoryIterator {

    const TYPE_DIR = 'DIR';

    const TYPE_FILE = 'FILE';

    const TYPE_ALL = 'ALL';

    /**
     * 创建目录
     *
     * @param string $directory
     *            目录路径
     * @param mixed $mode
     *            目录权限
     * @return Boolean
     */
    public static function mkDirs($directory, $mode = 0775){
        $rs = mkdir($directory,$mode,true);
        if ($rs){
            $rs = @chmod($directory,$mode);
        }
        return $rs;
    }

    /**
     * 删除目录
     *
     * @param string $directory
     *            目录路径
     * @param Boolean $subdir
     *            是否删除子目录
     * @return void
     */
    public static function delDir($directory, $subdir = true){
        if (is_dir($directory) == false){
            // exit("The Directory Is Not Exist!");
            return false;
        }
        $handle = opendir($directory);
        while(($file = readdir($handle)) !== false){
            if ($file != "." && $file != ".."){
                is_dir("$directory/$file") ? self::delDir("$directory/$file"):unlink(
                        "$directory/$file");
            }
        }
        if (readdir($handle) == false){
            closedir($handle);
            rmdir($directory);
        }
    }

    /**
     * 删除目录下面的所有文件
     *
     * @param string $directory
     *            目录路径
     * @param Boolean $subdir
     *            是否删除子目录
     * @return void
     */
    public static function del($directory, $subdir = true){
        if (is_dir($directory) == false){
            // exit("The Directory Is Not Exist!");
            return false;
        }
        $handle = opendir($directory);
        while(($file = readdir($handle)) !== false){
            if ($file != "." && $file != ".."){
                if (is_file("$directory/$file")){
                    unlink("$directory/$file");
                }elseif (is_dir("$directory/$file") && true == $subdir){
                    self::deldir("$directory/$file",$subdir);
                }
            }
        }
        closedir($handle);
    }

    /**
     * 复制目录
     *
     * @param string $source
     *            源目录路径
     * @param string $destination
     *            目标目录路径
     * @return void
     */
    public static function copyDir($source, $destination){
        if (is_dir($source) == false){
            exit("The Source Directory Is Not Exist!");
        }
        if (is_dir($destination) == false){
            Ext_Dir::mkDirs($destination,0700);
        }
        $handle = opendir($source);
        while(false !== ($file = readdir($handle))){
            if ($file != "." && $file != ".." && $file != ".svn"){
                is_dir("$source/$file") ? self::copyDir("$source/$file",
                        "$destination/$file"):copy("$source/$file",
                        "$destination/$file");
            }
        }
        closedir($handle);
    }

    /**
     * 获取目录列表
     *
     * @param string $source
     *            源目录路径
     * @param string $type
     *            类型 DIR: 目录, FILE: 文件, ALL: 文件和目录
     * @param array $no
     *            排除文件, 默认排除 ., ..
     * @param array $ext
     *            指定扩展名
     * @return array 目录列表
     */
    public static function getDirList($source, $type = 'ALL', $no = array(), $ext = array()){
        if (is_dir($source) == false){
            return array();
        }
        $handle = opendir($source);
        $dirlist = array();
        array_push($no,'.');
        array_push($no,'..');
        while(false !== ($file = readdir($handle))){
            if (!in_array($file,$no)){
                if ($type == 'DIR' && !is_dir($source . '/' . $file)){
                    continue;
                }
                if ($type == 'FILE' && !is_file($source . '/' . $file)){
                    continue;
                }
                if (!empty($ext)){
                    if (is_array($ext)){
                        $rs = in_array(end(explode('.',$file)),$ext);
                    }else{
                        $rs = in_str(end(explode('.',$file)),$ext);
                    }
                    if (!$rs){
                        continue;
                    }
                }
                $dirlist[] = $file;
            }
        }
        closedir($handle);
        return $dirlist;
    }

    /**
     * 获取目录树
     * 所有文件夹和文件
     * 
     * @param string $source
     *            源目录路径
     * @param string $type
     *            类型 DIR: 目录, FILE: 文件, ALL: 文件和目录
     * @param array $no
     *            排除文件, 默认排除 ., ..
     * @param array $ext
     *            指定扩展名
     * @return array 目录列表
     */
    public static function getDirTree($source, $ext = array()){
        $list = self::getDirList($source,$type = 'ALL',
                $no = array(
                        '.',
                        '..',
                        '.svn'
                ),$ext);
        $tree = array();
        foreach($list as $value){
            if (is_dir($source . '/' . $value)){
                $tree[$value] = self::getDirTree($source . '/' . $value);
            }else{
                $tree[] = $value;
            }
        }
        return $tree;
    }
}
