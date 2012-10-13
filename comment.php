<?php

require_once('rss2.class.php');
require_once('evlog.php');
//include_once('conf.php');

define('DEBUG', false);

$errMsg = 'Error on input';
if (DEBUG) {
	header('Content-Type: text/plain');
	print_r($_POST);
}

$fname = array();
if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
	$fname['html'] = $_SERVER['HTTP_REFERER'];
} else die($errMsg);
$tmp = $_POST['link'];
if (isset($tmp)) $fname['html'] = $tmp;
$fname['localHtml'] = basename(dirname($fname['html'])) . '/' . basename($fname['html']);
if (!file_exists($fname['localHtml'])) die('Error: ' . $fname['html'] . ' not found');
if (isset($_POST['rss']) && !empty($_POST['rss'])) {
	$fname['rss'] = $_POST['rss'];
	$fname['localRss'] = basename(dirname($_POST['rss'])) . '/' . basename($_POST['rss']);
} else die ($errMsg);
if (!file_exists($fname['localRss'])) die('Error: ' . $fname['rss'] . ' not found');
$fname['base'] = basename(dirname($_POST['rss'])) . '/' . pathinfo($fname['rss'],  PATHINFO_FILENAME);
//$b2 = pathinfo($fname['html'], PATHINFO_FILENAME);
if (DEBUG) print_r($fname);

if (!isset($_POST['name']) && !isset($_POST['text'])) {
	die($errMsg);
}
// Check encoding. Should be UTF-8
if (mb_detect_encoding($_POST['text'], 'UTF-8', true) === FALSE) {
	$enc = mb_detect_encoding($text);
} else $enc = 'UTF-8';
if ($enc != 'UTF-8') die($errMsg);

//event_log(__FILE__, 'Comment request for ' . $fname['html']);
if (isset($_POST['name'])) $name = trim($_POST['name']);
else $name = null;
if (isset($_POST['text'])) $text=trim($_POST['text']);
else $text = null;

// Load comment feed
$rssDoc = new rssDocument();
if ($rssDoc->load($fname['localRss'], LIBXML_COMPACT | LIBXML_NOBLANKS) === false) 
	die($errMsg);
// Check ID
$c = $rssDoc->getChannel();
if (!isset($c)) die('Error: No channel found');
$id = $c->getAttribute('xml:id');
if ($id != $_POST['id']) die('Error on ID verification');

// Check for maximum number of allowed comments
$items = $rssDoc->getElementsByTagName('item');
if ($items->length > maxComments) die('maximum comment count reached');

// Update html name, if available
$link = $rssDoc->getElement('link');
if (isset($link)) {
	$fname['html'] = $link->nodeValue;
	$fname['localHtml'] = basename(dirname($fname['html'])) . '/' . basename($fname['html']);
} 

$data = Array(
	'description' => $_POST['text']
);

if (isset($_POST['name']) && !empty($_POST['name'])) {
	if (isset($_POST['source']) && !empty($_POST['source'])) {
		if (preg_match('/^https?:\/\//', $_POST['source'])) {
			$data['author'] = $_POST['source'] . ' ' . $_POST['name'];
		} 
		elseif (preg_match('/^[a-z]+:\/\//', $_POST['source'])) {
			$data['author'] = $_POST['source'] . ' ' . $_POST['name'];
		}
		else {
			$data['author'] = $_POST['name'];
		}
	} else $data['author'] = $_POST['name'];
}

$item = $rssDoc->addComment($data);
$itemId = $item->getAttribute('xml:id');
$rssDoc->crop(maxComments);

// Save rss comment feed
if (DEBUG) {
	print $rssDoc->saveXML();
} else {
	$rssDoc->save($fname['localRss'], LIBXML_NOBLANKS);
	chmod($fname, 0640);
}

// Save html comment feed
$params = array(
	'TYPE' => 'BlogPosting'
);
if ($items->length <= maxComments) {
	$params['CFORM'] = 'true';
} else {
	$params['CFORM'] = 'false';
}
if (DEBUG) {
	print $rssDoc->saveHTML(null, $params);
	print 'Save to: ' . $fname['localHtml'] . "\n";
} else {
	$rssDoc->saveHTMLFile($fname['localHtml'], $params);
	chmod($fname, 0640);
}

// Update comment feed (if existent)
if (file_exists(newCommentsFeed)) {
	$params['TYPE'] = 'Blog';
	$params['CFORM'] = 'false';
	$cdoc = new rssDocument();
	if (!isset($cdoc)) {
		if (DEBUG)	print 'Could not create ' . newCommentsFeed . "\n";
		else error_log('Could not create ' . newCommentsFeed); 
		break;
	}
	$rc = $cdoc->load(newCommentsFeed, LIBXML_COMPACT | LIBXML_NOBLANKS);
	if ($rc == false) {
		if (DEBUG) print 'Could not load ' . newCommentsFeed . "\n";
		else error_log('Could not load ' . newCommentsFeed, 0); 
		break;
	}
	if (DEBUG) print "Updating comments list\n";
	$meta = Array();
	$title = $rssDoc->getElement('title')->nodeValue;
	if (DEBUG) print $title . "\n";
	if (isset($title)) {
		$meta['title'] = $_POST['name'] . ' zu ' . $title;
		if (isset($itemId)) $meta['link'] = $fname['html'] . '#' . $itemId;
		else $meta['link'] = $fname['html'];
		if (DEBUG) print_r($meta);
		if (DEBUG) print $cdoc->saveXML();
		$cdoc->insertItem($cdoc->createItem($meta));
		$cdoc->crop(10);
		if (DEBUG) print "Inserted\n";
		if (DEBUG) print $cdoc->saveXML();
		else $cdoc->save(newCommentsFeed, LIBXML_COMPACT | LIBXML_NOBLANKS); 
		$hName = substr(newCommentsFeed, 0, -4) . '.html.de';
		if (!DEBUG) $cdoc->saveHTMLFile($hName, $params); 
	} else {
		if (DEBUG) print 'No title found in article' . "\n";
		else error_log('No title found in article', 0); 
	}
} else {
	if (DEBUG) print newCommentsFeed . ' not found' . "\n";
	else error_log(newCommentsFeed . ' not found', 0); 
}


// Redirect to just created html file
if (!DEBUG) header('Location: ' . $fname['html']);
else print $rssDoc->saveHTML(null, $params);
?>