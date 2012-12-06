/*加载评论*/
function loadComment(id, page) {
	if (!page) page = 0;
	var t = Math.random();
	var url = web_script + '?c=Comment&t=' + t;
	
	$.get(url, {id: id, p: page}, function(data) {
		$('#comment_tb').html(data);
	});
}

/*获取文章信息*/
function loadInfo(id, type) {
	if (!type) type = '';
	var t = Math.random();
	var url = web_script + '?c=Article&a=loadInfo&v=json&t=' + t;
	$.getJSON(url, {id: id, type: type}, function(output) {
		if (output.state < 0) {
			alert(output.data.errorMsg);	
		} else {
			$('#p_c_up_'+id).html(output.data.up.toString());
			$('#p_c_down_'+id).html(output.data.down.toString());
			$('#p_c_hits_'+id).html(output.data.hits.toString());
		}
	});
}


/*回复评论*/
function setReply(id, ifloor) {
	if (id) {
		$('#reply_span span').html(ifloor);
		$('#reply_id').val(id);
		$('#reply_span').show();	
	} else {
		$('#reply_span span').html('');
		$('#reply_id').val(0);
		$('#reply_span').hide();	
	}
}

/*支持评论*/
function upReply(id) {
	var t = Math.random();
	var url = web_script + '?c=Comment&a=up&t=' + t;	
	$.get(url, {id: id}, function(r){ $('#up_reply_'+id).text(parseInt($('#up_reply_'+id).text()) + 1) });
}

/*显示表情框*/
function showFace() {
  $('#cmtFace').toggle();
}

/*设置回复表情*/
function setFace(obj) {
 $('#comment_content').val($('#comment_content').val() + $(obj).attr('title'));
 showFace();
}

/*显示验证码*/
function showVcode() {
	if($('#vcode_span').is(':visible')) return;
	changeVcode();
	$('#vcode_span').show();
}
/*更换验证码*/
function changeVcode() {
	var t = Math.random();
	var url = web_script + '?c=Comment&a=vcode&t=' + t;	
	$('#captchax').attr('src', url);
}

/*幻灯模式*/
var viewBox = {
	page: 0,
	viewTotal: 0,
	viewSize: 5,
	curPage: 1,
	totalPage: 1,
	viewPage: 1,
	autoShow: function() {
		this.totalPage = Math.ceil(this.viewTotal / this.viewSize);
		this.viewPage = Math.ceil(this.curPage / this.viewSize);
		
		$('.thumb-li').removeClass('on');
		$('#thumb-li-' + (this.curPage - 1)).addClass('on');
		this.showPage();
	},
	showPre: function() {
		if (this.viewPage > 1) {
			this.viewPage--;
			this.showPage();	
		}
	},
	showNext: function() {
		if (this.viewPage < this.totalPage) {
			this.viewPage++;
			this.showPage();	
		}
	}, 
	showPage: function(){
		if (this.viewPage <= 1) {
			$('#thumb_pre').removeClass('thumbPreOn');
			$('#thumb_pre').addClass('thumbPreOff');	
		} else {
			$('#thumb_pre').removeClass('thumbPreOff');
			$('#thumb_pre').addClass('thumbPreOn');	
		}
		if (this.viewPage >= this.totalPage) {
			$('#thumb_next').removeClass('thumbNextOn');
			$('#thumb_next').addClass('thumbNextOff');
		} else {
			$('#thumb_next').removeClass('thumbNextOff');
			$('#thumb_next').addClass('thumbNextOn');	
		}
		var start = (this.viewPage - 1) * this.viewSize;
		var end = Math.min(this.viewTotal, start + this.viewSize);
		$('.thumb-li').hide();
		for (i = start; i < end; i++) {
			$('#thumb-li-' + i).show();	
		}
	}
}