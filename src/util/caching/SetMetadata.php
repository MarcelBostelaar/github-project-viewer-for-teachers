<?php

namespace GithubProjectViewer\Util\Caching;
class SetMetadata implements CacheRules{
    public CacheRules $wrapped;
    private array $metadata;
    public function __construct(CacheRules $wrapped, array $metadata){
        $this->wrapped = $wrapped;
        $this->metadata = $metadata;
    }
    
    public function getValidity():bool{
        return $this->wrapped->getValidity();
    }
    public function getMetaData(): array { 
        return $this->metadata;
     }

    public function getKey(...$items): string{
        return $this->wrapped->getKey(...$items);
    }
    public function signalSuccesfullyCached(): void {
        $this->wrapped->signalSuccesfullyCached();
    }
    public function serializeItem($item): string{
        return $this->wrapped->serializeItem($item);
    }
} 