<?php
namespace GithubProjectViewer\Util\Caching;

class SaveKeyWrapper implements CacheRules{
    public $generatedKey;
    private $wrapped;
    public function __construct(CacheRules $wrapped){
        $this->wrapped = $wrapped;
    }
    
    public function getValidity():bool{
        return $this->wrapped->getValidity();
    }
    public function getMetaData(): array { 
        return $this->wrapped->getMetaData();
     }

    public function getKey(...$items): string{
        $this->generatedKey = $this->wrapped->getKey(...$items);
        return $this->generatedKey;
    }
    public function signalSuccesfullyCached(): void {
        $this->wrapped->signalSuccesfullyCached();
    }
    public function serializeItem($item): string{
        return $this->wrapped->serializeItem($item);
    }
}