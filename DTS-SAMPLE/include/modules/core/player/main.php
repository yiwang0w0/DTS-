<?php

namespace player
{
	global $db_player_structure, $db_player_structure_types, $gamedata, $cmd, $main, $sdata;//注意，$sdata所有键值都是引用！
	global $fog,$upexp,$lvlupexp,$iconImg,$iconImgB,$iconImgBwidth,$ardef;//这些鬼玩意可以回头全部丢进$uip
	global $hpcolor,$spcolor,$newhpimg,$newspimg,$splt,$hplt, $tpldata; 
	
	function init()
	{
		eval(import_module('sys'));
		
		global $db_player_structure, $db_player_structure_types, $tpldata; 
		$db_player_structure = $db_player_structure_types = $tpldata=Array();
		$result = $db->query("DESCRIBE {$gtablepre}players");//这样的一个直接后果是：涉及到player.sql的改动需要先开新游戏再重载执行代码缓存才有效
		while ($sttdata = $db->fetch_array($result))
		{
			global ${$sttdata['Field']}; 
			$db_player_structure[] = $sttdata['Field'];
			$db_player_structure_types[$sttdata['Field']] = $sttdata['Type'];
			//array_push($db_player_structure,$pdata['Field']);
		}
	}
	
	//创建玩家锁文件。在daemon进程结束以及commmand_act.php结束时都会检查并释放玩家池对应的锁文件
	//返回值：0正常加锁  1本进程上过锁  2被阻塞导致失败  3无需加锁
	function create_player_lock($pdid, $forced=0)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('sys'));
		if(!defined('IN_COMMAND') && !$forced) return 3;//若没开启$forced，COMMAND以外的指令不加锁
		if(isset($pdata_lock_pool[$pdid])) return 1;//如果玩家池已存在，认为已经上锁了
		//if(!is_dir(GAME_ROOT.'./gamedata/tmp/playerlock/')) mymkdir(GAME_ROOT.'./gamedata/tmp/playerlock/');
		$dir = GAME_ROOT.'./gamedata/tmp/playerlock/room'.$groomid.'/';
		$file = 'player_'.$pdid.'.nlk';
		$lstate = \sys\check_lock($dir, $file, 5000);//最多允许5秒等待，之后穿透
		$res = 2;
		if(!$lstate) {
			if(\sys\create_lock($dir, $file)) $res = 0;
		}
		return $res;
	}
	
	//释放玩家锁文件
	function release_player_lock($pdid)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('sys'));
		$dir = GAME_ROOT.'./gamedata/tmp/playerlock/room'.$groomid.'/';
		$file = 'player_'.$pdid.'.nlk';
		\sys\release_lock($dir, $file);
		//writeover('a.txt', $dir.' ' .$file."\r\n",'ab+');
	}
	
	//清空玩家池对应的进程锁
	function release_lock_from_pool()
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('sys'));
		if(!empty($pdata_lock_pool)) {
			foreach(array_keys($pdata_lock_pool) as $pdid){
				release_player_lock($pdid);
			}
		}
		$pdata_lock_pool=array();
	}
	
	//注意这个函数默认情况下只能找玩家
	function fetch_playerdata($Pname, $Ptype = 0, $ignore_pool = 0)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('sys'));
		$pdata = false;
		if(!$ignore_pool){
			foreach($pdata_pool as $pd){
				if(isset($pd['name']) && $pd['name'] == $Pname){
					$pdata = $pd;
					break;
				}
			}
		}
		if(empty($pdata)){
			//先进行玩家锁判定
			$query = "SELECT pid FROM {$tablepre}players WHERE name = '$Pname' AND type = '$Ptype'";
			$result = $db->query($query);
			if(!$db->num_rows($result)) return NULL;
			$pdid = $db->fetch_array($result);
			$pdid = $pdid['pid'];
			create_player_lock($pdid);
			//阻塞结束后再真正取玩家数据，牺牲性能避免脏数据
			$query = "SELECT * FROM {$tablepre}players WHERE pid = '$pdid'";
			$result = $db->query($query);
			$pdata = $db->fetch_array($result);
			$pdata_origin_pool[$pdata['pid']] = $pdata_pool[$pdata['pid']] = $pdata;
			$pdata_lock_pool[$pdata['pid']] = 1;
			//if($pdata['name'] == 'a') writeover('a.txt', $pdata['hp'].' ','ab+');
		}
		$pdata = playerdata_construct_process($pdata);
		return $pdata;
	}
	
	function fetch_playerdata_by_pid($pid)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('sys'));
		if(isset($pdata_pool[$pid])){
			$pdata = $pdata_pool[$pid];
		}else{
			$result = $db->query("SELECT pid FROM {$tablepre}players WHERE pid = '$pid'");
			if(!$db->num_rows($result)) return NULL;
			$pdid = $db->fetch_array($result);
			$pdid = $pdid['pid'];
			create_player_lock($pdid);
			$query = "SELECT * FROM {$tablepre}players WHERE pid = '$pdid'";
			$result = $db->query($query);
			$pdata = $db->fetch_array($result);
			$pdata_origin_pool[$pdata['pid']] = $pdata_pool[$pdata['pid']] = $pdata;
			$pdata_lock_pool[$pdata['pid']] = 1;
		}
		$pdata = playerdata_construct_process($pdata);
		return $pdata;
	}
	
	function fetch_original_playerdata_by_id($pid)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('sys'));
		if(isset($pdata_origin_pool[$pid])){
			$pdata = $pdata_origin_pool[$pid];
		}else{
			$pdata = NULL;
		}
		return $pdata;
	}
	
	//对从数据库里读出来的raw数据的处理都继承这个函数
	function playerdata_construct_process($data){
		if (eval(__MAGIC__)) return $___RET_VALUE;
		//备份取出数据库时的player state
		//然后如果player state在写回时没有变，就直接unset掉
		//真正的防并发复活问题是用player_dead_flag这个单向的变量保证的，
		//但这个可以保证在并发问题发生时，绝大多数情况下UI不出问题（否则就会出现UI显示玩家死了却不显示死因的奇怪问题）
		//虽然理论上如果是在玩家触发state变化的那一瞬间（比如进入睡眠状态）被杀UI还是会挂，但是这几率太小了无视
		$data['state_backup']=$data['state'];
		return $data;
	}
	
	//注意！全局变量$sdata虽然是个数组，但是其中的每一个键值都是引用，单纯复制这个数组会导致引用问题！
	function load_playerdata($pdata)//其实最早这个函数是显示用的
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('sys','player'));
		foreach ($pdata as $key => $value) $$key=$value;
		$sdata=Array();
		foreach ($db_player_structure as $key)
			$sdata[$key]=&$$key;
		$sdata['state_backup']=$pdata['state_backup'];	//见上个函数注释
	}
	
	function get_player_killmsg(&$pdata)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('sys'));
		if ($pdata['type']==0)
		{
			$result = $db->query ( "SELECT killmsg FROM {$gtablepre}users WHERE username = '{$pdata['name']}'" );
			$kilmsg = $db->result ( $result, 0 );
			return $kilmsg;
		}
		return '';
	}
	
	function get_player_lastword(&$pdata)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('sys'));
		if ($pdata['type']==0)
		{
			$result = $db->query ( "SELECT lastword FROM {$gtablepre}users WHERE username = '{$pdata['name']}'" );
			$lstwd = $db->result ( $result, 0 );
			return $lstwd;
		}
		return '';
	}
	
	//查skill_query()如果给的$pa是NULL则会自动调用当前玩家，这是个大坑，判定陷阱方面尤其如此……只能给个假玩家数据蒙混一下了
	function create_dummy_playerdata()
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('player'));
		$dummy = array();
		foreach($db_player_structure_types as $f => $t)
		{
			if(strpos($t,'int')!==false) $v=0;
			else $v='';
			$dummy[$f] = $v;
		}
		return $dummy;
	}
	
	function icon_parser($type, $gd, $icon){
		if (eval(__MAGIC__)) return $___RET_VALUE;
		
		if(is_numeric($icon)){
			if(!$type){
				$iconImg = $gd.'_'.$icon.'.gif';
				$iconImgB = $gd.'_'.$icon.'a.gif';
			}else{
				$iconImg = 'n_'.$icon.'.gif';
				$iconImgB = 'n_'.$icon.'a.gif';
			}
		}else{
			$iconImg = $icon;
			$ext = pathinfo($icon,PATHINFO_EXTENSION);
			$iconImgB = substr($icon,0,strlen($icon)-strlen($ext)-1).'_a.'.$ext;
		}
		$iconImgBwidth = 0;
		if(!file_exists('img/'.$iconImgB)) {
			$iconImgB = '';
		}else {
			list($w,$h) = getimagesize('img/'.$iconImgB);
			if($h < 340) $iconImgB = '';
			else $iconImgBwidth = round($w/($h/340));
		}
		return array($iconImg, $iconImgB, $iconImgBwidth);
	}
	
	function init_playerdata(){
		if (eval(__MAGIC__)) return $___RET_VALUE;
		
		eval(import_module('sys','player'));
		
		//$ardef = $arbe + $arhe + $arae + $arfe;
		list($iconImg, $iconImgB, $iconImgBwidth) = icon_parser($type, $gd, $icon);
		
		if(!$weps) {
			$wep = $nowep;$wepk = 'WN';$wepsk = '';
			$wepe = 0; $weps = $nosta;
		}
		if(!$arbs) {
			$arb = $noarb;$arbk = 'DN'; $arbsk = '';
			$arbe = 0; $arbs = $nosta;
		}
	}
	
	//玩家界面非profile的信息渲染，目前而言只有command需要调用
	function parse_interface_gameinfo() {
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('sys','player','logger'));
		
		$uip['innerHTML']['log'] = $log;
		if ($gametype!=2) $uip['innerHTML']['anum'] = $alivenum;
		else $uip['innerHTML']['anum'] = $validnum;
//		$uip['innerHTML']['weather'] = $wthinfo[$weather];
//		$uip['innerHTML']['gamedate'] = "{$month}月{$day}日 星期{$week[$wday]} {$hour}:{$min}";
//		if ($gamestate == 40 ||($gametype == 17 && \skillbase\skill_getvalue(1000,'step')>=206)) {
//			$uip['innerHTML']['gamestateinfo'] = '<span class="yellow">连斗</span>';
//		} elseif ($gamestate == 50) {
//			$uip['innerHTML']['gamestateinfo'] = '<span class="red">死斗</span>';
//		}
	}
	
	//玩家界面profile数据渲染
	function parse_interface_profile(){
		if (eval(__MAGIC__)) return $___RET_VALUE;
		
		eval(import_module('sys','player','map'));
		
		$ardef = $arbe + $arhe + $arae + $arfe;
		
		//$karma = ($rp * $killnum - $def )+ $att;
		
		$hpcolor = 'clan';
		if($hp <= $mhp*0.2) $hpcolor = 'red';
		elseif($hp <= $mhp*0.5) $hpcolor = 'yellow';
		$newhppre = 6+floor(155*(1-$hp/$mhp));
		$newhpimg = '<img src="img/hpman.gif" style="position:absolute; clip:rect('.$newhppre.'px,55px,160px,0px);">';
		$hpltp = 3+floor(155*(1-$hp/$mhp));
		$hplt = '<img src="img/hplt.gif" style="position:absolute; clip:rect('.$hpltp.'px,55px,160px,0px);">';
		
		$spcolor = 'clan';
		if($sp <= $msp*0.2) $spcolor = 'grey';
		elseif($sp <= $msp*0.5) $spcolor = 'yellow';
		$newsppre = 6+floor(155*(1-$sp/$msp));
		$newspimg = '<img src="img/spman.gif" style="position:absolute; clip:rect('.$newsppre.'px,55px,160px,0px);">';
		$spltp = 3+floor(155*(1-$sp/$msp));
		$splt = '<img src="img/splt.gif" style="position:absolute; clip:rect('.$spltp.'px,55px,160px,0px);">';
		
		$uip['innerHTML']['pls'] = $plsinfo[$pls];
		$uip['value']['teamID'] = $teamID;
		if($teamID){
			$uip['innerHTML']['chattype'] = "<select name=\"chattype\" value=\"2\"><option value=\"0\" selected>$chatinfo[0]<option value=\"1\" >$chatinfo[1]</select>";
		}else{
			$uip['innerHTML']['chattype'] = "<select name=\"chattype\" value=\"2\"><option value=\"0\" selected>$chatinfo[0]</select>";
		}
		\map\init_areatiming();
		return;
	}

	//玩家禁区死亡或者回避禁区的处理
	function addarea_pc_process($atime)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('sys','map'));
		if($areanum >= sizeof($plsinfo) - 1) return;//如果禁区数已达上限，跳过所有处理（gameover()函数会判定游戏结束）
		$now_areaarr = array_slice($arealist,0,$areanum+1);
		$where = "('".implode("','",$now_areaarr)."')";//构建查询列表——当前所有禁区
		$result = $db->query("SELECT * FROM {$tablepre}players WHERE pls IN $where AND hp>0");
		while($sub = $db->fetch_array($result)) 
		{
			addarea_pc_process_single($sub, $atime);
		}
		$alivenum = $db->result($db->query("SELECT COUNT(*) FROM {$tablepre}players WHERE hp>0 AND type=0"), 0);
		$chprocess($atime);
		return;
	}
	
	//对每个在禁区里的角色进行单独处理
	function addarea_pc_process_single($sub, $atime){
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('sys','map'));
		$pid = $sub['pid'];
		$o_sub = $sub;
		if(!$sub['type']){
			//echo $sub['pid'].'=player ';
			if (!check_pc_avoid_killarea($sub, $atime)){
				$sub['hp'] = 0;
				$sub['state'] = 11;
				$sub['bid'] = 0;
				$sub['endtime'] = $atime;
				$sub['player_dead_flag'] = 1;
				$db->array_update("{$tablepre}players",$sub,"pid='$pid'",$o_sub);
				addnews($atime,'death11',$sub['name'],$sub['type'],$sub['pls']);
				$deathnum++;
			}else{
				$pls_available = \map\get_safe_plslist();//不能移动去的区域
				if(!$pls_available) $pls_available = \map\get_safe_plslist(0);//如果只能移动到危险区域，就移动到危险区域
				shuffle($pls_available);
				$sub['pls'] = $pls_available[0];
				$db->array_update("{$tablepre}players",$sub,"pid='$pid'",$o_sub);
				post_pc_avoid_killarea($sub, $atime);
			}
		}
	}
	
	function check_pc_avoid_killarea($sub, $atime){
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('sys','map'));
		if($gamestate < 40 && $areaesc) return true;
		else return false;
	}
	
	function post_pc_avoid_killarea($sub, $atime){
		if (eval(__MAGIC__)) return $___RET_VALUE;
	}

//	function add_new_killarea($where,$atime)
//	{
//		if (eval(__MAGIC__)) return $___RET_VALUE;
//		
//		eval(import_module('sys','map'));
//		$plsnum = sizeof($plsinfo) - 1;
//		if ($areanum >= sizeof($plsinfo) - 1) return $chprocess($where);
//		$query = $db->query("SELECT * FROM {$tablepre}players WHERE pls={$where} AND type=0 AND hp>0");
//		while($sub = $db->fetch_array($query)) 
//		{
//			$pid = $sub['pid'];
//			if (($gamestate >= 40 && (!$areaesc && ($sub['tactic']!=4))) || $areanum >= $plsnum)
//			{
//				$hp = 0;
//				$state = 11;
//				$deathpls = $sub['pls'];
//				$bid = 0;
//				$endtime = $atime;
//				$db->query("UPDATE {$tablepre}players SET hp='$hp', bid='$bid', state='$state', endtime='$endtime' WHERE pid=$pid");
//				addnews($endtime,"death$state",$sub['name'],$sub['type'],$deathpls);
//				$deathnum++;
//			}
//			else
//			{	
//				$pls = $arealist[rand($areanum+1,$plsnum)];
//				$db->query("UPDATE {$tablepre}players SET pls='$pls' WHERE pid=$pid");
//			}
//		} 
//		$alivenum = $db->result($db->query("SELECT COUNT(*) FROM {$tablepre}players WHERE hp>0 AND type=0"), 0);
//		$chprocess($where,$atime);
//	}
	
	function update_sdata()
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
	}
	
	
	//返回一个只有数据库合法字段键名的pdata数组
	function player_format_with_db_structure($data){
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('sys','player'));
		$ndata=Array();
		foreach ($db_player_structure as $key){
			if (isset($data[$key])) {
				$ndata[$key]=$data[$key];
			}
		}
		return $ndata;
	}
	
	//和玩家池里的数据进行对比，返回改变的值组成的数组
	function player_diff_from_poll($data){
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('sys','player'));
		if(isset($data['pid']))
		{
			$dpid = $data['pid'];
			if(isset($pdata_origin_pool[$dpid])){//数据池里存在这个pid的数据，则逐项对比
				$ndata=Array();
				$dklist = array_keys($data);
				
				foreach($dklist as $key){
					if(!isset($pdata_origin_pool[$dpid][$key]) || $data[$key] !== $pdata_origin_pool[$dpid][$key]){
						$ndata[$key]=$data[$key];
					}
				}
				return $ndata;
			}else{//数据池没有，直接返回
				return $data;
			}
		}
	}
	
	function player_save($data)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		
		eval(import_module('sys','player'));
		if(isset($data['pid']))
		{
			$spid = $data['pid'];
			//unset($data['pid']);
			//$ndata=Array();
			//if(!$spid) writeover('a.txt','seems problem.'.debug_backtrace()[0]['function']);
			
			$pdata_pool[$spid] = array_clone($data);
			//键名合法化
			$ndata = player_format_with_db_structure($data);
			//任意列的数值没变就不写数据库
			//会导致严重的脏数据问题，在player表加行锁前就先不搞这个了
			$ndata = player_diff_from_poll($ndata);
			unset($ndata['pid']);
			//建国后不准成精，你们复活别想啦
			if ($data['hp']<=0) {
				$ndata['player_dead_flag'] = 1;
				$pdata_origin_pool[$spid]['player_dead_flag'] = $pdata_pool[$spid]['player_dead_flag'] = 1;
			}
			
			//player_dead_flag单向，只能向数据库写入1，不能改回0
			if (isset($ndata['player_dead_flag']) && !$ndata['player_dead_flag']) {
				unset($ndata['player_dead_flag']);
			}
			
			//如果state没变就不写回state了，防止并发问题发生时UI挂掉（见fetch_playerdata注释）
//			if (isset($ndata['state_backup']) && $ndata['state']==$data['state_backup']){
//				unset($ndata['state']);
//			}
			
			if (sizeof($ndata)>0){
				$db->array_update("{$tablepre}players",$ndata,"pid='$spid'");
				//这里困扰了我一晚上，不知道为什么加了下面这句话就会导致$pdata_origin_pool里的$action自动变化以至于无法写入，最后注释掉了事……
				//知道了，见前面的load_playerdata()定义，全局变量$sdata里每一个键值都是对外面变量的引用……
				//简直是醉，这么牛逼的逻辑谁写的，要知道我写3.0的时候硬生生把所有地方都改成写数组，也不敢瞎引用
				//现在这里是一个数组浅拷贝
				$pdata_origin_pool[$spid] = array_clone($data);
			}
		}
		return;
	}
	
	function rs_game($xmode)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		
		$chprocess($xmode);
		
		eval(import_module('sys'));
		$sqldir = GAME_ROOT.'./gamedata/sql/';
		
		if ($xmode & 4) {
			//echo " - 角色数据库初始化 - ";
			$sql = file_get_contents("{$sqldir}players.sql");
			$sql = str_replace("\r", "\n", str_replace(' bra_', ' '.$tablepre, $sql));
			$db->queries($sql);
			//runquery($sql);
			$validnum = $alivenum = $deathnum = 0;
		}
		
		//save_gameinfo();
	}

	function deathnews(&$pa, &$pd)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		
		eval(import_module('sys','map','player'));
		$lwname = $typeinfo [$pd['type']] . ' ' . $pd['name'];
		$lstwd = \player\get_player_lastword($pd);
		\sys\addchat(3, $lstwd, '【'.$plsinfo[$pd['pls']].'】 '.$lwname);
		//$db->query ( "INSERT INTO {$tablepre}chat (type,`time`,send,recv,msg) VALUES ('3','$now','【{$plsinfo[$pd['pls']]}】 $lwname','','$lstwd')" );
		if ($pd['sourceless']) $x=''; else $x=$pa['name'];
		\sys\addnews ( $now, 'death' . $pd['state'], $pd['name'], $pd['type'], $x , $pa['attackwith'], $lstwd );
	}
	
	//维护一个名为'revive_sequence'的列表
	//键名为顺序，顺序越小越优先执行；键值在revive_process()里处理
	function set_revive_sequence(&$pa, &$pd)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		$pd['revive_sequence'] = array();
		return;
	}
	
	//复活的统一设置
	function revive_process(&$pa, &$pd)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		if(empty($pd['revive_sequence'])) return;
		//var_dump($pd['revive_sequence']);
		ksort($pd['revive_sequence']);
		//writeover('a.txt',var_export($pd['revive_sequence'],1));
		foreach($pd['revive_sequence'] as $rkey){
			//调用判定是否复活的函数，注意不可在这里直接执行复活
			//echo $rkey;
			if(revive_check($pa, $pd, $rkey)) {
				//执行复活函数，由于复活基本操作需要统一和复用，尽量不要直接继承revive_events()，尽量继承前后两个函数
				pre_revive_events($pa, $pd, $rkey);
				revive_events($pa, $pd, $rkey);
				post_revive_events($pa, $pd, $rkey);
				break;
			}
		}
	}
	
	//复活判定，建议采用或的逻辑关系
	function revive_check(&$pa, &$pd, $rkey)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		return false;
	}
	
	//复活前和复活后要执行的函数
	function pre_revive_events(&$pa, &$pd, $rkey)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		$pd['o_state'] = $pd['state'];
		return;
	}
	
	function post_revive_events(&$pa, &$pd, $rkey)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		return;
	}
	
	function revive_events(&$pa, &$pd, $rkey)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('sys'));
		$pd['state']=0;//死亡方式设为0
		$pd['hp']=1;//hp设为1，如果需要满血请在post_revive_events里设置
		$deathnum--;
		if ($pd['type']==0) $alivenum++;
		save_gameinfo();
		return;
	}
	
	//请自己设置好$pd['state']再调用，$pa为伤害来源，$pd为死者，$pa['attackwith']为死亡途径描述，返回$killmsg
	//如没有伤害来源，请把$pa设为&$pd，然后把$pd['sourceless']设为true
	//注意，“没有伤害来源”和“伤害来源是自己”是不同的！
	//例：常规击杀，有伤害来源，$pa为击杀者，$pd为死者，$pa['attackwith']为武器名
	//例：死于自己设置的陷阱，有伤害来源（来源是自己），$pa为死者自己，$pd亦为死者自己，$pa['attackwith']为陷阱名，$pd['sourceless']为假
	//例：死于野生陷阱，无伤害来源，$pa为死者自己，$pd亦为死者自己，$pa['attackwith']为陷阱名，$pd['sourceless']为真
	//调用完了记得player_save（而且如果是自己还需要再load_playerdata）双方数据才能生效！！
	function kill(&$pa, &$pd) 
	{	
		if (eval(__MAGIC__)) return $___RET_VALUE;
		
		eval(import_module('sys'));
		$pd['hp'] = 0; 
		if (!isset($pd['sourceless']) || $pd['sourceless']==0) $pd['bid'] = $pa['pid'];
		
		if ($pa['pid'] != $pd['pid'])
			$kilmsg = \player\get_player_killmsg($pa);
		else  $kilmsg = '';
		
		if ($pd['type']==0 && $pd['pid']!=$pa['pid']) $pa['killnum']++;
	
		deathnews($pa, $pd);
		
		$deathnum ++;
		if ($pd['type']==0) $alivenum--; 

		$pd['endtime'] = $now;
		save_gameinfo ();
		
		//复活判定注册
		set_revive_sequence($pa, $pd);
		//复活实际执行
		revive_process($pa, $pd);
		
		return $kilmsg;
	}
	
	function pre_act()
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('sys','player'));
		if ($player_dead_flag) $hp = 0;
		if ($hp<=0) $player_dead_flag = 1;
	}
	
	function act()	
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		
		eval(import_module('sys','player','input'));

			if($command == 'menu') {
				$mode = 'command';
			} elseif($mode == 'command') {
				if($command == 'special') {
				/*
					if($sp_cmd == 'sp_word'){
						include_once GAME_ROOT.'./include/game/special.func.php';
						getword();
						$mode = $sp_cmd;
					}elseif($sp_cmd == 'sp_adtsk'){
						include_once GAME_ROOT.'./include/game/special.func.php';
						adtsk();
						$mode = 'command';
					}else{
				*/
						$mode = $sp_cmd;
				//	}
					
				} 
			} 
			/*
			elseif($mode == 'special') {
				include_once GAME_ROOT.'./include/game/special.func.php';
				if(strpos($command,'chkp') === 0) {
					$itmn = substr($command,4,1);
					chkpoison($itmn);
				}
			*/
			/*
			} elseif($mode == 'chgpassword') {
				include_once GAME_ROOT.'./include/game/special.func.php';
				chgpassword($oldpswd,$newpswd,$newpswd2);
			} elseif($mode == 'chgword') {
				include_once GAME_ROOT.'./include/game/special.func.php';
				chgword($newmotto,$newlastword,$newkillmsg);
			}
			*/
	}
	
	function post_act()
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
	}
	
	function prepare_response_content()
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('player'));
		$gamedata['innerHTML']['notice'] = ob_get_contents();
	}
	
	//这个函数是game.php里调用的，上面那个是command.php里调用的。好像有点猎奇的小区别…… 
	function prepare_initial_response_content()
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
	}
	
	function post_enterbattlefield_events(&$pa)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
	}
}

?>