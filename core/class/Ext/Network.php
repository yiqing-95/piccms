<?php

/**
 * 网络通信扩展
 * 
 * @author 小鱼哥哥
 *         @time 2011-12-27 17:18
 * @version 1.0
 */
class Ext_Network {

    /**
     * 请求URL地址
     *
     * @param string $url
     *            URL地址
     * @param mixed $data
     *            要POST的数据
     * @param integer $timeout
     *            超时时间
     * @return string 响应内容
     */
    public static function openUrl($url, $data = null, $timeout = 30){
        $urlArr = @parse_url($url);
        if (empty($urlArr['host'])) return false;
        if (empty($urlArr['query'])) $urlArr['query'] = '';
        if (empty($urlArr['port'])) $urlArr['port'] = 80;
        if (empty($urlArr['path'])) $urlArr['path'] = '/';
        if (empty($urlArr['scheme'])) $urlArr['scheme'] = 'http';
        $urlArr['referer'] = $urlArr['host'];
        $fp = @fsockopen($urlArr['host'],$urlArr['port'],$errno,$errstr,
                $timeout);
        if (!$fp){
            echo "$errstr ($errno)<br />\n";
            return false;
        }
        if ($urlArr['query']){
            $sendStr = "GET {$urlArr['path']}?{$urlArr['query']} HTTP/1.1\r\n";
        }else{
            $sendStr = "GET {$urlArr['path']} HTTP/1.1\r\n";
        }
        $sendStr .= "Host: {$urlArr['host']}:{$urlArr['port']}\r\n";
        $sendStr .= "Accept: */*\r\n";
        $sendStr .= "Referer: {$urlArr['referer']}\r\n";
        $sendStr .= "User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9.2.8)\r\n";
        $sendStr .= "Cache-Control: no-cache\r\n";
        if ($data){
            $data = is_array($data) ? http_build_query($data):$data;
            $length = strlen($query_str);
            $sendStr .= "Content-Type: application/x-www-form-urlencoded\r\n";
            $sendStr .= "Content-Length: {$length}\r\n";
        }
        $sendStr .= "Connection: Close\r\n\r\n";
        if ($data){
            $sendStr .= $data;
        }
        fwrite($fp,$sendStr);
        $header = '';
        do{
            $header .= fgets($fp,4096);
        }while(!preg_match("/\r\n\r\n$/",$header));
        $headerArr = self::parseHeader($header);
        if (in_array($headerArr['status'],array(
                301,
                302
        ))){
            if (preg_match("/Location\:\s*(.+)\r\n/i",$header,$regs)){
                $rs = self::openUrl(trim($regs[1]),$data,$timeout);
                return $rs;
            }
        }elseif (200 != $headerArr['status']){
            return false;
        }
        $body = '';
        while(!feof($fp)){
            $body .= fgets($fp,4096);
        }
        fclose($fp);
        if (isset($headerArr['Transfer-Encoding']) &&
                 'chunked' == $headerArr['Transfer-Encoding']){
            $body = self::parseChunked($body);
        }
        if (strlen($body) < 1){
            return false;
        }
        return $body;
    }

    /**
     * 解析chunked编码
     *
     * @param string $data
     *            待解析的正文内容
     * @return string 解析后的正文
     */
    public static function parseChunked($data){
        $pos = 0;
        $temp = '';
        while($pos < strlen($data)){
            $len = strpos($data,"\r\n",$pos) - $pos;
            $str = substr($data,$pos,$len);
            $pos += $len + 2;
            $arr = explode(';',$str,2);
            $len = hexdec($arr[0]);
            $temp .= substr($data,$pos,$len);
            $pos += $len + 2;
        }
        return $temp;
    }

    /**
     * 分析Header参数
     *
     * @param mixed $header
     *            Header头信息
     * @return mixed
     */
    public static function parseHeader($header){
        $rs = array();
        if (preg_match_all("/(.+?):\s*(.+?)\r\n/i",$header,$regs)){
            $rs = array_combine($regs[1],$regs[2]);
        }
        $rs['status'] = 0;
        if (preg_match("/(.+) (\d+) (.+)\r\n/i",$header,$status)){
            $rs['status'] = $status[2];
        }
        return $rs;
    }

    /**
     * 获取客端IP
     *
     * @return string
     */
    public static function getClientIp(){
        if (isset($_SERVER['HTTP_CLIENT_IP']) &&
                 $_SERVER['HTTP_CLIENT_IP'] != 'unknown'){
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) &&
                 $_SERVER['HTTP_X_FORWARDED_FOR'] != 'unknown'){
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }else{
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    /**
     * 输出下载文件
     * 
     * @param
     *            mixed
     * @return void
     */
    public static function outContent($fileName, $content){
        header('Expires: ' . gmdate('D, d M Y H:i:s',time() + 31536000) . ' GMT');
        header("Content-type: application/octet-stream");
        header("Content-Disposition: attachment; filename=$fileName");
        echo $content;
    }
}
