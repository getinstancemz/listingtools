<?php

namespace getinstance\listingtools\scripts;

require_once(__DIR__ . "/_findautoload.php");

use getinstance\listingtools\output\Indexer;

if (count($argv) < 2) {
    fwrite(STDERR, "usage: doindex.php <file_or_dir>\n");
    exit(1);
}

$dir = $argv[1];
$indexer = new Indexer();
print "\n";
$indexer->doIndex($dir);
$listings = $indexer->getListings();
Indexer::dottedKeySort($listings);
foreach ($listings as $listing => $files) {
    print "$listing: \n";
    foreach ($files as $file) {
        print "    $file\n";
    }
}
