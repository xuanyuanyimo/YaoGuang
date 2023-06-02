<?php

    if(!defined('IN_YGF')) {
        exit('Access Denied');  
    }

    /**
     * 用户处理类: 使用数据库处理用户信息，可登录注册.
     */
    class UsersHandle
    {

        const SUCCESS_LOGIN = "登录成功，将在二十四小时内免密登录.";
        const SUCCESS_REGIST = "注册成功，正在登录...";

        const ERROR_LOGGED = "您已登录，请不要重复登录或注册.";
        const ERROR_PASSWORD = "登录失败，密码错误.";
        const ERROR_NO_USER = "登录失败，不存在该用户.";

        const ERROR_SAMENAME = "注册失败，同名用户已经存在！";
        const ERROR_UNKNOWN = "注册失败，未知错误.";

        public $database_obj;

        #用户数据表
        public $database_table = "users";

        /**
         * 构造方法: 与数据库建立连接，并检测是否存在用户数据表.
         * @return void
         */
        public function __construct(){
            $this->database_obj = new YaoGuang\DbOperation;

            //是否使用自定义配置
            if(isset($GLOBALS["_CONFIG"]["func_login"]) && is_array($GLOBALS["_CONFIG"]["func_login"])){
                //取自定义表名
                $this->database_table = $GLOBALS["_CONFIG"]["func_login"]["database_table"];
            }

            if(!$this->database_obj->db_exists_table($this->database_table)){
                $this->database_obj->db_create_table($this->database_table , array("username" , "password" , "email"));
            }
        }

        /**
         * 成员方法: 随机token.
         * @return String 32位md5字符串.
         */
        public function rand_token(){
            $return = md5(rand(1,512));
            return $return;
        }
    
        /**
         * 成员方法: 登录验证 设置cookie及创建登录验证缓存.
         * @param String $username 设置登录验证cookie的用户名.
         * @param Bool $online 是否设置为在线状态 (可选).
         * @return void
         */
        public function set_cookie($username , $online = true){
            $time = time();
            $overtime = $time+86400;
            $token = $this->rand_token();
    
            //设置cookie
            setcookie("online", $online, $overtime);
            setcookie("username", base64_encode($username), $overtime);
            setcookie("token", $token, $overtime);
            setcookie("login_time", $time, $overtime);
    
            //创建缓存
            $obj = new YaoGuang\cache();
            $obj->set_cache("users_verification",array("ver_token"=>$token,"ver_time"=>$time),$username."_ver_data.json",86400);
        }

        /**
         * 成员方法: 退出登录 删除cookie及登录验证缓存.
         * @param String $username 设置退出登录cookie的用户名.
         * @return Bool 若成功删除，则返回true，否则返回false.
         */
        public function log_out($username){
            $time = time();
            $overtime = $time-3600;
    
            //设置cookie
            setcookie("online", false, $overtime);
            setcookie("username", false, $overtime);
            setcookie("token", false, $overtime);
            setcookie("login_time", false, $overtime);
    
            //清除缓存
            $obj = new YaoGuang\cache();
            if($obj->cache_delete("users_verification" , $username."_ver_data.json")){
                return true;
            }
            return false;
        }

        /**
         * 成员方法: 检测是否登录.
         * @return Bool 若用户已登录，则返回true，否则返回false.
         */
        public function ver_login(){
            if(
                !isset($_COOKIE["online"])
                || !isset($_COOKIE["username"])
                || !isset($_COOKIE["token"])
                || !isset($_COOKIE["login_time"])
            ){
                return false;
            }
            if($_COOKIE["online"]){
                $username = base64_decode($_COOKIE["username"]);
                $obj = new YaoGuang\cache();
                if($obj->cache_exists("users_verification" , $username."_ver_data.json")){
                    $cache_data = $obj->cache_static_get_data("users_verification" , $username."_ver_data.json");
                if($_COOKIE["token"] == $cache_data["ver_token"] && $_COOKIE["login_time"] == $cache_data["ver_time"]){
                    return true;
                }
                }
            }
            return false;
        }
    
        /**
         * 成员方法: 登录.
         * @param String $username 登录的用户名.
         * @param String $password 登录的密码.
         * @param Bool $encode 是否需要加密为md5再处理 (可选).
         * @return String 根据UserHandle类中的成员常量组判断登录状态，若返回SUCCESS_LOGIN则代表登录成功，依次类推.
         */
        public function login($username , $password , $encode = true){
    
            if($encode){$password = md5($password);}

            if ($this->ver_login()){
                return $this::ERROR_LOGGED;
            }

            $userdata = $this->database_obj->db_query_data($this->database_table , "Byusername" , $username);
            
            //判断是否有该用户存在
            if ($userdata != []){
                //判断md5加密后用户输入的密码是否于内部数据匹配
                if($password == $userdata[0]["password"]){
                    //密码正确
                    //创建cookie和验证文件
                    $this->set_cookie($username);
                    return $this::SUCCESS_LOGIN;
                }else{
                    //密码错误
                    return $this::ERROR_PASSWORD;
                }
            }else{
                return $this::ERROR_NO_USER;
            }
        }
        
        /**
         * 成员方法: 注册.
         * @param String $username 注册的用户名.
         * @param String $password 注册的密码.
         * @param String $email 注册的电子邮件地址.
         * @param Bool $encode 是否需要加密为md5再处理 (可选).
         * @return String 根据UserHandle类中的成员常量组判断注册状态，若返回SUCCESS_REGIST则代表注册成功，依次类推.
         */
        public function regist($username , $password , $email , $encode = true){
    
            if($encode){$password = md5($password);}

            if ($this->ver_login()){
                return $this::ERROR_LOGGED;
            }

            $userdata = $this->database_obj->db_query_data($this->database_table , "Byusername" , $username);

            if ($userdata != []){
                //用户存在
                return $this::ERROR_SAMENAME;
            }else{
                //用户不存在，插入数据
                $this->database_obj->db_insert_data($this->database_table , array("username"=>$username , "password"=>$password , "email"=>$email));
                $userdata = $this->database_obj->db_query_data($this->database_table , "Byusername" , $username);
                //检测是否成功创建用户
                if($userdata != []){
                    //创建cookie和验证文件
                    $this->set_cookie($username);
                    return $this::SUCCESS_REGIST;
                }else{
                    return $this::ERROR_UNKNOWN;
                }
            }
        }
    }