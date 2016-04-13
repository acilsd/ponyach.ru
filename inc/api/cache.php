<?php
        $mc = new Memcached;
        $mc->addServer('127.0.0.1', 11211) or debug (" >>>>>> can't connect to memcached"); 

        /*
         *    common functions
         */

        function cache_add ($key, $obj, $time = 3600) {
                global $mc;
                debug (" caching object : " . $key);
                $mc->set('pony-' . $key, $obj, $time) or debug (" error caching data"); 
                $mc->set('pony-time-' . $key, time(), $time) or debug (" error caching data"); 
        }

        function cache_get ($key) {
                global $mc;
                debug (" retrieving object : " . $key);
                $res = $mc->get('pony-' . $key);
                if ($mc->getResultCode() != Memcached::RES_SUCCESS) {
			debug (" error: " . $mc->getResultCode());
			return false;
                }
                return $res;
        }

	function cache_get_time ($key) {
                global $mc;
                debug (" retrieving time of object : " . $key);
                $res = $mc->get('pony-time-' . $key);
                if ($mc->getResultCode() != Memcached::RES_SUCCESS) {
			debug (" error: " . $mc->getResultCode());
			return false;
                }
                return $res;
	}

	function cache_get_all () {
                global $mc;
                debug (" retrieving all keys");
		return $mc->getAllKeys ();
	}

	function cache_flush () {
		global $mc;
		debug (" flushing cache");
//		if (! $mc->flush ())
//			debug ("flush error");
		$all = cache_get_all ();
		foreach ($all as $key) {
			if (strpos ($key, 'pony-') === 0) $mc->Delete($key);
		}
	}

	function cache_grep ($expr) {
	// find keys by expression or ALL expressions
                global $mc;
		$res = array ();
		$all = cache_get_all ();
		foreach ($all as $key) {
			if (is_array ($expr)) {
				$do_del = true;
				foreach ($expr as $e) {
					if (strpos ($key, ':'.$e.':') === false) {
						$do_del = false;
						break;
					}
				}
				if ($do_del)
					$res[] = $key;
			} else {
				if (strpos ($key, ':'.$expr.':')) {
					$res[] = $key;
				}
			}
		}
		return $res;
	}

	function cache_grep_count ($expr) {
	// count keys by expression or ALL expressions
                global $mc;
		$all = cache_get_all ();
		$res = 0;
		foreach ($all as $key) {
			if (is_array ($expr)) {
				$do_del = true;
				foreach ($expr as $e) {
					if (strpos ($key, $e) === false) {
						$do_del = false;
						break;
					}
				}
				if ($do_del)
					$res++;
			} else {
				if (strpos ($key, $expr) !== false) {
					$res++;
				}
			}
		}
		return $res;
	}


        function cache_del ($key) {
                global $mc;
		if (is_array ($key)) {
			foreach ($key as $k) {
                		debug (" deleting object : " . $k);
				$mc->delete('pony-time-' .$k);
                		return $mc->delete('pony-' .$k); //TODO error
			}
		} else {
                	debug (" deleting object : " . $key);
			$mc->delete('pony-time-' .$key);
                	return $mc->delete('pony-' .$key); //TODO error
		}
        }

	function cache_inval ($expr) {
		// invalidates caches by given expression. ex: b=test, m=board
		// $expr is array or string
		debug (" invalidating cache for " . serialize ($expr));
		if ($keys = cache_grep ($expr))
			cache_del ($keys);
		debug (" removed ". count($keys) ." keys");
	}

?>
