<?php

defined('IN_GAME') || define('IN_GAME', TRUE);
defined('IN_COMMAND') || define('IN_COMMAND', TRUE);
defined('CURSCRIPT') || define('CURSCRIPT', 'game');
defined('GAME_ROOT') || define('GAME_ROOT', dirname(__FILE__).'/');
require GAME_ROOT.'./include/modulemng/modulemng.config.php';

if ($___MOD_SRV)
{
	if (isset($_POST['conn_passwd']) && $_POST['conn_passwd']==$___MOD_CONN_PASSWD && isset($_POST['command']) && $_POST['command']=='start')
	{
		define('IN_DAEMON', TRUE);
		define('GEXIT_RETURN_JSON', TRUE);
		
		//脚本以server模式被启动
		//让调用端不再等待立即退出
		ob_end_clean();
		header("Connection: close");
		header("HTTP/1.1 200 OK");
		ob_start();
		echo "received";
		$size=ob_get_length();
		header("Content-Length: $size");
		ob_end_flush();
		flush();
		unset($size);

		//开始执行
		if (isset($_POST['is_root'])) $___TEMP_is_root=1; else $___TEMP_is_root=0;
		
		unset($_COOKIE); unset($_POST); unset($_GET); unset($_REQUEST); unset($_FILES);
		
		$___TEMP_max_time = ini_get('max_execution_time');
		if ($___TEMP_max_time == 0) $___TEMP_max_time = 1800;
		set_time_limit($___TEMP_max_time);
		$___TEMP_server_start_time = time();
		
		ignore_user_abort(1);
		
		require GAME_ROOT.'./include/common.inc.php';
		require GAME_ROOT.'./include/socket.func.php';
		
		$___TEMP_EXPECTED_DEATH = 0;
		register_shutdown_function('MODSRV_shutDownFunction');
		
		$___TEMP_runmode = 'Server';
		$___TEMP_CONN_PORT = '-1';
		
		global $___PRESET_SYS__VARS__db; 
		$___PRESET_SYS__VARS__db=$db; 	//不重复连接db
					
		$___TEMP_db=$db;
		$___TEMP_tablepre=$tablepre;
		$___TEMP_newsrv_flag = 0;
		$___TEMP_last_cmd = 0;
		
		__SOCKET_LOG__("新服务器被启动，开始工作。"); 
		$___TEMP_socket=socket_create(AF_INET,SOCK_STREAM,getprotobyname("tcp"));  
		if ($___TEMP_socket===false) __SOCKET_ERRORLOG__('socket_create失败。'); 
		if (socket_set_option($___TEMP_socket,SOL_SOCKET,SO_REUSEADDR,1)===false) __SOCKET_ERRORLOG__('socket_set_option失败。'); 
		//socket_set_nonblock($___TEMP_socket);
		while (1)
		{
			$___TEMP_CONN_PORT_TRY=rand($___MOD_CONN_PORT_LOW,$___MOD_CONN_PORT_HIGH);
			__SOCKET_DEBUGLOG__("正在尝试绑定端口".$___TEMP_CONN_PORT_TRY."...");
			if (socket_bind($___TEMP_socket,'127.0.0.1',$___TEMP_CONN_PORT_TRY) === false) 
				__SOCKET_DEBUGLOG__("绑定端口".$___TEMP_CONN_PORT_TRY.'失败。（'.socket_strerror(socket_last_error()).'）'); 
			else
			{
				__SOCKET_LOG__("绑定端口".$___TEMP_CONN_PORT_TRY.'成功。'); 
				$___TEMP_CONN_PORT = $___TEMP_CONN_PORT_TRY;
				break;
			}
		}
		if (socket_listen($___TEMP_socket,5)===false) __SOCKET_ERRORLOG__('socket_listen失败。'); 
		
		mymkdir(GAME_ROOT.'./gamedata/tmp/server/'.$___TEMP_CONN_PORT);
		
		//进入闲置状态
		if (file_exists(GAME_ROOT.'./gamedata/tmp/server/'.$___TEMP_CONN_PORT.'/busy'))
			unlink(GAME_ROOT.'./gamedata/tmp/server/'.$___TEMP_CONN_PORT.'/busy');
				
		//__SOCKET_WARNLOG__("file: ".xdebug_get_profiler_filename());
		
		__SOCKET_DEBUGLOG__("开始监听端口..");
		while (true) 
		{  
			if (!__SOCKET_CHECK_WITH_TIMEOUT__($___TEMP_socket, 'a', $___MOD_SRV_WAKETIME, 0))
			{
				$___TEMP_runned_time = time()-$___TEMP_server_start_time;
				if ($___TEMP_runned_time+$___MOD_SRV_WAKETIME+5>$___TEMP_max_time)
				{
					//没有下一次唤醒了，主动退出
					__SOCKET_LOG__("已经运行了 ".$___TEMP_runned_time."秒，超过了".$___TEMP_max_time."秒的限制。主动退出。");
					if (!$___TEMP_newsrv_flag)
						__SOCKET_LOG__("由于过长时间没有收到命令且不是惟一的服务器，没有要求启动替代者。");
					__SERVER_QUIT__();
				}
				elseif ($___TEMP_runned_time+$___MOD_SRV_WAKETIME*2+5>$___TEMP_max_time && !$___TEMP_newsrv_flag)
				{
					//老server即将在下一次唤醒时主动退出，发信息给脚本启动一台新server。
					if ($___TEMP_runned_time-$___TEMP_last_cmd<=$___MOD_VANISH_TIME || $___TEMP_is_root)
					{
						__SOCKET_LOG__("已经运行了 ".$___TEMP_runned_time."秒，稍后将退出，已请求脚本启动新服务器。");
						$___TEMP_newsrv_flag = 1;
						if ($___TEMP_is_root)
							touch(GAME_ROOT.'./gamedata/tmp/server/request_new_root_server');
						else  touch(GAME_ROOT.'./gamedata/tmp/server/request_new_server');
					}
				}
				continue;
			}
			//进入忙碌状态
			$___TEMP_last_cmd = time()-$___TEMP_server_start_time;
			touch(GAME_ROOT.'./gamedata/tmp/server/'.$___TEMP_CONN_PORT.'/busy');
			while (__SOCKET_CHECK_WITH_TIMEOUT__($___TEMP_socket, 'a', 0, 0))	//处理全部现有消息队列
			{
				$___TEMP_connection = socket_accept($___TEMP_socket);  
				if($___TEMP_connection)
				{  
					$___TEMP_pagestarttime=microtime();
					__SOCKET_DEBUGLOG__("收到了一个新连接。");
					if (($___TEMP_uid=__SOCKET_LOAD_DATA__($___TEMP_connection))!==false)
					{
						$___TEMP_WORKFLAG=1;
						eval(import_module('sys','map','player','logger','itemmain','input'));
						sys\routine();

						$___TEMP_EXEC_START_TIME=microtime();
						
						include GAME_ROOT.'./command.php';
						
						$___TEMP_WORKFLAG=0;
						
						$___TEMP_tiused=get_script_runtime($___TEMP_EXEC_START_TIME);
						__SOCKET_DEBUGLOG__("执行完成。command.php本体耗时 ".$___TEMP_tiused." 秒。");
						
						$jgamedata = ob_get_contents();
						ob_end_flush();
						
						if ($___MOD_CONN_W_DB)
						{
							$___TEMP_db->query("UPDATE {$___TEMP_tablepre}temp SET value='".base64_encode($jgamedata)."' WHERE sid='{$___TEMP_uid}'");
						}
						else
						{
							writeover($___MOD_TMP_FILE_DIRECTORY.$___TEMP_uid,$jgamedata);
						}
						//返回消息给client
						if (!__SOCKET_CHECK_WITH_TIMEOUT__($___TEMP_connection, 'w', 0, 200000))	
						{
							//允许最多0.2秒等待，这应该已经非常非常宽松了……
							__SOCKET_WARNLOG__("警告：socket_write等待时间过长。结束流程。"); 
						}
						elseif (!socket_write($___TEMP_connection,$___MOD_CONN_PASSWD.'_ok'."\n")) 
						{ 
							__SOCKET_WARNLOG__("警告：socket_write失败。结束流程。"); 
						}  
					}
					socket_close($___TEMP_connection);  
					__SOCKET_DEBUGLOG__("关闭连接。");
					//只有游戏内指令需要储存成录像
					if(!isset($page) || 'command' == $page) {
						if (defined('MOD_REPLAY') && $___MOD_SRV && $___MOD_CODE_ADV3 && !in_array($gametype, $replay_ignore_mode)) 
						{
							if (!isset($jgamedata['url']))
							{
								$pid=(int)$pid;
								$rdir = GAME_ROOT.'./gamedata/tmp/replay/'.$room_prefix.'_/'.$pid;
								if (!file_exists($rdir))
								{
									create_dir($rdir);
								}
								elseif (!is_dir($rdir))
								{
									unlink($rdir);
									create_dir($rdir);
								}
								$rfile = $rdir.'/replay.php';
								$rcont = \replay\replay_record_op($oprecorder).','.($___PAGE_STARTTIME_VALUE-$starttime+$moveut*3600+$moveutmin*60).','.$___MOD_TMP_FILE_DIRECTORY.$___TEMP_uid.','."\n";
								if(!file_exists($rfile)) {
									$rcont = "<?php if(!defined('IN_GAME')) exit('Access Denied'); ?>\n".$rcont;
								}
								file_put_contents($rfile,$rcont,FILE_APPEND);
							}
						}
					}else{
						//非游戏指令则删除对应的回应文件
						//蛋疼，不能删，socket是异步的
						//unlink($___MOD_TMP_FILE_DIRECTORY.$___TEMP_uid);
					}
					//清除进程锁，避免烂代码导致daemon卡死
					//为了防止未来可能会绕过文件末尾那个判定的情况，放在这里
					if(!empty($plock)) {
						\sys\process_unlock();
					}
					\player\release_lock_from_pool();
					//收尾工作，清除所有全局变量
					$___TEMP_remain_list=Array('_SERVER','GLOBALS','magic_quotes_gpc','module_hook_list','language','_ERROR');
							
					$___TEMP_a=Array();
					$___TEMP_a=array_keys(get_defined_vars());
					foreach ($___TEMP_a as $___TEMP_key) 
					{
						if (strpos($___TEMP_key,'___LOCAL_')===0) continue;
						if (strpos($___TEMP_key,'___PRESET_')===0) continue;
						if (strpos($___TEMP_key,'___PRIVATE_')===0) continue;
						if (strpos($___TEMP_key,'___TEMP')===0) continue;
						if (strpos($___TEMP_key,'___MOD')===0) continue;
						if (in_array($___TEMP_key,$___TEMP_remain_list)) continue;
						unset($$___TEMP_key);
					}
					unset($___TEMP_a);
					
					//执行模拟载入代码，为下一次执行做准备
					
					$___LOCAL_INPUT__VARS__INPUT_VAR_LIST=Array();
					
					for ($i=1; $i<=$___TEMP_MOD_LIST_n; $i++) 
						if (strtoupper($___TEMP_MOD_NAME[$i])!='INPUT') 
						{
							$funcname = $___TEMP_MOD_NAME[$i].'\\___pre_init'; $funcname();
							$funcname = $___TEMP_MOD_NAME[$i].'\\init'; $funcname();
							$funcname = $___TEMP_MOD_NAME[$i].'\\___post_init'; $funcname();
							unset($funcname);
						}
					unset($i);
		
					//system('sync && echo 3 > /proc/sys/vm/drop_caches');  
					
					$___TEMP_tiused=get_script_runtime($___TEMP_EXEC_START_TIME);
					__SOCKET_DEBUGLOG__("执行完成。核心占用时间 ".$___TEMP_tiused." 秒。");
				}
			}
			//现在待处理队列为空
			$___TEMP_runned_time = time()-$___TEMP_server_start_time;
			if ($___TEMP_runned_time+$___MOD_SRV_WAKETIME+5>$___TEMP_max_time)
			{
				//没有下一次唤醒了，主动退出
				__SOCKET_LOG__("已经运行了 ".$___TEMP_runned_time."秒。主动退出。");
				__SERVER_QUIT__();
			}
			elseif ($___TEMP_runned_time+$___MOD_SRV_WAKETIME*2+5>$___TEMP_max_time && !$___TEMP_newsrv_flag)
			{
				//老server即将在下一次唤醒时主动退出，发信息给脚本启动一台新server。
				if ($___TEMP_runned_time-$___TEMP_last_cmd<=$___MOD_VANISH_TIME || $___TEMP_is_root)
				{
					__SOCKET_LOG__("已经运行了 ".$___TEMP_runned_time."秒，稍后将退出，已请求脚本启动新服务器。");
					$___TEMP_newsrv_flag = 1;
					if ($___TEMP_is_root)
						touch(GAME_ROOT.'./gamedata/tmp/server/request_new_root_server');
					else  touch(GAME_ROOT.'./gamedata/tmp/server/request_new_server');
				}
			}
			//进入闲置状态
			if (file_exists(GAME_ROOT.'./gamedata/tmp/server/'.$___TEMP_CONN_PORT.'/busy'))
				unlink(GAME_ROOT.'./gamedata/tmp/server/'.$___TEMP_CONN_PORT.'/busy');
		}
	}
	elseif ($___TEMP_WORKFLAG==0)
	{
		ignore_user_abort(1);
		
		$___TEMP_runmode = 'Client';
		$___TEMP_CONN_PORT = -1;
			
		//来自浏览器的调用，应该转发给server
		$cli_pagestartime=microtime(true); 

		define('NO_MOD_LOAD', TRUE);	//不在common.inc载入任何模块
		define('NO_SYS_UPDATE', TRUE);
		require GAME_ROOT.'./include/common.inc.php';
		require GAME_ROOT.'./include/socket.func.php';
		
		__SOCKET_DEBUGLOG__('Client开始执行。');
	
		if ($handle=opendir(GAME_ROOT.'./gamedata/tmp/server')) 
		{
			$flag=0; $srvlist=Array(); $chosen=-1; $touch_error_list=array();
			while (($sid=readdir($handle))!==false) 
			{
				if ($sid=='.' || $sid=='..') continue;
				$sid=(int)$sid; 
				if ($sid<$___MOD_CONN_PORT_LOW || $sid>$___MOD_CONN_PORT_HIGH) continue;
				if (is_dir(GAME_ROOT.'./gamedata/tmp/server/'.(string)$sid))
				{
					array_push($srvlist,$sid);
					$flag=1;
					if (file_exists(GAME_ROOT.'./gamedata/tmp/server/'.$sid.'/busy')) continue;
					$touchflag = __SEND_TOUCH_CMD__($sid);
					if ('ok' != $touchflag && 'ok_root' != $touchflag) {
						$touch_error_list[]=$sid;
						continue;
					}
					$chosen = $sid; break;
				}
			}
			if (!$flag) __SOCKET_ERRORLOG__("未找到在线的服务器。");
			if ($chosen == -1) 
			{
				$z=rand(0,count($srvlist)-1); $chosen=$srvlist[$z];
				touch(GAME_ROOT.'./gamedata/tmp/server/request_new_server');
				__SOCKET_LOG__("没有服务器空闲，已请求脚本启动新服务器。");
			}
			__SOCKET_DEBUGLOG__("选择了端口号为 ".$chosen.'的服务器 。');
			$___TEMP_CONN_PORT=$chosen;
			
			$auto_server_file = GAME_ROOT.'./gamedata/tmp/server/auto_requested_new_server';
			if (!empty($touch_error_list)) //请求daemonmng.php关闭检测到的异常进程
			{
				if(!file_exists($auto_server_file)){
					touch($auto_server_file);
					$daemonmng_url = 'http://'.$_SERVER['HTTP_HOST'].substr($_SERVER['PHP_SELF'],0,-11).'daemonmng.php';
					foreach($touch_error_list as $tev){
						$get_var = 'action=stop'.$tev.'&in_game_pass='.substr(base64_encode($___MOD_CONN_PASSWD),0,6);
						file_get_contents($daemonmng_url.'?'.$get_var);
					}
				}else{
					__SOCKET_ERRORLOG__("服务器连接错误，且尝试自动重启socket失败。");
				}				
			}else{
				if(file_exists($auto_server_file)) unlink($auto_server_file);
			}
			unset($touch_error_list, $auto_server_file, $touchflag, $daemonmng_url, $get_var);
		}
		else  __SOCKET_ERRORLOG__('无法打开gamedata/tmp/server目录。');
		
		__SOCKET_SEND_TO_SERVER__();
		
		__SOCKET_DEBUGLOG__('Client执行完成。');
	
		die();
	}
	else
	{
		//否则是由server自行引用的command.php，开始执行
		$pagestartime=microtime(true); 
	}
}
else	//未开启server-client模式，正常执行准备流程
{
	$pagestartime=microtime(true); 

	require GAME_ROOT.'./include/common.inc.php';
	
	$timecost2 = get_script_runtime($pagestartime);
	
}

////////////////////////////////////////////////////////////////////////////
//////////////////////////调用daemon进行的全局操作//////////////////////////////
////////////////////////////////////////////////////////////////////////////

if(isset($command)){
	if('area_timing_refresh' == $command){//刷新禁区时间
		\sys\routine();
		\map\init_areatiming();
		$gamedata = array('timing' => $uip['timing']);
		ob_clean();
		$jgamedata = gencode($gamedata);
		echo $jgamedata;
		ob_end_flush();
		return;
	}elseif('room_routine' == $command){//刷新房间内游戏状态
		ignore_user_abort(1);
		include_once './include/roommng/roommng.func.php';
		room_all_routine();
		return;
	}
}

////////////////////////////////////////////////////////////////////////////
//////////////////////////执行页面//////////////////////////////
////////////////////////////////////////////////////////////////////////////

if(!isset($page) || 'command' == $page) {
	$___CURSCRIPT = 'ACT';
	include GAME_ROOT.'./include/pages/command_act.php';
}elseif(in_array($page, array('command_game','command_roomcmd','command_valid','command_end','command_winner','command_rank','command_alive','command_help','command_news'))) {
	$___tmp_disable_codeadv3 = 1;//暂时还做不到游戏外页面解压文字
	$___CURSCRIPT = strtoupper(substr($page,strpos($page,'_')+1));
	if('command_help' == $page) {
		$___IN_HELP = 1;//代替常量IN_HELP
	}
	include GAME_ROOT.'./include/pages/'.$page.'.php';
}

//清除进程锁，避免烂代码导致daemon卡死
if(!empty($plock)) {
	\sys\process_unlock();
}
//清除玩家锁
\player\release_lock_from_pool();

/* End of file command.php */
/* Location: /command.php */