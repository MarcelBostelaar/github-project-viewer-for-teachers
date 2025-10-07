<?php

require_once __DIR__ . '/../../models/SectionGrouping.php';

class Outcome {
    public string $naam;
    /**
     * An array of arrays of numbers, indicating the periods in which the items are tested.
     * @var int[][]
     */
    public array $toetsmomenten;   // array of arrays
    /**
     * Ordered array of descriptions of the outcome levels.
     * @var string[]
     */
    public array $beschrijvingen;  // array of strings
    public function __construct(string $naam, array $toetsmomenten, array $beschrijvingen) {
        $this->naam = $naam;
        $this->toetsmomenten = $toetsmomenten;
        $this->beschrijvingen = $beschrijvingen;
    }
}


class Config {
    /**
     * @var AllSectionGroupings
     */
    public AllSectionGroupings $sectionGroupings; 
    /**
     * Outcome data
     * @var Outcome[]
     */
    public array $outcomes; 
    public function __construct(AllSectionGroupings $sectionGroupings, array $outcomes) {
        $this->sectionGroupings = $sectionGroupings;
        $this->outcomes = $outcomes;
    }
}

