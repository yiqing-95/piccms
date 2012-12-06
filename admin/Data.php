<?php

/**
 * 数据备分
 * 
 * @author 小鱼哥哥
 *         @time 2011-11-17 16:27
 * @version 1.0
 */
class Data_Controller extends Base_Controller {

    public function __construct(){
        parent::__construct();
        $this->checkLogin(Ext_Auth::SYS_EDIT);
    }

    public function showTables(){
        $dbPlus = new Db_Plus($this->db);
        $res = $dbPlus->showTables();
        $tables = array();
        foreach($res as $value){
            if (0 === strpos($value,Wee::$config['db_table_prefix'])){
                $tables[] = $value;
            }
        }
        $this->output->set('tables',$tables);
        $this->output->display('data_show.html');
    }

    public function buckUp(){
        $size = $this->input->getIntval('size');
        if (!$size){
            $size = 2048;
        }
        $tables = $this->input->get('tables');
        if (!$tables){
            show_msg('请选择要备份的表');
        }
        $dbPlus = new Db_Plus($this->db);
        $dbPlus->dumpSql($tables,Wee::$config['data_path'] . 'db_backup/',$size);
        show_msg('操作成功','?c=Data&a=backIn',0);
    }

    public function backIn(){
        $dbPlus = new Db_Plus($this->db);
        $sqlFile = $dbPlus->getSqlFile(Wee::$config['data_path'] . 'db_backup/');
        $this->output->set('sqlFile',$sqlFile);
        $this->output->display('data_backin.html');
    }

    public function import(){
        $filename = $this->input->get('id');
        $p = max(1,$this->input->getIntval('p'));
        $dbPlus = new Db_Plus($this->db);
        $path = Wee::$config['data_path'] . 'db_backup/';
        $file = $path . $filename . '_' . $p . '.sql';
        if (is_file($file)){
            $sql = Ext_File::read($file);
            $dbPlus->batQuery($sql);
            $nextPage = $p + 1;
            show_msg("第 $p 个备份文件恢复成功, 正在恢复下一个",
                    "?c=Data&a=import&id=$filename&p=$nextPage",1);
        }else{
            show_msg("数据库恢复成功",'?c=Data&a=backIn',3);
        }
    }

    public function del(){
        $ids = $this->input->get('ids');
        $dbPlus = new Db_Plus($this->db);
        $path = Wee::$config['data_path'] . 'db_backup/';
        if ($ids){
            foreach($ids as $value){
                $files = $dbPlus->getSamePreFile($path,$value);
                foreach($files as $file){
                    @unlink($file);
                }
            }
        }
        show_msg('操作成功','?c=Data&a=backIn',0);
    }

    public function runSql(){
        if (check_submit()){
            $sql = $this->input->getTrim('content');
            if (!$sql){
                show_msg('请输入SQL语句');
            }
            $sql = stripslashes($sql);
            $sql = str_replace('#@_',Wee::$config['db_table_prefix'],$sql);
            $dbPlus = new Db_Plus($this->db);
            $num = $dbPlus->batQuery($sql);
            show_msg("$num 条SQL语句成功运行",'?c=Data&a=runSql');
        }
        $this->output->display('data_runsql.html');
    }
}