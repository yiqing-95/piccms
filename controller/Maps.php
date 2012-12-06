<?php

/**
 * 网站地图
 *
 * @author 小鱼哥哥
 *         @time 2011-12-20 13:14
 * @version 1.0
 */
class Maps_Controller extends Base_Controller {

    /**
     * initController
     *
     * @param
     *            mixed
     * @return void
     */
    public function __construct(){
        parent::__construct();
    }

    /**
     * Rss订阅
     *
     * @param
     *            mixed
     * @return void
     */
    public function rss(){
        $modArticle = load_model('Article');
        $list = $modArticle->search(null,Wee::$config['web_rss_num']);
        $arr = array(
                'channel'=>array(
                        'title'=>Wee::$config['web_name'],
                        'description'=>Wee::$config['web_description'],
                        'link'=>Wee::$config['web_url'],
                        'language'=>'zh-cn',
                        'docs'=>Wee::$config['web_description'],
                        'generator'=>'Rss Powered By ' . Wee::$config['web_url'],
                        'image'=>array(
                                'url'=>Wee::$config['web_url'] .
                                         'images/logo.png'
                        )
                )
        );
        foreach($list as $value){
            $data = array(
                    'title'=>$value['title'],
                    'link'=>$value['url'],
                    'author'=>$value['author'],
                    'pubDate'=>$value['pubdate'],
                    'description'=>$value['remark']
            );
            if ($value['cover_url']){
                $data['description'] .= "<br><img src={$value['cover_url']}>";
            }
            $arr['channel'][] = $data;
        }
        $xml = Ext_Xml::encode($arr,'rss version="2.0"');
        if (Wee::$config['url_html_maps'] && $this->input->get('makeHtml')){
            $rssfile = APP_PATH . Wee::$config['url_dir_maps'] . '/rss.xml';
            Ext_File::write($rssfile,$xml);
        }else{
            echo $xml;
        }
    }

    /**
     * 网站地图
     *
     * @param
     *            mixed
     * @return void
     */
    public function index(){
        $modArticle = load_model('Article');
        $list = $modArticle->search(null,Wee::$config['web_maps_num']);
        $maps = array();
        foreach($list as $value){
            $maps[] = array(
                    'loc'=>$value['url'],
                    'lastmod'=>$value['pubdate'],
                    'changefreq'=>'hourly',
                    'priority'=>'1.0'
            );
        }
        $xml = Ext_Xml::encode($maps,'urlset','url');
        if (Wee::$config['url_html_maps'] && $this->input->get('makeHtml')){
            $rssfile = APP_PATH . Wee::$config['url_dir_maps'] . '/sitemap.xml';
            Ext_File::write($rssfile,$xml);
        }else{
            echo $xml;
        }
    }
}