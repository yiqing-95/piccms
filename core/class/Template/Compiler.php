<?php

/**
 * 模板编译引擎
 * 
 * @author 小鱼哥哥
 *         @time 2011-10-11 14:55
 * @version 1.0
 */
class Template_Compiler {

    /**
     *
     * @var string 编译过后的模板
     */
    private $_compileFile;

    /**
     *
     * @var string 模板文件
     */
    private $_tplFile;

    /**
     *
     * @var bool 是否调试模式
     */
    public $_debug = false;

    /**
     *
     * @var array 注册的标签
     */
    private $_tags = array();

    /**
     *
     * @var array 替换标签
     */
    private $_pattern = array(
            '/\{\$([\w\'\"\[\]\$\-\.\x7f-\xff]+)\}/', // {$abc}
            '/\{@([^(]+\(.*?\))\}/', // {@tag()}
            '/\{if\s*([^}]+)\}/', // <!--{ if $a>$b }-->
            '/\{else\}/', // <!--{ else }-->
            '/\{elseif\s*([^}]+)\}/', // <!--{ elseif $a<$b }-->
            '/\{foreach\s+(.+?)\s+as\s+(\S+)\}/', // <!--{ loop $list as $val
                                                  // }-->
            '/\{foreach\s+(.+?)\s+as\s+(\S+)\s*\=\>\s*(\S+)\s*\}/', // <!--{
                                                                    // loop
                                                                    // $list as
                                                                    // $val }-->
            '/\{(\/if)\}/', // <!--{ /if }-->
            '/\{(\/foreach)\}/', // <!--{ /foreach }-->
            '/\{include\s*[\"\']?([^}\"\']+)[\"\']?\}/is', // <!--{ view ..}-->
            '/\{echo\s*([^}]+)\}/is', // <!--{ echo ..}-->
            '/\{#\s*([^}]+)\#}/is', // <!--{ # ..}-->
            '/\{eval\s*([^}]+)\}/is', // <!--{ eval ..}-->
            '/\{php\}/is', // <!--{ php }-->
            '/\{\/php\}/is' // <!--{ /php}-->
    ;

    private $_replace = array(
            "<?php echo \$\\1;?>",
            "<?php echo @\\1;?>",
            "<?php if(\\1){?>",
            "<?php } else{?>",
            "<?php } elseif(\\1){?>",
            "<?php foreach(\\1 as \\2){?>",
            "<?php foreach(\\1 as \\2 => \\3){?>",
            "<?php }\n?>",
            "<?php }\n?>",
            "<?php \$this->display('\\1');?>",
            "<?php echo \\1;?>",
            "<?php echo \\1;?>",
            "<?php \\1;?>",
            "<?php \n",
            "\n?>"
    );

    /**
     * 构造方法
     * 
     * @param string $tplDir
     *            模板目录
     * @param string $compileDir
     *            模板编译目录
     * @return void
     */
    public function __construct(){
        $this->_debug = Wee::$config['template_debug'];
        $this->registerTag(
                array(
                        'url'=>"url",
                        'cutstr'=>"Ext_String::cut",
                        'idate'=>"Ext_Date::format"
                ));
    }

    /**
     * 注册标签
     * 
     * @param mixed $name
     *            标签名
     * @param string $callback
     *            标签对应的解析方法
     * @return void
     */
    public function registerTag($name, $callback = null){
        if (is_array($name)){
            $this->_tags = array_merge($this->_tags,$name);
        }else{
            $this->_tags[$name] = $callback;
        }
    }

    /**
     * 显示模板数据
     * 
     * @param string $tplFile
     *            模板文件
     * @return void
     */
    public function getCompileFile($tplFile, $compileFile){
        $this->_tplFile = $tplFile;
        $this->_compileFile = $compileFile;
        if ($this->_debug || !is_file($this->_compileFile) ||
                 filemtime($this->_compileFile) < filemtime($this->_tplFile)){
            if (is_file($this->_tplFile)){
                $tplCon = Ext_File::read($this->_tplFile);
                $tplCon = $this->compile($tplCon);
                if (!Ext_File::write($this->_compileFile,$tplCon)){
                    show_error($this->_tplFile . ' :编译失败');
                }
            }else{
                show_error($this->_tplFile . ' :文件不存在');
            }
        }
        return true;
    }

    /**
     * 编译模板
     * 
     * @param
     *            string 模板内容
     * @return void
     */
    public function compile($tplCon){
        // 转换<!--{}-->到 {}
        $pattern = '/<!--\{\s*([^\}]+)\s*\}-->/';
        $tplCon = preg_replace($pattern,'{$1}',$tplCon);
        // 转换操作符
        $tplCon = preg_replace($this->_pattern,$this->_replace,$tplCon);
        
        /*
         * // 解析循环标签 $tmptags = implode('|', array_keys($this->_tags)); $pattern
         * = "/\{($tmptags)\b([^}]*)\}(.+?)\{\/\\1\}/se"; $tplCon =
         * preg_replace($pattern, "\$this->_parseLoopTag('$1', '$2', '$3')",
         * $tplCon); // 解析单个标签 $pattern = "/\{($tmptags)\b([^}]*)\}/se"; $tplCon
         * = preg_replace($pattern, "\$this->_parseTag('$1', '$2')", $tplCon);
         */
        
        // 解析变量和标签
        $pattern = '/(\<\?php(.+?)\?\>)/se';
        $tplCon = preg_replace($pattern,"\$this->_parseCode('$0')",$tplCon);
        
        // 添加编译信息
        $tplCon = "<?php\n /* compiled by (WeePHP) at (" . date('Y-m-d H:i:s') .
                 ") */\n?>\n" . $tplCon;
        
        // 合并 php起始标记
        $pattern = '/\?\>\s*\<\?php/s';
        $tplCon = preg_replace($pattern,"\n",$tplCon);
        return $tplCon;
    }

    /**
     * 解板循标签
     * 
     * @param
     *            mixed
     * @return void
     */
    private function _parseLoopTag($name, $args, $content){
        $argsArr = $this->_parseArgs($args);
        $str = "<?php \$_tmpList = " . $this->_tags[$name] . "(" .
                 $this->_getVarExport($argsArr) .
                 "); foreach(\$_tmpList as \$key => \$val){?>" .
                 stripslashes($content) . "<?php }?>";
        return $str;
    }

    /**
     * 解析单个标签
     * 
     * @param
     *            mixed
     * @return void
     */
    private function _parseTag($name, $args){
        $argsArr = $this->_parseArgs($args);
        $str = "<?php echo " . $this->_tags[$name] . "(" .
                 $this->_getVarExport($argsArr) . ")?>";
        return $str;
    }

    /**
     * 解析参数
     * 
     * @param
     *            mixed
     * @return void
     */
    private function _parseArgs($args){
        $pattern = '/(\S+)\s*=\s*?([^\s\}]+)/se';
        $argsArr = array();
        if (preg_match_all($pattern,$args,$arr)){
            $argsArr = array_combine($arr[1],$arr[2]);
        }
        return $argsArr;
    }

    /**
     * 获取数变量的字符串表示
     * 
     * @param
     *            mixed
     * @return void
     */
    private function _getVarExport($var){
        $str = '';
        if (is_array($var)){
            $str = 'array(';
            foreach($var as $key=>$value){
                $str .= "'$key' => $value,";
            }
            $str .= ')';
        }else{
            $str = $var;
        }
        return $str;
    }

    /**
     * 解板变量和标签
     * 
     * @param
     *            mixed
     * @return void
     */
    private function _parseCode($code){
        // 转换标签
        $pattern = '/@([\w\-\>\:\x7f-\xff]+?)\(/se';
        $code = preg_replace($pattern,"\$this->_parseTags('$1')",$code);
        // 转换数组
        $pattern = '/((\$[\w\-\>\x7f-\xff]+)(\.[\w\-\>\.\"\'\[\]\$\x7f-\xff]+)+?)/se';
        $code = preg_replace($pattern,"\$this->_parseArray('$0')",$code);
        // 给数组[] 加上引号
        $pattern = '/((\$[\w\-\>\x7f-\xff]+)(\[[\w\-\>\.\"\'\[\]\$\x7f-\xff]+\])+?)/se';
        $code = preg_replace($pattern,"\$this->_addQuote('$0')",$code);
        // 替换本地变量
        $pattern = '/\$([\w\x7f-\xff]+)/se';
        $code = preg_replace($pattern,"\$this->_addVar('$1')",$code);
        return stripslashes($code);
    }

    /**
     * 解析标签
     * 
     * @param
     *            mixed
     * @return void
     */
    private function _parseTags($var){
        if (isset($this->_tags[$var])){
            if (is_array($this->_tags[$var])){
                return "load_model('" . $this->_tags[$var][0] . "')->" .
                         $this->_tags[$var][1] . "(";
            }else{
                return $this->_tags[$var] . '(';
            }
        }else{
            return $var . '(';
        }
    }

    /**
     * 解析数组
     * 
     * @param
     *            mixed
     * @return void
     */
    private function _parseArray($var){
        $pattern = '/\.([\w\-\x7f-\xff]+)/s';
        return preg_replace($pattern,"['$1']",$var);
    }

    /**
     * 给数组补全引号
     * 
     * @param
     *            mixed
     * @return void
     */
    private function _addQuote($var){
        $pattern = '/\[([\w\-\.\x7f-\xff]+)\]/s';
        return preg_replace($pattern,"['$1']",$var);
    }

    /**
     * 替换成引警内部变量
     * 
     * @param
     *            mixed
     * @return void
     */
    private function _addVar($var){
        if ('this' == $var){
            return "$" . $var;
        }else{
            return '$this->data[\'' . $var . '\']';
        }
    }
}
