<?php

namespace getinstance\listingtools\scripts;

require_once(__DIR__ . "/_findautoload.php");

use getinstance\listingtools\output\Parser;
use getinstance\listingtools\output\SourceFiles;
use getinstance\listingtools\output\Indexer;
use getinstance\listingtools\github\GitHubConnect;

function usageError($msg = "")
{
    fwrite(STDERR, "usage: gencode.php [options] <project> <srcdir> <chapterfile.md> [<output.md>]\n");
    fwrite(STDERR, "   -r  reflow. Ignore listing nn.nn and apply listings in sort order\n");
    fwrite(STDERR, "   -f  force. Where available slots do not match listings available in -r mode -- apply anyway. Careful!\n");
    fwrite(STDERR, "   -d  dry-run. Will show the current occupant of a slot against the incoming code index. Nothing written\n");
    fwrite(STDERR, "   -g  rather than generate text, will create a github gist and generate the embed code\n");
    fwrite(STDERR, "   -t  template\n");
    fwrite(STDERR, "\n\n");
    fwrite(STDERR, $msg . "\n\n");
    exit(1);
}

$offset = 0;
$opts = getopt("rfdgt:", [], $opind);
$offset = $opind - 1;


$mode = "named";
$dryrun = false;
$force = false;
$gistmode = false;
$template = null;

if (isset($opts['f'])) {
    $force = true;
}
if (isset($opts['r'])) {
    $mode = "flow";
}
if (isset($opts['d'])) {
    $dryrun = true;
}
if (isset($opts['g'])) {
    $gistmode = true;
}
if (isset($opts['t'])) {
    $template = $opts['t'];
}

$args = $argv;

array_splice($args, 1, $offset);
if (count($args) < 4) {
    usageError("not enough args");
}

$project = $args[1];
$dir = $args[2];
$file = $args[3];


//print "file is $file\n";
//exit;

$outfile = null;
if (isset($args[4])) {
    $outfile = $args[4];
}

$ret = "";
$contents = file_get_contents($file);
//$blocks = preg_split("|(```\n// listing.*?\n.*?```)|s", $contents, -1, PREG_SPLIT_DELIM_CAPTURE);
$blocks = preg_split("|(<!--\s+insert.*?\n.*?<!--\s+endinsert\s+-->)|s", $contents, -1, PREG_SPLIT_DELIM_CAPTURE);

$compiled = [];
$getter = new ListingGetter($project, $dir, $mode, $dryrun, $gistmode, $template);

if ($gistmode) {
    $tokenfile = $_SERVER['HOME'] . "/.listingtools/ghtoken.txt";
    if (! is_file($tokenfile)) {
        usageError("please save your github token to '$tokenfile' and run again");
    }
    $token = rtrim(file_get_contents($tokenfile));
    $ghcon = new GitHubConnect($token);
    $getter->setGitHubConnect($ghcon);
}

$codeblockcount = 0;

foreach ($blocks as $block) {
    // if (preg_match("|listing ([\\d\\.]+)|", $block, $matches)) {
    //if (preg_match("|^```\n// listing ([\\d\\.]+).*```$|s", $block, $matches)) {
    if (preg_match("|^<!--\s+insert ([\\d\\.]+)\s*(.*?)\s*-->|s", $block, $matches)) {
        $num = $matches[1];
        $additional = $matches[2];
        $compiled[] = [
            "type" => "code",
            "num" => $num,
            "additional" => $additional,
            "content" => $block
        ];
        $codeblockcount++;
    } else {
        $compiled[] = [
            "type" => "text",
            "content" => $block
        ];
    }
}
foreach ($compiled as $cblock) {
    if ($cblock['type'] == "code") {
        $num = $cblock['num'];
        $additional = $cblock['additional'];
        try {
            list($key, $listing) = $getter->getListing($cblock['num']);
            $ret .= "<!-- insert $key {$additional} -->\n";
            $ret .= $listing;
        } catch (\Exception $e) {
            usageError("error getting listing: " . $e->getMessage());
        }
        $ret .= "<!-- endinsert -->";
    } else {
        $ret .= $cblock['content'];
    }
}

// do not proceed if things don't add up in flow mode
if ($mode == "flow") {
    if ($codeblockcount != $getter->listingCount()) {
        fwrite(STDERR, "WARNING: available listings do no match chapter code blocks\n");
        fwrite(STDERR, "    codeblocks: {$codeblockcount}\n");
        fwrite(STDERR, "    available:  {$getter->listingCount()}\n\n");
        if (! $force && ! $dryrun) {
            exit(1);
        }
    }
}

if (! $dryrun) {
    if (is_null($outfile)) {
        print $ret;
    } else {
        file_put_contents($outfile, $ret);
    }
}

class ListingGetter
{
    private $listings = [];
    private $indexer;
    private $project;
    private $dir;
    private $mode = "named";
    private $gistmode = false;
    private $carp = false;
    private $ghcon = null;
    private $prefixtemplate = null;

    function __construct($project, $dir, $mode = null, $carp = false, $gistmode = false, $prefixtemplate = null)
    {
        $this->project = $project;
        $this->dir = $dir;
        $sourcefiles = new SourceFiles();
        $parser = new Parser();
        $this->indexer = new Indexer($this->dir, $parser, $sourcefiles);
        $this->listings = $this->indexer->getListings();
        $this->gistmode = $gistmode;
        if (! is_null($prefixtemplate)) {
            $this->prefixtemplate = $prefixtemplate;
        }
        if ($mode == "flow") {
            $this->mode = $mode;
        }
        $this->carp = $carp;
    }

    function setGitHubConnect(GitHubConnect $ghcon)
    {
        $this->ghcon = $ghcon;
    }

    function listingCount()
    {
        return (count($this->listings));
    }

    function listingKey()
    {
        $key = key($this->listings);
        return $key;
    }

    function getListing($key)
    {
        if ($this->carp) {
            fwrite(STDERR, $key);
            if ($this->mode == "flow") {
                fwrite(STDERR, " - {$this->listingKey()}");
            }
            fwrite(STDERR, "\n");
        }
        if ($this->mode == "flow") {
            $key = $this->listingKey();
        }

        if (! isset($this->listings[$key])) {
            throw new \Exception("$key not found\n");
        }

        $parser = new Parser();
        $out = "";
        foreach ($this->listings[$key] as $file) {
            $out .= $parser->parse(file_get_contents($file), $key, false);
        }
        next($this->listings);
        $extension = pathinfo($file, \PATHINFO_EXTENSION);
        if ($this->gistmode) {
            if (is_null($this->ghcon)) {
                throw new \Exception("GitHubConnect not initialised");
            }
            $out = $this->ghcon->createOrUpdateGist($this->project, "listing{$key}", $extension, $out);
        } else {
            $acceptable = ["json" => "json", "js" => "javascript", "php" => "php", "sh" => "shell", "py" => "python"];
            $syntax = "";
            if (in_array($extension,array_keys($acceptable))) {
                $syntax = $acceptable[$extension]; 
            }
            $out = "```{$syntax}\n{$out}\n```\n";
        }
        if (! is_null($this->prefixtemplate)) {
            $t =  $this->prefixtemplate;
            $t = preg_replace("/%num%/", $key, $t);
            $out = "{$t}\n" . $out;
        }
        return [$key, $out];
    }
}
