<?php

if(!defined('IN_YGF')) {
    exit('Access Denied');  
}

//是否不限制PHP程序运行时长
if($_CONFIG["main"]["TIME_LIMIT"]){
    set_time_limit(0);
}

//是否为开发环境 or 生产环境
if(!$_CONFIG["main"]["IS_IDE"]){
    //生产环境 关闭错误提示
    error_reporting(0);
}
//由摇光框架接管PHP错误和异常提示
set_error_handler("YaoGuang\ErrorHandler");
set_exception_handler('YaoGuang\ExceptionHandler');

//检测页面类型
if(defined("PAGE_TYPE")){
    switch(PAGE_TYPE){
        case "INLET":
            //入口文件
            
            break;
        case "API":
            //API接口文件
            // header('Content-Type: application/json; charset=utf-8');
            
            //检测跨域请求是否被允许
            if($_CONFIG["main"]["users"]["set_up"]["cross_domain"]){
                //允许，开启跨域
                header("Access-Control-Allow-Origin: *");
            }
            break;
        default:
            header("Content-Type: text/html; charset=utf-8");
            if(intval(PAGE_TYPE)){
                //HTTP错误页 记录地址及错误类型写入日志
                $http_error_log = new YaoGuang\LogHandler;
                $http_error_log->log_write(null , array("HTTP - " . PAGE_TYPE . " | WebURL: " . $_SERVER['REQUEST_URI']));
            }
            break;
    }
}else{
    //定义为普通页面
    define("PAGE_TYPE" , "COMMON");
}