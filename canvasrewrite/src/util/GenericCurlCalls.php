<?php
function genericCurlCall($url): array {
    // echo "Fetching URL: $url<br>";
    // Initialize cURL
    $ch = curl_init($url);

    // Set headers
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json"
    ]);

    // Return response instead of outputting
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute
    $response = curl_exec($ch);

    // Handle errors
    if (curl_errno($ch)) {
        echo "cURL Error: " . curl_error($ch);
        throw new Exception("cURL Error: " . curl_error($ch));
    } else {
        $data = json_decode($response, true);
    }

    // Close
    curl_close($ch);
    
    // echo "Total data: " . count($data) . "<br>";
    return $data;
}