<?php
require_once __DIR__ . '/BaseController.php';


class TestController extends BaseController{
    public function index(){
        echo "Testcontroller<br>";
        global $providers;
        // formatted_var_dump($providers->groupProvider->getAllGroups());
    }
}

$x = new TestController();
$x->index();