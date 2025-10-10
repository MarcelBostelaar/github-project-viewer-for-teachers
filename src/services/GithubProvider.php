<?php
require_once __DIR__ . '/../util/caching/Caching.php';
require_once __DIR__ . '/../util/caching/SaveKeyWrapper.php';
require_once __DIR__ . '/../util/caching/Unrestricted.php';
require_once __DIR__ . '/../util/caching/SetMetadataType.php';
require_once __DIR__ . '/../util/GithubCurlCalls.php';

class DisectedURL{
    public string $owner;
    public string $repo;

    public function __construct(string $original, string $owner, string $repo){
        $this->owner = $owner;
        $this->repo = $repo;
    }

    public static function fromUrl(string $url) : ?DisectedURL{
        // echo "Matching URL:<br>";
        // var_dump($url);
        // Match GitHub repo URLs: https://github.com/{owner}/{repo}[.git]
        $pattern = '/^https?:\/\/github\.com\/([^\/]+)\/([^\/]+?)(?:\.git)?\/?$/';
        if (preg_match($pattern, $url, $matches)) {
            // echo "Matched!<br>";
            $owner = $matches[1];
            $repo = $matches[2];
            return new DisectedURL($url, $owner, $repo);
        }
        // echo "No match.<br>";
        return null;
    }

    public function toApiUrl() : string{
        return "https://api.github.com/repos/$this->owner/$this->repo";
    }

    public function toGitURL() : string{
        return "https://github.com/$this->owner/$this->repo.git";
    }

    public function toWebUrl() : string{
        return "https://github.com/$this->owner/$this->repo";
    }
}

class UncachedGithubProvider{
    
    public function validateUrl(string $url) : SubmissionStatus{
        //ping and return false if 404
        $parsed = DisectedURL::fromUrl($url);
        if ($parsed === null) {
            return SubmissionStatus::MISSING;
        }
        $retrieved_commits = $this->getCommitHistoryInternal($parsed);
        if($retrieved_commits instanceof SubmissionStatus){
            return $retrieved_commits;
        }
        return SubmissionStatus::VALID_URL;
    }

    /**
     * Tried to retrieve commit history. Does not depend on validate url, so can be used to check for empty repos.
     * @param DisectedURL $url
     * @return CommitHistoryEntry[]|SubmissionStatus
     */
    protected function getCommitHistoryInternal(DisectedURL $url) : array | SubmissionStatus {
        $data = githubCurlCall($url->toApiUrl() . "/commits");
        if(isset($data['status_code']) && $data['status_code'] === 404){
            return SubmissionStatus::NOTFOUND;
        }
        if(isset($data['message'])){
            if(str_contains($data['message'], "API rate limit exceeded")){
                return [
                    new CommitHistoryEntry("GitHub API rate limit exceeded. Please try again later or set authentication.", "System", new DateTime())
                ];
            }
            if(str_contains($data['message'], "Git Repository is empty")){
                return SubmissionStatus::VALID_BUT_EMPTY;
            }
        }
        try{
            $history = array_map(function($commit) {
                $commitDescription = $commit['commit']['message'];
                $commitDate = $commit['commit']["author"]['date'];
                $commitAuthor = $commit['commit']["author"]['name'];
                return new CommitHistoryEntry($commitDescription, $commitAuthor, new DateTime($commitDate));
            }, $data);
            return $history;
        }catch(Error $e){
            $data = json_encode($data);
            return [
                new CommitHistoryEntry("Error fetching commit history: " . $e->getMessage() . "<br><pre>$data</pre>", "System", new DateTime())
            ];
        }
    }

    /**
     * Summary of getCommitHistory
     * @param string $url
     * @return CommitHistoryEntry[]
     */
    public function getCommitHistory(string $url): array{
        if(!$this->validateUrl($url) === SubmissionStatus::VALID_URL){
            throw new Exception("Invalid URL, cannot get commit history");
        }
        $url = DisectedURL::fromUrl($url);
        $result = $this->getCommitHistoryInternal($url);
        if($result === SubmissionStatus::VALID_BUT_EMPTY){
            return [];
        }
        return $result;
    }
}

class GithubProvider extends UncachedGithubProvider{
    public function validateUrl(string $url): SubmissionStatus {
        $rules = new SaveKeyWrapper(new SetMetadataType(new Unrestricted(), "github"));
        global $veryLongTimeout, $dayTimeout;
        $result = cached_call($rules, 
        $dayTimeout, fn() => parent::validateUrl($url),
        "GithubProvider", "validateURL", $url);
        if($result === SubmissionStatus::VALID_URL){
            //Very long cache.
            changeCacheExpireTimeForKey($rules->generatedKey, $veryLongTimeout);
        }
        else{
            //Very short cache.
        }
        return $result;
    }

    public function getCommitHistory(string $url): array {
        $rules = new SetMetadataType(new Unrestricted(), "github");
        global $dayTimeout;
        $result = cached_call($rules, 
        $dayTimeout, fn() => parent::getCommitHistory($url),
        "GithubProvider", "getCommitHistory", $url);
        return $result;
    }

    protected function getCommitHistoryInternal(DisectedURL $url) : array | SubmissionStatus {
        global $dayTimeout;
        $rules = new SetMetadataType(new Unrestricted(), "github");
        $result = cached_call($rules, 
        $dayTimeout, fn() => parent::getCommitHistoryInternal($url),
        "GithubProvider", "getCommitHistoryInternal", $url);
        return $result;
    }
}
