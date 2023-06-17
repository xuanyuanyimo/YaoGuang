<?php

// PHP版本检测
if (PHP_VERSION < '5.3') {
    header('Content-Type:text/html; charset=utf-8');
    exit('你的服务器PHP的版本太低，摇光框架要求PHP版本不小于5.3');
}

//引用全局配置文件
require_once("./config/main_config.php");

//摇光框架版本
define('YGF_VERSION', 1.110);

//检测是否存在template模板目录
if(!file_exists("template")){
    mkdir("template");
}
if(!file_exists("template/" . $_CONFIG["main"]["template"])){
    mkdir("template/" . $_CONFIG["main"]["template"]);
}

if(!file_exists("config")){
    die("必要配置文件缺失");
}

define('IN_YGF', true);
define('YGF', $_CONFIG["main"]["website_name"]);
