function update_counter(){
	xmlHttp = new XMLHttpRequest();
	xmlHttp.onreadystatechange = function(){
                if (xmlHttp.readyState === 4) {
                        if ( xmlHttp.status === 200) {
				if (xmlHttp.responseText) {
					res=xmlHttp.responseText.match(/^([0-9]+);([0-9]+);(.*)$/);
					$("#online").html(res[1]),$("#speed").html(res[2]);
					
					arr=res[3].split(';');
					var i = 0, b = [], bname = '', p_t = [], posts = 0, threads = 0;
					for (i = 0; i < arr.length; ++i) {
						b=arr[i].split('='); 
						bname=b[0]; p_t=b[1].split(':');
						if (bname == 'changelog') { 
							display_name = 'Чейнжлог';
						} else {
							display_name = bname;
						}
						if (getCookie('hide_nots_' + bname) == '1') continue;
						posts=p_t[0]; threads=p_t[1];

						if (localStorage.getItem("default_title_"+bname) === null) {
							localStorage["default_title_"+bname] = $('a#board_link_top_'+bname).attr('title');
						}
					
						var newtext = bname; var newtitle = localStorage["default_title_"+bname];
						if (bname == this_board_dir) {
							localStorage["last_post_"+bname] = posts;
							localStorage["last_thread_"+bname] = threads;
							//if ( $('a#board_link_top_'+name).length ) {
								//newtext = name;
								//newtitle = "";
							//}
						} else {
							if (localStorage.getItem("last_post_"+bname) === null) {
								localStorage["last_post_"+bname] = posts;
							} else {
								if (parseInt(localStorage["last_post_"+bname]) < parseInt(posts)) {
									newtext = display_name + '+';
									newtitle = "новых постов: " + (posts - localStorage["last_post_"+bname]);
								} else {
									newtext = display_name;
								}
							}
							if (localStorage.getItem("last_thread_"+bname) === null) {
								localStorage["last_thread_"+bname] = threads;
							} else {
								if (localStorage["last_thread_"+bname] < threads) {
									newtext = display_name + '*';
									newtitle = newtitle + ", новых тредов: " + (threads - localStorage["last_thread_"+bname]);
								}
							}

						}
						$('a#board_link_top_'+bname).text(newtext);
						$('a#board_link_bot_'+bname).text(newtext);
						$('a#board_link_bot_'+bname).attr('title', newtitle);
						$('a#board_link_top_'+bname).attr('title', newtitle);
						
					}
				}
			}
		}
	}

	xmlHttp.open( "GET", "/info.php?x=1", true);
	xmlHttp.send( null );
	setTimeout(update_counter, 12000);
}
