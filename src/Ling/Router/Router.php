<?php

namespace Ling\Router;

use function Ling\hook as hook;

class Router {
    /** from server */
    public $domain;
    public $referrer;
    public $uri;
    public $method;
    public $tokens;
    public $matched;

    /** from user set up */
    public $ruleTree;
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
        $this->tokens = explode($this->uri);
        $this->matched = false;

        $this->ruleTree = array();
        $this->params = array();
    }

    public function params(array $params) {
        $this->params = array_merge($this->params, $params);
    }


    public function rules(string $prefix, array $rules, array $tags = null) {
        if ($this->matched) return $this;
        
        $tokens = explode("/", $prefix);
        $temp = &$this->ruleTree;
        
        foreach($tokens as $token) {
            $temp[$token]= array();
            $temp = &$temp[$token];
        }

        if ($tags) {
            foreach ($rules as &$rule) {
                if (count($rule) > 3) {
                    $rule[3] =array_unique(array_merge($rule[3], $tags));
                }
            }
        }
        
        array_push($temp, $rules);
        
        return $this;
    }

    public function end(){
        if ($this->matched == false) {
            http_response_code(404);
            hook("hook.router.404");
        }
    }

}
