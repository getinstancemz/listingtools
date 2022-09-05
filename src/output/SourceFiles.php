<?php

namespace getinstance\listingtools\output;

class SourceFiles
{
    private $files = [];

    function __construct(public readonly bool $stdoutonly = false, public readonly bool $dryrun = false)
    {
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

    function saveFiles(Chat $chat, $removetag = null)
    {
        if ($this->dryrun) { 
            return;
        }
        foreach ($this->files as $file => $contents) {
            if (! is_null($removetag)) {
                $contents = preg_replace("/$removetag/", "", $contents);
            }
            if ($this->stdoutonly) {
                $chat->out($contents . "\n");
            } else {
                file_put_contents($file, $contents);
            }
        }
    }
}
