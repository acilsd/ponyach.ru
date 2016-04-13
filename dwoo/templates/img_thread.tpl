{%spacefull %}
<!-- POSTS START HERE -->
{% endspacefull %}
{% for postkey,post in posts %}
{% if post.IS_DELETED == 1 %}
<?php if($p['is_mod']){ ?>
{% endif %}
  
  {% if  post.parentid == 0 %}
{%spacefull %}
{% endspacefull %}
    
    <div class="pstnode" id="thread{{ post.id }}{{ board.name }}">
<div class="oppost" id="reply{{ post.id }}" data-num="{{ post.id }}"
{% if post.edit_timestamp != 0 %}
data-lastmodified="{{ post.edit_timestamp }}"
{% endif %}
>
    <a name="s{{ forloop.counter }}"></a>
    <a name="{{ post.id }}"></a>
    <label>
    <span class="coma-colormark" style="background:{{post.coma}} !important;"></span>
    <input class="mobile_checkbox_hide" type="checkbox" name="post[]" value="{{ post.id }}" style="display:none;" />
    <a  id="postmenuclick{{ post.id }}" class="dast-hide-tr" style="text-decoration:none;" onclick="showpostmenu({{ post.id }});" href="javascript:void(0);">▲</a>
</label>
{%spacefull %}
{% endspacefull %}
{% if post.email == 'dollchan' %}
<?php
if (! $p['is_reader']) {
echo '<span class="filetitle" style="display: none">Dollchan Extension Tools</span>';
}
?>
{% endif %}
    {% if  post.subject != '' %}
      <span class="filetitle">{{ post.subject }}</span>
    {% endif %}
{% if post.IS_DELETED == 1 %}
<span class="post_is_del" style="color:#ff0000">[del]</span> 
{% endif %}
<span class="postername">
{% if  post.email  AND  board.anonymous AND post.email != 'dollchan' %}
<a href="mailto:{{ post.email }}">
{% endif %}
{% if  post.name == ''  AND  post.tripcode == '' %}
{{ board.anonymous }}
{% else %}
{% if  post.name == ''  AND  post.tripcode != '' %}
{% else %}
{{ post.name }}
{% endif %}
{% endif %}
{% if  post.email != ''  AND  board.anonymous != '' %}
</a>
{% endif %}
</span>
    
    {% if  post.tripcode != '' %}
      <span class="postertrip">!{{ post.tripcode }}</span>
    {% endif %}

	{% if board.name == 'd' && post.mod_post == 1 %}
	<span class="postertrip">[M]</span>
	{% endif %}
    
    {% if  post.posterauthority == 1 %}
      <span class="admin">
      &#35;&#35;&nbsp;{% trans _("Admin") %}&nbsp;&#35;&#35;
      </span>
    {% endif %}
    {% if  post.posterauthority == 4 %}
      <span class="mod">
      &#35;&#35;&nbsp;{% trans _("Super Mod") %}&nbsp;&#35;&#35;
      </span>
    {% endif %}
    {% if  post.posterauthority == 2 %}
      <span class="mod">
      &#35;&#35;&nbsp;{% trans _("Mod") %}&nbsp;&#35;&#35;
      </span>
    {% endif %}
    <span class="mobile_date dast-date">
    {{ post.timestamp_formatted }}
    </span>
    <span class="reflink">
    {{ post.reflink|safe }}
    </span>
    <span class="extrabtns">
    {{ post.replylink|safe }}
    </span>
    <span style="display: inline-block;" class="dnb" id="dnb-{{ board.name }}-{{ post.id }}-y">&nbsp;[<a href="javascript:void(0);" style="text-decoration:none;" onclick="return putdnblks('dnb-{{ board.name }}-{{ post.id }}-y');">Мод</a>]</span>
<br>
{%spacefull %}
{% endspacefull %}
    
    {% if post.file_count != 0 %}
<div class="post-files" style="display:inline" >
    {% for fileid, file in post.files %}
    {% if forloop.counter == 1 %}
    {% if file.name != 'removed' %}
    <span 
	<?php if($p['hide_multi_thumb']){ ?>
	onclick="toggle_hidden_thumbs({{post.id}})"
	<?php } ?>
	class="fake_filesize" id="fake_filesize_{{post.id}}">
{%spacefull %}
{% endspacefull %}
      {% if post.file_count == 1 %}
      Файл 
      {% else %}
      Файл [{{ forloop.counter }}/{{post.file_count}}]
      {% endif %}
        {% if  file.rating_file == '' %}
          <a href="{{ file.url }}">
        {% else %}
          [R: {{ file.rating_name }}]
          <a href="{{ file.url }}">
        {% endif %}
      </a>
      <a class="hide123," href="/download.php?id={{ file.name }}">{{ file.name }}.{{ file.type }}</a>
      - ({{ file.size_formatted }}
      {% if  file.image_w > 0  AND  file.image_h > 0 %}
        , {{ file.image_w }}x{{ file.image_h }}
      {% endif %}
      <span class="mobile_filename_hide">
      ,
        {{ file.original }}.{{ file.type }}
      </span>
      )
      </span>
 
    <br />
    {% endif %}
    {% endif %}
<div style="display:
{%if forloop.counter > 1 %}
<?php if($p['hide_multi_thumb']){ ?>
none !important
<?php }else{ ?>
inline
<?php } ?>
{%else%}
inline
{%endif%}
"  id="file_{{file.name}}_{{forloop.counter}}" class="file
{%if post.file_count > 1 %}
last_op_div
{%if forloop.counter > 1 %}
multi_thumb_{{post.id}}
{%endif%}
{%endif%}
"
>
      <span class="filesize fs_{{ post.id }}" id="fs_{{ post.id }}_{{ forloop.counter }}" style="display:none">
      {% if post.file_count == 1 %}
      Файл 
      {% else %}
      Файл [{{ forloop.counter }}/{{post.file_count}}]
      {% endif %}
      <a href="{{ file.url }}">
      </a>
      <a class="hide123," href="/download.php?id={{ file.name }}">{{ file.name }}.{{ file.type }}</a>
      - ({{ file.size_formatted }}
      {% if  file.image_w > 0  AND  file.image_h > 0 %}
        , {{ file.image_w }}x{{ file.image_h }}
      {% endif %}
      <span class="mobile_filename_hide">
        , {{ file.original }}.{{ file.type }}
      </span>
      )
      </span>
{%spacefull %}
{% endspacefull %}
      {% if  file.name == 'removed' %}
        <div class="nothumb">
        {% trans _("Файл<br />удалён") %}
        </div>
      {% else %}
{%spacefull %}
{% endspacefull %}
        <a
        {% if  cf.KU_NEWWINDOW %}
          target="_blank"
        {% endif %}
        href="{{ file.url }}"
	onclick="javascript:expandimg('{{ post.id }}_{{ forloop.counter }}', '{{ file_path }}/src/{{ file.name }}.{{ file.type }}', '{{ file_path }}/thumb/{{ file.name }}s.{{ file.thumb_type }}', '{{ file.image_w }}', '{{ file.image_h }}', '{{ file.thumb_w }}', '{{ file.thumb_h }}');return false;"
	>
	<span id="thumb{{ file.name }}">
        <img id="thumbnail_{{ file.name }}" src="/images/loading.gif" data-src=
	{% if file.rating_id %}
        "<?php if($p['show_spoiler_{{ file.rating_id }}']){ ?>{{ file_path }}/thumb/{{ file.name }}s.{{ file.thumb_type }}<?php }else{ ?>/images/{{ file.rating_file }}<?php } ?>" alt="{{ post.id }}" class="thumb lazyload{% if file.type == 'webm' %} webm-file{% endif %}" height="<?php if($p['show_spoiler_{{ file.rating_id }}']){ ?>{{ file.thumb_h }}<?php }else{ ?>{{ file.rating_thumb_h }}<?php } ?>" width="<?php if($p['show_spoiler_{{ file.rating_id }}']){ ?>{{ file.thumb_w }}<?php }else{ ?>{{ file.rating_thumb_w }}<?php } ?>"
	{% else %}
        "{{ file_path }}/thumb/{{ file.name }}s.{{ file.thumb_type }}" alt="{{ post.id }}" class="thumb op_thumb lazyload{% if file.type == 'webm' %} webm-file{% endif %}" height="{{ file.thumb_h }}" width="{{ file.thumb_w }}"
	{% endif %}
            onmouseover="javascript:show_filesize({{ post.id }},{{ forloop.counter }});return false;"
        />
        </span></a>
      {% endif %}
</div>
    {% endfor %}
</div>
{%spacefull%}
{%endspacefull%}
    {% endif %}
    
  {% else %}
{{ post.premod_before|safe }}
    <table>
    <tbody>
    <tr class="pstnode">
    <td class="doubledash">
    &gt;&gt;
    </td>
    <td class="reply" id="reply{{ post.id }}" data-num="{{ post.id }}"
{% if post.edit_timestamp != 0 %}
data-lastmodified="{{ post.edit_timestamp }}"
{% endif %}
>
    <a name="{{ post.id }}"></a>
    <label>
    <span class="coma-colormark" style="background:{{post.coma}} !important;"></span>
    <input class="mobile_checkbox_hide" type="checkbox" name="post[]" value="{{ post.id }}" style="display:none;" />
    <a id="postmenuclick{{ post.id }}" class="dast-hide-tr" style="text-decoration:none;" onclick="showpostmenu({{ post.id }});" href="javascript:void(0);">▲</a>
    </label>
    
    {% if  post.subject != '' %}
      <span class="filetitle">
      {{ post.subject }}
      </span>
    {% endif %}
    
{% if post.IS_DELETED == 1 %}
<span class="post_is_del" style="color:#ff0000">[del]</span> 
{% endif %}
{% if post.premod != 0 %}
<?php if($p['is_mod']){ ?>
<span class="post_is_premod" style="color:#ff0000">[pre]</span> 
<?php } ?>
{% endif %}
<span class="postername">
{% if  post.email  AND  board.anonymous %}
<a href="mailto:{{ post.email }}">
{% endif %}
{% if  post.name == ''  AND  post.tripcode == '' %}
{{ board.anonymous }}
{% else %}
{% if  post.name == ''  AND  post.tripcode != '' %}
{% else %}
{{ post.name }}
{% endif %}
{% endif %}
{% if  post.email != ''  AND  board.anonymous != '' %}
</a>
{% endif %}
</span>
    
    {% if  post.tripcode != '' %}
      <span class="postertrip">!{{ post.tripcode }}</span>
    {% endif %}
	
	{% if board.name == 'd' && post.mod_post == 1 %}
	<span class="postertrip">[M]</span>
	{% endif %}
    
    {% if  post.posterauthority == 1 %}
      <span class="admin">
      &#35;&#35;&nbsp;{% trans _("Admin") %}&nbsp;&#35;&#35;
      </span>
    {% endif %}
    {% if  post.posterauthority == 4 %}
      <span class="mod">
      &#35;&#35;&nbsp;{% trans _("Super Mod") %}&nbsp;&#35;&#35;
      </span>
    {% endif %}
    {% if  post.posterauthority == 2 %}
      <span class="mod">
      &#35;&#35;&nbsp;{% trans _("Mod") %}&nbsp;&#35;&#35;
      </span>
    {% endif %}
    <span class="mobile_date dast-date">
    {{ post.timestamp_formatted }}
    </span>
    
    {% if post.repost_of %}
    [repost <a href="/{{board.name}}/res/{{post.repost_thread}}.html#{{post.repost_of}}" onclick="return highlight('{{post.repost_of}}', true);" class="de-preflink ref|{{board.name}}|{{post.repost_thread}}|{{post.repost_of}}">&gt;&gt;{{post.repost_of}}</a>]
    {% endif %}
    <span class="reflink">
    {{ post.reflink|safe }}
    </span>
    <span class="extrabtns">
    {{ post.replylink|safe }}
    </span>
    <span class="extrabtns">
    {% if  post.locked == 1 %}
      <img style="border: 0;" src="{{ boardpath }}css/icons/locked.png" alt="Закрыто" />
    {% endif %}
    
    {% if  post.stickied == 1 %}
      <img style="border: 0;" src="{{ boardpath }}css/icons/sticky.png" alt="Закреплено" />
    {% endif %}
    </span>	
    <span style="display: inline-block;" class="dnb" id="dnb-{{ board.name }}-{{ post.id }}-n">&nbsp;[<a href="javascript:void(0);" style="text-decoration:none;" onclick="return putdnblks('dnb-{{ board.name }}-{{ post.id }}-n');">Мод</a>]</span>
<br>
    {% if post.file_count != 0 %}
<div class="post-files" style="display:inline" >
    {% for fileid, file in post.files %}
    {% if forloop.counter == 1 %}
    {% if file.name != 'removed' %}
    <span
	<?php if($p['hide_multi_thumb']){ ?>
	onclick="toggle_hidden_thumbs({{post.id}})"
	<?php } ?>
	class="fake_filesize" id="fake_filesize_{{post.id}}">
{%spacefull %}
{% endspacefull %}
      {% if post.file_count == 1 %}
      Файл 
      {% else %}
      Файл [{{ forloop.counter }}/{{post.file_count}}]
      {% endif %}
        {% if  file.rating_file == '' %}
          <a href="{{ file.url }}">
        {% else %}
          [R: {{ file.rating_name }}]
          <a href="{{ file.url }}">
        {% endif %}
      </a>
      <a class="hide123," href="/download.php?id={{ file.name }}">{{ file.name }}.{{ file.type }}</a>
      - ({{ file.size_formatted }}
      {% if  file.image_w > 0  AND  file.image_h > 0 %}
        , {{ file.image_w }}x{{ file.image_h }}
      {% endif %}
      <span class="mobile_filename_hide">
      ,
      {{ file.original }}.{{ file.type }}
      </span>
      )
 
    </span>
    <br />
    {% endif %}
    {% endif %}
<div style="display:
{%if forloop.counter > 1 %}
<?php if($p['hide_multi_thumb']){ ?>
none !important
<?php }else{ ?>
inline
<?php } ?>
{%else%}
inline
{%endif%}
" 
id="file_{{file.name}}_{{forloop.counter}}" class="file
{%if post.file_count > 1 %}
last_op_div
{%if forloop.counter > 1 %}
multi_thumb_{{post.id}}
{%endif%}
{%endif%}
"
>
    {% if file.name != 'removed' %}
      <span class="filesize fs_{{ post.id }}" id="fs_{{ post.id }}_{{ forloop.counter }}" style="display:none" >
{%spacefull %}
{% endspacefull %}
      {% if post.file_count == 1 %}
      Файл 
      {% else %}
      Файл [{{ forloop.counter }}/{{post.file_count}}]
      {% endif %}
        {% if  file.rating_file == '' %}
          <a href="{{ file.url }}">
        {% else %}
          [R: {{ file.rating_name }}]
          <a href="{{ file.url }}">
        {% endif %}
      </a>
      <a class="hide123," href="/download.php?id={{ file.name }}">{{ file.name }}.{{ file.type }}</a>
      - ({{ file.size_formatted }}
      {% if  file.image_w > 0  AND  file.image_h > 0 %}
        , {{ file.image_w }}x{{ file.image_h }}
      {% endif %}
      <span class="mobile_filename_hide">
      ,
      {{ file.original }}.{{ file.type }}
      </span>
      )
      </span>
      
    {% endif %}
      {% if  file.name == 'removed' %}
        <div class="nothumb">
        {% trans _("File<br />Removed") %}
        </div>
      {% else %}
{%spacefull %}
{% endspacefull %}
        <a
        {% if  cf.KU_NEWWINDOW %}
          target="_blank"
        {% endif %}
        href="{{ file.url }}"
	onclick="javascript:expandimg('{{ post.id }}_{{ forloop.counter }}', '{{ file_path }}/src/{{ file.name }}.{{ file.type }}', '{{ file_path }}/thumb/{{ file.name }}s.{{ file.thumb_type }}', '{{ file.image_w }}', '{{ file.image_h }}', '{{ file.thumb_w }}', '{{ file.thumb_h }}');return false;"
	>
	<span id="thumb{{ file.name }}">
        <img id="thumbnail_{{ file.name }}" src="/images/loading.gif" data-src=
	{% if file.rating_id %}
        "<?php if($p['show_spoiler_{{ file.rating_id }}']){ ?>{{ file_path }}/thumb/{{ file.name }}s.{{ file.thumb_type }}<?php }else{ ?>/images/{{ file.rating_file }}<?php } ?>" alt="{{ post.id }}" class="thumb lazyload{% if file.type == 'webm' %} webm-file{% endif %}" height="<?php if($p['show_spoiler_{{ file.rating_id }}']){ ?>{{ file.thumb_h }}<?php }else{ ?>{{ file.rating_thumb_h }}<?php } ?>" width="<?php if($p['show_spoiler_{{ file.rating_id }}']){ ?>{{ file.thumb_w }}<?php }else{ ?>{{ file.rating_thumb_w }}<?php } ?>"
	{% else %}
        "{{ file_path }}/thumb/{{ file.name }}s.{{ file.thumb_type }}" alt="{{ post.id }}" class="thumb lazyload{% if file.type == 'webm' %} webm-file{% endif %}" height="{{ file.thumb_h }}" width="{{ file.thumb_w }}"
	{% endif %}
            onmouseover="javascript:show_filesize({{ post.id }},{{ forloop.counter }});return false;"
        />
        </span></a>
      {% endif %}
</div>
    {% endfor %}
</div>
{%spacefull%}
{%endspacefull%}
    {% endif %}
  {% endif %}
<div class="post-body" style="display:inline">
  <blockquote>
  {{ post.message|safe }}
  </blockquote>
</div>
  {% if  post.parentid != 0 %}
    </tbody>
    </table>
{{ post.premod_after|safe }}
{%else%}
</div>
  {% endif %}
{%spacefull %}
{% endspacefull %}
{%spacefull %}
{% endspacefull %}
{% if post.IS_DELETED == 1 %}
<?php } ?>
{% endif %}
{% endfor %}
{% if  not isread %}
  </div>
  {% if  not isexpand %}
    <br clear="left" />
    <hr />
  {% endif %}
  {% if  replycount > 2 %}
    <span style="float:right">
    &#91;<a href="/{{ board.name }}/">Назад</a>&#93;
    </span>
  {% endif %}
{% endif %}
{% if modifier == 'last50' %}
{% spacefull %}
<script type="text/javascript">
function deletePosts() {
	postsCount = $(".pstnode").not(':first').length;
	if (postsCount > 50){
		$('.pstnode').not(':first')[0].remove();
		deletePosts();
	}
}
setTimeout(function(){
	var target = document.querySelector('body > form > .pstnode');
	var observer = new MutationObserver(function(mutations) {
	mutations.forEach(function(mutation) {
		deletePosts()
		});    
	});
	var config = { attributes: true, childList: true, characterData: true }
	observer.observe(target, config);
}, 3000);
</script>
{% endspacefull %}
{% endif %}
