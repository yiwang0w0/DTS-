<?php

namespace itemmain
{
	$item_equip_list = Array('itm0','itm1','itm2','itm3','itm4','itm5','itm6');
	
	//■ 无限耐久度 ■
	$nosta = '∞';
	//■ 无属性 ■
	$nospk = '--';
	
	//道具发现基础几率(百分比);
	$item_obbs = 60;
	
	//不会有物品掉落的地区列表
	$map_noitemdrop_arealist = Array(0, 34);
	
	//物品类型
	//请在对应模块中赋值，这里只写注释代表哪些名字已经被哪些模块占用了
	$iteminfo = Array(
		//A* armor_art 饰品
		//B* item_ub 电池
		//C* item_uc 各类异常状态治疗药剂
		//D* armor 各类防具
		//EE item_uee 电脑设备
		//ER radar 雷达
		//EW weather 天气控制道具
		//GA wep_b 箭矢
		//GB* ammunition 各类弹药
		//HH,HS,HB edible 各类命体恢复
		//HM,HT song 歌魂补给
		//M* item_um 各类强化药
		'N' => '无',	
		//P* poison 各类被下毒的补给
		//p,ygo,fy 各类礼包
		//ss song 歌词卡片
		//T* trap 各类陷阱
		//U item_misc
		//V* item_uv 技能书籍
		//W* weapon 各类武器
		'X'=> '合成专用',
		'Y' => '特殊',
		'Z' => '特殊',#不可合并
		);
	
	//物品属性类型
	//请在对应模块中赋值，这里只写注释代表哪些名字已经被哪些模块占用了
	//鉴于字母已经基本用完，新属性应该全部命名为“^数字”的形式，其中数字可以任意
	//例： ^233 => '防拳' 
	//\itemmain\count_itmsk_num(属性)可以计数物品属性个数
	$itemspkinfo = Array(
		//A ex_phy_def 物防（全系防御）
		//a ex_dmg_def 属防（属性防御）
		//B ex_phy_nullify 物抹（伤害抹消）
		//b ex_dmg_nullify 属抹（属性抹消）
		//C ex_phy_def 防投
		//c rage 集气（重击辅助）
		//D ex_phy_def 防爆
		//d ex_dmg_att 爆炸
		//E ex_dmg_def 绝缘
		//e ex_dmg_att 电击
		//F ex_phy_def 防符
		//f ex_dmg_att 灼焰
		//G ex_phy_def 防弹
		//g ex_gender_attr 同志
		//H ex_hp_def 控噬（HP制御）
		//h ex_hp_def 控血（伤害制御）
		//I ex_dmg_def 防冻
		//i ex_dmg_att 冻气
		//J itemmix_overlay 超量素材
		//j wepchange 多重
		//K ex_phy_def 防斩
		//k ex_dmg_att 冰华
		//L ex_direct 直击
		//l ex_gender_attr 热恋
		//M ex_attr_trap 探雷（陷阱探测）
		//m ex_attr_trap 防雷（陷阱迎击）
		//N ex_attr_charge 冲击 
		//n ex_attr_pierce 物穿
		//O ex_cursed 诅咒
		//o ammunition 枪械一发
		//P ex_phy_def 防殴
		//p ex_dmg_att 带毒
		//Q ///////////////////////////
		//q ex_dmg_def 防毒
		//R ex_rapid_def 防连
		//r ex_rapid_attr 连击		
		//S ex_attr_silencer 消音
		//s itemmix_sync 调整（同调合成必需）
		//T ///////////////////////////
		//t ex_dmg_att 音爆
		//U ex_dmg_def 防火
		//u ex_dmg_att 火焰
		//V ex_seckill 弑神
		//v ex_seckill 直死
		//W ex_dmg_def 隔音
		//w ex_dmg_att 音波
		//X ///////////////////////////
		'x' => '奇迹',
		//Y ///////////////////////////
		//y ex_attr_pierce 属穿
		//Z blessstone 菁英
		'z' => '天然',
		//| 特殊分隔符
		//^001 itemmix_sync 同调（标记是同调产物）
		//^002 itemmix_sync 变星
	);
	
	$itemspkdesc = array(
		'x' => '人类，可以超越神么？……',
		'z' => '使用后会变成冴冴一样的天然呆',
	);
	
	$itemspkremark = array(
		'x' => '……',
		'z' => '……',
	);
}

?>