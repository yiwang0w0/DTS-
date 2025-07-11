<?php

namespace enemy
{
	function init() {}
	
	function findenemy(&$edata)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		
		eval(import_module('sys','logger','player','metman'));
		
		\player\update_sdata();
		
		$battle_title = '发现敌人';
		\metman\init_battle();
		$log .= "你发现了敌人<span class=\"red\">{$tdata['name']}</span>！<br>对方好像完全没有注意到你！<br>";
		
		include template(get_battlecmd_filename());
		$cmd = ob_get_contents();
		ob_clean();

		$main = MOD_METMAN_MEETMAN;
		
		return;
	}
	
	function get_battlecmd_filename(){
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('sys','logger','player','metman'));
		return MOD_ENEMY_BATTLECMD;
	}
	
	function calculate_active_obbs(&$ldata,&$edata)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('enemy'));
		//echo "面对NPC的先制率基础值：".$active_obbs_npc.'% <br>';;
		if($edata['type']) return $active_obbs_npc;
		else return $active_obbs_pc;
	}
	
	function calculate_active_obbs_multiplier(&$ldata,&$edata)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		return 1.0;
	}
	
	function calculate_active_obbs_change(&$ldata,&$edata,$active_r)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		return $active_r;
	}
	
	function get_final_active_obbs(&$ldata,&$edata)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('enemy'));
		//calculate_active_obbs()是加算，返回1-150的数值
		$active_r = min(max(calculate_active_obbs($ldata,$edata),1), 150);
		//echo "先攻率基础：$active_r <br>";
		//calculate_active_obbs_multiplier()是乘算，返回0-1的小数
		
		$active_r *= calculate_active_obbs_multiplier($ldata,$edata);
		
		//calculate_active_obbs_change()是最后改变，返回0-100的数值，这里只放特判，一般增减请用前两个函数
		$active_r = calculate_active_obbs_change($ldata,$edata,$active_r);
		//先攻率最大最小值判定
		$active_r = max($active_obbs_range[0], min($active_obbs_range[1], $active_r));
		//echo $active_r;
		return $active_r;
	}
	
	//判定主动，判定成功代表可以主动选择是否战斗，失败则被动强制进入战斗
	function check_enemy_meet_active(&$ldata,&$edata)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		$active_r = get_final_active_obbs($ldata,$edata);
		//echo "最终先攻率：$active_r <br>";
		$active_dice = rand(0,99);
		return ($active_dice < $active_r);
	}
	
	function meetman_alternative($edata)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('sys','player','metman','logger'));
		if ($edata['hp']>0)
		{
			extract($edata,EXTR_PREFIX_ALL,'w');
			if (check_enemy_meet_active($sdata,$edata)) {
				$action = 'enemy'.$edata['pid'];
				$sdata['keep_enemy'] = 1;
				findenemy($edata);
				return;
			} else {
				battle_wrapper($edata,$sdata,0);
				return;
			}
		}
		else $chprocess($edata);
	}
	
	function battle_wrapper(&$pa, &$pd, $active)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
	}
	
	function post_act()
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		$chprocess();
		eval(import_module('player'));
		if(empty($sdata['keep_enemy']) && strpos($action, 'enemy')===0){
			$action = '';
			unset($sdata['keep_enemy']);
		}
	}
	
	function act()
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		
		eval(import_module('sys','map','player','logger','metman','input'));
		if($command == 'enter')
			$sdata['keep_enemy'] = 1;
		if($mode == 'combat') 
		{
			if ($command == 'back') 
			{
				$log .= "你逃跑了。";
				$mode = 'command';
				return;
			}
			
			$enemyid = str_replace('enemy','',$action);
			
			if(!$enemyid || strpos($action,'enemy')===false){
				$log .= "<span class=\"yellow\">你没有遇到敌人，或已经离开战场！</span><br>";
				$mode = 'command';
				return;
			}
		
			$result = $db->query ( "SELECT * FROM {$tablepre}players WHERE pid='$enemyid'" );
			if (! $db->num_rows ( $result )) {
				$log .= "对方不存在！<br>";
				
				$mode = 'command';
				return;
			}
		
			$edata=\player\fetch_playerdata_by_pid($enemyid);
			extract($edata,EXTR_PREFIX_ALL,'w');
			
			if ($edata ['pls'] != $pls) {
				$log .= "<span class=\"yellow\">" . $edata ['name'] . "</span>已经离开了<span class=\"yellow\">$plsinfo[$pls]</span>。<br>";
				
				$mode = 'command';
				return;
			} elseif ($edata ['hp'] <= 0) {
				$log .= "<span class=\"red\">" . $edata ['name'] . "</span>已经死亡，不能被攻击。<br>";
				if(\corpse\check_corpse_discover($edata))
				{
					$action = 'corpse'.$edata['pid'];
					$sdata['keep_enemy'] = 1;
					\corpse\findcorpse ( $edata );
				}
				return;
			}
			
			\player\update_sdata();
			$ldata=$sdata;
			battle_wrapper($ldata,$edata,1);
			return;
		}
		$chprocess();
	}
}

?>