<?php

include_once "markdown.php";
require_once "xml.class.php";

define('version', '0.0.1dev');

class rssDocument extends xmlDocument{

	public $channel = null;
	private $allowable_tags = "<abbr><p><q><cite><blockquote><strong><em><dfn>";
	
	function __construct($version = '1.0', $enc = 'UTF-8') {
		parent::__construct($version, $enc);
		$this->appendChild(DOMImplementation::createDocumentType('rss'));
		if (!isset($this->documentElement))
			$this->appendChild($this->createElement('rss'));
			$this->channel = $this->documentElement->appendChild($this->createElement('channel'));
		date_default_timezone_set('Europe/Berlin'); // see: http://www.php.net/manual/de/function.date-default-timezone-set.php

	}

	public function loadXML($x) {
		parent::loadXML($x);
		//$this->channel = $this->documentElement->firstChild;
		$this->channel = $this->getElementsByTagName('channel')->Item(0);
	}
	
	public function load($filename, $options=null) {
		$rc = parent::load($filename, $options);
		if ($rc === false) die("Error on loading " . $filename);
		$this->channel = $this->getElementsByTagName('channel')->Item(0);
		return $rc;
	}
	
	public function getChannel() {
		if (!isset($this->channel)) die("rssDocument::getChannel():Channel not found");
		return $this->channel;
	}

	public function getChannelElement($name) {
		if (!isset($this->channel)) die("rssDocument::getChannelElement():Channel not found");
		if (!($this->channel->hasChildNodes())) {
			die("Error, channel has no child nodes");
		}
		$e = $this->channel->firstChild;
		while (isset($e) && $e->nodeName != $name) $e = $e->nextSibling;
		//if (isset($e)) print $e->nodeName . "\n";
		return $e;
	}
	
	public function setId() {
		$title = $this->getChannelElement('title');
		if (!isset($title)) {
			die("Error: Title not found");
		}
		$link = $this->getChannelElement('link');
		if (!isset($link)) die("Error: link element not found");
		$fname = str_replace(' ', '_', $title->nodeValue);
		$l = $link->nodeValue;
		if ($l[strlen($l)-1] != '/') $l .= '/';
		$now = getdate();
		$l .= $now['year'] . '/' . $fname;
		$this->setElement('link', $l . '.html');
		return $this->channel->appendChild(
			$this->createElementNS('http://purl.org/dc/elements/1.0/', 'dc:identifier', $l . '.rss')
		)->nodeValue;
	}
	
	public function addElement($name, $val=null) {
		if (!isset($this->channel)) die("rssDocument::addElement():Channel not found");
				$e = $this->createElement($name, $val);
		$this->channel->appendChild($e);
		return $e;
	}
	
	public function setElement($name, $val = NULL) {
		if (!isset($this->channel)) die("rssDocument::setElement():Channel not found");
		$e = $this->channel->firstChild;
		while (isset($e) && $e->nodeName != $name)
			$e = $e->nextSibling;
		if (isset($e)) {		// replace node
			return $this->channel->replaceChild($this->createElement($name, $val), $e);
		} else {				// append new node
			return $this->channel->appendChild($this->createElement($name, $val));
		}
	}
	
	public function setElementNS($namespaceURI, $qualifiedName, $val=null) {
		if (!isset($this->channel)) die("rssDocument::setElement():Channel not found");
		$e = $this->channel->firstChild;
		while (isset($e) && $e->nodeName != $qualifiedNameame)
			$e = $e->nextSibling;
		if (isset($e)) {		// replace node
			return $this->channel->replaceChild($this->createElementNS($namespaceURI, $qualifiedName, $val), $e);
		} else {				// append new node
			return $this->channel->appendChild($this->createElementNS($namespaceURI, $qualifiedName, $val));
		}
		
	}
	
	public function setTitle($title) {
		if (!isset($this->channel)) die("rssDocument::setTitle():Channel not found");
		return $this->setElement('title', $title);
	}
	
	public function saveXML($node=null, $options=0) {
		if (!isset($this->channel)) die("rssDocument::saveXML():Channel not found");
		$now = time();
		$nowRss = date(DATE_RSS, $now);
		$this->formatOutput = true;
		$this->preserveWhiteSpace = false;
		$pd = $this->channel->firstChild;
		while (isset($pd) && $pd->nodeName != 'pubDate')
			$pd = $pd->nextSibling;
		if (!isset($pd)) {
			$pd = $this->channel->appendChild($this->createElement('pubDate', $nowRss));
			$pd->setAttributeNS('http://purl.org/dc/elements/1.0/', 'dc:date', date(DATE_W3C, $now));
		}
		$lbd = $this->setElement('lastBuildDate', date(DATE_RSS, $now));
		$lbd->setAttributeNS('http://purl.org/dc/elements/1.0/', 'dc:date', date(DATE_W3C, $now));
		$this->setElement('generator', 'flexxblog v' . version);
		return parent::saveXML($node, $options);
	}
	
	public function save($filename, $options=null) {
		if (!isset($this->channel)) die("rssDocument::saveXML():Channel not found");
		$now = time();
		$nowRss = date(DATE_RSS, $now);
		$this->formatOutput = true;
		$this->preserveWhiteSpace = false;
		$pd = $this->channel->firstChild;
		while (isset($pd) && $pd->nodeName != 'pubDate')
			$pd = $pd->nextSibling;
		if (!isset($pd)) {
			$pd = $this->channel->appendChild($this->createElement('pubDate', $nowRss));
			$pd->setAttributeNS('http://purl.org/dc/elements/1.0/', 'dc:date', date(DATE_W3C, $now));
		}
		$lbd = $this->setElement('lastBuildDate', date(DATE_RSS, $now));
		$lbd->setAttributeNS('http://purl.org/dc/elements/1.0/', 'dc:date', date(DATE_W3C, $now));
		return parent::save($filename, $options);
	}
	
	public function GUID() {
    	if (function_exists('com_create_guid') === true)
    	{
	        return 'ID' . trim(com_create_guid(), '{}');
	    }
	    return sprintf('ID%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
	}

	public function createItem($text=null, $meta=null, $tags=null, $id=null) {
		$item = $this->createElement('item');
		$itemId = isset($id)?$id:$this->GUID();
		$item->setAttribute('xml:id', $itemId);
		$baseLink = $this->getChannelElement('link');
		if (isset($baseLink) && !empty($baseLink->nodeValue)) {
			$item->appendChild($this->createElement('link', $baseLink->nodeValue . '#' . $itemId));
		}
		if (isset($text)) {
			if (isset($tags)) {
				$item->appendChild($this->createElement('description', Markdown(strip_tags($text, $tags))));
			} else {
				$item->appendChild($this->createElement('description', Markdown(strip_tags($text, $this->allowable_tags))));
			}
		}
		if (isset($meta)) {
			foreach ($meta as $key => $val) {
				switch ($key) {
					case 'source':
						if (is_array($val)) {
							foreach ($val as $src) {
								list($url, $txt) = explode(' ', $src, 2);
								$e = $item->appendChild($this->createElement($key, $txt));
								$e->setAttribute('url', $url);
							}
						} else {
							list($url, $txt) = explode(' ', $val, 2);
							$e = $item->appendChild($this->createElement($key, $txt));
							$e->setAttribute('url', $url);
						}
						break;
					case 'guid':
						$e = $item->appendChild($this->createElement($key, $val));
						if (preg_match('/^https?:\/\//', $val)) {
							$e->setAttribute('isPermaLink', 'true');
						}
					case 'author':
						list($url, $aname) = explode(' ', $val, 2);
						if (empty($aname)) {
							$e = $item->appendChild($this->createElement($key, $val));
						} else {
							$e = $item->appendChild($this->createElement($key, $aname));
							$e->setAttributeNs('http://www.w3.org/1999/xlink', 'xlink:type', 'simple');
							$e->setAttributeNs('http://www.w3.org/1999/xlink', 'xlink:href', $url);							
						}
					case 'category':
						// TODO
						break;
					default:
						//print "adding $key -> $val\n";
						$e = $item->appendChild($this->createElement($key, $val));
						break;						
				}
			}
		}
		$now = time();
		$pd = $item->appendChild($this->createElement('pubDate', date(DATE_RSS, $now)));
		$pd->setAttributeNS('http://purl.org/dc/elements/1.0/', 'dc:date', date(DATE_W3C, $now));
		return $item;
	}

	public function appendItem($text=null, $meta=null, $tags=null, $id=null) {
		if (!isset($this->channel)) die("rssDocument::appendElement():Channel not found");
		return $this->channel->appendChild($this->createItem($text, $meta, $tags, $id));
	}
	
	public function insertItem($text=null, $meta=null, $tags=null, $id=null, $refIndex=null) {
		if (!isset($this->documentElement)) die('rssDocument::insertItem(): documentElement not found');
		if (!isset($this->channel)) die("rssDocument::insertItem():Channel not found");
		$ref=(isset($refIndex))?$refIndex:0;
		$items = $this->getElementsByTagName('item');
		if ($items->length <= $ref) return $this->appendItem($text, $meta, $tags, $id);
		$item = $this->createItem($text, $meta, $tags, $id);
		$refItem = $items->item($ref);
		return $refItem->parentNode->insertBefore($item, $refItem);
	}
	
	public function crop($n) {
		if (!isset($this->channel)) die("Error: no channel found");
		$items = $this->getElementsByTagName('item');
		if ($items->length < $n) return;
		for ($i=$n; $i < $items->length; $i++) {
			$this->channel->removeChild($items->item($i));
		}
	}
	
	
}

class rssImplementation extends DOMImplementation {
	
	function rssImplementation() {
	}
	
	public function createDocument() {
		$doc = new rssDocument('1.0', 'UTF-8');
		return $doc;
	}	
}

?>
