<?php
namespace GithubProjectViewer\Services;

class VirtualIDHandler {
    private $mapping = [];
    private $concrete_reverse_map = [];
    private $combined_reverse_map = [];

    private function findExisting(ConcreteGithublinkSubmission | CombinedGithublinkSubmission $submission): string | null {
        if($submission instanceof ConcreteGithublinkSubmission){
            return $this->concrete_reverse_map[$submission->getCanvasID()] ?? null;
        }
        else{
            return $this->combined_reverse_map[$submission->getGroup()->id] ?? null;
        }
    }

    public function getVirtualIdFor(IGithublinkSubmission $submission): string {
        $existing = $this->findExisting($submission);
        if($existing !== null){
            return $existing;
        }
        //virtual ids are based on real canvas ids, but prefixed with vID-C- or vID-G- to avoid collisions
        //This way they can be saved in localstorage and caching for later use
        if($submission instanceof ConcreteGithublinkSubmission){
            //Virtual ID Concrete
            $newID = "vID-C-" . $submission->getCanvasID();
            $this->concrete_reverse_map[$submission->getCanvasID()] = $newID;
        }
        else{
            //Virtual ID Grouped
            $newID = "vID-G-" . $submission->getGroup()->id;
            $this->combined_reverse_map[$submission->getGroup()->id] = $newID;
        }
        $this->mapping[$newID] = $submission;
        return $newID;
    }

    public function get(string $virtualID): IGithublinkSubmission | null {
        return $this->mapping[$virtualID] ?? null;
    }
}

class VirtualIDsProvider implements IVirtualIDsProvider {
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

    public function get(string $virtualID): IGithublinkSubmission | null {
        return $this->handler->get($virtualID);
    }
}