<?php
namespace Ling\Router;

use PHPUnit\Framework\TestCase;

class SimpleRouterTest extends TestCase
{

    protected $result = '';

    public function testSimpleRouter() {
        $uri = 'main/board/user/view/123';
        $controller = function ($board_name, $id, $router) { //last object is router, more arguments will be ignored..
            $this->result = $board_name . '|' . $id;
        };
        $testController = function ($board_name, $id, $router) { //last object is router, more arguments will be ignored..
            $this->result = $board_name . '|' . $id;
        };

        $tags = ['auth', 'def'];
        $rules =  [
            ['get', 'main/board/([^\/]+)/view/([0-9]+)?', $controller, ['abc']],
            ['get', 'main/test/([^\/]+)/view/([0-9]+)?', $testController, ['ghi']],
        ];

        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['HTTP_REFERER'] = '';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['PATH_INFO'] = $uri;

        $router = new SimpleRouter(['localhost']);
        $router->rules($rules, $tags);

        $this->assertEquals($router->tags, ['auth', 'def', 'abc']);
        $this->assertEquals($this->result, 'user|123');
    }
}
