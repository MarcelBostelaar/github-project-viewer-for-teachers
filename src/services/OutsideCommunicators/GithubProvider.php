<?php

namespace GithubProjectViewer\Services\OutsideCommunicators;

use DateTime;
use Error;
use Exception;
use GithubProjectViewer\Models\CommitHistoryEntry;
use GithubProjectViewer\Models\GithublinkSubmission\SubmissionStatus;

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

class GithubProvider{
    private ?string $githubAuthKey;

    public function __construct(string $githubAuthKey = null){
        $trimmed = trim($githubAuthKey);
        if($trimmed === ""){
            $this->githubAuthKey = null;
        }
        else{
            $this->githubAuthKey = $trimmed;
        }
    }
    
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
     * Tries to retrieve commit history. Does not depend on validate url, so can be used to check for empty repos.
     * @param DisectedURL $url
     * @return CommitHistoryEntry[]|SubmissionStatus
     */
    protected function getCommitHistoryInternal(DisectedURL $url) : array | SubmissionStatus {
        $data = self::githubCurlCall($url->toApiUrl() . "/commits");
        if(isset($data['status'])){
            if($data["status"] == 404){
                return SubmissionStatus::NOTFOUND;
            }
            if(str_contains($data['message'], "Git Repository is empty")){
                return SubmissionStatus::VALID_BUT_EMPTY;
            }
            if(str_contains($data['message'], "API rate limit exceeded")){
                return [
                    new CommitHistoryEntry("GitHub API rate limit exceeded. Please try again later or set authentication.", "System", new DateTime(), "")
                ];
            }
            throw new Exception("Error, status code:" . json_encode($data));
        }
        try{
            $history = array_map(function($commit) {
                // formatted_var_dump($commit);
                $commitDescription = $commit['commit']['message'];
                $commitDate = $commit['commit']["author"]['date'];
                $commitAuthor = $commit['commit']["author"]['name'];
                $commitUrl = $commit['html_url'];
                return new CommitHistoryEntry($commitDescription, $commitAuthor, new DateTime($commitDate), $commitUrl);
            }, $data);
            return $history;
        }catch(Error $e){
            $data = json_encode($data);
            return [
                new CommitHistoryEntry("Error fetching commit history: " . $e->getMessage() . "<br><pre>$data</pre>", "System", new DateTime(), "")
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

    private function githubCurlCall($url): array {
        // echo "Fetching URL: $url<br>";
        // Initialize cURL
        $ch = curl_init($url);

        $headers = [
            "User-Agent: Student Project Viewer",
            "Content-Type: application/json"
        ];
        if($this->githubAuthKey !== null){
            $headers[] = "Authorization: Bearer $this->githubAuthKey";
        }

        // Set headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Return response instead of outputting
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute
        $response = curl_exec($ch);

        // Handle errors
        if (curl_errno($ch)) {
            echo "cURL Error: " . curl_error($ch);
            throw new Exception("cURL Error: " . curl_error($ch));
        } else {
            // formatted_var_dump($response);
            $data = json_decode($response, true);
        }

        // Close
        curl_close($ch);
        // echo "Total data: " . count($data) . "<br>";
        return $data;
    }
}

// class GithubProvider extends UncachedGithubProvider{
//     public function validateUrl(string $url): SubmissionStatus {
//         $rules = new \SaveKeyWrapper(new \SetMetadataType(new \Unrestricted(), "github"));
//         global $veryLongTimeout, $dayTimeout;
//         $result = cached_call($rules, 
//         $dayTimeout, fn() => parent::validateUrl($url),
//         "GithubProvider", "validateURL", $url);
//         if($result === SubmissionStatus::VALID_URL){
//             //Very long cache.
//             changeCacheExpireTimeForKey($rules->generatedKey, $veryLongTimeout);
//         }
//         else{
//             //Very short cache.
//         }
//         return $result;
//     }

//     public function getCommitHistory(string $url): array {
//         $rules = new \SetMetadataType(new \Unrestricted(), "github");
//         global $dayTimeout;
//         $result = cached_call($rules, 
//         $dayTimeout, fn() => parent::getCommitHistory($url),
//         "GithubProvider", "getCommitHistory", $url);
//         return $result;
//     }

//     protected function getCommitHistoryInternal(DisectedURL $url) : array | SubmissionStatus {
//         global $dayTimeout;
//         $rules = new \SetMetadataType(new \Unrestricted(), "github");
//         $result = cached_call($rules, 
//         $dayTimeout, fn() => parent::getCommitHistoryInternal($url),
//         "GithubProvider", "getCommitHistoryInternal", $url);
//         return $result;
//     }
// }
