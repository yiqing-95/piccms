<?php

/**
 * 基础控制器
 *
 * @author YHS
 *         @time 2011-8-30 15:53
 * @version 1.0
 */
class Base_Controller extends Controller {

    public function __construct(){
        parent::__construct();
        $this->init();
    }

    protected function init(){
        if ('' != ($skin = $this->input->get('skin'))){
            if (in_array($skin,load_model('Config')->getSkinList())){
                Cookie::set('template_skin',$skin);
            }
        }
        if ('' != ($templateSkin = Cookie::get('template_skin'))){
            Wee::$config['template_skin'] = $templateSkin;
        }
        // 注册基础模块标签
        $this->output->registerTag(
                array(
                        'image'=>array(
                                'Tag',
                                'image'
                        ),
                        'article'=>array(
                                'Tag',
                                'article'
                        ),
                        'tags'=>array(
                                'Tag',
                                'tags'
                        ),
                        'links'=>array(
                                'Tag',
                                'links'
                        ),
                        'searchurl'=>array(
                                'Tag',
                                'searchurl'
                        ),
                        'tagsurl'=>array(
                                'Tag',
                                'tagsurl'
                        ),
                        'rssurl'=>array(
                                'Tag',
                                'rssurl'
                        ),
                        'sitemapurl'=>array(
                                'Tag',
                                'sitemapurl'
                        ),
                        'links'=>array(
                                'Tag',
                                'Links'
                        ),
                        'relevant'=>array(
                                'Tag',
                                'relevant'
                        ),
                        'adsense'=>array(
                                'Tag',
                                'adsense'
                        )
                ));
        // 注册已启用的插件
        $pluginDir = APP_PATH . 'plugin/';
        $pluginFiles = Ext_Dir::getDirList($pluginDir,Ext_Dir::TYPE_FILE,
                array(),array(
                        'php'
                ));
        foreach($pluginFiles as $value){
            import_file($pluginDir . $value);
        }
    }

    protected function assignData(){
        $this->output->set(
                array(
                        'sys_name'=>Wee::$config['sys_name'],
                        'sys_url'=>Wee::$config['sys_url'],
                        'sys_ver'=>Wee::$config['sys_ver'],
                        'web_uri'=>Wee::$config['web_uri'],
                        'web_dir'=>Wee::$config['web_dir'],
                        'web_script'=>Wee::$config['web_script'],
                        'web_host'=>Wee::$config['web_host'],
                        'web_copyright'=>Wee::$config['web_copyright'],
                        'web_description'=>Wee::$config['web_description'],
                        'web_email'=>Wee::$config['web_email'],
                        'web_icp'=>Wee::$config['web_icp'],
                        'web_keywords'=>Wee::$config['web_keywords'],
                        'web_name'=>Wee::$config['web_name'],
                        'web_path'=>Wee::$config['web_path'],
                        'web_qq'=>Wee::$config['web_qq'],
                        'web_tongji'=>Wee::$config['web_tongji'],
                        'web_url'=>Wee::$config['web_url']
                ));
        $cMod = load_model('Cate');
        $cList = $cMod->getList();
        $cTree = $cMod->getTree();
        $this->output->set(
                array(
                        'cateTree'=>$cTree,
                        'cateList'=>$cList
                ));
    }
}