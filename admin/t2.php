<?php

$ch = curl_init("http://www.rorkvell.de/");
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_VERBOSE, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$rc = curl_exec($ch);

curl_close($ch);

print $rc;

?>
