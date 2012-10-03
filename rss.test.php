<?php 

require_once 'rss2.class.php';
include_once 'admin/users.php';

$rssDoc = new rssDocument();
$rssDoc->formatOutput = true;
$rssDoc->preserveWhiteSpace = false;
$rssDoc->load('test.rss', LIBXML_NOBLANKS | LIBXML_NONET);

//$rssDoc->setLink('Test 1');

$article = array(
	'title' => 'Test Title',
	'description' => 'test text',
	'author' => 'http://www.rorkvell.de/impressum Siegfried Gipp',
	'category' => array(
		'http://www.example.org example category 1',
		'http://www.example.org example category 2',
		'example category 3'
	)
);
if (function_exists('getUser')) {
	$user = getUser($_SERVER['PHP_AUTH_USER']);
	if (isset($user)) $article['author'] = $user['name'];
}

$rssDoc->newArticle($article);

$channel = $rssDoc->getElementsByTagName('channel')->item(0);
//$channel->appendChild($rssDoc->createItem('Test'));
//$channel->appendChild($rssDoc->createItem('Das ist ein langer Text mit mehr als 20 Zeichen.'));
$data = array(
	'title' => 'Test',
	'description' => 'Description',
	'source' => array(
		'http://www.example.org example url',
		'http://www.example.org example url2'
	),
	'category' => array(
		'http://www.example.org example category 1',
		'http://www.example.org example category 2',
		'example category 3'
	)
);
//$channel->appendChild($rssDoc->createItem($data));
$rssDoc->appendItem($rssDoc->createItemItem($data));

print $rssDoc->saveXML();

$identifier = $rssDoc->getElement('dc:identifier');
if (!isset($identifier)) die('Error locating identifier');
$fname = basename(dirname($identifier->nodeValue)) . '/' . basename($identifier->nodeValue);
print $fname . "\n";

?>