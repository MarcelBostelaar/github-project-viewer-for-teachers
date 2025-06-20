<?php

/**
 * Recursively removes a directory and all its contents
 * 
 * @param string $dir Directory path to remove
 * @return void
 */
function rmdir_recursive($dir) {
    if (!is_dir($dir)) {
        return;
    }
    foreach(scandir($dir) as $file) {
        if ('.' === $file || '..' === $file) continue;
        if (is_dir("$dir/$file")) rmdir_recursive("$dir/$file");
        else {
            $path = "$dir/$file";
            chmod( $path, 0777 );
            unlink($path);
        }
    }
    rmdir($dir);
}

/**
 * Manages filter cookies and returns the current filters
 * 
 * @param array $filterFields Array of filter field names
 * @param int $cookieExpiration Cookie expiration time
 * @return array Current filter values
 */
function handleFilterCookies($filterFields, $cookieExpiration = null) {
    // Set default cookie expiration if not provided (30 days)
    if ($cookieExpiration === null) {
        $cookieExpiration = time() + (86400 * 30);
    }
    
    $filters = [];
    
    // Process filter form submission
    if (isset($_POST['filter'])) {
        foreach ($filterFields as $field) {
            if (isset($_POST[$field]) && $_POST[$field] !== '') {
                $filters[$field] = $_POST[$field];
                setcookie("filter_$field", $_POST[$field], $cookieExpiration);
            } else {
                setcookie("filter_$field", '', time() - 3600); // Delete cookie
            }
        }
    } else {
        // Load filters from cookies
        foreach ($filterFields as $field) {
            if (isset($_COOKIE["filter_$field"]) && $_COOKIE["filter_$field"] !== '') {
                $filters[$field] = $_COOKIE["filter_$field"];
            }
        }
    }
    
    // Clear filters if requested
    if (isset($_POST['clear_filters'])) {
        $filters = [];
        foreach ($filterFields as $field) {
            setcookie("filter_$field", '', time() - 3600); // Delete cookie
        }
    }
    
    return $filters;
}