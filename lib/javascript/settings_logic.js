!function (){
	var classes = {
		s: function (s){    return ' ' + s.match (/[^\s]+|$/g).join (' ') },
		h: function (e, c){ return this.s (e.className).indexOf (this.s (c)) != -1 },
		a: function (e, c){ return !this.h (e, c) && (e.className = e.className.match (/[^\s]+|$/g).join (' ') + c) },
		r: function (e, c){ e.className = this.s (e.className).replace (c, '').match (/[^\s]+/g).join (' ') },
		t: function (e, c){ if (!this.a (e, c)) this.r (e, c) }
	};
	
	var each = function (c, fn){ [].forEach.call (document.getElementsByClassName (c), fn) }

	each ('nomagic-switcher', function (a){
		a.addEventListener ('click', function (e){
			classes.t (document.getElementsByClassName ('nomagic-switcher')[0], 'nomagic-active');
			if (e.preventDefault)
				e.preventDefault ();
		})
	});

	each ('nomagic-header', function (a){
		a.addEventListener ('click', function (){
			each ('nomagic-header', function (a){ classes.r (a, 'nomagic-active') });
			classes.a (this, 'nomagic-active');
		})
	});
}();