<?php

namespace GithubProjectViewer\Util;

class Lookup{
    private $map;
    public function __construct(){
        $this->map = [];
    }

    private static function makeKey($key){
        return serialize($key);
    }

    /**
     * Summary of getStudentItem
     * @param int $id
     * @return mixed[]
     */
    public function getItem(mixed $index): array {
        $key = self::makeKey($index);
        if(isset($this->map[$key])){
            return $this->map[$key]["value"];
        }
        return [];
    }

    public function add(mixed $id, $item): void{
        $key = self::makeKey($id);
        if(!array_key_exists($key, $this->map)){
            $this->map[$key] = [
                "key"=> $id,
                "value"=> []
            ];
        }
        $this->map[$key]['value'][] = $item;
    }

    public function getKeyvalueList(): array{
        return array_values($this->map);
    }
}