<?php
/*
 * This file is part of kusaba.
 *
 * kusaba is free software; you can redistribute it and/or modify it under the
  terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * kusaba is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * kusaba; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 * +------------------------------------------------------------------------------+
 * Upload class
 * +------------------------------------------------------------------------------+
 * Used for image/misc file upload through the post form on board/thread pages
 * +------------------------------------------------------------------------------+
 */

class Upload {

	private $files;
	private $isreply = false;
	private $is_ponyaba_post = false;

	// thanks to phpuser at gmail dot com 

	public function __construct($isrp = false, $ispn = false) {
		$this->isreply = $isrp;
		$this->is_ponyaba_post = $ispn;
	}

	public function get_file_ids() {
		global $board_class;
		$ids = Array();
		//bdl_debug('AAAA ' . serialize($this->files));
		//ksort($this->files);
		for ($i = 0; $i <= $board_class->board['maximages']; $i++) {
		//foreach ($this->files as $file) {
			if(isset($this->files[$i]['fileid'])) {
			$id = $this->files[$i]['fileid'];
			$ids[] = Array("fileid" => $id);
			}
		}
		return $ids;
	}

	public function get_file_stuff() {
		return $this->files;
	}
	
	public function get_file_names($num){
		return $this->files[$num]['file_name'];
	}
	
	public function get_file_original($num){
		return $this->files[$num]['original_file_name'];
	}
	
	public function get_file_md5($num){
		return $this->files[$num]['file_md5'];
	}

	private function reArrayFiles() {
		global $board_class;
	
		$file_ary = false;

		if ($this->is_ponyaba_post) {
			$file_ary[0] = Array("name" => "fake ponyaba file");
			return $file_ary;
		}

		if (isset($_FILES['upload'])) {
		bdl_debug('AAAA ' . serialize($_FILES['upload']));
			$file_post = $_FILES['upload'];
			$file_ary = array();
			foreach($file_post as $file) {
				if(trim($file['name']) == "") unset ($file['name']);
			}
			$file_count = count($file_post['name']);
			$file_keys = array_keys($file_post);
			
			$j = 0;
			for ($i = 0; $i < $file_count; $i++) {
				
				// if the i-th file has an md5 field, this means it is either uploaded via
				// md5 magic or via booru uploaded, skip it, so we will have an empty slot in file_ary.
				// this slot will later be filled with info from db (in case of magic) or later here in case of booru.
				if (trim($_POST['md5-' . $i]) == '') {
					foreach ($file_keys as $key) {
						$file_ary[$i][$key] = $file_post[$key][$j];
					}
					$j++;
				} else {
					bdl_debug ('skipping file # ' . $i . ' as it has md5 set');
				}
			}
		bdl_debug('AAAA ' . serialize($file_ary));
		}

		// booru downloading goes here
		for ($md5num = 0; $md5num <= $board_class->board['maximages']; $md5num++) {
			bdl_debug("looking at " . $md5num. ' = ' . $_POST['md5-'.$md5num]);
			if (substr($_POST['md5-'.$md5num], 0, 7) === '[derpi]') {
				bdl_debug("got derpi");
				$encoded_url = substr($_POST['md5-'.$md5num], 7);
				if ($url = base64_decode ($encoded_url, true)) {
					$url = 'https://derpicdn.net/' . $url;
					bdl_debug (' will load from derpibooru : ' . $url);
					$ch = curl_init();
					$file = tempnam('/tmp', 'derpi');
					$handle = fopen($file, 'w');
					$handle_e = fopen($file.'.err', 'w');
					curl_setopt($ch, CURLOPT_FILE, $handle);
					curl_setopt($ch, CURLOPT_STDERR, $handle_e);
    					curl_setopt($ch, CURLOPT_URL, $url);
    					curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    					curl_setopt($ch, CURLOPT_VERBOSE, 0);
     					//curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
     					curl_setopt($ch, CURLOPT_AUTOREFERER, false);
     					curl_setopt($ch, CURLOPT_REFERER, "http://www.ponyach.ru");
     					curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
     					curl_setopt($ch, CURLOPT_HEADER, 0);
     					$result = curl_exec($ch);
     					curl_close($ch);
					fclose($handle);
					fclose($handle_e);
					unset($_POST['md5-'.$md5num]);

					if ($result) {
					$a = explode('/', $url);
					$name = end($a);
					
					// insert the downloaded "file" into appropriate place.
					$file_ary[$md5num - 1] = Array(
						'name' => $name,
						'size' => filesize($file),
						'error' => UPLOAD_ERR_OK,
						'tmp_name' => $file,
						'booru' => 'derpi'
					);
					}; // else -- we should inform user of a failure, maybe.
				}
			}
		}
		return $file_ary;
	}

	public function set_isreply() {
		$this->isreply = true;
	}

	public function HandleUpload() {
		global $tc_db, $board_class;
		bdl_debug ("handle upload");
		$this->HandleUpload_md5();
		$this->HandleUpload_post();
		//$this->HandleInPostDedup();
		//$this->HanldePostDedup24h();
	}

	// remove non-unique files in one post
	public function HandleInPostDedup() {
		$features = get_session_features();

		if (in_array('2', $features, true)) {
			bdl_debug('this user has an in-post NON-DUP feature');
			bdl_debug(serialize($this->files));
			$uniq_files = Array();
			$new_files = Array();
			foreach ($this->files as $file_num => $file) {
				if (trim($file['file_name']) != "") {
					if (array_key_exists($file['file_name'], $uniq_files)) {
						// removing dup
						bdl_debug('file ' . $file['file_name'] . ' already is in post, removing.', $uniq_files);
					} else {
						$uniq_files[$file['file_name']] = true;
						$new_files[] = $this->files[$file_num];
					}
				}
			}
			$this->files = array_reverse($new_files);
		}
	}

	public function HanldePostDedup24h() {
		global $tc_db;
		
		$features = get_session_features();

		if (in_array('1', $features, true)) {
		foreach ($this->files as $file_num => $file) {
                        $queue = 'select count(*) from posts p left join posts_files pf on p.id = pf.postid and p.boardid = pf.boardid left join files f on pf.fileid = f.id where p.session_md5=md5('.$tc_db->qstr(session_id()).') and ((f.md5 = '.$tc_db->qstr($file['file_md5']) . ' or f.md5_light = '.$tc_db->qstr($file['file_md5']) . ') or (f.name = '.$tc_db->qstr($file['file_name']) . ')) and p.timestamp > unix_timestamp() - 86400';

//			$queue = 'select count(*) from posts p left join posts_files pf on p.id = pf.postid and p.boardid = pf.boardid left join files f on pf.fileid = f.id where p.session_md5=md5('.$tc_db->qstr(session_id()).') and f.name = '.$tc_db->qstr($file['file_name']) . ' and p.timestamp > unix_timestamp() - 86400';
			if($tc_db->GetOne($queue) > 0) {
				xdie('ты уже постил эту картинку сегодня, прости.');
			}
		}
		}
	}

	public function HandleUploadPonyaba($files_imagefile_tmp_name = false, $files_imagefile_name= false) {
		global $tc_db, $board_class;
		if ($files_imagefile_tmp_name) {
			$this->is_ponyaba_post = true;
			$this->files[1]['files_imagefile_tmp_name'] = $files_imagefile_tmp_name;
			$this->files[1]['files_imagefile_name'] = $files_imagefile_name;
			$this->HandleUpload_post();
		}
	}

	private function HandleUpload_post() {
		global $tc_db, $board_class;

		$post_files = $this->reArrayFiles();
	
		if ($post_files) {
		for ($filenum = 0; $filenum  <= $board_class->board['maximages']; $filenum++) {
			$file = $post_files[$filenum];

			// skipping md5-uploaded files.
			if (isset($_POST['md5-'.$filenum]) && $_POST['md5-'.$filenum] != '') continue;

			bdl_debug ("working with file " . $file['tmp_name']);
			
			$imagefile_name = '';
			if ($this->is_ponyaba_post == true) {
				bdl_debug ('using file : ' . $this->files[$filenum]['files_imagefile_tmp_name']);
				$this->files[$filenum]['files_imagefile_size'] = filesize($this->files[$filenum]['files_imagefile_tmp_name']);
				$this->files[$filenum]['files_imagefile_error'] = UPLOAD_ERR_OK;
				$imagefile_name = $this->files[$filenum]['files_imagefile_name'];
			} else {
				if (isset($file['tmp_name']) && trim($file['tmp_name']) != "") {
					$this->files[$filenum]['files_imagefile_name']       = $file['name'];
					$this->files[$filenum]['files_imagefile_size']       = $file['size'];
					$this->files[$filenum]['files_imagefile_error']      = $file['error'];
					$this->files[$filenum]['files_imagefile_tmp_name']   = $file['tmp_name'];
					if (isset ($file['booru'])) {
						$this->files[$filenum]['booru']   = $file['booru'];
					} else {
						$this->files[$filenum]['booru']   = false;
					}
					$imagefile_name = $this->files[$filenum]['files_imagefile_name'];
				}
			}

			if (trim($imagefile_name) != '') {

				if ($this->files[$filenum]['files_imagefile_size'] > $board_class->board['maximagesize']) {
					exitWithErrorPage(sprintf(_gettext('Please make sure your file is smaller than %dB'), $board_class->board['maximagesize']));
				}
			
				$this->switch_on_error($this->files[$filenum]['files_imagefile_error']);

				if (!is_file($this->files[$filenum]['files_imagefile_tmp_name'])) {
					$pass = false; $ferror = "файл не закачался на сервер";
				}
				if (!is_readable($this->files[$filenum]['files_imagefile_tmp_name'])) {
					bdl_debug('unable to read - ' . $this->files[$filenum]['files_imagefile_tmp_name']);
					$pass = false; $ferror = "мне не хватает прав";
				} 

				// loading image here and passing loaded image everywhere
				$x = explode ('.', $this->files[$filenum]['files_imagefile_name']);
                                $extension = end($x); // fuuckin php
				$file_to_preview = $this->files[$filenum]['files_imagefile_tmp_name'];

				if ($pass !== false) {
				if ($extension == 'webm') {
					get_frame_from_webm($file_to_preview, '/tmp/converted.png');
					$file_to_preview = '/tmp/converted.png';
				}
				$image = new Gmagick ();
				try {
					bdl_debug ('opening image ' . $file_to_preview);
					$image->read($file_to_preview);
					if ($image->getNumberImages() > 1) {
						$image->coalesceImages();
						//$image = $image->flattenImages();
						$image->setImageFormat('JPG');
						$image->writeImage('/tmp/workaround_gif.jpg');
						$image = new Gmagick ();
						$image->read('/tmp/workaround_gif.jpg');
						$this->files[$filenum]['file_type'] = '.gif';

					}
					if (!$this->files[$filenum]['file_type']) {
						bdl_debug ('detecting file type');
						if ($this->files[$filenum]['file_type'] = convert_gm_to_db_filetype ($image->getImageFormat ())) {
							$this->files[$filenum]['file_type'] = '.' . $this->files[$filenum]['file_type'];
							$pass = true;
						} else {
							$pass = false; $ferror = "Формат файла не поддерживается.";
						}
					} else {
						$pass = true;
					}
					// workaround for strange 1x1 gifs
					if ($image->getImageWidth () == 1 && $image->getImageHeight ()) {
						bdl_debug ('WORKAROUND: throwing on 1x1 gif');
						throw new Exception ('1x1');
					}
				} catch (exception $e) {
					bdl_debug ('GM exception - ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
					// trying to workaround animated gif problem
					if ($extension == 'gif') {
						bdl_debug ('GIF workaround');
						$src_img = imagecreatefromgif($file_to_preview);
						if ($src_img) {
							imagejpeg($src_img, '/tmp/gif_workaround.jpg');
							$imageDim = getimagesize('/tmp/gif_workaround.jpg');
							$this->files[$filenum]['imgWidth'] = $imageDim[0];
							$this->files[$filenum]['imgHeight'] = $imageDim[1];
							try {
								$image = new Gmagick ();
								$image->read('/tmp/gif_workaround.jpg');
								$pass = true;
								$this->files[$filenum]['file_type'] = '.gif';
							} catch (exception $e) {
								$pass = false; $ferror = "Файл не очень какой-то.";
							}
						} else {
							$pass = false; $ferror = "Файл не очень какой-то.";
						}
					}
				}
				if ($extension == 'webm') {
					$this->files[$filenum]['file_type'] = '.webm';
				}
				}

				if (!$pass) {
					exitWithErrorPage(_gettext('Ошибка в передаче файла : ' . $ferror . '.'));
				}

				$this->files[$filenum]['file_name'] = substr(htmlspecialchars(preg_replace('/(.*)\..+/','\1',$this->files[$filenum]['files_imagefile_name']), ENT_QUOTES), 0, 50);
				$this->files[$filenum]['file_name'] = str_replace('.','_',$this->files[$filenum]['file_name']);
				$this->files[$filenum]['original_file_name'] = $this->files[$filenum]['file_name'];
				$this->files[$filenum]['file_md5'] = hash_file('sha512', $this->files[$filenum]['files_imagefile_tmp_name']);
				$this->files[$filenum]['file_md5_light'] = md5_antikukla($this->files[$filenum]['files_imagefile_tmp_name'], $extension);

				bdl_debug('File ' .$this->files[$filenum]['file_name']. ' sha512 is ' .$this->files[$filenum]['file_md5']. ' and md5_light is ' .$this->files[$filenum]['file_md5_light']);

				//if ($extension != 'webm') {
				if (!$this->files[$filenum]['imgWidth']) {
					$this->files[$filenum]['imgWidth'] = $image->getImageWidth ();
					$this->files[$filenum]['imgHeight'] = $image->getImageHeight ();
				//}

				if (! (($this->files[$filenum]['imgWidth'] <= $board_class->board['maxwidth']) && ($this->files[$filenum]['imgHeight'] <= $board_class->board['maxheigth']) )) {
					exitWithErrorPage('Максимально допустимое разрешение изображение  ' . $board_class->board['maxwidth']. 'x' . $board_class->board['maxheigth']);
				}
				}

				$this->files[$filenum]['file_type'] = strtolower($this->files[$filenum]['file_type']);
				$this->files[$filenum]['file_size'] = $this->files[$filenum]['files_imagefile_size'];

				bdl_debug ($this->files[$filenum]['file_type']);

				$filetype_forcethumb = $tc_db->GetOne("SELECT " . KU_DBPREFIX . "filetypes.force_thumb FROM " . KU_DBPREFIX . "boards, " . KU_DBPREFIX . "filetypes, " . KU_DBPREFIX . "board_filetypes WHERE " . KU_DBPREFIX . "boards.id = " . KU_DBPREFIX . "board_filetypes.boardid AND " . KU_DBPREFIX . "filetypes.id = " . KU_DBPREFIX . "board_filetypes.typeid AND " . KU_DBPREFIX . "boards.name = '" . $board_class->board['name'] . "' and " . KU_DBPREFIX . "filetypes.filetype = '" . substr($this->files[$filenum]['file_type'], 1) . "';");
				if ($filetype_forcethumb != '') { // this shit actualy checks if a filetype is allowed
					do { // keep generating filename, until a free found
						$this->files[$filenum]['file_name'] = time() . mt_rand(0, 9) . mt_rand(1, 9); // original kusaba code didn't allow to end file in double zeroes. i don't know why, but left legacy behaiviour.
					} while (file_exists(KU_SRCDIR . $this->files[$filenum]['file_name'] . $this->files[$filenum]['file_type']));

						$query = "select id, name, thumb_w, thumb_h from " . KU_DBPREFIX . "files where " . KU_DBPREFIX . "md5_light=" . $tc_db->qstr($this->files[$filenum]['file_md5_light']) . " limit 1";
						$same_file = $tc_db->GetRow($query);
						//bdl_debug($query);
						if ($same_file['name'] != '') {
							$this->files[$filenum]['file_name'] = $same_file['name'];
							$this->files[$filenum]['file_location'] = KU_SRCDIR . $this->files[$filenum]['file_name'] . $this->files[$filenum]['file_type'];
							$this->files[$filenum]['file_thumb_location'] = KU_THUMBDIR. $this->files[$filenum]['file_name'] . 's' . $this->files[$filenum]['file_type'];
							$this->files[$filenum]['imgWidth_thumb'] = $same_file['thumb_w'];
							$this->files[$filenum]['imgHeight_thumb'] = $same_file['thumb_h'];
							$this->files[$filenum]['fileid'] = $same_file['id'];
							if (!($this->is_ponyaba_post)) @unlink ($this->files_imagefile_tmp_name);
						}else{
							$this->files[$filenum]['file_location'] = KU_SRCDIR . $this->files[$filenum]['file_name'] . $this->files[$filenum]['file_type'];
							if ($this->files[$filenum]['file_type'] == '.webm') {
								$this->files[$filenum]['file_thumb_location'] = KU_THUMBDIR . $this->files[$filenum]['file_name'] . 's.png';
							} else {
								$this->files[$filenum]['file_thumb_location'] = KU_THUMBDIR . $this->files[$filenum]['file_name'] . 's' . $this->files[$filenum]['file_type'];
							}

							if ($this->is_ponyaba_post) {
								copy ($this->files[$filenum]['files_imagefile_tmp_name'], $this->files[$filenum]['file_location']);
							} else {
								bdl_debug ('moving from ' . $this->files[$filenum]['files_imagefile_tmp_name'] . ' to ' . $this->files[$filenum]['file_location'] . ' booru = ' . $this->files[$filenum]['booru'] );
								if ($this->files[$filenum]['booru']) {
								if (!rename($this->files[$filenum]['files_imagefile_tmp_name'], $this->files[$filenum]['file_location'])) {
									exitWithErrorPage(_gettext('Could not copy uploaded image.'));
								}
								} else {
								if (!move_uploaded_file($this->files[$filenum]['files_imagefile_tmp_name'], $this->files[$filenum]['file_location'])) {
									exitWithErrorPage(_gettext('Could not copy uploaded image.'));
								}
								}
							}
							chmod($this->files[$filenum]['file_location'], 0644);

							if ($this->files[$filenum]['files_imagefile_size'] == filesize($this->files[$filenum]['file_location'])) {

								if ($this->isreply) {
									$thumb_max_w = KU_REPLYTHUMBWIDTH; $thumb_max_h = KU_REPLYTHUMBHEIGHT;
								} else {
									$thumb_max_w = KU_THUMBWIDTH; $thumb_max_h = KU_THUMBHEIGHT;
								}
									if (!createThumbnailGM($image, $this->files[$filenum]['file_thumb_location'], $thumb_max_w, $thumb_max_h)) {
										exitWithErrorPage(_gettext('Could not create thumbnail.'));
									}

								$imageDim_thumb = getimagesize($this->files[$filenum]['file_thumb_location']);
								$this->files[$filenum]['imgWidth_thumb'] = $imageDim_thumb[0];
								$this->files[$filenum]['imgHeight_thumb'] = $imageDim_thumb[1];
								$imageused = true;
							} else {
								exitWithErrorPage(_gettext('File was not fully uploaded. Please go back and try again.'));
							}
						}
				} else {
					exitWithErrorPage(_gettext('Sorry, that filetype :'. $this->file_type .' is not allowed on this board.'));
				}

				if (! isset($this->files[$filenum]['fileid'])) {
					$filetype = filetype_name_to_id(substr($this->files[$filenum]['file_type'], 1));
					$query = ("insert into files (name, md5, md5_light, type, original, size, size_formatted, image_w, image_h, thumb_w, thumb_h)
						values (".$tc_db->qstr($this->files[$filenum]['file_name']).", ".$tc_db->qstr($this->files[$filenum]['file_md5']).", " .$tc_db->qstr($this->files[$filenum]['file_md5_light']).", " .
							  $tc_db->qstr($filetype).", ".$tc_db->qstr($this->files[$filenum]['original_file_name']).", " .
							  $tc_db->qstr($this->files[$filenum]['file_size']).", ".$tc_db->qstr(ConvertBytes($this->files[$filenum]['file_size'])).", " .
							  $tc_db->qstr($this->files[$filenum]['imgWidth']).", ".$tc_db->qstr($this->files[$filenum]['imgHeight']).", " .
							  $tc_db->qstr($this->files[$filenum]['imgWidth_thumb']).", ".$tc_db->qstr($this->files[$filenum]['imgHeight_thumb'])."
					)");
					//bdl_debug('QUERY--' .$query);
					$tc_db->Execute($query);
					$this->files[$filenum]['fileid'] = $tc_db->Insert_Id();
				}
			}

		}
		}
	}

	private function HandleUpload_md5() {
		global $tc_db, $board_class;

		if (!$this->is_ponyaba_post) {
		for ($md5num = 0; $md5num <= $board_class->board['maximages']; $md5num++) {
			$file_md5 = trim($_POST['md5-' . $md5num]);
			if ($file_md5 != '' && base64_decode ($file_md5, true)) { // checking if md5 is a valid base64 string (alphanum is not enough)
				$query = "
					select f.id, f.name, f.size, f.original, ft.filetype, 
					       f.thumb_w, f.thumb_h, f.image_w, f.image_h 
					from files f
					       inner join filetypes ft on f.type = ft.id
					where f.md5_light=" . $tc_db->qstr($file_md5) . " limit 1";
				$same_file = $tc_db->GetRow($query);
				//bdl_debug($query);
				if ($same_file) {
					bdl_debug ("got a known md5 for file # " . $md5num);
					$this->files[$md5num]['file_name'] = $same_file['name'];
					$this->files[$md5num]['file_type'] = '.'.$same_file['type'];
					$this->files[$md5num]['file_location'] = KU_SRCDIR . $this->file_name . $this->file_type;
					$this->files[$md5num]['file_thumb_location'] = KU_THUMBDIR . $this->file_name . 's' . $this->file_type;
					$this->files[$md5num]['imgWidth_thumb'] = $same_file['thumb_w'];
					$this->files[$md5num]['imgHeight_thumb'] = $same_file['thumb_h'];
					$this->files[$md5num]['original_file_name'] = $same_file['original'];
					$this->files[$md5num]['file_size'] = $same_file['size'];
					$this->files[$md5num]['file_md5'] = $file_md5;
					$this->files[$md5num]['imgWidth'] = $same_file['image_w'];
					$this->files[$md5num]['imgHeight'] = $same_file['image_h'];
					$this->files[$md5num]['fileid'] = $same_file['id'];
				}
			}
		}
		}
	
	}

	private function switch_on_error($error) {
		switch ($error) {
			case UPLOAD_ERR_OK:
				break;
			case UPLOAD_ERR_INI_SIZE:
				exitWithErrorPage(sprintf(_gettext('The uploaded file exceeds the upload_max_filesize directive (%s) in php.ini.')), ini_get('upload_max_filesize'));
				break;
			case UPLOAD_ERR_FORM_SIZE:
				exitWithErrorPage(sprintf(_gettext('Please make sure your file is smaller than %dB'), $board_class->board['maximagesize']));
				break;
			case UPLOAD_ERR_PARTIAL:
				exitWithErrorPage(_gettext('The uploaded file was only partially uploaded.'));
				break;
			case UPLOAD_ERR_NO_FILE:
				exitWithErrorPage(_gettext('No file was uploaded.'));
				break;
			case UPLOAD_ERR_NO_TMP_DIR:
				exitWithErrorPage(_gettext('Missing a temporary folder.'));
				break;
			case UPLOAD_ERR_CANT_WRITE:
				exitWithErrorPage(_gettext('Failed to write file to disk'));
				break;
			default:
				exitWithErrorPage(_gettext('Unknown File Error'));
		}
	}
}
?>
