<?php

namespace skill403
{
	$paneldesc=array('追击','追击','追击','追击','追击','暴风');
	$procrate=array(0,8,12,20,50,90);
	
	function init() 
	{
		define('MOD_SKILL403_INFO','card;unique;locked;');
		eval(import_module('clubbase'));
		$clubskillname[403] = '追击';
	}
	
	function acquire403(&$pa)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		\skillbase\skill_setvalue(403,'lvl','0',$pa);
	}
	
	function lost403(&$pa)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		\skillbase\skill_delvalue(403,'lvl',$pa);
	}
	
	function check_unlocked403(&$pa)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		return 1;
	}
	
	function get_skill403_procrate(&$pa,&$pd,&$active){
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('skill403','player','logger'));
		if (!\skillbase\skill_query(403, $pa) || !check_unlocked403($pa)) return 0;
		$r = $procrate[\skillbase\skill_getvalue(403,'lvl',$pa)];
		return $r;
	}

	function attack(&$pa, &$pd, $active)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		$chprocess($pa,$pd,$active);
		eval(import_module('logger','skill403'));
		$var_403=get_skill403_procrate($pa,$pd,$active);
		while (rand(0,99)<$var_403){
			$log.="<span class=\"clan\">追加攻击！</span><br>";
			$chprocess($pa,$pd,$active);
		}
	}
}

?>
