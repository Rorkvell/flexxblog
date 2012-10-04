<?php
/**
* A DOMDocument derived class for rss management.
*
* @author Siegfried Gipp
* @version 0.0.3
* @since 2012/9/25
*
*/
include_once 'markdown.php';
require_once 'xml.class.php';
include_once 'conf.php';


/**
 * 
 * A class for managing rss2 files and convert them to html.
 * @author siegfried
 *
 */
class rssDocument extends xmlDocument{

	public $channel = null;
	private $allowable_tags = "<abbr><p><q><cite><blockquote><strong><em><dfn><code>";
	private $logFile = 'rssDocument.log';
	
	function __construct($version = '1.0', $enc = 'UTF-8') {
		parent::__construct($version, $enc);
		$this->appendChild(DOMImplementation::createDocumentType('rss'));
		if (!isset($this->documentElement))
			$this->appendChild($this->createElement('rss'));
			$this->channel = $this->documentElement->appendChild($this->createElement('channel'));
		date_default_timezone_set('Europe/Berlin'); // see: http://www.php.net/manual/de/function.date-default-timezone-set.php

	}

	/**
	 * 
	 * Loads the document from an xml string.
	 * @param string $x The xml string
	 */
	public function loadXML($x) {
		parent::loadXML($x);
		//$this->channel = $this->documentElement->firstChild;
		$this->channel = $this->getElementsByTagName('channel')->Item(0);
	}
	
	/**
	 * 
	 * Loads document from a file.
	 * @param string $filename
	 * @param int $options
	 * @return The document
	 */
	public function load($filename, $options=null) {
		$rc = parent::load($filename, $options);
		if ($rc === false) die("Error on loading " . $filename);
		$this->channel = $this->getElementsByTagName('channel')->Item(0);
		return $rc;
	}

	/**
	 * 
	 * Retrieves the first channel element, if any.
	 * Returns null if no channel available.
	 * @return null if no channel, else the channel node
	 */
	public function getChannel() {
		if (!isset($this->documentElement)) return null;
		if (isset($this->channel)) return $this->channel;
		$c = $this->documentElement->firstChild;
		while (isset($c) && $c->nodeName != 'channel') $c = $c->nextSibling;
		$this->channel = $c;
		if (!isset($this->channel)) die("rssDocument::getChannel():Channel not found");
		return $this->channel;
	}

	/**
	 * 
	 * Gets any element of given name, if element is a direct descendant
	 * from channel.
	 * @param string $name
	 * @retun The DOMElement node or null if none found
	 * 
	 */
	public function getElement($name) {
		if (!isset($this->channel)) die("rssDocument::getChannelElement():Channel not found");
		if (!($this->channel->hasChildNodes())) {
			die("Error, channel has no child nodes");
		}
		$e = $this->channel->firstChild;
		while (isset($e) && $e->nodeName != $name) $e = $e->nextSibling;
		return $e;
	}
	
	/**
	 * @deprecated 
	 * Gets any element of given name, if element is a direct descendant
	 * from channel.
	 * @param string $name
	 * @retun The DOMElement node or null if none found
	 * 
	 */
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

	/**
	 * 
	 * Adds an element to channel (direct descendant).
	 * Element may already exist. If so, a new element of same name is added.
	 * @param string $name
	 * @param string $val
	 */
	public function addElement($name, $val=null) {
		if (!isset($this->channel)) die("rssDocument::addElement():Channel not found");
				$e = $this->createElement($name, $val);
		$this->channel->appendChild($e);
		return $e;
	}
	
	/**
	 * 
	 * Sets a channel descendant element. If the element does not exist,
	 * a new element is created. If it already exists, the element is
	 * replaced by a new one.
	 * @param string $name
	 * @param string $val
	 * @return the new element
	 */
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
	
	
	/**
	 * 
	 * Sets a channel descendant namespaced element. If the element does not exist,
	 * a new element is created. If it already exists, the element is
	 * replaced by a new one.
	 * @param string $namespaceURI
	 * @param string $qualifiedName
	 * @param string $val
	 * @return the new element
	 */
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
	
	/**
	 * 
	 * Sets the title of the blog (title element as descendant of channel).
	 * If the rss document has an image, the title there is set to the same value.
	 * @param string $title
	 */
	public function setTitle($title) {
		if (!isset($this->channel)) die("rssDocument::setTitle():Channel not found");
		$rc = $this->setElement('title', $title);
		$img = $this->getElement('image');
		if (isset($img)) {
			$c = $img->firstChild;
			while (isset($c) && $c->nodeName != 'title') $c = $c->nextSibling;
			if (isset($c)) $c->nodeValue = $title;
		}
		return $rc;
	}
	
	/**
	 * 
	 * Sets the link element (descendant of channel) from the title string.
	 * Converts special chars.
	 * @param string $title
	 */
	public function setLink($title) {
		$s = array(' ', 'Ä', 'Ö', 'Ü', 'ä', 'ö', 'ü', 'ß', '.');
		$r = array('_', 'A', 'O', 'U', 'a', 'o', 'u', 's', '_');
		if (!isset($this->channel)) die("rssDocument::setLink():Channel not found");
		$c = $this->channel->firstChild;
		while(isset($c) && $c->nodeName != 'link') $c = $c->nextSibling;
		if (isset($c)) {
			$file = $c->nodeValue;
			$now = getdate();
			//$rssDst = '../' . $now['year'] . '/' . basename($id);
			$url = dirname($file);
			$fname = basename($file);
			$ext = substr($fname, strpos($fname, '.'));
			$fname = rawurlencode(str_replace($s, $r, $title));
			$i = 0;
			while (file_exists('../' . $now['year'] . '/' . $fname)) {
				$fname .= $i;
				$i++;
			}
			$url .= '/' . $now['year'] . '/' . $fname;
			$rc = $this->setElement('link', $url . $ext);
			$img = $this->getElement('image');
			if (isset($img)) {
				$c = $img->firstChild;
				while (isset($c) && $c->nodeName != 'link') $c = $c->nextSibling;
				if (isset($c)) $c->nodeValue = $url . $ext;
			}
			$this->channel->appendChild(
				$this->createElementNS(NAMESPACE_DC, 'dc:identifier', $url . '.rss')
			);
		}
		return $rc;
	}
	
	/**
	 * 
	 * onverts the document to an xml string. If the document does not have
	 * a pubDate, it is addad before conversion. A lastBuildDate is always added or
	 * updated before conversion.
	 * @param unknown_type $node
	 * @param unknown_type $options
	 */
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
			$pd->setAttributeNS(NAMESPACE_DC, 'dc:date', date(DATE_W3C, $now));
		}
		$lbd = $this->setElement('lastBuildDate', date(DATE_RSS, $now));
		$lbd->setAttributeNS(NAMESPACE_DC, 'dc:date', date(DATE_W3C, $now));
		$this->setElement('generator', 'flexxblog v' . version);
		return parent::saveXML($node, $options);
	}
	
	/**
	 * 
	 * Saves the document to a file. The lastBuildDate is updated or set before 
	 * saving. If the document does not have a pubDate, it is added before 
	 * conversion.
	 * @param string $filename
	 * @param int $options
	 */
	public function save($filename, $options=null) {
		if (!isset($filename) || empty($filename)) die('Error: No file name given');
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
			$pd->setAttributeNS(NAMESPACE_DC, 'dc:date', date(DATE_W3C, $now));
		}
		$lbd = $this->setElement('lastBuildDate', date(DATE_RSS, $now));
		$lbd->setAttributeNS(NAMESPACE_DC, 'dc:date', date(DATE_W3C, $now));
		return parent::save($filename, $options);
	}
	
	/**
	 * 
	 * Creates a GUID
	 * @return string GUID
	 */
	public function GUID() {
    	if (function_exists('com_create_guid') === true)
    	{
	        return 'ID' . trim(com_create_guid(), '{}');
	    }
	    return sprintf('ID%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
	}
	
	/**
	 * 
	 * Sets a new article. The article itself is stored in the channel elements
	 * title and description. The author is stored into a dc:creator element.
	 * @param mixed $text
	 * 	If $text is a string, the article is stored into the title or description,
	 * 	depending on length. 
	 * 	If $text is an associative array, the key/value pairs are used for creating elements.
	 * @return The document
	 */
	public function newArticle($text) {
		//$this->documentElement->appendChild($this->createComment('new article'));
		$urlStart = '/^https?:\/\//';
		if (is_array($text)) {
			foreach($text as $key => $val) {
				switch ($key) {
					case 'title':
						$this->setTitle($val);
						$this->setLink($val);
						break;
					case 'description':
						$this->setElement('description', Markdown($val));
						break;
					case 'category':
						if (is_array($val)) {
							foreach($val as $v) {
								if(preg_match($urlStart, $v) > 0) {
									list($url, $content) = explode(' ', $v, 2);
									$this->channel->appendChild($this->createElement($key, $content))->setAttribute('domain', $url);
								} else {
									$this->channel->appendChild($this->createElement($key, $v));
								}
							}
						} else {
							if (preg_match($urlStart, $val)) {
								list($url, $content) = explode(' ', $val, 2);
								$this->channel->appendChild($this->createElement($key, $content))->setAttribute('domain', $url);
							} else {
								$this->channel->appendChild($this->createElement($key, $val));
							}
						}
						break;
					case 'author':
						if (preg_match($urlStart, $val)) {
							list($url, $name) = explode(' ', $val, 2);
							$author = $this->channel->appendChild($this->createElementNS(NAMESPACE_DC, 'dc:creator', $name));
							$author->setAttributeNS(NAMESPACE_XLINK, 'xlink:type', 'simple');
							$author->setAttributeNS(NAMESPACE_XLINK, 'xlink:href', $url);
						} else {
							$author = $this->channel->appendChild($this->createElementNS(NAMESPACE_DC, 'dc:creator', $val));
						}
						break;
					default:
						$this->setElement($key, $val);
						break;
				}
			}
		} else {
			if (strlen($text) > 20) $this->setElement('description', $text);
			else $this->setTitle($text);
		}
		$now = time();
		$pd = $this->channel->appendChild($this->createElement('pubDate', date(DATE_RSS, $now)));
		$pd->setAttributeNS(NAMESPACE_DC, 'dc:date', date(DATE_W3C, $now));
		return this;
	}
	
	/**
	 * 
	 * Creates an item from associative array
	 * @param mixed $text
	 * 	If $text is an associative array, all key/value pairs are processed,
	 * 	where key is the element name, and value is the elements value.
	 * 	If the element may contain a url, then the first word of the value is that url.
	 * 	If the value itself is an array, then all of the array values are processed as elements
	 * 	with key as name.
	 * If $text is a string, then the resulting item contains that string as the value
	 * 	of the title element (if string length < 20), or as value of the description eement.
	 * @return The item DOMNode. 
	 */
	public function createItem($text) {
		$urlStart = '/^https?:\/\//';
		$hasGuid = false;
		$hasLink = false;
		$item = $this->createElement('item');
		$item->setAttribute('xml:id', $this->GUID());
		if (is_array($text)) {
			foreach($text as $key => $val) {
				switch($key) {
					case 'description':
						$str = trim(Markdown(strip_tags($val, $this->allowable_tags)));
						$item->appendChild($this->createElement($key, $str));
						break;
					case 'source':
						if (is_array($val)) {
							foreach($val as $v) {
								list($url, $content) = explode(' ', $v, 2);
								$item->appendChild($this->createElement($key, $content))->setAttribute('url', $url);
							}
						} else 	list($url, $content) = explode(' ', $val, 2);
						break;
					case 'category':
						if (is_array($val)) {
							foreach($val as $v) {
								if(preg_match($urlStart, $v) > 0) {
									list($url, $content) = explode(' ', $v, 2);
									$item->appendChild($this->createElement($key, $content))->setAttribute('domain', $url);
								} else {
									$item->appendChild($this->createElement($key, $v));
								}
							}
						} else {
							if (preg_match($urlStart, $val)) {
								list($url, $content) = explode(' ', $val, 2);
								$item->appendChild($this->createElement($key, $content))->setAttribute('domain', $url);
							} else {
								$item->appendChild($this->createElement($key, $val));
							}
						}
						break;
					case 'guid':
						if (preg_match($urlStart, $val)) {
							$item->appendChild($this->createElement($key, $val))->setAttribute('isPermaLink', 'true');
						} else {
							$item->appendChild($this->createElement($key, $val));
						}
						$hasGuid = true;
						break;
					case 'pubDate':
						break;
					case 'link':
						$item->appendChild($this->createElement($key, $val));
						$hasLink = true;
						break;
						default:
						$item->appendChild($this->createElement($key, $val));
						break;
				}
			}
		} else {
			if (is_string($text)) {
				if (strlen($text) > 20) {
					$item->appendChild($this->createElement('description', $text));
				} else {
					$item->appendChild($this->createElement('title', $text));
				}
			} else return null;
		}
		$now = time();
		$pd = $item->appendChild($this->createElement('pubDate', date(DATE_RSS, $now)));
		$pd->setAttributeNS(NAMESPACE_DC, 'dc:date', date(DATE_W3C, $now));
		$channel = $this->getElementsBytagName('channel')->item(0);
		$c = $channel->firstChild;
		while(isset($c) && $c->nodeName != 'link') $c = $c->nextSibling;
		if (isset($c)) {
			$file = $c->nodeValue;
			if (!hasLink) 
				$item->appendChild($this->createElement('link', $file . '#' . $item->getAttribute('xml:id')));
			if (!$hasGuid) 
				$item->appendChild($this->createElement('guid', $file . '#' . $item->getAttribute('xml:id')))->setAttribute('isPermaLink', 'true');
		}
		return $item;
	}

	/**
	 * 
	 * Appends an item to the channel.
	 * @param DOMNode $item
	 */
	public function appendItem($item) {
		if (!isset($this->channel)) die("rssDocument::appendElement():Channel not found");
		return $this->channel->appendChild($item);
	}
	
	/**
	 * 
	 * Inserts an item before another item, or appends the item
	 * if the number of items is less than the reference index.
	 * @param DOMNode $item
	 * @param int $refIndex
	 * @return the inserted item.
	 */
	public function insertItem($item, $refIndex = 0) {
		if (!isset($this->documentElement)) die('rssDocument::insertItem(): documentElement not found');
		if (!isset($this->channel)) die("rssDocument::insertItem():Channel not found");
		$ref=(isset($refIndex))?$refIndex:0;
		$items = $this->getElementsByTagName('item');
		if ($items->length <= $ref) return $this->appendItem($item);
		$refItem = $items->item($ref);
		return $refItem->parentNode->insertBefore($item, $refItem);
	}
	
	/**
	 * 
	 * Spam scoring. Reads a list of patterns/scores. All text input is matched 
	 * against the patterns, and the score is summed up for each match.
	 * @param string $text
	 * @return int score
	 */
	public function spamScore($text) {
		$totalScore = 0;
		if (file_exists('score.txt')) {
			$fh = fopen('score.txt');
			if (isset($fh) && $fh != false) {
				$line = trim(fgets($fh));
				while (isset($line) && !empty($line)) {
					$c = $line[0];
					$pos = strrpos($line, $c);
					$pattern = substr($line, 0, $pos+1);
					$score = substr($line, $pos+1);
					$n = preg_match_all($pattern, $text, $matches);
					if ($n > 0) {
						print "pattern matched $n times\n";
						$totalScore += $n * $score;
					}
					print $pattern . ', ' . $score . "\n";
					$line = trim(fgets($fh));					
				}
				fclose($fh);
			}
		}
		return $totalScore;
	}
	
	/**
	 * 
	 * Returns true if spamScore > 5.
	 * @param string $text
	 * @return true if spamScore > 5, false else
	 */
	public function isSpam($text) {
		return $this->spamScore($text) > 5;
	}
	
	/**
	 * 
	 * Adds a comment as item to the rss file. The text is scored
	 * for spam before insertion.
	 * @param mixed $data
	 * @return DOMNode item or null if not added.
	 */
	public function addComment($data) {
		if (!isset($data)) return;
		if (is_array($data)) {
			foreach($data as $d) {
				if ($this->isSpam($d)) return null;
			}
		} else {
			if ($this->isSpam($data)) return null;
		}
		return $this->appendItem($this->createItem($data));
	}

	/**
	 * 
	 * Cuts down number of items. Keeps the first $n and cuts off
	 * remainder.
	 * @param int $n
	 */
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
