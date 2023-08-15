<?php
    //引用初始文件
    require dirname(__FILE__) . "/core/initial.php";
    //引用摇光空间
    require dirname(__FILE__) . "/namespace/YaoGuang.php";

	$footer_title = ' | ' . YGF . " Powered by YaoGuang";

    function include_library($file){
        $dir = __DIR__ . "/$file/";
        $files = scandir($dir , 1);
        foreach($files as $key => $value){
            $fileExtend = substr(strrchr($value , '.') , 1);
            if($fileExtend == "php"){
                //引入扩展
                require_once($dir . $value);
                $extendFile = str_replace(".php" , "" , $value);
                //扩展表
                $GLOBALS["_ExtendList"][$extendFile] = $value;
            }
        }
    }

    //引入函数库
    include_library("function");
    
    //启动摇光框架
    require dirname(__FILE__) . '/core/start.php';

    //使用核心函数库
    require dirname(__FILE__) . '/core/function.php';
    

    //模板引擎
    require dirname(__FILE__) . '/phptpl.inc.php';
    //标题控制器
    require dirname(__FILE__) . '/title.inc.php';
    //引擎标签配置
    require dirname(__FILE__) . '/language/lang_core.php';