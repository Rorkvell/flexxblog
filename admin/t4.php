<?php

include('users.php');

if (function_exists('getUser')) {
	$user = getUser('siegfried');
		if (isset($user)) {
			print_r($user);
		}	
	}

?>