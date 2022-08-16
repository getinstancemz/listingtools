<?php

namespace getinstance\listingtools\scripts;

require_once(__DIR__ . "/_findautoload.php");

use getinstance\listingtools\output\Parser;

if (count($argv) < 2) {
    fwrite(STDERR, "usage: parser.php <file> [listingno]\n");
    exit(1);
}

$file = $argv[1];
$listingno = (count($argv) > 2) ? $argv[2] : "";
$text = file_get_contents($file);
$parser = new Parser();

print "\n";
$out = $parser->parse($text, $listingno, true);
print $out;
/*
$lines = explode("\n", $out);
foreach($lines as $line) {
    if (preg_match("/^(\s+)/", $line, $matches)) {
        $leading =
    }
}
*/
