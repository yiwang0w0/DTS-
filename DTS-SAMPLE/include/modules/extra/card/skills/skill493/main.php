<?php

namespace skill493
{
	$skill493stateinfo = array(
		1 => '熟练度',
		2 => '经验值',
		3 => '怒气'
	);
	
	function init() 
	{
		define('MOD_SKILL493_INFO','card;unique;upgrade;locked;');
		eval(import_module('clubbase'));
		$clubskillname[493] = '通感';
	}
	
	function acquire493(&$pa)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		\skillbase\skill_setvalue(493,'choice','1',$pa);
	}
	
	function lost493(&$pa)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		\skillbase\skill_delvalue(493,'choice',$pa);
	}
	
	function check_unlocked493(&$pa=NULL)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		return 1;
	}
	
	function upgrade493()
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('player','logger','skill493','clubbase'));
		if (!\skillbase\skill_query(493) || !check_unlocked493($sdata))
		{
			$log .= '你没有这个技能。<br>';
			return;
		}
		eval(import_module('input'));
		$val = (int)$skillpara1;
		if ($val<1 || $val>3)
		{
			$log .= '参数不合法。<br>';
			return;
		}
		\skillbase\skill_setvalue(493,'choice',$val);
		$log.='已把「'.$clubskillname[493].'」的状态改变为「'.$skill493stateinfo[$val].'」。<br>';
	}
	
	//记录熟练度增加值并变为0
	function calculate_attack_weapon_skill_gain_change(&$pa, &$pd, $active, $skillup)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		$ret = $chprocess($pa, $pd, $active, $skillup);
		if (\skillbase\skill_query(493,$pa) && check_unlocked493($pa)) {
			$pa['skill493_o_skillup'] = $ret;
			$ret = 0;
		}
		return $ret;
	}
	
	//记录经验值增加值并变为0
	function calculate_attack_exp_gain_change(&$pa, &$pd, $active, $expup)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		$ret = $chprocess($pa, $pd, $active, $expup);
		if (\skillbase\skill_query(493,$pa) && check_unlocked493($pa)) {
			$pa['skill493_o_expup'] = $ret;
			$ret = 0;
		}
		return $ret;
	}
	
	//记录怒气增加值并变为0
	function calculate_attack_rage_gain_change(&$pa, &$pd, $active, $rageup)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		$ret = $chprocess($pa, $pd, $active, $rageup);
		if (!empty($pa['receive_rage']) && \skillbase\skill_query(493,$pa) && check_unlocked493($pa)) {
			//echo '攻击者'.$pa['name'].'本来是'.$pa['name'].'获得怒气<br>';
			//if(!isset($pa['skill493_o_rageup'])) $pa['skill493_o_rageup'] = 0;
			$pa['skill493_o_rageup'] += $ret;
			$ret = 0;
		}
		if (!empty($pd['receive_rage']) && \skillbase\skill_query(493,$pd) && check_unlocked493($pd)) {
			//echo '攻击者'.$pa['name'].'本来是'.$pd['name'].'获得怒气<br>';
			//if(!isset($pd['skill493_o_rageup'])) $pd['skill493_o_rageup'] = 0;
			$pd['skill493_o_rageup'] += $ret;
			$ret = 0;
		}
		
		return $ret;
	}
	
	//平衡性措施，等效经验
	function calculate_attack_lvl(&$pdata)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		$ret = $chprocess($pdata);
		if(\skillbase\skill_query(493,$pdata) && check_unlocked493($pdata)){
			$null=\player\create_dummy_playerdata();
			$wep_skill = \weapon\get_skill($pdata, $null, 1);
			//echo '武器熟练'.$wep_skill;
			$e_exp = max($pdata['exp'], max($pdata['rage'], $wep_skill));
			//echo '等效经验'.$e_exp;
			$e_lvl = \lvlctl\calc_uplv($e_exp, 0);
			//echo ' 等效等级'.$e_lvl;
			if($e_lvl > $ret) $ret = $e_lvl;
		}
		return $ret;
	}
	
	function attack_prepare(&$pa, &$pd, $active)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		foreach(array(&$pa, &$pd) as &$pl){
			if($pl['hp'] > 0 && \skillbase\skill_query(493,$pl) && check_unlocked493($pl)){
				$pl['skill493_o_skillup'] = $pl['skill493_o_expup'] = $pl['skill493_o_rageup'] = 0;
			}
		}
		return $chprocess($pa, $pd, $active);
	}
	
	//攻击结束时根据所选项来转化
	function attack_finish(&$pa,&$pd,$active)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('rage'));
		$chprocess($pa,$pd, $active);
		foreach(array(&$pa, &$pd) as &$pl){
			if($pl['hp'] > 0 && \skillbase\skill_query(493,$pl) && check_unlocked493($pl)){
				$skill493var = \skillbase\skill_getvalue(493,'choice');
				$allup = 0;
				foreach(array('skill493_o_skillup','skill493_o_expup','skill493_o_rageup') as $nval){
					if(!empty($pl[$nval])) $allup += $pl[$nval];
					//echo $pl['name'].$nval.' '.$pl[$nval].' ';
				}
				$null=\player\create_dummy_playerdata();
				
				if(1==$skill493var) \weapon\apply_weapon_skill_gain($pl, $null, $active, $allup);
				elseif(2==$skill493var) \lvlctl\getexp($allup, $pl);
				else \rage\get_rage($allup, $pl);
			}
		}
	}
}

?>