<?php

/*
 * a hack to fix cloud header mess
*/
//if ($_SERVER['HTTP_X_FORWARDED_FOR'] != $_SERVER['REMOTE_ADDR']) {
//	$_SERVER['HTTP_X_FORWARDED_FOR'] = str_replace (', ' . $_SERVER['REMOTE_ADDR'], '', $_SERVER['HTTP_X_FORWARDED_FOR']);
//	$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
//}
//$_SERVER['SERVER_ADDR'] = '82.146.36.52';
/*
 *
*/

$cloud_nets = Array('78.47.99','204.93.240','204.93.177','199.27.128','199.27.129','199.27.130','199.27.131','199.27.132','199.27.133','199.27.134','199.27.135','173.245.48','173.245.49','173.245.50','173.245.51','173.245.52','173.245.53','173.245.54','173.245.55','173.245.56','173.245.57','173.245.58','173.245.59','173.245.60','173.245.61','173.245.62','173.245.63','103.21.244','103.21.245','103.21.246','103.21.247','103.22.200','103.22.201','103.22.202','103.22.203','103.31.4','103.31.5','103.31.6','103.31.7','141.101.64','141.101.65','141.101.66','141.101.67','141.101.68','141.101.69','141.101.70','141.101.71','141.101.72','141.101.73','141.101.74','141.101.75','141.101.76','141.101.77','141.101.78','141.101.79','141.101.80','141.101.81','141.101.82','141.101.83','141.101.84','141.101.85','141.101.86','141.101.87','141.101.88','141.101.89','141.101.90','141.101.91','141.101.92','141.101.93','141.101.94','141.101.95','141.101.96','141.101.97','141.101.98','141.101.99','141.101.100','141.101.101','141.101.102','141.101.103','141.101.104','141.101.105','141.101.106','141.101.107','141.101.108','141.101.109','141.101.110','141.101.111','141.101.112','141.101.113','141.101.114','141.101.115','141.101.116','141.101.117','141.101.118','141.101.119','141.101.120','141.101.121','141.101.122','141.101.123','141.101.124','141.101.125','141.101.126','141.101.127','108.162.192','108.162.193','108.162.194','108.162.195','108.162.196','108.162.197','108.162.198','108.162.199','108.162.200','108.162.201','108.162.202','108.162.203','108.162.204','108.162.205','108.162.206','108.162.207','108.162.208','108.162.209','108.162.210','108.162.211','108.162.212','108.162.213','108.162.214','108.162.215','108.162.216','108.162.217','108.162.218','108.162.219','108.162.220','108.162.221','108.162.222','108.162.223','108.162.224','108.162.225','108.162.226','108.162.227','108.162.228','108.162.229','108.162.230','108.162.231','108.162.232','108.162.233','108.162.234','108.162.235','108.162.236','108.162.237','108.162.238','108.162.239','108.162.240','108.162.241','108.162.242','108.162.243','108.162.244','108.162.245','108.162.246','108.162.247','108.162.248','108.162.249','108.162.250','108.162.251','108.162.252','108.162.253','108.162.254','108.162.255','190.93.240','190.93.241','190.93.242','190.93.243','190.93.244','190.93.245','190.93.246','190.93.247','190.93.248','190.93.249','190.93.250','190.93.251','190.93.252','190.93.253','190.93.254','190.93.255','188.114.96','188.114.97','188.114.98','188.114.99','188.114.100','188.114.101','188.114.102','188.114.103','188.114.104','188.114.105','188.114.106','188.114.107','188.114.108','188.114.109','188.114.110','188.114.111','197.234.240','197.234.241','197.234.242','197.234.243','198.41.128','198.41.129','198.41.130','198.41.131','198.41.132','198.41.133','198.41.134','198.41.135','198.41.136','198.41.137','198.41.138','198.41.139','198.41.140','198.41.141','198.41.142','198.41.143','198.41.144','198.41.145','198.41.146','198.41.147','198.41.148','198.41.149','198.41.150','198.41.151','198.41.152','198.41.153','198.41.154','198.41.155','198.41.156','198.41.157','198.41.158','198.41.159','198.41.160','198.41.161','198.41.162','198.41.163','198.41.164','198.41.165','198.41.166','198.41.167','198.41.168','198.41.169','198.41.170','198.41.171','198.41.172','198.41.173','198.41.174','198.41.175','198.41.176','198.41.177','198.41.178','198.41.179','198.41.180','198.41.181','198.41.182','198.41.183','198.41.184','198.41.185','198.41.186','198.41.187','198.41.188','198.41.189','198.41.190','198.41.191','198.41.192','198.41.193','198.41.194','198.41.195','198.41.196','198.41.197','198.41.198','198.41.199','198.41.200','198.41.201','198.41.202','198.41.203','198.41.204','198.41.205','198.41.206','198.41.207','198.41.208','198.41.209','198.41.210','198.41.211','198.41.212','198.41.213','198.41.214','198.41.215','198.41.216','198.41.217','198.41.218','198.41.219','198.41.220','198.41.221','198.41.222','198.41.223','198.41.224','198.41.225','198.41.226','198.41.227','198.41.228','198.41.229','198.41.230','198.41.231','198.41.232','198.41.233','198.41.234','198.41.235','198.41.236','198.41.237','198.41.238','198.41.239','198.41.240','198.41.241','198.41.242','198.41.243','198.41.244','198.41.245','198.41.246','198.41.247','198.41.248','198.41.249','198.41.250','198.41.251','198.41.252','198.41.253','198.41.254','198.41.255');


function extract_second_ip($input, $first_ip) {
	global $cloud_nets;
        preg_match_all('/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/', $input, $ips);
        foreach ($ips[0] as $ip) {
		foreach ($cloud_nets as $net) {
			$cloud = false;
			if (strpos ($ip, $net) !== false) {
				$cloud = true; break;
			}
		}
		if (!$cloud) {
                if ($ip != $first_ip){
                        $octs = explode ('.', $ip);
                        $grey_octs = Array ('127', '172', '10', '192');
                        if (!in_array($octs[0], $grey_octs)){
                                $valid_ip = 1;
                                foreach ($octs as $oct)
                                        if ($oct > 255 || $oct < 0)
                                                $valid_ip = 0;
					
                        if ($valid_ip == 1)
                                return $ip;
                        }
                }
		}
        }
	return NULL;
}

function get_real_ip() {

        $client_ip = $_SERVER['REMOTE_ADDR']; // this may be a proxy or not

        $proxy_headers = array(
                'HTTP_X_FORWARDED_FOR',
                'HTTP_VIA',
                'HTTP_FORWARDED_FOR',
                'HTTP_X_FORWARDED',
                'HTTP_FORWARDED',
                'HTTP_CLIENT_IP',
                'HTTP_FORWARDED_FOR_IP',
                'VIA',
                'X_FORWARDED_FOR',
                'FORWARDED_FOR',
                'X_FORWARDED',
                'FORWARDED',
                'CLIENT_IP',
                'FORWARDED_FOR_IP',
                'HTTP_PROXY_CONNECTION'
        );

        foreach($proxy_headers as $header)
                if ($_SERVER[$header] != '' ) {
                        $prox_ip = extract_second_ip ($_SERVER[$header], $client_ip);
                        if ($prox_ip)
                                return $prox_ip;
                }
        return $client_ip;
}

function detect_prox() {

	$is_proxy = 0;
	$x_ips = explode (',', $_SERVER['HTTP_X_FORWARDED_FOR']);
	if ($_SERVER['HTTP_X_FORWARDED_FOR']){
	        foreach ($x_ips as $x_ip) {
	                if (trim($x_ip) != $_SERVER['REMOTE_ADDR'])
	                        $is_proxy = 1;
	        }
	}else{
	        $proxy_headers = array(
	                'HTTP_VIA',
	//              'HTTP_X_FORWARDED_FOR', -- this is set by cloud always - can't rely on that one.
	                'HTTP_FORWARDED_FOR',
	                'HTTP_X_FORWARDED',
	                'HTTP_FORWARDED',
	                'HTTP_CLIENT_IP',
	                'HTTP_FORWARDED_FOR_IP',
	                'VIA',
	                'X_FORWARDED_FOR',
	                'FORWARDED_FOR',
	                'X_FORWARDED',
	                'FORWARDED',
	                'CLIENT_IP',
	                'FORWARDED_FOR_IP',
	                'HTTP_PROXY_CONNECTION'
	        );
	
	        foreach($proxy_headers as $header)
	                if (isset($_SERVER[$header]))
	                        $is_proxy = 1;
	}
	
	return $is_proxy;
}

function check_tor ($ip) {
	global $tc_db;

	//if (KU_CHECKTOR == false) return false; -- moved to bans class

	$tc_db -> Execute ("DELETE FROM tor_cache` WHERE timestamp < '".(time() - 10800)."';");
	if (! $results = $tc_db->GetOne ( "SELECT * FROM tor_cache WHERE ip=" . $tc_db->qstr($ip) . ";") ) {
	        $reverse_client_ip = implode('.', array_reverse(explode('.', $ip)));
	        $reverse_server_ip = implode('.', array_reverse(explode('.', '91.232.225.62')));
	        $hostname = $reverse_client_ip . "." . $_SERVER['SERVER_PORT'] . "." . $reverse_server_ip . ".ip-port.exitlist.torproject.org";
		bdl_debug ('tor check: ' . $hostname);
		if (gethostbyname($hostname) == "127.0.0.2") {
			return true;
		} else {
			$tc_db->Execute ("INSERT INTO tor_cache (`ip`) VALUES (" .$tc_db->qstr($ip). ");");
			return false;
		}
	} else {
		return false;
	}
} 

function check_anon_prox_helper ($ip) {
        $ch = curl_init();
        $timeout = 10; // set to zero for no timeout 
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt ($ch, CURLOPT_URL, "http://rubrony.net:22222/katsu.php?a=" . $ip);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        $file_contents = curl_exec ($ch);
        curl_close ($ch);
        return trim($file_contents);

}

function check_anon_prox ($ip) {
	global $tc_db;
	$tc_db -> Execute ("DELETE FROM prox_cache` WHERE timestamp < '".(time() - 10800)."' and is_prox='0';");
	$results = $tc_db->GetAll ( "SELECT is_proxy FROM prox_cache WHERE ip=" . $tc_db->qstr($ip) . ";") ;

	if (count($results) > 0) {
		if ($results[0][0] === '0') {
			return false;
		} else {
			return true;
		}
	} else {
		if (check_anon_prox_helper ($ip) === "1") {
			$tc_db->Execute ("INSERT INTO prox_cache (`ip`, `is_proxy`) VALUES (" .$tc_db->qstr($ip). ", '1');");
			return true;
		} else {
			$tc_db->Execute ("INSERT INTO prox_cache (`ip`, `is_proxy`) VALUES (" .$tc_db->qstr($ip). ", '0');");
			return false;
		}
	}

} 

function check_cgi_prox ($ip) {
	global $tc_db;
	
	if (is_reader())
		return false;

	$tc_db -> Execute ("DELETE FROM cgi_prox_cache ` WHERE timestamp < '".(time() - 18000).";");
	$results = $tc_db->GetOne ( "SELECT count(*) FROM cgi_prox_cache WHERE ip=" . $tc_db->qstr($ip) . " and `key`=".$tc_db->qstr($_COOKIE['ijslo']).";");

	if ($results >0) {
		bdl_debug('cgi check pass');
		return false;
	}else{
		bdl_debug('cgi check fail');
		return false;
	}

}

function is_reader () {
	if (strrpos($_SERVER['HTTP_USER_AGENT'], 'PonyachReader')){
		//bdl_debug("reader");
		return true;
	}else{
		return false;
	}
}

?>
