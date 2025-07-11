<?php

namespace poison
{
	function init() {
		eval(import_module('itemmain'));
		$iteminfo['PH'] = '生命恢复（有毒）';
		$iteminfo['PS'] = '体力恢复（有毒）';
		$iteminfo['PB'] = '命体恢复（有毒）';
		$iteminfo['PM'] = '歌魂增加（有毒）';
		$iteminfo['PT'] = '歌魂增加（有毒）';
		$iteminfo['PR'] = '怒气增加（有毒）';
	}
	
	function parse_itmk_words($k_value, $reveal=0)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		if (isset($k_value[0]) && $k_value[0]=='P' && !$reveal) $k_value[0]='H';
		return $chprocess($k_value);
	}
	
	function send_poison_enemylog($itm,$itmsk)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('sys','player','logger'));
		if($hp<=0){
			$w_log = "<span class=\"red\">{$name}食用了你下毒的补给{$itm}并被毒死了！</span><br>";
			\logger\logsave ( $itmsk, $now, $w_log ,'b');
		}
		else
		{
			$w_log = "<span class=\"yellow\">{$name}食用了你下毒的补给{$itm}！</span><br>";
			\logger\logsave ( $itmsk, $now, $w_log ,'b');
		}
	}
	
	function itemuse(&$theitem) 
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		
		eval(import_module('sys','player','itemmain','logger'));
		
		$itm=&$theitem['itm']; $itmk=&$theitem['itmk'];
		$itme=&$theitem['itme']; $itms=&$theitem['itms']; $itmsk=&$theitem['itmsk'];
		
		if (strpos ( $itmk, 'P' ) === 0) {
			$m=(int)substr($itmk, 2,1);//支持1.5-9倍毒
			if($m < 1) $m = 1;
			elseif($m == 1) $m = 1.5;
			$damage = round($itme * $m);
			
			if (defined('MOD_WOUND')) \wound\get_inf('p');
			
			$hp -= $damage;
			
			$playerflag = 0;
			if ($itmsk && is_numeric($itmsk)) $playerflag = 1;
			$selflag = 0;
			if ($playerflag && $itmsk == $pid) $selflag = 1;
			if  ($playerflag)
			{
				$wdata = \player\fetch_playerdata_by_pid($itmsk);
				$wprefix = '<span class="yellow">'.$wdata['name'].'</span>';
				if ($selflag) $wprefix = '你自己';
				$log .= "糟糕，<span class=\"yellow\">$itm</span>中被{$wprefix}掺入了毒药！你受到了<span class=\"dmg\">$damage</span>点伤害！<br>";
				addnews ( $now, 'poison', $name, $wdata ['name'], $itm );
				if (!$selflag) send_poison_enemylog($itm,$itmsk);
			} else {
				$log .= "糟糕，<span class=\"yellow\">$itm</span>有毒！你受到了<span class=\"dmg\">$damage</span>点伤害！<br>";
			}
			if ($hp <= 0) {
			
				$state = 26;
				\player\update_sdata();
				
				if ($playerflag && !$selflag) 	//有来源且不是自己
				{	
					$sdata['bid'] = $itmsk;
					$log .= "你被<span class=\"red\">" . $wdata ['name'] . "</span>毒死了！";
				}
				else  if ($playerflag)			//有来源，来源是自己（自己下的毒）
				{
					$sdata['bid'] = $itmsk;
					$wdata = &$sdata;
					$log .= "你被毒死了！";
				}
				else						//无来源
				{
					$wdata = &$sdata;
					$sdata['sourceless']=1;
					$log .= "你被毒死了！";
				}
				$wdata['attackwith']=$itm;
				$killmsg = \player\kill($wdata,$sdata);
				if (isset($sdata['sourceless'])) unset($sdata['sourceless']);
				
				if($killmsg){$log .= "<span class=\"yellow\">{$wdata['name']}对你说：“{$killmsg}”</span><br>";}
				
				if ($playerflag && !$selflag) \player\player_save($wdata);
				\player\player_save($sdata);
				\player\load_playerdata($sdata);
			} 
			\itemmain\itms_reduce($theitem);
			return;
		}
		
		if ((strpos ( $itmk, 'Y' ) === 0 || strpos ( $itmk, 'Z' ) === 0) && ($itm == '毒药')) 
		{
			include template(MOD_POISON_POISON);
			$cmd = ob_get_contents();
			ob_clean();
			return;
		}
		
		$chprocess($theitem);
	}
	
		
	function poison($itmn = 0) 
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('sys','player','logger','input'));
		if ( $itmp < 1 || $itmp > 6 ) {
			$log .= '此道具不存在，请重新选择。';
			$mode = 'command';
			return;
		}
		$poison = & ${'itm'.$itmp};
		$poisonk = & ${'itmk'.$itmp};
		$poisone = & ${'itme'.$itmp};
		$poisons = & ${'itms'.$itmp};
		$poisonsk = & ${'itmsk'.$itmp};
		if ( $itmn < 1 || $itmn > 6 ) {
			$log .= '此道具不存在，请重新选择。';
			$mode = 'command';
			return;
		}
		$itm = & ${'itm'.$itmn};
		$itmk = & ${'itmk'.$itmn};
		$itmsk = & ${'itmsk'.$itmn};
		//$log.=$itmk.' '.$poison;
		if(($poison != '毒药') || (strpos($itmk, 'H') !==0 && strpos($itmk, 'P') !== 0)) {
			$log .= '道具选择错误，请重新选择。<br>';
			$mode = 'command';
			return;
		}
		$itmk = substr_replace($itmk,'P',0,1);
		//if($club == 8){ $itmk = substr_replace($itmk,'2',2,1); }
		$p_factor = check_poison_factor();
		if(!empty($p_factor) && (int)substr($itmk,2,1) < (int)$p_factor) $itmk = substr_replace($itmk,$p_factor,2,1);
		if($art == '妖精的羽翼') {
			$itmk = substr_replace($itmk,'H',0,1);
			if((int)substr($itmk,2,1) > 0) $itmk = substr_replace($itmk,'0',2,1);
			$log .= "一种神秘的力量净化了毒药，你的毒药变成了解毒剂！";
			if(!$itmsk || is_numeric($itmsk)) $itmsk = 'z';
		}else{
			$itmsk = $pid;
		}
		if($art == '妖精的羽翼') {
			$log .= "使用了 <span class=\"red\">$poison</span> ，<span class=\"yellow\">${'itm'.$itmn}</span> 被净化了！<br>";
		}
		else {
			$log .= "使用了 <span class=\"red\">$poison</span> ，<span class=\"yellow\">${'itm'.$itmn}</span> 被下毒了！<br>";
		}
		$poisons--;
		if($poisons <= 0){
			$log .= "<span class=\"red\">$poison</span> 用光了。<br>";
			$poison = $poisonk = '';$poisone = $poisons = 0;
		}

		$mode = 'command';
		return;
	}
	
	function check_poison_factor(){
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('player','logger'));
		$ret = '';
		if($art == '毒物说明书') {
			$log .= '毒物说明书让你调制的毒物更加危险。';
			$ret = '1';
		}
		return $ret;
	}

	function act()
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		
		eval(import_module('sys','player','input'));
		if($mode == 'item' && $usemode == 'poison') 
		{
			if ($command=='menu'){
				$mode = 'command';
				return;
			}
			$item = substr($command,3);
			poison($item);
			return;
		}
		
		$chprocess();
	}
	
	function parse_news($nid, $news, $hour, $min, $sec, $a, $b, $c, $d, $e, $exarr = array())
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('sys','player'));
		
		if($news == 'poison') 
			return "<li id=\"nid$nid\">{$hour}时{$min}分{$sec}秒，<span class=\"purple\">{$a}食用了{$b}下毒的{$c}</span></li>";
		
		if($news == 'death26') {
			$dname = $typeinfo[$b].' '.$a;
			if(!$e){
				$e0="<span class=\"yellow\">【{$dname} 什么都没说就死去了】</span><br>\n";
			}else{
				$e0="<span class=\"yellow\">【{$dname}：“{$e}”】</span><br>\n";
			}
			if($c) {
				return "<li id=\"nid$nid\">{$hour}时{$min}分{$sec}秒，<span class=\"yellow\">$a</span>因食用了<span class=\"yellow\">$c</span>下毒的<span class=\"red\">$d</span>被毒死{$e0}</li>";
			} else {
				return "<li id=\"nid$nid\">{$hour}时{$min}分{$sec}秒，<span class=\"yellow\">$a</span>因食用了有毒的<span class=\"red\">$d</span>被毒死{$e0}</li>";
			}
		}
		return $chprocess($nid, $news, $hour, $min, $sec, $a, $b, $c, $d, $e, $exarr);
	}
}

?>
