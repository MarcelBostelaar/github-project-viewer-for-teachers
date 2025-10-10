<?php

namespace GithubProjectViewer\Util\Caching;
function init_cache(){
    $_SESSION['cache'] = [
        "values" => []
    ];
}

function clearCacheForMetadata(callable $predicate){
    cache_start();
    foreach($_SESSION['cache']['values'] as $key => $entry){
        if($predicate($entry['metadata'])){
            unset($_SESSION['cache']['values'][$key]);
        }
    }
}

function clearCacheForKey($key){
    cache_start();
    if(isset($_SESSION['cache']['values'][$key])){
        unset($_SESSION['cache']['values'][$key]);
    }
}

function changeCacheExpireTimeForKey($key, $newExpireSeconds){
    cache_start();
    if(isset($_SESSION['cache']['values'][$key])){
        $_SESSION['cache']['values'][$key]['expires_at'] = time() + $newExpireSeconds;
    }
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