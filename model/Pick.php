<?php

/**
 * 采集器
 * 
 * @author 小鱼哥哥
 *         @time 2011-12-27 17:30
 * @version 1.0
 */
class Pick_Model extends Model {

    public function __construct(){
        parent::__construct();
    }

    /**
     * 添加采庥规则
     * 
     * @param
     *            mixed
     * @return void
     */
    public function add($data){
        return $this->db->table('#@_pick_rule')->insert($data);
    }

    /**
     * 更新节点数据
     * 
     * @param
     *            mixed
     * @return void
     */
    public function set($id, $data){
        return $this->db->table('#@_pick_rule')
            ->where("id = $id")
            ->update($data);
    }

    /**
     * 获取节点信息
     * 
     * @param
     *            mixed
     * @return void
     */
    public function get($id){
        $res = $this->db->table('#@_pick_rule')
            ->where("id = $id")
            ->getOne();
        return $res;
    }

    /**
     * 删除采集规则
     * 
     * @param
     *            mixed
     * @return void
     */
    public function del($ruleId){
        $this->db->table('#@_pick_rule')
            ->where("id = $ruleId")
            ->delete();
        $this->db->table('#@_pick_list')
            ->where("rule_id = $ruleId")
            ->delete();
    }

    /**
     * 获取采集信息列表
     * 
     * @param
     *            mixed
     * @return void
     */
    public function getList(){
        $res = $this->db->table('#@_pick_rule')->getAll();
        return $res;
    }

    /**
     * 检查方案名是否已存在
     * 
     * @param
     *            mixed
     * @return void
     */
    public function checkWebName($webname, $id = 0){
        $res = $this->db->table('#@_pick_rule')
            ->where("webname = '$webname'")
            ->getOne();
        if ($res && $res['id'] != $id){
            return false;
        }
        return true;
    }

    /**
     * 栓查一个地址是否存在
     * 
     * @param
     *            mixed
     * @return void
     */
    public function haveProUrl($ruleId, $url){
        $res = $this->db->table('#@_pick_list')
            ->where("rule_id = $ruleId AND url = '$url'")
            ->getOne();
        return $res;
    }

    /**
     * 获取一个采集列表信息
     * 
     * @param
     *            mixed
     * @return void
     */
    public function getPro($ruleId, $isPicked = 0, $limit = 1){
        $res = $this->db->table('#@_pick_list')
            ->where("rule_id = $ruleId AND is_picked = $isPicked")
            ->limit("$limit")
            ->getOne();
        if ($res){
            $res['p_content_urls'] = json_decode($res['p_content_urls'],true);
        }
        return $res;
    }

    /**
     * 添加一条列表信息
     * 
     * @param
     *            mixed
     * @return void
     */
    public function addPro($data){
        return $this->db->table('#@_pick_list')->insert($data);
    }

    /**
     * 更新一个列表信息
     * 
     * @param
     *            mixed
     * @return void
     */
    public function setPro($id, $data){
        $res = $this->db->table('#@_pick_list')
            ->where("id = $id")
            ->update($data);
        return $res;
    }

    /**
     * 获取列表条数
     * 
     * @param
     *            mixed
     * @return void
     */
    public function getProNum($ruleId, $isPicked = null){
        $this->db->table('#@_pick_list')->field("COUNT(1) AS num");
        if (is_null($isPicked)){
            $this->db->where("rule_id = $ruleId");
        }else{
            $this->db->where("rule_id = $ruleId AND is_picked = $isPicked");
        }
        $res = $this->db->getOne();
        return $res['num'];
    }

    /**
     * 重置采集任务
     * 
     * @param
     *            mixed
     * @return void
     */
    public function replay($ruleId){
        $data = array(
                'p_list_page'=>0
        );
        $this->set($ruleId,$data);
        $this->db->table('#@_pick_list')
            ->where("rule_id = $ruleId")
            ->delete();
    }

    /**
     * 导出
     * 
     * @param
     *            mixed
     * @return void
     */
    public function export($pickInfo){
        return 'BASE64:' . base64_encode(json_encode($pickInfo)) . ':END';
    }

    /**
     * 导入
     * 
     * @param
     *            mixed
     * @return void
     */
    public function import($base64){
        $arr = explode(":",$base64);
        if (isset($arr[1])){
            return json_decode(base64_decode($arr[1]),true);
        }else{
            return false;
        }
    }

    /**
     * 获取列表地址
     * 
     * @param
     *            mixed
     * @return void
     */
    public function getListUrls($listmoreurl, $listurl, $pageBegin, $pageEnd, 
            $pageStep){
        $urlArr = array();
        // 额外的地址
        if ($listmoreurl){
            $urlArr = explode("\n",$listmoreurl);
        }
        if (!$pageStep){
            $pageStep = 1;
        }
        // 生成列表地址数据
        if ($listurl){
            for($i = $pageBegin; $i <= $pageEnd; $i += $pageStep){
                $url = str_replace('[page]',$i,$listurl);
                $urlArr[] = $url;
            }
        }
        $urlArr = array_unique($urlArr);
        return $urlArr;
    }

    /**
     * 获取HTML数据
     * 
     * @param
     *            mixed
     * @return void
     */
    public function getHtml($url, $charset = 'UTF-8'){
        $html = Ext_Network::openUrl($url) ;
        $html = ($html)? $html:@file_get_contents($url);
        $html = ($html)? $html:@file($url);
        if ($html){
            $html = implode('',$html);
        }else{
            return false;
        }
        if ('UTF-8' != $charset){
            $html = @iconv($charset,'UTF-8',$html);
        }
        $html = str_replace(array(
                "\r",
                "\n"
        ),'',$html);
        return $html;
    }

    /**
     * 对规则做最基本的处理
     * 
     * @param
     *            mixed
     * @return void
     */
    public function dealRule($rule){
        $rule = str_replace(array(
                "\n",
                "\r"
        ),'',$rule);
        $rule = preg_quote($rule,'/');
        return $rule;
    }

    /**
     * 获取内容图片
     * 
     * @param
     *            mixed
     * @return void
     */
    public function getContent($rule, $html){
        if (!$rule){
            return $html;
        }
        $rule = $this->dealRule($rule);
        $rule = '/' . str_replace(array(
                ' ',
                '\[content\]',
                '\[\*\]'
        ),array(
                '\s',
                '(?P<content>.+?)',
                '(?:[^>]*?)'
        ),$rule) . '/';
        if (preg_match($rule,$html,$arr)){
            return $arr['content'];
        }else{
            return false;
        }
    }

    /**
     * 获取urls
     * 
     * @param
     *            mixed
     * @return void
     */
    public function getUrls($rule, $url, $html, $linkInWord = '', 
            $linkNoinWord = '', $listUrlJoin = ''){
        $urlInfo = parse_url($url);
        $urlInfo['basedir'] = dirname($url) . '/';
        $rule = $this->dealRule($rule);
        $rule = '/' . str_replace(array(
                ' ',
                '\[url\]',
                '\[title\]',
                '\[\*\]'
        ),array(
                '\s',
                '(?P<url>[^\s>\'\"]+?)',
                '(?P<title>.+?)',
                '(?:[^>]*?)'
        ),$rule) . '/';
        $list = array();
        if (preg_match_all($rule,$html,$arr)){
            if (!isset($arr['url'])){
                return $list;
            }
            $tmpArr = array();
            $urlArr = array_unique($arr['url']);
            foreach($urlArr as $key=>$value){
                if ($linkInWord && (false === strpos($value,$linkInWord))){
                    continue;
                }
                if ($linkNoinWord && (false !== strpos($value,$linkNoinWord))){
                    continue;
                }
                if (isset($tmpArr[$value])){
                    continue;
                }
                $tmpArr[$value] = true;
                if ($listUrlJoin){
                    $arr['url'][$key] = str_replace('[content]',
                            $arr['url'][$key],$listUrlJoin);
                }
                /*
                 * if (0 === strpos($arr['url'][$key], $urlInfo['scheme'] .
                 * '://')) { // 绝对地址 } elseif (0 === strpos($arr['url'][$key],
                 * '/')) { // 绝对路径 $arr['url'][$key] = $urlInfo['scheme']
                 * .'://'. $urlInfo['host'] . $arr['url'][$key]; } else {
                 * $arr['url'][$key] = $urlInfo['basedir'] . $arr['url'][$key];
                 * }
                 */
                $data = array(
                        'url'=>$arr['url'][$key],
                        'title'=>''
                );
                if (isset($arr['title'][$key])){
                    $data['title'] = $arr['title'][$key];
                }
                $list[] = $data;
            }
        }
        return $list;
    }
}