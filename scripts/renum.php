<?php

namespace getinstance\listingtools\scripts;
use getinstance\listingtools\output\Renum;

require_once(__DIR__ . "/_findautoload.php");

use getinstance\listingtools\output\Parser;
use getinstance\listingtools\output\Indexer;
use getinstance\listingtools\output\SourceFiles;
use getinstance\listingtools\output\Chat;

$chat = new Chat();
if (count($argv) < 2) {
    $chat->warn("usage: renum.php <dir>\n");
    $chat->warn("   -d  dry-run. Will output summary but write nothing\n");
    $chat->warn("   -o  print changes. Outputs changes to STDOUT - does not write to file\n");
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



$indexer = new Indexer($dir);
$sourcefiles = new SourceFiles($stdoutonly, $dryrun);
$renum = new Renum($indexer, $sourcefiles, $stdoutonly, $dryrun);
$renum->run($chat);
