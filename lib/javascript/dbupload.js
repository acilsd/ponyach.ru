			var page = 1;
			var actualpage = 1;
			var usedq;
			var versiondb = "2.0.0";
			
			//getting json from derpibooru
			$(document).ready(function () {
				infodb = $("#infodb");
				if (!document.getElementById('versdb')){
					$(".modal__inner").prepend('<span class="versdb" style="top: 10px; position: absolute; right: 3%;">' + versiondb + '</span>');
				}
				//appending clear button
				clearbutton = '<a href="javascript:void(0)" id="clearapikey" onclick="clearask();" style="text-decoration: none; top:10px; position:absolute; right:6%;">Очистить API Key</a>';  
				$('#versdb').after(clearbutton);
				$('#clearapikey').click(function() {
					alertify.log("Ключ сброшен");
				});
				
				//clear ls
				for (i=1;i<9999;i++){
					localStorage.removeItem(i);
				}
				
				//main stuff 
				$('#dbpage').keyup(function() {
					page = $(this).val();
				});
				
				$('#dbpage').keypress(function(e) {
					if(e.which == 13) {
						sqo = $('#dbsearch').val();
						if (document.getElementById("imgdev"+actualpage)){
							$("#imgdev"+actualpage).hide("slow");
							setTimeout(function() {$('#imgdev'+actualpage).remove(); }, 800);
						}
						
						if (sqo.length > 0){
							updatesq(page);
						} else {
							infodb.empty().append("Enter search tags")
						}
					}
				});
		
				$('#dbsearch').keypress(function(e) {
					if(e.which == 13) {
						sqo = $(this).val();
						updatesq(page);
					}
				});
				
				$('#dbsearch').keyup(function () {
					if ($('#dbpage').is(':hidden')) {
						$('#dbpage').show('slow');
					}
				});
            });
			
			function countq(pagex) {
				var pagex = pagex +1;
				for (i=0;i<pagex;i++){
					localStorage.setItem(i, i);
				}
			}
			
			function updatesq(pagezz) {
				apikey = getCookie('userkey');
				if (apikey.length < 20) {
					apikey = "";
				}
				actualpage = pagezz;
				if (usedq !== sqo) {
					if (document.getElementById("imgdev"+actualpage)){
						$("#imgdev"+actualpage).remove();
					}
					$.ajax({ type: "GET",   
						url: "/getdb.php?q=" + sqo.replace(/\s/g, '%2B') + "&page=" + pagezz + "&apikey=" + apikey
					, success: function(data) {
						getlinks(data, page);
						usedq = sqo;
						localStorage.setItem(pagezz, data)
					}
					});
				} else {
					$.ajax({ type: "GET",   
						url: "/getdb.php?q=" + sqo.replace(/\s/g, '%2B') + "&page=" + pagezz + "&apikey=" + apikey
					, success: function(data) {
						getlinks(data, page);
						usedq = sqo;
						localStorage.setItem(pagezz, data)
					}
					});
				}
			}
				   
			function updateactualquery(pageact) {
				jr = localStorage.getItem(pageact);
				jr = JSON.parse(jr);
			}
	
			//building preview with thumbs
			function getlinks(response, pageqq) {
				jr = JSON.parse(response);
				jrl = jr.search.length;
				prevpage = pageqq -1;
				if (response.length > 200) {
				
					if (document.getElementById("imgdev" + prevpage)) {
						$('#imgdev' + prevpage).hide("slow");
					}
					
					if (!document.getElementById("imgdev" + pageqq)) {
						var imgpar = document.getElementById("imagesgoeshere");
						var d=document.createElement('div');
						d.className = 'imgdiv';
						d.id = 'imgdev' + pageqq;
						imgpar.appendChild(d);

						for (i=0;i<jrl;i++){
							if (jr.search[i].representations){
							thumb = jr.search[i].representations.thumb;
							}
							clearthumb = thumb.replace(/\/\//g,"");
							image = document.createElement("img");
							imageParent = document.getElementById("imgdev" + pageqq);
							image.className = "imagedb";
							image.id = [i];
							image.style.display = 'none';
							image.src = "http://" + clearthumb;
							imageParent.appendChild(image);
						}
						setTimeout('$(".imagedb").show("slow")', 900);
						setTimeout(bindlinks, 1000);
					}
					
					actualpage = pageqq;
					prevpage = actualpage -1;
					nextpage = actualpage +1;
					page = Number(page);
					setTimeout('infodb.empty()', 500);
					
					if (actualpage > 1){
						setTimeout("$('#prev').show('slow')", 1200);
						setTimeout("$('.prevbutton').show('slow')", 1200);
					}
					
					if (actualpage){
						setTimeout("$('#next').show('slow')", 1200);
						setTimeout("$('.nextbutton').show('slow')", 1200);
					}
					
				
				} else {
					setTimeout('infodb.empty().append("Wrong request")', 500);
				}
			}
			
			function showdiv() {
				if (!document.getElementById('pp1') && !document.getElementById('pp2') && !document.getElementById('pp3') && !document.getElementById('pp4') && !document.getElementById('pp5')) {
					$("#prepreview").hide();			
				}
				if (document.getElementById('pp1') || document.getElementById('pp2') || document.getElementById('pp3') || document.getElementById('pp4') || document.getElementById('pp5')) {
					$("#prepreview").show();			
				}
			}
			
			function upc() {
				prevpage = actualpage -1;
				nextpage = actualpage +1;
			}
			
			function prev(){
				if (actualpage > 1) {
					if (document.getElementById('previmgdev'+prevpage)){
					
						$("#imgdev"+actualpage).unbind('click');		
						$("#imgdev" + actualpage).hide("slow");
						$("#previmgdev" + prevpage).show("slow");
						
						if (document.getElementById('previmgdev' + prevpage)){
								imglength = $("#previmgdev"+prevpage).children('img').length
								for (i=0;i<imglength;i++) {
									document.getElementById('previmgdev'+prevpage).getElementsByClassName('imagedb')[i].setAttribute('id', i)
								}
								$("#previmgdev" + prevpage).attr({"id": "imgdev"+prevpage, "class": "imgdiv"});
						}
					
						imglength = $("#imgdev"+actualpage).children('img').length
						for (i=0;i<imglength;i++) {
							document.getElementById('imgdev'+actualpage).getElementsByClassName('imagedb')[i].setAttribute('id', 'nextimageid'+i)
						}
						$("#imgdev" + actualpage).attr({"id": "nextimgdev"+actualpage, "class": "nextimgdiv"+actualpage});	
						
						page = page -1;
						page = Number(page);
						actualpage = actualpage -1;
						
						$('#dbpage').val(actualpage);
						upc();
						setTimeout(bindlinks, 1000);
						updateactualquery(actualpage);
						
					} else {
						$("#imgdev"+actualpage).unbind('click');		
						$("#imgdev" + actualpage).hide("slow");
						
						imglength = $("#imgdev"+actualpage).children('img').length
						for (i=0;i<imglength;i++) {
							document.getElementById('imgdev'+actualpage).getElementsByClassName('imagedb')[i].setAttribute('id', 'nextimageid'+i)
						}
						$("#imgdev" + actualpage).attr({"id": "nextimgdev"+actualpage, "class": "nextimgdiv"+actualpage});	
						
						page = Number(page);
						page = page -1;
						actualpage = page;
						page = Number(page);
						updatesq(page);
						$('#dbpage').val(page);
						upc();
						
					}
				}
			}
			
			function next(){
				if (document.getElementById("imgdev"+actualpage)){
					$("#imgdev"+actualpage).hide("slow");
					$("#imgdev"+actualpage).unbind('click');
					
					
					function showfooter() {
						if (document.getElementById('imgdev'+actualpage)){
							if ($("#imgdev"+actualpage).is(':hidden')) {
								setTimeout("$('.prevbutton').show('slow')", 1100);
								setTimeout("$('.nextbutton').show('slow')", 1100);
							} else {
								setTimeout("$('.prevbutton').show('slow')", 100);
								setTimeout("$('.nextbutton').show('slow')", 100);
							}
						} else {
							setTimeout(showfooter, 500);
						}
					}
					showfooter();
					
					
					imglength = $("#imgdev"+actualpage).children('img').length
					for (i=0;i<imglength;i++) {
						document.getElementById('imgdev'+actualpage).getElementsByClassName('imagedb')[i].setAttribute('id', 'previmageid'+i)
					}
					$("#imgdev" + actualpage).attr({"id": "previmgdev"+actualpage, "class": "previmgdiv"+actualpage});	
					
					if (document.getElementById('nextimgdev' + nextpage)){
						imglength = $("#nextimgdev"+nextpage).children('img').length
						for (i=0;i<imglength;i++) {
							document.getElementById('nextimgdev'+nextpage).getElementsByClassName('imagedb')[i].setAttribute('id', i)
						}
						$("#nextimgdev" + nextpage).attr({"id": "imgdev"+nextpage, "class": "imgdiv"});
					}
					
					
					if (document.getElementById('imgdev' + nextpage)){
						$("#imgdev" + nextpage).show("slow");
						actualpage = actualpage +1;
						page = page +1;
						page = Number(page);
						updateactualquery(actualpage);
					} 
					else {
						page = Number(page);
						page = page +1;
						actualpage = page;
						page = Number(page);
						updatesq(page);
					}
					$('#dbpage').val(page);
					upc();
					
					setTimeout(bindlinks, 1000);
					$('.prevbutton').hide();
					$('.nextbutton').hide();
					
				} 
			} 
			
			//adding selected images in md5 fields and preview area
			//i know it's not good enough
			function bindlinks(){
				$("#imgdev"+actualpage).delegate("img", "click", function(){
					tmpnum = $(this).attr("id");
					
					ppvi = document.createElement("img");
					ppviParent = document.getElementById("prepreview");
					ppvi.className = "prepreviewimage";
					ppvi.style.marginRight = "5px" 
					ppvi.src = "http://" + jr.search[tmpnum].representations.thumb_small.replace(/\/\//g,"");
					
					
					if ($("#md5-4").val().length > 0 ) {
					if ($("#md5-5").val().length == 0 ) {
						if (!document.getElementById('upload-image-5').files[0]) {
						$("#md5-5").val("[derpi]" + btoa(jr.search[tmpnum].representations.full.replace(/^\/\/[a-z0-9\.]+\//g,""))); 
						ppvi.id = "pp5";
						$("#xfake-rating-5").before(ppvi);
						$("#pp5").append('<br>')
						$("#xfake-rating-5").show("slow");
						$("#upload-rating-5").val('');
						bindpreview();
						$("#upload-image-5").hide("slow");
						if (document.getElementById("replace5")){
							$("#replace5").show();
						} else {
							$("#file-clear-5").after('<span id="replace5"> Выбран файл с Дерпибуры<br></span>');
						}
						$( "#xfake-rating-5" ).change(function() {
								$("#upload-rating-5").val($("#xfake-rating-5").val());
						});
						$("#file-clear-5").fadeIn();
						showdiv();
						handleFileSelect(5);
						}
					}}
					
					if ($("#md5-3").val().length > 0 ) {
					if ($("#md5-4").val().length == 0 ) {
						if (!document.getElementById('upload-image-4').files[0]) {
						$("#md5-4").val("[derpi]" + btoa(jr.search[tmpnum].representations.full.replace(/^\/\/[a-z0-9\.]+\//g,"")));
						ppvi.id = "pp4";
						$("#xfake-rating-4").before(ppvi);
						$("#pp4").append('<br>')
						$("#xfake-rating-4").show("slow");
						$("#upload-rating-4").val('');
						bindpreview();
						$("#upload-image-4").hide("slow");
						if (document.getElementById("replace4")){
							$("#replace4").show();
						} else {
							$("#file-clear-4").after('<span id="replace4"> Выбран файл с Дерпибуры<br></span>');
						}
						$( "#xfake-rating-4" ).change(function() {
								$("#upload-rating-4").val($("#xfake-rating-4").val());
						});
						$("#file-clear-4").fadeIn();
						showdiv();
						handleFileSelect(4);
						}
					}}
					
					if ($("#md5-2").val().length > 0 ) {
					if ($("#md5-3").val().length == 0 ) {
						if (!document.getElementById('upload-image-3').files[0]) {
						$("#md5-3").val("[derpi]" + btoa(jr.search[tmpnum].representations.full.replace(/^\/\/[a-z0-9\.]+\//g,"")));
						ppvi.id = "pp3";
						$("#xfake-rating-3").before(ppvi);
						$("#pp3").append('<br>')
						$("#xfake-rating-3").show("slow");
						$("#upload-rating-3").val(''); 
						bindpreview();
						$("#upload-image-3").hide("slow");
						if (document.getElementById("replace3")){
							$("#replace3").show();
						} else {
							$("#file-clear-3").after('<span id="replace3"> Выбран файл с Дерпибуры<br></span>');
						}
						$( "#xfake-rating-3" ).change(function() {
								$("#upload-rating-3").val($("#xfake-rating-3").val());
						});
						$("#file-clear-3").fadeIn();	
						showdiv();
						handleFileSelect(3);
						}
					}}
					
					if ($("#md5-1").val().length > 0 ) {
					if ($("#md5-2").val().length == 0 ) {
						if (!document.getElementById('upload-image-2').files[0]) {
						$("#md5-2").val("[derpi]" + btoa(jr.search[tmpnum].representations.full.replace(/^\/\/[a-z0-9\.]+\//g,"")));
						ppvi.id = "pp2";
						$("#xfake-rating-2").before(ppvi);
						$("#pp2").append('<br>')
						$("#xfake-rating-2").show("slow");
						$("#upload-rating-2").val('');
						bindpreview();
						$("#upload-image-2").hide("slow");
						if (document.getElementById("replace2")){
							$("#replace2").show();
						} else {
							$("#file-clear-2").after('<span id="replace2"> Выбран файл с Дерпибуры<br></span>');
						}
						$( "#xfake-rating-2" ).change(function() {
								$("#upload-rating-2").val($("#xfake-rating-2").val());
						});
						$("#file-clear-2").fadeIn();
						showdiv();
						handleFileSelect(2);
						}
					}}
					
					if ($("#md5-1").val().length == 0 ) {
						if (!document.getElementById('upload-image-1').files[0]) {
						$("#md5-1").val("[derpi]" + btoa(jr.search[tmpnum].representations.full.replace(/^\/\/[a-z0-9\.]+\//g,"")));
						ppvi.id = "pp1";
						$("#xfake-rating-1").before(ppvi);
						$("#pp1").append('<br>')
						$("#xfake-rating-1").show("slow");
						$("#upload-rating-1").val('');
						bindpreview();
						$("#upload-image-1").hide("slow");
						if (document.getElementById("replace1")){
							$("#replace1").show();
						} else {
							$("#file-clear-1").after('<span id="replace1"> Выбран файл с Дерпибуры<br></span>');
						}
						$( "#xfake-rating-1" ).change(function() {
								$("#upload-rating-1").val($("#xfake-rating-1").val());
						});
						$("#file-clear-1").fadeIn();
						showdiv();
						handleFileSelect(1);
						}	
					}
		
				});
				
			}
		//i'm sorry for this part but srsly i don't know how to use loop here
		//maybe later i'll rewrite this part and part above
		function bindpreview() {
				$("#pp5").bind('click', function(){
				
					if (document.getElementById("pp5")) {
						$("#md5-5").val('');
						$("#pp5").hide("slow");
						if (document.getElementById("pp5")) {
							setTimeout('$("#pp5").remove()', 600);
						}
						$("#xfake-rating-5").hide("slow");
						$("#replace5").hide();
						$("#upload-image-5").show();
						$("#upload-image-5").val('');
						setTimeout(showdiv, 605);
						
					}
				});
				
				$("#pp4").bind('click', function(){
					if (document.getElementById("pp4")) {
						$("#md5-4").val('');
						$("#pp4").hide("slow");
						if (document.getElementById("pp4")) {
							setTimeout('$("#pp4").remove()', 600);
						}
						$("#xfake-rating-4").hide("slow");
						$("#replace4").hide();
						$("#upload-image-4").show();
						$("#upload-image-4").val('');
						setTimeout(showdiv, 605);
					}
				});
				
				$("#pp3").bind('click', function(){
					if (document.getElementById("pp3")) {
						$("#md5-3").val('');
						$("#pp3").hide("slow");
						if (document.getElementById("pp3")) {
							setTimeout('$("#pp3").remove()', 600);
						}
						$("#xfake-rating-3").hide("slow");
						$("#replace3").hide();
						$("#upload-image-3").show();
						$("#upload-image-3").val('');
						setTimeout(showdiv, 605);
					}
				});
				
				$("#pp2").bind('click', function(){
					if (document.getElementById("pp2")) {
						$("#md5-2").val('');
						$("#pp2").hide("slow");
						if (document.getElementById("pp2")) {
							setTimeout('$("#pp2").remove()', 600);
						}
						$("#xfake-rating-2").hide("slow");
						$("#replace2").hide();
						$("#upload-image-2").show();
						$("#upload-image-2").val('');
						setTimeout(showdiv, 605);
					}
				});
				
				$("#pp1").bind('click', function(){
					if (document.getElementById("pp1")) {
						$("#md5-1").val('');
						$("#pp1").hide("slow");
						if (document.getElementById("pp1")) {
							setTimeout('$("#pp1").remove()', 600);
						}
						$("#xfake-rating-1").hide("slow");
						$("#replace1").hide();
						$("#upload-image-1").show();
						$("#upload-image-1").val('');
						setTimeout(showdiv, 605);
					}
				});
		}
	
	
	//add open icon
	//greasemonkey dollchan
	function adb() {
		if (document.getElementById('de-file-area')) {
			$("#de-file-area").append('<label for="modal-1"> <img id="dbpic_vi" src="https://derpicdn.net/favicon.ico"></label>');
		}	
	}
	$(document).ready(function () {
		//appending icon
		setTimeout(adb, 900);

		//standrat postbox
		$("#upload-image-1").after('<label for="modal-1"> <img id="dbpic_st" src="https://derpicdn.net/favicon.ico" /></label> ');

		//asking for API key 
		if (getCookie('asked') !== '1') {
			$('#dbsearch').hide();
			atext = '<p id="textask">Вы можете ввести свой API Key для использования ваших фильтров или использовать встроенный</p> \
			<br><input type="button" onclick="hideask_user();" id="userkey" value="Использовать мой"> </input><input id="userkey_value" type="text" placeholder="Ваш ключ" maxlength="25" size="25"> </input> \
			<br><br><input type="button" onclick="hideask_def();" id="defaultkey" value="Использовать встроенный"> </input>';
			$('#dbsearch').after(atext);
			
			$('#userkey_value').keyup(function() {
				tempkey = $(this).val();
				setCookie('userkey', tempkey);
			});
			$('#userkey_value').val('');
			
			if (getCookie('userkey').length < 20) {
				setCookie('userkey', '');
			}
		}
	});
	
	//api key stuff
	function saveask(){
		setCookie('asked', '1');
	}
	
	function clearask(){
		setCookie('asked', '');
	}

	function hideask_user(){
		if (getCookie('userkey').length < 20) {
		alertify.log("Неправильный ключ");
		return false;
		} else{
			saveask();
			$('#userkey').hide();
			$('#userkey_value').hide();
			$('#defaultkey').hide();
			$('#textask').hide();
			$('#dbsearch').show('slow');
		}
	}
	
	function hideask_def(){ 
		if (getCookie('userkey').length > 0) {
			alertify.log("Введите корректный ключ или очистите поле");
			return false;
		} else {
			saveask();
			$('#userkey').hide();
			$('#userkey_value').hide();
			$('#defaultkey').hide();
			$('#textask').hide();
			$('#dbsearch').show('slow');
		}
	}