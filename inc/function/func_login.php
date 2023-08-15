<?php
    /**
     * 摇光PHP框架
     * 文件类型: 扩展文件
     * 开发日期: 2023 02 22
     */

    if(!defined('IN_YGF')) {
        exit('Access Denied');  
    }

    $_Extend_INFO["func_login"] = array(
        "ExtendName" => "func_login" ,
        "ExtendVersion" => 2.5 ,
        "ExtendNecessity" => null
    );

    /**
     * 用户处理类: 使用数据库处理用户信息，可登录注册.
     * 特别注意: 在传入参数之前，请确认是否将特殊字符(如单引号和双引号)转义或删除，否则可能会生成注入点.
     */
    class UsersHandle
    {

        #已登录错误
        const ERROR_LOGGED = "ERROR_LOGGED";
        #密码错误
        const ERROR_PASSWORD = "ERROR_PASSWORD";
        #无此用户错误
        const ERROR_NO_USER = "ERROR_NO_USER";

        #同名用户错误
        const ERROR_SAMENAME = "ERROR_SAMENAME";
        #未知错误
        const ERROR_UNKNOWN = "ERROR_UNKNOWN";
        #未知错误
        const UNKNOWN_METHOD = "UNKNOWN_METHOD";
        #邮箱被占用
        const ERROR_SAMEMAIL = "ERROR_SAMEMAIL";

        #余额不足
        const MONEY_LACK = "MONEY_LACK";

        public $database_obj;

        #用户数据表
        public $database_table = "xusers";

        /**
         * 构造方法: 与数据库建立连接，并检测是否存在用户数据表.
         * @return void
         */
        public function __construct(){

            $this->database_obj = new YaoGuang\DbOperation;

            if(!$this->database_obj->db_exists_table($this->database_table)){
                $this->database_obj->db_create_table($this->database_table ,
                array("username" , "password" , "email" , "usergroup" ,
                "permission" => array("DataType" => "INT" , "DataLength" => 6) ,
                "money01" => array("DataType" => "INT" , "DataLength" => 16) ,
                "money02" => array("DataType" => "INT" , "DataLength" => 16) ,
                "money03" => array("DataType" => "INT" , "DataLength" => 16) ,
                "money04" => array("DataType" => "INT" , "DataLength" => 16) ,
                "introduce" => array("DataType" => "TEXT" , "DataLength" => 512) ,
                "other" => array("DataType" => "TEXT" , "DataLength" => 512)
                # 这个字段"other"的本意是使这里可以存储更多可以在数据表已经被创建过后但仍需增加字段的数据
                )
            );
            }
        }

        /**
         * 成员方法: 随机token.
         * @return String 32位md5字符串.
         */
        public function rand_token(){
            $return = md5(rand(1 , 512));
            return $return;
        }
    
        /**
         * 成员方法: 登录验证 设置cookie及创建登录验证缓存.
         * @param String $username 设置登录验证cookie的用户名.
         * @param Bool $online 是否设置为在线状态 (可选).
         * @param Int $overtime 设置登录状态cookie的过期时间，单位: 秒
         * @return void
         */
        public function set_cookie($username , $online = true , $overtime = null){
            $time = time();
            if(is_null($overtime)){
                $overtime = 0;
            }

            $token = $this->rand_token();
    
            //设置cookie
            setcookie("online" , $online, $overtime);
            setcookie("username" , base64_encode($username), $overtime);
            setcookie("token" , $token, $overtime);
            setcookie("login_time" , $time, $overtime);
    
            //创建缓存
            $obj = new YaoGuang\cache();
            $obj->set_cache("users_verification" , array("ver_token" => $token , "ver_time" => $time) , $username . "_ver_data.phpon" , 86400);
        }

        /**
         * 成员方法: 退出登录 删除cookie及登录验证缓存.
         * @param String $username 设置退出登录cookie的用户名.
         * @return Bool 若成功删除，则返回true，否则返回false.
         */
        public function log_out($username){
            $time = time();
            $overtime = $time - 3600;
    
            //设置cookie
            setcookie("online" , false, $overtime);
            setcookie("username" , false, $overtime);
            setcookie("token" , false, $overtime);
            setcookie("login_time" , false, $overtime);
    
            //清除缓存
            $obj = new YaoGuang\cache();
            if($obj->cache_delete("users_verification" , $username . "_ver_data.phpon")){
                return true;
            }
            return false;
        }

        /**
         * 静态成员方法: 检测是否登录.
         * @return Bool 若用户已登录，则返回true，否则返回false.
         */
        public static function ver_login(){
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
                //初始化摇光缓存类
                $obj = new YaoGuang\cache();
                //验证是否存在用户登录验证文件
                if($obj->cache_exists("users_verification" , $username . "_ver_data.phpon")){
                    //存在验证文件
                    $cache_data = $obj->cache_static_get_data("users_verification" , $username . "_ver_data.phpon");
                    //验证客户端验证信息是否和服务端匹配
                    if($_COOKIE["token"] == $cache_data["ver_token"] && $_COOKIE["login_time"] == $cache_data["ver_time"]){
                        return true;
                    }
                }
            }
            return false;
        }
    
        /**
         * 成员方法: 登录.
         * @param Int|String $username 登录的用户名或UID.
         * @param String $password 登录的密码.
         * @param Bool $encode 是否需要加密为md5再处理 (可选).
         * @param Int $overtime 登录验证cookie过期时间，单位: 秒 (可选).
         * @return String 根据UserHandle类中的成员常量组判断登录状态，若返回SUCCESS_LOGIN则代表登录成功，依次类推.
         */
        public function login($username , $password , $encode = true , $overtime = null){
    
            ($encode)?($password = md5($password)):(true);

            if ($this->ver_login()){
                return $this::ERROR_LOGGED;
            }

            $userdata = $this->database_obj->db_query_data($this->database_table , "Byusername" , $username);
            $userQueryMethod = "username";
            //如果用户名模式无法搜索到用户，尝试将之作为id搜索
            if($userdata == []){
                $userdata = $this->database_obj->db_query_data($this->database_table , "Byid" , $username);
                $userQueryMethod = "id";
            }
            //如果用户名模式无法搜索到用户，尝试将之作为邮箱地址搜索
            if($userdata == []){
                $userdata = $this->database_obj->db_query_data($this->database_table , "Byemail" , $username);
                $userQueryMethod = "email";
            }
            
            //判断是否有该用户存在
            if ($userdata != []){
                //判断md5加密后用户输入的密码是否于内部数据匹配
                if($password == $userdata[0]["password"]){
                    //密码正确
                    //判断是否需要保留自定义时长的登录信息
                    if(is_null($overtime)){
                        $this->set_cookie($username , true , $overtime);
                        return true;
                    }
                    //创建cookie和验证文件
                    $this->set_cookie($username);
                    return true;
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
    
            ($encode)?($password = md5($password)):(true);

            if ($this->ver_login()){
                return $this::ERROR_LOGGED;
            }

            $userdata = $this->database_obj->db_query_data($this->database_table , "Byusername" , $username);
            $userdata_id = $this->database_obj->db_query_data($this->database_table , "Byid" , $username);
            $userdata_email = $this->database_obj->db_query_data($this->database_table , "Byemail" , $username);

            if ($userdata != [] || $userdata_id != [] || $userdata_email != []){
                //用户存在
                return $this::ERROR_SAMENAME;
            }else{
                //检测邮箱是否已经被占用
                if($userdata = $this->database_obj->db_query_data($this->database_table , "Byemail" , $email) != []){
                    //已被占用
                    return $this::ERROR_SAMEMAIL;
                }
                //用户不存在，插入数据
                $this->database_obj->db_insert_data($this->database_table ,
                array("username" => $username , "password" => $password , "email" => $email ,
                "usergroup" => $GLOBALS["_CONFIG"]["main"]["users"]["usergroup"] ,
                "permission" => $GLOBALS["_CONFIG"]["main"]["users"]["permission"] ,
                "money01" => $GLOBALS["_CONFIG"]["main"]["users"]["money"]["01"]["initial"] ,
                "money02" => $GLOBALS["_CONFIG"]["main"]["users"]["money"]["02"]["initial"] ,
                "money03" => $GLOBALS["_CONFIG"]["main"]["users"]["money"]["03"]["initial"] ,
                "money04" => $GLOBALS["_CONFIG"]["main"]["users"]["money"]["04"]["initial"] ,
                "introduce" => $GLOBALS["_CONFIG"]["main"]["users"]["introduce"] ,
                "other" => "[]"
                )
        );
                $userdata = $this->database_obj->db_query_data($this->database_table , "Byusername" , $username);
                //检测是否成功创建用户
                if($userdata != []){
                    //创建cookie和验证文件
                    $this->set_cookie($username);
                    return true;
                }else{
                    return $this::ERROR_UNKNOWN;
                }
            }
        }

        /**
         * 成员方法: 修改用户的密码
         * @param Int|String $username 用户名或UID或邮箱地址
         * @param String $password 用户的新密码
         * @param Bool $encode 是否需要加密为md5再处理 (可选).
         * @return Bool 成功编辑则返回true，失败返回false，如果是其他情况，则会返回本类的某些常量.
         */
        public function setPasswd($username , $password , $encode = true){

            ($encode)?($password = md5($password)):(true);

            $userdata = $this->database_obj->db_query_data($this->database_table , "Byusername" , $username);
            $userQueryMethod = "username";
            //如果用户名模式无法搜索到用户，尝试将之作为id搜索
            if($userdata == []){
                $userdata = $this->database_obj->db_query_data($this->database_table , "Byid" , $username);
                $userQueryMethod = "id";
            }
            //如果用户名模式无法搜索到用户，尝试将之作为邮箱地址搜索
            if($userdata == []){
                $userdata = $this->database_obj->db_query_data($this->database_table , "Byemail" , $username);
                $userQueryMethod = "email";
            }

            if($userdata != []){
                return ($this->database_obj->db_update_field($this->database_table , "password" , $password , array($userQueryMethod => $username)) !== false)?(true):(false);
            }else{
                return $this::ERROR_NO_USER;
            }

        }
        /**
         * 成员方法: 编辑用户的积分
         * @param Int|String $username 需要操作的用户的用户名或用户UID
         * @param Int|Float $value 需要操作的修改数值
         * @param String $moneyName 需要操作的积分名，如money01，money02..... 如果使用不存在的积分类型则会报错，请在使用前检查参数
         * @param String $method 编辑方法，默认可选参数"set"/"add"/"red"/"reset"，分别为"编辑"，"增加"，"减少"，"重置"，如若留空，默认为编辑模式 如果减少积分时当前积分不足需要减少的数值，则不会继续扣除积分，且将返回MONEY_LACK，如果需要将积分减少至负数，则应使用set (可选)
         * @return Bool 成功编辑则返回true，失败返回false，如果是其他情况，则会返回本类的某些常量.
         */
        public function set_money($username , $value  , $moneyName , $method = "set"){

            $userdata = $this->database_obj->db_query_data($this->database_table , "Byusername" , $username);
            $userQueryMethod = "username";
            //如果用户名模式无法搜索到用户，尝试将之作为id搜索
            if($userdata == []){
                $userdata = $this->database_obj->db_query_data($this->database_table , "Byid" , $username);
                $userQueryMethod = "id";
            }
            
            //判断是否有该用户存在
            if ($userdata != []){
                //首先获取需要操作的积分余额
                $userMoneyValue = $userdata[0][$moneyName];
                //选择操作类型
                switch($method){
                    case "set":
                        return ($this->database_obj->db_update_field($this->database_table , $moneyName , $value , array($userQueryMethod => $username)) !== false)?(true):(false);
                    break;

                    case "add":
                        $userMoneyValue += $value;
                        return ($this->database_obj->db_update_field($this->database_table , $moneyName , $userMoneyValue , array($userQueryMethod => $username)) !== false)?(true):(false);
                    break;

                    case "red":
                        $userMoneyValue -= $value;
                        if($userMoneyValue < 0){
                            return $this::MONEY_LACK;
                        }
                        return ($this->database_obj->db_update_field($this->database_table , $moneyName , $userMoneyValue , array($userQueryMethod => $username)) !== false)?(true):(false);
                    break;

                    case "reset":
                        return ($this->database_obj->db_update_field($this->database_table , $moneyName , $GLOBALS["_CONFIG"]["main"]["users"]["money"][str_replace("money" , "" , $moneyName)]["initial"] , array($userQueryMethod => $username)) !== false)?(true):(false);
                    break;

                    default:
                        return $this::UNKNOWN_METHOD;
                }
            }else{
                return $this::ERROR_NO_USER;
            }
        }

        /**
         * 成员方法: 权限设置
         * @param Int|String $username 用户名或用户UID
         * @param Int $Permission 用户新权限
         * @return Bool|String 成功设置权限则返回true，失败则返回false 其他情况，则会返回本类的某些常量.
         */
        public function set_perm($username , $Permission){
            
            $userdata = $this->database_obj->db_query_data($this->database_table , "Byusername" , $username);
            $userQueryMethod = "username";
            //如果用户名模式无法搜索到用户，尝试将之作为id搜索
            if($userdata == []){
                $userdata = $this->database_obj->db_query_data($this->database_table , "Byid" , $username);
                $userQueryMethod = "id";
            }

            //判断是否有该用户存在
            if ($userdata != []){
                return ($this->database_obj->db_update_field($this->database_table , "permission" , $Permission , array($userQueryMethod => $username)) !== false)?(true):(false);
            }else{
                return $this::ERROR_NO_USER;
            }
        }

         /**
          * 成员方法: 检测权限是否足够，或查询权限等级
          * @param String $username 用户名或用户UID
          * @param Int $assignPermission 指定权限等级 如果留空则返回当前用户权限等级 (可选)
          * @return Bool|Int|String 如果所有参数全部填入，且用户所持有的权限高于或等于指定权限等级，则返回true，不足则返回false 如果$assignPermission参数未传入，则会返回当前用户所持有的权限等级。其他情况，则会返回本类的某些常量.
          */
        public function ver_perm($username , $assignPermission = null){

            $userdata = $this->database_obj->db_query_data($this->database_table , "Byusername" , $username);
            //如果用户名模式无法搜索到用户，尝试将之作为id搜索
            if($userdata == []){
                $userdata = $this->database_obj->db_query_data($this->database_table , "Byid" , $username);
            }

            //判断是否有该用户存在
            if ($userdata != []){
                $userPerm = $userdata[0]["permission"];
                //是否需要直接返回当前用户所持有的权限等级
                if($assignPermission == null){
                    return $userPerm;
                }
                return ($userPerm >= $assignPermission)?(true):(false);
            }else{
                return $this::ERROR_NO_USER;
            }
        }

        /**
         * 成员方法: 用户组设置
         * @param Int|String $username 用户名或用户UID
         * @param String $group 用户新组名
         * @return Bool|String 成功设置用户组则返回true，失败则返回false 其他情况，则会返回本类的某些常量.
         */
        public function set_group($username , $group){

            $userdata = $this->database_obj->db_query_data($this->database_table , "Byusername" , $username);
            $userQueryMethod = "username";
            //如果用户名模式无法搜索到用户，尝试将之作为id搜索
            if($userdata == []){
                $userdata = $this->database_obj->db_query_data($this->database_table , "Byid" , $username);
                $userQueryMethod = "id";
            }

            //判断是否有该用户存在
            if ($userdata != []){
                return ($this->database_obj->db_update_field($this->database_table , "usergroup" , $group , array($userQueryMethod => $username)) !== false)?(true):(false);
            }else{
                return $this::ERROR_NO_USER;
            }
        }

         /**
          * 成员方法: 检测用户是否属于用户组，或查询用户所在的用户组名
          * @param Int|String $username 用户名或用户UID
          * @param Int $userGroup 指定用户组名 如果留空则返回当前用户所在的用户组名 (可选)
          * @return Bool|Int|String 如果所有参数全部填入，且用户所在的用户组与指定用户组相同，则返回true，不同则返回false 如果$userGroup参数未传入，则会返回当前用户所在的用户组名。其他情况，则会返回本类的某些常量.
          */
          public function ver_group($username , $userGroup = null){

            $userdata = $this->database_obj->db_query_data($this->database_table , "Byusername" , $username);
            //如果用户名模式无法搜索到用户，尝试将之作为id搜索
            if($userdata == []){
                $userdata = $this->database_obj->db_query_data($this->database_table , "Byid" , $username);
            }

            //判断是否有该用户存在
            if ($userdata != []){
                $group = $userdata[0]["usergroup"];
                //是否需要直接返回当前用户所在的用户组
                if($userGroup == null){
                    return $group;
                }
                return ($group == $userGroup)?(true):(false);
            }else{
                return $this::ERROR_NO_USER;
            }
        }

        /**
         * 成员方法: 获取用户额外数据
         * @param Int|String $username 用户名或用户UID
         * @return Array 返回用户额外数据
         */
        public function get_other_data($username){
            
            $userdata = $this->database_obj->db_query_data($this->database_table , "Byusername" , $username);
            //如果用户名模式无法搜索到用户，尝试将之作为id搜索
            if($userdata == []){
                $userdata = $this->database_obj->db_query_data($this->database_table , "Byid" , $username);
            }

            //判断是否有该用户存在
            if ($userdata != []){
                //存在
                //检查是否存在数据
                if(!isset($userdata["other"]) || $userdata["other"] == []){
                    return array();
                }
                return json_decode($userdata["other"] , true);
            }else{
                return $this::ERROR_NO_USER;
            }
        }

        /**
         * 成员方法: 写入用户额外数据
         * @param Int|String $username 用户名或用户UID
         * @param Array $otherData 用户额外数据
         * @return Bool|String 若成功写入用户额外数据返回true，失败则返回false。其他情况，则会返回本类的某些常量.
         */
        public function set_other_data($username , $otherData){

            $userdata = $this->database_obj->db_query_data($this->database_table , "Byusername" , $username);
            $userQueryMethod = "username";
            //如果用户名模式无法搜索到用户，尝试将之作为id搜索
            if($userdata == []){
                $userdata = $this->database_obj->db_query_data($this->database_table , "Byid" , $username);
                $userQueryMethod = "id";
            }

            //判断是否有该用户存在
            if ($userdata != []){
                return ($this->database_obj->db_update_field($this->database_table , "other" , json_encode($otherData , JSON_UNESCAPED_UNICODE) , array($userQueryMethod => $username)) !== false)?(true):(false);
            }else{
                return $this::ERROR_NO_USER;
            }
        }


    }