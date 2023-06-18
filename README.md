># YaoGuang PHP Frame -> 摇光PHP框架
>### 早期摇光框架地址: [摇光PHP框架](https://www.duxianmen.com/bbs/thread-175-1-1.html)

## 什么是摇光PHP框架？

##### 摇光框架是一个基于MVC的轻量、高效、灵活、安全的PHP框架，借助此框架，可以快速构建后端站点，且便于维护，可方便扩展.
##### 此框架开发于2023年1月30日 ~~前身是一个名为玄元框架的不规则shit山聚合体...~~

## 在使用之前

### 请确保服务器已安装以下PHP扩展:
* pdo_mysql
* curl
### 建议使用的伪静态 _ nginx
您也可以根据自己的需要对伪静态进行修改
```
location / 
{
    rewrite (.*).html /index.php?mods=$1 last;
}
```
### 建议使用的伪静态 _ apache
```
RewriteEngine On
RewriteRule ^(.*)\.html$ /index.php?mods=$1 [L]
```
### 配置文件
请根据你的站点信息配置`config`目录下的`main_config.php`文件

## 示例

##### 以下是框架主入口文件`index.php`基础示例

```
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
```
#### 这段代码意为:
- 在从框架根目录访问`index.html`或`index.php`时，都将自动转至 `https://www.youdoname.com/`上，即自动去除文件名，然后将模板目录下的模板文件`index.html`发送给用户
- 检查用户在地址栏输入的文件名，比如`https://www.youdoname.com/testpage.html`这个URL，框架就会从模板文件夹寻找名为testpage.html的文件，并通过框架带有的模板引擎处理后发送给用户，如果此文件不存在，将返回一个`HTTP 404`的状态码

#### 如果您想要在此文件定义什么文件名将发送什么内容给用户，可以添加一个`case`分支在`switch($mod)`下，例如:
```
switch($mods){
        case 'index':
            //主页
            //include...
            tpl::phptpl_file( "./template/" . $_CONFIG["main"]["template"] . "/index.html" , $str_replace_array , null , null , $if_exist_array , null , true );

            break;
        case 'yourpage.html':
            //自定义页面内容
            fileflow_download("./yourfile.dat");

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
```
#### 关于这段代码:
- 这段代码将使用户在访问`https://www.youdoname.com/yourpage.html`时，服务器将同目录的`./yourflie.dat`文件发送给用户的浏览器 (用户浏览器会弹出下载提示)
- `fileflow_download()`函数是`inc/function`目录下的扩展文件`func_tool.php`函数库的一部分，此函数用于以文件流形式将文件发送给用户浏览器

#### 除此之外，您可以通过将`fileflow_download("./yourfile.dat");`替换为:
```
tpl::phptpl_file( "./template/" . $_CONFIG["main"]["template"] . "/header.html" , $str_replace_array , null , null , $if_exist_array , null , true );
tpl::phptpl_file( "./template/" . $_CONFIG["main"]["template"] . "/body.html" , $str_replace_array , null , null , $if_exist_array , null , true );
tpl::phptpl_file( "./template/" . $_CONFIG["main"]["template"] . "/footer.html" , $str_replace_array , null , null , $if_exist_array , null , true );
```
#### 若入口文件聚合的多个页面同时引用了`header.html`或`footer.html`，您就可以修改这两个文件而不用每个页面文件都修改一次了
