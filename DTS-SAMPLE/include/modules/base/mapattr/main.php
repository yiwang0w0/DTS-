<?php

namespace mapattr
{
	function init() {}
	
	function calculate_itemfind_obbs()
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('sys','player','mapattr','logger'));
		//$log.= '发现率修正：'.$pls_itemfind_obbs[$pls].' ';
		if (!isset($pls_itemfind_obbs[$pls])) $r=0; else $r=$pls_itemfind_obbs[$pls];
		return $chprocess()+$r;
	}
	
	function calculate_findman_obbs(&$edata)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('sys','player','mapattr','logger'));
		//$log.= '遇敌率修正：'.$pls_findman_obbs[$pls].' ';
		if (!isset($pls_findman_obbs[$pls])) $r=0; else $r=$pls_findman_obbs[$pls];
		return $chprocess($edata)+$r;
	}
	
	function calculate_meetman_rate($schmode)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('sys','player','mapattr','logger'));
		
		if (!isset($pls_meetman_obbs[$pls])) $r=0; else $r=$pls_meetman_obbs[$pls];
		//$log.= '遇敌率修正：'.$r.' ';
		return $chprocess($schmode)+$r;
	}
	
	function get_att_multiplier(&$pa,&$pd,$active)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('sys','mapattr','logger'));
		//$log.= '攻击率修正：'.$pls_attack_modifier[$pa['pls']].' ';
		if (isset($pls_attack_modifier[$pa['pls']]))		
			return $chprocess($pa,$pd,$active)*(1+$pls_attack_modifier[$pa['pls']]/100);
		else  return $chprocess($pa,$pd,$active);
	}
	
	function get_def_multiplier(&$pa,&$pd,$active)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('sys','mapattr','logger'));
		//$log.= '防御率修正：'.$pls_defend_modifier[$pa['pls']].' ';
		if (isset($pls_defend_modifier[$pa['pls']]))		
			return $chprocess($pa,$pd,$active)*(1+$pls_defend_modifier[$pa['pls']]/100);
		else  return $chprocess($pa,$pd,$active);
	}
	
	function calculate_real_trap_obbs()
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('player'));
		if($pls == 0)	//在后台非常容易踩陷阱
			return $chprocess()+15;
		else  return $chprocess();
	}
}

?>
