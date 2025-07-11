<?php

namespace skill73
{

	function init() 
	{
		define('MOD_SKILL73_INFO','club;battle;');
		eval(import_module('clubbase'));
		$clubskillname[73] = '必杀';
	}
	
	function acquire73(&$pa)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
	}
	
	function lost73(&$pa)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
	}
	
	function check_unlocked73(&$pa)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		return $pa['lvl']>=3;
	}
	
	function get_rage_cost73(&$pa = NULL)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('skill73'));
		if ($pa===NULL)
		{
			eval(import_module('player'));
			$clb=$club;
		}
		else  $club=$pa['club'];
		return ($club==9?40:85);
	}
	
	function strike_prepare(&$pa, &$pd, $active)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		if ($pa['bskill']!=73) return $chprocess($pa, $pd, $active);
		if (!\skillbase\skill_query(73,$pa) || !check_unlocked73($pa))
		{
			eval(import_module('logger'));
			$log .= '你尚未解锁这个技能！';
			$pa['bskill']=0;
		}
		else
		{
			$rcost = get_rage_cost73($pa);
			if ($pa['rage']>=$rcost)
			{
				eval(import_module('logger'));
				if ($active)
					$log.="<span class=\"lime\">你对{$pd['name']}发动了技能「必杀」！</span><br>";
				else  $log.="<span class=\"lime\">{$pa['name']}对你发动了技能「必杀」！</span><br>";
				$pa['rage']-=$rcost;
				addnews ( 0, 'bskill73', $pa['name'], $pd['name'] );
			}
			else
			{
				if ($active)
				{
					eval(import_module('logger'));
					$log.='怒气不足或其他原因不能发动。<br>';
				}
				$pa['bskill']=0;
			}
		}
		$chprocess($pa, $pd, $active);
	}	
	
	function get_physical_dmg_multiplier(&$pa, &$pd, $active)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		$r=Array();
		if ($pa['bskill']==73) 
		{
			eval(import_module('logger'));
			if ($active)
				$log.='<span class="lime">你对敌人释放出必杀技！</span><br>';
			else  $log.='<span class="lime">敌人对你释放出必杀技！</span><br>';
			$r=Array(2);
		}
		return array_merge($r,$chprocess($pa,$pd,$active));
	}

	function attack_finish(&$pa,&$pd,$active)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		if (in_array('c', \attrbase\get_ex_attack_array($pa, $pd, $active)) && $pa['bskill']==73 && $pa['club']==9)
		{
			//灵系称号且有重辅额外返还15点怒气
			$pa['rage']+=15;
		}
		$chprocess($pa,$pd,$active);
	}
	
	function parse_news($nid, $news, $hour, $min, $sec, $a, $b, $c, $d, $e, $exarr = array())
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		
		eval(import_module('sys','player'));
		
		if($news == 'bskill73') 
			return "<li id=\"nid$nid\">{$hour}时{$min}分{$sec}秒，<span class=\"clan\">{$a}对{$b}发动了技能<span class=\"yellow\">「必杀」</span></span></li>";
		
		return $chprocess($nid, $news, $hour, $min, $sec, $a, $b, $c, $d, $e, $exarr);
	}
}

?>
