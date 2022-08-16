<?php

namespace getinstance\listingtools\scripts;

require_once(__DIR__ . "/_findautoload.php");

use getinstance\listingtools\output\Indexer;


if (count($argv) < 3) {
    fwrite(STDERR, "usage: nextlist.php <article-id> <dir>\n");
    exit(1);
}

$dirchapter = $argv[1];
$dir = $argv[2];

//if (preg_match("|^(.*/ch)(\\d+)/|", $dir, $matches)) {
/*
    $dir = $matches[1].$matches[2];
    $dirchapter = $matches[2];
} else {
    fwrite(STDERR, "usage: nextlist.php <path_with_chNN>\n");
    exit(1);
}
*/

$indexer = new Indexer();
$indexer->doIndex($dir);
$listings = $indexer->getListings();
Indexer::dottedKeySort($listings);
$listings = array_reverse($listings);
if (! count($listings)) {
    $chapter = $dirchapter;
    $idx = "01";
} else {
    $num = key($listings);
    list($chapter, $idx) = explode(".", $num);
    $idx++;
    $idx = str_pad($idx, 2, "0", STR_PAD_LEFT);
}
$num = "$chapter.$idx";
print "/* listing $num */\n/* /listing $num */\n";
