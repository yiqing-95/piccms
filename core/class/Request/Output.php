<?php

/**
 * 传出数据对象
 * 
 * @author 小鱼哥哥
 *         @time 2011-12-27 17:20
 * @version 1.0
 */
class Request_Output {

    /**
     *
     * @var string 控制器名
     */
    public $controllerName = null;

    /**
     *
     * @var string 方法名
     */
    public $actionName = null;

    /**
     *
     * @var integer 传出状态
     */
    public $state = 0;

    /**
     *
     * @var mixed 数据模式
     */
    public $dataMode = false;

    /**
     *
     * @var array 传出数据参数
     */
    public $data = array();

    /**
     * 构造方法
     *
     * @return void
     */
    public function __construct(){}

    /**
     * 获取模板引擎对象
     * 
     * @return object 缓存对象
     */
    public function getCompiler(){
        if (!isset(Wee::$box['CompilerInstance'])){
            Wee::$box['CompilerInstance'] = new Template_Compiler();
        }
        return Wee::$box['CompilerInstance'];
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
        $this->getCompiler()->registerTag($name,$callback);
    }

    /**
     * 显示模板数据
     * 
     * @param string $tplFile
     *            模板文件
     * @return void
     */
    public function display($tplFile, $absPath = false){
        if (Wee::ENTRANCE_INDEX == Wee::$config['entrance']){
            if (is_file(
                    Wee::$config['view_path'] . Wee::$config['template_skin'] .
                             '/' . $tplFile)){
                $skin = Wee::$config['template_skin'];
            }else{
                $skin = 'default';
            }
        }elseif (Wee::ENTRANCE_ADMIN == Wee::$config['entrance']){
            $skin = 'admin';
        }elseif (Wee::ENTRANCE_INSTALL == Wee::$config['entrance']){
            $skin = 'install';
        }
        $realTplFile = Wee::$config['view_path'] . $skin . '/' . $tplFile;
        $compileFile = Wee::$config['data_path'] . 'tpl_compile/' . $skin . '/' .
                 $tplFile . '.php';
        if ($this->getCompiler()->getCompileFile($realTplFile,$compileFile)){
            error_reporting(E_ALL & ~E_NOTICE);
            include $compileFile;
        }
    }

    /**
     * 返回显示数据
     * 
     * @param
     *            mixed
     * @return void
     */
    public function makeHtml($tplFile = null, $htmlFile = null, $absPath = false){
        $this->display($tplFile,$absPath);
        $content = ob_get_contents();
        ob_end_clean();
        if ($htmlFile){
            Ext_File::write($htmlFile,$content);
        }
        return $content;
    }

    /**
     * 设置方法名
     *
     * @param string $actionName
     *            方法名
     * @return void
     */
    public function setActionName($actionName){
        $this->actionName = $actionName;
    }

    /**
     * 设置控制器名
     *
     * @param string $actionName
     *            方法名
     * @return void
     */
    public function setControllerName($controllerName){
        $this->controllerName = $controllerName;
    }

    /**
     * 设置全部数据值
     *
     * @param array $data            
     * @return void
     */
    public function setData($data){
        $this->data = $data;
    }

    /**
     * 设置传出状态
     *
     * @param integer $state            
     * @return void
     */
    public function setState($state = 1){
        $this->state = $state;
    }

    /**
     * 设置输出模式
     * 
     * @param
     *            string 模板文件
     * @return void
     */
    public function setDataMode($mode = true){
        $this->dataMode = $mode;
    }

    /**
     * 获取数据值
     *
     * @param string $name
     *            参数键名
     * @return mixed 参数值
     */
    public function get($name){
        return isset($this->data[$name]) ? $this->data[$name]:null;
    }

    /**
     * 设置数据值
     *
     * @param string $name
     *            参数键名
     * @param mixed $value
     *            参数值
     * @return void
     */
    public function set($name, $value = null){
        if (is_object($name)){
            $name = get_object_vars($name);
        }
        if (is_array($name)){
            $this->data = array_merge((array)$this->data,$name);
        }else{
            $this->data[$name] = $value;
        }
    }

    public function __get($name){
        return $this->get($name);
    }

    public function __set($name, $value){
        $this->set($name,$value);
    }

    public function __isset($name){
        return isset($this->data[$name]);
    }
}