/* Fix search image buttons */

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

(function(){
    $('.pstnode > .reply').each(function() {
        show_filesize($(this).attr('data-num'), 1);
    });
})();