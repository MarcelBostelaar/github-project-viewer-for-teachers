<?php
require_once __DIR__ . '/../services/DependenciesContainer.php';
class BaseController{
    public function __construct(){
        setupGlobalDependencies();
    }
}