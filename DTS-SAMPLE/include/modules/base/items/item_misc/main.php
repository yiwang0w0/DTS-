<?php

namespace item_misc
{
	function init() 
	{
		eval(import_module('itemmain'));
		$iteminfo['U']='扫雷设备';
		if (defined('MOD_NOISE'))
		{
			eval(import_module('noise'));
			$noiseinfo['corpseclear']='一阵强大的吸力';
		}
	}
	
	function parse_itmuse_desc($n, $k, $e, $s, $sk){
		if (eval(__MAGIC__)) return $___RET_VALUE;
		$ret = $chprocess($n, $k, $e, $s, $sk);
		if(strpos($k,'U')===0) {
			$ret .= '使用后将扫除本地1枚效果值不小于'.$e.'的陷阱';
		}elseif(strpos($k,'Y')===0 || strpos($k,'Z')===0){
			if ($n == '凸眼鱼'){
				$ret .= '使用后可以销毁整个战场现有的尸体';
			}elseif ($n == '■DeathNote■') {
				$ret .= '填入玩家的名字和头像就可以直接杀死该玩家';
			}elseif ($n == '游戏解除钥匙') {
				$ret .= '使用后达成『锁定解除』胜利';
			}elseif ($n == '奇怪的按钮') {
				$ret .= '警告：高度危险！';
			}elseif ($n == '『C.H.A.O.S』') {
				$ret .= '献祭包裹里的全部物品以获得通往『幻境解离』的必备道具';
			}elseif ($n == '『S.C.R.A.P』') {
				$ret .= '还不满足『幻境解离』的条件！使用后可以恢复成『C.H.A.O.S』';
			}elseif ($n == '『G.A.M.E.O.V.E.R』') {
				$ret .= '使用后达成『幻境解离』胜利';
			}elseif ($n == '杏仁豆腐的ID卡') {
				$ret .= '连斗后使用可以让全场NPC消失并进入『死斗阶段』';
			}elseif ($n == '水果刀') {
				$ret .= '可以切水果，视你的斩系熟练度决定生成补给还是水果皮';
			}
		}
		return $ret;
	}
	
	function itemuse(&$theitem)
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		
		eval(import_module('sys','player','logger'));
		
		$itm=&$theitem['itm']; $itmk=&$theitem['itmk'];
		$itme=&$theitem['itme']; $itms=&$theitem['itms']; $itmsk=&$theitem['itmsk'];
		
		if ($itmk=='U') 
		{
			$trapresult = $db->query("SELECT * FROM {$tablepre}maptrap WHERE pls = '$pls' AND itme>='$itme'");
			$trpnum = $db->num_rows($trapresult);
			if ($trpnum>0){
				$itemno = rand(0,$trpnum-1);
				$db->data_seek($trapresult,$itemno);
				$mi=$db->fetch_array($trapresult);
				$deld = $mi['itm'];
				$delp = $mi['tid'];
				$db->query("DELETE FROM {$tablepre}maptrap WHERE tid='$delp'");
				if($itm=='☆混沌人肉探雷车★') $log.="远方传来一阵爆炸声，伟大的<span class=\"yellow\">{$itm}</span>用生命和鲜血扫除了<span class=\"yellow\">{$deld}</span>。<br><span class=\"red\">实在是大快人心啊！</span><br>";
				else $log.="远方传来一阵爆炸声，<span class=\"yellow\">{$itm}</span>扫除了<span class=\"yellow\">{$deld}</span>。<br>";
			}else{
				$log.="你使用了<span class=\"yellow\">{$itm}</span>，但是没有发现陷阱。<br>";
			}
			\itemmain\itms_reduce($theitem);
			return;
		}elseif (strpos ( $itmk, 'Y' ) === 0 || strpos ( $itmk, 'Z' ) === 0) {	
			if ($itm == '御神签') {
				$log .= "使用了<span class=\"yellow\">$itm</span>。<br>";
				divining ();
				\itemmain\itms_reduce($theitem);
				return;
			} elseif ($itm == '凸眼鱼') {
				eval(import_module('sys','corpse'));
				$tm = $now - $corpseprotect;//尸体保护
				if ($gametype!=2)
					$db->query ( "UPDATE {$tablepre}players SET corpse_clear_flag='1',weps='0',arbs='0',arhs='0',aras='0',arfs='0',arts='0',itms0='0',itms1='0',itms2='0',itms3='0',itms4='0',itms5='0',itms6='0',money='0' WHERE hp <= 0 AND endtime <= $tm" );
				else	$db->query ( "UPDATE {$tablepre}players SET corpse_clear_flag='1',weps='0',arbs='0',arhs='0',aras='0',arfs='0',arts='0',itms0='0',itms1='0',itms2='0',itms3='0',itms4='0',itms5='0',itms6='0',money='0' WHERE type > 0 AND hp <= 0 AND endtime <= $tm" );
				$cnum = $db->affected_rows ();
				addnews ( $now, 'corpseclear', $name, $cnum );
				if (defined('MOD_NOISE')) \noise\addnoise($pls,'corpseclear',$pid);
				$log .= "使用了<span class=\"yellow\">$itm</span>。<br>突然刮起了一阵怪风，";
				if($cnum) $log .= "<span class=\"yellow\">吹走了地上的{$cnum}具尸体！</span><br>";
				else $log .= "不过好像没有什么效果？";
				\itemmain\itms_reduce($theitem);
				return;
			} elseif ($itm == '■DeathNote■') {
				include template('deathnote');
				$cmd = ob_get_contents();
				ob_clean();
				$log .= '你翻开了■DeathNote■<br>';
				return;
			} elseif ($itm == '游戏解除钥匙') {
				$state = 6;
				$url = 'end.php';
				\sys\gameover ( $now, 'end3', $name );
			}elseif ($itm == '『C.H.A.O.S』') {
				$flag=false;
				$log.="一阵强光刺得你睁不开眼。<br>强光逐渐凝成了光球，你揉揉眼睛，发现包裹里的东西全都不翼而飞了。<br>";
				for ($i=1;$i<=6;$i++){
					//global ${'itm'.$i},${'itmk'.$i},${'itme'.$i},${'itms'.$i},${'itmsk'.$i};
					if (${'itm'.$i}=='黑色发卡') {
						$flag=true;
						$tmp_itm = ${'itm'.$i}; $tmp_itmk = ${'itmk'.$i}; $tmp_itmsk = ${'itmsk'.$i};
						$tmp_itme = ${'itme'.$i}; $tmp_itms = ${'itms'.$i};
					}
					${'itm'.$i} = ${'itmk'.$i} = ${'itmsk'.$i} = '';
					${'itme'.$i} = ${'itms'.$i} = 0;
				}
				$karma=$rp*$killnum-$def+$att;
				$f1=$f2=$f3=false;
				//『G.A.M.E.O.V.E.R』itmk:Y itme:1 itms:1 itmsk:zxZ
				if (($ss>=600)&&($killnum<=15)){
					$itm0='『T.E.R.R.A』';
					$itmk0='Y';
					$itme0=1;
					$itms0=1;
					$itmsk0='z';
					\itemmain\itemget();
					$f1=true;
				}
				if ($karma<=2000){
					$itm0='『A.Q.U.A』';
					$itmk0='Y';
					$itme0=1;
					$itms0=1;
					$itmsk0='x';
					\itemmain\itemget();
					$f2=true;
				}
				if ($flag==true){
					$itm0='『V.E.N.T.U.S』';
					$itmk0='Y';
					$itme0=1;
					$itms0=1;
					$itmsk0='Z';
					\itemmain\itemget();
					$f3=true;
				}
				if (!$f1 || !$f2 || !$f3){
					$itm0='『S.C.R.A.P』';
					$itmk0='Z';
					$itme0=1;
					$itms0=1;
					$itmsk0='';
					\itemmain\itemget();
					if(isset($tmp_itm)){
						for ($i=1;$i<=6;$i++){
							if(!${'itms'.$i}) {
								${'itm'.$i} = $tmp_itm; ${'itmk'.$i} = $tmp_itmk; ${'itmsk'.$i} = $tmp_itmsk;
								${'itme'.$i} = $tmp_itme; ${'itms'.$i} = $tmp_itms;
								break;
							}
						}
					}					
				}
				return;
			}elseif ($itm == '『S.C.R.A.P』') {
				$log.="你眼前一黑。当你再次能看见东西，你发现包裹里的东西再次不翼而飞了。<br>";
				for ($i=1;$i<=6;$i++){
					if (${'itm'.$i}!='黑色发卡'){
						${'itm'.$i} = ${'itmk'.$i} = ${'itmsk'.$i} = '';
						${'itme'.$i} = ${'itms'.$i} = 0;
					}					
				}
				$itm0='『C.H.A.O.S』';
				$itmk0='Z';
				$itme0=1;
				$itms0=1;
				$itmsk0='';
				\itemmain\itemget();
				return;
			}elseif ($itm == '『G.A.M.E.O.V.E.R』') {
				$state = 6;
				$url = 'end.php';
				\sys\gameover ( $now, 'end7', $name );
			}elseif ($itm == '杏仁豆腐的ID卡') {
				eval(import_module('sys'));
				if ($gametype==2)
				{
					$log.='本模式下不可用。<br>';
					return;
				}
				$duelstate = \gameflow_duel\duel($now,$itm);
				if($duelstate == 50){
					$log .= "<span class=\"yellow\">你使用了{$itm}。</span><br><span class=\"evergreen\">“干得不错呢，看来咱应该专门为你清扫一下战场……”</span><br><span class=\"evergreen\">“所有的NPC都离开战场了。好好享受接下来的杀戮吧，祝你好运。”</span>——林无月<br>";
					$itm = $itmk = $itmsk = '';
					$itme = $itms = 0;
				}elseif($duelstate == 51){
					$log .= "你使用了<span class=\"yellow\">{$itm}</span>，不过什么反应也没有。<br><span class=\"evergreen\">“咱已经帮你准备好舞台了，请不要要求太多哦。”</span>——林无月<br>";
				} else {
					$log .= "你使用了<span class=\"yellow\">{$itm}</span>，不过什么反应也没有。<br><span class=\"evergreen\">“表演的时机还没到呢，请再忍耐一下吧。”</span>——林无月<br>";
				}
				return;
			} elseif ($itm == '权限狗的ID卡') {
				$result = $db->query("SELECT groupid,password FROM {$gtablepre}users WHERE username='$cuser'");
				$result = $db->fetch_array($result);
				$ugroupid = $result['groupid'];
				$upassword = $result['password'];
				include_once GAME_ROOT.'./include/user.func.php';
				if(pass_compare($cuser, $cpass, $upassword) && ($ugroupid >= 5 || $cuser == $gamefounder)){
					$log.='大逃杀幻境已确认你的权限狗身份，正在为你输送权限套装……<br>';
					$wp=$wk=$wg=$wc=$wd=$wf=666;
					$ss=$mss=600;
					$att+=200;$def+=200;
					$money+=19980;
					$itm1='美味补给';$itmk1 = 'HB';$itmsk1 = '';$itme1 = 2777;$itms1 = 277;
					$itm2='全恢复药剂';$itmk2 = 'Ca';$itmsk2 = '';$itme2 = 1;$itms2 = 44;
					$itm3='食堂的剩饭';$itmk3 = 'HR';$itmsk3 = '';$itme3 = 100;$itms3 = 15;
					$itm4='量子雷达';$itmk4 = 'ER';$itmsk4 = '2';$itme4 = 20;$itms4 = 1;
					$itm5='聪明药';$itmk5 = 'ME';$itmsk5 = '';$itme5 = 100;$itms5 = 4;
					//$itm5='游戏解除钥匙';$itmk5 = 'Y';$itmsk5 = '';$itme5 = 1;$itms5 = 1;
					$arb='代码聚合体的长袍';$arbk = 'DB';$arbsk = 'Bb';$arbe = 500;$arbs = 100;
					$art='Untainted Glory';$artk = 'A';$artsk = 'Hh';$arte = 1;$arts = 1;
					if (defined('MOD_CLUBBASE')) eval(import_module('clubbase'));
					foreach(array(1010,1011) as $skv){
						if(defined('MOD_SKILL'.$skv)) {
							if (!\skillbase\skill_query($skv)) {
								$log.="你获得了技能「<span class=\"yellow\">$clubskillname[$skv]</span>」！<br>";
								\skillbase\skill_acquire($skv);
							}
						}
					}
					addnews ( $now, 'adminitem', $name, $itm );
				}else{
					$log.='你没有足够的权限。可能因为是你的缓存密码有误，也可能你压根就不是一条权限狗。<br>';
				}
				$itm = $itmk = $itmsk = '';
				$itme = $itms = 0;
				$mode='command';$command='';
				return;
			} elseif ($itm == '奇怪的按钮') {
				$button_dice = rand ( 1, 10 );
				$log .= "你按下了<span class=\"yellow\">$itm</span>。<br>";
				if ($button_dice < 5) {
					$log .= '按钮不翼而飞，你的手中多了一瓶褐色的饮料，上面还有个标签……<br><span class="gold b">“感谢特朗普总统选用我司的可乐递送服务。”</span><br>蛤？<br>';
					$itm = '特朗普特供版「核口可乐」';
					$itmk = 'HB';
					$itmsk = '';
					$itme = 200;
					$itms = 1;
				} elseif ($button_dice < 8) {
					$state = 6;
					$url = 'end.php';
					\sys\gameover ( $now, 'end5', $name );
				} else {
					$log .= '好像什么也没发生嘛？咦，按钮上的标签写着什么？<br><span class="red">“危险，勿触！”</span>……？<br>呜哇，按钮爆炸了！<br>';
					$itm = $itmk = $itmsk = '';
					$itme = $itms = 0;
					$state = 30;
					\player\update_sdata(); $sdata['sourceless'] = 1; $sdata['attackwith'] = '';
					\player\kill($sdata,$sdata);
					\player\player_save($sdata);
					\player\load_playerdata($sdata);
				}
				return;
			} else if (substr($itm,0,strlen('提示纸条'))=='提示纸条') {
				if ($itm == '提示纸条A') {
					$log .= '你读着纸条上的内容：<br>“执行官其实都是幻影，那个红暮的身上应该有召唤幻影的玩意。”<br>“用那个东西然后打倒幻影的话能用游戏解除钥匙出去吧。”<br>';
				} elseif ($itm == '提示纸条B') {
					$log .= '你读着纸条上的内容：<br>“我设下的灵装被残忍地清除了啊……”<br>“不过资料没全部清除掉。<br>用那个碎片加上传奇的画笔和天然属性……”<br>“应该能重新组合出那个灵装。”<br>';
				} elseif ($itm == '提示纸条C') {
					$log .= '你读着纸条上的内容：<br>“小心！那个叫红暮的家伙很强！”<br>“不过她太依赖自己的枪了，有什么东西能阻挡那伤害的话……”<br>';
				} elseif ($itm == '提示纸条D') {
					$log .= '你读着纸条上的内容：<br>“喂你真的是全部买下来了么……”<br>“这样的提示纸条不止这四种，其他的纸条估计被那两位撒出去了吧。”<br>“总之祝你好运。”<br>';
				} elseif ($itm == '提示纸条E') {
					$log .= '你读着纸条上的内容：<br>“生存并不能靠他人来喂给你知识，”<br>“有一套和元素有关的符卡的公式是没有出现在帮助里面的，用逻辑推理好好推理出正确的公式吧。”<br>“金木水火土在这里都能找到哦～”<br>';
				} elseif ($itm == '提示纸条F') {
					$log .= '你读着纸条上的内容：<br>“我不知道另外那个孩子的底细。如果我是你的话，不会随便乱惹她。”<br>“但是她貌似手上拿着符文册之类的东西。”<br>“也许可以利用射程优势？！”<br>“你知道的，法师的射程都不咋样……”<br>';
				} elseif ($itm == '提示纸条G') {
					$log .= '你读着纸条上的内容：<br>“上天保佑，”<br>“请不要在让我在模拟战中被击坠了！”<br>“空羽 上。”<br>';
				} elseif ($itm == '提示纸条H') {
					$log .= '你读着纸条上的内容：<br>“在研究施设里面出了大事的SCP竟然又输出了新的样本！”<br>“按照董事长的意见就把这些家伙当作人体试验吧！”<br>署名看不清楚……<br>';
				} elseif ($itm == '提示纸条I') {
					$log .= '你读着纸条上的内容：<br>“嗯……”<br>“制作神卡所用的各种认证都可以在商店里面买到。”<br>“其实卡片真的有那么强大的力量么？”<br>';
				} elseif ($itm == '提示纸条J') {
					$log .= '你读着纸条上的内容：<br>“知道么？”<br>“果酱面包果然还是甜的好，哪怕是甜的生姜也能配制出如地雷般爆炸似的美味。”<br>“祝你好运。”<br>';
				} elseif ($itm == '提示纸条K') {
					$log .= '你读着纸条上的内容：<br>“水符？”<br>“你当然需要水，然后水看起来是什么颜色的？”<br>“找一个颜色类似的东西合成就有了吧。”<br>';
				} elseif ($itm == '提示纸条L') {
					$log .= '你读着纸条上的内容：<br>“木符？”<br>“你当然需要树叶，而且是拥有治愈之力的树叶。然后说到树叶那是什么颜色？”<br>“找一个颜色类似的东西合成就有了吧。”<br>';
				} elseif ($itm == '提示纸条M') {
					$log .= '你读着纸条上的内容：<br>“火符？”<br>“你当然需要找把火，然后说到火那是什么颜色？”<br>“找一个颜色类似的东西合成就有了吧。”<br>';
				} elseif ($itm == '提示纸条N') {
					$log .= '你读着纸条上的内容：<br>“土符？”<br>“说到土那就是石头吧，然后说到石头那是什么颜色？”<br>“找一个颜色类似的东西合成就有了吧。”<br>';
				} elseif ($itm == '提示纸条P') {
					$log .= '你读着纸条上的内容：<br>“金符？这个的确很绕人……”<br>“说到金那就是炼金，然后这是21世纪了，炼制一个金色方块需要什么？”<br>“总之祝你好运。”<br>';
				} elseif ($itm == '提示纸条Q') {
					$log .= '你读着纸条上的内容：<br>“据说在另外的空间里面；”<br>“一个吸血鬼因为无聊就在她所居住的地方洒满了大雾，”<br>“真任性。”<br>';
				} elseif ($itm == '提示纸条R') {
					$log .= '你读着纸条上的内容：<br>“知道么，”<br>“东方幻想乡这作游戏里面EXTRA的最终攻击”<br>“被老外们称作『幻月的Rape Time』，当然对象是你。”<br>';
				} elseif ($itm == '提示纸条S') {
					$log .= '你读着纸条上的内容：<br>“土水符？”<br>“哈哈哈那肯定是需要土和水啦，可能还要额外的素材吧。”<br>“总之祝你好运。”<br>';
				} elseif ($itm == '提示纸条T') {
					$log .= '你读着纸条上的内容：<br>“我一直对虚拟现实中的某些迹象很在意……”<br>“这种未名的威压感是怎么回事？”<br>“总之祝你好运。”<br>';
				} elseif ($itm == '提示纸条U') {
					$log .= '你读着纸条上的内容：<br>“纸条啥的……”<br>“希望这张纸条不会成为你的遗书。”<br>“总之祝你好运。”<br>';
				} else {
					$log .= '你打开了纸条，发现是一张白纸。<br>';
				}
				return;
			} else if (substr($itm,0,strlen('任务指令书'))=='任务指令书') {
				if ($itm == '任务指令书A') {
					$log .= '指令书上这样写着：<br>“很高兴大家能来参与幻境系统的除错工作。”<br>“我们对系统进行了一些调整，就算遭遇袭击和陷阱也不会造成致命伤害，所以请尽管放心。”<br>“任务结束后我们会根据工作量发放相应的奖励。”<br>';
				} else if ($itm == '任务指令书B') {
					ob_clean();
					include template('MOD_SKILL475_EXPLANATION');
					$log .= ob_get_contents();
					ob_clean();
					return;
				} else {
					$log .= '你展开了指令书，发现上面什么都没写。<br>';
				}
				return;
			}elseif ($itm == '仪水镜') {
				$log .= '水面上映出了你自己的脸，你仔细端详着……<br>';
				if ($rp < 40){
					$log .= '你的脸看起来十分白皙。<br>';
				} elseif ($rp < 200){
					$log .= '你的脸看起来略微有点黑。<br>';
				} elseif ($rp < 550){
					$log .= '你的脸上貌似笼罩着一层黑雾。<br>';
				} elseif ($rp < 1200){
					$log .= '你的脸已经和黑炭差不多了，赶快去洗洗！<br>';
				} elseif ($rp < 5499){
					$log .= '你印堂漆黑，看起来最近要有血光之灾！<br>';
				} elseif ($rp > 5500){
					$log .= '水镜中已经黑的如墨一般了。<br>希望你的H173还在……<br>';
				} else{
					$log .= '你的脸从水镜中消失了。<br>';
				}
				return;
			} elseif ($itm == '风祭河水'){
				$slv_dice = rand ( 1, 20 );
					if ($slv_dice < 8) {
					$log .= "你一口干掉了<span class=\"yellow\">$itm</span>，不过好像什么都没有发生！";
					$itm = $itmk = $itmsk = '';
					$itme = $itms = 0;
				} elseif ($slv_dice < 16) {
					$rp = $rp - 10*$slv_dice;
					$log .= "你感觉身体稍微轻了一点点。<br>";
					$itm = $itmk = $itmsk = '';
					$itme = $itms = 0;
				} elseif ($slv_dice < 20) {
					$rp = 0 ;
					$log .= "你头晕脑胀地躺到了地上，<br>感觉整个人都被救济了。<br>你努力着站了起来。<br>";
					$wp = $wk = $wg = $wc = $wd = $wf = 100;
					$itm = $itmk = $itmsk = '';
					$itme = $itms = 0;
				} else {
					$log .= '你头晕脑胀地躺到了地上，<br>感觉整个人都被救济了。<br>';
					$log .= '然后你失去了意识。<br>';
					$state = 35;
					\player\update_sdata(); $sdata['sourceless'] = 1; $sdata['attackwith'] = '';
					\player\kill($sdata,$sdata);
					\player\player_save($sdata);
					\player\load_playerdata($sdata);
				}
				return;
			} elseif ($itm == '水果刀') {
				$flag = false;
				
				for($i = 1; $i <= 6; $i ++) {
					foreach(Array('香蕉','苹果','西瓜') as $fruit){
						
						if ( strpos ( ${'itm' . $i} , $fruit ) !== false && strpos ( ${'itm' . $i} , '皮' ) === false && (strpos ( ${'itmk' . $i} , 'H' ) === 0 || strpos ( ${'itmk' . $i} , 'P' ) === 0 )) {
							if($wk >= 120){
								$log .= "练过刀就是好啊。你娴熟地削着果皮。<br><span class=\"yellow\">${'itm'.$i}</span>变成了<span class=\"yellow\">★残骸★</span>！<br>咦为什么会出来这种东西？算了还是不要吐槽了。<br>";
								${'itm' . $i} = '★残骸★';
								${'itme' . $i} *= rand(2,4);
								${'itms' . $i} *= rand(3,5);
								$flag = true;
								$wk++;
							}else{
								$log .= "想削皮吃<span class=\"yellow\">${'itm'.$i}</span>，没想到削完发现只剩下一堆果皮……<br>手太笨拙了啊。<br>";
								${'itm' . $i} = str_replace($fruit, $fruit.'皮',${'itm' . $i} );
								${'itmk' . $i} = 'TN';
								${'itms' . $i} *= rand(2,4);
								$flag = true;
								$wk++;
							}
							break;
						}
					}
					if($flag == true) {break;};
				}
				if (! $flag) {
					$log .= '包裹里没有水果。<br>';
				} else {
					$dice = rand(1,5);
					if($dice==1){
						$log .= "<span class=\"red\">$itm</span>变钝了，无法再使用了。<br>";
						$itm = $itmk = $itmsk = '';
						$itme = $itms = 0;
					}
				}
				return;
			} elseif(strpos($itm,'RP回复设备')!==false){
				$rp = 0;
				$log .= "你使用了<span class=\"yellow\">$itm</span>。你的RP归零了。<br>";
				return;
			} elseif(strpos($itm,'测试用阻塞设备')!==false){
				sleep(10);
				$log .= "刚才那是什么，是卡了么？<br>";
				$hp = 1;
				return;
			} elseif('『我是说在座的各位都是垃圾』' === $itm){
				$mhpdown = 100;
				if($mhp <= $mhpdown){
					$log .= '一个声音传来：<span class="yellow">“wslnm，没血你装什么逼？！”</span><br>';
				}elseif($now - $starttime > 300){//开局5分钟之内吃才有用
					$log .= '你一边拉屎，一边看着外边满地乱滚的无名沙包，忽然决定给自己增加一点挑战。不过你胯下的翔似乎已经凉了。<br>';
				}else{
					$mhp -= $mhpdown;
					if($hp > $mhp) $hp = $mhp;
					$log .= '你一边拉屎，一边看着外边满地乱滚的无名沙包，忽然决定给自己增加一点挑战。于是你抓起自己胯下的翔，大口地吃了下去。<br><span class="red">你自扣了100点生命上限！</span><br>';
					if(!$club) {
						$log .= '你突然想起一件很重要的事情：<span class="red">老子还没选称号呢？</span>不过似乎你不用担心了，因为<span class="yellow">你刚才吃下的翔化为了你的力量！</span><br>';
						\clubbase\club_acquire(97);
					}
					\sys\addnews ( 0, 'debuffself', $name);
					\sys\addchat(6, "{$name}一边大口吃翔一边说道：“满场沙包，不足为惧。且看爷吃了这百斤翔，再来包你们爽！”");
				}
				return;
			}
		}
		$chprocess($theitem);
	}
	
	function divining(){
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('logger','player'));
		
		$dice = rand(0,99);
		if($dice < 20) {
			$up = 5;
			list($uphp,$upatt,$updef) = explode(',',divining1($up));
			$log .= "是大吉！要有什么好事发生了！<BR><span class=\"yellow b\">【命】+$uphp 【攻】+$upatt 【防】+$updef</span><BR>";
		} elseif($dice < 40) {
			$up = 3;
			list($uphp,$upatt,$updef) = explode(',',divining1($up));
			$log .= "中吉吗？感觉还不错！<BR><span class=\"yellow b\">【命】+$uphp 【攻】+$upatt 【防】+$updef</span><BR>";
		} elseif($dice < 60) {
			$up = 1;
			list($uphp,$upatt,$updef) = explode(',',divining1($up));
			$log .= "小吉吗？有跟无也没有什么分别。<BR><span class=\"yellow b\">【命】+$uphp 【攻】+$upatt 【防】+$updef</span><BR>";
		} elseif($dice < 80) {
			$up = 1;
			list($uphp,$upatt,$updef) = explode(',',divining2($up));
			$log .= "凶，真是不吉利。<BR><span class=\"red b\">【命】-$uphp 【攻】-$upatt 【防】-$updef</span><BR>";
		} else {
			$up = 3;
			list($uphp,$upatt,$updef) = explode(',',divining2($up));
			$log .= "大凶？总觉得有什么可怕的事快要发生了<BR><span class=\"red b\">【命】-$uphp 【攻】-$upatt 【防】-$updef</span><BR>";
		}
		return;
	}

	function divining1($u) {
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('player'));
		
		$uphp = rand(0,$u);
		$upatt = rand(0,$u);
		$updef = rand(0,$u);
		
		$hp+=$uphp;
		$mhp+=$uphp;
		$att+=$upatt;
		$def+=$updef;

		return "$uphp,$upatt,$updef";

	}

	function divining2($u) {
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('player'));
		
		$uphp = rand(0,$u);
		$upatt = rand(0,$u);
		$updef = rand(0,$u);
		
		$hp-=$uphp;
		$mhp-=$uphp;
		$att-=$upatt;
		$def-=$updef;

		return "$uphp,$upatt,$updef";

	}

	function deathnote($itmd=0,$dnname='',$dndeath='',$dngender='m',$dnicon=1) {
		
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('sys','logger','player'));
		
		$dn = & ${'itm'.$itmd};
		$dnk = & ${'itmk'.$itmd};
		$dne = & ${'itme'.$itmd};
		$dns = & ${'itms'.$itmd};
		$dnsk = & ${'itmsk'.$itmd};

		$mode = 'command';

		if($dn != '■DeathNote■'){
			$log .= '道具使用错误！<br>';
			return;
		} elseif($dns <= 0) {
			$dn = $dnk = $dnsk = '';
			$dne = $dns = 0;
			$log .= '道具不存在！<br>';
			return;
		}

		if(!$dnname){return;}
		if($dnname == $cuser){
			$log .= "你不能自杀。<br>";
			return;
		}
		$dn_ignore_words = deathnote_process($dnname,$dndeath,$dngender,$dnicon);
		$dns--;
		if($dns<=0){
			if(!$dn_ignore_words) $log .= '■DeathNote■突然燃烧起来，转瞬间化成了灰烬。<br>';
			$dn = $dnk = $dnsk = '';
			$dne = $dns = 0;
		}
		return;
	}
	
	function deathnote_process($dnname='',$dndeath='',$dngender='m',$dnicon=1){
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('sys','logger','player'));
		if(!$dndeath){$dndeath = '心脏麻痹';}
		$log .= "你将<span class=\"yellow b\">$dnname</span>的名字写在了■DeathNote■上。";
		$result = $db->query("SELECT * FROM {$tablepre}players WHERE name='$dnname' AND type = 0 AND hp > 0");
		if(!$db->num_rows($result)) { 
			$log .= "但是什么都没有发生。<br>哪里出错了？<br>"; 
		} else {
			$edata = \player\fetch_playerdata($dnname);
			if(($dngender != $edata['gd'])||($dnicon != $edata['icon'])) {
				$log .= "但是什么都没有发生。<br>哪里出错了？<br>"; 
			} else {
				$log .= "<br><span class=\"yellow b\">$dnname</span>被你杀死了。";
				$edata['state'] = 28; $sdata['attackwith']=$dndeath;
				\player\update_sdata(); 
				\player\kill($sdata,$edata);
				\player\player_save($edata);
				\player\player_save($sdata);
				\player\load_playerdata($sdata);
				//$killnum++;
			}
		}
		return;
	}
	
	function act()
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		
		eval(import_module('sys','player','input','logger'));
		
		if($mode == 'deathnote') {
			if($dnname){
				deathnote($item,$dnname,$dndeath,$dngender,$dnicon);
			} else {
				$log .= '嗯，暂时还不想杀人。<br>你合上了■DeathNote■。<br>';
				$mode = 'command';
			}
			return;
		}
		$chprocess();
	}
	
	function parse_news($nid, $news, $hour, $min, $sec, $a, $b, $c, $d, $e, $exarr = array())	
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		eval(import_module('sys','player'));
		
		if(isset($exarr['dword'])) $e0 = $exarr['dword'];
		
		if($news == 'adminitem') 
			return "<li id=\"nid$nid\">{$hour}时{$min}分{$sec}秒，<span class=\"red\">{$a}使用了{$b}，变成了一条权限狗！（管理员{$a}宣告其正在进行测试。）</span></li>";	
		elseif($news == 'death28') 
			return "<li id=\"nid$nid\">{$hour}时{$min}分{$sec}秒，<span class=\"yellow\">$a</span>因<span class=\"yellow\">$d</span>意外身亡{$e0}</li>";
		elseif($news == 'death30') 
			return "<li id=\"nid$nid\">{$hour}时{$min}分{$sec}秒，<span class=\"yellow\">$a</span>因误触伪装成核弹按钮的蛋疼机关被炸死{$e0}</li>";
		elseif($news == 'death38')
			return "<li id=\"nid$nid\">{$hour}时{$min}分{$sec}秒，<span class=\"yellow\">$a</span>因为敌意过剩，被虚拟意识救♀济！{$e0}</li>";
		elseif($news == 'debuffself')
			return "<li id=\"nid$nid\">{$hour}时{$min}分{$sec}秒，<span class=\"yellow\">{$a}认为在座的各位都是垃圾，并大口吃下一百斤翔以表达他的不屑！（{$a}自扣了100点生命上限）</span></li>";
			
		return $chprocess($nid, $news, $hour, $min, $sec, $a, $b, $c, $d, $e, $exarr);
	}
}

?>
