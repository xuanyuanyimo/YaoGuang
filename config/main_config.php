<?php
    #站点名称
    $_CONFIG["main"]["website_name"] = "离耀控制阵列";

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
        "dbname" => "com_com",
        //数据库用户名
        "username" => "root",
        //数据库密码
        "password" => "123456"
    );
    
    #度仙门主站API密匙
    $_CONFIG["main"]["mail"]["api_token"] = "aD7yD0qV6xK3oI2dL2eM9cC8fE3eW2eZ";
    
    #注册api.php程序是否开启跨域 (使用Ajax的修士务必开启)
    $_CONFIG["main"]["users"]["set_up"]["cross_domain"] = true;

    #本站API接口token，若调用接口，须在HTTP表头中写入: [token] => $_CONFIG["main"]["token"]
    $_CONFIG["main"]["token"] = "鸡你 实在太美 哦baby 实在是太美 多一眼就要爆炸 近一点快被融化 鸡你实在太美 哦baby 实在是太美 多一眼就会爆炸 近一点快被融化 干嘛? 干嘛 哈哈 啊嘛嗯 哦呀嗨嗨哟";

    #注册登录短期次数限制
    $_CONFIG["main"]["use_time"] = 10;