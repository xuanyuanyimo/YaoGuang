<?php
    /**
     * 摇光PHP框架
     * 文件类型: API接口
     * 开发日期: 2023 01 30
     */
    define("PAGE_TYPE" , "API");
    include("./inc/common.inc.php");

    //检测是否传入了method参数
    if(!isset($_GET["method"])){
        //未传入
        exit(json_encode(array("return" , "错误: method未传入.") , JSON_UNESCAPED_UNICODE));
    }

    //清除所有传来的数据的单引号，防止SQL注入
    foreach($_POST as $key => $value){
        $_POST[$key] = preg_replace("/(['])/" , "" , $value);
    }

    //假如使用的mod模块为:login 或 regist
    if($_GET["method"] == "login" || $_GET["method"] == "regist"){

        if(!Max_limit_count($_CONFIG["main"]["users"]["use_count"] , $_CONFIG["main"]["users"]["use_time"])){
            exit(json_encode(array("return" , "错误: 登录或注册次数超出限制.") , JSON_UNESCAPED_UNICODE));
        }

        //如果使用http连接注册登录传入的密码参数请在前端加密成md5，这样更加安全，假如部署了安全性更高的SSL证书，可以把login或者regist方法的最后一个参数设置为false，这样后端就不会再次加密.
        //用jQuery的md5插件即可：$.md5("加密数据");
        //允许跨域传参，可在前端使用ajax

        //传入数据预处理
        if(
            !isset($_POST["username"])
            || !isset($_POST["password"])
        ){
            exit(json_encode(array("return" , "错误: 必要参数未填写.") , JSON_UNESCAPED_UNICODE));
        }else{
            if(
                strlen($_POST["username"]) < 3 && strlen($_POST["password"]) < 8
                && strlen($_POST["username"]) > 32 && strlen($_POST["password"]) > 64
            ){ exit(json_encode(array("return" , "错误: 用户名或密码长度不符合规范.") , JSON_UNESCAPED_UNICODE)); }
            //转义参数中可能带有的html字符
            $_POST["username"] = htmlentities($_POST["username"]);
            $_POST["password"] = htmlentities($_POST["password"]);
        }
        //登录&注册API，实例化用户处理类
        $user_obj = new UsersHandle;
    }

    //检测API种类
    switch ($_GET["method"]) {
        /**
         * 登录API
         */
        case 'login':
            $user_obj->login($_POST["username"] , $_POST["password"]);
            break;
        /**
         * 注册API
         */
        case 'regist':
            //注册需要传入Email参数
            if(isset($_POST["email"])){
                $user_obj->regist($_POST["username"] , $_POST["password"] , $_POST["email"]);
            }
            break;
        case 'test':
            echo (isset($_POST["param"])) ? "Your Post Param Is " . $_POST["param"] : "You Hav't To Conveying Post Param";
            break;
        default:
            exit(json_encode(array("return" , "错误: method不正确.") , JSON_UNESCAPED_UNICODE));
            break;
    }

