<?php

declare(strict_types=1);

namespace getinstance\listingtools\tests;

use getinstance\listingtools\output\Indexer;
use getinstance\listingtools\output\SourceFiles;
use getinstance\listingtools\output\Chat;
use getinstance\listingtools\output\Parser;

use PHPUnit\Framework\TestCase;

final class IndexerTest  extends TestCase
{
    function testIndexer() {
        $parser = $this->createMock(Parser::class);
        $sourcefiles = $this->createMock(SourceFiles::class);
        $sourcefiles->method("doIndex")->willReturnCallback(function($dir, $callback) {
            $callback("one.php");
            $callback("two.php");
            $callback("three.php");
            $callback("four.php");
        });
        $parser->method("getMatches")->willReturnOnConsecutiveCalls(
            ["001.02" => ["a","b","c"]],
            ["001.04" => ["a","b","c"]],
            ["001.08" => ["a","b","c"]],
            ["001.08" => ["a","b","c"]],
        );
        $indexer = new Indexer("mockdir", $parser, $sourcefiles);
        $listings = $indexer->getListings();

		$expected = [
			"001.02" => [ "one.php" ],
			"001.04" => [ "two.php" ],
			"001.08" => [ "three.php", "four.php" ],
		];
		$this->assertEquals($expected, $listings);
        $listings2 = $indexer->getStructuredListings();
		$expected = [
			"001" => [
				"001.02" => [[
					"listing" =>"001.02",
					"files" => [ "one.php" ]
				]],
				"001.04" => [[
					"listing" =>"001.04",
					"files" => [ "two.php" ]
				]],
				"001.08" => [[
					"listing" =>"001.08",
					"files" => [ "three.php", "four.php" ]
				]]
			]
		];

		$this->assertEquals($expected, $listings2);
    }
}
