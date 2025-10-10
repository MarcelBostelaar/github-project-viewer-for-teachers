<?php

namespace GithubProjectViewer\Services\OutsideCommunicators;

class CanvasCommunicator{
    protected string $apiKey;
    protected string $baseURL;
    protected ?int $courseID;
    protected ?int $assignmentID;

    public function __construct(string $apiKey, string $baseURL, ?string $courseID = null, ?string $assignmentID = null) {
        $this->apiKey = $apiKey;
        $this->baseURL = $baseURL;
        $this->courseID = $courseID;
        $this->assignmentID = $assignmentID;
    }

    //GET calls
    public function assignmentGet(string $endpoint): array {
        $this->validateCourseID();
        $this->validateAssignmentID();
        $url = "$this->baseURL/courses/$this->courseID/assignments/$this->assignmentID/$endpoint";
        $data = self::curlGet($url, $this->apiKey);
        return $data;
    }

    public function courseGet(string $endpoint): array {
        $this->validateCourseID();
        $url = "$this->baseURL/courses/$this->courseID/$endpoint";
        $data = self::curlGet($url, $this->apiKey);
        return $data;
    }

    public function globalGet(string $endpoint): array {
        $url = "$this->baseURL/$endpoint";
        $data = self::curlGet($url, $this->apiKey);
        return $data;
    }

    //PUT calls
    public function assignmentPut(string $endpoint, $data) {
        $this->validateCourseID();
        $this->validateAssignmentID();
        $url = "$this->baseURL/courses/$this->courseID/assignments/$this->assignmentID/$endpoint";
        self::putCurlCall($url, $this->apiKey, $data);
    }

    public function coursePut(string $endpoint, $data) {
        $this->validateCourseID();
        $url = "$this->baseURL/courses/$this->courseID/$endpoint";
        self::putCurlCall($url, $this->apiKey, $data);
    }

    public function globalPut(string $endpoint, $data) {
        $url = "$this->baseURL/$endpoint";
        self::putCurlCall($url, $this->apiKey, $data);
    }

    //Static cURL calls
    private static function curlGet($url, $apiKey): array {
        // echo "Fetching URL: $url<br>";
        // Initialize cURL
        $ch = curl_init($url);

        //Handling header reader to handle paginated results
        $nextURLHandler = new PaginationHeaderHandler();
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, [&$nextURLHandler, "handle"]);

        // Set headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $apiKey",
            "Content-Type: application/json"
        ]);

        // Return response instead of outputting
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute
        $response = curl_exec($ch);

        // Handle errors
        if (curl_errno($ch)) {
            echo "cURL Error: " . curl_error($ch);
            throw new \Exception("cURL Error: " . curl_error($ch));
        } else {
            $data = json_decode($response, true);
        }

        // Close
        curl_close($ch);
        if(isset($data["errors"])){
            $errors = "URL: $url\n";
            foreach($data["errors"] as $message){
                $errors .= $message["message"] . "\n";
            }
            throw new \Exception($errors);
        }
        // var_dump($data);
        //if a next link for paginated results was found, call it recursively, append all results together.
        if($nextURLHandler->nextURL !== null){
            $topKey = null;
            if(!array_is_list($data)){
                //Non-list results need special handling to merge properly
                //Assume the top key is the one that contains the list of results
                $topKey = array_key_first($data);
                if(count($data) != 1 || !array_is_list($data[$topKey])){
                    throw new \Exception("Unexpected data structure when handling pagination for URL $url");
                }
                $data = $data[$topKey];
                $additionalData = self::curlGet($nextURLHandler->nextURL, $apiKey)[$topKey];
                $data = array_merge($data, $additionalData);
                $data = [$topKey => $data];
            }
            else{
                $additionalData = self::curlGet($nextURLHandler->nextURL, $apiKey);
                $data = array_merge($data, $additionalData);
            }
        }
        // echo "Total data: " . count($data) . "<br>";
        return $data;
    }

    private static function putCurlCall($url, $apiKey, $data): void {
        // echo $url . "<br>";
        // echo "Fetching URL: $url<br>";
        // Initialize cURL
        $ch = curl_init($url);

        // Set headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $apiKey"
        ]);

        // Set the HTTP method to PUT
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        $encoded = http_build_query($data);
        // Prepare JSON data
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded);

        // Return response instead of outputting
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Execute
        $response = curl_exec($ch);

        // Handle errors
        if (curl_errno($ch)) {
            echo "cURL Error: " . curl_error($ch);
            throw new \Exception("cURL Error: " . curl_error($ch));
        } else {
            $data = json_decode($response, true);
        }

        // Close
        curl_close($ch);
        if(isset($data["errors"])){
            $errors = "URL: $url\n";
            foreach($data["errors"] as $message){
                $errors .= $message["message"] . "\n";
            }
            throw new \Exception($errors);
        }
    }

    //Validators

    protected function validateCourseID(): void {
        if($this->courseID === null){
            throw new \Exception("Course ID is not set");
        }
    }

    protected function validateAssignmentID(): void {
        if($this->assignmentID === null){
            throw new \Exception("Assignment ID is not set");
        }
    }

    //Getters

    public function getApiKey(): string {
        return $this->apiKey;
    }

    public function getBaseURL(): string {
        return $this->baseURL;
    }

    public function getCourseID(): ?string {
        return $this->courseID;
    }

    public function getAssignmentID(): ?string {
        return $this->assignmentID;
    }
}