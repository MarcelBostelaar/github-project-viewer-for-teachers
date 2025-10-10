<?php

namespace GithubProjectViewer\Models;

class Group{
    public int $id;
    public string $name;
    public ?array $students = null;
    public function __construct(int $id, string $name){
        $this->id = $id;
        $this->name = $name;
    }
}