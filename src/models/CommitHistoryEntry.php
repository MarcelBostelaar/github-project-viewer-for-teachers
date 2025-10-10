<?php

namespace GithubProjectViewer\Models;

class CommitHistoryEntry{
    public string $description;
    public string $author;
    public string $url;
    public \DateTime $date;

    public function __construct(string $description, string $Author, \DateTime $Date, string $url){
        $this->description = $description;
        $this->author = $Author;
        $this->date = $Date;
        $this->url = $url;
    }
}