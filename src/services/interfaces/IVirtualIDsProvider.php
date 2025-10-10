<?php

namespace GithubProjectViewer\Services\Interfaces;

use GithubProjectViewer\Models\GithublinkSubmission\IGithublinkSubmission;

interface IVirtualIDsProvider {
    public function getVirtualIdFor(IGithublinkSubmission $submission);
    public function get(string $virtualID): IGithublinkSubmission | null;
}