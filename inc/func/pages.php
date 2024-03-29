<?php
/**
 * Generate the list of pages, linking to each
 *
 * @param integer $boardpage Current board page
 * @param integer $pages Number of pages
 * @param string $board Board directory
 * @return string Generated page list
 */

/* <3 coda for this wonderful snippet
print $contents to $filename by using a temporary file and renaming it */
function print_page($filename, $contents, $board, $dir = false) {

	$filename = str_replace('/', '_', $filename);

	if ($dir) {
		$filename = $dir . '/' . $filename;
	} else {
		$filename = KU_TEMPLATEDIR_2 . '/' . $filename;
	}

	bdl_debug("saving - " . $filename);
	cache_add($filename, $contents);

	return;

	$tempfile = tempnam(KU_TEMPLATEDIR_2, 'tmp'); /* Create the temporary file */
	$fp = fopen($tempfile, 'w');
	fwrite($fp, $contents);
	fclose($fp);
	/* If we aren't able to use the rename function, try the alternate method */
	if (!@rename($tempfile, $filename)) {
		copy($tempfile, $filename);
		unlink($tempfile);
	}

	chmod($filename, 0664); /* it was created 0600 */
}

?>
