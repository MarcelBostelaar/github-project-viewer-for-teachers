<?php
function githubCurlCall($url): array {
    // echo "Fetching URL: $url<br>";
    // Initialize cURL
    $ch = curl_init($url);

    $headers = [
        "User-Agent: Student Project Viewer",
        "Content-Type: application/json"
    ];
    global $githubAuthKey;
    if($githubAuthKey !== null && trim($githubAuthKey) !== ""){
        $headers[] = "Authorization: Bearer " . trim($githubAuthKey);
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