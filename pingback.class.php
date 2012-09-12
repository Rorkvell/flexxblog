<?php

require_once('rss2.class.php');
require_once('conf.php');

class pingback extends xmlrpcDocument {

	/*
	 * Checks for robots permission.
	 * 
	 * Loads robots.txt and parses it.
	 * @param $url url to be downloaded if permission
	 * @see http://www.searchtools.com/robots/robots-exclusion-protocol.html
	 */
	public function robotsAllowedURL($url) {
		$urlParts = parse_url($url);
		$baseUrl = $urlParts['scheme'] . '://';
		if (isset($urlParts['user']) && !empty($urlParts['user']))
			$baseUrl .= $urlParts['user'] . '@';
		$baseUrl .= $urlParts['host'];
		if (isset($urlParts['pass']) && !empty($urlParts['pass']))
			$baseUrl .= ':' . $urlParts['pass'];
		$robotsUrl = $baseUrl . '/robots.txt';
		if (DEBUG) print "robotsUrl = $robotsUrl\n";
		$hd = Array(
			'Accept-Language: *',
			'Accept: text/plain,*/*;q=0.3'
		);
		$ch = curl_init($robotsUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
		curl_setopt($ch, CURLOPT_HTTPHEADER, $hd);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
		//curl_setopt($ch, CURLOPT_HEADER, true);
		$rs = curl_exec($ch);
		if (DEBUG) {
			print "robots.txt:\n";
			print $rs;
			print "\n";
		}
		$info = curl_getinfo($ch);
		if (DEBUG) print_r($info);
		curl_close($ch);
		
		// Check for existence
		if ($info['http_code'] >= 400) return true;	// Any error like not existing file
		if ($info['http_code'] >= 300) return false;	// TODO: Add proper handling
		if ($info['http_code'] < 200) return false;	// TODO: Add proper handling
		// Result = 2xx, robots.txt existing.
		if (DEBUG) print "Parsing robots.txt\n";
		if (strpos($info['content_type'], ';') !== false) {
			list($type, $charset) = explode(';', $info['content_type']);
			$type = trim($type);
			$charset = trim($charset);
		} else {
			$type = trim($info['content_type']);
			$charset = '';
		}
		// Parse file, see http://www.searchtools.com/robots/robots-exclusion-protocol.html
		$me = false;
		$rc = true;
		$lines = explode("\n", $rs);
		foreach ($lines as $line) {
			$line = trim($line);
			$p = strpos($line, '#');	// Any comments?
			if ($p !== false) {		// Cut off comments
				$line = substr($line, 0, $p-1);
			}
			if (strlen($line)>5 && strpos($line, ':') != false) {
				list($key, $val) = explode(':', $line, 2);
				$val = trim($val);
				switch ($key) {
					case 'User-agent':
						if ($val == '*' || $val == UA) {
							if (DEBUG) print "Found user agent $val\n";
							$me = true;
						} else {
							$me = false;
						}
						break;
					case 'Disallow':
						if ($val[0] != '/') $val = '/' . $val;
						if ($me) {
							if (DEBUG) print "Found Disallow $val\n";
							$a = strlen($urlParts['path']);
							$b = strlen($val);
							if ($a > $b) {
								if (substr($urlParts['path'], 0, $b) == $val) {
									$rc = false;
								}
							}
						}
						break;
					case 'Allow':							
						if ($val[0] != '/') $val = '/' . $val;
						if ($me) {
							if (DEBUG) print "Found Allow $val\n";
							$a = strlen($urlParts['path']);
							$b = strlen($val);
							if ($a > $b) {
								if (substr($urlParts['path'], 0, $b) == $val) {
									$rc = true;
								}
							}
						}
						break;
				} // switch (key)	
			} // strlen 		
		} // foreach line
		return $rc;
	}

	/*
	 * Loads a web resource. HTTP header is parsed for X-Robots-Tag: noindex. 
	 * If found, returns the empty string
	 * @see https://developers.google.com/webmasters/control-crawl-index/docs/robots_meta_tag
	 */
	public function load($ch) {
		$hd = Array(
			'Accept-Language: de-de, de, en;q=0.5, fr;q=0.2',
			'Accept: application/xml,application/rss+xml,text/xml;q=0.9,application/xhtml+xml;q=0.8,text/html;q=0.7,*/*;q=0.3'
		);
		include_once('licenses.php');
		$allowed = 7;
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
		curl_setopt($ch, CURLOPT_HTTPHEADER, $hd);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
		curl_setopt($ch, CURLOPT_HEADER, true);
		$rs = curl_exec($ch);
		$info = curl_getinfo($ch);
		if (DEBUG) print_r($info);
		
		// Parse header
		$line = trim(substr($rs, 0, strpos($rs, "\n")));
		while ($line != '') {
			if (DEBUG) print strlen($line) . '|' . $line . "|\n";
			if (strlen($line) > 12 && strpos($line, ':')) {
				list($key, $val) = explode(':', $line, 2);
				$val = trim($val);

				switch($key) {
					case 'X-Robots-Tag':
						if (strpos($val, 'noindex') !== false) {
							if (DEBUG) print "Disallowed by HTTP X-Robots-Tag\n";
							return '';
						}
						break;
					case 'Link':
						if ($val[0] == '<') {
							list($url, $att) = explode(';', $val, 2);
							$url = trim($url);
							$url = trim($url, "<>");
							$att = trim($att);
							list($attName, $attVal) = explode('=', $att);
							$attName = trim($attName);
							$attVal = trim($attVal);
							if ($attVal == 'license' || $attVal == 'dc:rights') {
								if (DEBUG) print "found license: $url\n";
								if (array_key_exists($url, $Licenses)) {
									$allowed &= $Licenses[$url];
									if (DEBUG) print "License found\n";
								} else {
									$allowed = 0;
									if (DEBUG) print "License not found\n";
								}
							}
						}
						break;
				}
			}
			$rs = substr($rs, strpos($rs, "\n")+1);
			$line = trim(substr($rs, 0, strpos($rs, "\n")));
		}
		$rs = substr($rs, strpos($rs, "\n")+1);	// remove the empty line
		return $rs;
	}
			
	/*
	 * handles a pingback request
	 * 
	 * @see: http://www.hixie.ch/specs/pingback/pingback#TOC3
	 */
	public function ping($params) {
		if (DEBUG) print "pingback::ping()\n";
		$accessAllowed = true;
		if (!is_array($params)) {
			return $this->errorResponse(32, 'Neither source nor target found');
		}
		if (count($params) < 2) {
			return $this->errorResponse(32, 'Either source or target not found');
		}
		$src = $params[0];
		$dst = $params[1];
		$dstFile = basename($dst);
		$fnameParts = explode('.', $dstFile, 2);
		$dstFile = basename(dirname($dst)) . '/' . $fnameParts[0] . '.rss';
		
		// 1. test target (local file)
		// Is target existing?
		if (!file_exists($dstFile)) return $this->errorResponse(32, $dstFile . ' not found');
		$rssDoc = new rssDocument();
		// Could target be read and parsed as xml?
		$rc = $rssDoc->load($dstFile);
		if ($rc === false) return $this->errorResponse(0, 'Error loading ' . $dstFile);
		// Is target an rss document?
		if (!isset($rssDoc->documentElement)) return $this->errorResponse(0, 'Empty document');
		if ($rssDoc->documentElement->nodeName != 'rss') return $this->errorResponse(0, 'Wrong document format');
		// Has target a channel?
		$e = $rssDoc->documentElement->firstChild;
		while (isset($e) && $e->nodeName != 'channel') $e = $e->nextSibling;
		if (!isset($e)) return $this->errorResponse(0, 'No channel found');
		$att = $e->getAttribute('xml:id');
		if (!isset($att)) return $this->errorResponse(33, 'Target is not ping enabled');
		if (($srcStr = $this->IsvalidSource($src)) === false) return $this->errorResponse(0, $src . ' is invalid');
		// O.k., everything seems fine
		
		// 2. test source (remote file)
		if (!$this->robotsAllowedURL($src)) {
			return $this->errorResponse(17, 'Access to ' . $src . ' not allowed');
		}
		
		// Here we go if either there was no robots.txt or access was not disallowed
		// Now get the source file
//		$hd = Array(
//			'Accept-Language: de-de, de, en;q=0.5, fr;q=0.2',
//			'Accept: application/xml,application/rss+xml,text/xml;q=0.9,application/xhtml+xml;q=0.8,text/html;q=0.7,*/*;q=0.3'
//		);
		$ch = curl_init($src);
		$rs = $this->load($ch);
		if (empty($rs)) return $this->errorResponse(17, 'file empty or access not allowed');
/*
$fh = fopen('debug.html', "w");
fwrite($fh, $rs);
fclose($fh);
*/		
		$info = curl_getinfo($ch);
		curl_close($ch);
		if (strpos($info['content_type'], ';') !== false) {
			list($type, $charset) = explode(';', $info['content_type']);
		} else {
			$type = $info['content_type'];
			$charset = '';
		}
		switch ($type) {
			case 'text/html':
				$srcDoc = new DOMDocument();
				$rc = $srcDoc->loadHTML($rs);
				if ($rc === false) return $this->errorResponse(17, 'error parsing source file');
				if (!isset($srcDoc->documentElement)) return $this->errorResponse(17, 'error parsing source file');
				if ($srcDoc->documentElement->nodeName != 'html') return $this->errorResponse(17, 'wrong source format');
				if (DEBUG) print $srcDoc->saveHTML();
				$transformName = 'html2rdf.xsl';
				if (!file_exists($transformName)) return $this->errorResponse(17, 'could not parse source (xsl transform sheet missing');
				$xslDoc = new DOMDocument();
				if (!isset($xslDoc)) return $this->errorResponse(0, 'Error on creating xslDoc');
				$rc = $xslDoc->load($transformName);
				if ($rc === false) return $this->errorResponse(0, 'error parsing xsl doc');
				$proc = new XSLTProcessor();
				if (!isset($proc)) return $this->errorResponse(0, "Error on creating XSLTProcessor");
				$proc->importStylesheet($xslDoc);
				$proc->setParameter('', 'REF', $dst); // TODO: check
				$proc->setParameter('', 'SRC', $src);
				$meta = $proc->transformToDoc($srcDoc);
				if ($meta === false) return $this->errorresponse(0, "Error on converting to rdf");
				if (DEBUG) print $meta->saveXML();					
				break;
			case 'application/xhtml+xml':
				break;
			case 'application/xml':
			case 'application/rss+xml':
			case 'text/xml':
				// TODO: replace with proper parsing
				return $this->errorResponse(17, 'Source file format not parseable, found ' . $type);
				break;
			default:
				return $this->errorResponse(17, 'Source file format not parseable, found ' . $type);
		}
		//print $rs;
		
		// Everything went fine. Now check the resulting rdf
		
		// Check for licenses
//		include_once('licenses.php');
//		$docLicenses = $meta->getElementsbyTagNameNS('http://purl.org/dc/elements/1.1/', 'rights');
//		if ($docLicenses->length > 0) {
//			for ($i=0; $i<$docLicenses->length; $i++) {
//				if (array_key_exists($docLicenses->item($i)->nodeValue, $Licenses)) {
					
//				}
//			}
//		}
		
		// Check for backlinks
		$backlinks = $meta->getElementsByTagNameNS('http://purl.org/dc/elements/1.1/', 'source');
		if ($backlinks->length == 0) {
			return $this->errorResponse(17, 'backlink not found');
		}
		//print $backlinks->length . "\n";
		
		// Checking for title
		$title = '';
		$tl = $meta->getElementsbyTagNameNS('http://purl.org/dc/elements/1.1/', 'title');
		if (isset($tl) && $tl->length > 0) {
			$title = $tl->item(0)->nodeValue;
		}
		
		$linkUrl = '';
		$linkText = '';
		
		for($i=0; $i<$backlinks->length; $i++) {
			$backlink = $backlinks->item($i);
			if ($backlink->hasChildNodes()) {
				$c = $backlink->firstChild;
				while (isset($c) && $c->nodeName != 'dct:abstract') {
					$c = $c->nextSibling;
				}
				if (isset($c) && strlen($c->nodeValue) > strlen($linkText)) {
					$linkText = $c->nodeValue;
					$linkUrl = $backlink->getAttributeNS('http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'resource');
				}
			}
		}
		if (empty($linkText)) {
			$dl = $meta->getElementsByTagNameNS('http://purl.org/dc/elements/1.1/', 'description');
			if (isset($dl) && $dl->length > 0) {
				$linkText = $dl->item(0)->nodeValue;
			}
		}
		
		// Author
		$Author = '';
		$AuthorURI = '';
		$authors = $meta->getElementsByTagNameNS('http://purl.org/dc/elements/1.1/', 'creator');
		//print $authors->length . "\n";
		if ($authors->length > 0) {
			for ($i=0; $i<$authors->length; $i++) {
				//print "\t" . $authors->item($i)->nodeValue . "\n";
				if (empty($AuthorURI)) {
					$l = $authors->item($i)->getAttributeNS('http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'resource');
					if (isset($l)) {
						$Author = $authors->item($i)->nodeValue;
						$AuthorURI = $l;
					}
				}
			}
		}

		//print $linkText . "\n";
		//print $linkUrl . "\n";
		//print $Author . "\n";
		//print $AuthorURI . "\n";
		//print $title . "\n";
		
		// We now got a set of metadata regarding the backlink:
		// $linkText: The text around the backlink.
		// $linkUrl: The URL of the backlink
		// $Author: Name of Author	
		// $title: Title
		
		//print "Load $dstFile\n";
		$cfeed = new rssDocument();
		$cfeed->load($dstFile);
		$citem = array('description' => $linkText, 'title' => 'ping');
		if (!empty($Author)) {
			$citem['author'] = $Author;
		} else {
			if (!empty($title)) $citem['author'] = $title;
		}
		$citem['source'] = $src;
		if (DEBUG) print_r($citem);
		$cfeed->appendItem(null, $citem);
		if (DEBUG) print $cfeed->saveXML();
		else {
			$cfeed->save($dstFile);
			$cfeed->saveHTMLFile(substr($dstFile, 0, -4) . '.html.de');
		}
		
		
		return $this->buildResponse('Pingback tested.');
		// TODO: parse source for title and/or author

	}
	
	public function IsValidSource($src) {
		// TODO: check source
		return true;
	}

}
?>