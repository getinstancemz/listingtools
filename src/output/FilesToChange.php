<?php

namespace getinstance\listingtools\output;

class FilesToChange
{
    private $files = [];
    private $stdoutonly = false;

    function __construct($stdoutonly = false)
    {
        if ($stdoutonly) {
            $this->stdoutonly = true;
        }
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

    function saveFiles($removetag = null)
    {
        foreach ($this->files as $file => $contents) {
            if (! is_null($removetag)) {
                $contents = preg_replace("/$removetag/", "", $contents);
            }
            if ($this->stdoutonly) {
                fwrite(STDERR, "write to STDOUT only for $file:\n");
                print $contents . "\n";
            } else {
                fwrite(STDERR, "writing $file\n");
                file_put_contents($file, $contents);
            }
        }
    }
}
