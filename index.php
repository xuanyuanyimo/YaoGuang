<?php
    /**
     * 摇光PHP框架
     * 文件类型: 主入口文件
     * 开发日期: 2023 01 30
     */
    define("PAGE_TYPE" , "INLET");
    include("./inc/common.inc.php");

    if(!isset($_GET["mods"]) || is_null($_GET["mods"])){
        $_GET["mods"] = "index";
    }
    $mods = $_GET["mods"];

    YaoGuang\PageOperation::jump_home();

    switch($mods){
        case 'index':
            //主页
            //include...
            tpl::phptpl_file( "./template/" . $_CONFIG["main"]["template"] . "/index.html" , $str_replace_array , null , null , $if_exist_array , null , true );

            break;
        default:
            $file = "./template/".$_CONFIG["main"]["template"] . "/" . $mods . ".html";
            if(file_exists($file)){
                //模板
                tpl::phptpl_file( $file , $str_replace_array , null , null , null , null , true );
            }else{
                http_response_code(404);
            }
    }
