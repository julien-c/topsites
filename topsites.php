<?php

require 'topsites.class.php';


if (count($argv) < 3) {
    echo "Usage: $argv[0] ACCESS_KEY_ID SECRET_ACCESS_KEY [COUNTRY_CODE]\n";
    exit(-1);
}
else {
    $accessKeyId = $argv[1];
    $secretAccessKey = $argv[2];
    $start = count($argv) > 3 ? $argv[3] : 1;
}

$topSites = new TopSites($accessKeyId, $secretAccessKey, 'FR', $start);
$topSites->getTopSites();

