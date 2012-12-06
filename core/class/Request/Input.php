<?php

/**
 * 传入数据对象
 * 
 * @author 小鱼哥哥
 *         @time 2011-9-2 15:28
 * @version 1.0
 */
class Request_Input {

    /**
     *
     * @var string 控制器名
     */
    public $controllerName;

    /**
     *
     * @var string 方法名
     */
    public $actionName;

    /**
     *
     * @var array 传入数据参数
     */
    public $args;

    /**
     * 初始化传入参数
     *
     * @param string $args
     *            传入变量
     * @return void
     */
    public function __construct($args = null){
        $this->controllerName = !empty(
                $args[Wee::$config['controller_var_name']]) ? $args[Wee::$config['controller_var_name']]:Wee::$config['default_controller'];
        $this->actionName = !empty($args[Wee::$config['action_var_name']]) ? $args[Wee::$config['action_var_name']]:Wee::$config['default_action'];
        $this->args = $args;
    }

    /**
     * 获取控制器名
     *
     * @return string 控制器名
     */
    public function getControllerName(){
        return $this->controllerName;
    }

    /**
     * 获取方法名
     *
     * @return string 方法名
     */
    public function getActionName(){
        return $this->actionName;
    }

    /**
     * 获取所有参数
     *
     * @return array 参数数组
     */
    public function getArgs(){
        if (!is_array($this->args)){
            $this->args = Ext_Array::objectToArray($this->args);
        }
        return $this->args;
    }

    /**
     * 获取参数值
     *
     * @param string $name
     *            参数名
     * @return mixed 参数值
     */
    public function get($name){
        return isset($this->args[$name]) ? $this->args[$name]:null;
    }

    /**
     * 设置参数值
     *
     * @param string $name
     *            参数名
     * @param string $value
     *            参数值
     * @return void
     */
    public function set($name, $value = null){
        if (is_object($name)){
            $name = get_object_vars($name);
        }
        if (is_array($name)){
            $this->args = array_merge((array)$this->args,$name);
        }else{
            $this->args[$name] = $value;
        }
    }

    public function __get($name){
        return $this->get($name);
    }

    public function __set($name, $value){
        $this->set($name,$value);
    }

    public function __isset($name){
        return isset($this->args[$name]);
    }

    /**
     * 获取整数型参数值
     *
     * @param string $name
     *            参数名
     * @return integer 整数型参数值
     */
    public function getIntval($name){
        return intval($this->get($name));
    }

    /**
     * 获取去掉空白的参数值
     *
     * @param string $name
     *            参数名
     * @return string 去掉空白的参数值
     */
    public function getTrim($name){
        return trim($this->get($name));
    }
}