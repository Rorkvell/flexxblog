<?php
$users = Array(
	'siegfried' => Array(
		'name' => 'Siegfried Gipp', 
		'url' => 'http://www.rorkvell.de/impressum', 
		'email' => 'siegfried@rorkvell.de'
	)
);

function getUser($name) {
	global $users;
	if (array_key_exists($name, $users)) {
		return $users[$name];
	} else return false;
}
?>