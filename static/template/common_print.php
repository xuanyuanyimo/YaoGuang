<?php if(!defined('IN_YGF')){exit('Access Denied');} ?><!doctype html><html><head><meta charset="utf-8"><title><?php echo $print_title; ?></title><meta name="viewport"content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0"/><meta name="apple-mobile-web-app-capable"content="yes"/></head><body><div style="margin-left:10%;margin-top:5%;"><div style="margin-bottom:20px;"><img src="./static/images/<?php echo $print_type; ?>"height="120"></div><div style="font-size:20px;margin-bottom:20px;"><span id="time"style="font-size:18px;"><?php echo $print_message; ?></span></div><div><span style="font-size:12px;border-top:1px solid #ccc;color:#ccc;padding-top:2px;">摇光框架版本:<?php echo YGF_VERSION; ?>&nbsp;|&nbsp;PHP程序版本:<?php echo PHP_VERSION; ?></span></div></div></body></html>