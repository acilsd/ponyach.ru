var kumod_set = false;

$(document).ready(function () {
	if (getCookie("kumod") == "allboards") {
		kumod_set = true
	} else if (getCookie("kumod") != "") {
		var c = getCookie("kumod").split('|');
		var d = document.getElementById("postform").board.value;
		for (var f in c) {
			if (c[f] == d) {
				kumod_set = true;
				break
			}
		}
	}
	if (kumod_set == true) {
		togglePassword();
	}
	//hack for close all modlog messages
	window.addEventListener("storage", modlog_viewed_global, false);
});

function ajaxdel(i) {
	$.ajax({url:ku_boardspath + "/manage_page.php?action=ajaxdel&board=" + e[1] + "&id=" + e[2] + (i == 1 ? '&undel=1' : ''),
		headers: { 'X-TOKEN': ajaxtoken }
	,success: function(data) {
		alertify.log(data);
	},error: function() {
		alertify.log(data);
	}});
}

function instantban(board, id) {
	$.ajax({url:ku_boardspath + '/manage_page.php?action=bans&banboard=' + board + '&banpost=' + id + '&instant=y&reason=',
		headers: { 'X-TOKEN': ajaxtoken }
	,success: function(data) {
		alertify.log('Забанила');
	},error: function() {
		alertify.log('Не удалось забанить');
	}});
}

function putdnblks(k) {
	d = document.getElementById(k);
	e = k.split('-');
	//mod stuff
	if (getCookie('kumod') != 'allboards') {
		var modboards = getCookie('kumod').split('|');
		var in_array = modboards.indexOf(e[1]);
		if (in_array == -1) throw 'You don\'t mod this board'
	}
	var f = "";
	
		function ajaxdel() {
			$.ajax({url:ku_boardspath + "/manage_page.php?action=ajaxdel&board=" + e[1] + "&id=" + e[2],
				headers: { 'X-TOKEN': ajaxtoken }
			,success: function(data) {
				alertify.log(data);
			},error: function() {
				alertify.log(data);
			}});
		}
	

	$.ajax({url:ku_boardspath + '/manage_page.php?action=getajaxtoken&board=' + e[1] + '&id=' + e[2]
		,success: function(data) {
			ajaxtoken = data;
		},error: function() {
			ajaxtoken = "can't get token";
		}});
	
	
        $.ajax({url:ku_boardspath + "/manage_page.php?action=getip&boarddir=" + e[1] + "&id=" + e[2]
			,success: function(a) {
                ipaddr = a.trim().split("=") || "Ошибка авторизации";
		i = ipaddr[2];
                span = document.getElementById(ipaddr[0]);
                f = '[IP: ' + ipaddr[1] + ' <a href="' + ku_boardspath + '/manage_page.php?action=deletepostsbyip&ip=' + ipaddr[1] + '" target="_blank" title="Удалить все посты этого IP">Удалить все посты</a> / \
				<a href=' + ku_boardspath + '"/manage_page.php?action=ipsearch&ip=' + ipaddr[1] + '" target="_blank" title="Найти все посты этого IP">Найти все посты</a>] ' ;

				var oppostInThread = document.getElementsByClassName('dnb')[0];
				ooppostInThreadId = oppostInThread.id.split('-')[2];
                f += ' [<a href="javascript:void(0)" id="ajaxdel" onclick="ajaxdel('+i+')">' + (i == 0 ? 'Удалить' : 'Разудалить') + '</a> / \
				<a href="' + ku_cgipath + '/manage_page.php?action=bans&banboard=' + e[1] + '&banpost=' + e[2] + '" target="_blank" title="Бан">Забанить<\/a> / \
				<a href="' + ku_cgipath + '/manage_page.php?action=threadbans&board_x=' + e[1] + '&post_x=' + e[2] + '" target="_blank" title="Бан">Бан в треде<\/a> / \
				<a href="javascript:void(0)" title="Пермамент бан" onclick="instantban(\'' + e[1] + '\',' + e[2] + ');">Быстрый бан<\/a> / \
				<a href="' + ku_cgipath + '/manage_page.php?action=editpost&boarddir=' + e[1] + '&editpostid=' + e[2] + '" target="_blank" title="Редактировать пост">Ред</a> / \
				<a href="' + ku_cgipath + '/manage_page.php?action=lockpost&board=' + e[1] + '&postid=' + ooppostInThreadId + '" title="Закрыть тред (тред)" id="lockfromthread" >Закр</a>] \
				<a href="' + ku_cgipath + '/manage_page.php?action=lockpost&board=' + e[1] + '&postid=' + e[2] + '" title="Закрыть тред (доска)" id="lockfromboard" >Закр</a> \
                [<a href="javascript:void(0)" onclick="return reset_dnblinks();"><<</a>] ';

			   
	
	$("#"+k).hide().html(f).toggle("slow");
	//cheking if this page = board
	for (testcount=1;testcount<10;testcount++) {
		testvar = document.getElementsByClassName('dnb')[testcount].id.split('-')[3];
		if (testvar == "y"){
		break;
		}
	}
	if (testvar == "y"){
		$("#lockfromthread").hide();
	} else {
		$("#lockfromboard").hide();
	}

            },error: function() {
                f = "[Ошибка авторизации]";
                d.innerHTML = f;
			}});
  return false;
}

function reset_dnblinks() {
    if (kumod_set) {
		var d;
		var e;
		var c = document.getElementsByTagName('span');
		for (var i = 0; i < c.length; i++) {
			d = c[i];
			if (d.getAttribute('id')) {
				if (d.getAttribute('id').substr(0, 3) == 'dnb') {
			d.innerHTML='';
					d.innerHTML='&#91;<a href="javascript:void(0)" onclick="return putdnblks(\'' + d.getAttribute('id') + '\');">Мод</a>&#93;';
				}
			}
		}
	}
    return false;
}

function delandbanlinks() {
    if (kumod_set){
		view_modlog();
		var c = document.getElementsByTagName('span');
		var d;
		var e;
		for (var i = 0; i < c.length; i++) {
			d = c[i];
			if (d.getAttribute('id')) {
				if (d.getAttribute('id').substr(0, 3) == 'dnb') {
					if (!d.innerHTML.trim()) {
							var x =' &#91;<a href="javascript:void(0)" onclick="return putdnblks(\'' + d.getAttribute('id') + '\');">Мод</a>&#93;';
							$("#"+d.getAttribute('id')).hide().html(x).toggle("slow");
					}
				}
			}
		}
	} else {
		return;
	}
}

function togglePassword() {
    var a = (navigator.userAgent.indexOf('Safari') != -1);
    var b = (navigator.userAgent.indexOf('Opera') != -1);
    var c = (navigator.appName == 'Netscape');
    var d = document.getElementById("passwordbox");
    if (d) {
        var e;
        if ((a) || (b) || (c))
            e = d.innerHTML;
        else
            e = d.text;
        e = e.toLowerCase();
        var f = '<td></td><td></td>';
        if (e == f) {
	var f = '<td class="postblock">Модерация</td><td> \
		<div id="modbutton"><label><input id="status" type="checkbox" name="displaystaffstatus" /><span>Статус</span></label> \
		<label><input id="closethread" type="checkbox" name="lockonpost" /><span>Закр</span></label> \
		<label><input id="sticky" type="checkbox" name="stickyonpost" /><span>Прикр</span></label> \
		<label><input id="raw" type="checkbox" name="rawhtml" /><span>Raw</span></label> \
		<label><input id="name" type="checkbox" name="usestaffname" /><span>Имя</span></label> \
		</div></td>'
        }
        if ((a) || (b) || (c))
            d.innerHTML = f;
        else
            d.text = f
    }
    return false
}



//===========MODLOG===========//

function view_modlog() {
	if (getCookie('kumod') != 'allboards') return;
	var last_view = localStorage.getItem('modlog');
	if (last_view == null) {
		last_view = '0';
	}
	$.ajax({
	url:ku_boardspath + "/manage_page.php?action=modlog_after&timestamp=" + last_view
	, success: function(a) {
		if (a.trim() !== 'false'){
			if ($("#popupMessage").get() == "") {
				alertify.log('<span onclick="modlog_viewed();" id="popupMessage">' + a + '</span>', '', 0);
			}
		}
	}
	});
}

function modlog_viewed() {
	last_view = Math.round(new Date().getTime() / 1000);
	localStorage.setItem('modlog', last_view);
	$(".alertify-log-show").trigger('click');
	localStorage.setItem('modlog_global', 'clicked')
}

function modlog_close() {
	$(".alertify-log-show").trigger('click');
}

function modlog_viewed_global() {
	if (localStorage.getItem('modlog_global') == 'clicked'){
		last_view = Math.round(new Date().getTime() / 1000);
		localStorage.setItem('modlog', last_view);
		modlog_close();
		localStorage.setItem('modlog_global', 'notclicked');
	}
}

function popupMessageModlog(b, a) {
    if (a == null) {
        a = 1000
    }
    if ($("#popupMessage").get() == "") {
        $("body").children().last().after('<div id="popupMessage" onclick="modlog_viewed();" class="reply"></div>');
        $("#popupMessage").css("position", "fixed");
        $("#popupMessage").css("top", "5px");
        $("#popupMessage").css("padding", "10px");
        $("#popupMessage").css("width", "50%");
        $("#popupMessage").css("left", "45%");
        $("#popupMessage").css("text-align", "center");
        $("#popupMessage").css("-webkit-box-shadow", "#999 0px 0px 10px");
        $("#popupMessage").hide()
    }
    $("#popupMessage").html('<span class="postername">' + b + "</span>");
    $("#popupMessage").fadeIn(150).delay(a).fadeOut(300)
}


window.addEventListener("storage", modlog_viewed_global, false);