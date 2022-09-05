<?php

namespace getinstance\listingtools\output;

class Chat {
    public function out($str) {
        print $str;
    }

    public function warn($str) {
        fwrite(STDERR, $str);
    }
}
