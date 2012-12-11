<?php

require 'topsites.class.php';

$accessKeyId = $argv[1];
$secretAccessKey = $argv[2];

for ($i = 0; $i < 300; $i++) {
	$start = $i * 100 + 1;
	$topSites = new TopSites($accessKeyId, $secretAccessKey, 'FR', $start);
	$topSites->getTopSites();
	
	sleep(2);
}
