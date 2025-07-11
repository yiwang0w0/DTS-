<?php

define('CURSCRIPT', 'help');
define('IN_GAME', true);
defined('GAME_ROOT') || define('GAME_ROOT', dirname(__FILE__).'/');
require GAME_ROOT.'./include/global.func.php';
$url = url_dir().'command.php';
$context = array('page'=>'command_help');
foreach($_POST as $pkey => $pval){
	$context[$pkey] = $pval;
}
$cookies = array();
foreach($_COOKIE as $ckey => $cval){
	if(strpos($ckey,'user')!==false || strpos($ckey,'pass')!==false) $cookies[$ckey] = $cval;
}
$helpinfo = curl_post($url, $context, $cookies);
echo $helpinfo;

/* End of file help.php */
/* Location: /help.php */

/* 
define('CURSCRIPT', 'help');
define('IN_HELP', TRUE);
$___IN_HELP = 1;

require './include/common.inc.php';
eval(import_module('pose','tactic','itemmain','npc'));

include_once GAME_ROOT . './include/itemplace.func.php';


$mixfile = GAME_ROOT.'./include/modules/base/itemmix/itemmix/config/itemmix.config.php';
include $mixfile;
$writefile = GAME_ROOT.TPLDIR.'/tmp_mixhelp.htm';
//ͨ���ϳɱ��Զ�����
if(!file_exists($writefile) || filemtime($mixfile) > filemtime($writefile)){
	if(empty($iplacedata)) init_item_place();//��ʼ��itemplace����
	$mixitem = array();
	foreach($mixinfo as $mix){
		if($mix['class'] !== 'hidden'){
			$mixitmk = \itemmain\parse_itmk_words($mix['result'][1]);
			$mixitmsk = \itemmain\parse_itmsk_words($mix['result'][4]);
			if ($mixitmsk == '--') $mixitmsk = '';
			$mixitem[$mix['class']][] = array('stuff' => $mix['stuff'], 'result' => array($mix['result'][0],$mixitmk,$mix['result'][2],$mix['result'][3],$mixitmsk));
		}
		
	}
	
	$mixclass = array(
		'wp'=> array('Źϵ����','yellow'),
		'wk'=> array('նϵ����','yellow'),
		'wg'=> array('��ϵ����','yellow'),
		'wc'=> array('Ͷϵ����','yellow'),
		'wd'=> array('��ϵ����','yellow'),
		'wf'=> array('��ϵ����','yellow'),
		'w' => array('��������','yellow'),
		'd' => array('����','yellow'),
		'h' => array('����Ʒ','lime'),
		'pokemon'=> array('С��ϵ����','yellow'),
		'ocg'=> array('��Ϸ��ϵ����','clan'),
		'key'=> array('KEYϵ����','lime'),
		'cube'=> array('����ϵ����','yellow'),
		'item'=> array('��������','yellow'),
		);
	$mixhelpinfo = '';
	foreach($mixitem as $class => $list){
		$classname = $mixclass[$class][0];
		$classcolor = $mixclass[$class][1];
		$mixhelpinfo .= "<p><span class=\"$classcolor\">{$classname}�ϳɱ�</span>��</p>\n";
		$mixhelpinfo .= <<<'MIXITEM_HELP_TABLE_TITLE'
<table>
	<tr>
		<td class="b1" height=20px><span>�ز�1</span></td>
		<td class="b1"><span>�ز�2</span></td>
		<td class="b1"><span>�ز�3</span></td>
		<td class="b1"><span>�ز�4</span></td>
		<td class="b1"><span>�ز�5</span></td>
		<td class="b1"><span>�ز�6</span></td>
		<td class="b1"></td>
		<td class="b1"><span>�ϳɽ��</span></td>
		<td class="b1"><span>��;</span></td>
	</tr>
MIXITEM_HELP_TABLE_TITLE;
		foreach($list as $val){
			if(!empty($val['result'][4])){$itmskword = '/'.$val['result'][4];}
			else{$itmskword = '';}
			for($i = 2;$i < 6; $i++) {
				if(!isset($val['stuff'][$i])){$val['stuff'][$i] = '-';}
			}
			$mixhelpinfo .= 
			"<tr>
				<td class=\"b3\" height='19px' title='" . get_item_place_single ( $val ['stuff'] [0] ) . "'><span>{$val['stuff'][0]}</span></td>";
			for($i = 1; $i < 6; $i ++) {
				$mixhelpinfo .= "<td class=\"b3\"";
				if ($val ['stuff'] [$i] != '-') {
					$mixhelpinfo .= "title='" . get_item_place_single ( $val ['stuff'] [$i] ) . "'";
				}
				$mixhelpinfo .= "><span>{$val['stuff'][$i]}</span></td>";
			}
			$mixhelpinfo .= "<td class=\"b3\">��</td>
				<td class=\"b3\"><span>{$val['result'][0]}</span></td>
				<td class=\"b3\"><span>{$val['result'][1]}/{$val['result'][2]}/{$val['result'][3]}{$itmskword}</span></td>
			</tr>
			";
		}
		$mixhelpinfo .= "</table>\n";
	}
	
	writeover($writefile,$mixhelpinfo);
}

//ͬ���ϳɱ��Զ�����
$syncfile = GAME_ROOT.'./include/modules/base/itemmix/itemmix_sync/config/sync.config.php';
$writefile = GAME_ROOT.TPLDIR.'/tmp_mixhelp_sync.htm';
if(!file_exists($writefile) || filemtime($syncfile) > filemtime($writefile)){
	if(empty($iplacedata)) init_item_place();//��ʼ��itemplace����
	$syncinfo=openfile($syncfile);
	$syncitem = array();
	$syncitem_special = array();
	foreach($syncinfo as $sync){
		$sync_arr=array_combine(array('itm', 'itmk', 'itme', 'itms', 'itmsk', 'star', 'special'), array_slice(explode(',',$sync), 0, 7));
		$sync_arr['itmk'] = \itemmain\parse_itmk_words($sync_arr['itmk']);
		$sync_arr['itmsk'] = \itemmain\parse_itmsk_words($sync_arr['itmsk']);
		if(!empty($sync_arr['special'])){
			$sync_arr['special'] = explode('+',$sync_arr['special']);
			$syncitem_special[] = $sync_arr;
		}else{
			$syncitem[] = $sync_arr;
		}
	}
	$synchelpinfo = '<p><span class="yellow">ͨ��ͬ���ϳɱ�</span>��</p>';
	$synchelpinfo .= <<<'SYNC_HELP_INFO_DOC'
<table>
	<tr>
		<td class="b1" height=20px><span>ͬ������</span></td>
		<td class="b1"><span>��;</span></td>
	</tr>
SYNC_HELP_INFO_DOC;
	$synchelpinfo_special = '<p><span class="lime">����ͬ���ϳɱ�</span>��</p>';
	$synchelpinfo_special .= <<<'SYNC_HELP_INFO_SPEC_DOC'
<table>
	<tr>
		<td class="b1" height=20px><span>ͬ���ز�һ</span></td>
		<td class="b1"><span>ͬ���زĶ�</span></td>
		<td class="b1"><span>ͬ���ز���</span></td>
		<td class="b1"><span>ͬ���ز���</span></td>
		<td class="b1"><span>ͬ���ز���</span></td>
		<td class="b1"></td>
		<td class="b1"><span>ͬ������</span></td>
		<td class="b1"><span>��;</span></td>
	</tr>
SYNC_HELP_INFO_SPEC_DOC;
	
	foreach ($syncitem as $sval){
		if(!empty($sval['itmsk'])){$itmskwords = '/'.$sval['itmsk'];}
		else{$itmskwords = '';}
		$synchelpinfo .= <<<SYNC_HELP_INFO_DOC_TR
	<tr>
		<td class='b3' height='20px'><span>{$sval['itm']}</span></td>
		<td class='b3'><span>{$sval['itmk']}/{$sval['itme']}/{$sval['itms']}{$itmskwords}</span></td>
	</tr>
SYNC_HELP_INFO_DOC_TR;
	}
	$synchelpinfo .= '</table>';
	
	foreach ($syncitem_special as $sval){
		if(!empty($sval['itmsk'])){$itmskwords = '/'.$sval['itmsk'];}
		else{$itmskwords = '';}
		$synchelpinfo_special .= '<tr>';
		for($i = 0; $i <= 4; $i ++) {
			$synchelpinfo_special .= '<td class="b3"';
			if (isset($sval['special'][$i])) $synchelpinfo_special .= "title='" . get_item_place_single ( $sval['special'][$i] ) . "'";
			$synchelpinfo_special .= isset($sval['special'][$i]) ? "><span>{$sval['special'][$i]}</span></td>" : "><span>-</span></td>";
		}
		$synchelpinfo_special .=<<<SYNC_HELP_INFO_SPEC_DOC_TR
		<td class='b3'>��</td>
		<td class='b3'><span>{$sval['itm']}</span></td>
		<td class='b3'><span>{$sval['itmk']}/{$sval['itme']}/{$sval['itms']}{$itmskwords}</span></td>
	</tr>
SYNC_HELP_INFO_SPEC_DOC_TR;
	}
	$synchelpinfo_special .= '</table>';
	
	$writecont=<<<SYNC_HELP_WRITE_CONTENT
<p>�����ǿ��ܻ�õ�ͬ��������б�</p>
{$synchelpinfo}
<p>���⣬����ֻ��һ������� ��һЩͬ���������ͨ��<span class="yellow">�ض���ͬ������</span>���ܺϳɣ���Щ�ϳɽ����±����г���</p>
{$synchelpinfo_special}
<br>
SYNC_HELP_WRITE_CONTENT;
	writeover($writefile,$writecont);
}
//�����ϳɱ��Զ�����
$overlayfile = GAME_ROOT.'./include/modules/base/itemmix/itemmix_overlay/config/overlay.config.php';
$writefile = GAME_ROOT.TPLDIR.'/tmp_mixhelp_overlay.htm';
if(!file_exists($writefile) || filemtime($overlayfile) > filemtime($writefile)){
	if(empty($iplacedata)) init_item_place();//��ʼ��itemplace����
	$overlayinfo=openfile($overlayfile);
	$overlayitem = array();
	foreach($overlayinfo as $overlay){
		$overlay_arr=array_combine(array('itm', 'itmk', 'itme', 'itms', 'itmsk', 'star', 'num'), array_slice(explode(',',$overlay), 0, 7));
		$overlay_arr['itmk'] = \itemmain\parse_itmk_words($overlay_arr['itmk']);
		$overlay_arr['itmsk'] = \itemmain\parse_itmsk_words($overlay_arr['itmsk']);
		$overlayitem[] = $overlay_arr;
	}
	$overlayhelpinfo = '<p><span class="yellow">�����ϳɱ�</span>��</p>';
	$overlayhelpinfo .= <<<'OVERLAY_HELP_INFO_DOC'
<table>
	<tr>
		<td class="b1" height=20px><span>�����ز�����</span></td>
		<td class="b1"><span>��Ŀ</span></td>
		<td class="b1"><span>��������</span></td>
		<td class="b1"><span>��;</span></td>
	</tr>
OVERLAY_HELP_INFO_DOC;
	$overlay_star_words = array(
		1 => '��Ϸ��һ���ز�',
		2 => '��Ϸ�������ز�',
		3 => '��Ϸ�������ز�',
		4 => '��Ϸ�������ز�',
		5 => '��Ϸ�������ز�',
		6 => '��Ϸ�������ز�',
		7 => '��Ϸ�������ز�',
		8 => '��Ϸ�������ز�',
		9 => '��Ϸ�������ز�',
		10 => '��Ϸ��ʮ���ز�'
	);
	foreach ($overlayitem as $oval){
		if(!empty($oval['itmsk'])){$itmskwords = '/'.$oval['itmsk'];}
		else{$itmskwords = '';}
		$overlayhelpinfo .= <<<OVERLAY_HELP_INFO_DOC_TR
	<tr>
		<td class='b3' height='20px'><span>{$overlay_star_words[$oval['star']]}</span></td>
		<td class='b3'><span> �� {$oval['num']}</span></td>
		<td class='b3'><span>{$oval['itm']}</span></td>
		<td class='b3'><span>{$oval['itmk']}/{$oval['itme']}/{$oval['itms']}{$itmskwords}</span></td>
	</tr>
OVERLAY_HELP_INFO_DOC_TR;
	}
	$overlayhelpinfo .= '</table>';
	
	$writecont=<<<OVERLAY_HELP_WRITE_CONTENT
<p>�����ǿ��ܻ�õĳ���������б�</p>
{$overlayhelpinfo}
OVERLAY_HELP_WRITE_CONTENT;
	writeover($writefile,$writecont);
}

$npcfile = GAME_ROOT.'./include/modules/base/npc/config/npc.data.config.php';
$npcfile_i8 = GAME_ROOT.'./include/modules/extra/instance/instance8_proud/config/npc.data.config.php';
$npcfile_i9 = GAME_ROOT.'./include/modules/extra/instance/instance9_rush/config/npc.data.config.php';
$npcfile_ev = GAME_ROOT.'./include/modules/extra/club/skills/skill21/config/evonpc.config.php';
include $npcfile_i8;
include $npcfile_i9;
include $npcfile_ev;
$enpcinfo_show = array();
foreach ($enpcinfo as $enkey => $enval){
	$enpcinfo_show[$enkey] = array('sub' => array());
	foreach($enval as $enval2){
		$enpcinfo_show[$enkey]['sub'][] = $enval2;
	}
}
$srcfile = GAME_ROOT.TPLDIR.'/npchelp.htm';
$writefile = GAME_ROOT.TPLDIR.'/tmp_npchelp.htm';

//NPC�б��Զ�����
$need_refresh = 0;
if(!file_exists($writefile)){
	$need_refresh = 1;
}else{
	foreach(array($npcfile, $npcfile_i8, $npcfile_i9, $npcfile_ev, $srcfile ) as $fv){
		if(filemtime($fv) > filemtime($writefile)) $need_refresh = 1;
	}
}

if($need_refresh){
	$writecont = dump_template('npchelp');
	writeover($writefile,$writecont);
}

$extrahead = <<<EOT
<STYLE type=text/css>
BODY {
	FONT-SIZE: 10pt;MARGIN: 0; color:#eee; FONT-FAMILY: "Trebuchet MS","Gill Sans","Microsoft Sans Serif",sans-serif;
}
A {
	COLOR: #eee
}
A:visited {
	COLOR: #eee
}
A:active {
	color: #98fb98;text-decoration:underline
}
P{ line-height:16px
}

.subtitle2 {
	font-family: "΢���ź�"; color: #98fb98; width: 100%;font-size: 16px;font-weight:900;
}

DIV.FAQ {
	PADDING-LEFT: 1em; line-height:16px
}
DIV.FAQ DT {
	COLOR: #98fb98
}
DIV.FAQ DD {
	
}

</STYLE>
EOT;

include template ( 'help' );

?> */