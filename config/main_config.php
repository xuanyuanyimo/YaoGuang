<?php
    /**
     * 摇光PHP框架
     * 文件类型: 配置文件
     */

    #站点名称
    $_CONFIG["main"]["website_name"] = "摇光框架";

    #程序运行目录 (相对于站点根目录)
    $_CONFIG["main"]["current"]["directory"] = "";
    
    #当前启用的模板
    $_CONFIG["main"]["template"] = "default";

    #是否不限制PHP脚本运行的最大时长
    $_CONFIG["main"]["TIME_LIMIT"] = true;

    #是否为开发环境
    $_CONFIG["main"]["IS_IDE"] = true;
    
    #数据库基础信息
    $_CONFIG["main"]["database"] = array(
        //数据库类型
        "type" => "mysql",
        //数据库地址
        "address" => "localhost",
        //数据库名
        "dbname" => "mc_com",
        //数据库用户名
        "username" => "mc_com",
        //数据库密码
        "password" => "123456"
    );

    #度仙门主站邮件API的TOKEN
    $_CONFIG["api"]["email"]["token"] = "";
    #度仙门主站 Minecraft 查询API的TOKEN
    $_CONFIG["api"]["mc_server_info"]["token"] = "aD7yD0qV6xK3oI2dL2eM9cC8fE3eW2eZ";

    # api.php 是否开启跨域 (使用Ajax务必开启)
    $_CONFIG["main"]["users"]["set_up"]["cross_domain"] = true;

    /* 用户系统 */

    #注册登录用户名长度限制
    $_CONFIG["main"]["users"]["name"]["limitation"] = array(
        "Min" => 3 ,
        "Max" => 32
    );
    #注册登录密码长度限制
    $_CONFIG["main"]["users"]["passwd"]["limitation"] = array(
        "Min" => 8 ,
        "Max" => 64
    );

    #注册后是否直接登录，
    $GLOBALS["_CONFIG"]["main"]["register"]["ver"] = true;

    #注册登录短期次数限制
    $_CONFIG["main"]["users"]["use_count"] = 5;
    #注册登录最短时间限制
    $_CONFIG["main"]["users"]["use_time"] = 3;
    
    #用户默认权限
    $_CONFIG["main"]["users"]["permission"] = 000;
    #用户默认用户组
    $_CONFIG["main"]["users"]["usergroup"] = "default";
    #用户默认简介
    $_CONFIG["main"]["users"]["introduce"] = "这个人其实不懒，只是TA还不知道要写些什么罢了";

    #站内积分系统
    $_CONFIG["main"]["users"]["money"]["01"] = array("name" => "金钱" , "initial" => 0);
    $_CONFIG["main"]["users"]["money"]["02"] = array("name" => "威望" , "initial" => 0);
    $_CONFIG["main"]["users"]["money"]["03"] = array("name" => "贡献" , "initial" => 0);
    $_CONFIG["main"]["users"]["money"]["04"] = array("name" => "坤币" , "initial" => 0);

    #网站版权信息
    $_CONFIG["main"]["copyright"] = 'Copyright © 2018-<script>document.write(new Date().getFullYear());</script>, <a href="https://www.duxianmen.com" target="_blank" title="度仙门工作室">Duxian</a> studio.';

    # Minecraft 服务器信息
    $_CONFIG["func_mc_query"]["ServerInfo"] = array(
        //服务器地址 IPAddress or Domain
        "ServerHost" => "mcp2.rhymc.com" ,
        //服务器端口
        "ServerPort" => 1157
    );