<?php

namespace getinstance\listingtools\output;

class Indexer
{
    private $indexed = [];
    private $listings = array();

    public function __construct($dir) {
        $this->doIndex($dir);
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

    private function doIndex($dir)
    {
        if (in_array($dir, $this->indexed)) {
            return;
        }
        if (! file_exists($dir)) {
            throw new \Exception("'$dir' does not exist");
        }

        if (is_file($dir)) {
            return $this->handleFile($dir);
        }
        $dh = opendir($dir);
        while (false !== ($item = readdir($dh))) {
            if (strpos($item, ".") === 0) {
                continue;
            }
            $path = $dir . "/" . $item;
            if (is_link($path)) {
                continue;
            } elseif (is_file($path)) {
                $this->handleFile($path);
            } elseif (is_dir($path)) {
                $this->doIndex($path);
            }
        }
        closedir($dh);
        $this->indexed[] = $dir;
    }

    function getListings()
    {
        return $this->listings;
    }

    function getStructuredListings() {
        $listings = $this->listings;
        self::dottedKeySort($listings);
        
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
        if (! is_file($file)) {
            throw new Exception("'$file' is not a file");
        }
        $parser = new Parser();
        $parser->parse(file_get_contents($file));
        $matches = $parser->getMatches();
        foreach ($matches as $listing => $text) {
            $this->listings[$listing][] = $file;
        }
    }
}
