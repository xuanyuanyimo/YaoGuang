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
                $extendFilePath = "./config/" . $extendFile . "_config.php";
                //检测扩展是否存在自定义配置文件
                if(file_exists($extendFilePath)){
                    require_once($extendFilePath);
                    define($extendFile . "ExtendConfig" , true);
                }
            }
        }
    }

    //引入函数库
    include_library("function");
    
    //启动摇光框架
    require dirname(__FILE__) . '/core/start.php';

    //模板引擎
    require dirname(__FILE__) . './phptpl.inc.php';
    //引擎标签配置
    require dirname(__FILE__) . './language/lang_core.php';