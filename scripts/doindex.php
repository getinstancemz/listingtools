<?php

namespace getinstance\listingtools\scripts;

require_once(__DIR__ . "/_findautoload.php");

use getinstance\listingtools\output\Indexer;
use getinstance\listingtools\output\Parser;
use getinstance\listingtools\output\SourceFiles;

if (count($argv) < 2) {
    fwrite(STDERR, "usage: doindex.php <file_or_dir>\n");
    exit(1);
}


$sourcefiles = new SourceFiles();
$parser = new Parser();
$dir = $argv[1];
$indexer = new Indexer($dir, $parser, $sourcefiles);
print "\n";
$listings = $indexer->getListings();
foreach ($listings as $listing => $files) {
    print "$listing: \n";
    foreach ($files as $file) {
        print "    $file\n";
    }
}
