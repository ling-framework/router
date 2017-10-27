<?php

namespace Ling\Router;

use function Ling\hook as hook;

class Router {
    /** from server */
    public $domain;
    public $referrer;
    public $uri;
    public $method;

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

        $this->ruleTree = array();
        $this->params = array();
    }

    public function rules(string $prefix, array $rules, array $tags = null) {
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


    public function params(array $params) {
        $this->params = array_merge($this->params, $params);
    }


    public function run(){
        // we need common session function 
        //"UserController@showProfile"
        //"func"

        
        hook('hook.router.inited', array($this));
        

        return $this;
    }

}
