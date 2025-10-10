<?php

namespace GithubProjectViewer\Util;
class PaginationHeaderHandler{
    public $nextURL = null;

    public function handle($curl, $header_line){
        if(str_starts_with($header_line , 'link:')){
            if (preg_match('/<([^>]*)>;\s*rel="next"/', trim($header_line), $matches)) {
                $this->nextURL = $matches[1];
            }
        }
        // echo $header_line . "<br>";
        return strlen($header_line);
    }
}