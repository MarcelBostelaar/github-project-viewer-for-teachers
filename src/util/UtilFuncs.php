<?php
namespace GithubProjectViewer\Util;

function isSetMany($object, ...$keys): mixed{
    foreach($keys as $key){
        if(!isset($object[$key])){
            return [false, $key];
        }
    }
    return [true, null];
}

function arrayTurboFlattener(...$arrays): array{
    $result = [];
    foreach($arrays as $array){
        if(is_array($array)){
            $array = array_values($array);
            $result = array_merge($result, arrayTurboFlattener(...$array));
        } else {
            $result[] = $array;
        }
    }
    return $result;
}

function shiftArrayToRight(&$array, $fillValueGenerator = null, $positions = 1){
    if($fillValueGenerator == null){
        $fillValueGenerator = fn() => null;
    }
    for($i = 0; $i < $positions; $i++){
        array_unshift($array, $fillValueGenerator());
    }
    return $array;
}

function roundToNearestFraction(float $value, int $n): float {
    if ($n <= 0) {
        throw new \InvalidArgumentException('n must be a positive integer');
    }
    $fraction = 1 / $n;
    return round($value * $fraction) / $fraction;
}

function array_map_assoc(callable $callback, array $array): array
{
    $data = array_map(function($key) use ($callback, $array){
        return $callback($key, $array[$key]);
    }, array_keys($array));
    $realdata = [];
    foreach($data as $item){
        $realdata[$item['key']] = $item['value'];
    }
    return $realdata;
}

function formatted_var_dump($data){
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
}

function array_unique_predicate($predicate, $array): array {
    $seen = [];
    $result = [];
    foreach ($array as $item) {
        $key = $predicate($item);
        if (!in_array($key, $seen, true)) {
            $seen[] = $key;
            $result[] = $item;
        }
    }
    return $result;
}

function array_any($array, $predicate): bool {
    foreach ($array as $item) {
        if ($predicate($item)) {
            return true;
        }
    }
    return false;
}