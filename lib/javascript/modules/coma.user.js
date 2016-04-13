// ==UserScript==
// @name Модификация для Поняча: Coma
// @description Отмечает посты цветными квадратиками (цвет хитрым алгоритмом зависит от имени отправившего)
// @namespace dast
// @include http://ponyach.ru/*
// @include http://ponya.ch/*
// @include http://ponyach.cf/*
// @include http://ponyach.ga/*
// @include http://ponyach.ml/*
// @version 1.30
// @grant none
// ==/UserScript==

(function (parent){
    if (!parent)
        return;

    document.head.appendChild(document.createElement('style')).innerHTML = '.coma-colormark {\
        display: inline-block;\
        width: 16px;\
        height: 16px;\
        margin: 2px 2px -2px 2px;\
        border-radius: 5px;\
        border: 1px solid #aaaaaa;\
        background: white;\
    }';

    var color = {
        calculated: 0, 
        cache: {},
    
        set: function (seed){
            this.calculated = 0x4872d1e6;
            for (var i = 0, n = seed.length; i < n; i ++)
                this.calculated = (this.calculated << 1) + seed.charCodeAt(i);
            this.calculated = Math.abs(this.calculated);
            this.cache = {};
        },
        
        get: function (name){
            if (this.cache[name])
                return this.cache[name];

            if (name == 'Аноним')
                return 'transparent';
            
            var a = this.calculated % 0x1f4b, result = ['#'];
            function g(){
                for (var i = 0, b; i < name.length; i ++){
                    b = 0.02519603282416938 * (a += name.charCodeAt(i));
                    a = b >>> 0, b = (b - a) * a, a = b >>> 0;
                    a += (b - a) * 0x100000000;
                }
                return (a >>> 0) * 2.3283064365386963e-10;
            }
            
            for (var i = 0; i < 6; i ++)
                result.push('0123456789abcdef'[g() * 16 | 0]);
            return this.cache[name] = result.join('');
        }
    }

    function work(target, event){
        function createMark(name){
            var div = document.createElement('div');
            div.setAttribute('class', 'coma-colormark');
            if (name)
                div.style.background = color.get(name.trim());
            return div;
        }
        
        var unprocessed = document.querySelectorAll('.postername');
        for (var i = 0; i < unprocessed.length; i ++){
            var p = unprocessed[i].parentNode,
                n = p.querySelector('.postername').textContent.trim() || 
                    (p.querySelector('.postertrip') || {}).textContent || '';
           // p.insertBefore(createMark(n), unprocessed[i]);
		   if ($('.postername:eq(' + i + ')').parent().find('.coma-colormark').length == 0){
				$('.postername:eq(' + i + ')').parent().prepend(createMark(n))
		   }
			
        }
    }

    color.set('ЧgЖ0CгтWйwРstлХИyхoязХи9МymЫzLмz');
    work(document);

    new (window.MutationObserver || window.WebKitMutationObserver)(work).observe(
        parent, { subtree: true, attributes: true });    
})(document.querySelector('body > form > .pstnode'))