<?php

namespace getinstance\listingtools\scripts;

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
$indexer = new Indexer();
$indexer->doIndex($dir);
$listings = $indexer->getListings();
Indexer::dottedKeySort($listings);
$filestochange = [];

$ordered = [];

foreach ($listings as $listing => $files) {
    $listingparts = explode(".", $listing);
    if (! isset($ordered[$listingparts[0]][$listing])) {
        $ordered[$listingparts[0]][$listing] = [];
    }
    $ordered[$listingparts[0]][$listing][] = [
        "listing" => $listing,
        "files" => $files
    ];
}
$fc = new FilesToChange($stdoutonly);
$rtag = "__renum__";

foreach ($ordered as $cno => $chapter) {
    $count = 0;
    Indexer::dottedKeySort($chapter);
    $trigger = Parser::getRegexpTrigger();
    foreach ($chapter as $listingkey => $infos) {
         $count++;
         $countstr = str_pad($count, 2, "0", STR_PAD_LEFT);
         $newtarget = "{$cno}.{$countstr}";
        if ($newtarget == $listingkey) {
            fwrite(STDERR, "no change: $newtarget\n");
            continue;
        } else {
            fwrite(STDERR, "$listingkey -> $newtarget\n");
        }
        foreach ($infos as $info) {
            foreach ($info['files'] as $file) {
                fwrite(STDERR, "   $file\n");
                $contents = $fc->getFileContents($file);
                $keymatch = preg_replace("/\\./", "\\.", $listingkey);
                $contents = preg_replace("%^($trigger)(/*listing)\s+{$keymatch}\s%m", "\\1" . "{$rtag}" . "\\2 {$newtarget} ", $contents);
            }
            $fc->storeFile($file, $contents);
        }
    }
}

if (! $dryrun) {
    $fc->saveFiles($rtag);
} else {
    fwrite(STDERR, "dry run -- no write\n");
}

class FilesToChange
{
    private $files = [];
    private $stdoutonly = false;

    function __construct($stdoutonly = false)
    {
        if ($stdoutonly) {
            $this->stdoutonly = true;
        }
    }

    function getFileContents($file)
    {
        if (! isset($this->files[$file])) {
            $this->files[$file] = file_get_contents($file);
        }
        return $this->files[$file];
    }

    function storeFile($file, $contents)
    {
        $this->files[$file] = $contents;
    }

    function saveFiles($removetag = null)
    {
        foreach ($this->files as $file => $contents) {
            if (! is_null($removetag)) {
                $contents = preg_replace("/$removetag/", "", $contents);
            }
            if ($this->stdoutonly) {
                fwrite(STDERR, "write to STDOUT only for $file:\n");
                print $contents . "\n";
            } else {
                fwrite(STDERR, "writing $file\n");
                file_put_contents($file, $contents);
            }
        }
    }
}
