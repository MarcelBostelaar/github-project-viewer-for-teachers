<?php
namespace GithubProjectViewer\Util\Caching;
class SetMetadataType extends SetMetadata{
    private string $type;
    public function __construct(CacheRules $wrapped, string $type){
        parent::__construct($wrapped, ['type'=>$type]);
        $this->type = $type;
    }
    
    public function getType(): string {
        return $this->type;
    }
}