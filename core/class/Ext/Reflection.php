<?php

/**
 * 类反射
 * 
 * @author 小鱼哥哥
 *         @time 2011-12-27 17:18
 * @version 1.0
 */
class Ext_Reflection {

    /**
     * 初始化类文档
     *
     * @param string $className
     *            类名
     * @return void
     */
    public function __construct($className){
        $this->className = $className;
        $this->ReflectionClass = new ReflectionClass($this->className);
    }

    /**
     * 分析类文档
     *
     * @return mixed 文档内容
     */
    public function parseDoc(){
        $classDoc = $this->getClass($this->ReflectionClass);
        return $classDoc;
    }

    /**
     * 获取类文档
     *
     * @param mixed $ReflectionClass
     *            类对象
     * @return mixed 类文档
     */
    public function getClass($ReflectionClass){
        $info = array();
        $info['name'] = $ReflectionClass->getName();
        $info['doc'] = $this->getDoc($ReflectionClass->getDocComment());
        $info['file'] = $ReflectionClass->getFileName();
        $info['line'] = $ReflectionClass->getStartLine() . '-' .
                 $ReflectionClass->getEndLine();
        $info['modifier'] = $this->getModifier($ReflectionClass->getModifiers());
        // 常量
        $info['const'] = $ReflectionClass->getConstants();
        // 变量
        $info['vars'] = array();
        $Properties = $ReflectionClass->getProperties();
        foreach($Properties as $ReflectionProperty){
            $info['vars'][] = $this->getProperty($ReflectionProperty);
        }
        // 方法
        $info['method'] = array();
        $method = $ReflectionClass->getMethods();
        foreach($method as $ReflectionMethod){
            $temp = $this->getMethod($ReflectionMethod);
            if ($temp['isApi']){
                $info['method'][$temp['name']] = $temp;
            }
        }
        // ksort($info['method']);
        return $info;
    }

    /**
     * 获取属性文档
     *
     * @param mixed $ReflectionProperty
     *            属性对象
     * @return mixed 属性文档
     */
    public function getProperty($ReflectionProperty){
        $info['name'] = $ReflectionProperty->getName();
        $info['doc'] = $this->getDoc($ReflectionProperty->getDocComment());
        $info['modifier'] = $this->getModifier(
                $ReflectionProperty->getModifiers());
        return $info;
    }

    /**
     * 获取方法文档
     *
     * @param mixed $ReflectionMethod
     *            方法对象
     * @return mixed 方法文档
     */
    public function getMethod($ReflectionMethod){
        $info['name'] = $ReflectionMethod->getName();
        $info['doc'] = $this->getDoc($ReflectionMethod->getDocComment());
        $info['modifier'] = $this->getModifier(
                $ReflectionMethod->getModifiers());
        $DeclaringClass = $ReflectionMethod->getDeclaringClass();
        $info['class'] = $DeclaringClass->getName();
        $info['file'] = $ReflectionMethod->getFileName();
        $info['line'] = $ReflectionMethod->getStartLine() . '-' .
                 $ReflectionMethod->getEndLine();
        $info['isPublic'] = $ReflectionMethod->isPublic();
        $info['isStatic'] = $ReflectionMethod->isStatic();
        $info['isApi'] = false;
        if ($info['isPublic'] && false === strpos($info['name'],'__')){
            $info['isApi'] = true;
        }
        
        // 参数
        $info['args'] = array();
        $Parameters = $ReflectionMethod->getParameters();
        foreach($Parameters as $ReflectionParameter){
            $info['args'][] = $this->getArg($ReflectionParameter);
        }
        $info['args'] = implode(", ",$info['args']);
        return $info;
    }

    /**
     * 获取修饰符
     *
     * @param mixed $Modifiers
     *            修饰对象
     * @return mixed 修饰符
     */
    public function getModifier($Modifiers){
        return implode(" ",(Reflection::getModifierNames($Modifiers)));
    }

    /**
     * 获取注释
     *
     * @param mixed $DocComment
     *            注释对象
     * @return mixed 获取注释
     */
    public function getDoc($DocComment){
        if (trim($DocComment)){
            $DocComment = str_replace(array(
                    '/**',
                    '**/',
                    '*/',
                    "*"
            ),'',$DocComment);
            $DocComment = str_replace(array(
                    "\t",
                    "  "
            ),array(
                    "&nbsp;&nbsp;",
                    "&nbsp;"
            ),$DocComment);
            $DocComment = nl2br(trim($DocComment));
        }else{
            $DocComment = "暂无文档";
        }
        return $DocComment;
    }

    /**
     * 获取参数
     *
     * @param mixed $DocComment
     *            参数对象
     * @return mixed 获取参数
     */
    public function getArg($ReflectionParameter){
        $arg = '$' . $ReflectionParameter->getName();
        if ($ReflectionParameter->isArray()){
            $arg = '(Array)' . $arg;
        }
        if ($ReflectionParameter->isDefaultValueAvailable()){
            $arg .= ' = ' . $ReflectionParameter->getDefaultValue();
        }
        return $arg;
    }

    /**
     * 打印类文档
     *
     * @return void
     */
    public function export(){
        Wee::dump(Reflection::export($this->ReflectionClass,true));
    }
}
