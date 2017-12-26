<?php
namespace Ling\Router;

use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{

    protected $result = '';

    public function testRouter() {
        $uri = 'main/board/user/view/123';
        $prefix = 'main/board';
        $controller = function ($board_name, $id, $router) { // more argument is ignored..
            $this->result = $board_name . '|' . $id;
        };
        $tags = ['abc', 'def'];
        $rules1 =  [
            ['get', '{board_name}/view/{id}', $controller, ['auth']]
        ]; // if controller is not callable..
        $rules2 =  [
            ['get', 'test/view/{id}', $controller, ['auth2']]
        ]; // if controller is not callable..

        $params = array(
            'board_name' => "[^\/]+",
            'id' => '(\d+)?'
        );

        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['HTTP_REFERER'] = '';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['PATH_INFO'] = $uri;

        $router = new Router();
        $router->params($params);
        $router->rules($prefix, $rules1, $tags);
        $router->rules($prefix, $rules2, $tags);

        $this->assertEquals($this->result, 'user|123');
        $this->assertEquals($router->tags, ['abc', 'def', 'auth']);
    }
}
