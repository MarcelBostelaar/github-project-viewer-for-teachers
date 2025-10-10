<?php
namespace GithubProjectViewer\Controllers;

class ClearCacheController{
    public function index(){
        if(isset($_GET['type'])){
            clearCacheForMetadata(function($x){
                if(isset($x['type']) && $x['type'] === $_GET['type']){
                    return true;
                }
                return false;
            });
            echo "Cache cleared for type " . htmlspecialchars($_GET['type']) . ".";
            return;
        }
        clearCache();
        echo "Cache cleared.";
    }

    // public function clearForStudentID($studentID){
    //     clearCacheForStudentID($studentID);
    //     echo "Cache cleared for student ID $studentID";
    // }
}

$x = new ClearCacheController();
$x->index();