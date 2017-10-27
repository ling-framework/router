<?php
namespace Ling\Router;

use PHPUnit\Framework\TestCase;
use Ling\Router\Router;

class RouterTest extends TestCase
{

    protected $result = "";


    public function testRouter() {
        $uri = "main/board/user/view/123";
        $prefix = "main/board";
        $controller = function ($board_name, $id, $router) { // more argument is ignored..
            $this->result = $board_name . "|" . $id;
        };
        $tags = ["auth", "def"];
        $rules =  [
            ["GET", "{board_name}/view/{id}", $controller, ["auth"]]
        ]; // if controller is not callable..
   
        $params = [
            "board_name" => "[\s]+",
            "id" => "([0-9]+)?"
        ];

        $router = new Router();
        $router->params($params);
        $router->rules($prefix, $rules, $tags);

        $_SERVER["PATH_INFO"] = $uri; 
        $router->run();
        
        $this->assertEquals($this->result, "user|123");
        $this->assertEquals($this->tags, ["abc", "def", "auth"]);
    }
}
