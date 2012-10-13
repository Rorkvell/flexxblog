<?php 

require_once '../rss2.class.php';
require_once '../conf.php';
include_once './users.php';

if (isset($_POST['title']) && isset($_POST['text'])) {
	umask(0027);
	
	// DEBUG
	if (DEBUG) header('Content-Type: text/plain');
	
	// 1. Create new rss file
	$rssDoc = new rssDocument();
	$rssDoc->preserveWhiteSpace = false;
	$rssDoc->formatOutput = true;
	$rc = $rssDoc->load('template.rss');
	if ($rc === false) die('Error on loading rss template');
	
	// 2. Read the main (overview) feed name from template
	$link = $rssDoc->getElement('link');
	if (isset($link)) {
		$mainFeedName = basename($link->nodeValue);
	}
	else $mainFeedName = null;
	
	// 3. Set the article
	$articleData = array(
		'title' => $_POST['title'],
		'description' => $_POST['text']
	);
	if (isset($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_USER'])) {
		if (function_exists('getUser')) {
			$user = getUser($_SERVER['PHP_AUTH_USER']);
			$articleData['author'] = $user['url'] . ' ' . $user['name'];
		} else 	$data['author'] = $_SERVER['PHP_AUTH_USER'];
	}
	$rssDoc->newArticle($articleData);
	$articleData['id'] = $rssDoc->channel->getAttribute('xml:id');
	if (DEBUG) {
		print 'ID = ' . $articleData['id'] . "\n";
		print $rssDoc->saveXML();
	}
	
	// 4. Save rss file
	$identifier = $rssDoc->getElement('dc:identifier');
	if (!isset($identifier)) die('Error locating rss identifier');
	$fname = basename(dirname($identifier->nodeValue)) . '/' . basename($identifier->nodeValue);
	if (DEBUG) print '../' . $fname . "\n";
	else $rssDoc->save('../' . $fname);
	
	// 5. Save Article HTML file
	$identifier = $rssDoc->getElement('link');
	if (!isset($identifier)) die('Error locating html identifier');
	$articleData['link'] = $identifier->nodeValue;
	$articleData['guid'] = $identifier->nodeValue;
	$fname = basename(dirname($identifier->nodeValue)) . '/' . basename($identifier->nodeValue);
	if (DEBUG) print '../' . $fname . "\n";
	$params = array(
		'TYPE' => 'BlogPosting',
		'CFORM' => 'true'
	);
	if (DEBUG) print $rssDoc->saveHTML(null, 'BlogPosting');
	else $rssDoc->saveHTMLFile('../' . $fname, $params);
		
	if (isset($mainFeedName)) {
		// 6. Update overview feed
		if (DEBUG) print $mainFeedName . "\n";
		$baseFeedName = substr($mainFeedName, 0, strpos($mainFeedName, '.'));
		$feed = new rssDocument();
		$feedName = '../' . $baseFeedName . '.rss';
		if (DEBUG) print "\n" . $feedName . "\n";
		$feed->load($feedName, LIBXML_NOBLANKS | LIBXML_NONET);
		$feed->insertItem($feed->createItem($articleData));
		if (DEBUG) print $feed->saveXML();
		else $feed->save($feedName, LIBXML_NOBLANKS);
		
		// 7. Convert overview feed to html
		$htmlName = '../' . basename($link->nodeValue);
		$params = array(
			'TYPE' => 'Blog',
			'CFORM' => 'false'
		);
		if (DEBUG) {
			print "\n" . $htmlName . "\n";
			print $feed->saveHTML(null, $params);
		} else {
			$feed->saveHTMLFile($htmlName, $params);
			header('Location: ' . $identifier->nodeValue);
			//header('Location: ' . $link->nodeValue); 	// if redirect to overview feed
		}
	}
	
} else {
	// Build form
	$doc = new rssDocument();
	$doc->load('template.rss');
	$params = array(
		'TYPE' => 'BlogPosting',
		'CFORM' => 'false'
	);
	$htmlDoc = $doc->convertToHTMLDoc($params);
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
			$input->setAttribute('cols', '60');
			$input->setAttribute('rows', '14');
		} else {
			$input = $fitem->appendChild($htmlDoc->createElement('input'));
			$input->setAttribute('type', 'text');
			$input->setAttribute('size', '60');
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