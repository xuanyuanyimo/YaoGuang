<?php

    if(!defined('IN_YGF')) {
        exit('Access Denied');  
    }

    /**
     * 方法: 查找当前页面的标题.
     * @return String 返回当前页面的标题.
     */
    function det_title(){

        $path = basename($_SERVER['REQUEST_URI']);

        $tes = array
        (
            "首页" => $GLOBALS["_CONFIG"]["main"]["current"]["directory"] ,
            "你没鸡" => "test.html"
        );

        $tit = array_flip($tes);
        if(in_array($path,$tes)){

            define("PAGE_TITLE" , $tit[$path]);
            define("PAGE_PATH" , $path);

            return $tit[$path];
        }else{
            if(defined("PAGE_TYPE") && is_int(PAGE_TYPE)){
                return PAGE_TYPE."错误";
            }
            return "Error->>错误的访问参数！".$path;
        }
    }
