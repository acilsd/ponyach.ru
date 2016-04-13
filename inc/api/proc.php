<?php

require_once ($cf['PN_ROOTDIR']) . "inc/api/db.php";
require_once ($cf['PN_ROOTDIR']) . "inc/api/req_processors.php";
require_once ($cf['PN_ROOTDIR']) . "inc/api/asserts.php";

assert_options(ASSERT_ACTIVE,   true); // TODO: remove?
assert_options(ASSERT_BAIL,     true);
assert_options(ASSERT_WARNING,  false);

// globals
$status = "okay";

function get_format () { return isset ($_REQUEST['f']) ? $_REQUEST['f'] : "j"; } // json by default

function get_request_queue () {
	if (isset ($_GET['m'])) {
		// assuming GET request metod is used
		$request['mode'] = $_GET['m'];
		$request['board_name'] = $_GET['b'];
		$request['post_id'] = $_GET['p'];
		$request['thread_id'] = $_GET['t'];
		$request['page'] = $_GET['pg'];
		$request['timestamp'] = $_GET['ts'];
		$queue[] = $request;
	
	} else if (isset ($_POST['data'])) {
		// POST method - there will be dra^W cycles. or not.
		$queue = json_decode ($_POST['data'], true);  
		if ($error = json_last_error())
			die ("bad json"); 
	} else {
		// i don't know what this complete strager wants and can't do anything about it
		die ("stay out of my shed");
	}
	return $queue;
}

function process_request_queue ($queue, $format) {
	global $no_cache_modes;
	foreach ($queue as $request) {
		$mc_key = get_mc_key ($request, $format);
		if ((! ($result = cache_get ($mc_key)) ) || (in_array ($request['mode'], $no_cache_modes) )) {
			debug (" cache not hit");
			connect_db ();
			$result = process_responce (process_request ($request), $format);
			if (!(in_array ($request['mode'], $no_cache_modes))) //TODO: inmemory status
				cache_add ($mc_key, $result);
			$data[] = $result;
		
		} else {
			debug (" cache hit");
			$data[] = $result;
		}
	}
	return $data;
}

function process_responce_queue ($queue, $format) {
	global $status;
	$result = '';

	switch ($format) {
		case "p": // groowy json
		case "j": // json
			// do header
			header('Content-type: application/json;charset=utf-8');
			$version = 1;
			$timestamp = time();
			$result .= "{\n\"version\": $version,\n\"timestamp\": $timestamp,\n\"status\": \"$status\",\n\"data\": [\n";
			// body
			$result .= implode ("\n,\n", $queue);
			// footer
			$result .= "\n]\n}";
		break;
		default:
			return "unsupported format";
		break;
	}
	return $result;
}

function get_mc_key ($request, $format = "j") {
	// calculating memcache key for this request
	
	$mc_key = ":$format:";
	ksort ($request);
	foreach ($request as $key => $val) {
		$mc_key .= ($key . '=' . $val . ':');
	}
	return $mc_key;
}

function clear_row_data ($input) {
	// if given an array - clean it recursivly, else just leave it alone
	
	if (is_array ($input)) {
		foreach ($input as &$value) {
			if (is_array ($value)) {
				$value = clear_row_data ($value); // may use 'strlen' as callback
			}
		}
   
		return array_filter ($input);
	} else {
		return $input;
	}
}

function process_request ($request) {
	global $status;

	debug (" >>> processing request");

	$process_request_callback = "process_request_". $request['mode'];
	$assert_request_callback = "can_process_request_". $request['mode'];

	if (function_exists ($assert_request_callback)) {
		if (($assert_err = call_user_func_array ($assert_request_callback, array ($request))) === true) {
			if (function_exists ($process_request_callback)){
				debug ("running ". $process_request_callback);
				$result = clear_row_data (call_user_func_array ($process_request_callback, array ($request)));
			} else {
				debug ("missing callback function for ". $request['mode']);
       				$result["error"] = "unsupported mode"; $status = "chotto";
			}
		} else {
			$result["error"] = $assert_err; $status = "chotto";
		}
	} else {
		debug ("missing assert callback for ". $request['mode']);
       		$result["error"] = "unsupported mode"; $status = "chotto";
	}

	return $result;
}

		
function process_responce ($responce, $format = "j") {
	
	switch ($format) {
		case "j": // json
			$result = json_encode ($responce, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT | JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
			$error = json_last_error ();
			if ($error) 
				$result = $error;
		break;
		case "p": // pretty json
			$result = json_encode ($responce, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT | JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
			$error = json_last_error ();
			if ($error) 
				$result = $error;
		break;
		case "x": // xml
			$result = XmlSerializer::toXml ($responce);
		break;
		default:
			$result = "unknown format";
		break;
	}
	
	return $result;
}

?>
