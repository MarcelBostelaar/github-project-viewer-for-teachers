<?php

class Group{

    /**
     * May be null for virtual groups (not from canvas)
     * @var int|null $id
     */
    public ?int $id;
    public string $name;
    public ?array $students = null;
    public function __construct(int $id, string $name){
        $this->id = $id;
        $this->name = $name;
    }
}