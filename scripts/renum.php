<?php

namespace getinstance\listingtools\scripts;
use getinstance\listingtools\output\Renum;

require_once(__DIR__ . "/_findautoload.php");

use getinstance\listingtools\output\Parser;
use getinstance\listingtools\output\Indexer;

if (count($argv) < 2) {
    fwrite(STDERR, "usage: renum.php <dir>\n");
    fwrite(STDERR, "   -d  dry-run. Will output summary but write nothing\n");
    fwrite(STDERR, "   -o  print changes. Outputs changes to STDOUT - does not write to file\n");
    exit(1);
}

$offset = 0;
$opts = getopt("do", [], $opind);
$offset = $opind - 1;
$dryrun = false;
$stdoutonly = false;
if (isset($opts['d'])) {
    $dryrun = true;
}
if (isset($opts['o'])) {
    $stdoutonly = true;
}
$pid = getmypid();
$dir = $argv[($offset + 1)];



$renum = new Renum($dir, $stdoutonly, $dryrun);
$renum->run();
