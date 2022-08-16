<?php

namespace getinstance\listingtools\scripts;

require_once(__DIR__ . "/_findautoload.php");

use getinstance\listingtools\github\GitHubConnect;

$connect = new GitHubConnect("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");
//$user = $connect->allGists();
$user = $connect->getUser();
$gists = $connect->allGists();
print_r($gists);


/*
if (count($argv) < 2) {
    fwrite(STDERR, "usage: parser.php <file> [listingno]");
    exit(1);
}
*/
