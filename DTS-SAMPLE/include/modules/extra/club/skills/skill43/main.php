<?php

namespace skill43
{
	function init() 
	{
		define('MOD_SKILL43_INFO','club;upgrade;limited;');
		eval(import_module('clubbase'));
		$clubskillname[43] = '龙胆';
	}
	
	function acquire43(&$pa)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
	}
	
	function lost43(&$pa)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
	}
	
	function check_unlocked43(&$pa)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		return ($pa['lvl']>=3);
	}
	
	function upgrade43()
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('player','logger'));
		if (!\skillbase\skill_query(43) || !check_unlocked43($sdata))
		{
			$log .= '你没有这个技能。<br>';
			return;
		}
		eval(import_module('input'));
		$val = (int)$skillpara1;
		if ($val!=1 && $val!=2)
		{
			$log.='参数不合法。<br>';
			return;
		}
		if ($val==1)
		{
			if (!\skillbase\skill_query(41)) \skillbase\skill_acquire(41);
			\skill41\unlock41($sdata);
		}
		else
		{
			if (!\skillbase\skill_query(42)) \skillbase\skill_acquire(42);
			\skill42\unlock42($sdata);
		}
		\skillbase\skill_lost(43);
		$log.='选择成功。<br>';
	}
}

?>
