<?php

declare(strict_types=1);

namespace getinstance\listingtools\tests;

use getinstance\listingtools\output\Renum;
use getinstance\listingtools\output\Indexer;
use getinstance\listingtools\output\SourceFiles;
use getinstance\listingtools\output\Chat;
use PHPUnit\Framework\TestCase;

final class RenumTest  extends TestCase
{
    function testRenumRun() {
       
        $output = $this->createMock(Chat::class);
        $sourcefiles = $this->createMock(SourceFiles::class);

        $listings = [
            ["001.02", "first", "001\\.01"],
            ["001.02.01", "second", "001\\.02"],
            ["001.03.01", "third", "001\\.03"],
            ["001.03.03", "fourth", "001\\.04"]
        ];

        $fullstructure = [];
        $argmap = [];
        $consectest = [];
        foreach ($listings as $listing) {
            $filename = "ch001/batch01/{$listing[1]}.php";
            // the index after transformation
            $idxreg=$listing[2];
            $fullstructure["001"][$listing[0]] = [[
                        "listing" => $listing[0],
                        "files" => [
                            $filename
                        ]
                    ]];
            $contents = <<<CONTENTS
                print "hello world";
                /* listing {$listing[0]} */
                    // show me off
                /* /listing {$listing[0]} */

CONTENTS;
            $argmap[] = [$filename, $contents];
            $consectest[] = [
                    $this->callback(function($file) use ($filename) {
                        return ($file == $filename);
                    }),
                    $this->callback(function($contents) use ($idxreg) {
                        return (
                            preg_match("|/\* __renum__listing {$idxreg}\s+\*/|", $contents) &&
                            preg_match("|/\* __renum__/listing {$idxreg}\s+\*/|", $contents));
                    }),
                ];

        }
        $indexer = $this->createStub(Indexer::class);
        $indexer->method("getStructuredListings")->willReturn($fullstructure);
        $sourcefiles->method("getFileContents")->willReturnMap($argmap);
        $sourcefiles->expects($this->any())
            ->method("storeFile")
            ->withConsecutive($consectest[0], $consectest[1], $consectest[2], $consectest[3]);
        $renum = new Renum($indexer, $sourcefiles);
        $renum->run($output);
        $this->assertInstanceof(Renum::class, $renum);
    }
}
