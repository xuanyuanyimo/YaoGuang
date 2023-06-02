<?php

    if(!defined('IN_YGF')) {
        exit('Access Denied');  
    }

    /**
     * 方法: 获取当前客户端的IP地址.
     * @return Mixed 若成功获取客户端IP，则返回其IPv4地址，否则返回false.
     */
    function getClientIP(){
        if(getenv("HTTP_CLIENT_IP")){
            $ip = getenv("HTTP_CLIENT_IP");
        }elseif(getenv("HTTP_X_FORWARDED_FOR")) {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        }elseif(getenv("REMOTE_ADDR")) {
            $ip = getenv("REMOTE_ADDR");
        }else{
            return false;
        }
        if($ip == '::1'){
            $ip = '127.0.0.1';
        }
        return $ip;
    }

    /**
     * 方法: 获取当前服务器的IP地址.
     * @return String 返回当前联网的网卡的IPv4地址.
     */
    function getServerIp(){
        $server_ip = gethostbyname($_SERVER["SERVER_NAME"]);
        return $server_ip;
    }

    /**
     * 方法: 下载远程文件到当前服务器的指定目录.
     * @param String $file_url 文件的URL地址.
     * @param String $file_dir 文件需要存放的目录.
     * @param String $file_name 下载来的文件的新名，不传入此项参数则使用源文件名 (可选).
     * @return Bool 下载成功则返回true，失败则返回false.
     */
    function file_Download($file_url , $file_dir , $file_name = null){
        if($file_name == null){
            $file_name = basename($file_url);
        }
        if(file_put_contents($file_dir . $file_name , file_get_contents($file_url)) != false){
            return true;
        }
        return false;
    }

    /**
     * 方法: 获取字符串中的网址.
     * @param String $str 包含链接的字符串，比如一个html文档.
     * @return Array 返回的数组的 0 号元素为http链接， 1 号元素为https链接.
     */
    function getUrl($str){
        preg_match_all('/http:[\/]{2}[a-z]+[.]{1}[a-z\d\-]+[.]{1}[a-z\d]*[\/]*[A-Za-z\d]*[\/]*[A-Za-z\d]*/' , $str , $http_addr);
        preg_match_all('/https:[\/]{2}[a-z]+[.]{1}[a-z\d\-]+[.]{1}[a-z\d]*[\/]*[A-Za-z\d]*[\/]*[A-Za-z\d]*/' , $str , $https_addr);
        $arr = array($http_addr , $https_addr);
        
        return $arr;
    }

    /**
     * 方法: 通过度仙门网的API发送邮件.
     * @param String $mail_address 目标邮箱邮件地址.
     * @param String $mail_username 显示在邮件里的目标邮箱地址的用户名[自定义].
     * @param String $mail_subject 邮件主题.
     * @param String $mail_text 邮件正文，可添加html代码.
     * @return String 从度仙门主站返回的json数据，包含邮件发送情况.
     */
    function DXM_SendMail($mail_address , $mail_username , $mail_subject , $mail_text){
        $token = $GLOBALS["_CONFIG"]["main"]["mail"]["api_token"];
        return file_get_contents("https://www.duxianmen.com/api/mail/index.php?mail_address=".$mail_address."&mail_subject=".urlencode($mail_subject)."&username=".urlencode($mail_username)."&mail_text=".urlencode($mail_text)."&token=".$token);
    }

    /**
     * 方法: 根据时间返回问候内容. 时区为中国上海.
     * @param String $time 时间参数[Unit时间戳]，不传入此项参数则使用当前时间 (可选).
     * @return String 返回早上好、中午好等问候内容.
     */
    function getGreet($time = null){
        if(is_null($time)){
            $time = time();
        }
        date_default_timezone_set('Asia/Shanghai');
        $h = date("H",$time);
        if($h<11) return "Hi！早上好~";
        else if($h<13) return "Hi！中午好~";
        else if($h<17) return "Hi！下午好~";
        else return "Hi！晚上好~";
    }

    /**
     * 方法: 获取文件后缀名.
     * @param String $file_name 文件名.
     * @return String 返回文件的后缀名.
     */
    function get_file_extension($file_name) {
        return substr(strrchr($file_name , '.') , 1);
    }

    /**
     * 方法: 基于exec方法执行多个命令.
     * @param Array $CommandGroup 需要执行的命令.
     * @return Array 执行的命令返回内容.
     */
    function MultiLineCommand($CommandGroup){
        if(!is_array($CommandGroup)){
            return YaoGuang\ErrorGroup::ERROR_NOT_ARRAY;
        }
        foreach($CommandGroup as $key => $value){
            $command = $value;
            exec($command , $output , $code);
            $return[$key]["return"] = $output;
            $return[$key]["code"] = $code;
            unset($output);
            unset($code);
        }
        return $return;
    }

    /**
     * 方法: 以POST请求URL.
     * @param String $url 目标HTTP地址.
     * @param Array $data 格式为索引数组的POST数据.
     * @return String 返回内容.
     */
    function file_post_contents($url , $data){

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER , FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST , FALSE);
        curl_setopt($curl, CURLOPT_USERAGENT , 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($curl, CURLOPT_POST , 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS , http_build_query($data));
        curl_setopt($curl, CURLOPT_TIMEOUT , 30);
        curl_setopt($curl, CURLOPT_HEADER , 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER , 1);

        $tmpInfo = curl_exec($curl);
        curl_close($curl);

        return $tmpInfo;
    }

    /**
     * 方法: 给文件大小加上单位，且可指定精确到几位小数 (传入经过filesize方法处理的数据).
     * @param String $size 经过filesize方法处理的文件大小数据.
     * @param Int $munber_format 精确到几位小数，不传入此项参数则默认精确到2位小数 (可选).
     * @return Array 返回内容.
     */
    function format_filesize($size , $mumber_format = 2){
        $length = strlen($size);
        if($length < 4){
            $return = array($size , "B");
        }elseif($length < 8){
            $return = array($size/1024 , "KB");
        }elseif($length < 12){
            $return = array($size/1048576 , "MB");
        }elseif($length < 16){
            $return = array($size/1073741824 , "GB");
        }elseif($length < 20){
            $return = array($size/1099511627776 , "TB");
        }elseif($length < 24){
            $return = array($size/1125899906842624 , "PB");
        }else{
            $return = false;
        }
        if($return != false){
            $return[0] = number_format($return[0] , 2);
        }
        return $return;
    }

    /**
     * 方法: 将文件以流的形式输出到浏览器，通常浏览器会将文件下载，建议不要输出大文件.
     * @param String $file_path 文件存贮路径.
     * @param String $file_name 浏览器显示的文件名，不传入此项参数则使用源文件名 (可选).
     * @return void
     */
    function fileflow_download($file_path , $file_name = null){
        //源文件名
        $source_file_name = basename($file_path);
        if(is_null($file_name)){
            $file_name = $source_file_name;
        }
        //检查文件是否存在
        if(!file_exists($file_path)){
            http_response_code(404);
        }else{
            //定义HTTP返回头
            header ("Content-type: application/octet-stream");
            header ("Accept-Ranges: bytes");
            header ("Accept-Length: ".filesize($file_path));
            header ("Content-Disposition: attachment; filename=".$file_name);
            //以只读和二进制模式打开文件
            $file = fopen($file_path , "rb");
            //读取文件内容并直接输出到浏览器
            echo fread($file , filesize($file_path));
            fclose($file);
        }
    }

    /**
     * 方法: 获取HTTP表头数据.
     * @return Array 以索引数组格式返回的HTTP表头.
     */
    function get_all_headers(){
        $headers = array();
        foreach($_SERVER as $key => $value){
            if(substr($key, 0, 5) === 'HTTP_'){
                $key = substr($key, 5);
                $key = str_replace('_', ' ', $key);
                $key = str_replace(' ', '-', $key);
                $key = strtolower($key);
                $headers[$key] = $value;
            }
        }
        return $headers;
    }

    /**
     * 方法: 发送使用curl发送http请求，可自定义header请求头和post数据.
     * @param String $url 目标HTTP地址.
     * @param Array $header 以索引数组格式存储的HTTP表头数据.
     * @param Array $post 以索引数组格式存储的POST数据.
     * @return String 返回内容.
     */
    function http_send($url , $header , $post){
        $ch = curl_init();
        if(substr($url , 0 , 5) == 'https'){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        $response = curl_exec($ch);
        if($error = curl_error($ch)){
            die($error);
        }
        curl_close($ch);
        return $response;
    }

    /**
     * 方法: 获取URL的状态码.
     * @param String $url 目标HTTP地址.
     * @return Int 目标地址返回的HTTP状态码，若返回值为 0 则代表目标地址无法访问(如加载超时、服务器拒绝连接).
     */
    function get_http_state($url){
        $ch = curl_init();
        if(substr($url , 0 , 5) == 'https'){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_NOBODY, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $head = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $httpCode;
    }

    /**
     * 方法: 以流的形式输出音频至浏览器.
     * @param String $filePath 以音频流格式输出mp3、wav等文件输出到浏览器.
     * @return void.
     */
    function print_mp3($filePath){
        $strContext = stream_context_create(array('http'=>array('method'=>'GET','header'=>"Accept-language: zh-cn\r\n")));
        $fpOrigin = fopen($filePath, 'rb', false, $strContext);
        header('Content-disposition: inline; filename="index.mp3"');
        header('Pragma: no-cache');
        header('Content-type: audio/mpeg');
        header('Content-Length: '.filesize($filePath));
        while(!feof($fpOrigin)){
            $buffer=fread($fpOrigin, 4096);
            echo $buffer;
            flush();
        }
        fclose($fpOrigin);
    }

    /**
     * 方法: 随机数组中的某个元素
     * @param Array $eleList 需要随机取元素的数组.
     * @param Int $getRanNum 随机取的元素个数，默认为一个 (可选).
     * @return Array 随机元素.
     */
    function randGetArrEle($eleList , $getRanNum = 1){
        $getRanEle = [];
        $eleNum = count($eleList);

        for($getRanNum; $getRanNum > 0; $getRanNum--){
            $arrKey = rand(0 , $eleNum-1);
            //检测是否和已经选中的冲突
            while(in_array($eleList[$arrKey] , $getRanEle)){
                $arrKey = rand(0 , $eleNum-1);
            }
            array_push($getRanEle , $eleList[$arrKey]);
        }
        return $getRanEle;
    }

    /**
     * 方法: 逐行读取文件.
     * @param String $filePath 文件路径.
     * @return Array 索引数组格式的数据.
     */
    function file_rll($filePath){
        $file_data = file_get_contents($filePath);
        $list = explode(PHP_EOL , $file_data);
        $return = [];
        foreach($list as $value){
            if($value == "" || is_null($value)){
                continue;
            }
            array_push($return , $value);
        }
        return $return;
    }

    /**
     * 方法: 从URL中获取域名.
     * @param $url URL地址
     */
    function getDomain($url) {
        $pattern = '/^(?:https?:\/\/)?(?:[^@\n]+@)?(?:www\.)?([^:\/\n]+)/im';
        preg_match($pattern, $url, $matches);
        return $matches[1];
    }

    /**
     * 方法: 生成随机字符串
     * @param $length 字符串的位数，默认13位 (可选)
     */
    function generate_unique_string($length = 13) {
        $prefix = '';
        if (function_exists('posix_getpid')) {
            $prefix = str_pad(posix_getpid() % 8192, 5, '0', STR_PAD_LEFT) . '_';
        }
        return $prefix . substr(uniqid('', true), 0, $length);
    }

    /**
     * 方法: 删除一整个目录及其子目录和文件
     * @param $dir 目录
     */
    function deleteDirectory($dir) {
        if (!is_dir($dir)) {
            return;
        }
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file != "." && $file != "..") {
                $path = $dir . "/" . $file;
                if (is_dir($path)) {
                    deleteDirectory($path);
                } else {
                    unlink($path);
                }
            }
        }
        rmdir($dir);
    }