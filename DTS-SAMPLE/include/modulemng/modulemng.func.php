<?php

function writeover_array($file,$arr)
{
	$s=''; $in=sizeof($arr);
	for ($i=0; $i<$in; $i++) if (strlen($arr[$i])>4) $s.=$arr[$i]."\n";
	$s=str_replace("\n\n","\n",$s);
	writeover($file,$s);
}

function copy_without_comments($src, $objfile){
	if(pathinfo($src,PATHINFO_EXTENSION)!='php'){
		copy($src,$objfile);
	}else{
		//去除注释
		$content = strip_comments(file_get_contents($src));
		writeover($objfile, $content);
		unset($content);
	}
}

//用NULL作为默认参数来执行任意函数
//php 7.1以上版本，函数参数不足时会Error，因此用反射函数获得参数个数，再用call_user_func_array()回调回去
function reflection_run_code($funcname){
	$code = <<<'REFLECTION_CODE'
	$reflect = new ReflectionFunction(__FUNCNAME__);
	$default_pars = $reflect->getParameters();
	$testing_pars = array();
	$null = NULL;
	foreach ($default_pars as $pv)
	{
		if( $pv->isPassedByReference()) $testing_pars[] = &$null;
		else  $testing_pars[] = NULL;
	}
	$__RET=call_user_func_array(__FUNCNAME__,$testing_pars);
	unset($reflect);
REFLECTION_CODE;
	return str_replace('__FUNCNAME__', "'".$funcname."'", $code);
}

function module_validity_check($file)
{
	$log='';
	if (!file_exists($file))
	{
		$log.="<span><font color=\"red\">模块列表文件不存在。</font></span><br>";
		return $log;
	}
	$content=openfile($file);
	$in=sizeof($content); $n=0; $m=0;
	for ($i=0; $i<$in; $i++)
	{
		list($modname,$modpath,$inuse) = explode(',',$content[$i]);
		if ($inuse==1)
		{
			if (isset($modnamelist[$modname]))
			{
				$log.="<span><font color=\"red\">模块{$modname}被多次加载，路径分别为{$modpath}和{$modnamelist[$modname]}。</font></span><br>";
				return $log;
			}
			$modnamelist[$modname]=$modpath;
			$n++; $modn[$n]=$modname; $modp[$n]=$modpath;
		}
		else
		{
			$m++; $bmodn[$m]=$modname; $bmodp[$m]=$modpath;
		}
	}
	
	global $___TEMP_DRY_RUN, $___TEMP_DRY_RUN_COUNTER;
	$___TEMP_DRY_RUN=1; 
	$mod_exist['root']=1; $dependency=Array(); $dependency_optional=Array(); $sup_func_list=Array();
	for ($i=1; $i<=$n; $i++) $dependency[$i]=Array();
	for ($i=1; $i<=$n; $i++) $dependency_optional[$i]=Array();
	for ($i=1; $i<=$n; $i++) $sup_func_list[$i]=Array();
		
	for ($i=1; $i<=$n; $i++)
	{
		$modname=$modn[$i]; $modpath=$modp[$i];
		if (!file_exists(GAME_ROOT.'./include/modules/'.$modpath.'module.inc.php'))
		{
			$log.="<span><font color=\"red\">模块{$modname}没有提供module.inc.php。</font></span><br>";
			return $log;
		}
		global $faillog;
		$faillog="<span><font color=\"red\">遇到了一个未知错误。这可能是由于模块{$modname}包含语法错误造成的。</font></span><br>";
		require GAME_ROOT.'./include/modules/'.$modpath.'module.inc.php';
		$faillog='';
		$mod_exist[$modname]=$modpath;
		if (isset($conflict_list[$modname]))
		{
			$log.="<span><font color=\"red\">模块{$modname}与模块{$conflict_list[$modname]}冲突。</font></span><br>";
			return $log;
		}
		foreach(explode(' ',$___MODULE_conflict) as $key) if ($key!='')
		{
			if (isset($mod_exist[$key]))
			{
				$log.="<span><font color=\"red\">模块{$modname}与模块{$key}冲突。</font></span><br>";
				return $log;
			}
			$conflict_list[$key]=$modname;
		}
		foreach(explode(' ',$___MODULE_dependency) as $key) if ($key!='')
		{
			array_push($dependency[$i],$key);
		}
		foreach(explode(' ',$___MODULE_dependency_optional) as $key) if ($key!='')
		{
			array_push($dependency_optional[$i],$key);
		}
		foreach(explode(' ',$___MODULE_codelist) as $key) if ($key!='')
			if (!file_exists(GAME_ROOT.'./include/modules/'.$modpath.$key))
			{
				$log.="<span><font color=\"red\">模块{$modname}声明包含代码文件{$key}，但这个文件并不存在。</font></span><br>";
				return $log;
			}
		foreach(explode(' ',$___MODULE_templatelist) as $key) if ($key!='')
			if (!file_exists(GAME_ROOT.'./include/modules/'.$modpath.$key.'.htm'))
			{
				$log.="<span><font color=\"red\">模块{$modname}声明包含模板文件{$key}.htm，但这个文件并不存在。</font></span><br>";
				return $log;
			}
		$a=get_defined_functions()['user']; $b=Array();
		foreach ($a as $key) if ($key!='')
			if (strtoupper(substr($key,0,strlen($modname)+1))==strtoupper($modname.'\\'))
				array_push($b,substr($key,strlen($modname)+1));
				
		$init_exist=0;
		
		foreach ($b as $key) if ($key!='')
			if ($key!='init')
			{
				array_push($sup_func_list[$i],$key);
			}
			else  $init_exist=1;
		
		if (!$init_exist)
		{
			$log.="<span><font color=\"red\">模块{$modname}没有包含init()函数。</font></span><br>";
			return $log;
		}
		
		foreach ($b as $key) if ($key!='')
			if ($key!='init')
			{
				global $faillog;
				$faillog="<span><font color=\"red\">模块{$modname}的函数{$key}的开头没有写上".
					'<pre>if (eval(__MAGIC__)) return $___RET_VALUE;</pre>这句话。</font></span><br>';
				$expect=$___TEMP_DRY_RUN_COUNTER+1;
				global $___TEMP_FUNCNAME_EXPECT; $___TEMP_FUNCNAME_EXPECT = $modname.'\\'.$key;
				$__RET='';
				//php 7.1以上版本，函数参数不足时会Error，因此不能直接空参数执行
				eval(reflection_run_code($modname.'\\'.$key));
				$faillog='';
				if ($__RET!='23333333' || $___TEMP_DRY_RUN_COUNTER!=$expect)
				{
					$log.="<span><font color=\"red\">模块{$modname}的函数{$key}的开头没有写上".
					'<pre>if (eval(__MAGIC__)) return $___RET_VALUE;</pre>这句话。</font></span><br>';
					return $log;
				}
			}
	}
	
	$g=Array();
	for ($i=1; $i<=$n; $i++) $g[$i]=Array();
	for ($i=1; $i<=$n; $i++)
	{
		foreach ($dependency[$i] as $key)
		{
			$flag=0;
			for ($j=1; $j<=$n; $j++)
			{
				if ($modn[$j]==$key) { $flag=1; array_push($g[$j],$i); $rd[$i]++; break; }
			}
			if (!$flag)
			{
				$log.="<span><font color=\"red\">模块{$modn[$i]}的依赖{$key}没有满足。</font></span><br>";
				return $log;
			}
		}
		foreach ($dependency_optional[$i] as $key)
		{
			$flag=0;
			for ($j=1; $j<=$n; $j++)
			{
				if ($modn[$j]==$key) { $flag=1; array_push($g[$j],$i); $rd[$i]++; break; }
			}
		}
	}
	
	$head=1; $tail=1;
	for ($i=1; $i<=$n; $i++) 
		if ($rd[$i]==0)
		{
			$q[$tail]=$i; $tail++;
		}
		
	while ($head<$tail)
	{
		$x=$q[$head]; $head++;
		foreach ($g[$x] as $y)
		{
			$rd[$y]--;
			if ($rd[$y]==0)
			{
				$q[$tail]=$y; $tail++;
			}
		}
	}
	
	if ($tail!=$n+1)
	{
		$log.="<span><font color=\"red\">模块出现了循环依赖，请检查。</font></span><br>";
		return $log;
	}
	
	for ($z=1; $z<=$n; $z++)
	{
		$i=$q[$z]; $modname=$modn[$i];
		foreach ($sup_func_list[$i] as $key) if ($key!='')
		{
			$flag=0;
			
			foreach($dependency[$i] as $r) if ($r!='')
				if (isset($func[$r]) && isset($func[$r][strtoupper($key)]))
					$flag=1;
			
			foreach($dependency_optional[$i] as $r) if ($r!='')
				if (isset($func[$r]) && isset($func[$r][strtoupper($key)]))
					$flag=1;
			
			if (!$flag)
			{
				if (isset($funclist[strtoupper($key)]))
				{
					$x=$funclist[strtoupper($key)];
					$log.="<span><font color=\"red\">模块{$modname}提供了函数{$key}，
					这个函数没有重载任何其依赖的模块中的同名函数，
					却重载了模块{$x}中的同名函数。这可能是函数名冲突造成的。</font></span><br>";
					return $log;
				}
			}
				
			$funclist[strtoupper($key)]=$modname;
			$func[$modname][strtoupper($key)]=1;
		}
	}
	
	$content=Array();
	for ($i=1; $i<=$n; $i++) array_push($content,$modn[$q[$i]].','.$modp[$q[$i]].','.'1');
	for ($i=1; $i<=$m; $i++) array_push($content,$bmodn[$i].','.$bmodp[$i].','.'0');
	writeover_array(GAME_ROOT.'./gamedata/modules.list.pass.php',$content);
	return 1;
}

function printmodtable($file, $readonly=0)
{
	if (!file_exists($file))
	{
		$log.="<span><font color=\"red\">错误：模块列表文件不存在。</font></span><br>";
		die();
	}
	$content=openfile($file);
	$in=sizeof($content); $n=0;
	for ($i=0; $i<$in; $i++)
	{
		list($a,$b,$c) = explode(',',$content[$i]);
		$modn[$i]=$a; $modp[$i]=$b; $modinuse[$i]=$c; $modinuse2[$i]=$c;
	}
	
	global $___TEMP_DRY_RUN, $___TEMP_DRY_RUN_COUNTER;
	$___TEMP_DRY_RUN=1;
	for ($i=0; $i<$in; $i++)
	{
		$modname=$modn[$i]; $modpath=$modp[$i]; $inuse=$modinuse[$i];
		if (!file_exists(GAME_ROOT.'./include/modules/'.$modpath.'module.inc.php'))
		{
			$modinuse[$i]=2;
			continue;
		}
		global $faillog;
		$faillog="<span><font color=\"red\">遇到了一个未知错误。这可能是由于模块{$modname}包含语法错误造成的。</font></span><br>";
		require GAME_ROOT.'./include/modules/'.$modpath.'module.inc.php';
		$faillog='';
		$dependency[$i]=explode(" ",$___MODULE_dependency);
		$dependency_optional[$i]=explode(" ",$___MODULE_dependency_optional);
		$conflict[$i]=explode(" ",$___MODULE_conflict);
	}
	
	echo '<span><font color="red">红色</font>：损坏的模块或不满足的依赖关系<br><font color="green">绿色</font>：启用的模块或满足的依赖关系<br><font color="grey">灰色</font>：被禁用的模块或不满足的可选依赖关系</span>';
	echo '<br><br><table border="1"><tr><td>模块名</td><td>依赖</td><td>可选依赖</td><td>冲突</td><td>操作</td>';
	for ($i=0; $i<$in; $i++)
	{
		$modname=$modn[$i]; $modpath=$modp[$i]; $inuse=$modinuse[$i];
		echo '<tr><td>';
		if ($inuse==0)
			echo "<font color=\"grey\">{$modname}</font>";
		elseif ($inuse==1)
			echo "<font color=\"green\">{$modname}</font>";
		else echo "<font color=\"red\">{$modname}</font>";
		echo '</td><td style="max-width:600px;word-wrap:break-word">';
		if ($inuse==2)
		{
			echo "<font color=\"red\">损坏</font>";
		}
		else 
		{
			foreach ($dependency[$i] as $key) if ($key!='')
			{
				if ($inuse==0)
					echo "<font color=\"grey\">{$key}</font>";
				else
				{
					$flag=0;
					for ($j=0; $j<$in; $j++)
						if ($modn[$j]==$key || $key=='root')
						{
							$flag=1;
							if ($modinuse[$j]==0 && $key!='root')
								echo "<font color=\"red\">{$key}</font>";
							elseif ($modinuse[$j]==1 || $key=='root')
								echo "<font color=\"green\">{$key}</font>";
							else echo "<font color=\"red\">{$key}</font>";
							break;
						}
					if (!$flag)
					{
						echo "<font color=\"red\">{$key}</font>";
					}
				}
				echo '&nbsp;';
			}
		}
		echo '</td><td style="max-width:600px;word-wrap:break-word">';
		if ($inuse==2)
		{
			echo "<font color=\"red\">损坏</font>";
		}
		else 
		{
			foreach ($dependency_optional[$i] as $key) if ($key!='')
			{
				if ($inuse==0)
					echo "<font color=\"grey\">{$key}</font>";
				else
				{
					$flag=0;
					for ($j=0; $j<$in; $j++)
						if ($modn[$j]==$key || $key=='root')
						{
							$flag=1;
							if ($modinuse[$j]==0 && $key!='root')
								echo "<font color=\"grey\">{$key}</font>";
							elseif ($modinuse[$j]==1 || $key=='root')
								echo "<font color=\"green\">{$key}</font>";
							else echo "<font color=\"grey\">{$key}</font>";
							break;
						}
					if (!$flag)
					{
						echo "<font color=\"grey\">{$key}</font>";
					}
				}
				echo '&nbsp;';
			}
		}
		echo '</td><td>';
		if ($inuse==2)
		{
			echo "<font color=\"red\">损坏</font>";
		}
		else 
		{
			foreach ($conflict[$i] as $key) if ($key!='')
			{
				if ($inuse==0)
					echo "<font color=\"grey\">{$key}</font>";
				else
				{
					$flag=0;
					for ($j=0; $j<$in; $j++)
						if ($modn[$j]==$key || $key=='root')
						{
							$flag=1;
							if ($modinuse2[$j]==0 && $key!='root')
								echo "<font color=\"grey\">{$key}</font>";
							else echo "<font color=\"red\">{$key}</font>";
							break;
						}
					if (!$flag)
					{
						echo "<font color=\"grey\">{$key}</font>";
					}
				}
				echo '&nbsp;';
			}
		}
		echo '</td><td>';
		if (!$readonly)
		{
			if ($modinuse[$i]==2)
			{
				if ($modinuse2[$i]==0)
					echo '<span><font color="black">[无效]</font></span>';
				else echo '<a href="modulemng.php?action=disable&sid='.$i.'" style="text-decoration: none"><span><font color="red">[禁用]</font></span></a>';
			}
			elseif ($modinuse2[$i]==0)
				echo '<a href="modulemng.php?action=enable&sid='.$i.'" style="text-decoration: none"><span><font color="blue">[启用]</font></span></a>';
			else echo '<a href="modulemng.php?action=disable&sid='.$i.'" style="text-decoration: none"><span><font color="red">[禁用]</font></span></a>';
			echo ' | <a href="modulemng.php?action=remove&sid='.$i.'" style="text-decoration: none"><span><font color="red">[删除模块]</font></span></a>';
		}
		echo '</td></tr>';
	}
	echo '</table>';
}

function show_adv_state(){
	global $___MOD_CODE_ADV1, $___MOD_CODE_ADV2, $___MOD_CODE_ADV3, $___MOD_SRV;
	$lang_on = '<font color="green">已开启</font>';
	$lang_off = '<font>已关闭</font>';
	$lang_unav = '<font color="red">无法正常运行</font>';
	$lang_turn_on = '[点此开启]';
	$lang_turn_off = '[点此关闭]';
	$adv_state_log = '';
	$adv_state_log .= '<span>代码预处理(ADV1)'.
		($___MOD_CODE_ADV1 ? $lang_on : $lang_off).'。'.
			($___MOD_CODE_ADV1 ? '<a href="modulemng.php?mode=advmng&action=turn_off&type=1">'.$lang_turn_off.'</a>' : '<a href="modulemng.php?mode=advmng&action=turn_on&type=1">'.$lang_turn_on.'</a>').'</span><br>';
	$adv_state_log .= '<span>eval预处理(ADV2)'.
		($___MOD_CODE_ADV2 ? ($___MOD_CODE_ADV1 ? $lang_on : $lang_unav) : $lang_off).'。'.
			($___MOD_CODE_ADV2 ? '<a href="modulemng.php?mode=advmng&action=turn_off&type=2">'.$lang_turn_off.'</a>' : '<a href="modulemng.php?mode=advmng&action=turn_on&type=2">'.$lang_turn_on.'</a>').'</span><br>';
	$adv_state_log .= '<span>模板html预处理(ADV3)'.
		($___MOD_CODE_ADV3 ? ($___MOD_CODE_ADV2 ? ($___MOD_CODE_ADV1 ? $lang_on : $lang_unav) : $lang_unav) : $lang_off).'。'.
			($___MOD_CODE_ADV3 ? '<a href="modulemng.php?mode=advmng&action=turn_off&type=3">'.$lang_turn_off.'</a>' : '<a href="modulemng.php?mode=advmng&action=turn_on&type=3">'.$lang_turn_on.'</a>').'</span><br>';
	$adv_state_log .= '<span>daemon模式(SRV)'.
		($___MOD_SRV ? ($___MOD_CODE_ADV2 ? ($___MOD_CODE_ADV1 ? $lang_on : $lang_unav) : $lang_unav) : $lang_off).'。'.
			($___MOD_SRV ? '<a href="modulemng.php?mode=advmng&action=turn_off&type=4">'.$lang_turn_off.'</a>' : '<a href="modulemng.php?mode=advmng&action=turn_on&type=4">'.$lang_turn_on.'</a>').'</span><br>';
	return $adv_state_log;
}
		
		
/* End of file modulemng.func.php */
/* Location: /include/modulemng/modulemng.func.php */