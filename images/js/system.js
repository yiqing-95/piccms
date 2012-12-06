/* start ready */
(function ($){
	/*全选反选*/
	$.selectAll = function(name) {
		var checked = $("input=[name='"+name+"']").attr('checked');
		$("input=[name='"+name+"']").attr('checked', !checked);
	}
	
	/*获取复选框的值*/
	$.checkBoxValue = function (name, string) {
		var ids = [];
		$("input=[name='"+name+"']:checked").each(function(i) {
			ids.push($(this).val());
		});
		return string ? ids.join(',') : ids;
	}
	
	/*tab切换*/
	$.showTab = function(the, total, tabid, divid, onclass) {
		if (undefined == divid) {
			divid = '#showtab_';	
		}
		if (undefined == tabid) {
			tabid = '#tab_';
		}
		if (undefined == onclass) {
			onclass = 'on';	
		}
		for(var i=1;i<=total;i++){
			$(divid+i).hide();
			$(tabid+i).removeClass(onclass);
		}
		$(tabid+the).addClass(onclass);
		$(tabid+the).blur();
		$(divid+the).show();
	}
	
	/*包含swf的Tab切换*/
	$.showSwfTab = function(the, total, tabid, divid) {
		if (undefined == divid) {
			divid = '#showtab_';	
		}
		if (undefined == tabid) {
			tabid = '#tab_';
		}
		for(var i=0;i<=total;i++){
			$(divid+i).css('visibility:', 'hidden');
			$(divid+i).parent().css('height', 0);
			$(tabid+i).removeClass('on');
		}
		$(tabid+the).addClass('on');
		$(tabid+the).blur();
		$(divid+the).css('visibility', 'visible');
		$(divid+the).parent().css('height', 'auto');
	}
		
	
	/* 读取Cookie */
	$.getCookie = function(name){
		var sta=document.cookie.indexOf(name+"=");
		var len=sta+name.length+1;
		if((!sta)&&(name!=document.cookie.substring(0,name.length))){
			return null;
		}
		if(sta==-1) return null;
		var end=document.cookie.indexOf(';',len);
		if(end==-1) end=document.cookie.length;
		return unescape(document.cookie.substring(len,end));
	}
	/* 设置Cookie */
	$.setCookie=function(name,value,expires,path,domain,secure){
		var today=new Date();
		today.setTime( today.getTime() );
		if ( expires ) {
		expires = expires * 1000 * 60 * 60 * 24;
		}
		var expires_date = new Date( today.getTime() + (expires) );
		document.cookie = name+'='+escape( value ) + ( ( expires ) ? ';expires='+expires_date.toGMTString() : '' ) +
		( ( path ) ? ';path=' + path : '' ) + ( ( domain ) ? ';domain=' + domain : '' ) + ( ( secure ) ? ';secure' : '' );
	}
	
	/*复制到剪切版*/
	$.setCopy = function(s){
		try {
			clipboardData.setData('Text',s);
			alert('本页地址已成功复制在剪贴板内！');
		} catch(e) {}
	}
	
	/*加载Js*/
	$.loadJs = function(file) {
		var js = document.createElement("script");
		js.src = file;
		document.getElementsByTagName("head")[0].appendChild(js);
	}
	
	/*添加到收藏夹*/
    $.addFav = function (web_url, web_name) {
		if (document.all) {
			window.external.addFavorite(web_url, web_name);
		} else if (window.sidebar) {
			window.sidebar.addPanel(web_name, web_url, "");
		}
	}
	
	/*在弹出窗口中打开远程地址*/
	$.fopen = function (url, postdata, title) {
		$.ajax({
			url:url,
			type:'POST',
			data: postdata,
			timeout:60000,
			cache:false,
			error:function(e){
				$.alerts.alert('出错了');
			},
			success: function(back){
				$.alerts.window(back, title);
			}
		});		
	}
	
	$.popen = function (url, width, height) {
		$.ajax({
			url:url,
			type:'POST',
			data: postdata,
			timeout:60000,
			cache:false,
			error:function(e){
				$.alerts.alert('出错了');
			},
			success: function(back){
				$.alerts.window(back, title);
			}
		});		
	}
})(jQuery);


/*幻灯*/
function weeFoucs(config) {
	var lastKey;
	var curKey = 1;
	var timer;
	var target = $(config['target']);
	var control = $(config['control']);
	var title = $(config['title']);
	var maxCount = control.length;
	var inEffect = 'fadeIn';
	var outEffect = 'fadeOut';
	var curClass = 'current';
	if (config['curClass']) {
		curClass = config['curClass'];
	}
	control.each(function(i) {
		$(control[i]).hover(function(){
			stop();
			playTo(i);
		}, function() {
			play();
		});
	});
	function playTo(i) {
		if (i != lastKey) {
			$(target[lastKey]).hide();
			$(title[lastKey]).hide();
			$(control[lastKey]).removeClass(curClass);
		} 
		lastKey = i;
		$(target[i]).fadeIn();
		$(title[i]).show();
		$(control[i]).addClass(curClass);
	}
	function play() {
		if (timer) {
			stop();
		}
		timer = window.setInterval(function() {
			if (curKey > maxCount - 1) {
				curKey = 0;
			}
			playTo(curKey);
			curKey++;
		},
		config.delayTime);
	}
	function stop() {
		window.clearInterval(timer);
	}
	if (maxCount > 0) {
		playTo(0);
		play();
	}
}

/*搜索框*/
function subsearch(textId) {
	var keyword = $('#' + textId).val().replace(/(^\s*)|(\s*$)/, '');
	if (keyword) {
		location.href = web_script + "?c=Search&keyword="+encodeURI(keyword);
	}
}


