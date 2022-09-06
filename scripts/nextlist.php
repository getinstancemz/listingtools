<?php

namespace getinstance\listingtools\scripts;

require_once(__DIR__ . "/_findautoload.php");

use getinstance\listingtools\output\Indexer;
use getinstance\listingtools\output\Parser;
use getinstance\listingtools\output\SourceFiles;

if (count($argv) < 3) {
    fwrite(STDERR, "usage: nextlist.php <article-id> <dir>\n");
    exit(1);
}

$dirchapter = $argv[1];
$dir = $argv[2];

$sourcefiles = new SourceFiles();
$parser = new Parser();
$indexer = new Indexer($dir, $parser, $sourcefiles);

$listings = $indexer->getListings();



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
