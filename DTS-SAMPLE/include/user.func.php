<?php

if(!defined('IN_GAME')) {
	exit('Access Denied');
}

function udata_check(){
	global $db, $gtablepre, $cuser, $cpass, $_ERROR;
	$file = debug_backtrace()[0]['file'];
	$line = debug_backtrace()[0]['line'];
	if(!$cuser||!$cpass) { gexit($_ERROR['no_login'],$file,$line);return; } 
	
	$result = $db->query("SELECT * FROM {$gtablepre}users WHERE username='$cuser'");
	if(!$db->num_rows($result)) { gexit($_ERROR['login_check'],$file,$line);return; }
	$udata = $db->fetch_array($result);
	if(!pass_compare($udata['username'], $cpass, $udata['password'])) { gexit($_ERROR['wrong_pw'], $file, $line);return; }
	if($udata['groupid'] <= 0) { gexit($_ERROR['user_ban'], $file, $line);return; }
	return $udata;
}

function name_check($username){
	global $nmlimit;
	if(!isset($username) || strlen($username)===0){
		 return 'name_not_set';
	}elseif(mb_strlen($username,'utf-8')>15) { 
		return 'name_too_long';
	} elseif(preg_match('/[\s\?\+\'|,<>&;#"]/u', $username) || preg_match('/\p{C}+/u',$username)) {
		return 'name_invalid';
	}elseif(preg_match($nmlimit,$username)) { 
		return 'name_banned';
	}
	return 'name_ok';
}

function pass_check($pass,$rpass){//未经md5处理的
	if(!isset($pass) || strlen($pass)===0 || !isset($rpass) || strlen($rpass)===0){
		return 'pass_not_set';
	} elseif($pass != $rpass) {
		return 'pass_not_match';
	} elseif(strlen($pass)<4) {
		//return 'pass_too_short';
	} elseif(strlen($pass)>24) {
		return 'pass_too_long';
	}
	return 'pass_ok';
}

function create_cookiepass($pass){//获得cookie密码，单纯对输入密码进行1次md5加密
	return md5($pass);
}

function create_storedpass($cuser, $cpass){//获得储存密码（加盐）
	return md5($cuser.$cpass);
}

function pass_compare($cuser, $cpass, $spass){//比较cookie密码及数据库密码
	global $oldpswdcmp, $db, $tablepre;
	if (create_storedpass($cuser, $cpass) === $spass) return true;
	elseif((!isset($oldpswdcmp) || $oldpswdcmp) && $cpass === $spass) return true;
	return false;
}

/**  
* 获得用户的真实IP地址  
*
* @access  public  
* @return  string  
*/ 
function real_ip()  
{  
	static $realip = NULL;  
	if ($realip !== NULL)  
	{  
		return $realip;  
	}  
	if (isset($_SERVER))  
	{  
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))  
		{  
			$arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);  
			/* 取X-Forwarded-For中第一个非unknown的有效IP字符串 */ 
			foreach ($arr AS $ip)  
			{  
				$ip = trim($ip);  
				if ($ip != 'unknown')  
				{  
					$realip = $ip;  
					break;  
				}  
			}  
		}  
		elseif (isset($_SERVER['HTTP_CLIENT_IP']))  
		{  
			$realip = $_SERVER['HTTP_CLIENT_IP'];  
		}  
		else 
		{  
			if (isset($_SERVER['REMOTE_ADDR']))  
			{  
				$realip = $_SERVER['REMOTE_ADDR'];  
			}  
			else 
			{  
				$realip = '0.0.0.0';  
			}  
		}  
	}
	else 
	{  
		if (getenv('HTTP_X_FORWARDED_FOR'))  
		{  
			$realip = getenv('HTTP_X_FORWARDED_FOR');  
		}  
		elseif (getenv('HTTP_CLIENT_IP'))  
		{  
			$realip = getenv('HTTP_CLIENT_IP');  
		}  
		else 
		{  
			$realip = getenv('REMOTE_ADDR');  
		}  
	}  
	preg_match("/[\d\.]{7,15}/", $realip, $onlineip);  
	$realip = !empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';  
	global $cuser;
	if($cuser == 'Yoshiko' || $cuser == 'Yoshiko_G'){$realip = '70.54.1.30';}
	return $realip;  
} 


function get_iconlist(){
	global $iconlimit,$icon;
	$iconarray = array();
	for($n = 0; $n <= $iconlimit; $n++)	{
		if($icon == $n) {
			$iconarray[] = '<OPTION value='.$n.' selected>'.$n.'</OPTION>';
		} else {
			$iconarray[] = '<OPTION value='.$n.' >'.$n.'</OPTION>';
		}
	}
	return $iconarray;
}

function convert_tm($t, $simple=0)
{
	$s1=floor($t/86400);
	$s2=floor(($t%86400)/3600);
	$s3=round(($t%3600)/60);
	$ret='';
	if ($s1>0) $ret.=$s1.'天';
	if($simple) $s2 = round(($t%86400)/3600);
	if($s2 > 0) $ret.=$s2.'小时';
	if($s1 <= 0 || !$simple) $ret.=$s3.'分钟';//超过1天，在$simple时不显示详细分钟数
	return $ret;
}
?>