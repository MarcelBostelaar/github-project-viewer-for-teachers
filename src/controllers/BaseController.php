<?php

namespace GithubProjectViewer\Controllers;

class BaseController{
    protected $courseID;
    protected $assignmentID;
    public function __construct(){
        $this->courseID = $_GET['course'] ?? $_POST['course'] ?? null;
        $this->assignmentID = $_GET['assignment'] ?? $_POST['assignment'] ?? null;
        if($this->courseID == null){
            http_response_code(400);
            echo "Missing course parameter.";
            exit();
        }
        if($this->assignmentID == null){
            http_response_code(400);
            echo "Missing assignment parameter.";
            exit();
        }
        setupGlobalDependencies($this->courseID, $this->assignmentID);
    }

    protected function getSubmissionFromRequest($fromGet = true){
        if($fromGet){
            $source = $_GET;
        } else {
            $source = $_POST;
        }
        global $providers;
        $id = $source['id'] ?? null;
        if($id !== null){
            $found = $providers->virtualIDsProvider->get($id);
            if($found === null){
                http_response_code(404);
                echo "No submission found for that id.";
                exit();
            }
            return $found;
        }
        else{
            http_response_code(400);
            echo "Missing submission id parameter.";
            exit();
        }
    }
}