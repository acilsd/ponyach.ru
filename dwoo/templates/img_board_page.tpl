{%spacefull %}
<div class="pstnode">
<!-- POSTS START HERE -->
{% endspacefull %}
{% for post in posts %}
{% if post.IS_DELETED == 1 %}
<?php if($p['is_mod']){ ?>
{% endif %}
{%spacefull %}
{% endspacefull %}
		{% if  post.parentid == 0 %}
	<div id="thread{{ post.id }}{{ board.name }}">
<div class="oppost" id="reply{{ post.id }}" data-num="{{ post.id }}"
{% if post.edit_timestamp != 0 %}
data-lastmodified="{{ post.edit_timestamp }}"
{% endif %}
>
			<a name="s{{ forloop.counter }}"></a>
{%spacefull %}
{% endspacefull %}
				<a name="{{ post.id }}"></a>
			<label>
			<span class="coma-colormark" style="background:{{post.coma}} !important;"></span>
			<input class="mobile_checkbox_hide" type="checkbox" name="post[]" value="{{ post.id }}" style="display:none;" />
    			<a id="postmenuclick{{ post.id }}" class="dast-hide-tr" style="text-decoration:none;" onclick="showpostmenu({{ post.id }});"  href="javascript:void(0);">▲</a>
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
				<span class="filetitle">
					{{ post.subject }}
				</span>
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
					{{ post.name }}
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
			</label>
			<span class="reflink">
				{{ post.reflink|safe }}
			</span>
			<span class="extrabtns">
			{% if  post.locked == 1 %}
				<img style="border: 0;" src="{{ boardpath }}css/icons/locked.png" alt="{% trans _("Locked") %}" />
			{% endif %}
			{% if  post.stickied == 1 %}
				<img style="border: 0;" src="{{ boardpath }}css/icons/sticky.png" alt="{% trans _("Stickied") %}" />
			{% endif %}
			</span>
    			<span style="display: inline-block;" class="dnb" id="dnb-{{ board.name }}-{{ post.id }}-y">&nbsp;[<a href="javascript:void(0);" style="text-decoration:none;" onclick="return putdnblks('dnb-{{ board.name }}-{{ post.id }}-y');" data-toggle="modal" data-target="#modmodal">Мод</a>]</span>
			[<a href="{{ cf.KU_BOARDSFOLDER }}{{ board.name }}/res/{% if  post.parentid == 0 %}{{ post.id }}{% else %}{{ post.parentid }}{% endif %}.html">{% trans _("Ответ") %}</a>]
	{% if  cf.KU_FIRSTLAST  AND  post.stickied == 0  AND  post.replies > 50 %}
[<a href="{{ cf.KU_BOARDSFOLDER }}{{ board.name }}/res/050{{ post.id }}.html">{% trans _("+50") %}</a>]
{% endif %}
			<br />
		
    {% if post.file_count != 0 %}
<div class="post-files" style="display:inline" >
    {% for fileid,file in post.files %}
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
          <a href="/{{ board.name }}/src/{{ file.name }}/{{ file.original }}.{{ file.type }}">
        {% else %}
          [R: {{ file.rating_name }}]
          <a href="/{{ board.name }}/src/{{ file.name }}/{{ file.original }}.{{ file.type }}">
        {% endif %}
      </a>
      <a class="hide123," href="/download.php?id={{ file.name }}">{{ file.name }}.{{ file.type }}</a>
      - ({{ file.size_formatted }}
      {% if  file.image_w > 0  AND  file.image_h > 0 %}
        , {{ file.image_w }}x{{ file.image_h }}
      {% endif %}
      <span class="mobile_filename_hide">
      ,
      {% if  file.original %} {{ file.original }}{% else %} {{ file.name }}{% endif %}.{{ file.type }}
      </span>
      )
      </span>
 
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
{%endif%}
"
id="file_{{file.name}}_{{forloop.counter}}" class="file
{%if post.file_count > 1 %}
last_op_div
{%if forloop.counter > 1 %}
multi_thumb_{{post.id}}
{%endif%}
{%endif%}
">
      <span class="filesize fs_{{ post.id }}" id="fs_{{ post.id }}_{{ forloop.counter }}" style="display:none">
      {% if post.file_count == 1 %}
      Файл 
      {% else %}
      Файл [{{ forloop.counter }}/{{post.file_count}}]
      {% endif %}
     <a href="/{{ board.name }}/src/{{ file.name }}/{{ file.original }}.{{ file.type }}">
    </a>
    <a class="hide123," href="/download.php?id={{ file.name }}">{{ file.name }}.{{ file.type }}</a>
    - ({{ file.size_formatted }}
    {% if file.image_w > 0  AND  file.image_h > 0 %}
     , {{ file.image_w }}x{{ file.image_h }}
    {% endif %}
      <span class="mobile_filename_hide">
      {% if  file.file_original != ''  AND  file.original != file.name %}
        , {{ file.file_original }}.{{ file.type }}
      {% endif %}
      </span>
    )
    </span>
{%spacefull %}
{% endspacefull %}
    {% if  post.file == 'removed' %}
     <div class="nothumb">
      {% trans _("Файл<br />Удалён") %}
     </div>
    {% else %}
        <a
        {% if  cf.KU_NEWWINDOW %}
          target="_blank"
        {% endif %}
        href="{{ file_path }}/src/{{ file.name }}/{{ file.original }}.{{ file.type }}"
	onclick="javascript:expandimg('{{ post.id }}', '{{ file_path }}/src/{{ file.name }}.{{ file.type }}', '{{ file_path }}/thumb/{{ file.name }}s.{{ file.thumb_type }}', '{{ file.image_w }}', '{{ file.image_h }}', '{{ file.thumb_w }}', '{{ file.thumb_h }}');return false;"
	>
	<span id="thumb{{ file.name }}">
        <img src="/images/loading.gif" data-src=
	{% if file.rating_id %}
        "<?php if($p['show_spoiler_{{ file.rating_id }}']){ ?>{{ file_path }}/thumb/{{ file.name }}s.{% if file.type == 'webm' %}png{% else %}{{ file.type }}{% endif %}<?php }else{ ?>/images/{{ file.rating_file }}<?php } ?>" alt="{{ post.id }}" class="thumb lazyload{% if file.type == 'webm' %} webm-file{% endif %}" height="<?php if($p['show_spoiler_{{ file.rating_id }}']){ ?>{{ file.thumb_h }}<?php }else{ ?>{{ file.rating_thumb_h }}<?php } ?>" width="<?php if($p['show_spoiler_{{ file.rating_id }}']){ ?>{{ file.thumb_w }}<?php }else{ ?>{{ file.rating_thumb_w }}<?php } ?>"
	{% else %}
        "{{ file_path }}/thumb/{{ file.name }}s.{% if file.type == 'webm' %}png{% else %}{{ file.type }}{% endif %}" alt="{{ post.id }}" class="thumb op_thumb lazyload{% if file.type == 'webm' %} webm-file{% endif %}" height="{{ file.thumb_h }}" width="{{ file.thumb_w }}"
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
				<tr>
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
{%spacefull %}
{% endspacefull %}

						
						
						{% if  post.subject != '' %}
							<span class="filetitle">
								{{ post.subject }}
							</span>
						{% endif %}
								{% if post.IS_DELETED == 1 %}
								<span class="post_is_del" style="color:#ff0000">[del]</span> 
								{% endif %}
								<span class="postername">
                                                                {% if post.premod != 0 %}
                                                                <?php if($p['is_mod']){ ?>
                                                                <span class="post_is_premod" style="color:#ff0000">[pre]</span> 
                                                                <?php } ?>
                                                                {% endif %}
								
								{% if  post.email  AND  board.anonymous %}
									<a href="mailto:{{ post.email }}">
								{% endif %}
								{% if  post.name == ''  AND  post.tripcode == '' %}
									{{ board.anonymous }}
								{% else %}
									{{ post.name }}
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
						</label>

						<span class="reflink">
							{{ post.reflink|safe }}
						</span>
												<span class="extrabtns">
							{{ post.replylink|safe }}
						</span>
						<span class="extrabtns">
						{% if  post.locked == 1 %}
							<img style="border: 0;" src="{{ boardpath }}css/icons/locked.png" alt="{% trans _("Locked") %}" />
						{% endif %}
						{% if  post.stickied == 1 %}
							<img style="border: 0;" src="{{ boardpath }}css/icons/sticky.png" alt="{% trans _("Stickied") %}" />
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
          <a href="/{{ board.name }}/src/{{ file.name }}/{{ file.original }}.{{ file.type }}">
        {% else %}
          [R: {{ file.rating_name }}]
          <a href="/{{ board.name }}/src/{{ file.name }}/{{ file.original }}.{{ file.type }}">
        {% endif %}
      </a>
      <a class="hide123," href="/download.php?id={{ file.name }}">{{ file.name }}.{{ file.type }}</a>
      - ({{ file.size_formatted }}
      {% if  file.image_w > 0  AND  file.image_h > 0 %}
        , {{ file.image_w }}x{{ file.image_h }}
      {% endif %}
      <span class="mobile_filename_hide">
      ,
      {% if  file.original %} {{ file.original }}{% else %} {{ file.name }}{% endif %}.{{ file.type }}
      </span>
      )
      </span>
 
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
{%endif%}
"
id="file_{{file.name}}_{{forloop.counter}}" class="file
{%if post.file_count > 1 %}
last_op_div
{%if forloop.counter > 1 %}
multi_thumb_{{post.id}}
{%endif%}
{%endif%}
">
    {% if file.name != 'removed' %}
      <span class="filesize fs_{{ post.id }}" id="fs_{{ post.id }}_{{ forloop.counter }}" style="display:none">
{%spacefull %}
{% endspacefull %}
      {% if post.file_count == 1 %}
      Файл 
      {% else %}
      Файл [{{ forloop.counter }}/{{post.file_count}}]
      {% endif %}
        {% if  file.rating_file == '' %}
          <a href="/{{ board.name }}/src/{{ file.name }}/{{ file.original }}.{{ file.type }}">
        {% else %}
          [R: {{ file.rating_name }}]
          <a href="/{{ board.name }}/src/{{ file.name }}/{{ file.original }}.{{ file.type }}">
        {% endif %}
      </a>
      <a class="hide123," href="/download.php?id={{ file.name }}">{{ file.name }}.{{ file.type }}</a>
      - ({{ file.size_formatted }}
      {% if  file.image_w > 0  AND  file.image_h > 0 %}
        , {{ file.image_w }}x{{ file.image_h }}
      {% endif %}
      <span class="mobile_filename_hide">
      ,
      {% if  file.original %} {{ file.original }}{% else %} {{ file.name }}{% endif %}.{{ file.type }}
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
        <img src="/images/loading.gif" data-src=
	{% if file.rating_id %}
        "<?php if($p['show_spoiler_{{ file.rating_id }}']){ ?>{{ file_path }}/thumb/{{ file.name }}s.{% if file.type == 'webm' %}png{% else %}{{ file.type }}{% endif %}<?php }else{ ?>/images/{{ file.rating_file }}<?php } ?>" alt="{{ post.id }}" class="thumb lazyload{% if file.type == 'webm' %} webm-file{% endif %}" height="<?php if($p['show_spoiler_{{ file.rating_id }}']){ ?>{{ file.thumb_h }}<?php }else{ ?>{{ file.rating_thumb_h }}<?php } ?>" width="<?php if($p['show_spoiler_{{ file.rating_id }}']){ ?>{{ file.thumb_w }}<?php }else{ ?>{{ file.rating_thumb_w }}<?php } ?>"
	{% else %}
        "{{ file_path }}/thumb/{{ file.name }}s.{% if file.type == 'webm' %}png{% else %}{{ file.type }}{% endif %}" alt="{{ post.id }}" class="thumb lazyload{% if file.type == 'webm' %} webm-file{% endif %}" height="{{ file.thumb_h }}" width="{{ file.thumb_w }}"
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
{%spacefull %}
{% endspacefull %}
		{{ post.message|safe }}
		</blockquote>
</div>
		{% if  post.parentid == 0 %}
			</div>
			<div id="replies{{ post.id }}{{ board.name }}">
			{% if  post.replies %}
				<span class="omittedposts">
					{% if  post.stickied == 0 %}
						{{ post.replies }} 
						{% if  post.replies == 1 %}
							ответ
						{% else %}
							ответа
						{% endif %}
					{% else %}
						{{ post.replies }}
						{% if  post.replies == 1 %}
							ответ
						{% else %}
							ответа
						{% endif %}
					{% endif %}
					{% if  post.images > 0 %}
						{% trans _("and") %} {{ post.images }}
						{% if  post.images == 1 %}
							{% trans _("Image") %} 
						{% else %}
							{% trans _("Images") %} 
						{% endif %}
					{% endif %}
					{% trans _("Click Reply to view.") %}
					</span>
			{% endif %}
		{% else %}
			</div>
				</td>
			</tr>
		</tbody>
		</table>
{{ post.premod_after|safe }}
		
		{% endif %}
{%spacefull %}
{% endspacefull %}
{% if post.last_post %}
			</div>
			</div>
		<br clear="left" />
		<hr />
{% endif %}
{% endfor %}
</div>
