<?php

namespace ammunition
{
	$ammukind = array(
		'WJ' => array('GBh', 4),
		'e' => array('GBe', 10),
		'w' => array('GBe', 10),
		'i' => array('GBi', 10),
		'u' => array('GBi', 10),
		'r' => array('GBr', 20),
		'WG' => array('GB', 6),
	);
	//注意目前连击的气体和能源弹药的弹夹数会额外+2
	
	function init()
	{
		eval(import_module('itemmain'));
		$iteminfo['GB'] = '手枪弹药';	
		$iteminfo['GBr'] = '机枪弹药';
		$iteminfo['GBi'] = '气体弹药';
		$iteminfo['GBh'] = '重型弹药';
		$iteminfo['GBe'] = '能源弹药';
		
		$itemspkinfo['o'] = '一发';
		$itemspkdesc['o']='本枪械不能装填弹药';
		$itemspkremark['o']='……';
		
	}
	
	function parse_itmk_desc($k_value, $sk_value) {
		if (eval(__MAGIC__)) return $___RET_VALUE;
		$ret = $chprocess($k_value, $sk_value);
		if(strpos($k_value,'WG')===0 || strpos($k_value,'WJ')===0) {
			list($bulletkind, $bulletnum) = check_ammukind($k_value, $sk_value);
			eval(import_module('itemmain'));
			$ret .= '<br>弹药类型为'.$iteminfo[$bulletkind].'，弹夹'. $bulletnum .'发';
		}
		return $ret;
	}
	
	function check_ammukind($cwepk, $cwepsk){
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('ammunition'));
		$retk = 'GB'; $retn = 6;
		foreach($ammukind as $ak => $av){
			if((strpos($ak, 'W')===0 && strpos($cwepk, $ak) === 0) || (strpos($ak, 'W')!==0 && strpos($cwepsk, $ak) !== false)){
				$retk = $av[0]; $retn = $av[1];
				if($retn <= 10 && strpos($cwepsk, 'r') !== false) {
					if('GBh'==$retk) $retn = 6;//连击重枪弹夹数为6
					else $retn = 12;//带连击的气体和能源枪的弹夹数变成12
				}
				break;
			}
		}
		return array($retk, $retn);
	}
	
	function itemuse_ugb(&$theitem)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		
		eval(import_module('sys','player','itemmain','logger'));
		
		$itm=&$theitem['itm']; $itmk=&$theitem['itmk'];
		$itme=&$theitem['itme']; $itms=&$theitem['itms']; $itmsk=&$theitem['itmsk'];
		
		if ((strpos ( $wepk, 'WG' ) !== 0)&&(strpos ( $wepk, 'WJ' ) !== 0)) {
			$log .= "<span class=\"red\">你没有装备枪械，不能使用子弹。</span><br>";
			$mode = 'command';
			return;
		}
		
		if (strpos ( $wepsk, 'o' ) !== false) {
			$log .= "<span class=\"red\">{$wep}不能装填弹药。</span><br>";
			$mode = 'command';
			return;
		}
		
		list($bulletkind, $bulletnum) = check_ammukind($wepk, $wepsk);
		if($itmk != $bulletkind){
			$log .= "<span class='red'>弹药类型不匹配，需要</span><span class='yellow'>$iteminfo[$bulletkind]</span>。<br>";
			$mode = 'command';
			return;
		}

		if ($weps == $nosta) {
			$weps = 0;
		}
		$bullet = $bulletnum - $weps;
		if ($bullet <= 0) {
				$log .= "<span class=\"red\">{$wep}的弹匣是满的，不能装弹。</span>";
			return;
		} elseif ($bullet >= $itms) {
			$bullet = $itms;
		}
		$itms -= $bullet;
		$weps += $bullet;
		$log .= "为<span class=\"red\">$wep</span>装填了<span class=\"red\">$itm</span>，<span class=\"red\">$wep</span>残弹数增加<span class=\"yellow\">$bullet</span>。<br>";
		if ($itms <= 0) {
			$log .= "<span class=\"red\">$itm</span>用光了。<br>";
			$itm = $itmk = $itmsk = '';
			$itme = $itms = 0;
		}
	}
	
	function itemuse(&$theitem) 
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		
		eval(import_module('sys','player','itemmain','logger'));
		
		$itm=&$theitem['itm']; $itmk=&$theitem['itmk'];
		$itme=&$theitem['itme']; $itms=&$theitem['itms']; $itmsk=&$theitem['itmsk'];
		
		if (strpos ( $itmk, 'GB' ) === 0) 
		{
			itemuse_ugb($theitem);
			return;
		}
		$chprocess($theitem);
	}
	
}

?>