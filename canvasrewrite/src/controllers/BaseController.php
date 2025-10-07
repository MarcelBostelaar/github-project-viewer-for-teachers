<?php
require_once __DIR__ . '/../services/DependenciesContainer.php';
require_once __DIR__ . '/../util/UtilFuncs.php';
class BaseController{
    public function __construct(){
        setupGlobalDependencies();
    }
}