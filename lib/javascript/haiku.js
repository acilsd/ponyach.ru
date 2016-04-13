function haiku(a) {
        $("#haikaptcha").html('загрузка капчи ...');
	var xmlHttp = new XMLHttpRequest();
	if (typeof a != 'undefined') {
		xmlHttp.open( "GET", "/haikaptcha.php?m=chk&a="+a, true);
	} else {
		xmlHttp.open( "GET", "/haikaptcha.php?m=get", true);
	}
	xmlHttp.onreadystatechange = function(){
	        if (xmlHttp.readyState === 4) {
	                if ( xmlHttp.status === 200) {
	                        var haires=xmlHttp.responseText;
	                        $("#haikaptcha").html(haires);
	                }
	        }
	}
	xmlHttp.send( null );
}

function haiku_wait(a) {
	if (--a > 0) {
		setTimeout(haiku_wait(a), 1000);
                $("#haikaptcha").html('придётся подождать '+ a +' сек.');
	} else {
		haiku();
	}
}

function haiku_del() {
	var xmlHttp = new XMLHttpRequest();
	xmlHttp.open( "GET", "/haikaptcha.php?m=del", false);
	xmlHttp.onreadystatechange = function(){
		haiku_check();
	}
	xmlHttp.send( null );
}

function haiku_check() {
	var xmlHttp = new XMLHttpRequest();
	xmlHttp.open( "GET", "/haikaptcha.php?m=isndn", true);
	xmlHttp.onreadystatechange = function(){
	        if (xmlHttp.readyState === 4) {
	                if ( xmlHttp.status === 200) {
	                        var haires=xmlHttp.responseText;
				if (haires === "1") {
					// remove respond button, show captcha button
					$("#haiku_btn").show();
					$("#go").hide();
                			//if ($("#haikaptha").html().substr(0, 1) !== '<') {
						haiku();
					//}
				} else {
	                        	$("#haikaptcha").html(haires); // just in case
					$("#haiku_btn").hide();
					$("#go").show();
					$('.overlay-bg, .overlay-content').hide();
					// remove captcha button, show respond button
				}
			}
	        }
	}
	xmlHttp.send( null );
}

function update_haiku() {
	haiku_check();
	setTimeout(update_haiku, 120000);
}

function set_captcha_type(a) {
	setCookie('captcha_type', a, 36500);
	haiku();
}
