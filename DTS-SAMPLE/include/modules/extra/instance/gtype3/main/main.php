<?php

namespace gtype3
{
	function init() {}
	
	function prepare_new_game()
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('sys'));
		//宝石乱斗 offline
		return $chprocess();
		
		if (room_check_subroom($room_prefix)) return $chprocess();
		list($sec,$min,$hour,$day,$month,$year,$wday) = explode(',',date("s,i,H,j,n,Y,w",$now));
		$tg=$gamenum-4;
		$res=$db->query("SELECT gametype FROM {$gtablepre}history WHERE gid='$tg'");
		$gt=3;
		if ($db->num_rows($res)){
			$zz=$db->fetch_array($res); $gt=$zz['gametype'];
		}
		if ($wday==0 && !$disable_event){
 			if (($hour>=20)&&($hour<23)&&($gt!=3)){ 
 				$gametype=3;
 			}
 		}
		$chprocess();
	}
	
	function check_addarea_gameover($atime){
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('sys','map'));
		if ($gametype==3){
			if($alivenum <= 0){
				\sys\gameover($atime,'end1');
				return;
			}
			if ($areanum>=($areaadd*2)){//限时2禁
				$result = $db->query("SELECT * FROM {$tablepre}players WHERE hp>0 AND type=0 ORDER BY killnum DESC LIMIT 1");
				$wdata = $db->fetch_array($result);
				$winner = $wdata['name'];
				\sys\gameover($atime,'end8',$winner);
				return;
			}
			\sys\rs_game(16+32);
			return;
		}
		$chprocess($atime);	
	}
}

?>