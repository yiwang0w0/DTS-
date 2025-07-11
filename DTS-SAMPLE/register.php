<?php

define('CURSCRIPT', 'register');
define('LOAD_CORE_ONLY', TRUE);
require './include/common.inc.php';

include './include/user.func.php';
include './gamedata/banlist.list';

if(isset($cuser) && isset($cpass)){
	gexit($_ERROR['logged_in'],__file__,__line__);
}
if(!isset($cmd)){
	//$ustate = 'register';
	$icon = 0;
	$gender = 'm';
	$iconarray = get_iconlist();
	$select_icon = 0;
	$motto = $criticalmsg = $killmsg = $lastword = '';
	include template('register');
}elseif($cmd = 'post_register'){
	//$ustate = 'register';
	$gamedata = Array();
	$name_check = name_check($username);
	$pass_check = pass_check($npass,$rnpass);
	$onlineip = real_ip();
	
	if($name_check!='name_ok'){
		$gamedata['innerHTML']['info'] = $_ERROR[$name_check];
	}elseif($pass_check!='pass_ok'){
		$gamedata['innerHTML']['info'] = $_ERROR[$pass_check];
	}elseif(preg_match($iplimit,$onlineip)){
		$gamedata['innerHTML']['info'] = $_ERROR['ip_banned'];
	}else{
		$result = $db->query("SELECT * FROM {$gtablepre}users WHERE username = '$username'");
		if($db->num_rows($result) > 0) {
			$gamedata['innerHTML']['info'] = $_ERROR['name_exists'];
		}else{//现在开始注册
			$groupid = 1;
			$credits = 0;
			$password = create_cookiepass($npass);
			$stored_password = create_storedpass($username, $password);
			$result = $db->query("INSERT INTO {$gtablepre}users (username,password,alt_pswd,groupid,ip,credits,gender,icon,motto,killmsg,lastword,gold,cardlist) VALUES ('$username', '$stored_password', 1, '$groupid', '$onlineip', '$credits', '$gender', '$icon', '$motto', '$killmsg', '$lastword','80','0')");
			if($result){
				$gamedata['innerHTML']['info'] = $_INFO['reg_success'];
				$ustate = 'check';
				gsetcookie('user',$username);
				gsetcookie('pass',$password);
			}else{
				$gamedata['innerHTML']['info'] = $_ERROR['db_failure'];
				$gamedata['innerHTML']['info'] .= ob_get_contents();
			}
		}
	}
	if($ustate == 'check'){
		$gamedata['innerHTML']['postreg'] = '<input type="button" value="返回游戏首页" onclick="window.location.href=\'index.php\'">';
		if(isset($error)){$gamedata['innerHTML']['error'] = $error;}
		ob_clean();
		$jgamedata = gencode($gamedata);
		echo $jgamedata;
		ob_end_flush();
	}else{
		ob_clean();
		if(isset($error)){$gamedata['innerHTML']['error'] = $error;}
		$jgamedata = gencode($gamedata);
		echo $jgamedata;
		ob_end_flush();
	}
}

?>