<style>
.desu {
    margin: 5px;
    transition-duration: 0.4s;
    transition-property: transform;
}
.desu:hover {
    box-shadow: 0 0 8px rgba(0, 0, 0, 0.6);
    transform: scale(1.1);
}
</style>

<script>
function reload_img() {
	$.ajax({
		url: "http://ponyach.ru/getdb.php?repairs",
		success: function(data) {
			$(".desu").remove();
			$("body").append(data);
		},
		error: function(data) {
			$(".desu").remove();
			$("body").append(data);
		}
	});
}
function more_pics(){
	reload_img();
}

function auto_pics(){
	if (typeof intervalpic !== 'undefined') {
		stopauto();
	}
	var input = document.getElementById('seconds');
	if(input.value.length == 0){
		seconds = 5;
	}
	everysec = seconds;
	everysec = everysec + "000";
	everysec = parseInt(everysec);
	intervalpic = setInterval(reload_img, everysec);
}
function stopauto(){
	clearInterval(intervalpic);
}

function showauto() {
	$('.autopics').show('slow');
}

function showstop() {
	$('.stopauto').show('slow');
}
function hidestop() {
	$('.stopauto').hide('slow');
}

$(document).ready(function () {
	everysec = 5;
	reload_img();
	$('#seconds').keyup(function() {
		seconds = $(this).val();
	});
	$('#seconds').keypress(function(e) {
		if(e.which == 13) {
			auto_pics();
			$('.stopauto').show('slow');
		}
	});
});
</script>
<div class="reply">
<center><h3><b>Что это за страница? Где я?</b></h3>
<p>Ты видишь эту страничку потому что на Поняче сейчас проводятся технические работы которые требуют отключения доски. Обычно это ненадолго, а пока ты можешь посидеть в ламповом чатике с видео <a href="http://tube.ponyach.ru/r/ponyach" target="_blank"><input type="button" value="Убежище"></a></p>
<p>Или можешь <input id="morepics" type="button" value="Посмотреть картинки" onclick="more_pics();showauto()"></p>
<input class="autopics" style="display:none;" type="button" value="Хочу чтобы они появлялись сами каждые " onclick="auto_pics();showstop();"><input style="display:none;" id="seconds"class="autopics" placeholder="секунд" maxlength="6" size="6" type="text"> <br class="stopauto" style="display:none;"><br class="stopauto" style="display:none;">
<input class="stopauto" style="display:none;" type="button" value="Всё, хватит картинок" onclick="stopauto();hidestop();">
</center>
</div>
<br>
<br>
<br>
<br>

</body>
</html>