<?php

namespace skill42
{
	function init() 
	{
		define('MOD_SKILL42_INFO','club;locked;');
	}
	
	function acquire42(&$pa)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		\skillbase\skill_setvalue(42,'u','0',$pa);	//是否已经被解锁
	}
	
	function lost42(&$pa)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
	}
	
	function unlock42(&$pa)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		\skillbase\skill_setvalue(42,'u','1',$pa);
	}
	
	function check_unlocked42(&$pa)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		if (\skillbase\skill_getvalue(42,'u',$pa)=='1') return 1; else return 0;
	}
	
	//战斗中基础防御力增加
	function get_internal_def(&$pa,&$pd,$active)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		if (!\skillbase\skill_query(42,$pd) || !check_unlocked42($pd)) return $chprocess($pa,$pd,$active);
		return $chprocess($pa,$pd,$active)*1.35;
	}
	
	//每次被攻击增加2点基础防御
	function attack_finish(&$pa, &$pd, $active)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		if (\skillbase\skill_query(42,$pd) && check_unlocked42($pd))
		{
			$pd['def']+=2;
		}
		$chprocess($pa, $pd, $active);
	}
	
	//击杀敌人时攻击+2防御+4
	function player_kill_enemy(&$pa,&$pd,$active)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		if (\skillbase\skill_query(42,$pa) && check_unlocked42($pa))
		{
			$pa['att']+=2; $pa['def']+=4;
		}
		$chprocess($pa, $pd, $active);
	}
	
	//先攻率+12%
	function calculate_active_obbs_multiplier(&$ldata,&$edata)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		$r = 1;
		if (\skillbase\skill_query(42,$ldata) && check_unlocked42($ldata)) $r*=1.12;
		if (\skillbase\skill_query(42,$edata) && check_unlocked42($edata)) $r/=1.12;
		return $chprocess($ldata,$edata)*$r;
	}
	
	//暂时获得并解锁神速
	function skill42_temp_acquire41(&$pa)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		$pa['skill42_flag1']=1;
		if (!\skillbase\skill_query(41,$pa))
		{
			//原先没有神速
			\skillbase\skill_acquire(41,$pa);
			\skill41\unlock41($pa);
			$pa['skill42_flag2']=1;
		}
		else
		{
			$stat = \skill41\check_unlocked41($pa);
			if ($stat) 
				$pa['skill42_flag2']=3; 
			else
			{
				\skill41\unlock41($pa);
				$pa['skill42_flag2']=2; 
			}
		}
	}
	
	//恢复原神速技能状态
	function skill42_restore_skill41(&$pa)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		if (!isset($pa['skill42_flag1']) || !$pa['skill42_flag1']) return;
		if ($pa['skill42_flag2']==1)
		{
			\skillbase\skill_lost(41,$pa);
		}
		else  if ($pa['skill42_flag2']==2)
		{
			\skill41\relock41($pa);
		}
	}
	
	function battle_prepare(&$pa, &$pd, $active)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('logger'));
		if (\skillbase\skill_query(42,$pa) && check_unlocked42($pa))
		{
			//敌方视为具有神速
			skill42_temp_acquire41($pd);
		}
		if (\skillbase\skill_query(42,$pd) && check_unlocked42($pd))
		{
			//敌方视为具有神速
			skill42_temp_acquire41($pa);
		}
		$chprocess($pa, $pd, $active);
	}
	
	function battle_finish(&$pa, &$pd, $active)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		//恢复神速技能状态
		skill42_restore_skill41($pa);
		skill42_restore_skill41($pd);
		$chprocess($pa, $pd, $active);
	}
}

?>
