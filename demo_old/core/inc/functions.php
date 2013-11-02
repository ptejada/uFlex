<?php
	function aPrint($a){	
		echo "<pre>";
		print_r($a);
		echo "</pre>";
	}
	
	function p($txt,$s=3){
		if(is_array($txt)){
			aPrint($txt);
			return;
		}
		echo "<h{$s}>{$txt}</h{$s}>";
	}
	
	function redirect($url=false, $time = 0){
		$url = $url ? $url : $_SERVER['HTTP_REFERER'];
		
		if(!headers_sent()){
			if(!$time){
				header("Location: {$url}"); 
			}else{
				header("refresh: $time; {$url}");
			}
		}else{
			echo "<script> setTimeout(function(){ window.location = '{$url}' },". ($time*1000) . ")</script>";	
		}
	}
	
	function getVar($index){
		$tree = explode("/",@$_GET['path']);
		$tree = array_filter($tree);
		
		if(is_int($index)){
			$res = @$tree[$index-1];
		}else{
			$res = @$_GET[$index];
		}
		return $res;
	}
	
	function showMsg($index="NoteMsgs"){
		if(isset($_SESSION[$index])){
			if(!is_array($_SESSION[$index])) return;
			
			$res = "<ul>";
			foreach($_SESSION[$index] as $i=>$x){
				$res .= "<li>$x</li>";
			}
			$res .= "</ul>";
			
			unset($_SESSION[$index]);
			
			return $res;
		}
	}
	
	function maxArg($num){
		$tree = explode("/",@$_GET['path']);
		$tree = array_filter($tree);
		
		if(count($tree) > $num){
			send404();
		}
	}
	
	function send404(){
		if(!headers_sent()){			
			header("HTTP/1.0 404 Not Found");
			include("404.html");
			die();
		}else{
			redirect("404.html");
		}
	}
?>