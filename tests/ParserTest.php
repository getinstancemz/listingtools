<?php

declare(strict_types=1);

namespace getinstance\listingtools\tests;

use getinstance\listingtools\output\Parser;

use PHPUnit\Framework\TestCase;

final class ParserTest  extends TestCase
{
    public function testBasicParse() {

        $source = <<<SOURCE
intro
/* listing 001.01 */
first
/* /listing 001.01 */
ignore me
/* listing 001.02 */
aaa
/* /listing 001.02 */
<!-- listing 001.01 -->
second
<!-- /listing 001.01 -->
ignore again
/* listing 001.01 */
third
/* /listing 001.01 */
ignore final
/* listing 001.02 */
bbb
/* /listing 001.02 */
more ignore
"_comment": "listing 001.01"
fourth
"_comment": "/listing 001.01"
outro
SOURCE;

        $parser = new Parser();
        $parser->parse($source);
        $expected = [
            "001.01" => [
                "first", "second", "third", "fourth"
            ],
            "001.02" => [
                "aaa", "bbb"
            ]
        ];
        $extracted = $parser->getMatches();
        $this->assertEquals($expected, $extracted);
        $output = $parser->parse($source, "001.01", true);
        $this->assertEquals(implode("\n", ["// listing 001.01", "first", "second", "third", "fourth", ""]), $output); 
    }

    public function testArgs() {
        $source = <<<SOURCE
intro
/* listing 001.01 chop */
first

/* /listing 001.01 */

more ignore

"_comment": "listing 001.02 jsonwrap"
    "hats": "green"
"_comment": "/listing 001.02"
outro
SOURCE;

        $parser = new Parser();
        $parser->parse($source);
        $extracted = $parser->getMatches();
        $expected = [
            "001.01" => [
                "first"
            ],
            "001.02" => [
                "{", '    "hats": "green"', "}"
            ]
        ];
        $this->assertEquals($expected, $extracted);
    }
}
