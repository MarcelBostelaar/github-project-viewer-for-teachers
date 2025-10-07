<?php

const APIKSW = "api_keys_studentID_whitelist";
function init_cache(){
    $_SESSION['cache'] = [
        "values" => [],
        APIKSW => []
    ];
}

//Whitelisting API keys for access to specific student IDs
function checkTimeoutAPIKSW($key): bool{
    if(isset($_SESSION['cache'][APIKSW][$key])){
        if($_SESSION['cache'][APIKSW][$key]["expires"] < time()){
            //expired
            unset($_SESSION['cache'][APIKSW][$key]);
            return false;
        }
        return true;
    }
    return false;
}

function whitelist_current_request_for_student_id_in_course(int $studentID){
    global $studentDataCacheTimeout, $providers;
    cache_start();
    $key = $providers->canvasReader->getApiKey() . $providers->canvasReader->getCourseURL();
    checkTimeoutAPIKSW($key);
    if(!isset($_SESSION['cache'][APIKSW][$key])){
        $_SESSION['cache'][APIKSW][$key] = [
            "expires" => time() + $studentDataCacheTimeout,
            "ids" => []
        ];
    }
    $_SESSION['cache'][APIKSW][$key]["ids"][$studentID] = true;
}

/**
 * Summary of canSeeStudentInfo
 * @param mixed $apiKey
 * @param mixed $studentID
 * @return bool. True is whitelisted, false if unknown.
 */
function canSeeStudentInfo($studentID): bool{
    global $providers;
    cache_start();
    $key = $providers->canvasReader->getApiKey() . $providers->canvasReader->getCourseURL();
    checkTimeoutAPIKSW($key);
    if(isset($_SESSION['cache'][APIKSW][$key])){
        if(isset($_SESSION['cache'][APIKSW][$key]["ids"][$studentID])){
            return $_SESSION['cache'][APIKSW][$key]["ids"][$studentID];
        }
    }
    return false;
}

function clearCacheForMetadata(callable $predicate){
    cache_start();
    foreach($_SESSION['cache']['values'] as $key => $entry){
        if($predicate($entry['metadata'])){
            unset($_SESSION['cache']['values'][$key]);
        }
    }
}

function clearCacheForStudentID($studentID){
    clearCacheForMetadata(function($meta) use ($studentID){
        if(is_array($meta) && isset($meta['studentID']) && $meta['studentID'] === $studentID)
            return true;
        return false;
    });
}

function clearCacheForKey($key){
    cache_start();
    if(isset($_SESSION['cache']['values'][$key])){
        unset($_SESSION['cache']['values'][$key]);
    }
}

function getLastCacheDateForStudentID($studentID): ?DateTime{
    cache_start();
    $latest = new DateTime("1970-01-01");
    foreach($_SESSION['cache']['values'] as $entry){
        if(is_array($entry['metadata']) && isset($entry['metadata']['studentID']) && $entry['metadata']['studentID'] === $studentID){
            if($latest === null || $entry['metadata']['date'] > $latest){
                $latest = $entry['metadata']['date'];
            }
        }
    }
    return $latest;
}

function getLastCacheDateForAnyStudents(){
    cache_start();
    $latest = new DateTime("1970-01-01");
    foreach($_SESSION['cache']['values'] as $entry){
        if(is_array($entry['metadata']) && isset($entry['metadata']['studentID'])){
            if($latest === null || $entry['metadata']['date'] > $latest){
                $latest = $entry['metadata']['date'];
            }
        }
    }
    return $latest;
}

//general caching functions
function clearCache(){
    //todo implement non-session
    cache_start();
    init_cache();
}

function cache_start(){
    if(!session_id()){
        session_start();
    }
    if(!isset($_SESSION['cache'])){
        init_cache();
    }
}

function _set_cache($key, $value, $expireSeconds, $metadata){
    cache_start();
    $_SESSION['cache']['values'][$key] = [
        'value'=> $value,
        'expires_at'=> time() + $expireSeconds,
        'metadata' => ($metadata ?? [])
    ];
}

function get_cached($key){
    cache_start();
    
    if (isset($_SESSION['cache']['values'][$key])) {
        if ($_SESSION['cache']['values'][$key]["expires_at"] > time()) {
            return $_SESSION['cache']['values'][$key]["value"];
        }
        //Cache expired
        unset($_SESSION['cache']['values'][$key]);
    }
    return null;
}

function cached_call(CacheRules $cachingRules, int $expireInSeconds,
                        callable $callback, mixed ...$cacheKeyItems){
    cache_start();

    //caching rules help generate key and track validity
    $key = md5($cachingRules->getKey(...$cacheKeyItems));
    
    $data = null;
    if($cachingRules->getValidity()){//if rules say valid, try get from cache
        $data = get_cached($key);
        // echo "Cache " . (($data !== null) ? "hit" : "miss") . "for key $key<br>";
    }
    if($data === null){
        $data = $callback();
        if($data !== null){
            $metadata = $cachingRules->getMetaData();
            _set_cache($key, $data, $expireInSeconds, $metadata);
            //let the rule object know we succesfully retrieved and cached our item
            //rule can use this to perform additional caching work if needed
            $cachingRules->signalSuccesfullyCached(); 
        }
    }
    return $data;
}