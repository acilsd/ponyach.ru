$(document).ready(function(){
	$('.show-popup').click(function(event){
		event.preventDefault();
		var docHeight = $(document).height();
		var scrollTop = $(window).scrollTop();
		var selectedPopup = $(this).data('showpopup');

		$('.overlay-bg').show().css({'height' : docHeight});
		$('.popup'+selectedPopup).show();
		$('.popup'+selectedPopup).css({'opacity': 1});
	});
 
	$('.close-btn').click(function(){
		$('.overlay-bg, .overlay-content').hide();
	});
 
	$('.overlay-bg').click(function(){
		$('.overlay-bg, .overlay-content').hide();
	})
	$('.overlay-content').click(function(){
		return false;
	});
 
});
