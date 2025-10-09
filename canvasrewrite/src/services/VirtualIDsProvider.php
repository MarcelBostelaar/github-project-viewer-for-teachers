<?php

class VirtualIDHandler {
    private $mapping = [];
    private $concrete_reverse_map = [];
    private $combined_reverse_map = [];

    private function findExisting(ConcreteGithublinkSubmission | CombinedGithublinkSubmission $submission): int | null {
        if($submission instanceof ConcreteGithublinkSubmission){
            return $this->concrete_reverse_map[$submission->getCanvasID()] ?? null;
        }
        else{
            return $this->combined_reverse_map[$submission->getGroup()->id] ?? null;
        }
    }

    public function getVirtualIdFor(IGithublinkSubmission $submission) {
        $existing = $this->findExisting($submission);
        if($existing !== null){
            return $existing;
        }
        $newID = rand();
        $this->mapping[$newID] = $submission;
        if($submission instanceof ConcreteGithublinkSubmission){
            $this->concrete_reverse_map[$submission->getCanvasID()] = $newID;
        }
        else{
            $this->combined_reverse_map[$submission->getGroup()->id] = $newID;
        }
        return $newID;
    }

    public function get(int $virtualID): IGithublinkSubmission | null {
        return $this->mapping[$virtualID] ?? null;
    }
}

class VirtualIDsProvider {
    private VirtualIDHandler $handler;
    private const CACHEKEY = "virtual_ids_provider_instance";
    public function __construct() {
        global $veryLongTimeout;
        cache_start();
        $existing = get_cached(self::CACHEKEY);
        if($existing !== null){
            $this->handler = $existing;
            return;
        }
        $this->handler = new VirtualIDHandler();
        _set_cache(self::CACHEKEY, $this->handler, $veryLongTimeout, []);
    }

    public function getVirtualIdFor(IGithublinkSubmission $submission) {
        return $this->handler->getVirtualIdFor($submission);
    }

    public function get(int $virtualID): IGithublinkSubmission | null {
        return $this->handler->get($virtualID);
    }
}