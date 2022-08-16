<?php

namespace getinstance\listingtools\output;

class Parser
{
    private $output = [];
    private $reading = [];
    private static $trigger = "(?:\\s*/\\*|<!--|#)\\s+";
    private $listingstring = "listing\\s+(\\d+\\.\\d+(?:.\\d+)*)";

    public static function getRegexpTrigger()
    {
        return self::$trigger;
    }

    public function parse($text, $listingno = "", $outputlisting = false)
    {
        $this->reset();
        $lines = explode("\n", $text);
        foreach ($lines as $line) {
            $this->handleLine($line, $listingno);
        }
        $ret = "";
        if (count($this->output)) {
            $count = 0;
            foreach ($this->output as $key => $val) {
                if ($count++ > 0) {
                    $ret .= "\n";
                }
                if ($outputlisting) {
                    $ret .= "// listing {$key}\n";
                }
                $ret .= (implode("\n", $this->output[$key])) . "\n";
            }
        }
        if (empty($ret)) {
            return $ret;
        }
        return $this->doFlush($ret);
    }

    public function doFlush($txt)
    {
        $lines = explode("\n", $txt);
        $start = 0;
        if (preg_match("|\/\/ listing|", $lines[0])) {
            $start = 1;
        }
        $tocut = null;
        for ($x = $start; $x < count($lines); $x++) {
            if (preg_match("/^(\\s*)\\S/", $lines[$x], $matches)) {
                $indent = strlen($matches[1]);
                $tocut = (is_null($tocut) || $indent < $tocut) ? $indent : $tocut;
            }
        }
        $cutme = str_repeat(" ", $tocut);
        for ($x = $start; $x < count($lines); $x++) {
            $lines[$x] = preg_replace("|^{$cutme}|", "", $lines[$x]);
        }
        return implode("\n", $lines);
    }


    public function getMatches()
    {
        return $this->output;
    }

    public function reset()
    {
        $this->reading = [];
        $this->output = [];
    }

    public function handleLine($line, $listingno)
    {
        $trigger = self::getRegexpTrigger();
        if (count($this->reading)) {
            foreach ($this->reading as $reading => $readingcount) {
                $this->readLine($line, $reading, $listingno);
            }
        }
        if (preg_match("%{$trigger}{$this->listingstring}%", $line, $matches)) {
            if (empty($listingno) || ($listingno == $matches[1])) {
                $this->reading[$matches[1]] = 1;
            }
        }
    }

    public function readLine($line, $reading, $listingno)
    {
        $trigger = self::getRegexpTrigger();
        $startmatch = false;
        $endmatch   = false;

        if (preg_match("%{$trigger}/{$this->listingstring}%", $line, $matches)) {
            // could be another listing's end -- so register the match.. we will hide from output
            $endmatch = true;
            if (empty($listingno) || ($listingno == $matches[1])) {
                unset($this->reading[$reading]);
            }
            return;
        }
        if (preg_match("%{$trigger}{$this->listingstring}%", $line, $matches)) {
            // could be another listing's start -- so register the match.. we will hide from output
            $startmatch = true;
        }

        // only include lines that aren't directives
        if (! $startmatch && ! $endmatch) {
            $this->output[$reading][] = $line;
        }
    }
}
