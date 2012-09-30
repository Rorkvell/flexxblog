<?php

require_once('rss2.class.php');
require_once('evlog.php');
include_once('conf.php');

$fname = 'comments.rss';

if (file_exists($fname)) {
	$rssDoc = new rssDocument();
	$rssDoc->load($fname, LIBXML_COMPACT | LIBXML_NOBLANKS);
	$htmlDoc = $rssDoc->convertToHTMLDoc('CommentsList');
	$lst = $htmlDoc->getElementsByTagName('article');
	for ($i=0; $i<$lst->length; $i++) {
		if ($lst->item($i)->hasAttribute('id')) {
			$id = $lst->item($i)->getAttribute('id');
			print $htmlDoc->saveXml($lst->item($i));
		}
	}
}
?>