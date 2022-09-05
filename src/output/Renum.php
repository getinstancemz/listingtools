<?php

namespace getinstance\listingtools\output;

class Renum {

    function __construct(private Indexer $indexer, private SourceFiles $sourcefiles) {

    }

    function getOrderedList()
    {
        return $this->indexer->getStructuredListings();
    }

    function run(Chat $chat) {
        $ordered = $this->getOrderedList(); 

        $fc = $this->sourcefiles;
        $rtag = "__renum__";
        foreach ($ordered as $cno => $chapter) {
            $count = 0;
            //Indexer::dottedKeySort($chapter);
            $trigger = Parser::getRegexpTrigger();
            foreach ($chapter as $listingkey => $infos) {
                $count++;
                $countstr = str_pad($count, 2, "0", STR_PAD_LEFT);
                $newtarget = "{$cno}.{$countstr}";
                if ($newtarget == $listingkey) {
                    $chat->warn("no change: $newtarget\n");
                    continue;
                } else {
                    $chat->warn("$listingkey -> $newtarget\n");
                }
                foreach ($infos as $info) {
                    foreach ($info['files'] as $file) {
                        $chat->warn("   $file\n");
                        $contents = $fc->getFileContents($file);
                        $keymatch = preg_replace("/\\./", "\\.", $listingkey);
                        $contents = preg_replace("%^($trigger)(/*listing)\s+{$keymatch}\b%m", "\\1" . "{$rtag}" . "\\2 {$newtarget} ", $contents);
                    }
                    $fc->storeFile($file, $contents);
                }
            }
        }
        $fc->saveFiles($chat, $rtag);
    }
}

