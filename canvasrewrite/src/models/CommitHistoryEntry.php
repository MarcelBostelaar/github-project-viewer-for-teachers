<?php

class CommitHistoryEntry{
    public string $name;
    public string $description;
    public string $author;
    public DateTime $date;

    public function __construct(string $commitName, string $commitDescription, string $commitAuthor, DateTime $commitDate){
        $this->name = $commitName;
        $this->description = $commitDescription;
        $this->author = $commitAuthor;
        $this->date = $commitDate;
    }
}