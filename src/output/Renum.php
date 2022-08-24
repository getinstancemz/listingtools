<?php

namespace getinstance\listingtools\output;

class Renum {

    function __construct(private string $dir, private bool $stdoutonly=false, private bool $dryrun=false) {

    }

    function getOrderedList() {
        $indexer = new Indexer();
        $indexer->doIndex($this->dir);
        $listings = $indexer->getListings();
        Indexer::dottedKeySort($listings);

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
        return $ordered;
    }

    function run() {
        $ordered = $this->getOrderedList(); 
        $fc = new FilesToChange($this->stdoutonly);
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
                        $contents = preg_replace("%^($trigger)(/*listing)\s+{$keymatch}\b%m", "\\1" . "{$rtag}" . "\\2 {$newtarget} ", $contents);
                    }
                    $fc->storeFile($file, $contents);
                }
            }
        }

        if (! $this->dryrun) {
            $fc->saveFiles($rtag);
        } else {
            fwrite(STDERR, "dry run -- no write\n");
        }
    }
}

