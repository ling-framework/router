<?php

namespace Ling\Router;

use function Ling\hook as hook;

class SimpleRouter {
    /** from server */
    public $domain;
    public $referrer;
    public $uri;
    public $method;

    /** from user set up */
    public $rules;
    public $params;

    /** set in run */
    public $tags;
    public $prefix;
    public $rule;

    
    public function __construct(){
        $this->domain = filter_input(INPUT_SERVER, "HTTP_HOST", FILTER_SANITIZE_URL);
        $this->referrer = filter_input(INPUT_SERVER, "HTTP_REFERER", FILTER_SANITIZE_STRING); // how it works?
        $this->uri = filter_input(INPUT_SERVER, "PATH_INFO", FILTER_SANITIZE_STRING); //remove query part
        $this->method = filter_input(INPUT_SERVER, "REQUEST_METHOD", FILTER_SANITIZE_SPECIAL_CHARS);

        $this->ruleTree = array();
        $this->params = array();
    }

    public function rules(array $rules, array $tags = null) {
        if ($this->matched) return $this;
        // this code wasn't tested against same prefix addition. and root need test set
        $this->rules = $rules;
        foreach ($this->rules as &$rule) {
            if ($tags) {
                if (count($rule) > 3) {
                    $rule[3] = array_unique(array_merge($rule[3], $tags));
                } else {
                    $rule[3] = $tags;
                }
            }

            // if matched, run

            
        }

        //if not matched, 404 error

    }

    public function end(){
        if ($this->matched == false) {
            http_response_code(404);
            hook("hook.router.404");
        }
    }

}
