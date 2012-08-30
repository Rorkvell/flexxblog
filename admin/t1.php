<?php 

require_once "../rss2.class.php";

$baseFeed = '../RorkvellNews.rss';
$lang = 'de';

function buildRSS($doc, $title, $text) {
	if (!file_exists('template.rss')) die("Error: Template file not found");
	$rc = $doc->load('template.rss', LIBXML_COMPACT | LIBXML_NOBLANKS);
	if ($rc === false) die('Error: Could not load template.rss');
	$title = strip_tags($title);
	$doc->setElement('title', $title);
	$guid = $doc->GUID();
	$doc->channel->setAttribute('xml:id', $guid);
	$text = trim(Markdown($text));
	$doc->setElement('description', $text);
	return $doc;
}

function saveCommentRss($doc) {
	$id = $doc->setId();	// returns formalized title as file name
	if (empty($id)) die("Error retrieving rss id");
	$now = getdate();
	$rssDst = '../' . $now['year'] . '/' . basename($id);
	$rc = $doc->save($rssDst);
	if ($rc === false) die("Error: Could not write " . $rssDst . "\n");
	$rc = chmod($rssDst, 0640);
	if ($rc === false) die("Error on chmod" . $rssDst);
	return $rssDst;
}

function saveCommentHtml($doc, $rssDst, $lang) {
	$htmlDst = substr($rssDst, 0, -4) . '.html';
	if (isset($lang) && !empty($lang)) $htmlDst .= '.' . $lang;
	$rc = $doc->saveHTMLFile($htmlDst);
	if ($rc === false) die("Error: Could not write " . $htmlDst . "\n");
	$rc = chmod($htmlDst, 0640);
	if ($rc === false) die("Error on chmod" . $htmlDst);
	return $htmlDst;
}


function updateMainFeed($baseFeed, $htmlId, $guid, $lang, $title, $text) {
	if (!file_exists($baseFeed)) die("Error: " . $baseFeed . " not found");
	$feed = new rssDocument();
	if (!isset($feed)) die("Error on creating rssDocument");
	$rc = $feed->load($baseFeed, LIBXML_COMPACT | LIBXML_NOBLANKS);
	if ($rc === false) die('Error: could not load base feed');
	$meta = Array(
		'title' => $title,
		'description' => $text,
		'guid' => $htmlId
	);
	$feed->insertItem(null, $meta, null, $guid);
	$feed->crop(20);
	$feed->save($baseFeed);	// save rss

	$fname = substr($baseFeed, 0, -4) . '.html';
	if (isset($lang) && !empty($lang)) $fname .= '.' . $lang;
	$feed->saveHTMLFile($fname);
	print $fname;
}



$doc = new rssDocument();
buildRSS($doc, 'Test18', 'text');
//print $doc->saveXML();
$rssDst = saveCommentRss($doc);
print $rssDst . "\n";
print file_get_contents($rssDst);

/*
$htmlDst = saveCommentHtml($doc, $rssDst, $lang);
print $htmlDst . "\n";
$link = $doc->getChannelElement('link');
print $link->nodeValue . "\n";
$channel = $doc->getChannel();
print $channel->nodeName . "\n";

$guid = $channel->getAttribute('xml:id');
print $guid . "\n";

updateMainFeed($baseFeed, $link->nodeValue, $lang, $guid, 'title', 'text');
*/
?>


