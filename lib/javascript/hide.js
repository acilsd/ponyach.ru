$(document).ready(function () {
//add user localstorage var into our field
	userinputnameLS = localStorage.getItem("userinputname");
	if (userinputnameLS){
		if (userinputnameLS.length > 0){
			$('#hidename').val(userinputnameLS);
			userinputname = $('#hidename').val();
			hidemess = "<span id='spanhidednames' >Буду скрывать " + "<b>" + userinputname.match(/\(+([^)]+)\)+/g).toString().replace(/,/g, "").match(/([^()]+)/g).toString().replace(/,/g, ", ") + "</b>";
			if (!document.getElementById('spanhidednames')) {
				$('#tellhide').append(hidemess);
			}
		}
	}
	//bind field and setting ls if changed
	$('#hidename').keyup(function () {
		userinputname = $(this).val();
		if (userinputname.length === 0) {
			$('#spanhidednames').remove();
		}
		localStorage.setItem("userinputname", userinputname);
		userinputname = userinputname.match(/\(+([^)]+)\)+/g).toString().replace(/,/g, "");
		userinputname = userinputname.match(/([^()]+)/g);
		hidemess = "<span id='spanhidednames' >Буду скрывать " + "<b>" + userinputname.toString().replace(/,/g, ", ") + "</b>";
		$('#spanhidednames').remove();
		$('#tellhide').append(hidemess);
	});

});

function hideanddelete(){
	if (typeof userinputname != 'undefined' && userinputname != null && userinputname.length > 0) {
		if(typeof userinputname != 'object'){
			userinputname = userinputname.match(/\(+([^)]+)\)+/g).toString().replace(/,/g, "");
			userinputname = userinputname.match(/([^()]+)/g);
		}
		for (i=0; i < userinputname.length; i++){
			$(".postername:contains('" + userinputname[i] + "')").closest(".reply").not('div[de-oppost]').css({'position' : 'absolute', 'left' : '-9999px', 'overflow' : 'hidden', 'height' : 0});
			$(".postername:contains('" + userinputname[i] + "')").closest(".pstnode").not('div[de-oppost]').not(":first").parent().parent().css({'position' : 'absolute', 'left' : '-9999px', 'overflow' : 'hidden', 'height' : 0});
			
			//hide refmap
			/*
			hidedposts = $(".postername:contains('" + userinputname[i] + "')").parent().parent().find(".reflink").text().split("\n").length
			if (hidedposts > 0){
				for (z=0;z<hidedposts;z++){
					//cheking if de-link-ref with this number exist
					reflinknumber = $(".postername:contains('" + userinputname[i] + "')").parent().parent().find(".reflink").text().split("\n")[z].split(".")[1].replace(/\s/g, '');
					refmaplength = $('.de-refmap').find('.de-link-ref[href*="' + reflinknumber + '"]').parent();
					if (refmaplength.length > 0){
						multiplelinks = $('.de-refmap').find('.de-link-ref[href*="' + reflinknumber + '"]').parent().find('.de-link-ref:eq(1)');
						if (multiplelinks.length > 0){
							//refmap have multiple links, hide only our links
							$('.de-refmap').find('.de-link-ref[href*="' + reflinknumber + '"]').hide();
							$('.de-refmap').find('.de-link-ref[href*="' + reflinknumber + '"]').next().hide();
						} else {
							//hide whole refmap?...well
							$('.de-refmap').find('.de-link-ref[href*="' + reflinknumber + '"]').parent().hide();
						}
					}
				}
			}
			*/
			
			//second try, first was to slow
			//OOOOK this to breaks browser for 2 secs. I'm retarded, maybe latter
			
			/*
			refsraw = [];
			$(".postername:contains('" + userinputname + "')").parent().parent().find('blockquote > a[class*="ref|"]').each(function() { refsraw.push($(this).attr('class'))});

			refs = [];
			for (i=0; i<refsraw.length;i++){
				refs.push(refsraw[i].split("|")[3]);
			}

			for (z=0;z<refs.length;z++){
				multiplelinks = $('.de-refmap').find('.de-link-ref[href*="' + refs[z] + '"]').parent().find('.de-link-ref:eq(1)');
				if (multiplelinks.length > 0){
					//refmap have multiple links, hide only our links
					$('.de-refmap').find('.de-link-ref[href*="' + refs[z] + '"]').hide();
					$('.de-refmap').find('.de-link-ref[href*="' + refs[z] + '"]').next().hide();
				} else {
					//hide whole refmap?...well
					$('.de-refmap').find('.de-link-ref[href*="' + refs[z] + '"]').parent().hide();
				}
			}
			*/
			
			
		}
	}
}
function bindobs(){
	var target = document.querySelector('body > form > .pstnode');
	var observer = new MutationObserver(function(mutations) {
	mutations.forEach(function(mutation) {
			hideanddelete();
		});    
	});
	var config = { attributes: true, childList: true, characterData: true }
	observer.observe(target, config);
}

setTimeout('bindobs();hideanddelete()', 1500);



