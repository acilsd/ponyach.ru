<input class="modal-state" id="modal-1" type="checkbox" />
<div class="modal" style="z-index: 99999999;">
<label class="modal__bg" for="modal-1"></label>
<div class="modal__inner">
<label class="modal__close" for="modal-1"></label>
	
	<div class="reply" id="prepreview" style="position: fixed; z-index: 99999999; display:none;">
		<b id="textpp" style="display:none;"></b>
		<div id="prepreviewspan1" style="display:inline-block">
			<select style="display:none; margin-left: 22.5%;" id="xfake-rating-1" class="xfake-rating" onchange="fake_rating_change(1);" name="fake_rating_1">
			<option value=""> </option>
			<option value="9">[S]</option>
			<option value="10">[C]</option>
			<option value="11">[A]</option>
			</select>
		</div>
		<div id="prepreviewspan2" style="display:inline-block">
			<select style="display:none; margin-left: 22.5%;" id="xfake-rating-2" class="xfake-rating" onchange="fake_rating_change(2);" name="fake_rating_2">
			<option value=""> </option>
			<option value="9">[S]</option>
			<option value="10">[C]</option>
			<option value="11">[A]</option>
			</select>
		</div>
		<div id="prepreviewspan3" style="display:inline-block">
			<select style="display:none; margin-left: 22.5%;" id="xfake-rating-3" class="xfake-rating" onchange="fake_rating_change(3);" name="fake_rating_3">
			<option value=""> </option>
			<option value="9">[S]</option>
			<option value="10">[C]</option>
			<option value="11">[A]</option>
			</select>
		</div>
		<div id="prepreviewspan4" style="display:inline-block">
			<select style="display:none; margin-left: 22.5%;" id="xfake-rating-4" class="xfake-rating" onchange="fake_rating_change(4);" name="fake_rating_4">
			<option value=""> </option>
			<option value="9">[S]</option>
			<option value="10">[C]</option>
			<option value="11">[A]</option>
			</select>
		</div>
		<div id="prepreviewspan5" style="display:inline-block">
			<select style="display:none; margin-left: 22.5%;" id="xfake-rating-5" class="xfake-rating" onchange="fake_rating_change(5);" name="fake_rating_5">
			<option value=""> </option>
			<option value="9">[S]</option>
			<option value="10">[C]</option>
			<option value="11">[A]</option>
			</select>
		</div>
	</div>
	
	<br><br><br><br><br><br><br><br><br><br>
	
    <center>
		<input size="70" maxlength="70" id="dbsearch" placeholder="Введите теги для поиска" type="text">
		<input size="4" maxlength="4" id="dbpage" type="text" style="display:none"><br><br>
		<span id="infodb"></span><br>
		<input type="button" value="Предыдущая страница" id="prev" onclick="prev();" style="display:none;">
		<input type="button" value="Следующая страница" id="next" onclick="next();" style="display:none;">
	</center>
    <br><br>
	
	<span id="imagesgoeshere"></span>
	
	<span>
		<center>
		<input type="button" class="prevbutton" value="Предыдущая страница" id="prev" onclick="prev();" style="display:none;">
		<input type="button" class="nextbutton" value="Следующая страница" id="next" onclick="next();" style="display:none;">
		</center>
	</span>
	
</div>
</div>




<input class="modal-state" id="modal-2" type="checkbox" />
<div class="modal" style="z-index: 99999999;">
<label class="modal__bg" for="modal-2"></label>
<div class="modal__inner">
<label class="modal__close" for="modal-2"></label>
	
	<div class="reply" id="preview_passcode_div" style="position: fixed; z-index: 99999999; display:none;"></div>
	<input size="30" type="text" placeholder="Поиск..." id="passcode_search" style="position: relative; z-index: 99999999; float:right; right:4%">
	<br><br><br><br><br><br><br><br>
	
	<center>
		<input id="passcodegetimages" type="button" value="Получить список картинок">
		<span id="passcodeimages"></span>
	</center>
	
</div>
</div>