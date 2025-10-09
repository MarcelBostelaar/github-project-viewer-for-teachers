<?php
require_once __DIR__ . '/../services/DependenciesContainer.php';
require_once __DIR__ . '/../util/UtilFuncs.php';
class BaseController{
    public function __construct(){
        setupGlobalDependencies();
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