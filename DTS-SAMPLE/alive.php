<?php

define('CURSCRIPT', 'alive');
define('IN_GAME', true);
defined('GAME_ROOT') || define('GAME_ROOT', dirname(__FILE__).'/');
require GAME_ROOT.'./include/global.func.php';
$url = url_dir().'command.php';
$context = array('page'=>'command_alive');
foreach($_POST as $pkey => $pval){
	$context[$pkey] = $pval;
}
$cookies = array();
foreach($_COOKIE as $ckey => $cval){
	if(strpos($ckey,'user')!==false || strpos($ckey,'pass')!==false) $cookies[$ckey] = $cval;
}
$aliveinfo = curl_post($url, $context, $cookies);
echo $aliveinfo;

/* End of file alive.php */
/* Location: /alive.php */