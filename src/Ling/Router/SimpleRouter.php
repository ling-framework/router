<?php

namespace Ling\Router;

use function Ling\env;
use function Ling\hook;

class SimpleRouter {
    /** from server */
    public $domain;
    public $referrer;
    public $uri;
    public $method;

    /** set in run */
    public $tags;
    public $rule;
    public $matched;

    
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

        $this->rule = null;
        $this->tags = array();
        $this->matched = false;
    }

    /**
     * @param array $rules
     * @param array|null $tags
     */
    public function rules(array $rules, array $tags = null) {
        if ($this->matched) {
            return;
        }

        foreach($rules as $rule) {
            list($method, $regex, $controller) = $rule;

            if ($method === 'all' || false !== strpos($this->method, $method)) {

                if (preg_match('#^' . $regex . '$#', $this->uri, $matches)) {
                    array_shift($matches);

                    $this->rule = $rule;
                    if ($tags) {
                        $this->tags = array_merge($this->tags, $tags);
                    }
                    if (count($rule) > 3) {
                        $this->tags = array_merge($this->tags, $rule[3]);
                    }
                    $this->matched = true;

                    $matches[] = $this;

                    env(array('router' => $this)); // save to env
                    hook('hook.router.initialized'); // run hook
                    call_user_func_array($controller, $matches); // run controller

                    return;
                }
            }
        }

        return;
    }

    public function notFound(){
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
