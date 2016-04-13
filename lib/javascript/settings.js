function apply_settings()
{
	// show-hide r34 and rf
	if ($('input[name="show_r34"]').is(':checked')){
		setCookie('r34', '1', 36500);
	}else{
		setCookie('r34', '0', 36500);
	}
	
	if ($('input[name="show_rf"]').is(':checked')){
		setCookie('rf', '1', 36500);
	}else{
		setCookie('rf', '0', 36500);
	}
	
	// graphical spoilers
	var elements = document.getElementsByClassName('spoiler_setting');
	for (var i=0; i<elements.length; i++) {
		if (elements[i].checked){
			setCookie('show_spoiler_' + elements[i].value, 'true', 36500);
	        } else{
			setCookie('show_spoiler_' + elements[i].value, '', 36500);
		}
	}
	
	// show-hide rp tag
	if ($('input[name="rp"]').is(':checked')){
		setCookie('rp', 'hided', 36500);
	}else{
		setCookie('rp', 'nothided', 36500);
	}


	//gala disable
	if ($('input[name="galaoff"]').is(':checked')){
		setCookie('galaoff', 'true', 36500);
	}else{
		setCookie('galaoff', 'false', 36500);
	}	
	
	//snow
	if ($('input[name="Вкл"]').is(':checked')){
		setCookie('snow_set', 'enabled', 36500);
	}else{
		setCookie('snow_set', 'disabled', 36500);
	}	
	//snow is enabled even if focus not in browser
	if ($('input[name="Всегда"]').is(':checked')){
		setCookie('snow_tab', 'enabled', 36500);
	}else{
		setCookie('snow_tab', 'disabled', 36500);
	}

	// hide notifications in boards
	var nots_array = ['b', 'd', 'tea', 'test', 'vg', 'oc', 'r34', 'rf'];
	for (i = 0; i < nots_array.length; i++) {
	 boards_nots_array = nots_array[i];
	if ($('input[name="'+boards_nots_array+'"]').is(':checked')){
		setCookie('hide_nots_' + boards_nots_array, '1', 36500);
	} else {
		setCookie('hide_nots_' + boards_nots_array, '0', 36500);
		}	
	}

	// >> post arrows
	if ($('input[name="doubledash"]').is(':checked')){
		setCookie('doubledash', '1', 36500);
	}else{
		setCookie('doubledash', '0', 36500);
	}

	// dollchan extensions
	if ($('input[name="kl_off"]').is(':checked')){
		setCookie('kl_off', '0', 36500);
	}else{
		setCookie('kl_off', '1', 36500);
	}
	
	//preview posts mod
	if ($('input[name="mepr"]').is(':checked')){
		setCookie('mepr_set', 'enabled', 36500);
	}else{
		setCookie('mepr_set', 'disabled', 36500);
	}

	//color marks mod
	if ($('input[name="coma"]').is(':checked')){
		setCookie('coma_set', 'enabled', 36500);
	}else{
		setCookie('coma_set', 'disabled', 36500);
	}

	//typo mod
	if ($('input[name="typo"]').is(':checked')){
		setCookie('typo_set', 'enabled', 36500);
	}else{
		setCookie('typo_set', 'disabled', 36500);
	}
	
	//show-hide 2-5 pics
	if ($('input[name="zanuda"]').is(':checked')){
		setCookie('zanuda', '1', 36500);
	}else{
		setCookie('zanuda', '0', 36500);
	}

	//disable lazyload
	if ($('input[name="lazyload_disable"]').is(':checked')){
		setCookie('lloff', '1', 36500);
	}else{
		setCookie('lloff', '0', 36500);
	}

	//disable ctrlEnter submit
	if ($('input[name="ctrlenoff"]').is(':checked')){
		setCookie('ctrlenoff', '1', 36500);
	}else{
		setCookie('ctrlenoff', '0', 36500);
	}
	
	//db icon replace 
	if ($('input[name="dbrep"]').is(':checked')){
		setCookie('dbrep', '1', 36500);
	}else{
		setCookie('dbrep', '0', 36500);
	}
	
	//update button enable
	if ($('input[name="upbutton"]').is(':checked')){
		setCookie('upbutton', '1', 36500);
	}else{
		setCookie('upbutton', '0', 36500);
	}
	
	//hide banners
	if ($('input[name="hidebanners"]').is(':checked')){
		setCookie('hidebanners', '1', 36500);
	}else{
		setCookie('hidebanners', '0', 36500);
	}
	
	//fixed header
	if ($('input[name="enablefixedheader"]').is(':checked')){
		setCookie('enablefixedheader', '1', 36500);
	}else{
		setCookie('enablefixedheader', '0', 36500);
	}
}

function xapply_settings()
{
	apply_settings();
}

function check_kl(){
    if ( getCookie ('kl_off') !== '1' ) {
        return 'checked';
    }
}


//style preview
function previewstyle(style) {
 var currentstyle = getCookie("kustyle"); 
 localStorage.setItem("currentstyle_key", currentstyle); 
 localStorage.setItem("ifchangedstyle_key", 0); 
 set_stylesheet(style);
}

function set_new_style(style) {
 var old_style = localStorage.getItem("currentstyle_key");
 if (old_style != style) {
  localStorage.setItem("ifchangedstyle_key", 1); 
  set_stylesheet(style); 
 }
}

function resetstyle() {
 var ifchange_ls = localStorage.getItem("ifchangedstyle_key");
 var old_style = localStorage.getItem("currentstyle_key"); 
 if (ifchange_ls != 1) {
  set_stylesheet(old_style);       
 } else { 
  //do nothing
 }
}


//new checkboxes
function create_option_checkbox(classinput, valueinput, nameinput, cookie_name, cookie_value, name) {
	var resHtml = '<label class="menu-checkboxes"><input  type="checkbox" onchange="xapply_settings();" class="' + classinput + '" value="' + valueinput + '" name="' + nameinput + '"';
	if ( getCookie (cookie_name) == cookie_value ) {
        resHtml += ' checked';
    } 
    resHtml += '>' + name + '</label>';

    var radioFragment = document.createElement('div');
    radioFragment.innerHTML = resHtml;

    return radioFragment.innerHTML;
}

//<label class="menu-checkboxes"><input type="checkbox" onchange="xapply_settings();" name="' + nameinput + '">Обновление капчи по клику</label>
