<?php
namespace GithubProjectViewer\Controllers\Api;
use GithubProjectViewer\Controllers\BaseController;
function erasethis($buffer)
{
    return "";
}
abstract class APIController extends BaseController{
    protected $debug_keep_output = false;
    public function index(){
        try{
            if(!$this->debug_keep_output){
                ob_start("erasethis");
            }
            $data = $this->handle();
            if(!$this->debug_keep_output){
                ob_end_flush();
            }
            header('Content-Type: application/json');
            echo json_encode($data);
            // var_dump($_SESSION['cache']["values"]);
        }catch(\Exception $e){
            if(!$this->debug_keep_output){
                ob_end_flush();
            }
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(["error" => $e->getMessage(), "trace" => explode("\n", $e->getTraceAsString())]);
        }
    }

    public abstract function handle();
}