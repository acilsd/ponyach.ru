function passcode_icon() {
	if (document.getElementById('de-file-area')) {
		$("#de-file-area").append('<label for="modal-2"> <img id="passcode_icon" src="https://ponyach.ru/images/fsto.ico" /></label> ');
	}
}	

$(document).ready(function () {
	//standrat postbox
	$("#upload-image-1").after('<label for="modal-2"> <img id="passcode_icon" src="https://ponyach.ru/images/fsto.ico" /></label> ');
	setTimeout(passcode_icon, 900);
	
	$('#passcode_icon').click(function (){
		$('.versdb').hide();
	});
	
	$('#dbpic_st').click(function (){
		$('.versdb').show();
	});
	
	$('#dbpic_vi').click(function (){
		$('.versdb').show();
	});
	
	$('#passcodegetimages').click(function (){
		$('#passcodegetimages').hide();
		$.ajax({ type: "GET",   
			url: "/getdb.php?passcodeimages"
		, success: function(data) {
			$('#passcodeimages').append(data);
		}
		});
	});
	
	$('#passcode_search').keypress(function(e) {
		if(e.which == 13) {
			if ($(this).val().length > 0){
				selector = 'img[title*=\'' + $(this).val() + '\']';
				$('.passcode_image').hide('slow');
				$(selector).show('slow');
			} else {
				$('.passcode_image').show('slow');
			}

		}
	});
	
});

function insertmd5(){
	$('.passcode_image').click(function (){
		passcode_upload = true;
		for (i = 1; i < maximages;i++){
				if ($("#md5-"+i).val().length == 0 ) {
					$("#md5-"+i).val($(this).attr('id'));
					previewimage = document.createElement("img");
					previewimageParent = document.getElementById("preview_passcode_div");
					previewimage.className = "preview_passcode_image";
					previewimage.style.marginRight = "5px" 
					previewimage.src = $(this).attr('src');
					$("#preview_passcode_div").before(previewimage);
					break;
				}
		}
	});
}