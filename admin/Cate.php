<?php

/**
 * 分类管理
 * 
 * @author YHS
 *         @time 2011-8-30 15:53
 * @version 1.0
 */
class Cate_Controller extends Base_Controller {

    public function __construct(){
        parent::__construct();
        $this->checkLogin(Ext_Auth::CATE_EDIT);
    }

    public function show(){
        $cateMod = load_model('Cate');
        $cTree = $cateMod->getTree();
        if (empty($cTree)){
            show_msg("暂时还没有分类, 请先添加分类","?c=Cate&a=add");
        }
        $this->output->set('cTree',$cTree);
        $this->output->display('cate_show.html');
    }

    public function updateOid(){
        if (check_submit()){
            $oid = $this->input->get('oid');
            $cateMod = load_model('Cate');
            $cList = $cateMod->getList();
            if (!empty($oid)){
                foreach($oid as $cid=>$value){
                    $value = intval($value);
                    if ($value != $cList[$cid]['oid']){
                        $this->db->table('#@_cate')
                            ->where("cid = $cid")
                            ->update(array(
                                'oid'=>$value
                        ));
                    }
                }
            }
            load_model('Config')->clearFileCache();
            show_msg("更新排序成功",'?c=Cate&a=show');
        }
    }

    public function delete(){
        // if (check_submit()) {
        $ids = $this->input->get('ids');
        if (!$ids){
            show_msg("至少要选择一个分类");
        }
        if (!is_array($ids)){
            $ids = array(
                    $ids
            );
        }
        $cateMod = load_model('Cate');
        $cTree = $cateMod->getTree();
        foreach($ids as $cid){
            $cid = intval($cid);
            if (isset($cTree[$cid]) && !empty($cTree[$cid]['son'])){
                show_msg("该分类包含子分类, 请先删除子分类",'?c=Cate&a=show');
            }
            $cateMod->del($cid);
        }
        load_model('Config')->clearFileCache();
        show_msg("删除分类成功",'?c=Cate&a=show');
        // }
    }

    /**
     * 更改分类状态
     * 
     * @param
     *            mixed
     * @return void
     */
    public function setStatus(){
        $cid = $this->input->getIntval('cid');
        $status = $this->input->getIntval('status');
        $cateMod = load_model('Cate');
        load_model('Config')->clearFileCache();
        $cateMod->set($cid,array(
                'status'=>$status
        ));
    }

    public function add(){
        $cid = $this->input->getIntval('cid');
        $cateMod = load_model('Cate');
        $cList = $cateMod->getList();
        $cTree = $cateMod->getTree();
        if ($cid){
            if (!isset($cList[$cid])){
                show_msg("$cid: 分类不存在");
            }
            $this->output->set($cList[$cid]);
            $cTreeStr = $cateMod->printTree('pid',$cList[$cid]['pid'],true);
        }else{
            $cTreeStr = $cateMod->printTree('pid',0,true);
        }
        if (check_submit()){
            $data['pid'] = $this->input->getIntval('pid');
            $data['name'] = $this->input->getTrim('name');
            $data['oid'] = $this->input->getIntval('oid');
            $data['view_type'] = $this->input->getTrim('view_type');
            $data['eng_name'] = $this->input->getTrim('eng_name');
            if (!$data['name']){
                show_msg('栏目名称不能为空');
            }
            $data['ctpl'] = $this->input->getTrim('ctpl');
            $data['ctitle'] = $this->input->getTrim('ctitle');
            $data['ckeywords'] = $this->input->getTrim('ckeywords');
            $data['cdescription'] = $this->input->getTrim('cdescription');
            load_model('Config')->clearFileCache();
            if ($cid){
                // 如果已经有了子分类则不能再做为子分类
                if ($cid == $data['pid']){
                    show_msg("不能做为自己的子分类");
                }
                if (0 != $data['pid'] && isset($cTree[$cid]) &&
                         !empty($cTree[$cid]['son'])){
                    show_msg("该分类包含子分类,不能再做为子分类");
                }
                $this->db->table('#@_cate')
                    ->where("cid = $cid")
                    ->update($data);
                show_msg('编辑栏目成功','?c=Cate&a=show');
            }else{
                $this->db->table('#@_cate')->insert($data);
                show_msg('添加栏目成功','?c=Cate&a=show');
            }
        }
        $this->output->set('cTreeStr',$cTreeStr);
        $this->output->display('cate_add.html');
    }
}