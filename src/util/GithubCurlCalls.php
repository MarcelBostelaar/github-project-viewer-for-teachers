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

    // Get HTTP status code
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

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
    
    // If status code is not 200, return the code
    if ($httpCode !== 200) {
        return ['status_code' => $httpCode];
    }
    // echo "Total data: " . count($data) . "<br>";
    return $data;
}