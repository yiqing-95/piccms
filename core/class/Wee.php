<?php

/**
 * Wee核心类
 * 
 * @author 小鱼哥哥
 *         @time 2011-12-27 17:16
 * @version 1.0
 */
class Wee {

    /**
     *
     * @var string 框架版本
     */
    const CORE_VER = '1.0';

    /**
     *
     * @var mixed 前台入口
     */
    const ENTRANCE_INDEX = 0;

    /**
     *
     * @var mixed 后台入口
     */
    const ENTRANCE_ADMIN = 1;

    /**
     *
     * @var mixed 安装包入口
     */
    const ENTRANCE_INSTALL = 2;

    /**
     *
     * @var mixed 后台入口
     */
    const ENTRANCE_SERVER = 3;

    /**
     *
     * @var Request_Input 传入数据对象
     */
    public static $input = null;

    /**
     *
     * @var Request_Output 传出数据对象
     */
    public static $output = null;

    /**
     *
     * @var array 系统运行时配置
     */
    public static $config = array();

    /**
     *
     * @var array 全局变量容器
     */
    public static $box = array();

    /**
     * 运行服务
     * 
     * @param
     *            mixed
     * @return void
     */
    public static function run($controllerName = null, $actionName = null){
        Wee::init();
        if (!$controllerName){
            $controllerName = Wee::$input->getControllerName();
        }
        if (!$actionName){
            $actionName = Wee::$input->getActionName();
        }
        $controllerName .= '_Controller';
        if (!class_exists($controllerName) ||
                 'Base_Controller' == $controllerName){
            show_msg("$controllerName: The controller does not exist");
        }
        try{
            $handle = new $controllerName();
            $handle->$actionName();
        }catch(Error $e){
            catch_error($e);
        }
        Wee::$output->setControllerName(Wee::$input->getControllerName());
        Wee::$output->setActionName(Wee::$input->getActionName());
        if (true == Wee::$output->dataMode){
            Wee::_outputData();
        }
    }

    public static function init(){
        if (Wee::ENTRANCE_SERVER == Wee::$config['entrance']){
            $args = self::_initServer();
        }else{
            $args = self::_initClient();
        }
        Wee::$input = new Request_Input($args);
        Wee::$output = new Request_Output();
    }

    private static function _initServer(){
        if (!isset($_SERVER['argv'])){
            exit('Bad request');
        }
        $argv = $_SERVER['argv'];
        $script = array_shift($argv);
        parse_str(implode('&',$argv),$args);
        $args['script'] = $script;
        return $args;
    }

    /**
     * 初始化应用
     * 
     * @param
     *            mixed
     * @return void
     */
    private static function _initClient(){
        if (0 == get_magic_quotes_gpc()){
            $_GET = Ext_Array::map($_GET,'addslashes');
            $_POST = Ext_Array::map($_POST,'addslashes');
            $_COOKIE = Ext_Array::map($_COOKIE,'addslashes');
        }
        $args = array_merge($_GET,$_POST);
        self::_parseRequestInfo();
        if (Wee::$config['url_mode'] > 0){
            $pattern = "#^(([^" . Wee::$config['url_delimiter'] . "]+" .
                     Wee::$config['url_delimiter'] . "?)*)" .
                     preg_quote(Wee::$config['url_suffix']) . "$#is";
            $query = Wee::$config['web_query'];
            if (preg_match($pattern,$query,$pregArr)){
                if ($pregArr[1]){
                    $query = explode(Wee::$config['url_delimiter'],$pregArr[1]);
                    if (isset($query[0])){
                        $routeName = array_shift($query);
                        if (Wee::$config['url_route'] &&
                                 isset(
                                        Wee::$config['url_route_rule'][$routeName])){
                            $routeValue = Wee::$config['url_route_rule'][$routeName];
                            $args[Wee::$config['controller_var_name']] = $routeValue[0];
                            $args[Wee::$config['action_var_name']] = $routeValue[1];
                            $routeArgs = array();
                            if (!empty($routeValue[2])){
                                $routeArgs = explode(',',$routeValue[2]);
                                foreach($routeArgs as $key=>$value){
                                    if (isset($query[$key])){
                                        $args[$value] = addslashes(
                                                urldecode($query[$key]));
                                    }else{
                                        $args[$value] = null;
                                    }
                                }
                            }
                            unset($pregArr,$query,$routeName,$routeValue,
                                    $routeArgs);
                        }else{
                            $args[Wee::$config['controller_var_name']] = $routeName;
                            if (isset($query[0])){
                                $args[Wee::$config['action_var_name']] = array_shift(
                                        $query);
                            }
                            $argsNum = count($query);
                            for($i = 0; $i < $argsNum; $i = $i + 2){
                                $args[$query[$i]] = null;
                                if (isset($query[$i + 1])){
                                    $args[$query[$i]] = addslashes(
                                            urldecode($query[$i + 1]));
                                }
                            }
                            unset($pregArr,$query,$routeName,$argsNum);
                        }
                    }
                }
            }
        }
        return $args;
    }

    /**
     * 输出数据
     * 
     * @param mixed $output
     *            传出数据对象
     * @return void
     */
    private static function _outputData(){
        ob_end_clean();
        $v = Wee::$input->get(Wee::$config['view_var_name']);
        switch($v){
            case 'xml':
                echo Ext_Xml::encode(Wee::$output);
                break;
            case 'json':
                echo json_encode(Wee::$output);
                break;
            case 'array':
                var_export((array)Wee::$output);
                break;
            case 'dump':
                echo '<pre>';
                print_r(Wee::$output);
                echo '</pre>';
                break;
        }
    }

    /**
     * 解析请求信息
     * 
     * @param
     *            mixed
     * @return void
     */
    private static function _parseRequestInfo(){
        if (isset($_SERVER['HTTP_X_REWRITE_URL'])){
            Wee::$config['web_uri'] = $_SERVER['HTTP_X_REWRITE_URL'];
        }elseif (isset($_SERVER['REQUEST_URI'])){
            Wee::$config['web_uri'] = $_SERVER['REQUEST_URI'];
        }
        if (isset($_SERVER['SCRIPT_NAME'])){
            Wee::$config['web_script'] = $_SERVER['SCRIPT_NAME'];
        }elseif (isset($_SERVER['PHP_SELF'])){
            Wee::$config['web_script'] = $_SERVER['PHP_SELF'];
        }elseif (isset($_SERVER['ORIG_SCRIPT_NAME'])){
            Wee::$config['web_script'] = $_SERVER['ORIG_SCRIPT_NAME'];
        }
        if (substr(Wee::$config['web_script'],-1,1) == '/'){
            Wee::$config['web_dir'] = Wee::$config['web_script'];
        }else{
            Wee::$config['web_dir'] = rtrim(dirname(Wee::$config['web_script']),
                    '\\/') . '/';
        }
        Wee::$config['web_host'] = strtolower(
                substr($_SERVER['SERVER_PROTOCOL'],0,
                        strpos($_SERVER['SERVER_PROTOCOL'],'/'))) . '://' .
                 $_SERVER['HTTP_HOST'];
        if (!empty($_SERVER['PATH_INFO'])){
            Wee::$config['web_query'] = substr($_SERVER['PATH_INFO'],1);
        }elseif (isset($_SERVER['QUERY_STRING'])){
            Wee::$config['web_query'] = $_SERVER['QUERY_STRING'];
        }
    }
}