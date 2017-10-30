<?php

namespace Ling\Router;

use function Ling\config as hook;
use function Ling\hook as hook;

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

    
    public function __construct(){
        $this->domain = filter_input(INPUT_SERVER, "HTTP_HOST", FILTER_SANITIZE_URL);
        $this->referrer = filter_input(INPUT_SERVER, "HTTP_REFERER", FILTER_SANITIZE_STRING); // how it works?
        $this->uri = filter_input(INPUT_SERVER, "PATH_INFO", FILTER_SANITIZE_STRING); //remove query part
        $this->method = strtolower(filter_input(INPUT_SERVER, "REQUEST_METHOD", FILTER_SANITIZE_SPECIAL_CHARS));

        $this->rule = null;
        $this->tags = array();
        $this->matched = false;
    }

    public function rules(array $rules, array $tags = null) {
        if ($this->matched) return $this;
        // this code wasn't tested against same prefix addition. and root need test set

        foreach($rules as $rule) {
            $method = $rule[0];
            $regex = $rule[1];
            $controller = $ruls[2];

            if ($method == "all" || strstr($method, $this->method)) {
                if (preg_match($regex, $this->uri, $matches)) {
                    array_shift($match);

                    $this->rule = $rule;
                    if ($tags) $this->tags = array_merge($this->tags, $tags); 
                    if (count($rule) > 3) $this->tags = array_merge($tags, $rule[3]);
                    $this->tags = array_unique($this->tags);

                    $this->matched = true;

                    array_push($matches, $this);

                    config(array("router", $this));
                    hook("hook.router.initialized");
                    call_user_func_array($controller, $matched);

                    return;
                }
            }
        }
    }

    public function notFound(){
        if ($this->matched == false) {
            http_response_code(404);
            hook("hook.router.notFound");
        }
    }

}
