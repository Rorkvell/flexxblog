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
	
	public function saveHTML($node = null, $schema = null) {
		if (!isset($this->documentElement)) return parent::saveHTML($node);
		$transformName = $this->documentElement->nodeName . '2html.xsl';
		if (!file_exists($transformName)) $transformName = '../' . $transformName;
		if (!file_exists($transformName)) die("Error: Could not find " . $transformName);
		
		$xslDoc = new DOMDocument();
		if (!isset($xslDoc)) die("Error on creating xslDoc");
		$xslDoc->load($transformName);

		$proc = new XSLTProcessor();
		if (!isset($proc)) die("Error on creating XSLTProcessor");
		$proc->importStylesheet($xslDoc);
		if (isset($schema)) {
			$proc->setParameter('', 'TYPE', $schema);
		}
		if (isset($node)) $rc = $proc->transformToXML($node);
		else $rc = $proc->transformToXML($this);
		if ($rc === false) die("Error on converting to html");

		for ($i=0; $i<count(xmlDocument::$errorPatterns); $i++)
			$rc = preg_replace(xmlDocument::$errorPatterns[$i], xmlDocument::$errorReplacements[$i], $rc);
		//return preg_replace(xmlDocument::$errorPatterns, xmlDocument::$errorReplacements, $rc);
		if (empty($rc)) die("Error on converting to html");
		return $rc;
	}
	
	public function saveHTMLFile($filename, $schema = null) {
		if (!isset($filename)) die("Error: No filename given");
		$fh = fopen($filename, "w");
		if (!isset($fh)) die("Error opening file");
		$rc = $this->saveHTML(null, $schema);
		if ($rc === false) die("Error converting to html");
		$n = fwrite($fh, $rc);
		fclose($fh);
		return $n;
	}
	
	public function convertToHTMLDoc() {
		$transformName = $this->documentElement->nodeName . '2html.xsl';
		if (!file_exists($transformName)) $transformName = '../' . $transformName;
		if (!file_exists($transformName)) return null;
		
		$this->normalizeDocument();
		$xslDoc = new DOMDocument();
		$xslDoc->load($transformName);

		$proc = new XSLTProcessor();
		$proc->importStylesheet($xslDoc);
		$proc->setParameter('', 'FORMAT', 'HTML');
		return $proc->transformToDoc($this);
	}
	


}
?>