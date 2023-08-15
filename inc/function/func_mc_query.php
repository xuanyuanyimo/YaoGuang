<?php
    /**
     * 摇光PHP框架
     * 文件类型: 扩展文件
     * 开发日期: 2023 08 04
     */

    if(!defined('IN_YGF')) {
        exit('Access Denied');  
    }

    $_Extend_INFO["func_mc_query"] = array(
        "ExtendName" => "func_mc_query" ,
        "ExtendVersion" => 1.0 ,
        "ExtendNecessity" => null
    );

    /**
     * Minecraft 服务器查询类: 查询在线人数
     */
    class MinecraftServerQuery{

        #服务器离线错误
        public const ERROR_SERVER_OFFLINE = "ERROR_SERVER_OFFLINE";

        public $ServerInfo;

        public $APIDefaultToken;

        public function __construct(){

            if(isset($GLOBALS["_CONFIG"]["func_mc_query"]) && is_array($GLOBALS["_CONFIG"]["func_mc_query"])){
                $this->ServerInfo = $GLOBALS["_CONFIG"]["func_mc_query"]["ServerInfo"];
            }
            $this->APIDefaultToken = $GLOBALS["_CONFIG"]["api"]["mc_server_info"]["token"];

        }

        /**
         * 成员方法: 无缓存获取 Minecraft 服务器信息
         * @param String $ServerHost Minecraft 服务器主机名或IP地址
         * @param Int $ServerPort Minecraft 服务器端口号
         * @param String $APItoken API接口TOKEN
         * @return String API返回报文 (JSON格式)
         */
        public function GetServerInfo($ServerHost , $ServerPort , $APItoken = null){

            // 如果没有定义token，则直接使用默认token
            if(is_null($APItoken)){
                $APItoken = $this->APIDefaultToken;
            }

            // 构建 POST 数据
            $postData = array(
                'ServerHost' => $ServerHost ,
                'ServerPort' => $ServerPort
            );
        
            // 初始化 cURL
            $ch = curl_init();
        
            // 设置 cURL 选项
            curl_setopt($ch, CURLOPT_URL, 'https://www.duxianmen.com/api/mc_server_info/?token=' . $APItoken);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
            // 执行 cURL 请求
            $response = curl_exec($ch);
        
            // 检查是否有错误发生
            if (curl_errno($ch)) {
                $error = curl_error($ch);
                curl_close($ch);
                return 'Error: ' . $error;
            }
        
            // 关闭 cURL
            curl_close($ch);
        
            // 返回服务器响应
            return $response;
        }

        /**
         * 成员方法: 缓存获取 Minecraft 服务器信息
         * @param String $ServerHost Minecraft 服务器主机名或IP地址
         * @param Int $ServerPort Minecraft 服务器端口号
         * @param Int $OverTime 缓存超时时间，超时后会重新调用一次API并写入缓存，单位: 秒 (可选)
         * @param String $APItoken API接口TOKEN，若不填，则使用默认TOKEN (可选)
         * @return String 缓存后的 Minecraft 服务器信息
         */
        public function ReturnServerData($ServerHost = null , $ServerPort = null , $OverTime = 120 , $APItoken = null){

            $CacheOBJ = new YaoGuang\cache;

            if(is_null($ServerHost) || is_null($ServerPort)){
                $ServerHost = $this->ServerInfo["ServerHost"];
                $ServerPort = $this->ServerInfo["ServerPort"];
            }

            // 检测缓存状态
            if(
                ($CacheOverTime = $CacheOBJ->cache_is_overtime("MinecraftServerData" , $ServerHost . "_Data.phpon")) == $CacheOBJ::ERROR_NOT_FOUND
                || $CacheOverTime === true
            ){
                // 获取服务器信息并设置缓存
                $ServerData = json_decode($this->GetServerInfo($ServerHost , $ServerPort , $APItoken) , true);
                
                // 检测错误信息是否存在
                if(
                    isset($ServerData["Error"])
                ){
                    $ErrorArray = array(
                        "Error" => $ServerData["Error"]
                    );
                    // 将错误缓存
                    $CacheOBJ->set_cache("MinecraftServerData" , $ErrorArray , $ServerHost . "_Data.phpon" , $OverTime);
                    // 返回错误
                    return $this::ERROR_SERVER_OFFLINE;
                }else{
                    // 没有错误发生
                    $CacheOBJ->set_cache("MinecraftServerData" , $ServerData , $ServerHost . "_Data.phpon" , $OverTime);
                }
            }

            // 获取缓存内容
            if(isset($ServerData)){
                // 如果刚刚已经从API获取了服务器信息
                $CacheData = $ServerData;
            }else{
                // 如果有缓存
                $CacheData = $CacheOBJ->cache_static_get_data("MinecraftServerData" , $ServerHost . "_Data.phpon");
            }

            // 检测错误信息是否存在
            if(
                isset($CacheData["Error"])
            ){
                // 返回错误
                return $this::ERROR_SERVER_OFFLINE;
            }

            $ServerModArr = [];

            // 模组列表
            foreach($CacheData["forgeData"]["mods"] as $key => $value){
                array_push($ServerModArr , $CacheData["forgeData"]["mods"][$key]);
            }

            // 返回数据
            return array(
                // MOTD
                "MOTD" => $CacheData["description"]["text"] ,
                // 服务器版本
                "version" => $CacheData["version"]["name"] ,
                // 在线人数
                "players" => array(
                    "max" => $CacheData["players"]["max"] ,
                    "online" => $CacheData["players"]["online"]
                ) ,
                // 模组信息
                "mods" => array(
                    // 模组数量
                    "quantity" => (isset($CacheData["forgeData"]["mods"]))?(count($CacheData["forgeData"]["mods"])):(null) ,
                    // 模组列表
                    "list" => (isset($CacheData["forgeData"]["mods"]))?($ServerModArr):(null)
                )
            );
        }
    }