<?php 

class xmlDocument extends DOMDocument {
	
	// sed test: s/<meta \([^>]*\)><\/meta>/<meta \1\/>/g
	public static $errorPatterns = Array(
		'/<meta ([^>]*)><\/meta>/',
		'/<link ([^>]*)><\/link>/',
		'/<img ([^>]*)><\/img>/',
		'/<input ([^>]*)><\/input>/',
		'/<hr ([^>]*)><\/hr>/'
		);
	public static $errorReplacements = Array(
		'<meta ${1}/>',
		'<link ${1}/>',
		'<img ${1}/>',
		'<input ${1}/>',
		'<hr ${1}/>'
		);
		
	public $transformName = null;
	
	public function saveAs($type, $node = null, $params = null) {
		if (!isset($type)) die('Error: No target type given');
		if (!isset($this->documentElement)) die('Error: No root element given');
		if (!isset($this->transformName))
			$this->transformName = $this->documentElement->nodeName . '2' . $type . '.xsl';
		if (!file_exists($this->transformName)) $this->transformName = '../' . $this->transformName;
		if (!file_exists($this->transformName)) die("Error: Could not find " . $this->transformName);
		// Get xsl stylesheet		
		$xslDoc = new DOMDocument();
		if (!isset($xslDoc)) die("Error on creating xslDoc");
		$xslDoc->load($this->transformName, LIBXML_NOBLANKS | LIBXML_NONET);
		// Create xslt processor
		$proc = new XSLTProcessor();
		if (!isset($proc)) die("Error on creating XSLTProcessor");
		$proc->importStylesheet($xslDoc);
		if (isset($params)) {
			if (is_array($params)) {
				foreach ($params as $key => $val) {
					$proc->setParameter('', $key, $val);
				}				
			} else {
				$proc->setParameter('', 'TYPE', $params);
			}
		}
		// Transform document
		if (isset($node)) $rc = $proc->transformToXML($node);
		else $rc = $proc->transformToXML($this);
		if ($rc === false || empty($rc)) die('Error on converting to ' . $type);
		$Doc = new DOMDocument();
		$Doc->preserveWhiteSpace = false;
		$Doc->formatOutput = true;
		if ($type == 'html') {
			$Doc->loadHTML($rc);
			return $Doc->saveHTML();
		} else {
			$Doc->loadXML($rc);
			return $Doc->saveXML();
		}
	}
	
	public function saveHTML($node = null, $params = null) {
		$rc = $this->saveAs('html', $node, $params);
		for ($i=0; $i<count(xmlDocument::$errorPatterns); $i++)
			$rc = preg_replace(xmlDocument::$errorPatterns[$i], xmlDocument::$errorReplacements[$i], $rc);
		return $rc;
	}
	
	public function saveHTMLFile($filename, $params = null) {
		if (!isset($filename)) die("Error: No filename given");
		$rc = $this->saveHTML(null, $params);
		$fh = fopen($filename, "w");
		if (!isset($fh)) die("Error opening file");
		$n = fwrite($fh, $rc);
		fclose($fh);
		return $n;
	}
	
	public function convertToHTMLDoc($params = null) {
		if (!isset($this->transformName))
			$this->transformName = $this->documentElement->nodeName . '2html.xsl';
		if (!file_exists($this->transformName)) $this->transformName = '../' . $this->transformName;
		if (!file_exists($this->transformName)) return null;
		$htmlDoc = new DomDocument();
		$htmlDoc->preserveWhiteSpace = false;
		$htmlDoc->formatOutput = true;
		$htmlDoc->loadHTML($this->saveHTML($params));
		return $htmlDoc;		
	}
	


}
?>