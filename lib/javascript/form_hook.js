  function on_save_rating() {
    if ($("#settings-upload-rating")) {
        setCookie('rating', $("#settings-upload-rating").val(), 36500);
    }
  }

  function set_default_ratings() {
    var i = 1;
    while(i <= maximages) {
      if ($("#upload-rating-"+(i))) {
        if($("#upload-rating-"+(i)).val == false || $("#upload-rating-"+(i)).val == '') {
          $(("#upload-rating-")+(i)).val(getCookie("rating"));
        }
      }

      if ($("#fake-rating-"+(i))) {
        if($("#fake-rating-"+(i)).val == false || $("#upload-rating-"+(i)).val == '') {
          $(("#fake-rating-")+(i)).val(getCookie("rating"));
        }
      }

      $i++;
    }
  }

  function handleFileSelect(x) {
    
    var y = x + 1;
    if ($("#upload-image-"+y)) {
    	if (! $("#upload-image-"+y).is(':visible')) {
            if (! document.getElementById("upload-bred-"+y)) {
              $("#upload-rating-"+y).before('<span id="upload-bred-'+y+'"><br></span>');
            }
    	    $("#upload-image-"+y).fadeIn();
    	    $("#upload-rating-"+y).fadeIn();
    	}
    }

    add_fake_ratings();
    $("#upload-rating-"+x).val(getCookie("rating"));
    safe_fake_rating_set_default(x);
	
	if (document.getElementById('upload-image-' + x).files[0]){
		f = document.getElementById('upload-image-' + x).files[0];
		var ext = f.type.split('/');
	}
    var msg = '';

    //document.postform.submit.disabled = false;
	$("#go").prop('disabled', false)
    if (ext[1] !== "jpeg" && ext[1] !== "png" && ext[1] !== "gif" && ext[1] !== "webm" && f != ''){
      msg = "Недопустимый формат [" + ext[1] + "]";
      //document.postform.submit.disabled = true;
	  $("#go").prop('disabled', true);
    }else{
      if (f.size > max_file_size) {
        msg = "Слишком большой файл [" + f.size + "b]";
        //document.postform.submit.disabled = true;
		$("#go").prop('disabled', true);
      }
    }
    //document.postform.file_error.innerHTML = "<br />" + msg;
	
	//error handle
	if ($('#brbefore').length == 0){
		$('#msgbox').after('<br id="brbefore">');
	}
	$('#file_error').text(msg);
	if ($('#brafter').length == 0) {
		$('#file_error').after('<br id="brafter">');
	}
	
	if (msg.length == 0){
		$('#brbefore').remove();
	}
	
    if (document.getElementById('upload-image-' + x).files[0]){
      //document.postform.file_clear.style.display = "";
      $("#file-clear-"+x).fadeIn();
      $("#md5-"+(x)).val('');
      get_md5(x);
    }
  }

  function file_form_clear(x) {
    document.getElementById('upload-image-' + x).value = '';
    document.postform.submit.disabled = false;
    document.postform.file_error.innerHTML = "";
    //document.postform.file_clear.style.display = "none";
	$("#pp"+x).trigger("click");
    hide_clear(x);
  }

  function hide_clear(x) {
    $("#file-clear-"+x).hide();
  }

  function clear_md5() {
    var i = 1;
    while(i <= maximages) {

      if ($("#upload-image-"+(i))) {
    	  $("#md5-"+(i)).val('');
		  $('#md5passcode-'+i).val('');
      } else break;
      i++;
    }
  }


  function hide_clear_all() {
    var i = 2;
    while(i <= maximages) {

      if ($("#upload-image-"+(i))) {
//      	if ($("#upload-image-"+(i)).is(':visible')) {
          $("#file-clear-"+i).hide();
    	  $("#upload-image-"+(i)).hide();
    	  //$("#upload-image-"+(i)).val('');
    	  //$("#md5-"+(i)).val('');
    	  $("#upload-rating-"+(i)).hide();
          $("#upload-bred-"+(i)).remove();
//        }
      } else break;
      i++;
    }
  }
  
  function get_md5(x){
  // var isChromium = window.chrome,
    // vendorName = window.navigator.vendor;
	// if(isChromium !== null && isChromium !== undefined && vendorName === "Google Inc.") {
		// //nothing
	// } else {
		var file = document.getElementById('upload-image-'+x).files[0];
		var reader = new FileReader();
		
		//allow only .jpg and .png
		var extension = file.name.split('.').pop().toLowerCase();
		if (extension != 'jpg' && extension != 'jpeg' && extension != 'png' && extension != 'gif') return false;

		reader.onloadend = function(evt) {
		  if (evt.target.readyState == FileReader.DONE) {
			var filestring = evt.target.result;
			var stringLength = filestring.length;
			var i = 1;
			var lastChar = filestring.charAt(stringLength - i); 
			if (!isNaN(lastChar)) { // is number
				do {
					i++;
					lastChar = filestring.charAt(stringLength - i); 
				} while (!isNaN(lastChar));
				filestring = filestring.substring(0, stringLength - i);
			}

			var md5 = rstr2hex(rstr_md5(filestring));
			//console.log('md5-' + x + ' = ' + md5);

			var xmlHttp = null;
			xmlHttp = new XMLHttpRequest();
		xmlHttp.onreadystatechange = function(){
					if (xmlHttp.readyState === 4) {
							if ( xmlHttp.status === 200) {
						if (xmlHttp.responseText == 'true')
							$("#md5-"+x).val(md5);
							}
					}
			}
			xmlHttp.open( "GET", '/chkmd5.php?x=' + md5, true );
			xmlHttp.send( null );
			//always send sha512 of file for passcode records
			//field will be sent only if user have cookie with real passcode
			$("#md5passcode-"+x).val(md5);
		  }
		};
		str = reader.readAsBinaryString(file);
	// }
  }

  //def passcode_upload
$(document).ready(function () {
	passcode_upload = false;
});

function chk_same_file() {
    var i = 1;
    while(i <= maximages) {

      if ($("#upload-image-"+(i))) {
		//checking if #upload-image-i exist with javascript, cuz jquery check always return true
		if (document.getElementById('upload-image-'+i)) {
        if (document.getElementById('upload-image-'+i).value == '') {
			var desu = $('#md5-'+i).val();
			//checking if derpibooru md5 
			//if not [derpi] md5 -- clear md5 forms 
			if(!/[^[\]]+(?=])/i.test(desu)) {
			//working with passcode uploader md5. if passcode_upload = true passing cleanup
				if (!passcode_upload) {
					$("#md5-"+i).val('');
				}	
			} 
		}
        if ($("#md5-"+i).val().length > 0)
          document.getElementById('upload-image-'+i).value = '';
        }
      i++;
	  }
    }
  }

  
//derpibooru stuff 
 
function xfake_rating_change(x) {
   $("#upload-rating-1").val($("#xfake-rating-1").val());
}

function clear_xfake_rating(){
    $(".xfake-rating").val(0);
}

function dbclear() {
			for (i=1;i<6;i++) {
				$("#md5-"+i).val('');
				$("#pp"+i).hide("slow");
				if (document.getElementById("pp"+i)){
					document.getElementById("pp"+i).parentNode.removeChild(document.getElementById("pp"+i));
				}
				$("#xfake-rating-"+i).hide("slow");
				$("#replace"+i).hide();
				$("#upload-image-"+i).show();
			}		
}

function filetodb() { 
			var imgsw = document.createElement("img");
			var imgswParent = document.getElementById("prepreview");
			imgsw.className = "prepreviewimage";
			imgsw.style.marginRight = "5px";
			imgsw.style.display = "none";
			imgsw.src = "/images/pickeddb.png";
			imgsw.id = "rmv";
            $("#cloneimage").append(imgsw);
			
			$( "#upload-image-1" ).change(function() {
				if ($( "#upload-image-1" ).val().length > 1) {
					if(!document.getElementById("pp1")) {
						$("#rmv").clone().appendTo("#prepreviewspan1");
						if (document.getElementsByClassName('prepreviewimage')[0]) {
							document.getElementsByClassName('prepreviewimage')[0].id = "pp1";
						}
						$("#textpp").show("slow");
						$("#textpp").css("display", "block");
						$("#pp1").show();
						bindpreview();
						showdiv();
					}
				} else {
					//$("#pp1").hide();
					//document.getElementById("pp1").parentNode.removeChild(document.getElementById("pp1")); 
				}
			});
			$( "#upload-image-2" ).change(function() {
				if ($( "#upload-image-2" ).val().length > 1) {
					if(!document.getElementById("pp2")) {
						$("#rmv").clone().appendTo("#prepreviewspan2");
						if (document.getElementsByClassName('prepreviewimage')[1]) {
							document.getElementsByClassName('prepreviewimage')[1].id = "pp2";
						}
						$("#pp2").show();
						bindpreview();
						showdiv();
					}
				} else {
					//$("#pp2").hide();
					//document.getElementById("pp2").parentNode.removeChild(document.getElementById("pp2"));
				}
			});
			$( "#upload-image-3" ).change(function() {
				if ($( "#upload-image-3" ).val().length > 1) {
					if(!document.getElementById("pp3")) {
						$("#rmv").clone().appendTo("#prepreviewspan3");
						if (document.getElementsByClassName('prepreviewimage')[2]) {
							document.getElementsByClassName('prepreviewimage')[2].id = "pp3";
						}
						$("#pp3").show();
						bindpreview();
						showdiv();
					}
				} else {
					//$("#pp3").hide();
					//document.getElementById("pp3").parentNode.removeChild(document.getElementById("pp3"));
				}
			});
			$( "#upload-image-4" ).change(function() {
				if ($( "#upload-image-4" ).val().length > 1) {
					if(!document.getElementById("pp4")) {
						$("#rmv").clone().appendTo("#prepreviewspan4");
						if (document.getElementsByClassName('prepreviewimage')[3]) {
							document.getElementsByClassName('prepreviewimage')[3].id = "pp4";
						}
						$("#pp4").show();
						bindpreview();
						showdiv();
					}
				} else {
					//$("#pp4").hide();
					//document.getElementById("pp4").parentNode.removeChild(document.getElementById("pp4"));
				}
			});
			$( "#upload-image-5" ).change(function() {
				if ($( "#upload-image-5" ).val().length > 1) {
					if(!document.getElementById("pp5")) {
						$("#rmv").clone().appendTo("#prepreviewspan5");
						if (document.getElementsByClassName('prepreviewimage')[4]) {
							document.getElementsByClassName('prepreviewimage')[4].id = "pp5"
						}
						$("#pp5").show();
						bindpreview();
						showdiv();
					}
				} else {
					//$("#pp5").hide();
					//document.getElementById("pp5").parentNode.removeChild(document.getElementById("pp5"));
				}
			});
		bindpreview();
}

function rebindfiles() {
	for (i=0;i<5;i++){
		$("#upload-image-"+i).bind('click', function(){
			filetodb();
			});
	}
}
	$(document).ready(function () {
		filetodb();
		bindpreview();
	});

//end

//hide first [x] button. wtf?
function hidefirstx() {
	if (!document.getElementById('upload-image-1').files[0] && $("#md5-1").val().length == 0 ) {
		$("#file-clear-1").hide();
	}
}

//hide passcode_image_preview images if exist
function clear_passcode_preview(){
	if (document.getElementsByClassName('preview_passcode_image').length > 0) {
		$('.preview_passcode_image').remove();
	}
}

function postform_submit() {
  setTimeout('haiku_check()', 8000);
  clear_fake_rating();
  chk_same_file(); 

  //var frm = document.getElementsByName('postform')[0];
  hide_clear_all(); 
 // frm.submit();
  $("#fake_go").trigger( "click" );
  clear_md5();
  clear_fake_rating();
  fake_ratings_set_default();
  
  //derpibooru stuff 
  clear_xfake_rating();
  dbclear();
  setTimeout(filetodb, 9000);
  //end
  
  //passcode upload md5 stuff
  passcode_upload = false;
  clear_passcode_preview();
  //end
  hidefirstx();
  del_edit_field();

//  var p = document.getElementById('edit_post_num');
//  if(p !== null) {
//    document.getElementById('reply'+p.value).remove();
//  }
  return false;
}

function fake_rating_change(x) {
    $("#upload-rating-"+x).val($("#fake-rating-"+x).val());
}

function fake_ratings_set_default(x) {
    $(".fake-rating").val(getCookie("rating"));
}

// preserve existing shit
function safe_fake_rating_set_default(x) {
    var def = getCookie("rating");
    if (def) {
    if ($("#fake-rating-"+x)) {
//        if($("#fake-rating-"+(x)).val == false || $("#fake-rating-"+(x)).val == '') {
	if(!$("#fake-rating-"+(x)).val()) {
        	$("#fake-rating-"+x).val(def);
	}
    }
    }
}

function clear_fake_rating(){
    $(".fake-rating").val(0);
}

function create_pick_spoiler(x) {
    return '<span id="sp-fake-rating-'+x+'"><br><select class="fake-rating" id="fake-rating-'+x+'" onchange="fake_rating_change('+x+');" accesskey="r" name="fake_rating_'+x+'"><option value=""> </option><option value="10">[C]</option><option value="9">[S]</option><option value="11">[A]</option></select></span>';
}

function fix_css() {
	$('#de-file-area').css('overflow-y', 'visible');
	$('#de-file-area').css('overflow-x', 'visible');
}

function add_fake_rating(x) {
    if (!$("#fake-rating-"+x)) {
        $(".img.de-file-img").last().before(create_pick_spoiler(x));
        safe_fake_rating_set_default(x);
    }
    fix_css();
}

function add_fake_ratings() {
	var i = 1;
	$(".de-file-off").each(function() {
		var div = $(this);
		if (div.html().indexOf('rating') < 0) {
			div.html(div.html() + create_pick_spoiler(i));
        		safe_fake_rating_set_default(i);
			i++;
		}

	});
	fix_css();
}

function add_edit_field(a) {
	del_edit_field();
	$('#msgbox').before(
		'<span id="edit_post" >Редактировать пост #'+a+' <a href="javascript:void(0);" onclick="del_edit_field('+a+');">[x]</a><input type="hidden" name="editpost" id="edit_post_num" value="'+a+'" />'
	);
}

function del_edit_field() {
	$('#edit_post').remove();
}
