<?php

namespace team
{
	function init() {}
	
//	function check_alive_discover(&$edata)
//	{
//		if (eval(__MAGIC__)) return $___RET_VALUE;
//		//团队模式下非雾天不会在探索中遇到队友
//		eval(import_module('sys','player','metman','logger'));
//		if($teamID && (!$fog) && $teamID == $edata['teamID'] && in_array($gametype,$teamwin_mode))
//		{
//			return 0;
//		}
//		return $chprocess($edata);
//	}
	
	//团队模式下非雾天不会在探索中遇到队友
	function discover_player_filter_alive(&$edata)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		$ret = $chprocess($edata);
		eval(import_module('sys','player'));
		if($teamID && !$fog && $teamID == $edata['teamID'] && in_array($gametype,$teamwin_mode))
			$ret = false;	
		return $ret;
	}
	
	function meetman_alternative($edata)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		
		eval(import_module('sys','player','logger'));
		if($edata['hp']>0 && $teamID && (!$fog) && $gamestate<40 && $teamID == $edata['teamID'] && !in_array($gametype,$teamwin_mode))
		{
			findteam($edata);
			return;
		} 
		$chprocess($edata);
	}

	function findteam(&$edata)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		
		eval(import_module('sys','player','metman','logger'));
		
		extract($edata,EXTR_PREFIX_ALL,'w');
		$action = 'team'.$edata['pid'];
		$sdata['keep_team'] = 1;
		$battle_title = '发现队友';
		\metman\init_battle(1);
		
		$log .= "你发现了队友<span class=\"yellow\">{$tdata['name']}</span>！<br>";
		include template(MOD_TEAM_FINDTEAM);
		$cmd = ob_get_contents();
		ob_clean();
		$main = MOD_METMAN_MEETMAN;
		return;
	}
	
	function senditem_check($edata){
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('sys','map','logger','player'));
		if(!isset($edata)){
			$log .= "对方不存在！<br>";
			return false;
		} elseif($edata['pls'] != $pls) {
			$log .= '<span class="yellow">'.$edata['name'].'</span>已经离开了<span class="yellow">'.$plsinfo[$pls].'</span>。<br>';
			return false;
		} elseif($edata['hp'] <= 0) {
			$log .= '<span class="yellow">'.$edata['name'].'</span>已经死亡，不能接受物品。<br>';
			return false;
		} elseif(!$teamID || $edata['teamID']!=$teamID || $pid==$edata['pid']){
			$log .= '<span class="yellow">'.$edata['name'].'</span>并非你的队友，不能接受物品。<br>';
			return false;
		}
		return true;
	}

	function senditem(){
		if (eval(__MAGIC__)) return $___RET_VALUE;
		
		eval(import_module('sys','map','logger','player','metman','input'));
	
		$mateid = str_replace('team','',$action);
		if(!$mateid || strpos($action,'team')===false){
			$log .= '<span class="yellow">你没有遇到队友，或已经离开现场！</span><br>';
			
			$mode = 'command';
			return;
		}
		
		$edata=\player\fetch_playerdata_by_pid($mateid);
		$check = senditem_check($edata);
		if(!senditem_check($edata)){
			
			$mode = 'command';
			return;
		}
//		if(!isset($edata)){
//			$log .= "对方不存在！<br>";
//			
//			$mode = 'command';
//			return;
//		}
//
//		if($edata['pls'] != $pls) {
//			$log .= '<span class="yellow">'.$edata['name'].'</span>已经离开了<span class="yellow">'.$plsinfo[$pls].'</span>。<br>';
//			$mode = 'command';
//			
//			return;
//		} elseif($edata['hp'] <= 0) {
//			$log .= '<span class="yellow">'.$edata['name'].'</span>已经死亡，不能接受物品。<br>';
//			$mode = 'command';
//			
//			return;
//		} elseif(!$teamID || $edata['teamID']!=$teamID || $pid==$edata['pid']){
//			$log .= '<span class="yellow">'.$edata['name'].'</span>并非你的队友，不能接受物品。<br>';
//			$mode = 'command';
//			
//			return;
//		}

		if($message){
			$log .= "<span class=\"lime\">你对{$edata['name']}说：“{$message}”</span><br>";
			$x = "<span class=\"lime\">{$name}对你说：“{$message}”</span>";
			if(!$edata['type']) \logger\logsave($edata['pid'],$now,$x,'c');
		}
		
		if($command != 'back'){
			$itmn = substr($command, 4);
			if (!${'itms'.$itmn}) {
				$log .= '此道具不存在！';
				
				$mode = 'command';
				return;
			}
			$itm = & ${'itm'.$itmn};
			$itmk = & ${'itmk'.$itmn};
			$itme = & ${'itme'.$itmn};
			$itms = & ${'itms'.$itmn};
			$itmsk = & ${'itmsk'.$itmn};

			for($i = 1;$i <= 6; $i++){
				if(!$edata['itms'.$i]) {
					$edata['itm'.$i] = $itm; $edata['itmk'.$i] = $itmk; 
					$edata['itme'.$i] = $itme; $edata['itms'.$i] = $itms; $edata['itmsk'.$i] = $itmsk;
					$log .= "你将<span class=\"yellow\">".$edata['itm'.$i]."</span>送给了<span class=\"yellow\">{$edata['name']}</span>。<br>";
					$x = "<span class=\"yellow\">$name</span>将<span class=\"yellow\">".$edata['itm'.$i]."</span>送给了你。";
					if(!$edata['type']) \logger\logsave($edata['pid'],$now,$x,'t');
					addnews($now,'senditem',$name,$edata['name'],$itm);
					\player\player_save($edata);
					$itm = $itmk = $itmsk = '';
					$itme = $itms = 0;
					
					return;
				}
			}
			$log .= "<span class=\"yellow\">{$edata['name']}</span> 的包裹已经满了，不能赠送物品。<br>";
		}
		
		$mode = 'command';
		return;
	}
	
	function post_act()
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		$chprocess();
		eval(import_module('player'));
		if(empty($sdata['keep_team']) && strpos($action, 'team')===0){
			$action = '';
			unset($sdata['keep_team']);
		}
	}
	
	function act()
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		
		eval(import_module('sys','player','input'));
		if($mode == 'senditem') 
		{
			senditem();
			return;
		}
		
		if($mode == 'command' && $command == 'team') {
			if($teamcmd == 'teamquit') {				
				teamquit();
			} else{
				teamcheck();
			}
			return;
		}
		
		if($mode == 'team') {
			if ($command=='teammake') 
			{
				teammake($nteamID,$nteamPass);
				return; 
			}
			if ($command=='teamjoin')
			{
				teamjoin($nteamID,$nteamPass);
				return;
			}
		}
				
		$chprocess();
	}
	
	function check_team_button_exist()
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		return 1;
	}
	
	function parse_news($nid, $news, $hour, $min, $sec, $a, $b, $c, $d, $e, $exarr = array())	
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('sys','player'));
		
		if($news == 'teammake') 
			return "<li id=\"nid$nid\">{$hour}时{$min}分{$sec}秒，<span class=\"yellow\">{$b}创建了队伍{$a}</span></li>";
		if($news == 'teamjoin') 
			return "<li id=\"nid$nid\">{$hour}时{$min}分{$sec}秒，<span class=\"yellow\">{$b}加入了队伍{$a}</span></li>";
		if($news == 'teamquit') 
			return "<li id=\"nid$nid\">{$hour}时{$min}分{$sec}秒，<span class=\"red\">{$b}退出了队伍{$a}</span></li>";
		if($news == 'senditem') 
			return "<li id=\"nid$nid\">{$hour}时{$min}分{$sec}秒，<span class=\"lime\">{$a}将<span class=\"yellow\">$c</span>赠送给了{$b}</span></li>";
			
		return $chprocess($nid, $news, $hour, $min, $sec, $a, $b, $c, $d, $e, $exarr);
	}
	
}

?>
