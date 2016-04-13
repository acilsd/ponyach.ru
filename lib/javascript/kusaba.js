var ispage;
var ponyabavers = "1.0";
//here is actually 4 function and 1 variable from kusaba.

!function(){
	setInterval(function(a){(a=new XMLHttpRequest).open("GET","/messages.php?m=list");
	a.onreadystatechange=function(c,b){
		if (a.readyState === 4) {
			if (a.status === 200) {
				if (c=a.responseText){
					$("#messages").html(a.responseText)};
				}
			} 
		}
	a.send()
	},30000)
}();

function do_token() {
    var xmlHttp = null;
    xmlHttp = new XMLHttpRequest();
    xmlHttp.open( "GET", '/token.php', true );
    xmlHttp.onload = function (e){ 
            if (xmlHttp.readyState === 4) {
                    if ( xmlHttp.status === 200) {
                        var date = new Date( new Date().getTime() + 3*60*60*1000 );
                        document.cookie="ijslo=" + xmlHttp.responseText + "; path=/; expires="+date.toUTCString();
                    }
            }
    }
    xmlHttp.send( null );
}

function show_message_text(i) {
	(a=new XMLHttpRequest).open("GET","/messages.php?m=view&id=" + i);
	a.onreadystatechange=function(c){
		if (a.readyState === 4) {
			if (a.status === 200) {
				if (c=a.responseText){
					$("#message_text").html(' \
					<br><a class="button" href="javascript://" title="Скрыть сообщение" onclick="hide_message_text()">[Скрыть]</a> \
					<a class="button" href="javascript://" title="Удалить сообщение" onclick="del_message('+ i +')">[X]</a><br> ' + a.responseText)};
				}
			}
		}
	a.send();
}

function hide_message_text() {
	$("#message_text").html('');
}

function del_message(i) {
	(a=new XMLHttpRequest).open("GET","/messages.php?m=del&id=" + i);
	a.onreadystatechange=function(c){
		hide_message_text();
		$("#messages").html('');
	}
	a.send();
}

function show_filesize(a,b) {
    if ($("#fs_"+a+"_"+b)) {
        $("#fake_filesize_"+a).html($("#fs_"+a+"_"+b).html());
    }
}

function show_filesize_on_insert(event) {
    var id = event.target.id;
    var postid = id.substring(6);
    show_filesize(postid, 1);
}

function show_irc_reflink(x) {
	$(".irc-reflink-from-"+x).toggle();
}

function hide_irc_reflink(x) {
	$(".irc-reflink-from-"+x).hide();
}

function view_dialog(b,p,r) {
	hide_dialog();
        $.ajax({url:ku_boardspath + "/discuss.php?b="+b+"&p="+p+"&r="+r, success: function(a) {
		$("#reply-irc").html(a).show();
	}});
}

function hide_dialog() {
	$("#reply-irc").html('').hide();
};

//============================kusaba stuff============================//

function insert(a) {
    if (!ispage) {
        var b = document.forms.postform.message;
        if (b) {
            if (b.createTextRange && b.caretPos) {
                var c = b.caretPos;
                c.text = c.text.charAt(c.text.length - 1) == " " ? a + " " : a
            } else if (b.setSelectionRange) {
                var d = b.selectionStart;
                var e = b.selectionEnd;
                b.value = b.value.substr(0, d) + a + b.value.substr(e);
                b.setSelectionRange(d + a.length, d + a.length)
            } else {
                b.value += a + " "
            }
            b.focus();
            return false
        }
    }
    return true
}
function checkhighlight() {
    var a;
    if (a = /#i([0-9]+)/.exec(document.location.toString()))
        if (!document.forms.postform.message.value)
            insert(">>" + a[1] + "\n");
    if (a = /#([0-9]+)/.exec(document.location.toString()))
        highlight(a[1])
}
function highlight(a, b) {
    if ((b && ispage) || ispage) {
    }
    var c = document.getElementsByTagName("td");
    for (var i = 0; i < c.length; i++)
        if (c[i].className == "highlight")
            c[i].className = "reply";
    var d = document.getElementById("reply" + a);
    var e = d.parentNode;
    while (e.nodeName != 'TABLE') {
        e = e.parentNode
    }
    if ((d || document.postform.replythread.value == a) && e.parentNode.className != "reflinkpreview") {
        if (d) {
            d.className = "highlight"
        }
        var f = /^([^#]*)/.exec(document.location.toString());
        document.location = f[1] + "#" + a;
        return false
    }
    return true
}
//reader need this function and onclick elements on thumbs
function expandimg(a, H, F, C, G, E, A) {
    element = document.getElementById("thumb" + a);
    var D = '<img src="' + F + '" alt="' + a + '" class="thumb" width="' + E + '" height="' + A + '">';
    var J = '<img src="' + F + '" alt="' + a + '" class="thumb" height="' + A + '" width="' + E + '">';
    var K = '<img src="' + F + '" alt="' + a + '" class="thumb" height="' + A + '" width="' + E + '"/>';
    var B = "<img class=thumb height=" + A + " alt=" + a + ' src="' + F + '" width=' + E + ">";
    if (element.innerHTML.toLowerCase() != D && element.innerHTML.toLowerCase() != B && element.innerHTML.toLowerCase() != J && element.innerHTML.toLowerCase() != K) {
        element.innerHTML = D
    } else {
        element.innerHTML = '<img src="' + H + '" alt="' + a + '" class="thumb" height="' + G + '" width="' + C + '">'
    }
}

function set_inputs(a) {
    if (document.getElementById(a)) {
        with (document.getElementById(a)) {
            if (!name.value)
                name.value = getCookie("name");
            if (!em.value)
                em.value = getCookie("email");
        }
    }
}
window.onload = function(e) {
    checkhighlight();
};

//============================CUSTOM============================//
if ( getCookie("ctrlenoff")  !== '1' ) {
$(document).ready(function() {
    $('#msgbox').keydown(function (e) {
        if ((e.keyCode == 10 || e.keyCode == 13) && (e.ctrlKey || e.altKey)) {
            postform_submit();
        }
    });
});
}

$(function() {
	$('div#settings-main, div#settings-styles').draggable({
		snap: "#snapper, .droppable",
		snapMode: "both",
		snapTolerance: 50
	});
});

$(function() {
	$('div.reply-irc').draggable({
		snap: "#snapper, .droppable",
		snapMode: "both",
		snapTolerance: 50
	});
});


function showpostmenu(postnum){
	var topPos = $('#postmenuclick' + postnum).position();
	$('#postmenu').css({"top" : topPos.top + 25, "left" : topPos.left});
	
	if(typeof temppost === 'undefined'){
		$('#postmenu').show();
		temppost = postnum;
		return;
	} else {
		if (temppost != postnum){
			$('#postmenu').hide();
			$('#postmenu').show();
			temppost = postnum;
			return;
		}
	}
	
	if ($('#postmenu').is(":visible")){
		$('#postmenu').hide();
	} else{
		$('#postmenu').show();
	}
	temppost = postnum;
	return temppost;
}

function hidepostmenu_timer_cancel(){
	if(closetimer){
		window.clearTimeout(closetimer);
		closetimer = null;
	}
}

function hidepostmenu_timer(){
	closetimer = window.setTimeout(hidepostmenu, 500);
}

function hidepostmenu(){
	if ($('#postmenu').is(":visible"))
		$('#postmenu').hide();
}

function edit_post(){

        $.ajax({url:ku_boardspath + '/get_raw_post.php?b='+this_board_dir+'&p='+temppost, success: function(a) {
		var x = document.getElementById('reply'+temppost);
		if (x === null) {
			// mb it's an op post
			x = document.getElementById('thread'+temppost+this_board_dir);
		}

		if (x === null) {
			// still not found - well, better stop here
			return false;
		}

		var y = x.getElementsByClassName('de-btn-rep')[0];
		if (y === null) // no dollchan
			return false;

		y.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true }));
		var message = document.getElementById('msgbox');

		var b = JSON.parse(a);
		if (b.status == 0) {
			message.value = b.raw_message;
			subject.value = b.subject;
			namebox.value = b.name;
			em.value = b.email;
			add_edit_field(temppost);
		} else {
			alertify.alert(b.error);
		}
	}});
}


/* Fix search image buttons */

function fix_search_buttons(){
	$('.pstnode > .reply').each(function() {
        show_filesize($(this).attr('data-num'), 1);
    });

	var target = document.querySelector('body > form > .pstnode');
	var observer = new MutationObserver(function(mutations) {
	    mutations.forEach(function(mutation) {
	        if (mutation.addedNodes.length > 0) {
	            var mut = mutation.addedNodes;
	            Array.prototype.forEach.call(mut, function (node) {
	                if ($(node).find('.reply').attr('data-num')) {
	                    show_filesize($(node).find('.reply').attr('data-num'), 1);
	                }
	            });
	        }
	    });
	});
	var config = { attributes: true, childList: true, characterData: true };
	observer.observe(target, config);
}

(function(){
	//пока кукла отработает
    setTimeout(fix_search_buttons, 4000);
})();

/* ======================== */ 