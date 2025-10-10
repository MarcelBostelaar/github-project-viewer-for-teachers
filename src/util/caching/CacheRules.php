<?php

namespace GithubProjectViewer\Util\Caching;

interface CacheRules{
    public function getKey(...$items): string;
    public function serializeItem($item): string;
    /**
     * Returns whether or not the key just generated using these rules is valid. True if cached value may be returned, false if unknown or disallowed.
     * @arg $key For error tracking purposes
     * @return void
     */
    public function getValidity(): bool;
    /**
     * Post-caching signal in which the caching rule implementation can perform any extra operations.
     * @return void
     */
    public function signalSuccesfullyCached();
    /**
     * Gets optional metadata that is put in the metadata of the cache entry.
     * @return void
     */
    public function getMetaData(): array;
}
abstract class AGeneralCacheRules implements CacheRules{ 
    public function getKey(...$items): string{
        $parts = array_map(fn($x) => $this->serializeItem($x), $items);
        return implode("|", $parts);
    }
    /**
     * Serializes an item into a string for inclusion in a cache key.
     * Override/decorate to change key generation functionality.
     * @param mixed $item to serialize
     * @return string
     */
    public function serializeItem($item): string{
        if(is_scalar($item)){
            //Ensure uniformity in key generation when dealing with ints that might be strings other times.
            //At least for a few cases.
            if(is_bool($item)){
                return $item ? 'true' : 'false';
            }
            //int float string
            return (string)$item;
        }
        return serialize($item);
    }
    /**
     * Returns whether or not the key just generated using these rules is valid. True if cached value may be returned, false if unknown or disallowed.
     * @arg $key For error tracking purposes
     * @return void
     */
    public abstract function getValidity(): bool;
    /**
     * Post-caching signal in which the caching rule implementation can perform any extra operations.
     * @return void
     */
    public abstract function signalSuccesfullyCached();
    /**
     * Gets optional metadata that is put in the metadata of the cache entry.
     * @return void
     */
    public abstract function getMetaData(): array;

}