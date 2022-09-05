<?php

namespace getinstance\listingtools\output;

class Indexer
{
    private $listings = array();

    public function __construct($dir, private Parser $parser, private Sourcefiles $sf) {
        $this->doIndex($dir, $sf);
    }

    private function doIndex($dir, SourceFiles $sf)
    {
        $callback = function($file) {
            $this->handleFile($file);
        };
        $sf->doIndex($dir, $callback);
    }

    public static function dottedKeySort(&$array)
    {
        uksort($array, function ($a, $b) {
            $akeys = explode(".", $a);
            $bkeys = explode(".", $b);

            $largest = max(count($akeys), count($bkeys));
            $akeys = array_pad($akeys, $largest, "0");
            $bkeys = array_pad($bkeys, $largest, "0");

            foreach ($akeys as $idx => $akey) {
                $akey = (int)$akey;
                $bkey = (int)$bkeys[$idx];

                if ($akey == $bkey) {
                    continue;
                }
                if ($akey > $bkey) {
                    return 1;
                }
                if ($akey < $bkey) {
                    return -1;
                }
            }
            return 0;
        });
    }

    function getListings()
    {
        self::dottedKeySort($this->listings);
        return $this->listings;
    }

    function getStructuredListings() {
        $listings = $this->listings;
        
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

    private function handleFile($file)
    {
        $contents = $this->sf->getFileContents($file); 
        $this->parser->parse($contents);
        $matches = $this->parser->getMatches();
        foreach ($matches as $listing => $text) {
            $this->listings[$listing][] = $file;
        }
    }
}
