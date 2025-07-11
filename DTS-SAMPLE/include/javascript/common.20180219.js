// 对Date的扩展，将 Date 转化为指定格式的String
// 月(M)、日(d)、小时(h)、分(m)、秒(s)、季度(q) 可以用 1-2 个占位符， 
// 年(y)可以用 1-4 个占位符，毫秒(S)只能用 1 个占位符(是 1-3 位的数字) 
// 例子： 
// (new Date()).Format("yyyy-MM-dd hh:mm:ss.S") ==> 2006-07-02 08:09:04.423 
// (new Date()).Format("yyyy-M-d h:m:s.S")      ==> 2006-7-2 8:9:4.18 
Date.prototype.Format = function (fmt) { //author: meizz 
  var o = {
    "M+": this.getUTCMonth() + 1, //月份 
    "d+": this.getUTCDate(), //日 
    "h+": this.getUTCHours(), //小时 
    "m+": this.getUTCMinutes(), //分 
    "s+": this.getUTCSeconds(), //秒 
    "q+": Math.floor((this.getUTCMonth() + 3) / 3), //季度 
    "S": this.getUTCMilliseconds() //毫秒 
  };
  if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (this.getUTCFullYear() + "").substr(4 - RegExp.$1.length));
  for (var k in o)
  if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
  return fmt;
}

//getElementById
function $(id) {
	return document.getElementById(id);
}

timingforbidden = new Array();

//update time
function updateTime(domid,t,tm,intv,fmt)
{
	var tm = tm || 0;
	var domid = domid || 'timing';
	var init_flag = 0;
	if('undefined'!=typeof(timingforbidden[domid])) return;//如果计时被禁用则不计时
	if('undefined'==typeof(timinglist)) {
		timinglist = new Array();
		init_flag = 1;
	}
	if('undefined'==typeof(timinglist[domid])) {
		timinglist[domid] = new Array();
		init_flag = 1;
	}
	if(init_flag){//初始化，这一次时间不减
		var intv = intv || 1000;
		var fmt = fmt || 'hh:mm:ss';
		timinglist[domid]['timing'] = t;//注意timing现以毫秒为单位
		timinglist[domid]['mode'] = tm;
		timinglist[domid]['interval'] = intv;
		timinglist[domid]['format'] = fmt;
	}else{
		var t = timinglist[domid]['timing'];
		var t0 = t;
		var tm = timinglist[domid]['mode'];
		var intv = timinglist[domid]['interval'];
		var fmt = timinglist[domid]['format'];
		if(1==tm){
			t += intv;
		}else{
			t -= intv;
			if(t < 0) t = 0;
		}
		timinglist[domid]['timing'] = t;
		if(0==t){
			if(0 < t0 && 'timing'==domid) window.location.reload(); //首页
//			else if('area_timing' == domid) {//游戏界面内禁区自动刷新，不过由于两边时间不同步，可能执行不正常
//				setTimeout(
//					function(){
//						var o_command = $('command').value;
//						$('command').value = 'area_timing_refresh';
//						postCmd('gamecmd','command.php');
//						$('command').value = o_command;
//					},
//					1000
//				);
//			}
		}
	}
	var tstr = updateTime_render(timinglist[domid]['timing'], timinglist[domid]['mode'], timinglist[domid]['format']);
	if($(domid)) {
		$(domid).innerHTML = tstr;
	}
	if(timinglist[domid]['timing'] > 0 || 1==tm){
		setTimeout("updateTime('" + domid + "'," + t + "," + tm + "," + intv + ",'" + fmt + "')", timinglist[domid]['interval']);
	}
}

function updateTime_render(t, tm, fmt)
{
	var tobj = new Date();
	tobj.setTime(t);
	var tstr = tobj.Format(fmt);
	if(0==tm && t < 10*1000) tstr = '<span class="red">'+tstr+'</span>';
	else if(0==tm && t < 60*1000) tstr = '<span class="yellow">'+tstr+'</span>';
	return tstr;
}

//ajax
var zXml={useActiveX:(typeof ActiveXObject!="undefined"),useXmlHttp:(typeof XMLHttpRequest!="undefined")};
zXml.ARR_XMLHTTP_VERS=["MSXML2.XmlHttp.6.0","MSXML2.XmlHttp.3.0","MSXML2.XmlHttp","Microsoft.XmlHttp"];
function zXmlHttp(){}
zXmlHttp.createRequest=function(){
	if(zXml.useXmlHttp){return new XMLHttpRequest();}
	else if(zXml.useActiveX){
		if(!zXml.XMLHTTP_VER){
			for(var i=0;i<zXml.ARR_XMLHTTP_VERS.length;i++){
				try{new ActiveXObject(zXml.ARR_XMLHTTP_VERS[i]);
					zXml.XMLHTTP_VER=zXml.ARR_XMLHTTP_VERS[i];break;}catch(oError){;}
				}
			}
		if(zXml.XMLHTTP_VER){return new ActiveXObject(zXml.XMLHTTP_VER);}
		else{throw new Error("Could not create XML HTTP Request.");}
	}else{throw new Error("Your browser doesn't support an XML HTTP Request.");}
};
zXmlHttp.isSupported=function(){return zXml.useXmlHttp||zXml.useActiveX;};

//form转字符串
function getRequestBody(oForm) {
	var aParams = new Array();
	var n = oForm.elements.length;
  for (var i=0 ; i < n ; i++) {
		if((oForm.elements[i].type == 'radio' || oForm.elements[i].type == 'checkbox')&&(!oForm.elements[i].checked)){continue;}
		var sParam = encodeURIComponent(oForm.elements[i].name);
		sParam += "=" + encodeURIComponent(oForm.elements[i].value);
		aParams.push(sParam);
  } 
  return aParams.join("&"); 
}

//cookie类
function Cookie(){}
Cookie.setCookie=function(name,value,option){
	var str=name+"="+escape(value);
	if(option){
		if(option.expireHours){
			var date=new Date();
			var ms=option.expireHours*3600*1000;
			date.setTime(date.getTime()+ms);
			str+=";expires="+date.toGMTString();
		}
		if(option.path)str += ";path="+option.path;
		if(option.domain)str+=";domain="+option.domain;
		if(option.secure)str+=";true";
	}
	document.cookie=str;
}
Cookie.getCookie=function(name){
	var cookie_start = document.cookie.indexOf(name);
	var cookie_end = document.cookie.indexOf(";", cookie_start);
	return cookie_start == -1 ? '' : unescape(document.cookie.substring(cookie_start + name.length + 1, (cookie_end > cookie_start ? cookie_end : document.cookie.length)));
}
Cookie.deleteCookie=function(name){
	this.setCookie(name,'',{expireHours:-1});
}


//悬浮提示本体，需要与ajax配合才能生效的
function floating_hint()
{
	jQuery('[title]').each(function() {
		var msg = jQuery(this).attr('title');
		if(''!=msg){
			jQuery(this).on({
				mouseover: function(e) { 
					jQuery('#hoverHintMsgInner').html(msg);
					jQuery('#hoverHintMsg').css({
						display: "block",
						left: Number(Math.floor(e.clientX)+10).toString()+'px',
						top: Number(Math.floor(e.clientY)+10).toString()+'px'
					});
				},
				mousemove: function(e) {
					jQuery('#hoverHintMsg').css({
						left: Number(Math.floor(e.clientX)+10).toString()+'px',
						top: Number(Math.floor(e.clientY)+10).toString()+'px'
					});
				},
				mouseout: function() { 
					jQuery('#hoverHintMsg').css({
						display:"none"
					});
				}
			});
			jQuery(this).attr('title','');
		}
	});
}