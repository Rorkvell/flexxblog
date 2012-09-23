<?php 

include_once('evlog.php');
/*
 * @see: http://xmlrpc.scripting.com/spec.html
 * @see: http://www.hixie.ch/specs/pingback/pingback-1.0
 * @see: https://en.wikipedia.org/wiki/Pingback
 * @see: https://de.wikipedia.org/wiki/XML-RPC
 * @see: https://en.wikipedia.org/wiki/Linkback
 */

class xmlrpcDocument extends DOMDocument {
	
	public function __construct() {
		parent::__construct('1.0', 'UTF-8');
		$this->formatOutput = true;
	}
	
	/*
	 * Loads the document either from given file, or, if no file is given,
	 * from php://input, which is the raw post data.
	 */
	public function load($file = null, $options = null) {
		if (isset($file)) return parent::load($file, $options);
		$xml = file_get_contents('php://input');
		//print $xml;
		return parent::load('php://input', $options);
	}
	
	public function createParam($p) {
		static $types = array(
			'i' => 'int',
			'd' => 'double',
			's' => 'string',
			'b' => 'boolean'	// TODO: Add structured types and arrays
		);
		$param = $this->createElement('param');
		$value = $param->appendChild($this->createElement('value'));
		$s = serialize($p);
		$parts = explode(':', $s);
		$v = $parts[count($parts)-1];
		switch($parts[0]) {
			case 's':
				$v = substr($parts[2], 1, $parts[1]);
				break;
			default:
				$v = $parts[1];
		}
		$value->appendChild($this->createElement($types[$parts[0]], $v));
		return $param;		
	}
	
	
	/*
	 * Build outgoing xml rpc structure
	 * @param string $method name of method
	 * @param $p A single parameter or a simple array of parameters for method
	 */	
	public function buildRequest($method, $p) {
		if (isset($this->documentElement)) return false;
		$this->appendChild($this->createElement('methodCall'));
		$method = $this->documentElement->appendChild($this->createElement('methodName', $method));
		if (isset($p)) {
			$params = $method->appendChild($this->createElement('params'));
			if (is_array($p)) {
				foreach ($p as $pa) {
					$params->appendChild($this->createParam($pa));
				}
			} else {
				$params->appendChild($this->createParam($p));
			}
		}
		return $this;
	}
	
	public function buildResponse($str) {
		if (isset($this->documentElement)) return false;
		$this->appendChild($this->createElement('methodResponse'));
		$params = $this->documentElement->appendChild($this->createElement('params'));
		$params->appendChild($this->createParam($str));
		return $this;
	}


/*
 <methodResponse>
  <fault>
    <value>
      <struct>
        <member>
          <name>faultCode</name>
          <value><int>33</int></value>
        </member>
        <member>
          <name>faultString</name>
          <value><string>Die angegebene URL kann nicht als Ziel verwendet werden
. Entweder existiert sie nicht oder der Empfäer erlaubt keine Pingbacks.</string
></value>
        </member>
      </struct>
    </value>
  </fault>
</methodResponse>
*/
	/*
	 * Buid an error response as xml rpc structure
	 * @param int $errCode 
	 * @param string $errString
	 */
	public function errorResponse($errCode, $errString) {
		if (isset($this->documentElement)) return false;
		$this->appendChild($this->createElement('methodResponse'));
		$response = $this->documentElement->appendChild($this->createElement('fault'));
		$v1 = $response->appendChild($this->createElement('value'));
		$s = $v1->appendChild($this->createElement('struct'));
		$m1 = $s->appendChild($this->createElement('member'));
		$m1->appendChild($this->createElement('name', 'faultCode'));
		$v2 = $m1->appendChild($this->createElement('value'));
		$v2->appendChild($this->createElement('int', $errCode));
		$m2 = $s->appendChild($this->createElement('member'));
		$m2->appendChild($this->createElement('name', 'faultString'));
		$v3 = $m2->appendChild($this->createElement('value'));
		$v3->appendChild($this->createElement('string', $errString));
		return $this;		
	}
 
/*
<?xml version="1.0"?>
<methodCall>
	<methodName>pingback.ping</methodName>
		<params>
			<param>
				<value><string>http://www.rorkvell.de/news/2012/Ohne_Worte</string></value>
			</param>
			<param>
				<value><string>https://netzpolitik.org/2012/einfach-mal-die-kommentare-schliesen/</string></value>
			</param>
		</params>
</methodCall>
*/
	public function methodCall() {
		$mnlst = $this->getElementsByTagName('methodName');
		if (!isset($mnlst)) return false;
		if ($mnlst->length < 1) return false;
		$mname = $mnlst->item(0);
		if (!isset($mname)) return false;
		if (empty($mname->nodeValue)) return false;
		list($class, $method) = explode('.', $mname->nodeValue, 2);
		if (!isset($method)) {		// simple function
			// TODO
		} else {					// class and method
			$fname = $class . '.class.php';
			if (file_exists($fname)) {
				include_once($fname);
				if (class_exists($class)) {
					$f = new $class();
					$params = $this->getElementsByTagName('param');
					$p = Array();
					for ($i=0; $i<$params->length; $i++) {
						if ($params->item($i)->hasChildNodes()) {
							$v = $params->item($i)->firstChild;
							while (isset($v) && $v->nodeName != 'value') {
								$v = $v->nextSibling;
							}
							if (isset($v) && $v->hasChildNodes()) {
								$type = $v->firstChild;
								while (isset($type) && $type->nodeType != XML_ELEMENT_NODE) {
									$type = $type->nextSibling;
								}
								if (isset($type)) {
									$p[] = $type->nodeValue;
								}
							}
						}
					}
					return $f->$method($p);
				}

			} else return $this->errorResponse(0, $fname . ' not found');
		}
		return $this->errorResponse(0, 'dummy');
	}

	public function methodResponse() {
		$e = $this->documentElement;

	}
	

}



?>