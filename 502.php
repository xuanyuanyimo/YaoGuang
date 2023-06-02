<?php
    define("PAGE_TYPE" , 502);
    include("./inc/common.inc.php");

    $str_replace_array['{file_path}'] = str_repeat("." , substr_count($_SERVER['REQUEST_URI'] , "/"))."/template/".$_CONFIG["main"]["template"];
    
    //错误页模板
    tpl::phptpl_file( "./template/".$_CONFIG["main"]["template"]."/".PAGE_TYPE.".html" , $str_replace_array , null , null , $if_exist_array , null , true );