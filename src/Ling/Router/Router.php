<?php

namespace Ling\Router;
use function Ling\env;
use function Ling\hook;

/**
 * This support php 5.6
 * Class Router
 * @package Ling\Router
 */

class Router {
    /** from server */
    public $domain;
    public $referrer;
    public $uri;
    public $method;
    public $tokens;
    public $matched;

    /** from user set up */
    public $params;

    /** set in run */
    public $tags;
    public $prefix;
    public $rule;

    public function __construct(array $domainWhiteList = null){
        $domain = $_SERVER['HTTP_HOST'];
        $this->domain = 'localhost';
        if ($domainWhiteList && in_array($domain, $domainWhiteList, true))  {
            $this->domain = $domain;
        }

        // filter input doesn't support cli
        $this->referrer = isset($_SERVER['HTTP_REFERER']) ? filter_var($_SERVER['HTTP_REFERER'], FILTER_SANITIZE_STRING) : null;
        $this->uri = filter_var(strtok($_SERVER['REQUEST_URI'],'?'), FILTER_SANITIZE_SPECIAL_CHARS); //remove query part
        $this->method = strtolower(filter_var($_SERVER['REQUEST_METHOD'], FILTER_SANITIZE_SPECIAL_CHARS));
        $this->tokens = explode('/', $this->uri);
        $this->matched = false;

        $this->tags = array();
        $this->params = array();
    }

    public function params(array $params) {
        $this->params = array_merge($this->params, $params);
    }


    /**
     * @param $prefix
     * @param array $rules
     * @param array|null $tags
     */
    public function rules($prefix, array $rules, array $tags = null) {
        if ($this->matched) { // skip when already matched
            return;
        }

        $uri = $this->uri;
        if ($prefix !== '') { // there's some prefix
            if (strpos($uri, $prefix) !== 0) { // not match prefix, there's nothing
                return;
            }
            $uri = substr($uri, strlen($prefix));
        }

        foreach($rules as $rule) {
            list($method, $regex, $controller) = $rule;

            if ($method === 'all' || false !== strpos($this->method, $method)) {
                $regex = preg_replace_callback("#{([\S?]+?)}#", function ($matches) {
                    $param = $matches[1];
                    $optional = false;
                    if (strpos($param, '?')) {
                        $param = substr($param, 0, -1);
                        $optional = true;
                    }
                    if (isset($this->params[$param])) {
                        $val = $this->params[$param];
                        return "($val)" . ($optional ? '?' : '');
                    }
                    return $param;
                }, '#^' . $regex . '$#'); // change params

                if (preg_match($regex, $uri, $matches)) {
                    array_shift($matches);

                    $this->prefix = $prefix;
                    $this->rule = $rule;
                    if ($tags) {
                        $this->tags = array_merge($this->tags, $tags);
                    }
                    if (count($rule) > 3) {
                        $this->tags = array_merge($tags, $rule[3]);
                    }

                    $this->matched = true;

                    $matches[] = $this;

                    env(array('router' => $this));
                    hook('hook.router.initialized');
                    call_user_func_array($controller, $matches);

                    return;
                }
            }
        }
        return;
    }

    public function notFound($handle404 = null){
        if ($this->matched === false) {
            if ($handle404) {
                $handle404();
            } else {
                http_response_code(404);
                hook('hook.router.404');
            }
        }
    }

}
