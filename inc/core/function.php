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
            $cache->set_cache($session_id, array("count" => 0), $session_id . ".json", $time_limit);
        }

        $count = $cache->cache_static_get_data($session_id, $session_id . ".json")["count"];

        if ($count >= $max_count) {
            return false;
        }

        $cache->set_cache($session_id, array("count" => $count + 1), $session_id . ".json", $time_limit);

        return true;
    }
