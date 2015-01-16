<?php

	function __autoload($class){
		if(file_exists($class.'_class.php')){
			require_once($class.'_class.php');
		}else{
			throw new Exception($class.'_class.php not found');
		}
	}
	
	
	//1，除了下载固定页的图片
	//2，找到下一页的连接，请求下一页
	//3,下载新的一页的图片。
	//4，重复以上这个过程，直到找不到下一页连接。或者磁盘满。
	//5,需要另外开启一个实例，获取上一页的数据。过程同上。

	$source_url = 'http://zhengyuvision.lofter.com/?page=2';
	$root = '';
	$dir = 'pics';
	$pattern = '/((?<=(\ src=\"))|(?<=(\ real_src=\")))\S*\.(png|jpg|jpeg|gif|bmp)(?=(\"))/';
	
	$next_pattern = '/(?<=(next\ active\"><a\ href=\"))\S*(?=(\"))/';
	$pre_pattern = '/(?<=(prev\ active\"><a\ href=\"))\S*(?=(\"))/';
	$base = 'http://zhengyuvision.lofter.com';

	try{
		$get_pics = get_file_down::get_instance();
		$get_pics->init($source_url,$root,$pattern,'',$dir,'next',$next_pattern,$base);
		$get_pics->init_depth(true,2);
		$get_pics->down();
		$get_pics->init($source_url,$root,$pattern,'',$dir,'prev',$pre_pattern,$base);
		$get_pics->down();
		
	}catch(Exception $err){
		echo $err->getMessage();
		return;
	}
	
	echo 'done.<br/>';
?>