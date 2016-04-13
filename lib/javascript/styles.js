var style_cookie;

function set_stylesheet(a, b, c) {
	if (a == get_default_stylesheet()){
		delCookie("kustyle");
	} else {	
		setCookie("kustyle", a, 365);
	}	
    var d = document.getElementsByTagName("link");
    var e = false;
    for (var i = 0; i < d.length; i++) {
        var f = d[i].getAttribute("rel");
        var g = d[i].getAttribute("title");
        if (f.indexOf("style") != -1 && g) {
            d[i].disabled = true;
            if (a == g) {
                d[i].disabled = false;
                e = true
            }
        }
    }
    if (!e)
        set_preferred_stylesheet()
}
function set_preferred_stylesheet() {
    var a = document.getElementsByTagName("link");
    for (var i = 0; i < a.length; i++) {
        var b = a[i].getAttribute("rel");
        var c = a[i].getAttribute("title");
        if (b.indexOf("style") != -1 && c)
            a[i].disabled = (b.indexOf("alt") != -1)
    }
}
function get_active_stylesheet() {
    var a = document.getElementsByTagName("link");
    for (var i = 0; i < a.length; i++) {
        var b = a[i].getAttribute("rel");
        var c = a[i].getAttribute("title");
        if (b.indexOf("style") != -1 && c && !a[i].disabled)
            return c
    }
    return null
}
function get_preferred_stylesheet() {
    return get_default_stylesheet();
    // var a = document.getElementsByTagName("link");
    // for (var i = 0; i < a.length; i++) {
    //     var b = a[i].getAttribute("rel");
    //     var c = a[i].getAttribute("title");
    //     if (b.indexOf("style") != -1 && b.indexOf("alt") == -1 && c)
    //         return c
    // }
    // return null
}
function get_default_stylesheet() {
    var a = document.getElementById("default_stylesheet");
    if (a)
        return a.getAttribute("title");
    return null;
}

if (style_cookie) {
	var cookie = getCookie(style_cookie);
	var title = cookie ? cookie : get_preferred_stylesheet();
	if (title != get_active_stylesheet()) {
		set_stylesheet(title)
	}
}