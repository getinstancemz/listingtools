<?php

namespace getinstance\listingtools\scripts;

require_once(__DIR__ . "/_findautoload.php");

use getinstance\listingtools\output\Parser;
use getinstance\listingtools\output\SourceFiles;
use getinstance\listingtools\output\Indexer;
use getinstance\listingtools\output\Chat;

if (count($argv) < 3) {
    fwrite(STDERR, "usage: output.php <srcdir> <listingno>\n");
    exit(1);
}

$dir = $argv[1];
$key = $argv[2];
$parser = new Parser();
$sourcefiles = new SourceFiles();
$chat = new Chat();
$indexer = new Indexer($dir, $parser, $sourcefiles);
$listings = $indexer->getListings();


if (! isset($listings[$key])) {
    $chat->warn("no listing '$key' found\n");
    exit;
}

$parser = new Parser();
$out = "";
foreach ($listings[$key] as $file) {
    $out .= $parser->parse(file_get_contents($file), $key, false);
}

$chat->out("\n");
$chat->out($out);
