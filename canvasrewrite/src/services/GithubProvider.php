<?php
require_once __DIR__ . '/../util/caching/Caching.php';
require_once __DIR__ . '/../util/caching/SaveKeyWrapper.php';
require_once __DIR__ . '/../util/caching/Unrestricted.php';

class UncachedGithubProvider{
    
    public function validateUrl(string $url) : bool{
        //TODO find a different way that doesnt use headers, as it gives a session error
        //ping and return false if 404
        if(str_ends_with($url, ".git")){
            $url = substr($url, 0, -4);
        }
        $headers = @get_headers($url);
        if ($headers && strpos($headers[0], '200') !== false) {
            return true;
        }
        return false;
    }

    /**
     * Summary of getCommitHistory
     * @param string $url
     * @return CommitHistoryEntry[]
     */
    public function getCommitHistory(string $url): array{
        //TODO implement    
        return [
            new CommitHistoryEntry("Initial commit", "Description", "Marcel Bostelaar", new DateTime("2024-01-01 12:00:00")),
            new CommitHistoryEntry("Added README", "Description", "Marcel Bostelaar", new DateTime("2024-01-02 12:00:00")),
            new CommitHistoryEntry("Fixed bugs", "Description", "Marcel Bostelaar", new DateTime("2024-01-03 12:00:00")),
        ];
    }
}

class GithubProvider extends UncachedGithubProvider{
    public function validateUrl(string $url): bool {
        $rules = new SaveKeyWrapper(new Unrestricted());
        global $sharedCacheTimeout;
        $result = cached_call($rules, 
        $sharedCacheTimeout, fn() => parent::validateUrl($url),
        "GithubProvider", "validateURL", $url);
        if(!$result){
            //only cache positive validation.
            clearCacheForKey($rules->generatedKey);
        }
        return $result;
    }

    //TODO cache getCommitHistory as well for one day
}
