<?php

namespace GithubProjectViewer\Models\GithublinkSubmission;
enum SubmissionStatus : string{
    case MISSING = "Not submitted";
    case NOTFOUND = "Not found (private?)";
    case VALID_BUT_EMPTY = "Empty repository";
    case VALID_URL = "Valid URL";
}