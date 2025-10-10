<?php

namespace GithubProjectViewer\Services\OutsideCommunicators;
class GitProvider{
    private string $folderpath;
    
    public function __construct(string $folderpath) {
        $this->folderpath = $folderpath;
    }
    
    public function clone(string $url): string{
        $parsed = DisectedURL::fromUrl($url);
        
        // Clean the folder first
        $this->clean();
        
        // Clone directly into the folder
        $gitUrl = $parsed->toGitURL();
        $command = "git clone \"$gitUrl\" \"$this->folderpath\"";
        
        exec($command . ' 2>&1', $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \Exception("Git clone failed: " . implode("\n", $output));
        }
        
        return $this->folderpath;
    }

    /**
     * Removes all files in the folderpath
     * @return void
     */
    public function clean(){
        // Delete the folder if it exists
        if (is_dir($this->folderpath)) {
            exec("rmdir /s /q \"$this->folderpath\"", $output, $returnCode);
        }
        
        // Create the empty folder
        mkdir($this->folderpath, 0755, true);
    }
}