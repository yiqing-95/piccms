<?php

/**
 * 错误和异常处理
 * 
 * @author 小鱼哥哥
 *         @time 2011-12-27 17:14
 * @version 1.0
 */
class Error extends Exception {

    /**
     * @const integer 系统错误标识
     */
    const PHP_ERROR = 0;

    /**
     * @const integer 用户错误标识
     */
    const CODE_ERROR = -1;

    /**
     * @const integer 用户消息标识
     */
    const USER_MSG = -2;

    /**
     * @const integer 数据库错误标识
     */
    const DB_ERROR = -3;

    /**
     * 重定义构造器使 message 变为必须被指定的属性
     *
     * @param string $message
     *            错误消息
     * @param integer $code
     *            错误类型
     * @return void
     */
    public function __construct($message, $code = 0){
        parent::__construct($message,$code);
    }

    /**
     * 自定义字符串输出的样式
     *
     * @return void
     */
    public function __toString(){
        return __CLASS__;
    }

    /**
     * 获取错误信息
     *
     * @return array 详细错误信息
     */
    public function getError(){
        $trace = $this->getTrace();
        if (isset($trace[0]['file'])) $this->file = $trace[0]['file'];
        if (isset($trace[0]['line'])) $this->line = $trace[0]['line'];
        $types = array(
                self::CODE_ERROR=>'程序错误',
                self::PHP_ERROR=>'系统错误',
                self::USER_MSG=>'用户消息',
                self::DB_ERROR=>'数据库错误'
        );
        $this->type = isset($types[$this->code]) ? $types[$this->code]:'UNKNOW_ERROR';
        $tmpArr = array();
        $index = 0;
        foreach($trace as $value){
            $tmpStr = '';
            if (isset($value['line'])) $tmpStr .= '[Line: ' . $value['line'] .
                     ']';
            if (isset($value['file'])) $tmpStr .= $value['file'];
            $tmpStr .= '(';
            if (isset($value['class'])) $tmpStr .= $value['class'];
            if (isset($value['type'])) $tmpStr .= $value['type'];
            if (isset($value['function'])) $tmpStr .= $value['function'];
            if (isset($value['args'])) $tmpStr .= '(' .
                     $this->getArgsInfo($value['args']) . ')';
            $tmpStr .= ')';
            $tmpArr[] = $tmpStr;
            $index++;
        }
        $error['code'] = $this->code;
        $error['message'] = $this->message;
        $error['type'] = $this->type;
        $error['file'] = $this->file;
        $error['line'] = $this->line;
        $error['trace'] = $tmpArr;
        $error['traceArr'] = $trace;
        return $error;
    }

    /**
     * 获取参数信息
     *
     * @param string $args
     *            参数
     * @return string 参数信息
     */
    public function getArgsInfo($args){
        $tmpArr = array();
        foreach($args as $value){
            if (is_object($value)){
                $tmpArr[] = "Object(" . get_class($value) . ")";
            }elseif (is_array($value)){
                $tmpArr[] = 'Array';
            }else{
                $tmpArr[] = $value;
            }
        }
        return implode(", ",$tmpArr);
    }
}
