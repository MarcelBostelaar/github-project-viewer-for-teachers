<?php

namespace GithubProjectViewer\Models;

class Student{
    public $id;
    public $name;
    public function __construct(int $id, string $naam){
        $this->id = $id;
        $this->name = $naam;
    }

    /**
     * All the sections this student is in (classes).
     * @return string[]
     */
    public function getSections(): array {
        global $providers;
        return $providers->sectionsProvider->getSectionsForStudent($this->id);
    }
}