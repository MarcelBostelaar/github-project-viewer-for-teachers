<?php

class CommitHistoryEntry{
    public string $description;
    public string $author;
    public DateTime $date;

    public function __construct(string $description, string $Author, DateTime $Date){
        $this->description = $description;
        $this->author = $Author;
        $this->date = $Date;
    }
}