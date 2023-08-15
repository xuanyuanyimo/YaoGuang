<?php

    /**
     * 核心方法: 限定一段时间内的使用次数
     * @param Int $max_count 一段时间内最大的验证次数
     * @param Int $time_limit 规定时间
     * @return Bool 如果超出限制，返回false，没超过则返回true
     */
    function Max_limit_count($max_count, $time_limit) {
        session_start();
        $session_id = session_id();
        $cache = new YaoGuang\cache();
        if (!$cache->cache_exists($session_id)) {
            $cache->set_cache($session_id, array("count" => 0), $session_id . ".phpon", $time_limit);
        }
        $count = $cache->cache_static_get_data($session_id, $session_id . ".phpon")["count"];
        if ($count >= $max_count) {
            return false;
        }
        $cache->set_cache($session_id, array("count" => $count + 1), $session_id . ".phpon", $time_limit);
        return true;
    }

    /**
     * 核心方法: 检测组件是否存在
     * @param String $extendName 需要检测的组件名，如 func_tool.php
     * @return Bool 如果存在组件返回true，否则返回false
     */
    function Extend_Exists($extendName) {
        if (array_key_exists($extendName , $GLOBALS['_ExtendList'])) {
            return true;
        }
        return false;
    }
    