<?php
require_once __DIR__ . '/../util/caching/Caching.php';
require_once __DIR__ . '/../util/caching/SaveKeyWrapper.php';
require_once __DIR__ . '/../util/caching/Unrestricted.php';
require_once __DIR__ . '/../util/GenericCurlCalls.php';

class DisectedURL{
    public string $owner;
    public string $repo;

    public function __construct(string $original, string $owner, string $repo){
        $this->owner = $owner;
        $this->repo = $repo;
    }

    public static function fromUrl(string $url) : ?DisectedURL{
        // echo "Matching URL:<br>";
        var_dump($url);
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
        //TODO find a different way that doesnt use headers, as it gives a session error
        //ping and return false if 404
        $parsed = DisectedURL::fromUrl($url);
        if ($parsed === null) {
            return SubmissionStatus::MISSING;
        }
        $headers = @get_headers(url: $parsed->toWebUrl());
        if ($headers && strpos($headers[0], '200') !== false) {
            return SubmissionStatus::VALID_URL;
        }
        return SubmissionStatus::NOTFOUND;
    }

    /**
     * Summary of getCommitHistory
     * @param string $url
     * @return CommitHistoryEntry[]
     */
    public function getCommitHistory(string $url): array{
        // if(!$this->validateUrl($url) === SubmissionStatus::VALID_URL){
        //     throw new Exception("Invalid URL, cannot get commit history");
        // }
        // $url = DisectedURL::fromUrl($url);
        // $data = genericCurlCall($url->toApiUrl() . "/commits");
        // formatted_var_dump($data);
        // // TODO implement    
        return [
            new CommitHistoryEntry("Initial commit", "Description", "Marcel Bostelaar", new DateTime("2024-01-01 12:00:00")),
            new CommitHistoryEntry("Added README", "Description", "Marcel Bostelaar", new DateTime("2024-01-02 12:00:00")),
            new CommitHistoryEntry("Fixed bugs", "Description", "Marcel Bostelaar", new DateTime("2024-01-03 12:00:00")),
        ];
    }
}

class GithubProvider extends UncachedGithubProvider{
    public function validateUrl(string $url): SubmissionStatus {
        $rules = new SaveKeyWrapper(new Unrestricted());
        // $rules = new SaveKeyWrapper(new SetMetadataType(new Unrestricted(), "github"));
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

    // public function getCommitHistory(string $url): array {
    //     $rules = new SetMetadataType(new Unrestricted(), "github");
    //     global $dayTimeout;
    //     $result = cached_call($rules, 
    //     $dayTimeout, fn() => parent::getCommitHistory($url),
    //     "GithubProvider", "getCommitHistory", $url);
    //     return $result;
    // }
}
