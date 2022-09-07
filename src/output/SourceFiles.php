<?php

namespace getinstance\listingtools\output;

class SourceFiles
{
    private $files = [];

    function __construct(public bool $stdoutonly = false, public bool $dryrun = false)
    {
    }

    function getFileContents($file)
    {
        if (! is_file($file)) {
            throw new \Exception("'$file' is not a file");
        }
        if (! isset($this->files[$file])) {
            $this->files[$file] = file_get_contents($file);
        }
        return $this->files[$file];
    }

    function storeFile($file, $contents)
    {
        $this->files[$file] = $contents;
    }

    public function doIndex($dir, callable $filecallback, $indexed = [])
    {
        if (in_array($dir, $indexed)) {
            return;
        }
        if (! file_exists($dir)) {
            throw new \Exception("'$dir' does not exist");
        }

        if (is_file($dir)) {
            return $filecallback($dir);
        }

        $iterator = new \DirectoryIterator($dir);
        foreach ($iterator as $item) {
            if ($item->isDot()) {
                continue;
            }
            if ($item->isLink()) {
                continue;
            }
            $path = $item->getPathName();
            if ($item->isFile()) {
                $filecallback($path);
            } elseif (is_dir($path)) {
                $this->doIndex($path, $filecallback, $indexed);
            }
        }
        $indexed[] = $dir;
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
