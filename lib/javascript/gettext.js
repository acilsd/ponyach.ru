var Utf8 = {encode: function(a) {
        a = a.replace(/\r\n/g, "\n");
        var b = "";
        for (var n = 0; n < a.length; n++) {
            var c = a.charCodeAt(n);
            if (c < 128) {
                b += String.fromCharCode(c)
            } else if ((c > 127) && (c < 2048)) {
                b += String.fromCharCode((c >> 6) | 192);
                b += String.fromCharCode((c & 63) | 128)
            } else {
                b += String.fromCharCode((c >> 12) | 224);
                b += String.fromCharCode(((c >> 6) & 63) | 128);
                b += String.fromCharCode((c & 63) | 128)
            }
        }
        return b
    },decode: function(a) {
        var b = "";
        var i = 0;
        var c = c1 = c2 = 0;
        while (i < a.length) {
            c = a.charCodeAt(i);
            if (c < 128) {
                b += String.fromCharCode(c);
                i++
            } else if ((c > 191) && (c < 224)) {
                c2 = a.charCodeAt(i + 1);
                b += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                i += 2
            } else {
                c2 = a.charCodeAt(i + 1);
                c3 = a.charCodeAt(i + 2);
                b += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                i += 3
            }
        }
        return b
    }};
	
function replaceAll(a, b, c) {
    var d = a.indexOf(b);
    while (d > -1) {
        a = a.replace(b, c);
        d = a.indexOf(b)
    }
    return a
}

function getCookie(name)
{
    with(document.cookie)
        {
		var regexp=new RegExp("(^|;\\s+)"+name+"=(.*?)(;|$)");
		var hit=regexp.exec(document.cookie);
		if(hit&&hit.length>2) return Utf8.decode(unescape(replaceAll(hit[2],'+','%20')));
		else return '';
	}
}

function setCookie(name,value,days) {
	if(days) {
		var date=new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires="; expires="+date.toGMTString();
	} else expires="";
	document.cookie=name+"="+value+expires+"; path=/";
}

function delCookie(name) {
	document.cookie = name +'=; expires=Thu, 01-Jan-70 00:00:01 GMT; path=/';
} 

Gettext=function(a){this.domain='messages';this.locale_data=undefined;var b=["domain","locale_data"];if(this.isValidObject(a)){for(var i in a){for(var j=0;j<b.length;j++){if(i==b[j]){if(this.isValidObject(a[i]))this[i]=a[i]}}}}this.try_load_lang();return this};Gettext.context_glue="\004";Gettext._locale_data={};Gettext.prototype.try_load_lang=function(){if(typeof(this.locale_data)!='undefined'){var a=this.locale_data;this.locale_data=undefined;this.parse_locale_data(a);if(typeof(Gettext._locale_data[this.domain])=='undefined'){throw new Error("Error: Gettext 'locale_data' does not contain the domain '"+this.domain+"'");}}var b=this.get_lang_refs();if(typeof(b)=='object'&&b.length>0){for(var i=0;i<b.length;i++){var c=b[i];if(c.type=='application/json'){if(!this.try_load_lang_json(c.href)){throw new Error("Error: Gettext 'try_load_lang_json' failed. Unable to exec xmlhttprequest for link ["+c.href+"]");}}else if(c.type=='application/x-po'){if(!this.try_load_lang_po(c.href)){throw new Error("Error: Gettext 'try_load_lang_po' failed. Unable to exec xmlhttprequest for link ["+c.href+"]");}}else{throw new Error("TODO: link type ["+c.type+"] found, and support is planned, but not implemented at this time.");}}}};Gettext.prototype.parse_locale_data=function(a){if(typeof(Gettext._locale_data)=='undefined'){Gettext._locale_data={}}for(var b in a){if((!a.hasOwnProperty(b))||(!this.isValidObject(a[b])))continue;var c=false;for(var d in a[b]){c=true;break}if(!c)continue;var e=a[b];if(b=="")b="messages";if(!this.isValidObject(Gettext._locale_data[b]))Gettext._locale_data[b]={};if(!this.isValidObject(Gettext._locale_data[b].head))Gettext._locale_data[b].head={};if(!this.isValidObject(Gettext._locale_data[b].msgs))Gettext._locale_data[b].msgs={};for(var f in e){if(f==""){var g=e[f];for(var i in g){var h=i.toLowerCase();Gettext._locale_data[b].head[h]=g[i]}}else{Gettext._locale_data[b].msgs[f]=e[f]}}}for(var b in Gettext._locale_data){if(this.isValidObject(Gettext._locale_data[b].head['plural-forms'])&&typeof(Gettext._locale_data[b].head.plural_func)=='undefined'){var j=Gettext._locale_data[b].head['plural-forms'];var k=new RegExp('^(\\s*nplurals\\s*=\\s*[0-9]+\\s*;\\s*plural\\s*=\\s*(?:\\s|[-\\?\\|&=!<>+*/%:;a-zA-Z0-9_\(\)])+)','m');if(k.test(j)){var l=Gettext._locale_data[b].head['plural-forms'];if(!/;\s*$/.test(l))l=l.concat(';');var m='var plural; var nplurals; '+l+' return { "nplural" : nplurals, "plural" : (plural === true ? 1 : plural ? plural : 0) };';Gettext._locale_data[b].head.plural_func=new Function("n",m)}else{throw new Error("Syntax error in language file. Plural-Forms header is invalid ["+j+"]");}}else if(typeof(Gettext._locale_data[b].head.plural_func)=='undefined'){Gettext._locale_data[b].head.plural_func=function(n){var p=(n!=1)?1:0;return{'nplural':2,'plural':p}}}}return};Gettext.prototype.try_load_lang_po=function(a){var b=this.sjax(a);if(!b)return;var c=this.uri_basename(a);var d=this.parse_po(b);var e={};if(d){if(!d[""])d[""]={};if(!d[""]["domain"])d[""]["domain"]=c;c=d[""]["domain"];e[c]=d;this.parse_locale_data(e)}return 1};Gettext.prototype.uri_basename=function(a){var b;if(b=a.match(/^(.*\/)?(.*)/)){var c;if(c=b[2].match(/^(.*)\..+$/))return c[1];else return b[2]}else{return""}};Gettext.prototype.parse_po=function(a){var b={};var c={};var d="";var e=[];var f=a.split("\n");for(var i=0;i<f.length;i++){f[i]=f[i].replace(/(\n|\r)+$/,'');var g;if(/^$/.test(f[i])){if(typeof(c['msgid'])!='undefined'){var h=(typeof(c['msgctxt'])!='undefined'&&c['msgctxt'].length)?c['msgctxt']+Gettext.context_glue+c['msgid']:c['msgid'];var j=(typeof(c['msgid_plural'])!='undefined'&&c['msgid_plural'].length)?c['msgid_plural']:null;var k=[];for(var l in c){var g;if(g=l.match(/^msgstr_(\d+)/))k[parseInt(g[1])]=c[l]}k.unshift(j);if(k.length>1)b[h]=k;c={};d=""}}else if(/^#/.test(f[i])){continue}else if(g=f[i].match(/^msgctxt\s+(.*)/)){d='msgctxt';c[d]=this.parse_po_dequote(g[1])}else if(g=f[i].match(/^msgid\s+(.*)/)){d='msgid';c[d]=this.parse_po_dequote(g[1])}else if(g=f[i].match(/^msgid_plural\s+(.*)/)){d='msgid_plural';c[d]=this.parse_po_dequote(g[1])}else if(g=f[i].match(/^msgstr\s+(.*)/)){d='msgstr_0';c[d]=this.parse_po_dequote(g[1])}else if(g=f[i].match(/^msgstr\[0\]\s+(.*)/)){d='msgstr_0';c[d]=this.parse_po_dequote(g[1])}else if(g=f[i].match(/^msgstr\[(\d+)\]\s+(.*)/)){d='msgstr_'+g[1];c[d]=this.parse_po_dequote(g[2])}else if(/^"/.test(f[i])){c[d]+=this.parse_po_dequote(f[i])}else{e.push("Strange line ["+i+"] : "+f[i])}}if(typeof(c['msgid'])!='undefined'){var h=(typeof(c['msgctxt'])!='undefined'&&c['msgctxt'].length)?c['msgctxt']+Gettext.context_glue+c['msgid']:c['msgid'];var j=(typeof(c['msgid_plural'])!='undefined'&&c['msgid_plural'].length)?c['msgid_plural']:null;var k=[];for(var l in c){var g;if(g=l.match(/^msgstr_(\d+)/))k[parseInt(g[1])]=c[l]}k.unshift(j);if(k.length>1)b[h]=k;c={};d=""}if(b[""]&&b[""][1]){var m={};var n=b[""][1].split(/\\n/);for(var i=0;i<n.length;i++){if(!n.length)continue;var o=n[i].indexOf(':',0);if(o!=-1){var p=n[i].substring(0,o);var q=n[i].substring(o+1);var r=p.toLowerCase();if(m[r]&&m[r].length){e.push("SKIPPING DUPLICATE HEADER LINE: "+n[i])}else if(/#-#-#-#-#/.test(r)){e.push("SKIPPING ERROR MARKER IN HEADER: "+n[i])}else{q=q.replace(/^\s+/,'');m[r]=q}}else{e.push("PROBLEM LINE IN HEADER: "+n[i]);m[n[i]]=''}}b[""]=m}else{b[""]={}}return b};Gettext.prototype.parse_po_dequote=function(a){var b;if(b=a.match(/^"(.*)"/)){a=b[1]}a=a.replace(/\\"/,"");return a};Gettext.prototype.try_load_lang_json=function(a){var b=this.sjax(a);if(!b)return;var c=this.JSON(b);this.parse_locale_data(c);return 1};Gettext.prototype.get_lang_refs=function(){var a=new Array();var b=document.getElementsByTagName("link");for(var i=0;i<b.length;i++){if(b[i].rel=='gettext'&&b[i].href){if(typeof(b[i].type)=='undefined'||b[i].type==''){if(/\.json$/i.test(b[i].href)){b[i].type='application/json'}else if(/\.js$/i.test(b[i].href)){b[i].type='application/json'}else if(/\.po$/i.test(b[i].href)){b[i].type='application/x-po'}else if(/\.mo$/i.test(b[i].href)){b[i].type='application/x-mo'}else{throw new Error("LINK tag with rel=gettext found, but the type and extension are unrecognized.");}}b[i].type=b[i].type.toLowerCase();if(b[i].type=='application/json'){b[i].type='application/json'}else if(b[i].type=='text/javascript'){b[i].type='application/json'}else if(b[i].type=='application/x-po'){b[i].type='application/x-po'}else if(b[i].type=='application/x-mo'){b[i].type='application/x-mo'}else{throw new Error("LINK tag with rel=gettext found, but the type attribute ["+b[i].type+"] is unrecognized.");}a.push(b[i])}}return a};Gettext.prototype.textdomain=function(a){if(a&&a.length)this.domain=a;return this.domain};Gettext.prototype.gettext=function(a){var b;var c;var n;var d;return this.dcnpgettext(null,b,a,c,n,d)};Gettext.prototype.dgettext=function(a,b){var c;var d;var n;var e;return this.dcnpgettext(a,c,b,d,n,e)};Gettext.prototype.dcgettext=function(a,b,c){var d;var e;var n;return this.dcnpgettext(a,d,b,e,n,c)};Gettext.prototype.ngettext=function(a,b,n){var c;var d;return this.dcnpgettext(null,c,a,b,n,d)};Gettext.prototype.dngettext=function(a,b,c,n){var d;var e;return this.dcnpgettext(a,d,b,c,n,e)};Gettext.prototype.dcngettext=function(a,b,c,n,d){var e;return this.dcnpgettext(a,e,b,c,n,d,d)};Gettext.prototype.pgettext=function(a,b){var c;var n;var d;return this.dcnpgettext(null,a,b,c,n,d)};Gettext.prototype.dpgettext=function(a,b,c){var d;var n;var e;return this.dcnpgettext(a,b,c,d,n,e)};Gettext.prototype.dcpgettext=function(a,b,c,d){var e;var n;return this.dcnpgettext(a,b,c,e,n,d)};Gettext.prototype.npgettext=function(a,b,c,n){var d;return this.dcnpgettext(null,a,b,c,n,d)};Gettext.prototype.dnpgettext=function(a,b,c,d,n){var e;return this.dcnpgettext(a,b,c,d,n,e)};Gettext.prototype.dcnpgettext=function(a,b,c,d,n,e){if(!this.isValidObject(c))return'';var f=this.isValidObject(d);var g=this.isValidObject(b)?b+Gettext.context_glue+c:c;var h=this.isValidObject(a)?a:this.isValidObject(this.domain)?this.domain:'messages';var k='LC_MESSAGES';var e=5;var l=new Array();if(typeof(Gettext._locale_data)!='undefined'&&this.isValidObject(Gettext._locale_data[h])){l.push(Gettext._locale_data[h])}else if(typeof(Gettext._locale_data)!='undefined'){for(var m in Gettext._locale_data){l.push(Gettext._locale_data[m])}}var o=[];var q=false;var r;if(l.length){for(var i=0;i<l.length;i++){var s=l[i];if(this.isValidObject(s.msgs[g])){for(var j=0;j<s.msgs[g].length;j++){o[j]=s.msgs[g][j]}o.shift();r=s;q=true;if(o.length>0&&o[0].length!=0)break}}}if(o.length==0||o[0].length==0){o=[c,d]}var t=o[0];if(f){var p;if(q&&this.isValidObject(r.head.plural_func)){var u=r.head.plural_func(n);if(!u.plural)u.plural=0;if(!u.nplural)u.nplural=0;if(u.nplural<=u.plural)u.plural=0;p=u.plural}else{p=(n!=1)?1:0}if(this.isValidObject(o[p]))t=o[p]}return t};Gettext.strargs=function(a,b){if(null==b||'undefined'==typeof(b)){b=[]}else if(b.constructor!=Array){b=[b]}var c="";while(true){var i=a.indexOf('%');var d;if(i==-1){c+=a;break}c+=a.substr(0,i);if(a.substr(i,2)=='%%'){c+='%';a=a.substr((i+2))}else if(d=a.substr(i).match(/^%(\d+)/)){var e=parseInt(d[1]);var f=d[1].length;if(e>0&&b[e-1]!=null&&typeof(b[e-1])!='undefined')c+=b[e-1];a=a.substr((i+1+f))}else{c+='%';a=a.substr((i+1))}}return c};Gettext.prototype.strargs=function(a,b){return Gettext.strargs(a,b)};Gettext.prototype.isArray=function(a){return this.isValidObject(a)&&a.constructor==Array};Gettext.prototype.isValidObject=function(a){if(null==a){return false}else if('undefined'==typeof(a)){return false}else{return true}};Gettext.prototype.sjax=function(a){var b;if(window.XMLHttpRequest){b=new XMLHttpRequest()}else if(navigator.userAgent.toLowerCase().indexOf('msie 5')!=-1){b=new ActiveXObject("Microsoft.XMLHTTP")}else{b=new ActiveXObject("Msxml2.XMLHTTP")}if(!b)throw new Error("Your browser doesn't do Ajax. Unable to support external language files.");b.open('GET',a,false);try{b.send(null)}catch(e){return}var c=b.status;if(c==200||c==0){return b.responseText}else{var d=b.statusText+" (Error "+b.status+")";if(b.responseText.length){d+="\n"+b.responseText}alert(d);return}};Gettext.prototype.JSON=function(a){return eval('('+a+')')}

