<?php
        $mc = new Memcached;
        $mc->addServer('127.0.0.1', 11211) or debug (" >>>>>> can't connect to memcached"); 

        /*
         *    common functions
         */

        function cache_add ($key, $obj, $time = 3600) {
                global $mc;
                debug (" caching object : " . $key);
                $mc->set($key, $obj, $time) or debug (" error caching data"); 
        }

        function cache_get ($key) {
                global $mc;
                debug (" retrieving object : " . $key);
                if (!$res = $mc->get($key)) {
                        debug (" error: " . $mc->getResultCode());
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
		if (! $mc->flush ())
			debug ("flush error");
	}

	function cache_grep ($expr) {
	// find keys by expression or ALL expressions
                global $mc;
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
                		return $mc->delete($k); //TODO error
                		debug (" deleting object : " . $k);
			}
		} else {
                	return $mc->delete($key); //TODO error
                	debug (" deleting object : " . $key);
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
