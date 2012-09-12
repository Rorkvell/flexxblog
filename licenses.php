<?php
	
define('NONCOMERCIAL', 1);
define('NODERIVATIVES', 2);
define('SAMELICENSE', 4);
define('LICENSE_MASK', 6);
$Licenses = Array(
	'http://quotecommons.de/lizenzen/qca' => 7,
	'http://quotecommons.de/lizenzen/qcb' => 7,
	'http://quotecommons.de/lizenzen/qcc' => 0,
	'http://publicdomain/zero/1.0' => 7,
	'http://publicdomain/mark/1.0' => 7,
	'http://creativecommons.org/licenses/by/3.0' => 7,
	'http://creativecommons.org/licenses/by-sa/3.0'=> 7 - SAMELICENSE,
	'http://creativecommons.org/licenses/by-nd/3.0' => 7 - NODERIVATIVES,
	'http://creativecommons.org/licenses/by-nc/3.0' => 7 - NONCOMERCIAL,
	'http://creativecommons.org/licenses/by-nc-sa/3.0' => 7 - NONCOMERCIAL - NODERIVATIVES,
	'http://creativecommons.org/licenses/by-nc-nd/3.0' => 0
);
?>
