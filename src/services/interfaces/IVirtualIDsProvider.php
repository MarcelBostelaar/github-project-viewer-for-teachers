<?php

interface IVirtualIDsProvider {
    public function getVirtualIdFor(IGithublinkSubmission $submission);
    public function get(string $virtualID): IGithublinkSubmission | null;
}