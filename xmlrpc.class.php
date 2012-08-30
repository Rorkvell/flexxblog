<?php 

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
		//$this->appendChild($this->createElement('methodCall'));
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
		$value->appendChild($this->createElement($types[$parts[0]], $parts[count($parts)-1]));
		return $param;		
	}
	
	
	/*
	 * Build outgoing xml rpc structure
	 * @param string $method name of method
	 * @param $p A single parameter or a simple array of parameters for method
	 */	
	public function build($method, $p) {
		//print_r($params);
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
 
	

}


/*****************************************************/

//$doc = new xmlrpcDocument();
//$doc->build('pingback.ping', Array('http://www.rorkvell.de/news/2012/Ohne_Worte', 'https://netzpolitik.org/2012/einfach-mal-die-kommentare-schliesen/'));
//$doc->errorResponse(33, 'not implemented');


//print $doc->saveXML();

?>