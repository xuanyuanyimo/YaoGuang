<?php
    /**
     * 摇光PHP框架
     * 文件类型: 扩展文件
     * 开发日期: 2023 06 03
     */

    if(!defined('IN_YGF')) {
        exit('Access Denied');  
    }

    $_Extend_INFO["func_mail"] = array(
        "ExtendName" => "func_mail" ,
        "ExtendVersion" => 1.0 ,
        "ExtendNecessity" => null
    );

    /**
     * 站内邮件类: 站内邮件系统核心
     * 特别注意: 
     * 1. 在传入参数之前，请确认是否将特殊字符(如单引号和双引号)转义或删除，否则可能会生成注入点.
     * 2. 本组件必须在安装了用户管理组件 (func_login.php) 的情况下使用.
     */
    class MailInSite{

        #未知错误
        public const ERROR_UNKNOW = "ERROR_UNKNOW";

        public $database_obj;

        #邮件数据表
        public $database_table = "xmails";

        /**
         * 构造方法: 与数据库建立连接，并检查是否存在邮件表.
         */
        public function __construct()
        {
            $this->database_obj = new YaoGuang\DbOperation;

            //是否使用自定义配置
            if(isset($GLOBALS["_CONFIG"]["func_mail"]) && is_array($GLOBALS["_CONFIG"]["func_mail"])){
                //取自定义表名
                $this->database_table = $GLOBALS["_CONFIG"]["func_mail"]["database_table"];
            }

            //检测是否存在数据表，不存在则创建它
            if(!$this->database_obj->db_exists_table($this->database_table)){
                $this->database_obj->db_create_table($this->database_table ,
                array(
                    "mail_title" ,
                    "mail_content" ,
                    "mail_sender" ,
                    "mail_receiver" ,
                    "mail_status" => array("DataType" => "INT", "DataLength" => 1)
                )
            );
            }
        }

        /**
         * 成员方法: 发送邮件
         * @param String $mail_title 邮件标题
         * @param String $mail_content 邮件正文
         * @param String $mail_sender 邮件发送者
         * @param String $mail_receiver 邮件接受者
         * @param Int $mail_send_time 邮件发送时间，请用Unit时间戳表示，若留空将使用当前时间 (可选)
         * @return true|String
         */
        public function send_mail($mail_title , $mail_content , $mail_sender , $mail_receiver){
            //向邮件表中写入数据
            if(!$this->database_obj->db_insert_data($this->database_table , array("mail_title" => $mail_title , "mail_content" => $mail_content , "mail_sender" => $mail_sender , "mail_receiver" => $mail_receiver , "mail_status" => 0))){
                return $this::ERROR_UNKNOW;
            }
            return true;
        }

        /**
         * 成员方法: 获取邮件
         * @param String $mail_receiver 收件人
         * @param Int $mail_time 邮件日期区间，单位为天，若该参数为5，将返回从现在到五天前这期间收到的所有邮件。如果该选项为空，则会返回所有的邮件！请在使用之前三思，若返回数据过多，后端将会花费更多时间从数据库取回数据以返回给前端。 (可选)
         * @return Array
         */
        public function get_mail($mail_receiver , $mail_time = null){
            //判断是否返回所有数据
            if($mail_time == null){
                $ReturnData = $this->database_obj->db_sql_query("SELECT * FROM " . $this->database_table . " WHERE mail_receiver='" . $mail_receiver ."'");
                return $ReturnData;
            }
            //取当前日期时间
            $NowTimeData = date("Y-m-d H:i:s" , ($time = time()));
            //计算时间
            $LastTimeData = date("Y-m-d H:i:s" , ($time - $mail_time * 86400));

            //获取数据
            $ReturnData = $this->database_obj->db_sql_query("SELECT * FROM " . $this->database_table . " WHERE mail_receiver='" . $mail_receiver ."' AND UNIX_TIMESTAMP(change_date) >= UNIX_TIMESTAMP('" . $LastTimeData . "') AND UNIX_TIMESTAMP(change_date) <= UNIX_TIMESTAMP('" . $NowTimeData . "')");
            return $ReturnData;
        }

        /**
         * 成员方法: 设置邮件读/未读状态
         * @param Int $mail_id 邮件ID
         * @param Bool $mail_status 邮件状态，true代表已读，false代表未读，如果此选项留空，则代表设置邮件为已读状态 (可选)
         * @return Bool true代表设置成功，false代表失败
         */
        public function set_mail_status($mail_id , $mail_status = true){
            return $this->database_obj->db_sql("UPDATE " . $this->database_table . " SET mail_status = " . (($mail_status)?(1):(0)) . " WHERE id = " . $mail_id);
        }

    }