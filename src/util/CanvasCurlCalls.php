<?php

namespace GithubProjectViewer\Util;

function curlCall($url, $apiKey): array {
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
            $additionalData = curlCall($nextURLHandler->nextURL, $apiKey)[$topKey];
            $data = array_merge($data, $additionalData);
            $data = [$topKey => $data];
        }
        else{
            $additionalData = curlCall($nextURLHandler->nextURL, $apiKey);
            $data = array_merge($data, $additionalData);
        }
    }
    // echo "Total data: " . count($data) . "<br>";
    return $data;
}
function putCurlCall($url, $apiKey, $field, $data): void {
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
    $encoded = http_build_query([$field => $data]);
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