{% spacefull %}
<script type="text/javascript"><!--
  var max_file_size = {{ board.maximagesize }};
//--></script>

<div class="postarea">

<a id="postbox"></a>
<!-- FORM START HERE -->
{% endspacefull %}
<form name="postform" id="postform" action="{{ cf.KU_CGIPATH }}/board.php" method="post" enctype="multipart/form-data">
{% spacefull %}
{% endspacefull %}
<div class="overlay-bg">
<div id="haikaptcha" class="overlay-content popup1" >
</div>
</div>
{% spacefull %}
<input type="hidden" name="board" value="{{ board.name }}" />
<input type="hidden" name="replythread" value="<!sm_threadid>" />
{% if  board.maximagesize > 0 %}<input type="hidden" name="MAX_FILE_SIZE" value="{{ board.maximagesize }}" />{% endif %}
<input type="text" name="email" size="28" maxlength="75" value="" style="display: none;" />
<table class="postform">
{% endspacefull %}
	<tbody>
	{% if  board.forcedanon != 1 %}
		<tr id="nameid">
			<td class="postblock">
				{% trans _("Имя          ") %}</td>
			<td>
				<input type="text" name="name" id="namebox" size="28" maxlength="75" accesskey="n" />
			</td>
		</tr>
	{% endif %}
{% spacefull %}
{% endspacefull %}
	<tr>
		<td class="postblock">
			{% trans _("Email") %}</td>
		<td>
			<input type="text" name="em" id="em" size="28" maxlength="75" accesskey="e" />
		</td>
	</tr>

{% spacefull %}
{% endspacefull %}
	<tr>

		<td class="postblock">
			{% trans _("Тема") %}
		</td>
		<td>
			<input type="text" name="subject" id="subject" size="35" maxlength="75" accesskey="s" />&nbsp;	<a id="haiku_btn" class="show-popup" href="javascript:void(0)" data-showpopup="1" style="text-decoration:none; display:none;"><input type="button" value="Ввести капчу" ></a>
{%spaceless%}
<input id="go" onclick="postform_submit();" type="button" name="xxxx" value="Отправить" accesskey="z" />
{%endspaceless%}
<input id="fake_go" style="display: none" type="submit" name="shme" value="" >
		</td>
	</tr>
{% spacefull %}
{% endspacefull %}
	<tr>
		<td class="postblock">
			{% trans _("Сообщение") %}
		</td>
		<td>
			<textarea name="message" id="msgbox" cols="60" rows="7" accesskey="m"></textarea>
			<output name="file_error" id="file_error"></output> 
		</td>
	</tr>
{% spacefull %} 
{% endspacefull %}
		<tr>
			<td class="postblock"> 
				{% trans _("Файл") %}
			</td>
			<td>

			<input type="hidden" name="token" id="token" value="" />
			{% for file_id in file_ids %}
			<input type="hidden" name="md5-{{forloop.counter}}" id="md5-{{forloop.counter}}" value="" />
			<input type="hidden" name="md5passcode-{{forloop.counter}}" id="md5passcode-{{forloop.counter}}" value="" />
                        {% if  board_ratings != '' %}
                        <select 
			{% if not forloop.first %}
			style="display:none"
			{% endif %}
			class="rating_select" id="upload-rating-{{forloop.counter}}" name="upload-rating-{{forloop.counter}}" accesskey="r" onchange="on_rating_change({{forloop.counter}});">
                        <option value="">   </option>
			{% for rating_id,rating_name in board_ratings %}
                                <option value="{{ rating_id }}">{{ rating_name }}</option>
                        {% endfor %}
                        </select>
                        {% endif %}
	
			<button type="button" id="file-clear-{{forloop.counter}}" name="file_clear" style="display:none" onclick="file_form_clear({{forloop.counter}})">[X]</button>
			<span><input id="upload-image-{{forloop.counter}}"
			{% if not forloop.first %}
			style="display:none"
			{% endif %}
			type="file" name="upload[]" size="35" accesskey="f" onchange="handleFileSelect({{forloop.counter}})"></span> 

		{% if replythread == 0 %} {% if board.enablenofile == 1 %}
				[<input type="checkbox" name="nofile" id="nofile" accesskey="q" /><label for="nofile"> {% trans _("No File") %}</label>]
			{% endif %} {% endif %}
			{% endfor %}
                        
			</td>
	
		</tr>
			<tr>
		</tr>
{% spacefull %}
{% endspacefull %}
		<tr id="passwordbox"><td></td><td></td></tr>
		<tr>
		
		</tr>
	</tbody>

{% spacefull %}
</table>
</form>
<hr />
</div>
<script type="text/javascript"><!--
				set_inputs("postform");
				//--></script>
<form id="delform" action="{{ KU_CGIPATH }}/board.php" method="post">
<input type="hidden" name="board" value="{{ board.name }}" />{% endspacefull %}
