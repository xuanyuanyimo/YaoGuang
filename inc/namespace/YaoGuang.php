<?php
/**
 * 摇光空间
 * 注意: 函数的参数数据类型说明中，不包括错误集合中的常量或某个类的常量.
 */
namespace YaoGuang;

/**
 * 错误集合: 定义许多常用的错误提示常量，方便使用.
 */
class ErrorGroup
{
    #数据类型错误
    const ERROR_NOT_ARRAY = "错误: 传入的数据必须是数组.";
    const ERROR_NOT_STRING = "错误: 传入的数据必须是字符串.";
    const ERROR_NOT_INT = "错误: 传入的数据必须是整数.";
    const ERROR_NOT_FLOAT = "错误: 传入的数据必须是浮点数.";

    #文件错误
    const ERROR_FILE_NOT_FOUND = "错误: 指定文件不存在.";
    const ERROR_FILE_SANE_NAME = "错误: 指定文件已有重名存在.";
}

/**
 * 提示信息类: 摇光框架错误输出方式.
 */
class TpPrint
{

    const ERROR_NO_REUSE = "错误: 不可重复调用问题输出方法.";

    /**
     * 成员方法: 输出一个操作成功或失败的模板.
     * @param String $print_message 输出内容正文.
     * @param String $print_title 输出内容标题 (可选).
     * @param Bool $print_type 输出模板为成功或者失败，true代表成功，false代表失败 (可选).
     * @return void
     */
    public static function common_print($print_message , $print_title = "错误信息" , $print_type = false){
        if($print_type){$print_type = "face01.png";}else{$print_type = "face02.png";}
        include("./static/template/common_print.php");
        echo PHP_EOL;
    }

    /**
     * 成员方法: 输出一个加载中的跳转页面，将会预加载指定URL内容.
     * @param String $jump_link 跳转链接.
     * @param String $print_title 输出内容标题 (可选).
     * @return void
     */
    public static function loading_print($jump_link , $print_title = "加载中"){
        include("./static/template/loading_print.php");
        echo PHP_EOL;
    }
}

/**
 * 缓存类: 在某些场景下，如调用api、对数据库内某些常用的字段进行调用时可以使用缓存，免去多次调用.
 */
class cache
{

    const EH = 3600;

    const ERROR_NOT_FOUND = "错误: 缓存文件不存在.";

    /**
     * 构造方法: 检测是否存在缓存目录.
     * @return void
     */
    public function __construct(){
        if(!file_exists("./cache/")){
            mkdir("./cache/");
        }
	}

    /**
     * 成员方法: 设置缓存内容，如果传入的缓存名不存在则自动创建新的缓存.
     * @param String $cache_name 缓存集合名.
     * @param Array $cache_data 缓存数据正文.
     * @param String $cache_file 缓存数据存贮的文件名 (可选).
     * @param Int $cache_update_time 缓存超时时间 (可选).
     * @return Mixed 假如缓存设置成功则返回写入缓存的数据，设置失败则返回false.
     */
    public function set_cache($cache_name , $cache_data , $cache_file = "data.phpon" , $cache_update_time = 120){
        //检测是否为数组
        if(!is_array($cache_data)){
            return ErrorGroup::ERROR_NOT_ARRAY;
        }
        if(!file_exists("./cache/".$cache_name."/")){
            //创建缓存目录及默认文件
            mkdir("./cache/".$cache_name);
        }
        $cache_config = array("cache_name"=>$cache_name,"cache_update_time"=>$cache_update_time);
        $cache_data["cache_regtime"] = time();
        
        if ( file_put_contents("./cache/".$cache_name."/config.phpon",serialize($cache_config)) != false
        && file_put_contents("./cache/$cache_name/$cache_file",serialize($cache_data)) != false ){
            return $cache_data;
        }
        return false;
	}

    /**
     * 成员方法: 静态获取缓存数据 (不刷新缓存修改时间).
     * @param String $cache_name 缓存集合名.
     * @param String $cache_file 缓存数据存贮的文件名 (可选).
     * @return Array 返回指定的缓存文件中的数据.
     */
    public function cache_static_get_data($cache_name , $cache_file = "data.phpon"){
        //检测是否存在缓存
        if($this->cache_exists($cache_name , $cache_file) != true){
            return $this::ERROR_NOT_FOUND;
        }
        $cache_data_phpon = file_get_contents("./cache/".$cache_name."/".$cache_file);
        $cache_data = unserialize($cache_data_phpon);
        return $cache_data;
	}

    /**
     * 成员方法: 检测缓存数据是否超时.
     * @param String $cache_name 缓存集合名.
     * @param String $cache_file 缓存数据存贮的文件名 (可选).
     * @return Bool 缓存超时则返回true，返回未超时则返回false.
     */
    public function cache_is_overtime($cache_name , $cache_file = "data.phpon"){
        //检测是否存在缓存
        if($this->cache_exists($cache_name , $cache_file) != true){
            return $this::ERROR_NOT_FOUND;
        }
        $cache_config = $this->cache_static_get_data($cache_name , "config.phpon");
        $cache_data = $this->cache_static_get_data($cache_name , $cache_file);

        $time = time();
        $dvalue = $time-$cache_data["cache_regtime"];
        if($dvalue > $cache_config["cache_update_time"]){
            return true;
        }
        return false;
	}
	
	/**
     * 成员方法: 检测缓存数据是否存在.
     * @param String $cache_name 缓存集合名.
     * @param String $cache_file 缓存数据存贮的文件名 (可选).
     * @return Bool 缓存文件存在则返回true，不存在则返回false.
     */
    public function cache_exists($cache_name , $cache_file = "data.phpon"){
        //检测是否存在缓存
        if(file_exists("./cache/".$cache_name."/".$cache_file)){
            return true;
        }
        return false;
	}

    /**
     * 成员方法: 删除缓存
     * @param String $cache_name 缓存集合名.
     * @param String $cache_file 缓存数据存贮的文件名 (可选).
     * @return Bool 缓存被成功删除则返回true，失败则返回false.
     */
    public function cache_delete($cache_name , $cache_file = "data.phpon"){
        //检测是否存在缓存
        if($this->cache_exists($cache_name , $cache_file)){
            if(unlink("$cache_name/$cache_file")){
                return true;
            }
            return false;
        }
        return $this::ERROR_NOT_FOUND;
	}
}

/**
 * 数据库操作类: 以PDO模式连接数据库，并将常用语句封装成函数.
 */
class DbOperation
{

    public $conn;
    public $db_config;
    public $log_obj;
    public $log_arr;

    const ERROR_RESERVED_FIEID_NAME = "错误: 你使用了保留字段名，这不被摇光框架允许.";
    const ERROR_TABLE_SAME_NAME = "错误: 已存在同名的数据表.";
    const ERROR_NO_TABLE = "错误: 指定的数据表不存在.";

    /**
     * 构造方法: 连接数据库.
     * @return void
     */
    public function __construct(){
        //将数据库信息从全局空间赋值给成员变量，方便其他成员方法调用
        $db_config = $GLOBALS["_CONFIG"]["main"]["database"];
        $this->db_config = $db_config;
        //日志处理类
        $this->log_obj = new LogHandler;
        $this->log_arr = [];
        //连接数据库
        $this->conn = new \PDO($db_config["type"].":host=".$db_config["address"].";dbname=".$db_config["dbname"], $db_config["username"], $db_config["password"]);
        $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    /**
     * 成员方法: 插入数据到数据表中.
     * @param String $db_table_name 数据表名.
     * @param Array $db_insert_data 需要插入的数据.
     * 示例: $db_insert_data = array("字段2"=>"字段2的值" , "字段3"=>"字段3的值" ......);
     * @return Bool 若成功的插入数据，返回true，失败则返回false
     */
    public function db_insert_data($db_table_name , $db_insert_data){
        //检测是否存在数据表
        if(!$this->db_exists_table($db_table_name)){
            return $this::ERROR_NO_TABLE;
        }
        //检测是否为数组
        if(!is_array($db_insert_data)){
            return ErrorGroup::ERROR_NOT_ARRAY;
        }

        $db_insert_key_enter = "";
        $db_insert_value_enter = "";
        foreach($db_insert_data as $key => $value){
            $db_insert_value_enter = $db_insert_value_enter."'$value',";
            $db_insert_key_enter = $db_insert_key_enter."`$key`,";
        }

        $sql = "INSERT INTO $db_table_name(`id`, $db_insert_key_enter `change_date`) VALUES (id, $db_insert_value_enter CURRENT_TIMESTAMP)";

        //将SQL语句执行情况写入日志
        array_push($this->log_arr , $sql);
        //执行SQL语句
        if($this->conn->exec($sql) !== false){
            return true;
        }
        return false;
    }

    /**
     * 成员方法: 修改数据表中的数据.
     * @param String $db_table_name 数据表名.
     * @param Array $db_update_data 要更新的数据.
     * 示例: $db_update_data = array("字段2"=>"字段2的值" , "字段3"=>"字段3的值" ......);
     * @param String $db_query_method 以何种方式查找要修改的列，Byid的意思的指以id在表中搜索[SQL: WHERE id = ...]. (可选).
     * @param Mixed $db_query_value 要查找的值 (可选).
     * @return Bool 若成功的修改数据，返回true，失败则返回false
     */
    public function db_update_data($db_table_name , $db_update_data  , $db_query_method = "Byid" , $db_query_value = 1){
        //检测是否存在数据表
        if(!$this->db_exists_table($db_table_name)){
            return $this::ERROR_NO_TABLE;
        }

        $db_update_data_enter = "";
        foreach($db_update_data as $key => $value){
            $db_update_data_enter = $db_update_data_enter."`$key`='$value',";
        }
        $field_name = substr(strrchr($db_query_method,'By'),2);
        $sql = "UPDATE $db_table_name SET $db_update_data_enter`change_date`=CURRENT_TIMESTAMP WHERE $field_name = $db_query_value";

        //将SQL语句执行情况写入日志
        array_push($this->log_arr , $sql);
        //执行SQL语句
        if($this->conn->exec($sql) !== false){
            return true;
        }
        return false;
    }
 
    /**
     * 成员方法: 在数据表中查询数据.
     * @param String $db_table_name 数据表名.
     * @param String $db_query_method 以何种方式查找要修改的列，Byid的意思的指以id在表中搜索[SQL: WHERE id = ...]. (可选).
     * @param Mixed $db_query_value 要查找的值 (可选).
     * @return Array 查询的结果集.
     */
    public function db_query_data($db_table_name , $db_query_method = null , $db_query_value = null){
        //检测是否存在数据表
        if(!$this->db_exists_table($db_table_name)){
            TpPrint::common_print($this::ERROR_NO_TABLE , "错误提示");
            return $this::ERROR_NO_TABLE;
        }

        //设置查询参数
        if(is_null($db_query_method) || is_null($db_query_value)){
            //返回所有数据
            $sql = "SELECT * FROM $db_table_name";
        }else{
            //自定义查询
            $field_name = substr(strrchr($db_query_method,'By'),2);
            $sql = "SELECT * FROM $db_table_name WHERE $field_name='$db_query_value'";
        }

        //将SQL语句执行情况写入日志
        array_push($this->log_arr , $sql);
        //开始向数据库发送查询请求
        $stmt = $this->conn->query($sql);
        // 设置结果集为关联数组
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $return = $stmt->fetchAll();

        return $return;
    }

    /**
     * 成员方法: 检测数据表是否存在.
     * @param String $db_table_name 数据表名.
     * @return Bool 若数据表存在则返回true，不存在则返回false.
     */
    public function db_exists_table($db_table_name){
        //用SQL查询是否存在指定数据表
        $sql = "SELECT COUNT(1) FROM information_schema.tables WHERE table_name = '$db_table_name'";
        $stmt = $this->conn->query($sql);
        // 设置结果集为关联数组
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $return = $stmt->fetchAll();

        if($return[0]["COUNT(1)"] == "0"){
            return false;
        }
        return true;
    }

    /**
     * 成员方法: 删除指定数据表.
     * @param String $db_table_name 数据表名.
     * @return Bool 若数据表成功被删除则返回true，失败则返回false.
     */
    public function db_delete_table($db_table_name){
        //检测是否存在数据表
        if(!$this->db_exists_table($db_table_name)){
            return $this::ERROR_NO_TABLE;
        }

        $sql = "DROP TABLE $db_table_name";

        //将SQL语句执行情况写入日志
        array_push($this->log_arr , $sql);
        //执行SQL语句
        if($this->conn->exec($sql) !== false){
            //检测数据表是否成功删除
            if(!$this->db_exists_table($db_table_name)){
                return true;
            }
        }
        return false;
        
    }

    /**
     * 成员方法: 新建一张数据表.
     * @param String $db_table_name 数据表名.
     * @param Array $db_table_field 字段设置.
     * 示例: array("test" , "test2"=>array("DataType"=>"INT" , "DataLength"=>"32") , ......);
     * 解释: $db_table_field 数组中假如有个索引元素("test")，那么它将会作为一个VARCHAR数据类型且最大长度为128的字段被设置; 假如如test2一样声明为二维数组，可以自定义字段数据类型和最大长度.
     * @return Bool 若数据表成功被创建则返回true，失败则返回false.
     */
    public function db_create_table($db_table_name , $db_table_field){
        //检测是否已经存在同名的数据表
        if($this->db_exists_table($db_table_name)){
            return $this::ERROR_TABLE_SAME_NAME;
        }
        //检测是否为数组
        if(!is_array($db_table_field)){
            return ErrorGroup::ERROR_NOT_ARRAY;
        }
        //检测传入的字段名集合是否含有保留字段名
        if(
            in_array("id" , $db_table_field)
            || in_array("change_date" , $db_table_field)
        ){
            //关闭数据库连接且退出程序
            return DbOperation::ERROR_RESERVED_FIEID_NAME;
        }

        $foreach_times = 0;
        foreach($db_table_field as $key => $value){
            //检测是否传入了自定义字段属性
            if(
                !is_array($db_table_field[$key])
            ){
                //设置为默认字段属性
                $db_table_field_enter[$foreach_times] = $value." VARCHAR(128) NOT NULL,";
                $foreach_times++;
            }else{
                //设置为自定义字段属性
                $db_table_field_enter[$foreach_times] = $key." ".$db_table_field[$key]["DataType"]."(".$db_table_field[$key]["DataLength"].") NOT NULL,";
                $foreach_times++;
            }
        }

        $db_table_field_enter = implode("" , $db_table_field_enter);
        $sql = <<<EOF
    CREATE TABLE $db_table_name (
        id INT(8) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        $db_table_field_enter
        change_date TIMESTAMP
    )
EOF;

        //将SQL语句执行情况写入日志
        array_push($this->log_arr , $sql);
        //执行SQL语句
        if($this->conn->exec($sql) !== false){
            //检测数据表是否成功创建
            if($this->db_exists_table($db_table_name)){
                return true;
            }
        }
        return false;
    }

    /**
     * 成员方法: 执行一段无返回的SQL语句.
     * @param String $sql SQL语句.
     * @return Bool SQL语句被成功的执行则返回true，否则返回false.
     */
    public function db_sql($sql){
        //将SQL语句执行情况写入日志
        array_push($this->log_arr , $sql);

        //执行SQL语句
        if($this->conn->exec($sql) !== false){
            return true;
        }
        return false;
        
    }

    /**
     * 成员方法: 执行一段有返回的SQL语句 [查询].
     * @param String $sql SQL语句.
     * @return Bool SQL语句被成功的执行则返回true，否则返回false.
     */
    public function db_sql_query($sql){
        //将SQL语句执行情况写入日志
        array_push($this->log_arr , $sql);
        //查询
        $stmt = $this->conn->query($sql);
        // 设置结果集为关联数组
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $return = $stmt->fetchAll();

        if($return === false){
            return false;
        }
        return $return;
    }

    /**
     * 析构方法: 关闭数据库连接
     * @return void
     */
    public function __destruct(){
        //关闭数据库连接
        $this->conn = null;
    }

}

/**
 * 数据上传类: 快速接收用户传来的文件.
 */
class UploadData
{
    const SUCCESS_UPLOAD = "文件上传成功.";

    const ERROR_NO_EXTENSION = "错误: 不允许使用的扩展名.";
    const ERROR_FILE_TOOBIG = "错误: 文件大小超出限制.";
    const ERROR_FILE_SAMENAME = "错误: 文件与已有文件重名.";

    const EXTENSION_NO_PHP = array("php");

    public $log_obj;

    /**
     * 构造方法: 检测是否存在文件存贮目录.
     * @return void
     */
    public function __construct(){
        //实例化日志操作类
        $this->log_obj = new LogHandler;

        if(!file_exists("./data/")){
            mkdir("./data/");
        }
	}

    /**
     * 成员方法: 监听用户是否上传.
     * @param String $file_dir 文件存储目录.
     * @param String $file_name 文件名字 (可选).
     * @param Array $extension 允许或不允许的文件扩展名集合 (可选).
     * @param Bool $extension_whitelist 扩展名检测为白名单模式还是黑名单模式，$extension设置后才有效 (可选).
     * @param Int $file_max_size 允许的文件最大的大小，以MB为单位 (可选).
     * @param String $file_KeyName 前端表单里input标签的name属性的值 (可选).
     * @return Bool 文件成功上传返回true，未收到用户上传的文件或上传失败则返回false.
     */
    public function monitor_upload($file_dir , $file_name = null , $extension = null , $extension_whitelist = true , $file_max_size = 2048 , $file_KeyName = "file"){
        //将$file_max_size的单位从MB转换为B
        $file_max_size = $file_max_size * 1024 * 1024;

        //检测是否传入了文件
        if(isset($_FILES[$file_KeyName])){

            //是否使用用户传入的文件的原名
            if(is_null($file_name)){$file_name = $_FILES[$file_KeyName]["name"];}

            if($_FILES[$file_KeyName]["size"] > $file_max_size){
                $this->log_obj->log_write(null , array("Class: ".__CLASS__." Function: ".__FUNCTION__." return: ".$this::ERROR_FILE_TOOBIG));
                return $this::ERROR_FILE_TOOBIG;
            }

            //检测指定的二级存贮目录是否存在.
            if(!file_exists("./data/".$file_dir)){
                mkdir("./data/".$file_dir);
            }

            //检测是以白名单模式还是黑名单模式筛选文件扩展名
            if(!is_null($extension)){
                if($extension_whitelist){
                    //白名单模式
                    $file_extension = get_file_extension($_FILES[$file_KeyName]["name"]);
                    if(!in_array($file_extension , $extension)){
                        $this->log_obj->log_write(null , array("Class: ".__CLASS__." Function: ".__FUNCTION__." return: ".$this::ERROR_NO_EXTENSION));
                        return $this::ERROR_NO_EXTENSION;
                    }
                }else{
                    //黑名单模式
                    $file_extension = get_file_extension($_FILES[$file_KeyName]["name"]);
                    if(in_array($file_extension , $extension)){
                        $this->log_obj->log_write(null , array("Class: ".__CLASS__." Function: ".__FUNCTION__." return: ".$this::ERROR_NO_EXTENSION));
                        return $this::ERROR_NO_EXTENSION;
                    }
                }
            }

            if(file_exists("./data/".$file_dir."/".$file_name)){
                $this->log_obj->log_write(null , array("Class: ".__CLASS__." Function: ".__FUNCTION__." return: ".$this::ERROR_FILE_SAMENAME));
                return $this::ERROR_FILE_SAMENAME;
            }

            //从缓存目录将文件移动至指定目录
            move_uploaded_file($_FILES[$file_KeyName]["tmp_name"] , "./data/".$file_dir."/".$file_name);
            if(file_exists("./data/".$file_dir."/".$file_name)){
                $this->log_obj->log_write(null , array("Class: ".__CLASS__." Function: ".__FUNCTION__." return: ".$this::SUCCESS_UPLOAD));
                return true;
            }
        }
        return false;
    }
}

/**
 * 页面操作类: 可以对页面进行重定向、URL精练.
 */
class PageOperation
{
	/**
     * 静态成员方法: 清除URL中的index.php或index.html，自动跳转到根.
     * @return void
     */
    public static function jump_home(){
        $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
        if (strpos($_SERVER['REQUEST_URI'],'index.php')){
            header('Location: '.$http_type.$_SERVER['HTTP_HOST'].str_replace('index.php', "", $_SERVER['REQUEST_URI']));
        }
        if (strpos($_SERVER['REQUEST_URI'],'index.html')){
            header('Location: '.$http_type.$_SERVER['HTTP_HOST'].str_replace('index.html', "", $_SERVER['REQUEST_URI']));
        }
    }

    /**
     * 静态成员方法: 跳转到指定URL.
     * @param String $link 要跳转到的URL (可选).
     * @return void
     */
    public static function jump_link($link = "/"){
        header('Location: '.$link);
    }

    /**
     * 静态成员方法: 检测上一个页面的URL.
     * @param String $url 要求检测的上一个页面地址.
     * @return Bool 假如客户端的上一个页面为指定的URL返回true，不是则返回false.
     */
    public static function ver_referer($url){
        if(isset($_SERVER["HTTP_REFERER"])){
            if($_SERVER["HTTP_REFERER"] == $url){
                return true;
            }else{
                return false;
            }
        }
        return false;
    }
}

/**
 * Web日志处理类: 写入及读取日志，对日志进行操作
 */
class LogHandler
{
    /**
     * 构造方法: 检测是否存在日志目录.
     * @return Void
     */
    public function __construct(){
        if(!file_exists("./log/")){
            mkdir("./log/");
        }
	}

    /**
     * 成员方法: 检测是否存在log文件.
     * @param String $log_name 不含后缀名的日志的名称.
     * @return Bool 若日志文件存在，则返回true.
     */
    public function log_exists($log_name){
        //检测是否存在Log文件
        if(file_exists("./log/".$log_name.".log")){
            return true;
        }
        return false;
    }

    /**
     * 成员方法: 添加Log文件.
     * @param String $log_name 不含后缀名的日志的名称 (可选).
     * @return Bool 若成功创建日志文件返回true，失败则返回false.
     */
    public function add_log($log_name = null){
        //检测是否指定了Log名
        if(is_null($log_name)){
            $log_name = "[Weblog]" . date("Y年m月d日");
        }else{
            $log_name = $log_name;
        }
        //检测log是否存在
        if(!$this->log_exists($log_name)){
            //不存在则创建
            fopen("./log/$log_name.log" , "x");
            $file_data = "FILE_NAME: " . $log_name . PHP_EOL . "CREATE_TIME: " . time() . PHP_EOL;
            if(file_put_contents("./log/$log_name.log" , $file_data) != false){
                return true;
            }
            return false;
        }
        //已存在同名Log文件，返回错误代码
        return ErrorGroup::ERROR_FILE_SANE_NAME;
    }

    /**
     * 成员方法: 往log中写入数据.
     * @param String $log_name 不含后缀名的日志的名称 (可选).
     * @param Array $log_data 日志数据正文，索引数组格式，每个键都会被逐行写入日志中.
     * @return true 写入日志后返回true.
     */
    public function log_write($log_name = null , $log_data){
        //检测是否指定了Log名
        if(is_null($log_name)){
            $log_name = "[Weblog]" . date("Y年m月d日");
        }
        //检测是否为数组
        if(!is_array($log_data) || $log_data == []){
            return ErrorGroup::ERROR_NOT_ARRAY;
        }
        //检测log是否存在
        if(!$this->log_exists($log_name)){
            //不存在则创建
            $this->add_log($log_name);
        }
        
        //定界
        file_put_contents("./log/$log_name.log" , "!>> START: ".time().PHP_EOL , FILE_APPEND);
        //逐行写入日志信息
        foreach($log_data as $key => $value){
            if(file_put_contents("./log/$log_name.log" , date('[Y年m月d日H点i分s秒]') . "[$key]->$value" . PHP_EOL , FILE_APPEND) === false){
                return false;
            }
        }
        file_put_contents("./log/$log_name.log" , "ENE: " . time() . " <<!" . PHP_EOL , FILE_APPEND);
        return true;
    }
}

/**
 * 摇光错误处理方法: 接管PHP自带的错误提示.
 */
function ErrorHandler($errno, $errstr, $errfile, $errline){
    //日志处理类实例化
    $log_obj = new LogHandler;
    switch($errno){
        case E_USER_ERROR:
            $error = "错误(ERROR): [$errno] -> $errstr<br/>\n产生错误的文件: $errfile [行号: $errline]";
            $log_obj->log_write(null , array($error));
            exit(TpPrint::common_print($error , "错误提示 - ".YGF));
            break;
    
        case E_USER_WARNING:
            $error = "警告(WARNING): [$errno] -> $errstr<br/>\n产生错误的文件: $errfile [行号: $errline]";
            $log_obj->log_write(null , array($error));
            exit(TpPrint::common_print($error , "错误提示 - ".YGF));
            break;

        case E_USER_NOTICE:
            $error = "注意(NOTICE): [$errno] -> $errstr<br/>\n产生错误的文件: $errfile [行号: $errline]";
            $log_obj->log_write(null , array($error));
            exit(TpPrint::common_print($error , "错误提示 - ".YGF));
            break;

        default:
            $error = "未知错误类型(Unknown error type): [$errno] -> $errstr<br/>\n产生错误的文件: $errfile [行号: $errline]";
            $log_obj->log_write(null , array($error));
            echo(TpPrint::common_print($error , "错误提示 - ".YGF));
            break;
    }
    return true;
}

/**
 * 摇光异常处理方法: 接管PHP自带的异常提示.
 */
function ExceptionHandler($exception) {
    //日志处理类实例化
    $log_obj = new LogHandler;
    $log_obj->log_write(null , array("程序异常: ".$exception->getMessage()));
    echo(TpPrint::common_print("未捕获异常: ".$exception->getMessage() , "异常提示 - ".YGF));
}
