<?php

require_once('rss2.class.php');
require_once('evlog.php');
include_once('conf.php');


//header('Content-type: text/plain');

if (isset($_POST['name'])) $name = trim($_POST['name']);
else die('error on input');
if (isset($_POST['text'])) $text=trim($_POST['text']);
else die('error on input');

if (strlen($name) < 2) die('Name length error');
if (strlen($text) < 3) die('Text length error');

if (isset($_POST['rss'])) $rssFile = $_POST['rss'];
else die('error on input');
if (empty($rssFile)) die('Error: source file name empty');

event_log(__FILE__, 'Comment for ' . $rssFile);


// Load comment feed
$rssDoc = new rssDocument();
$fname = basename($rssFile);	// Get name from post
$fdir = dirname($rssFile);
$fname = basename($fdir) . '/' . $fname;
$rssDoc->load($fname, LIBXML_COMPACT | LIBXML_NOBLANKS);

// Meta data  
$meta = Array();
if (isset($_POST['name'])) $meta['author'] = trim($_POST['name']);
else $meta['autor'] = 'anonymous';
if (isset($_POST['source']) && !empty($_POST['source'])) {
//	$meta['author'] = $_POST['source'] . ' ' . $meta['author'];
	$meta['source'] = trim($_POST['source']);
}
$item = $rssDoc->appendItem($_POST['text'], $meta, null, null, null);

if (isset($item)) {
	$itemId = $item->getAttribute('xml:id');
} else $itemId = null;

$rssDoc->save($fname);			// Save the comment feed
chmod($fname, 0640);

$fname = substr($fname, 0, -4) . '.html.de';
//print $rssDoc->saveHTML();
$rssDoc->saveHTMLFile($fname, 'BlogPosting');	// Save comment feed html version
chmod($fname, 0640);

$link = $rssDoc->getChannelElement('link');
if (!isset($link)) die("Link not found");
$htmlDst = $link->nodeValue;	// Full URL of just created html feed

// Update comment feed (if existent)

if (file_exists(newCommentsFeed)) {
	$cdoc = new rssDocument();
	if (!isset($cdoc)) {
		error_log('Could not create ' . newCommentsFeed);
		break;
	}
	$rc = $cdoc->load(newCommentsFeed, LIBXML_COMPACT | LIBXML_NOBLANKS);
	if ($rc == false) {
		error_log('Could not load ' . newCommentsFeed, 0);
		break;
	}
	$meta = Array();
	$title = $rssDoc->getChannelElement('title');
	if (isset($title)) {
		$meta['title'] = $_POST['name'] . ' zu ' . $title->nodeValue;
		if (isset($itemId)) $meta['link'] = $htmlDst . '#' . $itemId;
		else $meta['link'] = $htmlDst;
		$cdoc->insertItem(null, $meta, null, null, null);
		$cdoc->crop(10);
		$cdoc->save(newCommentsFeed, LIBXML_COMPACT | LIBXML_NOBLANKS);
		$hName = substr(newCommentsFeed, 0, -4) . '.html.de';
		$cdoc->saveHTMLFile($hName, LIBXML_COMPACT | LIBXML_NOBLANKS); 
	} else error_log('No title found in article', 0);
} else error_log(newCommentsFeed . ' not found', 0);


// Redirect to just created html file
header('Location: ' . $htmlDst);

?>