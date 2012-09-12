<?php

function event_log($src, $str) {
	$logname = dirname(__FILE__) . '/flexxblog.log';
	//print $logname . "\n";
	$now = date(DATE_ISO8601);
	$out = $now . ': ' . basename($src) . '; ' . $str . "\n";
	$fh = fopen($logname, "at");
	if (isset($fh)) {
		fwrite($fh, $out);
		fclose($fh);
	}
}

?>
