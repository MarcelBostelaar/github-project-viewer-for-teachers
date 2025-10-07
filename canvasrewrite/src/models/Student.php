<?php

class Student{
    public $id;
    public $name;

    /**
     * Summary of sections
     * @var string[]
     */
    private array $sections;
    public function __construct(int $id, string $naam, array $sections = []){
        $this->id = $id;
        $this->name = $naam;
        $this->sections = $sections;
    }

    /**
     * Summary of getSections
     * @return string[]
     */
    public function getSections(): array {
        //TODO implement
        return $this->sections;
    }
}