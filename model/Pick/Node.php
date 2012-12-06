<?php

/**
 * 采集节点模型
 * 
 * @author 小鱼哥哥
 *         @time 2011-11-3 16:42
 * @version 1.0
 */
class Pick_Node_Model extends Model {

    public $mod;

    private $_ruleInfo;

    public function __construct($ruleInfo){
        parent::__construct();
        $this->mod = load_model('Pick');
        $this->_ruleInfo = $ruleInfo;
        $this->init();
    }

    public function init(){
        // 获取列表页地址
        $this->listUrls = $this->mod->getListUrls($this->listmoreurl,
                $this->listurl,$this->page_begin,$this->page_end,
                $this->page_step);
        if (!$this->listUrls){
            show_msg("没有找到列表页地址, 请检查规则");
        }
        // 获取列表页地址总数
        $this->totalListUrlsNum = count($this->listUrls);
        // 当前处理到的下标
        $this->pListPage = $this->p_list_page;
        // 当前处理的页数也就是下一个要处理的下标
        $this->nextPage = $this->pListPage + 1;
    }

    public function doPickList(){
        // 获取内容页标题和链接
        $this->titleUrls = $this->mod->getUrls($this->list_url_rule,
                $this->thisUrl(),$this->html(),$this->link_include_word,
                $this->link_noinclude_word,$this->list_url_join);
        // 总的记录数
        $this->totalTitleUrlsNum = count($this->titleUrls);
        // 已经存在的记录数
        $this->haveTitleNum = 0;
        // 保存还没有采集过的记录
        foreach($this->titleUrls as $value){
            if ($this->mod->haveProUrl($this->id,$value['url'])){
                $this->haveTitleNum++;
            }else{
                $data = array(
                        'rule_id'=>$this->id,
                        'url'=>addslashes($value['url']),
                        'title'=>addslashes($value['title'])
                );
                $this->mod->addPro($data);
            }
        }
        return true;
    }

    public function setNextPage(){
        $this->mod->set($this->id,array(
                'p_list_page'=>$this->nextPage
        ));
    }

    public function html(){
        if (isset($this->_html)){
            return $this->_html;
        }
        $url = $this->thisUrl();
        $html = $this->mod->getHtml($url,$this->charset_type);
        if (!$html){
            show_msg("$url: 访问列表页地址失败, 请检查地址是否正确");
        }
        $html = $this->mod->getContent($this->list_content_rule,$html);
        if (!$html){
            show_msg("分析列表页内容区域失败, 请检查规则");
        }
        $this->_html = $html;
        return $this->_html;
    }

    public function thisUrl(){
        return $this->listUrls[$this->pListPage];
    }

    public function isOver(){
        if (isset($this->listUrls[$this->pListPage])){
            return false;
        }else{
            return true;
        }
    }

    /**
     * 获取节点信息
     * 
     * @param
     *            mixed
     * @return void
     */
    public function __get($name){
        if (isset($this->_ruleInfo[$name])){
            return $this->_ruleInfo[$name];
        }else{
            return null;
        }
    }
}