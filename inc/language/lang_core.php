<?php
    if(!defined('IN_YGF')) {
        exit('Access Denied');  
    }

    #语言库配置
    $str_replace_array['{YGF_VERSION}'] = YGF_VERSION;
    
    #语言库变量配置
    //当前日期
    $str_replace_array['{today_date}'] = date('Y年m月d日',time());
    //当前脚本文件名
	$str_replace_array['{script_name}'] = htmlspecialchars($_SERVER["PHP_SELF"]);
    //主站定向链接
	$str_replace_array['{redirect_src}'] = 'https://www.duxianmen.com/?sd='.$_SERVER['HTTP_HOST'];
	//资源引用路径
	$str_replace_array['{file_path}'] = "./template/".$_CONFIG["main"]["template"];
    //标题
    $str_replace_array['{title}'] = det_title($_CONFIG["main"]["current"]["directory"]) . $footer_title;
    $str_replace_array['{website_name}'] = YGF;
    //客户端IP及服务器IP
    $str_replace_array['{client_ip_address}'] = getClientIP();
    $str_replace_array['{server_ip_address}'] = getServerIp();


    ##服务器基础信息
    //当前网站域名
    $str_replace_array['{domain_name}'] = $_SERVER['HTTP_HOST'];


    #区块判断配置
    $if_exist_array = null;
