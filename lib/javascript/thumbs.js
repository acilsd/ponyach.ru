function draw_spoiler (spoiler_id, id, thumb, th, tw)
{
    if ((getCookie("show_spoiler_" + spoiler_id) == "true") && (document.getElementById("rating" + id).src !== thumb)){
        document.getElementById("rating" + id).src = thumb;
        document.getElementById("rating" + id).height = th;
        document.getElementById("rating" + id).width = tw;
        document.getElementById("rating" + id).onload = null;
    }
}

function toggle_hidden_thumbs(p) {

	if ($(".multi_thumb_"+p).hasClass("visible")) {
  		$(".multi_thumb_"+p).attr('style','display:none !important');
		$(".multi_thumb_"+p).removeClass('visible');
	} else {
  		$(".multi_thumb_"+p).addClass('visible');
		$(".multi_thumb_"+p).attr('style','display:inline-block !important');
	}
}