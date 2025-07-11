<?php

namespace item_uv
{
	function init() 
	{
		eval(import_module('itemmain'));
		$iteminfo['VO'] = '卡片礼物';
		$iteminfo['V'] = '技能书籍';
		$iteminfo['VS'] = '技能书籍';
		$iteminfo['VV'] = '全系书籍';
		$iteminfo['VP'] = '殴系书籍';
		$iteminfo['VK'] = '斩系书籍';
		$iteminfo['VG'] = '射系书籍';
		$iteminfo['VC'] = '投系书籍';
		$iteminfo['VD'] = '爆系书籍';
		$iteminfo['VF'] = '灵系书籍';
	}
	
	function itemuse(&$theitem) 
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		
		eval(import_module('sys','player','weapon','itemmain','logger'));
		
		$itm=&$theitem['itm']; $itmk=&$theitem['itmk'];
		$itme=&$theitem['itme']; $itms=&$theitem['itms']; $itmsk=&$theitem['itmsk'];
		
		if (strpos ( $itmk, 'V' ) === 0) 
		{
			if ($itmk[1] == 'O')
				$log .= "你打开了<span class=\"red\">$itm</span>。<br>";
			else
				$log .= "你阅读了<span class=\"red\">$itm</span>。<br>";
				
			//特殊的技能书类型VS，效果是获得技能编号为itmsk的技能
			if (strpos ( substr($itmk,1), 'S' ) !== false)	//技能书
			{
				eval(import_module('clubbase'));
				$useflag = 0;
				$sk_kind = (int)$itmsk;
				if ($sk_kind<1) $sk_kind = 1;
				if (defined('MOD_SKILL'.$sk_kind) && $clubskillname[$sk_kind]!='')
				{
					if (\skillbase\skill_query($sk_kind))
					{
						$log.="你发现这本书就是昨天刚刚看过的那本，不打算继续看下去了。<br>";
					}
					else
					{
						$log.="你感觉受益匪浅。你获得了技能「<span class=\"yellow\">".$clubskillname[$sk_kind]."</span>」，请前往技能界面查看。<br>";
						\skillbase\skill_acquire($sk_kind);
						$useflag = 1;
						//\itemmain\itms_reduce($theitem);
					}
				}
				else
				{
					$log.="技能书参数错误，这应该是一个BUG，请联系管理员。<br>";
					return;
				}
			}
			
			//特殊的技能书类型VO
			//效果是这样的：（如果编号用完了请用单个字母）
			//VO/VO1: 获得编号为$itmsk的卡片
			//VO2: 获得A/B/C卡片
			//     A=10% B=35% C=55%
			//VO3: 获得S/A/B卡片
			//     S=10% A=25% B=65%
			//VO4: 获得特殊/S/A卡片 特殊=编号为$itmsk的卡片 （目前就是为那张特殊卡片服务的）
			//     特殊=15% S=20% A=65%
			//VO5: 获得S级卡片
			//VO6: 获得A级卡片
			//VO7: 获得B级卡片
			//VO8: 获得C级卡片
			//VO9: 获得B/C级卡片
			//     B=30% C=70%
			//
			if (defined('MOD_CARDBASE') && $itmk[1] == 'O')	//卡片礼物
			{
				eval(import_module('cardbase'));
				if (strlen($itmk) == 2) $cardpresent_type = '1'; else $cardpresent_type = $itmk[2];
				$itmn = $theitem['itmn'];
				$cardpresent_desc = 'N/A';
				if ($cardpresent_type == '1') $cardpresent_desc = '获得卡片“'.$cards[(int)$itmsk]['name'].'”';
				if ($cardpresent_type == '2') $cardpresent_desc = '从中有机会获得'.$card_rarity_html['A'].'/'.$card_rarity_html['B'].'/'.$card_rarity_html['C'].'级卡片';
				if ($cardpresent_type == '3') $cardpresent_desc = '从中有机会获得'.$card_rarity_html['S'].'/'.$card_rarity_html['A'].'/'.$card_rarity_html['B'].'级卡片';
				if ($cardpresent_type == '4') $cardpresent_desc = '从中有机会获得特殊卡片“<span class="yellow">'.$cards[(int)$itmsk]['name'].'</span>”，或一张'.$card_rarity_html['S'].'级或'.$card_rarity_html['A'].'级的卡片';
				if ($cardpresent_type == '5') $cardpresent_desc = '从中可以获得一张'.$card_rarity_html['S'].'级卡片';
				if ($cardpresent_type == '6') $cardpresent_desc = '从中可以获得一张'.$card_rarity_html['A'].'级卡片';
				if ($cardpresent_type == '7') $cardpresent_desc = '从中可以获得一张'.$card_rarity_html['B'].'级卡片';
				if ($cardpresent_type == '8') $cardpresent_desc = '从中可以获得一张'.$card_rarity_html['C'].'级卡片';
				if ($cardpresent_type == '9') $cardpresent_desc = '从中有机会获得'.$card_rarity_html['B'].'级或'.$card_rarity_html['C'].'级卡片';
				
				if ($cardpresent_desc == 'N/A')
				{
					$log.='物品代码配置错误，请联系管理员。<br>';
					return;
				}
				
				if ($itm == '博丽神社的参拜券')
				{
					eval(import_module('sys'));
					if ($now - $starttime >= 1200)
					{
						$log.='<span class="yellow">博丽神社今天已经关门啦，下次请早点来吧。（这个道具必须在开局20分钟内使用）<br></span>';
						return;
					}
				}
				
				eval(import_module('input'));
				if ($subcmd == 'flipcard')
				{
					$get_card_id = 0;
					if ($cardpresent_type == '1')
					{
						$get_card_id = (int)$itmsk;
					}
					else if ($cardpresent_type == '4' && rand(1,100)<=15)
					{
						$get_card_id = (int)$itmsk;
					}
					else 
					{
						if ($cardpresent_type == '2') $cardraw_pr = Array('S'=>0, 'A'=>10, 'B'=>35, 'C'=>55);
						if ($cardpresent_type == '3') $cardraw_pr = Array('S'=>10, 'A'=>25, 'B'=>65, 'C'=>0);
						if ($cardpresent_type == '4') $cardraw_pr = Array('S'=>24, 'A'=>76, 'B'=>0, 'C'=>0);
						if ($cardpresent_type == '5') $cardraw_pr = Array('S'=>100, 'A'=>0, 'B'=>0, 'C'=>0);
						if ($cardpresent_type == '6') $cardraw_pr = Array('S'=>0, 'A'=>100, 'B'=>0, 'C'=>0);
						if ($cardpresent_type == '7') $cardraw_pr = Array('S'=>0, 'A'=>0, 'B'=>100, 'C'=>0);
						if ($cardpresent_type == '8') $cardraw_pr = Array('S'=>0, 'A'=>0, 'B'=>0, 'C'=>100);
						if ($cardpresent_type == '9') $cardraw_pr = Array('S'=>0, 'A'=>0, 'B'=>30, 'C'=>70);
						$dice=rand(1,100); $kind='';
						foreach ($cardraw_pr as $key => $value)
						{
							if ($dice<=$value)
							{
								$kind=$key; break;
							}
							else
							{
								$dice-=$value;
							}
						}
						if ($kind=='')
						{
							$log.='物品代码配置错误，请联系管理员。<br>';
							return;
						}
						$get_card_id = $cardindex[$kind][rand(0,count($cardindex[$kind])-1)];
					}
					
					if ($get_card_id==0)
					{
						$log.='物品代码配置错误，请联系管理员。<br>';
						return;
					}
					
					$is_new = '';
					//$ext = '来自'.($room_prefix ? '房间' : '').'第'.$gamenum.'局的'.$itm.'。'; 
					//小房间的编号未必是历史记录的编号，因此小房间就不显示房间号了
					if($room_prefix) {
						$ext = '来自'.$gtinfo[$gametype].'的'.$itm.'。';
					}else{
						$ext = '来自第'.$gamenum.'局的'.$itm.'。';
					}
					if($cards[$get_card_id]['rare'] == 'A') $ext.='运气不错！';
					elseif($cards[$get_card_id]['rare'] == 'S') $ext.='一是欧洲人吧！';
					if ((\cardbase\get_card_message($get_card_id,$ext))==1) $is_new = "<span class=\"L5\">NEW!</span>";;
					ob_clean();
					include template('MOD_CARDBASE_CARDFLIP_RESULT');
					$log .= ob_get_contents();
					ob_clean();
					
					$log.='<span class="yellow">你获得了卡片「'.$cards[$get_card_id]['name'].'」！请前往“站内邮件”查收。</span><br>';
					
					addnews ( 0, 'VOgetcard', $name, $itm, $cards[$get_card_id]['name'] );
					
					\itemmain\itms_reduce($theitem);
					
					return;
				}
				else
				{
					ob_clean();
					include template('MOD_CARDBASE_CARDFLIP_BACK');
					$log .= ob_get_contents();
					ob_clean();
					return;
				}
			}
			
			//下面是普通的技能书处理（效果是加某个系的熟练）
			$skill_minimum = 100;
			$skill_limit = 300;
			
			$dice = rand ( - 5, 5 );
			$vefct = NULL;
			if (strpos ( substr($itmk,1), 'V' ) !== false) {//全系技能书
				$skcnt = 0; $ws_sum = 0;
				foreach (array_unique(array_values($skillinfo)) as $key)
				{
					$skcnt++;
					$ws_sum += $$key;
				}
				if ($ws_sum < $skill_minimum * $skcnt) {
					$vefct = $itme;
				} elseif ($ws_sum < $skill_limit * $skcnt) {
					$vefct = round ( $itme * (1 - ($ws_sum - $skill_minimum * $skcnt) / ($skill_limit * $skcnt - $skill_minimum * $skcnt)) );
				} else {
					$vefct = 0;
				}
				if ($vefct < 5) {
					if ($vefct < $dice) {
						$vefct = - $dice;
					}
				}
				foreach (array_unique(array_values($skillinfo)) as $key)
				{
					$$key+=$vefct;
				}
				$wsname = "全系熟练度";
				$useflag = 1;
			} elseif (strpos ( substr($itmk,1), 'P' ) !== false) {
				if ($wp < $skill_minimum) {
					$vefct = $itme;
				} elseif ($wp < $skill_limit) {
					$vefct = round ( $itme * (1 - ($wp - $skill_minimum) / ($skill_limit - $skill_minimum)) );
				} else {
					$vefct = 0;
				}
				if ($vefct < 5) {
					if ($vefct < $dice) {
						$vefct = - $dice;
					}
				}
				$wp += $vefct; //$itme;
				$wsname = "斗殴熟练度";
				$useflag = 1;
			} elseif (strpos ( substr($itmk,1), 'K' ) !== false) {
				if ($wk < $skill_minimum) {
					$vefct = $itme;
				} elseif ($wk < $skill_limit) {
					$vefct = round ( $itme * (1 - ($wk - $skill_minimum) / ($skill_limit - $skill_minimum)) );
				} else {
					$vefct = 0;
				}
				if ($vefct < 5) {
					if ($vefct < $dice) {
						$vefct = - $dice;
					}
				}
				$wk += $vefct;
				$wsname = "斩刺熟练度";
				$useflag = 1;
			} elseif (strpos ( substr($itmk,1), 'G' ) !== false) {
				if ($wg < $skill_minimum) {
					$vefct = $itme;
				} elseif ($wg < $skill_limit) {
					$vefct = round ( $itme * (1 - ($wg - $skill_minimum) / ($skill_limit - $skill_minimum)) );
				} else {
					$vefct = 0;
				}
				if ($vefct < 5) {
					if ($vefct < $dice) {
						$vefct = - $dice;
					}
				}
				$wg += $vefct;
				$wsname = "射击熟练度";
				$useflag = 1;
			} elseif (strpos ( substr($itmk,1), 'C' ) !== false) {
				if ($wc < $skill_minimum) {
					$vefct = $itme;
				} elseif ($wc < $skill_limit) {
					$vefct = round ( $itme * (1 - ($wc - $skill_minimum) / ($skill_limit - $skill_minimum)) );
				} else {
					$vefct = 0;
				}
				if ($vefct < 5) {
					if ($vefct < $dice) {
						$vefct = - $dice;
					}
				}
				$wc += $vefct;
				$wsname = "投掷熟练度";
				$useflag = 1;
			} elseif (strpos ( substr($itmk,1), 'D' ) !== false) {
				if ($wd < $skill_minimum) {
					$vefct = $itme;
				} elseif ($wd < $skill_limit) {
					$vefct = round ( $itme * (1 - ($wd - $skill_minimum) / ($skill_limit - $skill_minimum)) );
				} else {
					$vefct = 0;
				}
				if ($vefct < 5) {
					if ($vefct < $dice) {
						$vefct = - $dice;
					}
				}
				$wd += $vefct;
				$wsname = "引爆熟练度";
				$useflag = 1;
			} elseif (strpos ( substr($itmk,1), 'F' ) !== false) {
				if ($wf < $skill_minimum) {
					$vefct = $itme;
				} elseif ($wf < $skill_limit) {
					$vefct = round ( $itme * (1 - ($wf - $skill_minimum) / ($skill_limit - $skill_minimum)) );
				} else {
					$vefct = 0;
				}
				if ($vefct < 5) {
					if ($vefct < $dice) {
						$vefct = - $dice;
					}
				}
				$wf += $vefct;
				$wsname = "灵击熟练度";
				$useflag = 1;
			}
			if(NULL!==$vefct) {
				if ($vefct > 0) {
					$log .= "嗯，有所收获。<br>你的{$wsname}提高了<span class=\"yellow\">$vefct</span>点！<br>";
				} elseif ($vefct == 0) {
					$log .= "对你来说书里的内容过于简单了。<br>你的熟练度没有任何提升。<br>";
				} else {
					$vefct = - $vefct;
					$log .= "对你来说书里的内容过于简单了。<br>而且由于盲目相信书上的知识，你反而被编写者的纰漏所误导了！<br>你的{$wsname}下降了<span class=\"red\">$vefct</span>点！<br>";
				}
			}
			if($useflag) \itemmain\itms_reduce($theitem);
			return;
		}
		$chprocess($theitem);
	}
	
	function parse_news($nid, $news, $hour, $min, $sec, $a, $b, $c, $d, $e, $exarr = array())
	{
		if (eval(__MAGIC__)) return $___RET_VALUE;
		
		eval(import_module('sys','player'));
		
		if($news == 'VOgetcard') 
			return "<li id=\"nid$nid\">{$hour}时{$min}分{$sec}秒，<span class=\"yellow\">{$a}打开了{$b}，获得了卡片“{$c}”！</span></li>";
		
		return $chprocess($nid, $news, $hour, $min, $sec, $a, $b, $c, $d, $e, $exarr);
	}
}

?>
