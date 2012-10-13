<?php

//define('DEBUG', false);
define('UA', 'flexxblog');
define('version', '0.0.2dev');

$formItems = array(
	'title' => 'Titel',
	'text' => 'Text'
);
$baseFeed = '../RorkvellNews.rss';
$lang = 'de';

define('newCommentsFeed', 'comments.rss');
define('maxComments', 30); 

define('NAMESPACE_DC', 'http://purl.org/dc/elements/1.1/');
define('NAMESPACE_DCT', 'http://purl.org/dc/terms/');
define('NAMESPACE_RDF', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
define('NAMESPACE_RDFS', 'http://www.w3.org/2000/01/rdf-schema#');
define('NAMESPACE_XLINK', 'http://www.w3.org/1999/xlink');

?>
