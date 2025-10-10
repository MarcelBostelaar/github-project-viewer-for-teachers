<?php

namespace GithubProjectViewer\Util\Caching;
class Unrestricted extends AGeneralCacheRules{
    public function getValidity(): bool {
        return true;
    }

    public function getMetaData(): array {
        return [];
    }
    public function signalSuccesfullyCached() {
        //do nothing
    }
}