<?php

require_once('../rss2.class.php');
//require_once('../markdown.php');
include_once('users.php');
require_once('../conf.php');

define('DEBUG', false);


function buildRSS($doc, $title, $text) {
	if (!file_exists('template.rss')) die("Error: Template file not found");
	$rc = $doc->load('template.rss', LIBXML_COMPACT | LIBXML_NOBLANKS);
	if ($rc === false) die('Error: Could not load template.rss');
	$title = strip_tags($title);
	$doc->setElement('title', $title);
	$guid = $doc->GUID();
	$doc->channel->setAttribute('xml:id', $guid);
	//$text = trim(Markdown($text));
	$doc->setElement('description', $text);
	if (function_exists('getUser')) {
		$user = getUser($_SERVER['PHP_AUTH_USER']);
		if (isset($user)) {
			$creator = $doc->setElementNS('http://purl.org/dc/elements/1.0/', 'dc:creator', $user['name']);
			$creator->setAttributeNS('http://www.w3.org/1999/xlink', 'xlink:type', 'simple');
			$creator->setAttributeNS('http://www.w3.org/1999/xlink', 'xlink:href', $user['url']);
		}	
	}
	return $doc;
}

function saveCommentRss($doc) {
	if (DEBUG) print $doc->saveXML();
	$id = $doc->setId();	// returns formalized title as file name
	if (empty($id)) die("Error retrieving rss id");
	if (DEBUG) print 'id = ' . $id . "\n";
	$now = getdate();
	$rssDst = '../' . $now['year'] . '/' . basename($id);
	if (file_exists($rssDst)) {
		$rssBase = $rssDst;
		$i = 0;
		$rssDst = $rssBase . '_' . $i;
		while (file_exists($rssDst)) {
			$i++;
			$rssDst = $rssBase . '_' . $i;
		}
	}
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

function updateMainFeed($baseFeed, $htmlId, $lang, $guid, $title, $text) {
	if (!file_exists($baseFeed)) die("Error: " . $baseFeed . " not found");
	$feed = new rssDocument();
	if (!isset($feed)) die("Error on creating rssDocument");
	$rc = $feed->load($baseFeed, LIBXML_COMPACT | LIBXML_NOBLANKS);
	if ($rc === false) die('Error: could not load base feed');
	$meta = Array(
		'title' => $title,
		'description' => $text,	// TODO
		'guid' => $htmlId
	);
	$feed->insertItem(null, $meta, null, $guid);
	$feed->crop(20);
	$feed->save($baseFeed);	// save rss
	$fname = substr($baseFeed, 0, -4) . '.html';
	if (isset($lang) && !empty($lang)) $fname .= '.' . $lang;
	$feed->saveHTMLFile($fname);
}

if (isset($_POST['title']) && isset($_POST['text'])) {
	umask(0027);
	$text = trim(Markdown($_POST['text']));
	
	// Build the rss article
	$doc = new rssDocument();
	if (!isset($doc)) die("Error on creating rssDocument");
	buildRSS($doc, $_POST['title'], $text);
	$rssDst = saveCommentRss($doc);	
	if (!file_exists($rssDst)) die('Error: ' . $rssDst . ' does not exist' . "\n");
	//header('Content-type: application/xml');
	//print $doc->saveXML();
	
	// convert and save rss to html
	$htmlDst = saveCommentHtml($doc, $rssDst, $lang);
	if (!file_exists($htmlDst)) die('Error: ' . $htmlDst . ' does not exist' . "\n");
	//print file_get_contents($htmlDst);
	$link = $doc->getChannelElement('link');
	if (!isset($link)) die("Link not found");

	$channel = $doc->getChannel();
	if (!isset($channel)) die("Error: Channel not found");
	$guid = $channel->getAttribute('xml:id');
	updateMainFeed($baseFeed, $link->nodeValue, $lang, $guid, $_POST['title'], $text);	
	
	header('Location: ' . $link->nodeValue);	
	
	
} else {
	// Build form
	$doc = new rssDocument();
	$doc->load('template.rss');
	$htmlDoc = $doc->convertToHTMLDoc();
	$divs = $htmlDoc->getElementsByTagName('div');
	$mainDiv = null;
	for ($i=0; $i<$divs->length; $i++) {
		$divs->item($i)->setIdAttribute('id', true);
		if ($divs->item($i)->hasAttributes()) {
			$map = $divs->item($i)->attributes;
			$id = $map->getNamedItem('id');
			if ($id->nodeValue == 'main') {
				$mainDiv = $divs->item($i);
			}
		}
	}
	if (!isset($mainDiv)) die("error getting main div\n") ;
	// Append form
	$form = $mainDiv->appendChild($htmlDoc->createElement('form'));
	$form->setAttribute('action', 'new.php');
	$form->setAttribute('method', 'post');
	$legend = $form->appendChild($htmlDoc->createElement('legend', 'neuer Artikel'));
	$fset = $form->appendChild($htmlDoc->createElement('fieldset'));
	$flist = $fset->appendChild($htmlDoc->createElement('ul'));
	foreach($formItems as $key => $displayname) {
		$fitem = $htmlDoc->createElement('li');
		$label = $fitem->appendChild($htmlDoc->createElement('label', $displayname));
		$label->setAttribute('for', $key);
		if ($key == 'text') {
			$input = $fitem->appendChild($htmlDoc->createElement('textarea'));
			$input->setAttribute('cols', '80');
			$input->setAttribute('rows', '24');
		} else {
			$input = $fitem->appendChild($htmlDoc->createElement('input'));
			$input->setAttribute('type', 'text');
			$input->setAttribute('size', '80');
		}
		$input->setAttribute('name', $key);
		$input->setAttribute('style', 'width: 80%;');
		$flist->appendChild($fitem);
	}
	$button = $form->appendChild($htmlDoc->createElement('input'));
	$button->setAttribute('type', 'submit');
	$button->setAttribute('value', 'send');
	$htmlDoc->normalize();
	print $htmlDoc->saveHTML();	
}


?>