<?php
require_once __DIR__ . '/../util/Caching/Caching.php';
require_once __DIR__ . '/BaseController.php';

class ClearCacheController extends BaseController{
    public function index(){
        // if(isset($_GET['studentID'])){
        //     $this->clearForStudentID(intval($_GET['studentID']));
        //     return "Student id cache cleared.";
        // }
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