
add_fake_ratings();
function hidecheckboxes (){
	$('input[info="postSameImg"]').parent().hide();
	$('input[info="removeEXIF"]').parent().hide();
	$('input[info="removeFName"]').parent().hide();
}
function delaybind(){
	$('#de-btn-settings').click(function() {
		$('.de-cfg-tab-back:eq(4)').click(function() {
			hidecheckboxes();
		});
	});
}
//wait for dollchan done loading
setTimeout(delaybind, 3000);
