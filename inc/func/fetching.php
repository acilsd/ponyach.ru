<?php
/**
 * Gets information about the filetype provided, which is specified in the manage panel
 *
 * @param string $filetype Filetype
 * @return array Filetype image, width, and height
 */
function getfiletypeinfo($filetype) {
	global $tc_db;

	$return = '';

	if ($return != '') {
		return unserialize($return);
	}

	$results = $tc_db->GetAll("SELECT `image`, `image_w`, `image_h` FROM `" . KU_DBPREFIX . "filetypes` WHERE `filetype` = " . $tc_db->qstr($filetype) . " LIMIT 1");
	if (count($results) > 0) {
		foreach($results AS $line) {
			$return = array($line['image'],$line['image_w'],$line['image_h']);
		}
	} else {
		/* No info was found, return the generic icon */
		$return = array('generic.png',48,48);
	}

	return $return;
}
?>